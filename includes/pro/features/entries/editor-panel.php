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
        add_action('cf7m_editor_panel_sections', [$this, 'render_panel'], 5);
        add_action('wpcf7_save_contact_form', [$this, 'save'], 10, 1);
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
            <section class="cf7m-feat" data-cf7m-section="responses">
                <header class="cf7m-feat__header">
                    <h3 class="cf7m-feat__title"><?php esc_html_e('Responses', 'cf7-styler-for-divi'); ?></h3>
                    <p class="cf7m-feat__desc">
                        <?php esc_html_e('Keep submissions from this form in WordPress, including visitor details. View them in Responses.', 'cf7-styler-for-divi'); ?>
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
