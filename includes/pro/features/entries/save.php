<?php
/**
 * Entries Save – capture CF7 submission and save to CPT.
 *
 * @package CF7_Mate\Features\Entries
 * @since 3.0.0
 */

namespace CF7_Mate\Features\Entries;

if (!defined('ABSPATH')) {
    exit;
}

class Entries_Save
{
    /**
     * Per-form post-meta key. Value is '0' to disable response saving for that form.
     * Absent / '1' means enabled (default-on).
     */
    const PER_FORM_META_KEY = '_cf7m_save_to_db';

    public function __construct()
    {
        add_action('wpcf7_before_send_mail', [$this, 'capture_submission'], 5);
    }

    /**
     * Capture submission and save as entry.
     *
     * @param \WPCF7_ContactForm $contact_form
     */
    public function capture_submission($contact_form)
    {
        $submission = \WPCF7_Submission::get_instance();
        if (!$submission) {
            return;
        }

        if (!post_type_exists(Entries_CPT::POST_TYPE)) {
            return;
        }

        // Per-form opt-out: if explicitly disabled on the form, skip saving.
        if (! self::is_enabled_for_form($contact_form->id())) {
            return;
        }

        $data = $submission->get_posted_data();
        unset(
            $data['_wpcf7'],
            $data['_wpcf7_version'],
            $data['_wpcf7_locale'],
            $data['_wpcf7_unit_tag'],
            $data['_wpcf7_container_post'],
            $data['_cf7m_ps_token']
        );

        $status = 'new';

        $title = sprintf(
            /* translators: 1: form title, 2: date */
            __('%1$s — %2$s', 'cf7-styler-for-divi'),
            $contact_form->title(),
            current_time('Y-m-d H:i:s')
        );

        $entry_id = wp_insert_post([
            'post_type'   => Entries_CPT::POST_TYPE,
            'post_status' => 'publish',
            'post_title'  => $title,
        ]);

        if (!$entry_id || is_wp_error($entry_id)) {
            return;
        }

        update_post_meta($entry_id, '_cf7m_form_id', $contact_form->id());
        update_post_meta($entry_id, '_cf7m_form_title', $contact_form->title());
        update_post_meta($entry_id, '_cf7m_status', $status);
        // update_post_meta() unslashes; keep the JSON escapes for quotes, line breaks and non-ASCII text.
        update_post_meta($entry_id, '_cf7m_data', wp_slash(wp_json_encode($data)));
        update_post_meta($entry_id, '_cf7m_created', current_time('mysql'));
        update_post_meta($entry_id, '_cf7m_ip', $this->get_client_ip());
        update_post_meta($entry_id, '_cf7m_ua', $this->get_user_agent());
    }

    private function get_client_ip()
    {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $raw = sanitize_text_field(wp_unslash($_SERVER[$key]));
                $ip  = trim(explode(',', $raw)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '';
    }

    private function get_user_agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT'])
            ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))
            : '';
    }

    /**
     * Whether response saving is enabled for the given CF7 form.
     * Default-on: only disabled when meta is explicitly '0'.
     */
    public static function is_enabled_for_form($form_id)
    {
        $form_id = (int) $form_id;
        if ($form_id <= 0) {
            return true;
        }
        $value = get_post_meta($form_id, self::PER_FORM_META_KEY, true);
        return $value !== '0';
    }
}
