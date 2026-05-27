<?php
/**
 * Redirect Module — redirect visitors after a successful CF7 submission (Pro).
 *
 * Listens to the front-end wpcf7mailsent event in JS rather than performing a
 * server-side redirect, so it composes cleanly with AJAX submissions and
 * other client-side hooks (analytics, multi-step transitions, etc.).
 *
 * @package CF7_Mate\Features\Redirect
 * @since 3.1.0
 */

namespace CF7_Mate\Features\Redirect;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/editor-panel.php';

class Redirect extends Pro_Feature_Base {

	use Singleton;

	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		new Redirect_Editor_Panel();

		// Inject per-form config inside the rendered form HTML so multiple
		// CF7 forms on one page work independently.
		add_filter( 'wpcf7_form_elements', [ $this, 'inject_config' ], 50 );

		// Enqueue the redirect dispatcher.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );
	}

	/**
	 * Append a hidden <script type="application/json"> with the redirect config
	 * to the rendered form HTML. Per-form data, no globals.
	 */
	public function inject_config( $form_html ) {
		$cf7 = \WPCF7_ContactForm::get_current();
		if ( ! $cf7 ) {
			return $form_html;
		}

		$cfg = Redirect_Editor_Panel::get_config( (int) $cf7->id() );
		if ( empty( $cfg['enabled'] ) ) {
			return $form_html;
		}
		// Skip when there's nothing to redirect to and no rules either.
		if ( empty( $cfg['url'] ) && empty( $cfg['rules'] ) ) {
			return $form_html;
		}

		$payload = wp_json_encode( [
			'form_id'  => (int) $cf7->id(),
			'url'      => (string) $cfg['url'],
			'new_tab'  => ! empty( $cfg['new_tab'] ),
			'delay_ms' => (int) $cfg['delay_ms'],
			'rules'    => array_values( (array) $cfg['rules'] ),
		] );

		return $form_html
			. '<script type="application/json" class="cf7m-redirect-config">'
			. $payload
			. '</script>';
	}

	public function enqueue_script() {
		// Only enqueue when a CF7 form is on the page.
		if ( ! method_exists( '\CF7_Mate\Pro\Pro_Feature_Base', 'page_has_cf7_form' )
			|| ! \CF7_Mate\Pro\Pro_Feature_Base::page_has_cf7_form() ) {
			// Fall back to always enqueueing — page_has_cf7_form may not be
			// available in every code path. It's a 1 KB script.
		}

		$js = CF7M_PLUGIN_PATH . 'assets/pro/js/cf7m-redirect.js';
		if ( ! file_exists( $js ) ) {
			return;
		}
		$ver = ( defined( 'CF7M_VERSION' ) ? CF7M_VERSION : '3.0.0' ) . '.' . filemtime( $js );
		wp_enqueue_script(
			'cf7m-redirect',
			CF7M_PLUGIN_URL . 'assets/pro/js/cf7m-redirect.js',
			[],
			$ver,
			true
		);
	}
}
