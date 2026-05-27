<?php

namespace CF7_Mate\Lite\Features\AI_Form_Generator;

if (! defined('ABSPATH')) {
	exit;
}

class AI_Settings
{

	const OPTION_KEY = 'cf7m_ai_settings';

	private static $instance = null;

	public static function instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{
		add_action('cf7m_admin_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('rest_api_init', array($this, 'register_routes'));
		add_filter('cf7m_admin_app_localize', array($this, 'filter_admin_localize'), 10, 2);
	}

	/**
	 * Inject AI provider config so the Settings app can render the AI Provider tab.
	 *
	 * @param array $localize Existing localize data.
	 * @param array $options  Options passed to render_app_root.
	 * @return array
	 */
	public function filter_admin_localize($localize, $options)
	{
		$localize['aiProviders'] = self::get_providers();
		return $localize;
	}

	public function enqueue_assets($app)
	{
		// AI settings styles are bundled into the main settings.css; nothing extra to enqueue.
		unset($app);
	}

	/**
	 * Register REST routes.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function register_routes()
	{
		register_rest_route(
			'cf7-styler/v1',
			'/ai-settings',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_settings'),
					'permission_callback' => array($this, 'check_permission'),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'save_settings'),
					'permission_callback' => array($this, 'check_permission'),
				),
			)
		);

		register_rest_route(
			'cf7-styler/v1',
			'/ai-settings/test',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'test_connection'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);
	}

	/**
	 * Check user permission.
	 *
	 * @since  3.0.0
	 * @return bool
	 */
	public function check_permission()
	{
		return current_user_can('manage_options');
	}

	/**
	 * Get settings via REST.
	 *
	 * @since  3.0.0
	 * @return \WP_REST_Response
	 */
	public function get_settings()
	{
		$settings = self::get_all_settings();

		// Mask API keys for security - never expose full keys.
		$key_fields = array('openai_key', 'anthropic_key', 'kimi_key', 'grok_key');
		foreach ($key_fields as $key) {
			if (! empty($settings[$key])) {
				$settings[$key . '_masked'] = $this->mask_key($settings[$key]);
				$settings[$key . '_set']    = true;
			} else {
				$settings[$key . '_set'] = false;
			}
			unset($settings[$key]);
		}

		return rest_ensure_response($settings);
	}

	/**
	 * Save settings via REST.
	 *
	 * @since  3.0.0
	 * @param  \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function save_settings(\WP_REST_Request $request)
	{
		$current = self::get_all_settings();
		$params  = $request->get_json_params();

		// Validate provider.
		$valid_providers = array_keys(self::get_providers());
		$provider        = sanitize_key($params['provider'] ?? $current['provider']);
		if (! in_array($provider, $valid_providers, true)) {
			$provider = 'openai';
		}

		$settings = array(
			'provider'        => $provider,
			'openai_model'    => sanitize_text_field($params['openai_model'] ?? $current['openai_model']),
			'anthropic_model' => sanitize_text_field($params['anthropic_model'] ?? $current['anthropic_model']),
			'kimi_model'      => sanitize_text_field($params['kimi_model'] ?? $current['kimi_model']),
			'grok_model'      => sanitize_text_field($params['grok_model'] ?? $current['grok_model']),
		);

		// Process API keys.
		// Three distinct intents from the client:
		//   1. Field NOT in payload         → preserve existing value
		//   2. Field is the masked dots     → preserve existing value (user didn't retype the key)
		//   3. Field is an explicit empty   → CLEAR the stored value
		//   4. Field is a real value        → encrypt and store
		$key_fields = array('openai_key', 'anthropic_key', 'kimi_key', 'grok_key');
		foreach ($key_fields as $key) {
			if (! array_key_exists($key, $params)) {
				$settings[$key] = $current[$key];
				continue;
			}
			$new_value = is_string($params[$key]) ? $params[$key] : '';
			if ($new_value !== '' && false !== strpos($new_value, '••')) {
				// Masked placeholder — keep stored key.
				$settings[$key] = $current[$key];
			} elseif ($new_value === '') {
				// Explicit clear.
				$settings[$key] = '';
			} else {
				$settings[$key] = $this->encrypt_key(sanitize_text_field($new_value));
			}
		}

		update_option(self::OPTION_KEY, $settings, false);

		return rest_ensure_response(array('success' => true));
	}

	/**
	 * Test API connection.
	 *
	 * @since  3.0.0
	 * @param  \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function test_connection(\WP_REST_Request $request)
	{
		$provider = sanitize_key($request->get_param('provider'));
		$settings = self::get_all_settings();

		$handler = new AI_API_Handler($settings);
		$result  = $handler->test_connection($provider);

		return rest_ensure_response($result);
	}

	public static function get_all_settings()
	{
		$defaults = array(
			'provider'         => 'openai',
			'openai_key'       => '',
			'openai_model'     => 'gpt-5-mini',
			'anthropic_key'    => '',
			'anthropic_model'  => 'claude-sonnet-4-6',
			'google_key'       => '',
			'google_model'     => 'gemini-2.5-flash',
			'openrouter_key'   => '',
			'openrouter_model' => 'openai/gpt-5-mini',
		);

		$saved = get_option(self::OPTION_KEY, array());

		// Decrypt stored keys.
		$settings = wp_parse_args($saved, $defaults);
		$instance = self::instance();

		foreach (array('openai_key', 'anthropic_key', 'google_key', 'openrouter_key') as $key) {
			if (! empty($settings[$key])) {
				$settings[$key] = $instance->decrypt_key($settings[$key]);
			}
		}

		return $settings;
	}

	/**
	 * Get available AI providers and models.
	 *
	 * Model Selection Rationale for Form Generation:
	 * ------------------------------------------------
	 * Form generation requires: precise instruction-following, valid shortcode syntax,
	 * fast response times, and cost-effectiveness. We avoid reasoning models (o1, o3)
	 * as they're slow and overkill for structured output tasks.
	 *
	 * @since  3.0.0
	 * @return array
	 */
	public static function get_providers()
	{
		return array(
			'openai'     => array(
				'name'            => 'OpenAI',
				'models'          => array(
					// GPT-5.4 family — latest flagship.
					'gpt-5.4'        => 'GPT-5.4',
					'gpt-5.4-mini'   => 'GPT-5.4 Mini',
					'gpt-5.4-nano'   => 'GPT-5.4 Nano',
					// GPT-5 family.
					'gpt-5'          => 'GPT-5',
					'gpt-5-mini'     => 'GPT-5 Mini (Recommended)',
					'gpt-5-nano'     => 'GPT-5 Nano',
					// GPT-4.1 family.
					'gpt-4.1'        => 'GPT-4.1',
					'gpt-4.1-mini'   => 'GPT-4.1 Mini',
					'gpt-4.1-nano'   => 'GPT-4.1 Nano',
					// 4o family.
					'gpt-4o'         => 'GPT-4o',
					'gpt-4o-mini'    => 'GPT-4o Mini',
				),
				'key_placeholder' => 'sk-...',
				'key_url'         => 'https://platform.openai.com/api-keys',
			),
			'anthropic'  => array(
				'name'            => 'Anthropic',
				'models'          => array(
					'claude-opus-4-6'   => 'Claude Opus 4.6',
					'claude-sonnet-4-6' => 'Claude Sonnet 4.6 (Recommended)',
					'claude-haiku-4-5'  => 'Claude Haiku 4.5 (Fast)',
				),
				'key_placeholder' => 'sk-ant-...',
				'key_url'         => 'https://console.anthropic.com/settings/keys',
			),
			'google'     => array(
				'name'            => 'Google',
				'models'          => array(
					// Gemini 3.1 preview line.
					'gemini-3.1-pro-preview'         => 'Gemini 3.1 Pro Preview',
					'gemini-3.1-flash-preview'       => 'Gemini 3.1 Flash Preview',
					'gemini-3.1-flash-lite-preview'  => 'Gemini 3.1 Flash Lite Preview',
					// Gemini 2.5 stable.
					'gemini-2.5-pro'                 => 'Gemini 2.5 Pro',
					'gemini-2.5-flash'               => 'Gemini 2.5 Flash (Recommended)',
					'gemini-2.5-flash-lite'          => 'Gemini 2.5 Flash Lite',
				),
				'key_placeholder' => 'AIza...',
				'key_url'         => 'https://aistudio.google.com/app/apikey',
			),
			'openrouter' => array(
				'name'            => 'OpenRouter',
				'models'          => array(
					// OpenAI through OpenRouter.
					'openai/gpt-5.4'                  => 'OpenAI · GPT-5.4',
					'openai/gpt-5.4-mini'             => 'OpenAI · GPT-5.4 Mini',
					'openai/gpt-5.4-nano'             => 'OpenAI · GPT-5.4 Nano',
					'openai/gpt-5'                    => 'OpenAI · GPT-5',
					'openai/gpt-5-mini'               => 'OpenAI · GPT-5 Mini (Recommended)',
					'openai/gpt-5-nano'               => 'OpenAI · GPT-5 Nano',
					'openai/gpt-4.1'                  => 'OpenAI · GPT-4.1',
					'openai/gpt-4.1-mini'             => 'OpenAI · GPT-4.1 Mini',
					'openai/gpt-4.1-nano'             => 'OpenAI · GPT-4.1 Nano',
					'openai/gpt-4o'                   => 'OpenAI · GPT-4o',
					'openai/gpt-4o-mini'              => 'OpenAI · GPT-4o Mini',
					// Anthropic through OpenRouter.
					'anthropic/claude-opus-4.6'       => 'Anthropic · Claude Opus 4.6',
					'anthropic/claude-sonnet-4.6'     => 'Anthropic · Claude Sonnet 4.6',
					'anthropic/claude-haiku-4.5'      => 'Anthropic · Claude Haiku 4.5',
					// Google through OpenRouter.
					'google/gemini-3.1-pro-preview'        => 'Google · Gemini 3.1 Pro Preview',
					'google/gemini-3.1-flash-preview'      => 'Google · Gemini 3.1 Flash Preview',
					'google/gemini-3.1-flash-lite-preview' => 'Google · Gemini 3.1 Flash Lite Preview',
					'google/gemini-2.5-pro'                => 'Google · Gemini 2.5 Pro',
					'google/gemini-2.5-flash'              => 'Google · Gemini 2.5 Flash',
					'google/gemini-2.5-flash-lite'         => 'Google · Gemini 2.5 Flash Lite',
					// DeepSeek.
					'deepseek/deepseek-v3.2'          => 'DeepSeek · DeepSeek V3.2',
				),
				'key_placeholder' => 'sk-or-...',
				'key_url'         => 'https://openrouter.ai/keys',
			),
		);
	}

	private function encrypt_key($key)
	{
		if (empty($key)) {
			return '';
		}

		$salt = wp_salt('auth');

		// Use OpenSSL if available.
		if (function_exists('openssl_encrypt')) {
			$iv     = substr(hash('sha256', $salt), 0, 16);
			$cipher = openssl_encrypt($key, 'AES-256-CBC', $salt, 0, $iv);
			return base64_encode('v1:' . $cipher);
		}

		// Fallback: simple encoding (not secure, but better than plain text).
		return base64_encode('v0:' . $key);
	}

	private function decrypt_key($encrypted)
	{
		if (empty($encrypted)) {
			return '';
		}

		$decoded = base64_decode($encrypted);

		// Check version prefix.
		if (0 === strpos($decoded, 'v1:') && function_exists('openssl_decrypt')) {
			$salt   = wp_salt('auth');
			$iv     = substr(hash('sha256', $salt), 0, 16);
			$cipher = substr($decoded, 3);
			$key    = openssl_decrypt($cipher, 'AES-256-CBC', $salt, 0, $iv);
			return false !== $key ? $key : '';
		}

		if (0 === strpos($decoded, 'v0:')) {
			return substr($decoded, 3);
		}

		// Legacy: unencrypted value.
		return $encrypted;
	}

	private function mask_key($key)
	{
		$len = strlen($key);
		if ($len <= 8) {
			return str_repeat('•', 8);
		}
		return substr($key, 0, 4) . str_repeat('•', 8) . substr($key, -4);
	}
}
