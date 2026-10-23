<?php
/**
 * Calculator / Price Estimator Module.
 *
 * Provides calculation fields for forms:
 * - [cf7m-number] - Number input for calculations
 * - [cf7m-calc] - Define a calculation formula
 * - [cf7m-total] - Display calculation result
 *
 * @package CF7_Mate\Features\Calculator
 * @since   3.0.0
 */

namespace CF7_Mate\Features\Calculator;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Calculator
 *
 * @since 3.0.0
 */
class Calculator extends Pro_Feature_Base {

	use Singleton;

	/**
	 * Registered calculations for current form.
	 *
	 * @var array
	 */
	private $calculations = array();

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initialize the feature.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	protected function init() {
		add_filter( 'wpcf7_form_elements', array( $this, 'process_shortcodes' ), 15, 1 );
		add_action( 'wpcf7_admin_init', array( $this, 'add_tag_generators' ), 25 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Process calculator shortcodes.
	 *
	 * @since  3.0.0
	 * @param  string $form Form content.
	 * @return string
	 */
	public function process_shortcodes( $form ) {
		// Reset calculations for this form.
		$this->calculations = array();

		// Process [cf7m-calc] first to register formulas.
		if ( false !== strpos( $form, '[cf7m-calc' ) ) {
			$form = preg_replace_callback(
				'/\[cf7m-calc\s+([^\]]+)\]/',
				array( $this, 'register_calc' ),
				$form
			);
		}

		// Process [cf7m-number] inputs.
		if ( false !== strpos( $form, '[cf7m-number' ) ) {
			$form = preg_replace_callback(
				'/\[cf7m-number\*?\s+([^\]]+)\]/',
				array( $this, 'render_number' ),
				$form
			);
		}

		// Process [cf7m-total] displays.
		if ( false !== strpos( $form, '[cf7m-total' ) ) {
			$form = preg_replace_callback(
				'/\[cf7m-total\s+([^\]]+)\]/',
				array( $this, 'render_total' ),
				$form
			);
		}

		// Process [cf7m-button] – non-submitting button (e.g. "Calculate" for calculator-only forms).
		if ( false !== strpos( $form, '[cf7m-button' ) ) {
			$form = preg_replace_callback(
				'/\[cf7m-button\s*(?:"([^"]*)"|([^\]]*))\]/',
				array( $this, 'render_button' ),
				$form
			);
		}

		return $form;
	}

	/**
	 * Render [cf7m-button] – button with type="button" (does not submit form).
	 * Use for calculator "Calculate" or other actions; style matches submit button.
	 *
	 * Syntax: [cf7m-button "Calculate"] or [cf7m-button label:"Calculate"]
	 *
	 * @since  3.0.0
	 * @param  array $matches Regex matches.
	 * @return string Button HTML.
	 */
	public function render_button( $matches ) {
		$label = isset( $matches[1] ) && '' !== $matches[1] ? $matches[1] : '';
		if ( '' === $label && isset( $matches[2] ) && '' !== trim( $matches[2] ) ) {
			$atts = $this->parse_atts( $matches[2] );
			$label = $atts['label'] ?? __( 'Calculate', 'cf7-styler-for-divi' );
		}
		if ( '' === $label ) {
			$label = __( 'Calculate', 'cf7-styler-for-divi' );
		}
		return sprintf(
			'<button type="button" class="wpcf7-form-control cf7m-button cf7m-calc-trigger" data-cf7m-action="calculate">%s</button>',
			esc_html( $label )
		);
	}

	/**
	 * Register a calculation formula.
	 *
	 * Syntax: [cf7m-calc id:quote formula:"qty * price + shipping"]
	 *
	 * @since  3.0.0
	 * @param  array $matches Regex matches.
	 * @return string Empty string (no visible output).
	 */
	public function register_calc( $matches ) {
		$atts = $this->parse_atts( $matches[1] );

		$id      = sanitize_key( $atts['id'] ?? 'calc' );
		$formula = $atts['formula'] ?? '';

		if ( ! empty( $formula ) ) {
			$this->calculations[ $id ] = $formula;
		}

		// Output hidden element with formula data.
		return sprintf(
			'<input type="hidden" class="cf7m-calc-formula" data-calc-id="%s" data-formula="%s">',
			esc_attr( $id ),
			esc_attr( $formula )
		);
	}

	/**
	 * Render number input field.
	 *
	 * Syntax: [cf7m-number qty label:"Quantity" min:1 max:100 value:1 step:1]
	 *
	 * @since  3.0.0
	 * @param  array $matches Regex matches.
	 * @return string HTML output.
	 */
	public function render_number( $matches ) {
		$raw         = trim( $matches[1] );
		$is_required = false !== strpos( $matches[0], '[cf7m-number*' );
		$atts        = $this->parse_atts( $raw );

		// First word is the field name.
		$parts = preg_split( '/\s+/', $raw, 2 );
		$name  = sanitize_key( $parts[0] ?? 'number' );

		// Defaults.
		$label       = $atts['label'] ?? '';
		$value       = floatval( $atts['value'] ?? 0 );
		$min         = isset( $atts['min'] ) ? floatval( $atts['min'] ) : '';
		$max         = isset( $atts['max'] ) ? floatval( $atts['max'] ) : '';
		$step        = floatval( $atts['step'] ?? 1 );
		$placeholder = $atts['placeholder'] ?? '';
		$prefix      = $atts['prefix'] ?? '';
		$suffix      = $atts['suffix'] ?? '';

		// Build input attributes.
		$input_atts = array(
			'type'             => 'number',
			'name'             => $name,
			'id'               => 'cf7m-' . $name,
			'class'            => 'cf7m-calc-input wpcf7-form-control',
			'value'            => $value,
			'step'             => $step,
			'data-calc-field'  => $name,
			'aria-label'       => $label ?: $name,
		);

		if ( '' !== $min ) {
			$input_atts['min'] = $min;
		}
		if ( '' !== $max ) {
			$input_atts['max'] = $max;
		}
		if ( $placeholder ) {
			$input_atts['placeholder'] = $placeholder;
		}
		if ( $is_required ) {
			$input_atts['required']          = 'required';
			$input_atts['aria-required']     = 'true';
			$input_atts['class']            .= ' wpcf7-validates-as-required';
		}

		$input_html = '<input';
		foreach ( $input_atts as $key => $val ) {
			$input_html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $val ) );
		}
		$input_html .= '>';

		// Build wrapper.
		$html = sprintf(
			'<span class="wpcf7-form-control-wrap wpcf7-form-control-wrap-%s" data-name="%s">',
			esc_attr( $name ),
			esc_attr( $name )
		);

		$html .= '<span class="cf7m-number-field">';

		if ( $prefix ) {
			$html .= sprintf( '<span class="cf7m-number-prefix">%s</span>', esc_html( $prefix ) );
		}

		$html .= $input_html;

		if ( $suffix ) {
			$html .= sprintf( '<span class="cf7m-number-suffix">%s</span>', esc_html( $suffix ) );
		}

		$html .= '</span></span>';

		return $html;
	}

