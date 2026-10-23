<?php
/**
 * Conditional Logic Module.
 *
 * Show/hide form fields based on other field values.
 *
 * Syntax:
 *   [cf7m-if field:"name" is:"value"]...[/cf7m-if]
 *
 * Operators:
 *   is       - equals (default)
 *   not      - not equals
 *   gt       - greater than
 *   lt       - less than
 *   gte      - greater than or equal
 *   lte      - less than or equal
 *   contains - contains substring
 *   empty    - field is empty (true/false)
 *   checked  - checkbox is checked (true/false)
 *   any      - matches any value in comma-separated list
 *
 * @package CF7_Mate\Features\Conditional
 * @since   3.0.0
 */

namespace CF7_Mate\Features\Conditional;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Conditional
 *
 * @since 3.0.0
 */
class Conditional extends Pro_Feature_Base {

	use Singleton;

	/**
	 * Supported operators.
	 *
	 * @var array
	 */
	const OPERATORS = array(
		'is',
		'not',
		'gt',
		'lt',
		'gte',
		'lte',
		'contains',
		'empty',
		'checked',
		'any',
	);

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	protected function init() {
		add_filter( 'wpcf7_form_elements', array( $this, 'process_shortcodes' ), 12, 1 );
		add_filter( 'wpcf7_validate', array( $this, 'validate_visible_fields' ), 999, 2 );
		add_action( 'wpcf7_admin_init', array( $this, 'add_tag_generator' ), 25 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Process [cf7m-if]...[/cf7m-if] shortcodes.
	 *
	 * @since  3.0.0
	 * @param  string $form Form HTML.
	 * @return string Modified form HTML.
	 */
	public function process_shortcodes( $form ) {
		if ( false === strpos( $form, '[cf7m-if' ) ) {
			return $form;
		}

		return $this->replace_blocks( $form, array( $this, 'render_condition' ) );
	}

	/**
	 * Render a conditional block.
	 *
	 * @since  3.0.0
	 * @param  array $matches Regex matches.
	 * @return string HTML output.
	 */
	public function render_condition( $matches ) {
		$atts    = $this->parse_attributes( $matches[1] );
		$content = $matches[2];

		// Extract condition parts.
		$field    = $atts['field'] ?? '';
		$operator = $this->get_operator( $atts );
		$value    = $atts[ $operator ] ?? '';

		if ( empty( $field ) ) {
			return $content; // No field specified, show content.
		}

		// Build data attributes for JS.
		$data_atts = array(
			'data-cf7m-if'       => esc_attr( $field ),
			'data-cf7m-operator' => esc_attr( $operator ),
			'data-cf7m-value'    => esc_attr( $value ),
		);

		// Handle "and" conditions.
		if ( isset( $atts['and'] ) ) {
			$data_atts['data-cf7m-and'] = esc_attr( $atts['and'] );
		}

		// Handle "or" conditions.
		if ( isset( $atts['or'] ) ) {
			$data_atts['data-cf7m-or'] = esc_attr( $atts['or'] );
		}

		$data_string = '';
		foreach ( $data_atts as $key => $val ) {
			$data_string .= sprintf( ' %s="%s"', $key, $val );
		}


		return sprintf(
			'<div class="cf7m-condition" style="display:none;"%s>%s</div>',
			$data_string,
			$content
		);
	}

	/**
	 * Replace innermost blocks first so nested closing tags stay paired.
	 *
	 * @param string   $content Form content.
	 * @param callable $callback Block replacement.
	 * @return string
	 */
	private function replace_blocks( $content, $callback ) {
		$pattern = '/\[cf7m-if\s+([^\]]+)\]((?:(?!\[\/?cf7m-if\b).)*?)\[\/cf7m-if\]/s';
		do {
			$content = preg_replace_callback( $pattern, $callback, $content, -1, $count );
		} while ( $count );
		return $content;
	}

	/**
	 * Find fields hidden by the saved template and actual submitted values.
	 * No client-supplied list can disable validation of visible fields.
	 *
	 * @param \WPCF7_ContactForm $contact_form Current contact form.
	 * @param array             $posted Submitted field values.
	 * @return string[]
	 */
	public function hidden_fields( $contact_form, $posted ) {
		$template = $contact_form->prop( 'form' );
		if ( false === strpos( $template, '[cf7m-if' ) ) {
			return array();
		}
		$visible = $this->replace_blocks( $template, function ( $match ) use ( $posted ) {
			$atts = $this->parse_attributes( $match[1] );
			if ( empty( $atts['field'] ) ) {
				return $match[2];
			}
			$operator = $this->get_operator( $atts );
			$value = $posted[ $atts['field'] ] ?? '';
			return $this->compare( $value, $operator, $atts[ $operator ] ?? '' ) ? $match[2] : '';
		} );
		// Malformed conditions must never bypass normal validation.
		if ( false !== strpos( $visible, '[cf7m-if' ) || false !== strpos( $visible, '[/cf7m-if]' ) ) {
			return array();
		}
		$manager = \WPCF7_FormTagsManager::get_instance();
		$all_names = wp_list_pluck( $manager->scan( $template ), 'name' );
		$visible_names = wp_list_pluck( $manager->scan( $visible ), 'name' );
		return array_values( array_diff( array_filter( $all_names ), $visible_names ) );
	}

	/**
	 * Ignore errors only for fields proven hidden by the server-side template.
	 *
	 * @param \WPCF7_Validation $result CF7 validation result.
	 * @param array            $tags Form tags.
	 * @return \WPCF7_Validation
	 */
	public function validate_visible_fields( $result, $tags ) {
		$contact_form = \WPCF7_ContactForm::get_current();
		$submission = \WPCF7_Submission::get_instance();
		if ( ! $contact_form || ! $submission ) {
			return $result;
		}
		$hidden = $this->hidden_fields( $contact_form, (array) $submission->get_posted_data() );
		$errors = $result->get_invalid_fields();
		if ( ! array_intersect( array_keys( $errors ), $hidden ) ) {
			return $result;
		}
		$visible_result = new \WPCF7_Validation();
		foreach ( $errors as $name => $error ) {
			if ( ! in_array( $name, $hidden, true ) ) {
				$context = array( 'name' => $name, 'options' => empty( $error['idref'] ) ? array() : array( 'id:' . $error['idref'] ) );
				$visible_result->invalidate( $context, $error['reason'] );
			}
		}
		return $visible_result;
	}

	/**
	 * Match the frontend's comparisons, including multi-value controls.
	 *
	 * @param mixed  $value Submitted value.
	 * @param string $operator Comparison operator.
	 * @param string $target Configured value.
	 * @return bool
	 */
	private function compare( $value, $operator, $target ) {
		$values = array_values( array_filter( array_map( static function ( $item ) {
			return is_scalar( $item ) ? trim( (string) $item ) : '';
		}, (array) $value ), static function ( $item ) { return '' !== $item; } ) );
		$target = trim( $target );
		if ( 'empty' === $operator || 'checked' === $operator ) {
			$match = 'empty' === $operator ? empty( $values ) : ! empty( $values );
			return 'true' === $target ? $match : ! $match;
		}
		if ( 'not' === $operator ) {
			return ! in_array( $target, $values ?: array( '' ), true );
		}
		foreach ( $values ?: array( '' ) as $item ) {
			switch ( $operator ) {
				case 'gt': $match = (float) $item > (float) $target; break;
				case 'lt': $match = (float) $item < (float) $target; break;
				case 'gte': $match = (float) $item >= (float) $target; break;
				case 'lte': $match = (float) $item <= (float) $target; break;
				case 'contains': $match = false !== stripos( $item, $target ); break;
				case 'any': $match = in_array( $item, array_map( 'trim', explode( ',', $target ) ), true ); break;
				default: $match = $item === $target;
			}
			if ( $match ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the operator from attributes.
	 *
	 * @since  3.0.0
	 * @param  array $atts Parsed attributes.
	 * @return string Operator name.
	 */
	private function get_operator( $atts ) {
		foreach ( self::OPERATORS as $op ) {
			if ( isset( $atts[ $op ] ) ) {
				return $op;
			}
		}
		return 'is'; // Default operator.
	}

	/**
	 * Parse shortcode attributes.
	 *
	 * Handles: key:"value with spaces" and key:value
	 *
	 * @since  3.0.0
	 * @param  string $raw Raw attribute string.
	 * @return array Parsed attributes.
	 */
	private function parse_attributes( $raw ) {
		$atts = array();

		// Match key:"value" or key:value patterns.
		preg_match_all( '/(\w+):(?:"([^"]*)"|([^\s]+))/', $raw, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$key   = $match[1];
			$value = '' !== ( $match[2] ?? '' ) ? $match[2] : ( $match[3] ?? '' );
			$atts[ $key ] = $value;
		}

		return $atts;
	}

	/**
	 * Register tag generator in CF7 admin.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function add_tag_generator() {
		if ( ! class_exists( 'WPCF7_TagGenerator' ) ) {
			return;
		}

		\WPCF7_TagGenerator::get_instance()->add(
			'cf7m-if',
			__( 'conditional', 'cf7-styler-for-divi' ),
			array( $this, 'tag_generator_callback' ),
			array( 'version' => '2' )
		);
	}

	/**
	 * Tag generator UI.
	 *
	 * @since  3.0.0
	 * @param  \WPCF7_ContactForm $contact_form Contact form.
	 * @param  string             $options      Options.
	 * @return void
	 */
	public function tag_generator_callback( $contact_form, $options = '' ) {
		?>
		<div class="control-box">
			<fieldset>
				<legend><?php esc_html_e( 'Conditional Logic', 'cf7-styler-for-divi' ); ?></legend>
				<table class="form-table"><tbody>
					<tr>
						<th><label for="cf7m-if-field"><?php esc_html_e( 'Field Name', 'cf7-styler-for-divi' ); ?></label></th>
						<td>
							<input type="text" name="field" id="cf7m-if-field" class="oneline" placeholder="service">
							<p class="description"><?php esc_html_e( 'The field to watch for changes.', 'cf7-styler-for-divi' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="cf7m-if-operator"><?php esc_html_e( 'Condition', 'cf7-styler-for-divi' ); ?></label></th>
						<td>
							<select name="operator" id="cf7m-if-operator">
								<option value="is"><?php esc_html_e( 'equals', 'cf7-styler-for-divi' ); ?></option>
								<option value="not"><?php esc_html_e( 'not equals', 'cf7-styler-for-divi' ); ?></option>
								<option value="gt"><?php esc_html_e( 'greater than', 'cf7-styler-for-divi' ); ?></option>
								<option value="lt"><?php esc_html_e( 'less than', 'cf7-styler-for-divi' ); ?></option>
								<option value="gte"><?php esc_html_e( 'greater than or equal', 'cf7-styler-for-divi' ); ?></option>
								<option value="lte"><?php esc_html_e( 'less than or equal', 'cf7-styler-for-divi' ); ?></option>
								<option value="contains"><?php esc_html_e( 'contains', 'cf7-styler-for-divi' ); ?></option>
								<option value="empty"><?php esc_html_e( 'is empty', 'cf7-styler-for-divi' ); ?></option>
								<option value="checked"><?php esc_html_e( 'is checked', 'cf7-styler-for-divi' ); ?></option>
								<option value="any"><?php esc_html_e( 'matches any', 'cf7-styler-for-divi' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="cf7m-if-value"><?php esc_html_e( 'Value', 'cf7-styler-for-divi' ); ?></label></th>
						<td>
							<input type="text" name="value" id="cf7m-if-value" class="oneline" placeholder="custom">
							<p class="description"><?php esc_html_e( 'For "any": comma-separated values. For "empty/checked": true or false.', 'cf7-styler-for-divi' ); ?></p>
						</td>
					</tr>
				</tbody></table>
			</fieldset>
		</div>
		<div class="insert-box">
			<input type="text" name="cf7m-if" class="tag code" readonly onfocus="this.select()" value='[cf7m-if field:"service" is:"custom"]...[/cf7m-if]'>
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

		wp_enqueue_script(
			'cf7m-conditional',
			CF7M_PLUGIN_URL . 'assets/pro/js/cf7m-conditional.js',
			array(),
			$version,
			true
		);
	}
}
