<?php
/**
 * Phone Number Module.
 * Processes [cf7m-phone] shortcodes: input with auto country prefix, searchable dropdown.
 *
 * @package CF7_Mate\Lite\Features\Phone_Number
 * @since 3.0.0
 */

namespace CF7_Mate\Lite\Features\Phone_Number;

use CF7_Mate\Lite\Feature_Base;
use CF7_Mate\Lite\Traits\Shortcode_Atts_Trait;
use CF7_Mate\Lite\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Phone_Number extends Feature_Base
{
    use Shortcode_Atts_Trait;
    use Singleton;

    /** ISO2 => dial code (for initial HTML; must match JS countries). */
    private static $dial_codes = [
        'AF' => '+93', 'AL' => '+355', 'DZ' => '+213', 'AR' => '+54', 'AU' => '+61', 'AT' => '+43',
        'BD' => '+880', 'BE' => '+32', 'BR' => '+55', 'CA' => '+1', 'CL' => '+56', 'CN' => '+86',
        'CO' => '+57', 'CU' => '+53', 'CZ' => '+420', 'DK' => '+45', 'DO' => '+1', 'EC' => '+593',
        'EG' => '+20', 'ES' => '+34', 'FI' => '+358', 'FR' => '+33', 'DE' => '+49', 'GR' => '+30',
        'GT' => '+502', 'HK' => '+852', 'HU' => '+36', 'IN' => '+91', 'ID' => '+62', 'IE' => '+353',
        'IL' => '+972', 'IT' => '+39', 'JM' => '+1', 'JP' => '+81', 'KR' => '+82', 'MY' => '+60',
        'MX' => '+52', 'NL' => '+31', 'NZ' => '+64', 'NG' => '+234', 'NO' => '+47', 'PK' => '+92',
        'PE' => '+51', 'PH' => '+63', 'PL' => '+48', 'PT' => '+351', 'PR' => '+1', 'RO' => '+40',
        'RU' => '+7', 'SA' => '+966', 'SG' => '+65', 'ZA' => '+27', 'SE' => '+46', 'CH' => '+41',
        'TW' => '+886', 'TH' => '+66', 'TR' => '+90', 'UA' => '+380', 'AE' => '+971', 'GB' => '+44',
        'US' => '+1', 'UY' => '+598', 'VE' => '+58', 'VN' => '+84',
    ];

    protected function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        add_filter('wpcf7_form_elements', [$this, 'process_shortcodes'], 20, 1);
        add_filter('wpcf7_validate', [$this, 'validate_submission'], 20, 2);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generators'], 25);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Server-side validation for [cf7m-phone*] fields. We re-parse the form
     * markup for required phone shortcodes because they're not registered as
     * real form-tags, so wpcf7_validate_<tag> filters don't fire.
     *
     * @param \WPCF7_Validation $result
     * @param array             $tags
     * @return \WPCF7_Validation
     */
    public function validate_submission($result, $tags)
    {
        $form = \WPCF7_ContactForm::get_current();
        if (!$form) {
            return $result;
        }

        $markup = $form->prop('form');
        if (!is_string($markup) || strpos($markup, '[cf7m-phone*') === false) {
            return $result;
        }

        if (preg_match_all('/\[cf7m-phone\*\s+([^\]]+)\]/', $markup, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $parts = preg_split('/\s+/', trim($match[1]));
                $name  = !empty($parts[0]) ? sanitize_key($parts[0]) : '';
                if (!$name) {
                    continue;
                }
                $posted = isset($_POST[$name]) ? trim(wp_unslash($_POST[$name])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
                // Strip the dial prefix ("+1 ") and any whitespace — count
                // only digits when deciding whether the user supplied a value.
                $digits = preg_replace('/[^0-9]/', '', $posted);
                if ($digits === '') {
                    $result->invalidate(
                        ['name' => $name, 'type' => 'cf7m-phone*'],
                        wpcf7_get_message('invalid_required')
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Process [cf7m-phone name default:US] shortcodes.
     *
     * @param string $form
     * @return string
     */
    public function process_shortcodes($form)
    {
        if (strpos($form, '[cf7m-phone') === false) {
            return $form;
        }

        $form = preg_replace_callback(
            '/\[cf7m-phone\*?\s+([^\]]+)\]/',
            [$this, 'render_phone_field'],
            $form
        );

        return $form;
    }

    /**
     * Render a single phone number field.
     *
     * @param array $matches Regex matches
     * @return string HTML output
     */
    public function render_phone_field($matches)
    {
        $raw_atts = trim($matches[1]);
        $is_required = strpos($matches[0], '[cf7m-phone*') === 0;

        $label = '';
        $description = '';
        if (preg_match_all('/\b(label|description|desc):"([^"]*)"/', $raw_atts, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $val = trim($match[2]);
                if ($match[1] === 'label') {
                    $label = $val;
                } else {
                    $description = $val;
                }
            }
        }

        $parts = preg_split('/\s+/', $raw_atts);
        $name = !empty($parts[0]) ? sanitize_key($parts[0]) : 'phone';

        $default_country = 'US';
        $placeholder = '';

        for ($i = 1; $i < count($parts); $i++) {
            $part = $parts[$i];
            if (strpos($part, 'default:') === 0) {
                $default_country = strtoupper(sanitize_text_field(substr($part, 8)));
                if (strlen($default_country) !== 2) {
                    $default_country = 'US';
                }
            } elseif (strpos($part, 'placeholder:') === 0) {
                $placeholder = trim(trim(substr($part, 12)), '"');
            }
        }

        $field_id = wp_unique_id('cf7m-phone-');
        $input_id = $field_id . '-input';
        $label_id = $field_id . '-lbl';
        $desc_id = $field_id . '-desc';

        $required_attr    = $is_required ? ' data-required="true" required' : '';
        $aria_required    = $is_required ? ' aria-required="true"' : '';
        $placeholder_attr = $placeholder !== '' ? ' placeholder="' . esc_attr($placeholder) . '"' : '';

        $default_iso2 = strtoupper($default_country);
        $initial_dial = isset(self::$dial_codes[$default_iso2]) ? self::$dial_codes[$default_iso2] : '+1';
        $initial_flag = self::flag_emoji($default_iso2);

        $html = sprintf(
            '<span class="wpcf7-form-control-wrap wpcf7-form-control-wrap-%1$s" data-name="%1$s">',
            esc_attr($name)
        );
        $html .= '<span class="cf7m-phone-field">';
        if ($label !== '') {
            $html .= '<label id="' . esc_attr($label_id) . '" for="' . esc_attr($input_id) . '" class="cf7m-phone-label">';
            $html .= esc_html($label);
            if ($is_required) {
                $html .= '<span class="cf7m-phone-required" aria-hidden="true"> *</span>';
            }
            $html .= '</label>';
        }
        if ($description !== '') {
            $html .= '<div class="cf7m-phone-description" id="' . esc_attr($desc_id) . '">' . esc_html($description) . '</div>';
        }
        $html .= '<span class="cf7m-phone-number" data-name="' . esc_attr($name) . '">';
        $html .= '<span class="cf7m-phone-combo">';
        $html .= '<button type="button" class="cf7m-phone-trigger" aria-haspopup="listbox" aria-expanded="false" aria-label="' . esc_attr__('Select country', 'cf7-styler-for-divi') . '">';
        $html .= '<span class="cf7m-phone-flag">' . $initial_flag . '</span>';
        $html .= '<span class="cf7m-phone-dial">' . esc_html($initial_dial) . '</span>';
        $html .= '<span class="cf7m-phone-caret" aria-hidden="true"></span>';
        $html .= '</button>';

        // Single visible & submitted input. Pre-filled with the dial code + a
        // space so the user types digits directly after; without JS, this still
        // posts whatever they type.
        $aria_desc = $description !== '' ? ' aria-describedby="' . esc_attr($desc_id) . '"' : '';
        $html .= sprintf(
            '<input type="tel" id="%s" name="%s" value="%s " class="cf7m-phone-input wpcf7-form-control" autocomplete="tel" inputmode="tel" data-default-country="%s" aria-label="%s"%s%s%s>',
            esc_attr($input_id),
            esc_attr($name),
            esc_attr($initial_dial),
            esc_attr($default_country),
            esc_attr($label !== '' ? $label : __('Phone number', 'cf7-styler-for-divi')),
            $aria_desc,
            $aria_required,
            $placeholder_attr . $required_attr
        );

        // Sidecar so server-side code can route on country if it wants.
        $html .= sprintf(
            '<input type="hidden" name="%s__country" value="%s" class="cf7m-phone-country">',
            esc_attr($name),
            esc_attr($default_country)
        );

        $html .= '</span></span></span></span>';

        return $html;
    }

    /**
     * Get flag emoji from ISO 3166-1 alpha-2 (e.g. BD -> 🇧🇩).
     *
     * @param string $iso2 Two-letter country code.
     * @return string Flag emoji or empty string.
     */
    private static function flag_emoji($iso2)
    {
        $iso2 = strtoupper((string) $iso2);
        if (strlen($iso2) !== 2) {
            return '';
        }
        $a = 0x1F1E6 - 65; // Regional indicator A.
        $c1 = $a + ord($iso2[0]);
        $c2 = $a + ord($iso2[1]);
        if (function_exists('mb_chr')) {
            return mb_chr($c1, 'UTF-8') . mb_chr($c2, 'UTF-8');
        }
        return '';
    }

    /**
     * Register CF7 tag generator for [cf7m-phone].
     */
    public function add_tag_generators()
    {
        if (class_exists('WPCF7_TagGenerator')) {
            \WPCF7_TagGenerator::get_instance()->add(
                'cf7m-phone',
                __('phone number', 'cf7-styler-for-divi'),
                [$this, 'tag_generator_callback'],
                ['version' => '2']
            );
        }
    }

    /**
     * Tag generator callback for [cf7m-phone].
     *
     * @param \WPCF7_ContactForm $contact_form
     * @param string $options
     */
    public function tag_generator_callback($contact_form, $options = '')
    {
        ?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e('Phone Number', 'cf7-styler-for-divi'); ?></legend>
                <table class="form-table"><tbody>
                    <tr>
                        <th><?php esc_html_e('Field type', 'cf7-styler-for-divi'); ?></th>
                        <td><label><input type="checkbox" name="required" id="cf7m-phone-required"> <?php esc_html_e('Required', 'cf7-styler-for-divi'); ?></label></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-phone-name"><?php esc_html_e('Name', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" name="name" id="cf7m-phone-name" class="tg-name oneline" placeholder="phone"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-phone-default"><?php esc_html_e('Default country', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" name="default" id="cf7m-phone-default" class="oneline" value="US" maxlength="2" placeholder="US"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-phone-label"><?php esc_html_e('Label', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" name="label" id="cf7m-phone-label" class="oneline" placeholder="<?php esc_attr_e('Phone Number', 'cf7-styler-for-divi'); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-phone-description"><?php esc_html_e('Description', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" name="description" id="cf7m-phone-description" class="oneline" placeholder="<?php esc_attr_e('Enter your phone number.', 'cf7-styler-for-divi'); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-phone-placeholder"><?php esc_html_e('Placeholder', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" name="placeholder" id="cf7m-phone-placeholder" class="oneline" placeholder=""></td>
                    </tr>
                </tbody></table>
            </fieldset>
        </div>
        <div class="insert-box">
            <input type="text" name="cf7m-phone" class="tag code" readonly="readonly" onfocus="this.select()" value="[cf7m-phone phone default:US label:&quot;Phone Number&quot; description:&quot;Enter your phone number.&quot;]">
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>">
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue phone number front-end assets.
     */
    public function enqueue_assets()
    {
        if (!Feature_Base::page_has_cf7_form()) {
            return;
        }

        $base    = defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0';
        $shared  = CF7M_PLUGIN_PATH . 'assets/lite/css/cf7m-lite-forms.css';
        $css     = CF7M_PLUGIN_PATH . 'assets/lite/css/cf7m-phone-number.css';
        $js      = CF7M_PLUGIN_PATH . 'assets/lite/js/cf7m-phone-number.js';
        $shared_ver = $base . (file_exists($shared) ? '.' . filemtime($shared) : '');
        $css_ver    = $base . (file_exists($css) ? '.' . filemtime($css) : '');
        $js_ver     = $base . (file_exists($js) ? '.' . filemtime($js) : '');

        // Shared field tokens (focus ring, baseline) live in cf7m-lite-forms.css.
        wp_enqueue_style(
            'cf7m-lite-forms',
            CF7M_PLUGIN_URL . 'assets/lite/css/cf7m-lite-forms.css',
            [],
            $shared_ver
        );
        wp_enqueue_style(
            'cf7m-phone-number',
            CF7M_PLUGIN_URL . 'assets/lite/css/cf7m-phone-number.css',
            ['cf7m-lite-forms'],
            $css_ver
        );
        wp_enqueue_script(
            'cf7m-phone-number',
            CF7M_PLUGIN_URL . 'assets/lite/js/cf7m-phone-number.js',
            [],
            $js_ver,
            true
        );
    }
}
