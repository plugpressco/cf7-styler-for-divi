<?php
/**
 * REST API Endpoints.
 *
 * @package CF7_Mate\API
 * @since   3.0.0
 */

namespace CF7_Mate\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Rest_API
 *
 * @since 3.0.0
 */
class Rest_API {

	/**
	 * Instance.
	 *
	 * @var Rest_API|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @since  3.0.0
	 * @return Rest_API
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function register_routes() {
		$namespace = 'cf7-styler/v1';

		register_rest_route(
			$namespace,
			'/forms',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cf7_forms' ),
				'permission_callback' => array( $this, 'check_edit_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/onboarding/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_onboarding_status' ),
				'permission_callback' => array( $this, 'check_edit_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/onboarding/complete',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'complete_onboarding' ),
				'permission_callback' => array( $this, 'check_edit_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/settings/features',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_features' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_features' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/dashboard-stats',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_dashboard_stats' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/form-preview',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_form_preview' ),
				'permission_callback' => array( $this, 'check_edit_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => function ( $value ) {
							return is_numeric( $value ) && $value > 0;
						},
					),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/settings/webhook',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_webhook_settings' ),
					'permission_callback' => array( $this, 'check_pro_permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_webhook_settings' ),
					'permission_callback' => array( $this, 'check_pro_permission' ),
				),
			)
		);

		// White label endpoint (Agency plan only)
		register_rest_route(
			$namespace,
			'/settings/white-label',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_white_label' ),
					'permission_callback' => array( $this, 'check_agency_permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_white_label' ),
					'permission_callback' => array( $this, 'check_agency_permission' ),
					'args'                => array(
						'enabled'     => array( 'type' => 'boolean' ),
						'plugin_name' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
						'logo_url'    => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
						'docs_url'    => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
						'support_url' => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
					),
				),
			)
		);

		// License endpoints
		register_rest_route(
			$namespace,
			'/license/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_license_status' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/license/activate',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'activate_license' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'license_key' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function( $value ) {
							return ! empty( trim( $value ) );
						},
					),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/license/deactivate',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'deactivate_license' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/license/validate',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'validate_license' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);
	}

	/**
	 * Check edit permission.
	 *
	 * @since  3.0.0
	 * @return bool
	 */
	public function check_edit_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check admin permission.
	 *
	 * @since  3.0.0
	 * @return bool
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check admin permission AND valid pro license.
	 * Used for pro-only endpoints (webhook, AI settings, etc.).
	 *
	 * @since  3.0.0
	 * @return bool|\WP_Error
	 */
	public function check_pro_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( ! function_exists( 'cf7m_is_pro' ) || ! cf7m_is_pro() ) {
			return new \WP_Error(
				'cf7m_pro_required',
				__( 'A valid CF7 Mate Pro license is required.', 'cf7-styler-for-divi' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Get features settings.
	 *
	 * @since  3.0.0
	 * @return \WP_REST_Response
	 */
	public function get_features() {
		$defaults = self::get_default_features();
		$saved    = get_option( 'cf7m_features', array() );
		$features = wp_parse_args( $saved, $defaults );

		return rest_ensure_response(
			array(
				'features' => $features,
				'is_pro'   => function_exists( 'cf7m_is_pro' ) && cf7m_is_pro(),
			)
		);
	}

	/**
	 * Save features settings.
	 *
	 * @since  3.0.0
	 * @param  \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function save_features( \WP_REST_Request $request ) {
		$features = $request->get_param( 'features' );

		if ( ! is_array( $features ) ) {
			return new \WP_Error(
				'invalid_data',
				__( 'Invalid features data.', 'cf7-styler-for-divi' ),
				array( 'status' => 400 )
			);
		}

		$defaults  = self::get_default_features();
		$sanitized = array();

		foreach ( $defaults as $key => $default ) {
			$sanitized[ $key ] = isset( $features[ $key ] ) ? (bool) $features[ $key ] : $default;
		}

		update_option( 'cf7m_features', $sanitized, false );

		return rest_ensure_response(
			array(
				'success'  => true,
				'features' => $sanitized,
			)
		);
	}

	/**
	 * Get webhook settings (Pro). Returns list of webhook URLs.
	 *
	 * @since  3.0.0
	 * @return \WP_REST_Response
	 */
	public function get_webhook_settings() {
		$urls = get_option( 'cf7m_webhook_urls', array() );
		if ( ! is_array( $urls ) ) {
			$urls = array();
		}
		return rest_ensure_response( array( 'urls' => array_values( array_filter( $urls, 'is_string' ) ) ) );
	}

	/**
	 * Save webhook settings. Expects { urls: string[] }.
	 *
	 * @since  3.0.0
	 * @param  \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function save_webhook_settings( \WP_REST_Request $request ) {
		$urls = $request->get_param( 'urls' );
		if ( ! is_array( $urls ) ) {
			return new \WP_Error(
				'invalid_data',
				__( 'Invalid webhook URLs.', 'cf7-styler-for-divi' ),
				array( 'status' => 400 )
			);
		}
		$sanitized = array();
		foreach ( $urls as $url ) {
			$url = is_string( $url ) ? trim( $url ) : '';
			if ( $url === '' ) {
				continue;
			}
			if ( wp_http_validate_url( $url ) ) {
				$sanitized[] = $url;
			}
		}
		update_option( 'cf7m_webhook_urls', $sanitized, false );
		return rest_ensure_response( array( 'success' => true, 'urls' => $sanitized ) );
	}

	/**
	 * Get dashboard stats.
	 *
	 * @since  3.0.0
	 * @return \WP_REST_Response
	 */
	public function get_dashboard_stats() {
		$total_entries = 0;
		$new_today     = 0;

		if ( post_type_exists( 'cf7m_entry' ) ) {
			$total_query = new \WP_Query(
				array(
					'post_type'      => 'cf7m_entry',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'no_found_rows'  => false,
				)
			);
			$total_entries = (int) $total_query->found_posts;
			wp_reset_postdata();

			$today_start = wp_date( 'Y-m-d 00:00:00' );
			$today_end   = wp_date( 'Y-m-d 23:59:59' );

			$new_query = new \WP_Query(
				array(
					'post_type'      => 'cf7m_entry',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'date_query'     => array(
						array(
							'after'     => $today_start,
							'before'    => $today_end,
							'inclusive' => true,
						),
					),
					'fields'         => 'ids',
					'no_found_rows'  => false,
				)
			);
			$new_today = (int) $new_query->found_posts;
			wp_reset_postdata();
		}

		$total_forms = 0;
		if ( post_type_exists( 'wpcf7_contact_form' ) ) {
			$forms_query = new \WP_Query(
				array(
					'post_type'      => 'wpcf7_contact_form',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				)
			);
			$total_forms = count( $forms_query->posts );
			wp_reset_postdata();
		}

		$defaults = self::get_default_features();
		$saved    = get_option( 'cf7m_features', array() );
		$features = wp_parse_args( $saved, $defaults );

		$enabled_features = 0;
		foreach ( $features as $enabled ) {
			if ( $enabled ) {
				++$enabled_features;
			}
		}

		return rest_ensure_response(
			array(
				'total_entries'    => $total_entries,
				'new_today'        => $new_today,
				'total_forms'      => $total_forms,
				'enabled_features' => $enabled_features,
			)
		);
	}

	/**
	 * Get form preview HTML.
	 *
	 * @since  3.0.0
	 * @param  \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_form_preview( \WP_REST_Request $request ) {
		$form_id = absint( $request->get_param( 'id' ) );

		if ( ! $form_id ) {
			return new \WP_Error(
				'invalid_id',
				__( 'Invalid form ID.', 'cf7-styler-for-divi' ),
				array( 'status' => 400 )
			);
		}

		// Verify form exists.
		$form = get_post( $form_id );
		if ( ! $form || 'wpcf7_contact_form' !== $form->post_type ) {
			return new \WP_Error(
				'form_not_found',
				__( 'Form not found.', 'cf7-styler-for-divi' ),
				array( 'status' => 404 )
			);
		}

		$html = do_shortcode( sprintf( '[contact-form-7 id="%d"]', $form_id ) );

		// Strip any unprocessed [cf7m-presets] wrapper tags (pro-only shortcode).
		// When the pro module is inactive the tags pass through CF7 as literal text,
		// which would break the editor preview.
		if ( strpos( $html, '[cf7m-presets' ) !== false || strpos( $html, '[/cf7m-presets]' ) !== false ) {
			$html = preg_replace( '/\[cf7m-presets[^\]]*\]|\[\/cf7m-presets\]/i', '', $html );
		}

		return rest_ensure_response(
			array(
				'html' => $html,
			)
		);
	}

	/**
	 * Get default features.
	 *
	 * @since  3.0.0
	 * @return array
	 */
	public static function get_default_features() {
		return array(
			'cf7_module'        => true,
			'bricks_module'     => true,
			'elementor_module'  => true,
			'gutenberg_module'  => true,
			'grid_layout'       => true,
			'multi_column'      => true,
			'multi_step'        => true,
			'star_rating'       => true,
			'database_entries'  => true,
			'range_slider'      => true,
			'phone_number'      => true,
			'separator'         => true,
			'heading'           => true,
			'image'             => true,
			'icon'              => true,
			'calculator'        => true,
			'conditional'       => true,
			'ai_form_generator' => true,
			'presets'           => true,
			'webhook'           => true,
			'analytics'         => true,
			'form_scheduling'   => true,
			'email_routing'     => true,
			'partial_save'      => false,
		);
	}

	/**
	 * Check if feature is enabled.
	 *
	 * @since  3.0.0
	 * @param  string $feature Feature key.
	 * @return bool
	 */
	public static function is_feature_enabled( $feature ) {
		$defaults = self::get_default_features();
		$saved    = get_option( 'cf7m_features', array() );
		$features = wp_parse_args( $saved, $defaults );

		return isset( $features[ $feature ] ) ? (bool) $features[ $feature ] : false;
	}

	/**
	 * Get CF7 forms.
	 *
	 * @since  3.0.0
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_cf7_forms() {
		if ( ! function_exists( 'wpcf7' ) ) {
			return new \WP_Error(
				'cf7_not_installed',
				__( 'Contact Form 7 is not installed.', 'cf7-styler-for-divi' ),
				array( 'status' => 404 )
			);
		}

		$forms = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$form_list = array(
			array(
				'value' => '0',
				'label' => __( 'Select a form', 'cf7-styler-for-divi' ),
			),
		);

		foreach ( $forms as $form ) {
			$form_list[] = array(
				'value' => (string) $form->ID,
				'label' => $form->post_title ? esc_html( $form->post_title ) : sprintf(
					/* translators: %d: form ID */
					__( 'Form #%d', 'cf7-styler-for-divi' ),
					$form->ID
				),
			);
		}

