<?php
/**
 * Image Module – insert images into CF7 forms.
 *
 * @package CF7_Mate\Lite\Features\Image
 * @since 3.0.0
 */

namespace CF7_Mate\Lite\Features\Image;

use CF7_Mate\Lite\Feature_Base;
use CF7_Mate\Lite\Traits\Shortcode_Atts_Trait;
use CF7_Mate\Lite\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Image extends Feature_Base
{
    use Shortcode_Atts_Trait;
    use Singleton;

    /** @inheritdoc */
    protected function __construct()
    {
        parent::__construct();
    }

    /** @inheritdoc */
    protected function init()
    {
        add_filter('wpcf7_form_elements', [$this, 'process_shortcodes'], 10, 1);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generators'], 25);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_image_tag_admin_script'], 20);
    }

    /**
     * Enqueue media and script for image uploader in CF7 tag generator.
     */
    public function enqueue_image_tag_admin_script($hook)
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || strpos($screen->id ?? '', 'wpcf7') === false) {
            return;
        }
        wp_enqueue_media();
        $path = CF7M_PLUGIN_PATH . 'assets/js/cf7m-image-tag-admin.js';
        if (!file_exists($path)) {
            return;
        }
        wp_enqueue_script(
            'cf7m-image-tag-admin',
            CF7M_PLUGIN_URL . 'assets/js/cf7m-image-tag-admin.js',
            ['jquery'],
            defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0',
            true
        );
    }

    public function process_shortcodes($form)
    {
        if (strpos($form, '[cf7m-image') === false) {
            return $form;
        }

        return preg_replace_callback(
            '/\[cf7m-image\s*([^\]]*)\]/',
            [$this, 'render_image'],
            $form
        );
    }

    /**
     * @param array $matches
     * @return string
     */
    public function render_image($matches)
    {
        $atts = $this->parse_atts(isset($matches[1]) ? $matches[1] : '');
        $src = isset($atts['src']) ? esc_url($atts['src']) : '';
        $alt = isset($atts['alt']) ? sanitize_text_field($atts['alt']) : '';
        $width = isset($atts['width']) ? absint($atts['width']) : 0;
        $height = isset($atts['height']) ? absint($atts['height']) : 0;
        $class = isset($atts['class']) ? sanitize_html_class($atts['class']) : 'cf7m-image';

        if (empty($src)) {
            return '';
        }

        // Build the <img> tag from a fixed attribute whitelist — never use
        // user-supplied keys (only values), so an attacker can't sneak in
        // `onclick` etc. via the shortcode.
        $dims = '';
        if ($width > 0) {
            $dims .= sprintf(' width="%d"', $width);
        }
        if ($height > 0) {
            $dims .= sprintf(' height="%d"', $height);
        }
        $html = sprintf(
            '<img src="%s" alt="%s" class="%s" loading="lazy"%s />',
            esc_url($src),
            esc_attr($alt),
            esc_attr($class),
            $dims
        );

        return '<span class="cf7m-image-wrap">' . $html . '</span>';
    }

    public function add_tag_generators()
    {
        if (!class_exists('WPCF7_TagGenerator')) {
            return;
        }
        $tg = \WPCF7_TagGenerator::get_instance();
        $tg->add('cf7m-image', __('image', 'cf7-styler-for-divi'), [$this, 'tag_generator'], ['version' => '2']);
    }

    /** @param \WPCF7_ContactForm $contact_form */
    public function tag_generator($contact_form, $options = '')
    {
?>
        <div class="cf7m-tag-panel cf7m-image-tag-panel">
            <div class="control-box">
                <fieldset>
                    <legend><?php esc_html_e('Image', 'cf7-styler-for-divi'); ?></legend>
                    <table class="form-table">
                        <tbody>
                            <tr class="cf7m-image-upload-row">
                                <th scope="row"><label for="cf7m-image-src"><?php esc_html_e('Image URL', 'cf7-styler-for-divi'); ?></label></th>
                                <td>
                                    <input type="url" id="cf7m-image-src" name="src" class="oneline cf7m-image-src-input" placeholder="https://" />
                                    <button type="button" class="button cf7m-image-upload-trigger"><?php esc_html_e('Select Image', 'cf7-styler-for-divi'); ?></button>
                                    <p class="description" style="margin:6px 0 0 0; width:100%;"><?php esc_html_e('Enter a URL or use "Select Image" to choose from the media library.', 'cf7-styler-for-divi'); ?></p>
                                    <img class="cf7m-image-preview" src="" alt="" style="display:none;" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cf7m-image-alt"><?php esc_html_e('Alt text', 'cf7-styler-for-divi'); ?></label></th>
                                <td><input type="text" id="cf7m-image-alt" name="alt" class="oneline cf7m-image-alt-input" placeholder="<?php esc_attr_e('Describe the image', 'cf7-styler-for-divi'); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cf7m-image-width"><?php esc_html_e('Width', 'cf7-styler-for-divi'); ?></label></th>
                                <td><input type="number" id="cf7m-image-width" name="width" class="oneline" value="0" min="0" /> px (0 = auto)</td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cf7m-image-height"><?php esc_html_e('Height', 'cf7-styler-for-divi'); ?></label></th>
                                <td><input type="number" id="cf7m-image-height" name="height" class="oneline" value="0" min="0" /> px (0 = auto)</td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </div>
            <div class="insert-box">
                <input type="text" name="cf7m-image" class="tag code" readonly="readonly" onfocus="this.select()" value="[cf7m-image src:url alt:text]" />
                <div class="submitbox"><input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>" /></div>
            </div>
        </div>
<?php
    }
}