	/**
	 * Render total/result display.
	 *
	 * Syntax: [cf7m-total id:quote format:currency prefix:$ decimals:2 label:"Total"]
	 *
	 * @since  3.0.0
	 * @param  array $matches Regex matches.
	 * @return string HTML output.
	 */
	public function render_total( $matches ) {
		$atts = $this->parse_atts( $matches[1] );

		// First word can be the ID.
		$parts = preg_split( '/\s+/', trim( $matches[1] ), 2 );
		$id    = sanitize_key( $atts['id'] ?? $parts[0] ?? 'calc' );

		$format   = $atts['format'] ?? 'number';
		$prefix   = $atts['prefix'] ?? '';
		$suffix   = $atts['suffix'] ?? '';
		$decimals = intval( $atts['decimals'] ?? 2 );
		$label    = $atts['label'] ?? '';

		// Currency shortcuts.
		if ( 'currency' === $format && empty( $prefix ) ) {
			$prefix = '$';
		}

		$html = '<span class="cf7m-total-wrap">';

		if ( $label ) {
			$html .= sprintf( '<span class="cf7m-total-label">%s</span>', esc_html( $label ) );
		}

		$html .= sprintf(
			'<span class="cf7m-total" data-calc-id="%s" data-format="%s" data-prefix="%s" data-suffix="%s" data-decimals="%d">',
			esc_attr( $id ),
			esc_attr( $format ),
			esc_attr( $prefix ),
			esc_attr( $suffix ),
			$decimals
		);

		$html .= sprintf( '<span class="cf7m-total-prefix">%s</span>', esc_html( $prefix ) );
		$html .= '<span class="cf7m-total-value">0</span>';
		$html .= sprintf( '<span class="cf7m-total-suffix">%s</span>', esc_html( $suffix ) );
		$html .= '</span>';

		// Hidden input to submit the calculated value.
		$html .= sprintf(
			'<input type="hidden" name="%s" value="0" class="cf7m-total-input" data-calc-id="%s">',
			esc_attr( $id ),
			esc_attr( $id )
		);

		$html .= '</span>';

		return $html;
	}

