<?php
/**
 * Heading Module – H1–H6 headings in CF7 forms.
 * Supports both self-closing and wrapper syntax:
 *   [cf7m-heading text="My Title" tag:h3]
 *   [cf7m-heading level:3]My Title[/cf7m-heading]
 *
 * @package CF7_Mate\Features\Heading
 * @since 3.0.0
 */

namespace CF7_Mate\Features\Heading;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Shortcode_Atts_Trait;
use CF7_Mate\Pro\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Heading extends Pro_Feature_Base
{
    use Shortcode_Atts_Trait;
    use Singleton;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        add_filter('wpcf7_form_elements', [$this, 'process_shortcodes'], 15, 1);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generators'], 25);
    }

    /**
     * Process [cf7m-heading] shortcodes (both self-closing and wrapper).
     *
     * @param string $form
     * @return string
     */
    public function process_shortcodes($form)
    {
        if (strpos($form, '[cf7m-heading') === false) {
            return $form;
        }

        // First: wrapper syntax [cf7m-heading ...]content[/cf7m-heading]
        $form = preg_replace_callback(
            '/\[cf7m-heading\s*([^\]]*)\](.*?)\[\/cf7m-heading\]/s',
            [$this, 'render_wrapper_heading'],
            $form
        );

        // Second: self-closing syntax [cf7m-heading text="..." tag:h3]
        $form = preg_replace_callback(
            '/\[cf7m-heading\s+([^\]]+)\]/',
            [$this, 'render_self_closing_heading'],
            $form
        );

        return $form;
    }

    /**
     * Render wrapper syntax: [cf7m-heading level:3]Content[/cf7m-heading]
     *
     * @param array $matches
     * @return string
     */
    public function render_wrapper_heading($matches)
    {
        $atts = $this->parse_atts(isset($matches[1]) ? $matches[1] : '');
        $content = isset($matches[2]) ? trim($matches[2]) : '';

        $level = isset($atts['level']) ? absint($atts['level']) : 3;
        if ($level < 1 || $level > 6) {
            $level = 3;
        }

        return $this->build_heading_html('h' . $level, $content, $atts);
    }

    /**
     * Render self-closing syntax: [cf7m-heading text="Title" tag:h3]
     *
     * @param array $matches
     * @return string
     */
    public function render_self_closing_heading($matches)
    {
        $raw = trim($matches[1]);

        // Parse attributes
        $text = '';
        $tag = 'h3';
        $atts = [];

        // Extract text="..." or text:'...'
        if (preg_match('/text[=:]["\']([^"\']+)["\']/', $raw, $m)) {
            $text = $m[1];
        } elseif (preg_match('/text[=:](\S+)/', $raw, $m)) {
            $text = $m[1];
        }

        // Extract tag:h1-h6
        if (preg_match('/tag[=:]h([1-6])/', $raw, $m)) {
            $tag = 'h' . $m[1];
        }

        // Extract level:1-6 (alternative to tag)
        if (preg_match('/level[=:]([1-6])/', $raw, $m)) {
            $tag = 'h' . $m[1];
        }

        // Extract style attributes
        if (preg_match('/font_size[=:]["\']?([^"\'}\s]+)["\']?/', $raw, $m)) {
            $atts['font_size'] = $m[1];
        }
        if (preg_match('/font_family[=:]["\']([^"\']+)["\']/', $raw, $m)) {
            $atts['font_family'] = $m[1];
        }
        if (preg_match('/color[=:](#[A-Fa-f0-9]{3,6}|rgb[a]?\([^)]+\))/', $raw, $m)) {
            $atts['text_color'] = $m[1];
        }
        if (preg_match('/text_color[=:](#[A-Fa-f0-9]{3,6}|rgb[a]?\([^)]+\))/', $raw, $m)) {
            $atts['text_color'] = $m[1];
        }

        if (empty($text)) {
            return '';
        }

        return $this->build_heading_html($tag, $text, $atts);
    }

    /**
     * Build heading HTML.
     *
     * @param string $tag h1-h6
     * @param string $content Text content
     * @param array $atts Style attributes
     * @return string
     */
    private function build_heading_html($tag, $content, $atts)
    {
        $class = 'cf7m-heading';
        $style_parts = [];

        if (!empty($atts['font_size'])) {
            $style_parts[] = 'font-size:' . esc_attr(wp_strip_all_tags(trim($atts['font_size'])));
        }
        if (!empty($atts['font_family'])) {
            $style_parts[] = 'font-family:' . esc_attr(wp_strip_all_tags($atts['font_family']));
        }
        if (!empty($atts['text_color']) && $this->is_valid_color($atts['text_color'])) {
            $style_parts[] = 'color:' . esc_attr(trim($atts['text_color']));
        }

        $style_attr = !empty($style_parts) ? ' style="' . implode(';', $style_parts) . '"' : '';

        return sprintf(
            '<%1$s class="%2$s"%3$s>%4$s</%1$s>',
            esc_attr($tag),
            esc_attr($class),
            $style_attr,
            wp_kses_post($content)
        );
    }

    /**
     * Check if value is a valid CSS color.
     *
     * @param string $value
     * @return bool
     */
    private function is_valid_color($value)
    {
        $value = trim($value);
        return (bool) preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value)
            || preg_match('/^rgb\(|^rgba\(/', $value);
    }

    public function add_tag_generators()
    {
        if (!class_exists('WPCF7_TagGenerator')) {
            return;
        }
        \WPCF7_TagGenerator::get_instance()->add(
            'cf7m-heading',
            __('heading', 'cf7-styler-for-divi'),
            [$this, 'tag_generator'],
            ['version' => '2']
        );
    }

    public function tag_generator($contact_form, $options = '')
    {
        ?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e('Heading', 'cf7-styler-for-divi'); ?></legend>
                <table class="form-table"><tbody>
                    <tr>
                        <th><label for="cf7m-heading-text"><?php esc_html_e('Text', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" id="cf7m-heading-text" name="text" class="oneline" placeholder="Your heading text"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-heading-tag"><?php esc_html_e('Tag', 'cf7-styler-for-divi'); ?></label></th>
                        <td>
                            <select id="cf7m-heading-tag" name="tag" class="oneline">
                                <?php for ($i = 1; $i <= 6; $i++) : ?>
                                    <option value="h<?php echo esc_attr($i); ?>" <?php selected($i, 3); ?>>H<?php echo esc_html($i); ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cf7m-heading-color"><?php esc_html_e('Color', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="text" id="cf7m-heading-color" name="color" class="oneline" placeholder="#333333"></td>
                    </tr>
                </tbody></table>
            </fieldset>
        </div>
        <div class="insert-box">
            <input type="text" name="cf7m-heading" class="tag code" readonly="readonly" onfocus="this.select()" value='[cf7m-heading text:"Your Heading" tag:h3]'>
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>">
            </div>
        </div>
        <?php
    }
}
