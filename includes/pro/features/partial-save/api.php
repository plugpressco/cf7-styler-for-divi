<?php
/**
 * Partial Save REST API – save, retrieve, and delete partial form data.
 * Uses WordPress transients keyed by a client-generated 32-char hex token.
 *
 * @package CF7_Mate\Features\Partial_Save
 * @since 4.0.0
 */

namespace CF7_Mate\Features\Partial_Save;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Partial_Save_API {

	const REST_NAMESPACE = 'cf7-styler/v1';
	const TRANSIENT_PREFIX = 'cf7m_partial_';
	const EXPIRY = 604800; // 7 days

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/partial-save',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_partial' ],
				'permission_callback' => [ $this, 'check_nonce' ],
				'args'                => [
					'form_id' => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
					'token' => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => [ $this, 'validate_token' ],
					],
					'data' => [
						'required' => true,
						'type'     => 'object',
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/partial-save/(?P<token>[a-f0-9]{32})',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_partial' ],
					'permission_callback' => [ $this, 'check_nonce' ],
					'args'                => [
						'token' => [
							'validate_callback' => [ $this, 'validate_token' ],
						],
					],
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_partial' ],
					'permission_callback' => [ $this, 'check_nonce' ],
					'args'                => [
						'token' => [
							'validate_callback' => [ $this, 'validate_token' ],
						],
					],
				],
			]
		);
	}

	public function check_nonce( \WP_REST_Request $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' ) ?: $request->get_param( '_wpnonce' ) ?: '';
		return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
	}

	public function validate_token( $token ): bool {
		return is_string( $token ) && (bool) preg_match( '/^[a-f0-9]{32}$/D', $token );
	}

	public function save_partial( \WP_REST_Request $request ) {
		$form_id = (int) $request->get_param( 'form_id' );
		$token   = $request->get_param( 'token' );
		$data    = $request->get_param( 'data' );

		if ( ! $this->is_valid_form( $form_id ) || ! Partial_Save_Editor_Panel::is_enabled_for_form( $form_id ) ) {
			return new \WP_Error( 'invalid_form', __( 'Invalid form.', 'cf7-styler-for-divi' ), [ 'status' => 400 ] );
		}

        if (!is_array($data) || count($data) > 100 || strlen(wp_json_encode($data)) > 65536) {
            return new \WP_Error('invalid_data', __('Form progress is too large.', 'cf7-styler-for-divi'), ['status' => 400]);
        }
        $existing = get_transient(self::TRANSIENT_PREFIX . $token);
        if ($existing && (int) $existing['form_id'] !== $form_id) {
            return new \WP_Error('invalid_token', __('This progress token belongs to another form.', 'cf7-styler-for-divi'), ['status' => 400]);
        }
        $remote = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $rate_key = 'cf7m_ps_rate_' . hash('sha256', $remote);
        $count = (int) get_transient($rate_key);
        if ($count >= 30) {
            return new \WP_Error('rate_limited', __('Please wait before saving again.', 'cf7-styler-for-divi'), ['status' => 429]);
        }
        $clean = [];
        foreach ($data as $key => $value) {
            $key = preg_replace('/\[\]$/', '', (string) $key);
            if (!wpcf7_is_name($key) || strpos($key, '_') === 0) {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (!is_scalar($item)) {
                        return new \WP_Error('invalid_data', __('Invalid field value.', 'cf7-styler-for-divi'), ['status' => 400]);
                    }
                }
                $clean[$key] = array_map('sanitize_text_field', $value);
            } elseif (is_scalar($value)) {
                $clean[$key] = sanitize_textarea_field((string) $value);
            } else {
                return new \WP_Error('invalid_data', __('Invalid field value.', 'cf7-styler-for-divi'), ['status' => 400]);
            }
        }
        set_transient($rate_key, $count + 1, MINUTE_IN_SECONDS);

		$payload = [
			'form_id'  => $form_id,
			'data'     => $clean,
			'saved_at' => time(),
		];

		set_transient( self::TRANSIENT_PREFIX . $token, $payload, self::EXPIRY );

		return rest_ensure_response( [ 'success' => true ] );
	}

	public function get_partial( \WP_REST_Request $request ) {
		$token   = $request['token'];
		$payload = get_transient( self::TRANSIENT_PREFIX . $token );

		if ( ! $payload || ! $this->is_valid_form( (int) $payload['form_id'] ) || ! Partial_Save_Editor_Panel::is_enabled_for_form( (int) $payload['form_id'] ) ) {
			return rest_ensure_response( [ 'found' => false, 'data' => [] ] );
		}

		return rest_ensure_response( [
			'found'    => true,
			'form_id'  => $payload['form_id'],
			'data'     => $payload['data'],
			'saved_at' => $payload['saved_at'],
		] );
	}

	public function delete_partial( \WP_REST_Request $request ) {
		$token = $request['token'];
		delete_transient( self::TRANSIENT_PREFIX . $token );
		return rest_ensure_response( [ 'success' => true ] );
	}

	private function is_valid_form( int $form_id ): bool {
		$post = get_post( $form_id );
		return $post && $post->post_type === 'wpcf7_contact_form';
	}
}