	/**
	 * Parse shortcode attributes.
	 *
	 * Handles: key:value, key:"value with spaces"
	 *
	 * @since  3.0.0
	 * @param  string $raw Raw attribute string.
	 * @return array Parsed attributes.
	 */
	private function parse_atts( $raw ) {
		$atts = array();

		// Match key:value or key:"value" patterns.
		preg_match_all( '/(\w+):(?:"([^"]*)"|([^\s]+))/', $raw, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$key   = $match[1];
			$value = isset( $match[2] ) && '' !== $match[2] ? $match[2] : ( $match[3] ?? '' );
			$atts[ $key ] = $value;
		}

		return $atts;
	}

	/**
	 * Register CF7 tag generators.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function add_tag_generators() {
		if ( ! class_exists( 'WPCF7_TagGenerator' ) ) {
			return;
		}

		$generator = \WPCF7_TagGenerator::get_instance();

		$generator->add(
			'cf7m-number',
			__( 'number (calc)', 'cf7-styler-for-divi' ),
			array( $this, 'number_generator_callback' ),
			array( 'version' => '2' )
		);

		$generator->add(
			'cf7m-calc',
			__( 'calculator', 'cf7-styler-for-divi' ),
			array( $this, 'calc_generator_callback' ),
			array( 'version' => '2' )
		);

		$generator->add(
			'cf7m-total',
			__( 'total display', 'cf7-styler-for-divi' ),
			array( $this, 'total_generator_callback' ),
			array( 'version' => '2' )
		);

		$generator->add(
			'cf7m-button',
			__( 'button (no submit)', 'cf7-styler-for-divi' ),
			array( $this, 'button_generator_callback' ),
			array( 'version' => '2' )
		);
	}

	/**
	 * Number field tag generator.
	 *
	 * @since  3.0.0
	 * @param  \WPCF7_ContactForm $contact_form Contact form.
	 * @param  string             $options      Options.
	 * @return void
	 */
	public function number_generator_callback( $contact_form, $options = '' ) {
		?>
		<div class="control-box">
			<fieldset>
				<legend><?php esc_html_e( 'Number Field (Calculator)', 'cf7-styler-for-divi' ); ?></legend>
				<table class="form-table"><tbody>
					<tr>
						<th><?php esc_html_e( 'Field type', 'cf7-styler-for-divi' ); ?></th>
						<td><label><input type="checkbox" name="required" id="cf7m-number-required"> <?php esc_html_e( 'Required', 'cf7-styler-for-divi' ); ?></label></td>
					</tr>
					<tr>
						<th><label for="cf7m-number-name"><?php esc_html_e( 'Name', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="name" id="cf7m-number-name" class="tg-name oneline" placeholder="qty"></td>
					</tr>
					<tr>
						<th><label for="cf7m-number-label"><?php esc_html_e( 'Label', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="label" id="cf7m-number-label" class="oneline" placeholder="Quantity"></td>
					</tr>
					<tr>
						<th><label for="cf7m-number-value"><?php esc_html_e( 'Default Value', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="number" name="value" id="cf7m-number-value" class="oneline" value="1"></td>
					</tr>
					<tr>
						<th><label for="cf7m-number-min"><?php esc_html_e( 'Min', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="number" name="min" id="cf7m-number-min" class="oneline" value="0"></td>
					</tr>
					<tr>
						<th><label for="cf7m-number-max"><?php esc_html_e( 'Max', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="number" name="max" id="cf7m-number-max" class="oneline" value="100"></td>
					</tr>
					<tr>
						<th><label for="cf7m-number-step"><?php esc_html_e( 'Step', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="number" name="step" id="cf7m-number-step" class="oneline" value="1" step="any"></td>
					</tr>
					<tr>
						<th><label for="cf7m-number-prefix"><?php esc_html_e( 'Prefix', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="prefix" id="cf7m-number-prefix" class="oneline" placeholder="$"></td>
					</tr>
					<tr>
						<th><label for="cf7m-number-suffix"><?php esc_html_e( 'Suffix', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="suffix" id="cf7m-number-suffix" class="oneline" placeholder="items"></td>
					</tr>
				</tbody></table>
			</fieldset>
		</div>
		<div class="insert-box">
			<input type="text" name="cf7m-number" class="tag code" readonly onfocus="this.select()" value="[cf7m-number qty value:1 min:0 max:100]">
			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e( 'Insert Tag', 'cf7-styler-for-divi' ); ?>">
			</div>
		</div>
		<?php
	}

	/**
	 * Calculator formula tag generator.
	 *
	 * @since  3.0.0
	 * @param  \WPCF7_ContactForm $contact_form Contact form.
	 * @param  string             $options      Options.
	 * @return void
	 */
	public function calc_generator_callback( $contact_form, $options = '' ) {
		?>
		<div class="control-box">
			<fieldset>
				<legend><?php esc_html_e( 'Calculator Formula', 'cf7-styler-for-divi' ); ?></legend>
				<table class="form-table"><tbody>
					<tr>
						<th><label for="cf7m-calc-id"><?php esc_html_e( 'Calculation ID', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="id" id="cf7m-calc-id" class="oneline" placeholder="total"></td>
					</tr>
					<tr>
						<th><label for="cf7m-calc-formula"><?php esc_html_e( 'Formula', 'cf7-styler-for-divi' ); ?></label></th>
						<td>
							<input type="text" name="formula" id="cf7m-calc-formula" class="wide" placeholder="qty * price + shipping">
							<p class="description"><?php esc_html_e( 'Use field names and operators: + - * / ( )', 'cf7-styler-for-divi' ); ?></p>
						</td>
					</tr>
				</tbody></table>
			</fieldset>
		</div>
		<div class="insert-box">
			<input type="text" name="cf7m-calc" class="tag code" readonly onfocus="this.select()" value='[cf7m-calc id:total formula:"qty * price"]'>
			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e( 'Insert Tag', 'cf7-styler-for-divi' ); ?>">
			</div>
		</div>
		<?php
	}

	/**
	 * Total display tag generator.
	 *
	 * @since  3.0.0
	 * @param  \WPCF7_ContactForm $contact_form Contact form.
	 * @param  string             $options      Options.
	 * @return void
	 */
	public function total_generator_callback( $contact_form, $options = '' ) {
		?>
		<div class="control-box">
			<fieldset>
				<legend><?php esc_html_e( 'Total Display', 'cf7-styler-for-divi' ); ?></legend>
				<table class="form-table"><tbody>
					<tr>
						<th><label for="cf7m-total-id"><?php esc_html_e( 'Calculation ID', 'cf7-styler-for-divi' ); ?></label></th>
						<td>
							<input type="text" name="id" id="cf7m-total-id" class="oneline" placeholder="total">
							<p class="description"><?php esc_html_e( 'Must match [cf7m-calc] ID', 'cf7-styler-for-divi' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="cf7m-total-label"><?php esc_html_e( 'Label', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="label" id="cf7m-total-label" class="oneline" placeholder="Total:"></td>
					</tr>
					<tr>
						<th><label for="cf7m-total-format"><?php esc_html_e( 'Format', 'cf7-styler-for-divi' ); ?></label></th>
						<td>
							<select name="format" id="cf7m-total-format">
								<option value="number"><?php esc_html_e( 'Number', 'cf7-styler-for-divi' ); ?></option>
								<option value="currency"><?php esc_html_e( 'Currency', 'cf7-styler-for-divi' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="cf7m-total-prefix"><?php esc_html_e( 'Prefix', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="prefix" id="cf7m-total-prefix" class="oneline" placeholder="$"></td>
					</tr>
					<tr>
						<th><label for="cf7m-total-suffix"><?php esc_html_e( 'Suffix', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="suffix" id="cf7m-total-suffix" class="oneline" placeholder="USD"></td>
					</tr>
					<tr>
						<th><label for="cf7m-total-decimals"><?php esc_html_e( 'Decimals', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="number" name="decimals" id="cf7m-total-decimals" class="oneline" value="2" min="0" max="6"></td>
					</tr>
				</tbody></table>
			</fieldset>
		</div>
		<div class="insert-box">
			<input type="text" name="cf7m-total" class="tag code" readonly onfocus="this.select()" value='[cf7m-total id:total format:currency prefix:$ decimals:2]'>
			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e( 'Insert Tag', 'cf7-styler-for-divi' ); ?>">
			</div>
		</div>
		<?php
	}

	/**
	 * Button (no submit) tag generator – for calculator "Calculate" or other non-submit actions.
	 *
	 * @since  3.0.0
	 * @param  \WPCF7_ContactForm $contact_form Contact form.
	 * @param  string             $options      Options.
	 * @return void
	 */
	public function button_generator_callback( $contact_form, $options = '' ) {
		?>
		<div class="control-box cf7m-tag-panel">
			<fieldset>
				<legend><?php esc_html_e( 'Button (does not submit form)', 'cf7-styler-for-divi' ); ?></legend>
				<table class="form-table"><tbody>
					<tr>
						<th><label for="cf7m-button-label"><?php esc_html_e( 'Button text', 'cf7-styler-for-divi' ); ?></label></th>
						<td><input type="text" name="label" id="cf7m-button-label" class="oneline" placeholder="<?php esc_attr_e( 'Calculate', 'cf7-styler-for-divi' ); ?>"></td>
					</tr>
				</tbody></table>
				<p class="description"><?php esc_html_e( 'Use for calculator-only forms so the button recalculates without sending the form. Styled like the submit button.', 'cf7-styler-for-divi' ); ?></p>
			</fieldset>
		</div>
		<div class="insert-box">
			<input type="text" name="cf7m-button" class="tag code" readonly onfocus="this.select()" value='[cf7m-button "<?php esc_attr_e( 'Calculate', 'cf7-styler-for-divi' ); ?>"]'>
			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e( 'Insert Tag', 'cf7-styler-for-divi' ); ?>">
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! Pro_Feature_Base::page_has_cf7_form() ) {
			return;
		}

		$version = defined( 'CF7M_VERSION' ) ? CF7M_VERSION : '3.0.0';

		wp_enqueue_style(
			'cf7m-pro-forms',
			CF7M_PLUGIN_URL . 'assets/pro/css/cf7m-pro-forms.css',
			array(),
			$version
		);

		wp_enqueue_script(
			'cf7m-calculator',
			CF7M_PLUGIN_URL . 'assets/pro/js/cf7m-calculator.js',
			array(),
			$version,
			true
		);
	}

}
