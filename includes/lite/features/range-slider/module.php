<?php
/**
 * Range Slider Module.
 * Processes [cf7m-range] shortcodes in form markup (same pattern as multi-steps).
 *
 * @package CF7_Mate\Lite\Features\Range_Slider
 * @since 3.0.0
 */

namespace CF7_Mate\Lite\Features\Range_Slider;

use CF7_Mate\Lite\Feature_Base;
use CF7_Mate\Lite\Traits\Shortcode_Atts_Trait;
use CF7_Mate\Lite\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Range_Slider extends Feature_Base
{
    use Shortcode_Atts_Trait;
    use Singleton;

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
     * Server-side validation for [cf7m-range*] fields. A submission missing
     * the field entirely (e.g. JS removed it) counts as empty.
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
        if (!is_string($markup) || strpos($markup, '[cf7m-range*') === false) {
            return $result;
        }

        if (preg_match_all('/\[cf7m-range\*\s+([^\]]+)\]/', $markup, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $parts = preg_split('/\s+/', trim($match[1]));
                $name  = !empty($parts[0]) ? sanitize_key($parts[0]) : '';
                if (!$name) {
                    continue;
                }
                if (!isset($_POST[$name]) || trim(wp_unslash($_POST[$name])) === '') { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                    $result->invalidate(
                        ['name' => $name, 'type' => 'cf7m-range*'],
                        wpcf7_get_message('invalid_required')
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Process [cf7m-range name min:0 max:100 step:1 default:50] shortcodes.
     *
     * @param string $form
     * @return string
     */
    public function process_shortcodes($form)
    {
        if (strpos($form, '[cf7m-range') === false) {
            return $form;
        }

        // Match: [cf7m-range name min:0 max:100] or [cf7m-range* name ...]
        $form = preg_replace_callback(
            '/\[cf7m-range\*?\s+([^\]]+)\]/',
            [$this, 'render_range_slider'],
            $form
        );

        return $form;
    }

    /**
     * Render a single range slider field.
     *
     * @param array $matches Regex matches
     * @return string HTML output
     */
    public function render_range_slider($matches)
    {
        $raw_atts = trim($matches[1]);
        $is_required = strpos($matches[0], '[cf7m-range*') === 0;

        // Parse attributes: first word is name, rest are key:value pairs
        $parts = preg_split('/\s+/', $raw_atts);
        $name = !empty($parts[0]) ? sanitize_key($parts[0]) : 'amount';

        $min = 0.0;
        $max = 100.0;
        $step = 1.0;
        $default = 50.0;
        $prefix = '';
        $suffix = '';
        $track_color = '';
        $thumb_color = '';

        for ($i = 1; $i < count($parts); $i++) {
            $part = $parts[$i];
            if (strpos($part, 'min:') === 0) {
                $min = (float) substr($part, 4);
            } elseif (strpos($part, 'max:') === 0) {
                $max = (float) substr($part, 4);
            } elseif (strpos($part, 'step:') === 0) {
                $step = (float) substr($part, 5);
            } elseif (strpos($part, 'default:') === 0) {
                $default = (float) substr($part, 8);
            } elseif (strpos($part, 'prefix:') === 0) {
                $prefix = substr($part, 7);
            } elseif (strpos($part, 'suffix:') === 0) {
                $suffix = substr($part, 7);
            } elseif (strpos($part, 'track:') === 0) {
                $track_color = substr($part, 6);
            } elseif (strpos($part, 'thumb:') === 0) {
                $thumb_color = substr($part, 6);
            }
        }

        if ($step <= 0) {
            $step = 1.0;
        }
        $default = max($min, min($default, $max));
        $track_color = $this->sanitize_color($track_color);
        $thumb_color = $this->sanitize_color($thumb_color);

        // Normalise numeric output: integer if value is whole, else trim
        // trailing zeros so step=1 → "50" and step=0.5 → "2.5".
        $fmt = function ($n) {
            $n = (float) $n;
            if (floor($n) === $n) {
                return (string) (int) $n;
            }
            return rtrim(rtrim(sprintf('%.4F', $n), '0'), '.');
        };
        $min_s     = $fmt($min);
        $max_s     = $fmt($max);
        $step_s    = $fmt($step);
        $default_s = $fmt($default);

        // Build style attribute
        $style_parts = [];
        if ($track_color !== '') {
            $style_parts[] = '--cf7m-range-track:' . esc_attr($track_color);
        }
        if ($thumb_color !== '') {
            $style_parts[] = '--cf7m-range-thumb:' . esc_attr($thumb_color);
        }
        $style = !empty($style_parts) ? ' style="' . implode(';', $style_parts) . '"' : '';

        $required_attr = $is_required ? ' data-required="true"' : '';

        // Build HTML
        $html = sprintf(
            '<span class="wpcf7-form-control-wrap wpcf7-form-control-wrap-%1$s" data-name="%1$s">',
            esc_attr($name)
        );
        $html .= sprintf(
            '<span class="cf7m-range-slider" data-prefix="%s" data-suffix="%s" data-name="%s"%s%s>',
            esc_attr($prefix),
            esc_attr($suffix),
            esc_attr($name),
            $required_attr,
            $style
        );
        $html .= sprintf(
            '<input type="range" name="%s" min="%s" max="%s" step="%s" value="%s" class="cf7m-range-input wpcf7-form-control">',
            esc_attr($name),
            esc_attr($min_s),
            esc_attr($max_s),
            esc_attr($step_s),
            esc_attr($default_s)
        );
        $html .= sprintf(
            '<span class="cf7m-range-value" aria-live="polite">%s</span>',
            esc_html($prefix . $default_s . $suffix)
        );
        $html .= '</span></span>';

        return $html;
    }

    /**
     * Sanitize color value.
     *
     * @param string $value
     * @return string
     */
    private function sanitize_color($value)
    {
        $value = trim((string) $value);
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value)) {
            return $value;
        }
        return '';
    }

    /**
     * Register CF7 tag generator for [cf7m-range].
     */
    public function add_tag_generators()
    {
        if (class_exists('WPCF7_TagGenerator')) {
            \WPCF7_TagGenerator::get_instance()->add(
                'cf7m-range',
                __('range slider', 'cf7-styler-for-divi'),
                [$this, 'tag_generator_callback'],
                ['version' => '2']
            );
        }
    }

    /**
     * Tag generator callback for [cf7m-range].
     *
     * @param \WPCF7_ContactForm $contact_form
     * @param string $options
     */
    public function tag_generator_callback($contact_form, $options = '')
    {
        ?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e('Range Slider', 'cf7-styler-for-divi'); ?></legend>
                <table class="form-table"><tbody>
                    <tr>
                        <th><?php esc_html_e('Field type', 'cf7-styler-for-divi'); ?></th>
                        <td><label><input type="checkbox" name="required" id="cf7m-range-required"> <?php esc_html_e('Required', 'cf7-styler-for-divi'); ?></label></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-range-name"><?php esc_html_e('Name', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" name="name" id="cf7m-range-name" class="tg-name oneline" placeholder="amount"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-range-min"><?php esc_html_e('Min', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="number" name="min" id="cf7m-range-min" class="oneline" value="0"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-range-max"><?php esc_html_e('Max', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="number" name="max" id="cf7m-range-max" class="oneline" value="100"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-range-step"><?php esc_html_e('Step', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="number" name="step" id="cf7m-range-step" class="oneline" value="1" min="1"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-range-default"><?php esc_html_e('Default Value', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="number" name="default" id="cf7m-range-default" class="oneline" value="50"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-range-prefix"><?php esc_html_e('Prefix', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" name="prefix" id="cf7m-range-prefix" class="oneline" placeholder="$"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-range-suffix"><?php esc_html_e('Suffix', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" name="suffix" id="cf7m-range-suffix" class="oneline" placeholder="%"></td>
                    </tr>
                </tbody></table>
            </fieldset>
        </div>
        <div class="insert-box">
            <input type="text" name="cf7m-range" class="tag code" readonly="readonly" onfocus="this.select()" value="[cf7m-range amount min:0 max:100 step:1 default:50]">
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>">
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue range slider front-end assets.
     */
    public function enqueue_assets()
    {
        if (!Feature_Base::page_has_cf7_form()) {
            return;
        }

        $base    = defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0';
        $css     = CF7M_PLUGIN_PATH . 'assets/lite/css/cf7m-lite-forms.css';
        $js      = CF7M_PLUGIN_PATH . 'assets/lite/js/cf7m-range-slider.js';
        $css_ver = $base . (file_exists($css) ? '.' . filemtime($css) : '');
        $js_ver  = $base . (file_exists($js) ? '.' . filemtime($js) : '');

        wp_enqueue_style(
            'cf7m-lite-forms',
            CF7M_PLUGIN_URL . 'assets/lite/css/cf7m-lite-forms.css',
            [],
            $css_ver
        );
        wp_enqueue_script(
            'cf7m-range-slider',
            CF7M_PLUGIN_URL . 'assets/lite/js/cf7m-range-slider.js',
            [],
            $js_ver,
            true
        );
    }
}
