<?php
/**
 * Redirect after submission — per-form CF7 editor panel.
 *
 * Modelled on the popular "Redirection for Contact Form 7" plugin but kept
 * inside CF7 Mate so users get one toolkit instead of stacking add-ons.
 *
 * @package CF7_Mate\Features\Redirect
 * @since 3.1.0
 */

namespace CF7_Mate\Features\Redirect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Redirect_Editor_Panel {

	const NONCE_ACTION = 'cf7m_redirect';
	const NONCE_NAME   = 'cf7m_redirect_nonce';
	const META_CONFIG  = '_cf7m_redirect';

	public function __construct() {
		add_action( 'cf7m_editor_panel_sections', [ $this, 'render_section' ] );
		add_action( 'wpcf7_save_contact_form', [ $this, 'save' ], 10, 1 );
		add_action( 'admin_footer', [ $this, 'print_script' ] );
	}

	/**
	 * Read the saved config for a form. Always returns the full default shape
	 * so the template doesn't have to null-check every key.
	 */
	public static function get_config( int $form_id ): array {
		$defaults = [
			'enabled'  => false,
			'url'      => '',
			'new_tab'  => false,
			'delay_ms' => 0,
			'rules'    => [],
		];
		if ( $form_id <= 0 ) {
			return $defaults;
		}
		$saved = get_post_meta( $form_id, self::META_CONFIG, true );
		if ( is_string( $saved ) && $saved !== '' ) {
			$saved = json_decode( $saved, true );
		}
		if ( ! is_array( $saved ) ) {
			return $defaults;
		}
		return array_merge( $defaults, $saved );
	}

