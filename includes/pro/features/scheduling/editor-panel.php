<?php
/**
 * Form Scheduling – CF7 editor panel for setting open/close dates per form.
 *
 * @package CF7_Mate\Features\Scheduling
 * @since 3.1.0
 */

namespace CF7_Mate\Features\Scheduling;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scheduling_Editor_Panel {

	const NONCE_ACTION = 'cf7m_scheduling';
	const NONCE_NAME   = 'cf7m_scheduling_nonce';

	const META_ENABLED = '_cf7m_schedule_enabled';
	const META_START   = '_cf7m_schedule_start';
	const META_END     = '_cf7m_schedule_end';
	const META_MSG     = '_cf7m_schedule_msg';

	public function __construct() {
		add_action( 'cf7m_editor_panel_sections', [ $this, 'render_section' ] );
		add_action( 'wpcf7_save_contact_form', [ $this, 'save' ], 10, 1 );
	}

	public function render_section( $contact_form ) {
		$form_id = $contact_form ? (int) $contact_form->id() : 0;
		$enabled = $form_id ? get_post_meta( $form_id, self::META_ENABLED, true ) === '1' : false;
		$start   = $form_id ? (string) get_post_meta( $form_id, self::META_START, true ) : '';
		$end     = $form_id ? (string) get_post_meta( $form_id, self::META_END, true ) : '';
		$msg     = $form_id ? (string) get_post_meta( $form_id, self::META_MSG, true ) : '';
		if ( ! $msg ) {
			$msg = __( 'This form is no longer accepting submissions.', 'cf7-styler-for-divi' );
		}

		// Convert stored MySQL datetime to HTML datetime-local format (YYYY-MM-DDTHH:MM).
		$start_input = $start ? str_replace( ' ', 'T', substr( $start, 0, 16 ) ) : '';
		$end_input   = $end   ? str_replace( ' ', 'T', substr( $end, 0, 16 ) )   : '';
		?>
		<section class="cf7m-feat">
			<header class="cf7m-feat__header">
				<h3 class="cf7m-feat__title"><?php esc_html_e( 'Form Scheduling', 'cf7-styler-for-divi' ); ?></h3>
				<p class="cf7m-feat__desc">
					<?php esc_html_e( 'Open and close the form on specific dates. Visitors outside the window see a custom closed-state message.', 'cf7-styler-for-divi' ); ?>
				</p>
			</header>
			<div class="cf7m-feat__body">

		<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>

		<label class="cf7m-feat-check" style="margin-bottom: 12px;">
			<input type="checkbox" name="cf7m_schedule_enabled" value="1" <?php checked( $enabled ); ?> />
			<span><?php esc_html_e( 'Enable scheduling for this form', 'cf7-styler-for-divi' ); ?></span>
		</label>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="cf7m_schedule_start"><?php esc_html_e( 'Open date', 'cf7-styler-for-divi' ); ?></label>
					</th>
					<td>
						<input
							type="datetime-local"
							id="cf7m_schedule_start"
							name="cf7m_schedule_start"
							value="<?php echo esc_attr( $start_input ); ?>"
						/>
						<p class="description"><?php esc_html_e( 'Leave blank to accept submissions immediately.', 'cf7-styler-for-divi' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="cf7m_schedule_end"><?php esc_html_e( 'Close date', 'cf7-styler-for-divi' ); ?></label>
					</th>
					<td>
						<input
							type="datetime-local"
							id="cf7m_schedule_end"
							name="cf7m_schedule_end"
							value="<?php echo esc_attr( $end_input ); ?>"
						/>
						<p class="description"><?php esc_html_e( 'Leave blank to keep the form open indefinitely.', 'cf7-styler-for-divi' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="cf7m_schedule_msg"><?php esc_html_e( 'Closed message', 'cf7-styler-for-divi' ); ?></label>
					</th>
					<td>
						<textarea
							id="cf7m_schedule_msg"
							name="cf7m_schedule_msg"
							rows="3"
							cols="50"
						><?php echo esc_textarea( $msg ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Shown when the form is outside its scheduling window.', 'cf7-styler-for-divi' ); ?></p>
					</td>
				</tr>
			</table>
			</div>
		</section>
		<?php
	}

	public function save( $contact_form ) {
		if ( ! $contact_form instanceof \WPCF7_ContactForm ) {
			return;
		}
		$form_id = (int) $contact_form->id();
		if ( $form_id <= 0 ) {
			return;
		}
		if ( ! current_user_can( 'wpcf7_edit_contact_form', $form_id ) ) {
			return;
		}
		if ( empty( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ),
			self::NONCE_ACTION
		) ) {
			return;
		}

		$enabled = ! empty( $_POST['cf7m_schedule_enabled'] ) ? '1' : '0';
		update_post_meta( $form_id, self::META_ENABLED, $enabled );

		// Convert datetime-local (YYYY-MM-DDTHH:MM) to MySQL format (YYYY-MM-DD HH:MM:SS).
		$start_raw = sanitize_text_field( wp_unslash( $_POST['cf7m_schedule_start'] ?? '' ) );
		$end_raw   = sanitize_text_field( wp_unslash( $_POST['cf7m_schedule_end'] ?? '' ) );
		update_post_meta( $form_id, self::META_START, $start_raw ? str_replace( 'T', ' ', $start_raw ) . ':00' : '' );
		update_post_meta( $form_id, self::META_END,   $end_raw   ? str_replace( 'T', ' ', $end_raw )   . ':00' : '' );

		$msg = sanitize_textarea_field( wp_unslash( $_POST['cf7m_schedule_msg'] ?? '' ) );
		update_post_meta( $form_id, self::META_MSG, $msg );
	}
}
