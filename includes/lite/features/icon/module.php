<?php
/**
 * Icon Module – insert icons into CF7 forms.
 *
 * @package CF7_Mate\Lite\Features\Icon
 * @since 3.0.0
 */

namespace CF7_Mate\Lite\Features\Icon;

use CF7_Mate\Lite\Feature_Base;
use CF7_Mate\Lite\Traits\Shortcode_Atts_Trait;
use CF7_Mate\Lite\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Icon extends Feature_Base
{
    use Shortcode_Atts_Trait;
    use Singleton;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        add_filter('wpcf7_form_elements', [$this, 'process_shortcodes'], 10, 1);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generators'], 25);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_dashicons']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_icon_tag_admin_script'], 20);
    }

    public function enqueue_icon_tag_admin_script($hook)
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || strpos($screen->id ?? '', 'wpcf7') === false) {
            return;
        }
        wp_enqueue_style('dashicons');
        wp_enqueue_media();
        $path = CF7M_PLUGIN_PATH . 'assets/js/cf7m-icon-tag-admin.js';
        if (!file_exists($path)) {
            return;
        }
        wp_enqueue_script(
            'cf7m-icon-tag-admin',
            CF7M_PLUGIN_URL . 'assets/js/cf7m-icon-tag-admin.js',
            ['jquery'],
            defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0',
            true
        );
    }

    public function enqueue_dashicons()
    {
        global $post;
        if (!$post || !is_singular() || !has_shortcode($post->post_content, 'contact-form-7')) {
            return;
        }
        wp_enqueue_style('dashicons');
    }

    public function process_shortcodes($form)
    {
        if (strpos($form, '[cf7m-icon') === false) {
            return $form;
        }

        return preg_replace_callback(
            '/\[cf7m-icon\s*([^\]]*)\]/',
            [$this, 'render_icon'],
            $form
        );
    }

    public function render_icon($matches)
    {
        $atts = $this->parse_atts(isset($matches[1]) ? $matches[1] : '');
        $size = isset($atts['size']) ? absint($atts['size']) : 24;
        if ($size < 8 || $size > 96) {
            $size = 24;
        }

        // Layout/size lives in CSS via a single --cf7m-icon-size custom prop;
        // visual rules (display, vertical-align) are in cf7m-lite-forms.css.
        $size_var = sprintf(' style="--cf7m-icon-size:%dpx;"', $size);

        $src = isset($atts['src']) ? esc_url($atts['src']) : '';
        if ($src !== '') {
            return sprintf(
                '<span class="cf7m-icon cf7m-icon--img"%s><img src="%s" alt="" class="cf7m-icon-img" width="%d" height="%d" loading="lazy" /></span>',
                $size_var,
                $src,
                $size,
                $size
            );
        }

        $name = isset($atts['name']) ? sanitize_text_field($atts['name']) : 'dashicons-star-filled';
        if (strpos($name, 'dashicons-') !== 0) {
            $name = 'dashicons-star-filled';
        }

        return sprintf(
            '<span class="cf7m-icon cf7m-icon--font dashicons %s"%s aria-hidden="true"></span>',
            esc_attr($name),
            $size_var
        );
    }

    private static function get_browser_icons()
    {
        return [
            'dashicons-email', 'dashicons-email-alt', 'dashicons-phone', 'dashicons-location',
            'dashicons-location-alt', 'dashicons-calendar', 'dashicons-calendar-alt', 'dashicons-clock',
            'dashicons-admin-users', 'dashicons-id', 'dashicons-id-alt', 'dashicons-admin-generic',
            'dashicons-star-filled', 'dashicons-heart', 'dashicons-yes', 'dashicons-yes-alt',
            'dashicons-no', 'dashicons-no-alt', 'dashicons-plus', 'dashicons-minus', 'dashicons-dismiss',
            'dashicons-arrow-right', 'dashicons-arrow-left', 'dashicons-arrow-up', 'dashicons-arrow-down',
            'dashicons-external-link', 'dashicons-admin-links', 'dashicons-format-chat', 'dashicons-cart',
            'dashicons-money-alt', 'dashicons-lock', 'dashicons-unlock', 'dashicons-visibility',
            'dashicons-marker', 'dashicons-camera', 'dashicons-format-image', 'dashicons-media-default',
            'dashicons-admin-home', 'dashicons-building', 'dashicons-book', 'dashicons-book-alt',
            'dashicons-portfolio', 'dashicons-awards', 'dashicons-tag', 'dashicons-category',
            'dashicons-admin-comments', 'dashicons-share', 'dashicons-facebook', 'dashicons-twitter',
            'dashicons-instagram', 'dashicons-format-quote', 'dashicons-info', 'dashicons-warning',
            'dashicons-search', 'dashicons-privacy', 'dashicons-thumbs-up', 'dashicons-thumbs-down',
        ];
    }

    public function add_tag_generators()
    {
        if (!class_exists('WPCF7_TagGenerator')) {
            return;
        }
        $tg = \WPCF7_TagGenerator::get_instance();
        $tg->add('cf7m-icon', __('icon', 'cf7-styler-for-divi'), [$this, 'tag_generator'], ['version' => '2']);
    }

    /** @param \WPCF7_ContactForm $contact_form */
    public function tag_generator($contact_form, $options = '')
    {
        $icons = self::get_browser_icons();
        ?>
        <div class="cf7m-tag-panel cf7m-icon-tag-panel">
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e('Icon', 'cf7-styler-for-divi'); ?></legend>
                <table class="form-table"><tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Icon type', 'cf7-styler-for-divi'); ?></th>
                        <td>
                            <label><input type="radio" name="cf7m_icon_type" class="cf7m-icon-type" value="dashicon" checked /> <?php esc_html_e('Dashicon (browse below)', 'cf7-styler-for-divi'); ?></label>
                            <br />
                            <label><input type="radio" name="cf7m_icon_type" class="cf7m-icon-type" value="image" /> <?php esc_html_e('Custom image (upload)', 'cf7-styler-for-divi'); ?></label>
                        </td>
                    </tr>
                    <tr class="cf7m-icon-row-dashicon">
                        <th scope="row"><label><?php esc_html_e('Icon browser', 'cf7-styler-for-divi'); ?></label></th>
                        <td>
                            <div class="cf7m-icon-browser" role="listbox" aria-label="<?php esc_attr_e('Choose an icon', 'cf7-styler-for-divi'); ?>">
                                <?php foreach ($icons as $icon_class) : ?>
                                    <span class="cf7m-icon-picker-item dashicons <?php echo esc_attr($icon_class); ?>" data-name="<?php echo esc_attr($icon_class); ?>" role="option" tabindex="0" title="<?php echo esc_attr($icon_class); ?>"></span>
                                <?php endforeach; ?>
                            </div>
                            <p class="description"><?php esc_html_e('Click an icon to select it.', 'cf7-styler-for-divi'); ?></p>
                            <input type="text" name="name" class="oneline cf7m-icon-name-input" value="dashicons-star-filled" placeholder="dashicons-star-filled" style="margin-top:6px; max-width:240px;" />
                        </td>
                    </tr>
                    <tr class="cf7m-icon-row-image" style="display:none;">
                        <th scope="row"><label><?php esc_html_e('Image URL', 'cf7-styler-for-divi'); ?></label></th>
                        <td>
                            <input type="url" name="src" class="oneline cf7m-icon-src-input" placeholder="https://" style="max-width:240px;" />
                            <button type="button" class="button cf7m-icon-upload-trigger" style="margin-left:6px;"><?php esc_html_e('Select Image', 'cf7-styler-for-divi'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e('Size (px)', 'cf7-styler-for-divi'); ?></label></th>
                        <td><input type="number" name="size" class="oneline cf7m-icon-size-input" value="24" min="8" max="96" /></td>
                    </tr>
                </tbody></table>
            </fieldset>
        </div>
        <div class="insert-box">
            <input type="text" name="cf7m-icon" class="tag code" readonly="readonly" onfocus="this.select()" value="[cf7m-icon name:dashicons-star-filled size:24]" />
            <div class="submitbox"><input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>" /></div>
        </div>
        </div>
        <?php
    }
}
