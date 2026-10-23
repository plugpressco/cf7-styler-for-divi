<?php
/**
 * Analytics REST API – view tracking and stats endpoint.
 *
 * @package CF7_Mate\Features\Analytics
 * @since 4.0.0
 */

namespace CF7_Mate\Features\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Analytics_API {

	const REST_NAMESPACE = 'cf7-styler/v1';
	const OPTION_PREFIX  = 'cf7m_views_';

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/analytics/view',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'track_view' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'form_id' => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => function ( $v ) {
							return is_numeric( $v ) && $v > 0;
						},
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/analytics/stats',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_stats' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'days' => [
						'type'              => 'integer',
						'default'           => 0,
						'minimum'           => 0,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Increment the view counter for a form.
	 * Silently ignores invalid form IDs to prevent abuse.
	 */
	public function track_view( \WP_REST_Request $request ) {
		$form_id = (int) $request->get_param( 'form_id' );

		// Validate nonce (best-effort; don't hard-block to allow caching layers).
		$nonce = $request->get_header( 'X-WP-Nonce' ) ?: '';
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return rest_ensure_response( [ 'ok' => false ] );
		}

		// Validate form exists as a CF7 contact form.
		if ( ! $this->is_valid_form( $form_id ) ) {
			return rest_ensure_response( [ 'ok' => false ] );
		}

		$key   = self::OPTION_PREFIX . $form_id;
		$count = (int) get_option( $key, 0 );
		update_option( $key, $count + 1, false );

		return rest_ensure_response( [ 'ok' => true ] );
	}

	/**
	 * Return analytics stats for all CF7 forms.
	 *
	 * @param \WP_REST_Request $request
	 */
	public function get_stats( \WP_REST_Request $request ) {
		$after = ''; // View counters and saved-submission counts both cover all time.

		$forms = get_posts( [
			'post_type'      => 'wpcf7_contact_form',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		$rows = [];
		foreach ( $forms as $form ) {
			$fid         = (int) $form->ID;
			$views       = (int) get_option( self::OPTION_PREFIX . $fid, 0 );
			$submissions = $this->get_submission_count( $fid, $after );
			$rate        = $views > 0 ? round( ( $submissions / $views ) * 100, 1 ) : 0.0;
			$last        = $this->get_last_submission_date( $fid );

			$rows[] = [
				'form_id'         => $fid,
				'form_title'      => $form->post_title,
				'views'           => $views,
				'submissions'     => $submissions,
				'conversion'      => $rate,
				'last_submission' => $last,
			];
		}

		return rest_ensure_response( $rows );
	}

	private function is_valid_form( int $form_id ): bool {
		$post = get_post( $form_id );
		return $post && $post->post_type === 'wpcf7_contact_form';
	}

	private function get_submission_count( int $form_id, string $after = '' ): int {
		if ( ! post_type_exists( 'cf7m_entry' ) ) {
			return 0;
		}
		$args = [
			'post_type'      => 'cf7m_entry',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
			'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery
				[
					'key'     => '_cf7m_form_id',
					'value'   => $form_id,
					'compare' => '=',
				],
				[
					'key'     => '_cf7m_status',
					'value'   => [ 'trash', 'spam' ],
					'compare' => 'NOT IN',
				],
			],
		];
		if ( $after ) {
			$args['date_query'] = [ [ 'after' => $after, 'inclusive' => true ] ];
		}
		$q = new \WP_Query( $args );
		return (int) $q->found_posts;
	}

	private function get_last_submission_date( int $form_id ): string {
		if ( ! post_type_exists( 'cf7m_entry' ) ) {
			return '';
		}
		$posts = get_posts( [
			'post_type'      => 'cf7m_entry',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
			'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery
				[
					'key'     => '_cf7m_form_id',
					'value'   => $form_id,
					'compare' => '=',
				],
			],
		] );
		if ( empty( $posts ) ) {
			return '';
		}
		return (string) get_post_meta( $posts[0], '_cf7m_created', true );
	}
}
