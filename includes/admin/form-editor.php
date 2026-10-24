<?php
/**
 * Shared form settings, independent of optional response storage.
 *
 * @package CF7_Mate
 */
namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Form_Editor
{
    public function __construct()
    {
        add_filter('wpcf7_editor_panels', [$this, 'register_panel']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_panel($panels)
    {
        $panels['cf7m-panel'] = ['title' => __('CF7 Mate', 'cf7-styler-for-divi'), 'callback' => [$this, 'render_panel']];
        return $panels;
    }

    public function enqueue_assets()
    {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'wpcf7') === false) {
            return;
        }
        foreach (['css' => 'style', 'js' => 'script'] as $extension => $type) {
            $file = 'assets/lite/' . $extension . '/cf7m-editor-panel.' . $extension;
            $version = CF7M_VERSION . '.' . filemtime(CF7M_PLUGIN_PATH . $file);
            if ('style' === $type) {
                wp_enqueue_style('cf7m-editor-panel', CF7M_PLUGIN_URL . $file, [], $version);
            } else {
                wp_enqueue_script('cf7m-editor-panel', CF7M_PLUGIN_URL . $file, [], $version, true);
            }
        }
        wp_localize_script('cf7m-editor-panel', 'cf7mEditor', [
            'on' => __('On', 'cf7-styler-for-divi'),
            'off' => __('Off', 'cf7-styler-for-divi'),
            'enabled' => __('Enabled', 'cf7-styler-for-divi'),
            'unsaved' => __('Unsaved changes. Save the form to apply them.', 'cf7-styler-for-divi'),
            'routingEmpty' => __('No routing rules yet. Notifications use the recipient in the Mail tab.', 'cf7-styler-for-divi'),
            'redirectEmpty' => __('No conditional redirects. All submissions use the default destination above.', 'cf7-styler-for-divi'),
        ]);
    }

    public function render_panel($contact_form)
    {
        $form_id = $contact_form ? (int) $contact_form->id() : 0;
        ?>
        <div class="cf7m-panel-wrap" data-form-id="<?php echo esc_attr($form_id); ?>">
            <header class="cf7m-editor-heading">
                <div><h2><?php esc_html_e('Form settings', 'cf7-styler-for-divi'); ?></h2><p><?php esc_html_e('Choose what happens before and after someone submits this form.', 'cf7-styler-for-divi'); ?></p></div>
                <span class="cf7m-editor-save-status" role="status"><?php esc_html_e('Changes are saved with this form.', 'cf7-styler-for-divi'); ?></span>
            </header>
            <div class="cf7m-editor-layout">
                <aside class="cf7m-editor-sidebar" hidden>
                    <nav class="cf7m-editor-nav" aria-label="<?php esc_attr_e('Form settings sections', 'cf7-styler-for-divi'); ?>"></nav>
                    <a class="cf7m-editor-manage" href="<?php echo esc_url(admin_url('admin.php?page=cf7-mate#/features')); ?>"><?php esc_html_e('Manage features', 'cf7-styler-for-divi'); ?> <span aria-hidden="true">↗</span></a>
                </aside>
                <div class="cf7m-editor-panels">
                    <?php do_action('cf7m_editor_panel_sections', $contact_form); ?>
                    <p class="cf7m-editor-empty" hidden><?php esc_html_e('Enable form features in CF7 Mate to configure them here.', 'cf7-styler-for-divi'); ?></p>
                </div>
            </div>
            <datalist id="cf7m-form-fields">
                <?php if ($contact_form) : foreach ($contact_form->scan_form_tags() as $tag) : if (!$tag->name) { continue; } ?>
                    <option value="<?php echo esc_attr($tag->name); ?>"></option>
                <?php endforeach; endif; ?>
            </datalist>
        </div>
        <?php
    }
}
