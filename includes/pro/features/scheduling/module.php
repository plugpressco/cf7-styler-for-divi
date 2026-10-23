<?php
/**
 * Form Scheduling Module – open/close forms by date.
 *
 * @package CF7_Mate\Features\Scheduling
 * @since 4.0.0
 */

namespace CF7_Mate\Features\Scheduling;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scheduling extends Pro_Feature_Base {

	use Singleton;

	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		require_once __DIR__ . '/editor-panel.php';
		new Scheduling_Editor_Panel();

		// Priority 1 on both hooks — run before all other features.
		add_filter( 'wpcf7_form_elements', [ $this, 'maybe_replace_form' ], 1 );
		add_action( 'wpcf7_before_send_mail', [ $this, 'maybe_abort' ], 1, 2 );
	}

	/**
	 * Replace the rendered form HTML with a closed message when outside the scheduling window.
	 *
	 * @param string $form Rendered form HTML.
	 * @return string
	 */
	public function maybe_replace_form( $form ) {
		$cf7 = \WPCF7_ContactForm::get_current();
		if ( ! $cf7 || ! $cf7->id() ) {
			return $form;
		}

		if ( $this->is_form_open( $cf7->id() ) ) {
			return $form;
		}

		$msg = (string) get_post_meta( $cf7->id(), Scheduling_Editor_Panel::META_MSG, true );
		if ( ! $msg ) {
			$msg = __( 'This form is no longer accepting submissions.', 'cf7-styler-for-divi' );
		}

		return '<div class="cf7m-schedule-closed">' . esc_html( $msg ) . '</div>';
	}

	/**
	 * Abort the submission server-side if outside the scheduling window.
	 *
	 * @param \WPCF7_ContactForm $contact_form
	 * @param bool               $abort
	 */
	public function maybe_abort( $contact_form, &$abort ) {
		if ( $contact_form->id() && ! $this->is_form_open( $contact_form->id() ) ) {
			$abort = true;
		}
	}

	private function is_form_open( int $form_id ): bool {
		if ( get_post_meta( $form_id, Scheduling_Editor_Panel::META_ENABLED, true ) !== '1' ) {
			return true;
		}

		$now   = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		$start = (string) get_post_meta( $form_id, Scheduling_Editor_Panel::META_START, true );
		$end   = (string) get_post_meta( $form_id, Scheduling_Editor_Panel::META_END, true );

		if ( $start && $now < strtotime( $start ) ) {
			return false;
		}
		if ( $end && $now > strtotime( $end ) ) {
			return false;
		}

		return true;
	}
}
