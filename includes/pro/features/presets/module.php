<?php

/**
 * Form Design Presets – shortcode wrapper.
 * [cf7m-presets style="sky"]...form fields...[/cf7m-presets]
 * Renders with wrapper .cf7-mate-preset.{style} so preset CSS applies.
 *
 * @package CF7_Mate\Features\Presets
 * @since   3.0.0
 */

namespace CF7_Mate\Features\Presets;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Presets extends Pro_Feature_Base
{
    use Singleton;

    /** Allowed preset slugs */
    const PRESET_SLUGS = ['sky', 'classic', 'box', 'minimal', 'dark', 'modern', 'rounded'];

    protected function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        add_filter('wpcf7_form_elements', [$this, 'process_presets_shortcode'], 5, 1);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_preset_styles'], 20);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generator'], 30);
    }

    /**
     * Process [cf7m-presets style="sky"]...[/cf7m-presets] in form template.
     *
     * @param string $form Form HTML/template.
     * @return string
     */
    public function process_presets_shortcode($form)
    {
        if (strpos($form, '[cf7m-presets') === false) {
            return $form;
        }

        $form = preg_replace_callback(
            '/\[cf7m-presets\s+([^\]]+)\](.*?)\[\/cf7m-presets\]/s',
            [$this, 'render_preset_wrapper'],
            $form
        );

        return $form;
    }


    public function render_preset_wrapper($matches)
    {
        $raw_attrs = isset($matches[1]) ? trim($matches[1]) : '';
        $content   = isset($matches[2]) ? $matches[2] : '';

        $style = $this->parse_style_attr($raw_attrs);
        if (!in_array($style, self::PRESET_SLUGS, true)) {
            return $content;
        }

        $class = 'cf7-mate-preset cf7-mate-preset--' . sanitize_html_class($style);

        return sprintf('<div class="%1$s">%2$s</div>', esc_attr($class), $content);
    }

    /**
     * Parse style="sky" or style:sky from shortcode attrs.
     *
     * @param string $raw Raw attribute string.
     * @return string Slug or 'classic' as fallback.
     */
    private function parse_style_attr($raw)
    {
        if (preg_match('/style\s*[=:]\s*["\']?([a-z0-9_-]+)["\']?/i', $raw, $m)) {
            $slug = sanitize_key($m[1]);
            if (in_array($slug, self::PRESET_SLUGS, true)) {
                return $slug;
            }
        }
        return 'classic';
    }

    /**
     * Enqueue preset CSS when a CF7 form is on the page.
     */
    public function enqueue_preset_styles()
    {
        if (!Pro_Feature_Base::page_has_cf7_form()) {
            return;
        }

        wp_enqueue_style(
            'cf7m-presets',
            CF7M_PLUGIN_URL . 'assets/pro/css/cf7m-presets.css',
            [],
            defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0'
        );
    }

    /**
     * Register CF7 tag generator for [cf7m-presets].
     */
    public function add_tag_generator()
    {
        if (!class_exists('WPCF7_TagGenerator')) {
            return;
        }
        $tg = \WPCF7_TagGenerator::get_instance();
        $tg->add('cf7m-presets', __('style presets', 'cf7-styler-for-divi'), [$this, 'tag_generator_presets'], ['version' => '2']);
    }

    /**
     * Tag generator UI for presets.
     */
    public function tag_generator_presets($contact_form, $options = '')
    {
        $presets = [
            'sky'     => __('Sky', 'cf7-styler-for-divi'),
            'classic' => __('Classic', 'cf7-styler-for-divi'),
            'box'     => __('Box', 'cf7-styler-for-divi'),
            'minimal' => __('Minimal', 'cf7-styler-for-divi'),
            'dark'    => __('Dark', 'cf7-styler-for-divi'),
            'modern'  => __('Modern', 'cf7-styler-for-divi'),
            'rounded' => __('Rounded', 'cf7-styler-for-divi'),
        ];
?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e('Form Design Preset', 'cf7-styler-for-divi'); ?></legend>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th><label for="cf7m-preset-style"><?php esc_html_e('Style', 'cf7-styler-for-divi'); ?></label></th>
                            <td>
                                <select id="cf7m-preset-style" name="style" class="oneline">
                                    <?php foreach ($presets as $slug => $label) : ?>
                                        <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="description"><?php esc_html_e('Wrap your form fields in this shortcode to apply a preset style. Put all fields inside the opening and closing tags.', 'cf7-styler-for-divi'); ?></p>
            </fieldset>
        </div>
        <div class="insert-box">
            <input type="text" name="cf7m-presets" class="tag code" readonly="readonly" onfocus="this.select()" value='[cf7m-presets style="sky"]

[/cf7m-presets]'>
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>">
            </div>
        </div>
<?php
    }
}
