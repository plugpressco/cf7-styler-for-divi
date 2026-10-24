<?php

namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Setup state for the inline setup card on the Features tab.
 *
 * The card is part of the admin app; this class only stores whether setup
 * was finished or skipped and saves the feature choices it sends.
 */
class Onboarding
{
    private static $instance = null;

    const ONBOARDING_COMPLETED_OPTION = 'cf7m_onboarding_completed';
    const ONBOARDING_SKIPPED_OPTION = 'cf7m_onboarding_skipped';
    const ONBOARDING_STEP_OPTION = 'cf7m_onboarding_step';

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('wp_ajax_cf7m_complete_onboarding', [$this, 'complete_onboarding']);
        add_action('wp_ajax_cf7m_skip_onboarding', [$this, 'skip_onboarding']);
    }

    private function verify_request()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'cf7m_onboarding_nonce')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        }
    }

    public function skip_onboarding()
    {
        $this->verify_request();

        update_option(self::ONBOARDING_SKIPPED_OPTION, '1');
        delete_option(self::ONBOARDING_STEP_OPTION);

        wp_send_json_success();
    }

    public function complete_onboarding()
    {
        $this->verify_request();

        // Feature_Settings::save() merges recognized keys and sanitizes each value.
        if (isset($_POST['features'])) {
            $features = json_decode(wp_unslash($_POST['features']), true); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            if (!is_array($features)) {
                wp_send_json_error(['message' => 'Invalid feature settings'], 400);
            }
            Feature_Settings::save($features);
        }

        update_option(self::ONBOARDING_COMPLETED_OPTION, '1');
        delete_option(self::ONBOARDING_STEP_OPTION);
        delete_option(self::ONBOARDING_SKIPPED_OPTION);

        wp_send_json_success();
    }

    /**
     * Check if onboarding was skipped.
     *
     * @since 3.0.0
     * @return bool
     */
    public static function is_skipped()
    {
        return get_option(self::ONBOARDING_SKIPPED_OPTION, false) === '1';
    }

    /**
     * Reset onboarding so the setup card shows again.
     *
     * @since 3.0.0
     */
    public static function reset_onboarding()
    {
        delete_option(self::ONBOARDING_SKIPPED_OPTION);
        delete_option(self::ONBOARDING_COMPLETED_OPTION);
        update_option(self::ONBOARDING_STEP_OPTION, 1);
    }
}
