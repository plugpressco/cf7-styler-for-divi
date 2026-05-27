<?php
/**
 * Email Routing – CF7 editor panel for per-form routing rules.
 *
 * @package CF7_Mate\Features\Email_Routing
 * @since 3.1.0
 */

namespace CF7_Mate\Features\Email_Routing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Routing_Editor_Panel {

	const NONCE_ACTION = 'cf7m_email_routing';
	const NONCE_NAME   = 'cf7m_email_routing_nonce';
	const META_RULES   = '_cf7m_email_rules';

	public function __construct() {
		add_action( 'cf7m_editor_panel_sections', [ $this, 'render_section' ] );
		add_action( 'wpcf7_save_contact_form', [ $this, 'save' ], 10, 1 );
		add_action( 'admin_footer', [ $this, 'print_script' ] );
	}

	public function render_section( $contact_form ) {
		$form_id    = $contact_form ? (int) $contact_form->id() : 0;
		$rules_json = $form_id ? (string) get_post_meta( $form_id, self::META_RULES, true ) : '';
		$rules      = $rules_json ? json_decode( $rules_json, true ) : [];
		if ( ! is_array( $rules ) ) {
			$rules = [];
		}
		$operators = [
			'is'       => __( 'is', 'cf7-styler-for-divi' ),
			'is_not'   => __( 'is not', 'cf7-styler-for-divi' ),
			'contains' => __( 'contains', 'cf7-styler-for-divi' ),
		];
		?>
		<section class="cf7m-feat">
			<header class="cf7m-feat__header">
				<h3 class="cf7m-feat__title"><?php esc_html_e( 'Email Routing', 'cf7-styler-for-divi' ); ?></h3>
				<p class="cf7m-feat__desc">
					<?php esc_html_e( 'Override the notification recipient based on a field value. Rules are evaluated in order — the first match wins.', 'cf7-styler-for-divi' ); ?>
				</p>
			</header>
			<div class="cf7m-feat__body">

		<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>

		<div id="cf7m-routing-rules">
			<table class="widefat" id="cf7m-routing-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Field name', 'cf7-styler-for-divi' ); ?></th>
						<th><?php esc_html_e( 'Operator', 'cf7-styler-for-divi' ); ?></th>
						<th><?php esc_html_e( 'Value', 'cf7-styler-for-divi' ); ?></th>
						<th><?php esc_html_e( 'Send to (comma-separated emails)', 'cf7-styler-for-divi' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody id="cf7m-routing-rows">
					<?php foreach ( $rules as $i => $rule ) : ?>
					<tr>
						<td><input type="text" name="cf7m_route_field[]" value="<?php echo esc_attr( $rule['field'] ?? '' ); ?>" placeholder="e.g. department" style="width:100%;" /></td>
						<td>
							<select name="cf7m_route_operator[]">
								<?php foreach ( $operators as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $rule['operator'] ?? 'is', $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><input type="text" name="cf7m_route_value[]" value="<?php echo esc_attr( $rule['value'] ?? '' ); ?>" placeholder="e.g. Sales" style="width:100%;" /></td>
						<td><input type="text" name="cf7m_route_emails[]" value="<?php echo esc_attr( implode( ', ', (array) ( $rule['emails'] ?? [] ) ) ); ?>" placeholder="sales@example.com" style="width:100%;" /></td>
						<td><button type="button" class="button cf7m-remove-rule"><?php esc_html_e( 'Remove', 'cf7-styler-for-divi' ); ?></button></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p>
				<button type="button" id="cf7m-add-rule" class="button">
					<?php esc_html_e( '+ Add rule', 'cf7-styler-for-divi' ); ?>
				</button>
			</p>
		</div>
			</div>
		</section>

		<?php
	}

	public function print_script() {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'wpcf7' ) === false ) {
			return;
		}
		$operators = [
			'is'       => __( 'is', 'cf7-styler-for-divi' ),
			'is_not'   => __( 'is not', 'cf7-styler-for-divi' ),
			'contains' => __( 'contains', 'cf7-styler-for-divi' ),
		];
		?>
		<script>
		(function(){
			var addBtn  = document.getElementById('cf7m-add-rule');
			var rowsEl  = document.getElementById('cf7m-routing-rows');
			if ( ! addBtn || ! rowsEl ) { return; }

			var operators = <?php echo wp_json_encode( $operators ); ?>;
			var removeLabel = <?php echo wp_json_encode( __( 'Remove', 'cf7-styler-for-divi' ) ); ?>;

			function makeRow() {
				var tr = document.createElement('tr');
				var opOpts = Object.keys(operators).map(function(k){
					return '<option value="'+k+'">'+operators[k]+'</option>';
				}).join('');
				tr.innerHTML =
					'<td><input type="text" name="cf7m_route_field[]" placeholder="e.g. department" style="width:100%;"></td>' +
					'<td><select name="cf7m_route_operator[]">'+opOpts+'</select></td>' +
					'<td><input type="text" name="cf7m_route_value[]" placeholder="e.g. Sales" style="width:100%;"></td>' +
					'<td><input type="text" name="cf7m_route_emails[]" placeholder="sales@example.com" style="width:100%;"></td>' +
					'<td><button type="button" class="button cf7m-remove-rule">'+removeLabel+'</button></td>';
				return tr;
			}

			addBtn.addEventListener('click', function(){
				rowsEl.appendChild(makeRow());
			});

			rowsEl.addEventListener('click', function(e){
				if (e.target && e.target.classList.contains('cf7m-remove-rule')) {
					e.target.closest('tr').remove();
				}
			});
		})();
		</script>
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

		$fields    = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_POST['cf7m_route_field']    ?? [] ) ) );
		$operators = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_POST['cf7m_route_operator'] ?? [] ) ) );
		$values    = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_POST['cf7m_route_value']    ?? [] ) ) );
		$emails_in = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_POST['cf7m_route_emails']   ?? [] ) ) );

		$valid_ops = [ 'is', 'is_not', 'contains' ];
		$rules     = [];

		foreach ( $fields as $i => $field ) {
			$field    = trim( $field );
			$operator = isset( $operators[ $i ] ) && in_array( $operators[ $i ], $valid_ops, true ) ? $operators[ $i ] : 'is';
			$value    = trim( $values[ $i ] ?? '' );
			$raw_emails = array_filter( array_map( 'sanitize_email', explode( ',', $emails_in[ $i ] ?? '' ) ) );

			if ( ! $field || empty( $raw_emails ) ) {
				continue;
			}

			$rules[] = [
				'field'    => $field,
				'operator' => $operator,
				'value'    => $value,
				'emails'   => array_values( $raw_emails ),
			];
		}

		update_post_meta( $form_id, self::META_RULES, wp_json_encode( $rules ) );
	}
}