		return rest_ensure_response( $form_list );
	}

	/**
	 * Get onboarding status.
	 *
	 * @since  3.0.0
	 * @return \WP_REST_Response
	 */
	public function get_onboarding_status() {
		$is_completed = '1' === get_option( 'cf7m_onboarding_completed', '' );
		$is_skipped   = '1' === get_option( 'cf7m_onboarding_skipped', '' );
		$should_show  = ! $is_completed && $is_skipped;

		return rest_ensure_response(
			array(
				'is_completed'       => $is_completed,
				'is_skipped'         => $is_skipped,
				'should_show_notice' => $should_show,
			)
		);
	}

	/**
	 * Complete onboarding.
	 *
	 * @since  3.0.0
	 * @return \WP_REST_Response
	 */
	public function complete_onboarding() {
		delete_option( 'cf7m_onboarding_skipped' );
		delete_option( 'cf7m_onboarding_completed' );
		update_option( 'cf7m_onboarding_step', 1, false );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Onboarding reset successfully.', 'cf7-styler-for-divi' ),
			)
		);
	}

	/**
	 * Get license status.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_license_status() {
		if ( ! class_exists( 'CF7_Mate\License\License_Manager' ) ) {
			return rest_ensure_response(
				array(
					'status'     => '',
					'is_valid'   => false,
					'has_key'    => false,
					'masked_key' => '',
					'expires_at' => '',
				)
			);
		}

		$lm = \CF7_Mate\License\License_Manager::instance();
		return rest_ensure_response( $lm->get_status() );
	}

	/**
	 * Activate a license.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function activate_license( \WP_REST_Request $request ) {
		$key = $request->get_param( 'license_key' );

		if ( ! class_exists( 'CF7_Mate\License\License_Manager' ) ) {
			return new \WP_Error(
				'license_not_available',
				__( 'License manager is not available.', 'cf7-styler-for-divi' ),
				array( 'status' => 400 )
			);
		}

		$lm     = \CF7_Mate\License\License_Manager::instance();
		$result = $lm->activate( $key );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return rest_ensure_response( array_merge( array( 'success' => true ), $result ) );
	}

	/**
	 * Deactivate the current license.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function deactivate_license() {
		if ( ! class_exists( 'CF7_Mate\License\License_Manager' ) ) {
			return new \WP_Error(
				'license_not_available',
				__( 'License manager is not available.', 'cf7-styler-for-divi' ),
				array( 'status' => 400 )
			);
		}

		$lm     = \CF7_Mate\License\License_Manager::instance();
		$result = $lm->deactivate();

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Re-validate the current license against Lemon Squeezy on demand.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function validate_license() {
		if ( ! class_exists( 'CF7_Mate\License\License_Manager' ) ) {
			return new \WP_Error(
				'license_not_available',
				__( 'License manager is not available.', 'cf7-styler-for-divi' ),
				array( 'status' => 400 )
			);
		}

		$lm    = \CF7_Mate\License\License_Manager::instance();
		$valid = $lm->validate();

		return rest_ensure_response(
			array_merge(
				array( 'success' => true, 'is_valid' => (bool) $valid ),
				$lm->get_status()
			)
		);
	}

	/**
	 * Check permission for Agency-plan-only endpoints.
	 *
	 * @return bool|\WP_Error
	 */
	public function check_agency_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( ! class_exists( 'CF7_Mate\Pro\White_Label' )
			|| ! \CF7_Mate\Pro\White_Label::is_agency_plan() ) {
			return new \WP_Error(
				'cf7m_agency_required',
				__( 'An Agency plan license is required.', 'cf7-styler-for-divi' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * GET /settings/white-label — return current white label settings.
	 */
	public function get_white_label(): \WP_REST_Response {
		return rest_ensure_response( \CF7_Mate\Pro\White_Label::get() );
	}

	/**
	 * POST /settings/white-label — save white label settings.
	 */
	public function save_white_label( \WP_REST_Request $request ): \WP_REST_Response {
		$saved = \CF7_Mate\Pro\White_Label::save( $request->get_params() );
		return rest_ensure_response( array( 'success' => true, 'settings' => $saved ) );
	}
}

Rest_API::instance();
