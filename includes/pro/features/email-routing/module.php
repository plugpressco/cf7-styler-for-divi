<?php
/**
 * Email Routing Module – override CF7 mail recipient based on field values (Pro).
 *
 * @package CF7_Mate\Features\Email_Routing
 * @since 4.0.0
 */

namespace CF7_Mate\Features\Email_Routing;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Routing extends Pro_Feature_Base {

	use Singleton;

	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		require_once __DIR__ . '/editor-panel.php';
		new Email_Routing_Editor_Panel();

		add_action( 'wpcf7_before_send_mail', [ $this, 'apply_routing' ], 20, 2 );
	}

	/**
	 * Evaluate routing rules and override the mail recipient if a rule matches.
	 *
	 * @param \WPCF7_ContactForm $contact_form
	 * @param bool               $abort
	 */
	public function apply_routing( $contact_form, &$abort ) {
		if ( $abort ) {
			return;
		}

		$form_id    = (int) $contact_form->id();
		$rules_json = (string) get_post_meta( $form_id, Email_Routing_Editor_Panel::META_RULES, true );
		if ( ! $rules_json ) {
			return;
		}

		$rules = json_decode( $rules_json, true );
		if ( ! is_array( $rules ) || empty( $rules ) ) {
			return;
		}

		$submission = \WPCF7_Submission::get_instance();
		if ( ! $submission ) {
			return;
		}

		$posted = $submission->get_posted_data();

		foreach ( $rules as $rule ) {
			$field    = $rule['field']    ?? '';
			$operator = $rule['operator'] ?? 'is';
			$value    = $rule['value']    ?? '';
			$emails   = $rule['emails']   ?? [];

			if ( ! $field || empty( $emails ) ) {
				continue;
			}

			$field_val = isset( $posted[ $field ] ) ? (string) $posted[ $field ] : '';

			if ( $this->matches( $field_val, $operator, $value ) ) {
				$mail              = $contact_form->prop( 'mail' );
				$mail['recipient'] = implode( ', ', array_map( 'sanitize_email', $emails ) );
				$contact_form->set_properties( [ 'mail' => $mail ] );
				break; // First match wins.
			}
		}
	}

	private function matches( string $field_val, string $operator, string $rule_val ): bool {
		switch ( $operator ) {
			case 'is':
				return strcasecmp( $field_val, $rule_val ) === 0;
			case 'is_not':
				return strcasecmp( $field_val, $rule_val ) !== 0;
			case 'contains':
				return $rule_val !== '' && stripos( $field_val, $rule_val ) !== false;
			default:
				return false;
		}
	}
}
