<?php
/**
 * Analytics Module – track form views and submission conversion rates (Pro).
 *
 * @package CF7_Mate\Features\Analytics
 * @since 4.0.0
 */

namespace CF7_Mate\Features\Analytics;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Analytics extends Pro_Feature_Base {

	use Singleton;

	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		require_once __DIR__ . '/api.php';
		new Analytics_API();

		add_action( 'wpcf7_enqueue_scripts', [ $this, 'enqueue_beacon' ] );
	}

	/**
	 * Enqueue the tiny view-tracking beacon on pages that have a CF7 form.
	 */
	public function enqueue_beacon() {
		$path = CF7M_PLUGIN_PATH . 'assets/pro/js/cf7m-analytics-beacon.js';
		if ( ! file_exists( $path ) ) {
			return;
		}

		wp_enqueue_script(
			'cf7m-analytics-beacon',
			CF7M_PLUGIN_URL . 'assets/pro/js/cf7m-analytics-beacon.js',
			[],
			CF7M_VERSION,
			true
		);

		wp_localize_script(
			'cf7m-analytics-beacon',
			'cf7mAnalytics',
			[
				'url'   => rest_url( 'cf7-styler/v1/analytics/view' ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			]
		);
	}
}
