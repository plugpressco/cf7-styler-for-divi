<?php

/**
 * AI Form Generator Module.
 *
 * Adds AI-powered form generation to Contact Form 7 editor.
 *
 * @package CF7_Mate\Lite\Features\AI_Form_Generator
 * @since   3.0.0
 */

namespace CF7_Mate\Lite\Features\AI_Form_Generator;

use CF7_Mate\Lite\Feature_Base;
use CF7_Mate\Lite\Traits\Singleton;

if (! defined('ABSPATH')) {
	exit;
}

// Load dependencies.
require_once __DIR__ . '/prompt.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/api-handler.php';

/**
 * Class AI_Form_Generator
 *
 * @since 3.0.0
 */
class AI_Form_Generator extends Feature_Base
{

	use Singleton;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	protected function __construct()
	{
		parent::__construct();
	}

	/**
	 * Initialize the feature.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	protected function init()
	{
		AI_Settings::instance();

		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('admin_footer', array($this, 'render_button'));
	}

	/**
	 * Render AI button in CF7 editor.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function render_button()
	{
		$screen = get_current_screen();

		if (! $screen || false === strpos($screen->id, 'wpcf7')) {
			return;
		}

		$text = esc_js(__('AI Generate', 'cf7-styler-for-divi'));
?>
		<script>
			(function() {
				// Only show button when form editor is on the page (edit mode), so Insert works.
				var formEditor = document.querySelector('#wpcf7-form');
				if (!formEditor) return;
				if (document.getElementById('cf7m-ai-btn')) return;

				// Insert after "Add Contact Form" / "Add New" link (same row as title), same size as that button.
				var wrap = document.querySelector('.wrap.contact-form-editor, #wpcf7-contact-form-editor .wrap, .wrap');
				var addNewLink = wrap ? wrap.querySelector('a.page-title-action') : null;
				var insertAfterEl = addNewLink || (wrap ? wrap.querySelector('h1') : null);
				if (!insertAfterEl || !insertAfterEl.parentNode) {
					insertAfterEl = formEditor;
				}

				var b = document.createElement('button');
				b.type = 'button';
				b.id = 'cf7m-ai-btn';
				b.className = 'cf7m-ai-btn page-title-action';
				b.innerHTML = '<span class="cf7m-ai-btn__icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg></span><span class="cf7m-ai-btn__text"><?php echo esc_html($text); ?></span>';
				insertAfterEl.parentNode.insertBefore(b, insertAfterEl.nextSibling);
			})();
		</script>
<?php
	}

	/**
	 * Enqueue modal assets.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function enqueue_assets()
	{
		$screen = get_current_screen();

		if (! $screen || false === strpos($screen->id, 'wpcf7')) {
			return;
		}

		$css_path = CF7M_PLUGIN_PATH . 'assets/pro/css/cf7m-ai-generator.css';
		$js_path  = CF7M_PLUGIN_PATH . 'assets/pro/js/cf7m-ai-generator.js';
		$css_ver  = CF7M_VERSION . ( file_exists( $css_path ) ? '.' . filemtime( $css_path ) : '' );
		$js_ver   = CF7M_VERSION . ( file_exists( $js_path ) ? '.' . filemtime( $js_path ) : '' );

		wp_enqueue_style(
			'cf7m-ai-generator',
			CF7M_PLUGIN_URL . 'assets/pro/css/cf7m-ai-generator.css',
			array(),
			$css_ver
		);

		wp_enqueue_script(
			'cf7m-ai-generator',
			CF7M_PLUGIN_URL . 'assets/pro/js/cf7m-ai-generator.js',
			array('jquery'),
			$js_ver,
			true
		);

		$settings = AI_Settings::get_all_settings();
		$provider = $settings['provider'];
		$has_key  = ! empty($settings[$provider . '_key']);
		$providers = AI_Settings::get_providers();

		// Get presets for one-click generation.
		$presets = function_exists('cf7m_get_ai_presets') ? cf7m_get_ai_presets() : array();

		$model_field = $provider . '_model';
		$model_id    = $settings[$model_field] ?? '';
		$model_label = $providers[$provider]['models'][$model_id] ?? $model_id;

		wp_localize_script(
			'cf7m-ai-generator',
			'cf7mAI',
			array(
				'generateUrl'     => esc_url_raw(rest_url('cf7-styler/v1/ai-generate')),
				'settingsUrl'     => esc_url(admin_url('admin.php?page=cf7-mate#/ai-settings')),
				'dashUrl'         => esc_url(admin_url('admin.php?page=cf7-mate#/ai-settings')),
				'nonce'           => wp_create_nonce('wp_rest'),
				'hasApiKey'       => $has_key,
				'provider'        => $providers[$provider]['name'] ?? 'AI',
				'model'           => $model_label,
				'presets'         => $presets,
				'categoryLabels'  => array(
					'lead-contact' => __('Lead & contact', 'cf7-styler-for-divi'),
					'booking'      => __('Booking', 'cf7-styler-for-divi'),
				),
				'strings'         => array(
					'title'       => __('AI Form Generator', 'cf7-styler-for-divi'),
					'presets'     => __('Quick presets', 'cf7-styler-for-divi'),
					'custom'      => __('Describe your form', 'cf7-styler-for-divi'),
					'generate'    => __('Generate', 'cf7-styler-for-divi'),
					'regenerate'  => __('Regenerate', 'cf7-styler-for-divi'),
					'insert'      => __('Insert', 'cf7-styler-for-divi'),
					'insertEdited' => __('Insert edited form', 'cf7-styler-for-divi'),
					'copy'        => __('Copy', 'cf7-styler-for-divi'),
					'generating'  => __('Generating…', 'cf7-styler-for-divi'),
					'error'       => __('Error generating form.', 'cf7-styler-for-divi'),
					'copied'      => __('Copied!', 'cf7-styler-for-divi'),
					'configure'   => __('Configure', 'cf7-styler-for-divi'),
					'noKey'       => __('Configure AI provider first.', 'cf7-styler-for-divi'),
					'change'      => __('Change', 'cf7-styler-for-divi'),
					'shortcut'    => __('⌘ + Enter to generate', 'cf7-styler-for-divi'),
					'placeholder' => __('Describe the form you want to create, or pick a preset below…', 'cf7-styler-for-divi'),
					'dropImage'   => __('Drop an image here, or click to upload', 'cf7-styler-for-divi'),
					'removeImage' => __('Remove image', 'cf7-styler-for-divi'),
					'emptyError'  => __('Please describe the form, pick a preset, or upload an image.', 'cf7-styler-for-divi'),
					'noEditor'    => __('Form editor not found.', 'cf7-styler-for-divi'),
				),
			)
		);
	}

	/**
	 * Register REST API routes.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function register_routes()
	{
		register_rest_route(
			'cf7-styler/v1',
			'/ai-generate',
			array(
				'methods'             => 'POST',
				'callback'            => array($this, 'handle_generate'),
				'permission_callback' => function () {
					return current_user_can('wpcf7_edit_contact_forms');
				},
			)
		);
	}


	public function handle_generate(\WP_REST_Request $request)
	{
		$prompt = sanitize_textarea_field($request->get_param('prompt'));
		$image  = $request->get_param('image');
		$image_type = sanitize_text_field($request->get_param('image_type') ?: 'image/jpeg');

		if (empty($prompt) && empty($image)) {
			return new \WP_Error(
				'empty_prompt',
				__('Please describe the form or upload an image.', 'cf7-styler-for-divi'),
				array('status' => 400)
			);
		}

		if (!empty($image) && !preg_match('/^[A-Za-z0-9+\/=]+$/', $image)) {
			return new \WP_Error(
				'invalid_image',
				__('Invalid image data.', 'cf7-styler-for-divi'),
				array('status' => 400)
			);
		}

		if (empty($prompt) && !empty($image)) {
			$prompt = __('Convert this form design or screenshot into valid Contact Form 7 form code. Output only the form code.', 'cf7-styler-for-divi');
		}

		$handler = new AI_API_Handler();
		$result  = $handler->generate($prompt, !empty($image) ? $image : null, $image_type);

		if (is_wp_error($result)) {
			return $result;
		}

		$form = $this->clean_response($result);

		return rest_ensure_response(
			array(
				'success' => true,
				'form'    => $form,
			)
		);
	}

	/**
	 * Clean AI response to extract form code.
	 * Strips markdown, stray headings; fixes [label] so shortcodes render.
	 *
	 * @since  3.0.0
	 * @param  string $response Raw AI response.
	 * @return string Cleaned form code.
	 */
	private function clean_response($response)
	{
		// Remove markdown code blocks.
		$response = preg_replace('/^```[a-z]*\n?/m', '', $response);
		$response = preg_replace('/\n?```$/m', '', $response);

		// Strip stray headings/title markup (uncanny titles) so form starts with label or shortcode.
		$response = preg_replace('/^\s*<h[1-6][^>]*>.*?<\/h[1-6]>\s*/is', '', $response);
		$response = preg_replace('/^\s*<title[^>]*>.*?<\/title>\s*/is', '', $response);
		$response = preg_replace('/^\s*<p[^>]*class="[^"]*title[^"]*"[^>]*>.*?<\/p>\s*/is', '', $response);

		// Fix [label for="..."]...[/label] → <label for="...">...</label> so it renders (AI sometimes outputs square brackets).
		$response = preg_replace_callback(
			'/\[label\s+for=["\']([^"\']+)["\'][^\]]*\]\s*(.*?)\s*\[\/label\]/is',
			function ($m) {
				return '<label for="' . esc_attr($m[1]) . '">' . esc_html(trim($m[2])) . '</label>';
			},
			$response
		);

		return trim($response);
	}
}
