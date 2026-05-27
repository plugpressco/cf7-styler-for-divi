<?php
/**
 * CF7 Mate panel inside the CF7 form editor – per-form Save Responses toggle.
 *
 * @package CF7_Mate\Features\Entries
 */

namespace CF7_Mate\Features\Entries;

if (!defined('ABSPATH')) {
    exit;
}

class Entries_Editor_Panel
{
    const NONCE_ACTION = 'cf7m_save_to_db';
    const NONCE_NAME   = 'cf7m_save_to_db_nonce';
    const FIELD_NAME   = 'cf7m_save_to_db';

    public function __construct()
    {
        add_filter('wpcf7_editor_panels', [$this, 'register_panel']);
        add_action('wpcf7_save_contact_form', [$this, 'save'], 10, 1);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets($hook)
    {
        // CF7 form editor screens use admin.php?page=wpcf7 → screen ids
        // toplevel_page_wpcf7 (list) and contact_page_wpcf7-new (edit).
        if ($hook !== 'toplevel_page_wpcf7' && $hook !== 'contact_page_wpcf7-new') {
            return;
        }
        $css = CF7M_PLUGIN_PATH . 'assets/lite/css/cf7m-editor-panel.css';
        if (! file_exists($css)) {
            return;
        }
        $ver = (defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0') . '.' . filemtime($css);
        wp_enqueue_style(
            'cf7m-editor-panel',
            CF7M_PLUGIN_URL . 'assets/lite/css/cf7m-editor-panel.css',
            [],
            $ver
        );
    }

    /**
     * Register a "CF7 Mate" tab in the form editor.
     */
    public function register_panel($panels)
    {
        $panels['cf7m-panel'] = [
            'title'    => __('CF7 Mate', 'cf7-styler-for-divi'),
            'callback' => [$this, 'render_panel'],
        ];
        return $panels;
    }

    /**
     * Render the panel UI.
     *
     * @param \WPCF7_ContactForm $contact_form
     */
    public function render_panel($contact_form)
    {
        $form_id = $contact_form ? (int) $contact_form->id() : 0;
        $enabled = $form_id ? Entries_Save::is_enabled_for_form($form_id) : true;
        ?>
        <div class="cf7m-panel-wrap">
            <h2><?php esc_html_e('CF7 Mate', 'cf7-styler-for-divi'); ?></h2>

            <section class="cf7m-feat">
                <header class="cf7m-feat__header">
                    <h3 class="cf7m-feat__title"><?php esc_html_e('Responses', 'cf7-styler-for-divi'); ?></h3>
                    <p class="cf7m-feat__desc">
                        <?php esc_html_e('When enabled, every submission is stored and viewable from CF7 Mate → Responses. Disable for forms you don\'t want to log (e.g. search, login).', 'cf7-styler-for-divi'); ?>
                    </p>
                </header>
                <div class="cf7m-feat__body">
                    <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>
                    <label class="cf7m-feat-check">
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr(self::FIELD_NAME); ?>"
                            value="1"
                            <?php checked($enabled); ?>
                        />
                        <span><?php esc_html_e('Save submissions of this form to the database', 'cf7-styler-for-divi'); ?></span>
                    </label>
                </div>
            </section>

            <?php do_action('cf7m_editor_panel_sections', $contact_form); ?>
        </div>
        <?php
    }

    /**
     * Persist the toggle on form save.
     *
     * @param \WPCF7_ContactForm $contact_form
     */
    public function save($contact_form)
    {
        if (! $contact_form instanceof \WPCF7_ContactForm) {
            return;
        }

        $form_id = (int) $contact_form->id();
        if ($form_id <= 0) {
            return;
        }

        if (! current_user_can('wpcf7_edit_contact_form', $form_id)) {
            return;
        }

        // Require nonce; if absent, the panel wasn't submitted (e.g. CLI / programmatic save).
        if (empty($_POST[self::NONCE_NAME]) || ! wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])),
            self::NONCE_ACTION
        )) {
            return;
        }

        $enabled = ! empty($_POST[self::FIELD_NAME]);
        update_post_meta($form_id, Entries_Save::PER_FORM_META_KEY, $enabled ? '1' : '0');
    }
}
