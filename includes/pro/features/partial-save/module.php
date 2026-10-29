<?php
/**
 * Partial Save Module – save form progress to transients, restore on return.
 *
 * @package CF7_Mate\Features\Partial_Save
 * @since 4.0.0
 */

namespace CF7_Mate\Features\Partial_Save;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Partial_Save extends Pro_Feature_Base {

	use Singleton;

	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		require_once __DIR__ . '/editor-panel.php';
		require_once __DIR__ . '/api.php';

		new Partial_Save_Editor_Panel();
		new Partial_Save_API();

		// Inject "Save progress" button and localized data into forms that have it enabled.
		add_filter( 'wpcf7_form_elements', [ $this, 'inject_save_button' ], 25 );

		// Clear the transient when the form is successfully submitted.
		add_action( 'wpcf7_before_send_mail', [ $this, 'clear_on_submit' ], 3 );

		// Enqueue frontend JS.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );
	}

	/**
	 * Enqueue the save/restore JS on pages that have CF7 forms.
	 */
	public function enqueue_scripts() {
		if ( ! $this->page_has_cf7_form() ) {
			return;
		}

		$path = CF7M_PLUGIN_PATH . 'assets/pro/js/cf7m-partial-save.js';
		if ( ! file_exists( $path ) ) {
			return;
		}

		wp_enqueue_script(
			'cf7m-partial-save',
			CF7M_PLUGIN_URL . 'assets/pro/js/cf7m-partial-save.js',
			[],
			CF7M_VERSION,
			true
		);

		wp_localize_script(
			'cf7m-partial-save',
			'cf7mPartialSave',
			[
				'root'  => rest_url( 'cf7-styler/v1' ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'i18n'  => [
					'save'     => __( 'Save progress', 'cf7-styler-for-divi' ),
					'saved'    => __( 'Progress saved', 'cf7-styler-for-divi' ),
					'restored' => __( 'Progress restored', 'cf7-styler-for-divi' ),
					'saving'   => __( 'Saving…', 'cf7-styler-for-divi' ),
					'error'    => __( 'Could not save progress.', 'cf7-styler-for-divi' ),
				],
			]
		);
	}

	/**
	 * Append the "Save progress" bar to forms that have partial save enabled.
	 *
	 * @param string $form Rendered form HTML.
	 * @return string
	 */
	public function inject_save_button( $form ) {
		$cf7 = \WPCF7_ContactForm::get_current();
		if ( ! $cf7 || ! $cf7->id() ) {
			return $form;
		}

		if ( ! Partial_Save_Editor_Panel::is_enabled_for_form( $cf7->id() ) ) {
			return $form;
		}

		$bar = sprintf(
			'<div class="cf7m-partial-save-bar" data-form-id="%d">'
			. '<button type="button" class="cf7m-partial-save__btn">%s</button>'
			. '<span class="cf7m-partial-save__status" aria-live="polite"></span>'
			. '</div>',
			esc_attr( $cf7->id() ),
			esc_html__( 'Save progress', 'cf7-styler-for-divi' )
		);

		return $form . $bar;
	}

	/**
	 * Delete the transient after a successful submission.
	 * Reads the token from the posted data if present.
	 *
	 * @param \WPCF7_ContactForm $contact_form
	 */
	public function clear_on_submit( $contact_form ) {
		$submission = \WPCF7_Submission::get_instance();
		if ( ! $submission ) {
			return;
		}

		if ( ! Partial_Save_Editor_Panel::is_enabled_for_form( $contact_form->id() ) ) {
			return;
		}

		// Contact Form 7 leaves fields starting with "_" out of the posted data.
		// Its front-end submissions carry no nonce; a token only deletes its own progress.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$token = isset( $_POST['_cf7m_ps_token'] ) ? sanitize_text_field( wp_unslash( $_POST['_cf7m_ps_token'] ) ) : '';

		if ( $token && strlen( $token ) === 32 && ctype_xdigit( $token ) ) {
			delete_transient( Partial_Save_API::TRANSIENT_PREFIX . $token );
		}
	}
}
