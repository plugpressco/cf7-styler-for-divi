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
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/onboarding/complete',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'complete_onboarding' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
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
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_webhook_settings' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
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
	 * Get features settings.
	 *
	 * @since  3.0.0
	 * @return \WP_REST_Response
	 */
	public function get_features() {
		$features = \CF7_Mate\Feature_Settings::get();

		return rest_ensure_response(
			array(
				'features' => $features,
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

		$sanitized = \CF7_Mate\Feature_Settings::save( $features );

		return rest_ensure_response(
			array(
				'success'  => true,
				'features' => $sanitized,
			)
		);
	}

	/**
	 * Get webhook settings. Returns list of webhook URLs.
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
		return \CF7_Mate\Feature_Settings::defaults();
	}

	/**
	 * Check if feature is enabled.
	 *
	 * @since  3.0.0
	 * @param  string $feature Feature key.
	 * @return bool
	 */
	public static function is_feature_enabled( $feature ) {
		return \CF7_Mate\Feature_Settings::enabled( $feature );
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

}

Rest_API::instance();