	public function render_section( $contact_form ) {
		$form_id   = $contact_form ? (int) $contact_form->id() : 0;
		$cfg       = self::get_config( $form_id );
		$operators = [
			'is'       => __( 'is', 'cf7-styler-for-divi' ),
			'is_not'   => __( 'is not', 'cf7-styler-for-divi' ),
			'contains' => __( 'contains', 'cf7-styler-for-divi' ),
		];
		?>
		<fieldset style="margin-top:20px;">
		<legend><?php esc_html_e( 'Redirect after submission', 'cf7-styler-for-divi' ); ?></legend>
		<p class="description">
			<?php esc_html_e( 'Send visitors to a thank-you page after the form sends successfully. You can reference submitted values in the URL using [field_name] placeholders, e.g. /thanks/?email=[your-email].', 'cf7-styler-for-divi' ); ?>
		</p>

		<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable redirect', 'cf7-styler-for-divi' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="cf7m_redirect_enabled" value="1" <?php checked( $cfg['enabled'] ); ?> />
							<?php esc_html_e( 'Redirect after a successful submission', 'cf7-styler-for-divi' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cf7m-redirect-url"><?php esc_html_e( 'Redirect URL', 'cf7-styler-for-divi' ); ?></label></th>
					<td>
						<input type="text" id="cf7m-redirect-url" name="cf7m_redirect_url"
							value="<?php echo esc_attr( $cfg['url'] ); ?>"
							class="regular-text"
							placeholder="https://example.com/thank-you/?email=[your-email]" />
						<p class="description">
							<?php esc_html_e( 'Relative ("/thank-you/") or absolute URL. Placeholders like [field_name] are replaced with submitted values (URL-encoded).', 'cf7-styler-for-divi' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Open in new tab', 'cf7-styler-for-divi' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="cf7m_redirect_new_tab" value="1" <?php checked( $cfg['new_tab'] ); ?> />
							<?php esc_html_e( 'Open the target URL in a new browser tab', 'cf7-styler-for-divi' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cf7m-redirect-delay"><?php esc_html_e( 'Delay (ms)', 'cf7-styler-for-divi' ); ?></label></th>
					<td>
						<input type="number" id="cf7m-redirect-delay" name="cf7m_redirect_delay_ms"
							value="<?php echo (int) $cfg['delay_ms']; ?>"
							min="0" max="60000" step="100" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Optional pause before the redirect fires. Useful if you want the CF7 success message to be visible for a moment first.', 'cf7-styler-for-divi' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<h4 style="margin-top:24px;"><?php esc_html_e( 'Conditional redirects', 'cf7-styler-for-divi' ); ?></h4>
		<p class="description">
			<?php esc_html_e( 'Override the default redirect URL when a field matches a rule. Rules are evaluated in order — the first match wins. If no rule matches, the default URL above is used.', 'cf7-styler-for-divi' ); ?>
		</p>

		<table class="widefat" id="cf7m-redirect-table" style="margin-top:8px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Field name', 'cf7-styler-for-divi' ); ?></th>
					<th><?php esc_html_e( 'Operator', 'cf7-styler-for-divi' ); ?></th>
					<th><?php esc_html_e( 'Value', 'cf7-styler-for-divi' ); ?></th>
					<th><?php esc_html_e( 'Redirect to', 'cf7-styler-for-divi' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody id="cf7m-redirect-rows">
				<?php foreach ( $cfg['rules'] as $rule ) : ?>
				<tr>
					<td><input type="text" name="cf7m_redirect_field[]" value="<?php echo esc_attr( $rule['field'] ?? '' ); ?>" placeholder="e.g. plan" style="width:100%;" /></td>
					<td>
						<select name="cf7m_redirect_operator[]">
							<?php foreach ( $operators as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $rule['operator'] ?? 'is', $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="cf7m_redirect_value[]" value="<?php echo esc_attr( $rule['value'] ?? '' ); ?>" placeholder="e.g. Business" style="width:100%;" /></td>
					<td><input type="text" name="cf7m_redirect_target[]" value="<?php echo esc_attr( $rule['url'] ?? '' ); ?>" placeholder="/demo/" style="width:100%;" /></td>
					<td><button type="button" class="button cf7m-remove-redirect-rule"><?php esc_html_e( 'Remove', 'cf7-styler-for-divi' ); ?></button></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p style="margin-top:8px;">
			<button type="button" id="cf7m-add-redirect-rule" class="button">
				<?php esc_html_e( '+ Add rule', 'cf7-styler-for-divi' ); ?>
			</button>
		</p>
		</fieldset>
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
			var addBtn = document.getElementById('cf7m-add-redirect-rule');
			var rowsEl = document.getElementById('cf7m-redirect-rows');
			if ( ! addBtn || ! rowsEl ) { return; }

			var operators   = <?php echo wp_json_encode( $operators ); ?>;
			var removeLabel = <?php echo wp_json_encode( __( 'Remove', 'cf7-styler-for-divi' ) ); ?>;

			function makeRow() {
				var tr = document.createElement('tr');
				var opOpts = Object.keys(operators).map(function(k){
					return '<option value="'+k+'">'+operators[k]+'</option>';
				}).join('');
				tr.innerHTML =
					'<td><input type="text" name="cf7m_redirect_field[]" placeholder="e.g. plan" style="width:100%;"></td>' +
					'<td><select name="cf7m_redirect_operator[]">'+opOpts+'</select></td>' +
					'<td><input type="text" name="cf7m_redirect_value[]" placeholder="e.g. Business" style="width:100%;"></td>' +
					'<td><input type="text" name="cf7m_redirect_target[]" placeholder="/demo/" style="width:100%;"></td>' +
					'<td><button type="button" class="button cf7m-remove-redirect-rule">'+removeLabel+'</button></td>';
				return tr;
			}

			addBtn.addEventListener('click', function(){ rowsEl.appendChild(makeRow()); });
			rowsEl.addEventListener('click', function(e){
				if (e.target && e.target.classList.contains('cf7m-remove-redirect-rule')) {
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

		$enabled  = ! empty( $_POST['cf7m_redirect_enabled'] );
		$url      = isset( $_POST['cf7m_redirect_url'] ) ? trim( wp_unslash( $_POST['cf7m_redirect_url'] ) ) : '';
		$new_tab  = ! empty( $_POST['cf7m_redirect_new_tab'] );
		$delay_ms = isset( $_POST['cf7m_redirect_delay_ms'] ) ? (int) $_POST['cf7m_redirect_delay_ms'] : 0;
		$delay_ms = max( 0, min( 60000, $delay_ms ) );

		$fields    = (array) ( $_POST['cf7m_redirect_field']    ?? [] );
		$operators = (array) ( $_POST['cf7m_redirect_operator'] ?? [] );
		$values    = (array) ( $_POST['cf7m_redirect_value']    ?? [] );
		$targets   = (array) ( $_POST['cf7m_redirect_target']   ?? [] );

		$valid_ops = [ 'is', 'is_not', 'contains' ];
		$rules     = [];
		foreach ( $fields as $i => $field ) {
			$field    = sanitize_text_field( wp_unslash( (string) $field ) );
			$operator = isset( $operators[ $i ] ) ? sanitize_text_field( wp_unslash( (string) $operators[ $i ] ) ) : 'is';
			if ( ! in_array( $operator, $valid_ops, true ) ) {
				$operator = 'is';
			}
			$value  = isset( $values[ $i ] )  ? sanitize_text_field( wp_unslash( (string) $values[ $i ] ) )  : '';
			$target = isset( $targets[ $i ] ) ? trim( wp_unslash( (string) $targets[ $i ] ) )                : '';
			if ( ! $field || $target === '' ) {
				continue;
			}
			$rules[] = [
				'field'    => $field,
				'operator' => $operator,
				'value'    => $value,
				'url'      => esc_url_raw( $target ),
			];
		}

		$config = [
			'enabled'  => $enabled,
			'url'      => esc_url_raw( $url ),
			'new_tab'  => $new_tab,
			'delay_ms' => $delay_ms,
			'rules'    => $rules,
		];

		update_post_meta( $form_id, self::META_CONFIG, wp_json_encode( $config ) );
	}
}
