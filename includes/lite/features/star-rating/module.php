<?php

namespace CF7_Mate\Lite\Features\Star_Rating;

use CF7_Mate\Lite\Feature_Base;
use CF7_Mate\Lite\Traits\Shortcode_Atts_Trait;
use CF7_Mate\Lite\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Star_Rating extends Feature_Base
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
     * Server-side validation for [cf7m-star*] fields. A value of "0" (the
     * default for the hidden input) means "no rating chosen" — treat as empty.
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
        if (!is_string($markup) || strpos($markup, '[cf7m-star*') === false) {
            return $result;
        }

        if (preg_match_all('/\[cf7m-star\*\s+([^\]]+)\]/', $markup, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $parts = preg_split('/\s+/', trim($match[1]));
                $name  = !empty($parts[0]) ? sanitize_key($parts[0]) : '';
                if (!$name) {
                    continue;
                }
                $posted = isset($_POST[$name]) ? trim(wp_unslash($_POST[$name])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
                if ($posted === '' || $posted === '0') {
                    $result->invalidate(
                        ['name' => $name, 'type' => 'cf7m-star*'],
                        wpcf7_get_message('invalid_required')
                    );
                }
            }
        }

        return $result;
    }

    public function process_shortcodes($form)
    {
        if (strpos($form, '[cf7m-star') === false) {
            return $form;
        }

        // Match: [cf7m-star name max:5 default:0] or [cf7m-star* name max:5]
        $form = preg_replace_callback(
            '/\[cf7m-star\*?\s+([^\]]+)\]/',
            [$this, 'render_star_rating'],
            $form
        );

        return $form;
    }

    public function render_star_rating($matches)
    {
        $raw_atts = trim($matches[1]);
        $is_required = strpos($matches[0], '[cf7m-star*') === 0;

        // Parse attributes: first word is name, rest are key:value pairs
        $parts = preg_split('/\s+/', $raw_atts);
        $name = !empty($parts[0]) ? sanitize_key($parts[0]) : 'rating';

        $max = 5;
        $default = 0;
        $color = '';

        for ($i = 1; $i < count($parts); $i++) {
            $part = $parts[$i];
            if (strpos($part, 'max:') === 0) {
                $max = (int) substr($part, 4);
            } elseif (strpos($part, 'default:') === 0) {
                $default = (int) substr($part, 8);
            } elseif (strpos($part, 'color:') === 0) {
                $color = substr($part, 6);
            }
        }

        $max = max(1, min(10, $max));
        $default = max(0, min($default, $max));
        $color = $this->sanitize_color($color);

        // Custom-colour escape hatch — applied via CSS custom properties so
        // CSS can draw the star sprite from a single mask-image asset.
        $style = '';
        if ($color !== '') {
            $style = ' style="--cf7m-star-on:' . esc_attr($color) . ';--cf7m-star-hover:' . esc_attr($color) . ';"';
        }

        $required_attr = $is_required ? ' data-required="true"' : '';

        $html = sprintf(
            '<span class="wpcf7-form-control-wrap wpcf7-form-control-wrap-%1$s" data-name="%1$s">',
            esc_attr($name)
        );
        $html .= sprintf(
            '<span class="cf7m-star-rating" data-max="%d" data-value="%d" data-name="%s"%s%s role="radiogroup" aria-label="%s">',
            $max,
            $default,
            esc_attr($name),
            $required_attr,
            $style,
            esc_attr__('Star rating', 'cf7-styler-for-divi')
        );
        $html .= sprintf(
            '<input type="hidden" name="%s" value="%d" class="cf7m-star-input" data-cf7m-star-input>',
            esc_attr($name),
            $default
        );

        for ($i = 1; $i <= $max; $i++) {
            $active   = $i <= $default ? ' cf7m-star--on' : '';
            $checked  = $i === $default ? 'true' : 'false';
            $tabindex = ($default === 0 && $i === 1) || $i === $default ? '0' : '-1';
            $html .= sprintf(
                '<button type="button" class="cf7m-star%s" data-value="%d" role="radio" aria-checked="%s" tabindex="%s" aria-label="%s"></button>',
                $active,
                $i,
                $checked,
                $tabindex,
                /* translators: %d: star rating number (e.g. 1, 2, 3) */
                sprintf(esc_attr__('Rate %d star', 'cf7-styler-for-divi'), $i)
            );
        }

        $html .= '</span></span>';

        return $html;
    }

    private function sanitize_color($value)
    {
        $value = trim((string) $value);
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value)) {
            return $value;
        }
        return '';
    }

    public function add_tag_generators()
    {
        if (class_exists('WPCF7_TagGenerator')) {
            \WPCF7_TagGenerator::get_instance()->add(
                'cf7m-star',
                __('star rating', 'cf7-styler-for-divi'),
                [$this, 'tag_generator_callback'],
                ['version' => '2']
            );
        }
    }

    public function tag_generator_callback($contact_form, $options = '')
    {
?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e('Star Rating', 'cf7-styler-for-divi'); ?></legend>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th><?php esc_html_e('Field type', 'cf7-styler-for-divi'); ?></th>
                            <td><label><input type="checkbox" name="required" id="cf7m-star-required"> <?php esc_html_e('Required', 'cf7-styler-for-divi'); ?></label></td>
                        </tr>
                        <tr>
                            <th><label for="cf7m-star-name"><?php esc_html_e('Name', 'cf7-styler-for-divi'); ?></label></th>
                            <td><input type="text" name="name" id="cf7m-star-name" class="tg-name oneline" placeholder="rating"></td>
                        </tr>
                        <tr>
                            <th><label for="cf7m-star-max"><?php esc_html_e('Max Stars', 'cf7-styler-for-divi'); ?></label></th>
                            <td><input type="number" name="max" id="cf7m-star-max" class="oneline" value="5" min="1" max="10"></td>
                        </tr>
                        <tr>
                            <th><label for="cf7m-star-default"><?php esc_html_e('Default Value', 'cf7-styler-for-divi'); ?></label></th>
                            <td><input type="number" name="default" id="cf7m-star-default" class="oneline" value="0" min="0"></td>
                        </tr>
                        <tr>
                            <th><label for="cf7m-star-color"><?php esc_html_e('Star Color', 'cf7-styler-for-divi'); ?></label></th>
                            <td><input type="text" name="color" id="cf7m-star-color" class="oneline" placeholder="#f59e0b"></td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>
        <div class="insert-box">
            <input type="text" name="cf7m-star" class="tag code" readonly="readonly" onfocus="this.select()" value="[cf7m-star rating max:5 default:0]">
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>">
            </div>
        </div>
<?php
    }

    /**
     * Enqueue star rating front-end assets.
     */
    public function enqueue_assets()
    {
        if (!Feature_Base::page_has_cf7_form()) {
            return;
        }

        $base    = defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0';
        $css     = CF7M_PLUGIN_PATH . 'assets/lite/css/cf7m-lite-forms.css';
        $js      = CF7M_PLUGIN_PATH . 'assets/lite/js/cf7m-star-rating.js';
        $css_ver = $base . (file_exists($css) ? '.' . filemtime($css) : '');
        $js_ver  = $base . (file_exists($js) ? '.' . filemtime($js) : '');

        wp_enqueue_style(
            'cf7m-lite-forms',
            CF7M_PLUGIN_URL . 'assets/lite/css/cf7m-lite-forms.css',
            [],
            $css_ver
        );
        wp_enqueue_script(
            'cf7m-star-rating',
            CF7M_PLUGIN_URL . 'assets/lite/js/cf7m-star-rating.js',
            [],
            $js_ver,
            true
        );
    }
}
