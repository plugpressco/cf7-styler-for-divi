<?php

namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Onboarding
{
    private static $instance = null;

    const ONBOARDING_COMPLETED_OPTION = 'cf7m_onboarding_completed';
    const ONBOARDING_SKIPPED_OPTION = 'cf7m_onboarding_skipped';
    const ONBOARDING_STEP_OPTION = 'cf7m_onboarding_step';
    const REBRAND_SEEN_OPTION = 'cf7m_rebrand_seen';
    const REBRAND_VERSION = '3.0.0';

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init();
    }

    private function init()
    {
        add_action('admin_init', [$this, 'maybe_restart_guided_setup'], 5);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_footer', [$this, 'render_onboarding_root']);
        add_action('admin_notices', [$this, 'display_setup_notice']);
        add_action('wp_ajax_cf7m_check_onboarding_status', [$this, 'check_onboarding_status']);
        add_action('wp_ajax_cf7m_complete_onboarding', [$this, 'complete_onboarding']);
        add_action('wp_ajax_cf7m_skip_onboarding', [$this, 'skip_onboarding']);
        add_action('wp_ajax_cf7m_skip_setup_notice', [$this, 'skip_setup_notice']);
        add_action('wp_ajax_cf7m_next_onboarding_step', [$this, 'next_step']);
        add_action('wp_ajax_cf7m_dismiss_rebrand', [$this, 'dismiss_rebrand']);
    }

    /**
     * If user clicks "Guided Setup" from Quick Access (skipped state), reset onboarding and redirect so modal shows.
     */
    public function maybe_restart_guided_setup()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $param = isset($_GET['cf7m_guided_setup']) ? sanitize_text_field(wp_unslash($_GET['cf7m_guided_setup'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ($param !== '1') {
            return;
        }
        self::reset_onboarding();
        set_transient('cf7m_run_onboarding_' . get_current_user_id(), '1', 60);
        wp_safe_redirect(admin_url('admin.php?page=cf7-mate'));
        exit;
    }

    public function enqueue_scripts($hook)
    {
        // Only show the onboarding modal when explicitly triggered via "Run Setup Wizard".
        $transient_key = 'cf7m_run_onboarding_' . get_current_user_id();
        if (!get_transient($transient_key)) {
            return;
        }
        delete_transient($transient_key);

        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification
        if (strpos($page, 'cf7-mate') !== 0) {
            return;
        }

        // Only show for users who haven't completed/skipped onboarding.
        $onboarding_done = $this->is_onboarding_completed() || $this->is_onboarding_skipped();
        if ($onboarding_done) {
            return;
        }

        $onboarding_js = CF7M_PLUGIN_PATH . 'dist/js/onboarding.js';
        wp_enqueue_script(
            'cf7m-onboarding',
            CF7M_PLUGIN_URL . 'dist/js/onboarding.js',
            ['react', 'wp-element', 'wp-i18n', 'wp-dom-ready'],
            CF7M_VERSION . (file_exists($onboarding_js) ? '.' . filemtime($onboarding_js) : ''),
            true
        );

        $onboarding_css = CF7M_PLUGIN_PATH . 'dist/css/onboarding.css';
        if (file_exists($onboarding_css)) {
            wp_enqueue_style(
                'cf7m-onboarding',
                CF7M_PLUGIN_URL . 'dist/css/onboarding.css',
                [],
                CF7M_VERSION . '.' . filemtime($onboarding_css)
            );
        }

        wp_localize_script('cf7m-onboarding', 'dcsOnboarding', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cf7m_onboarding_nonce'),
            'current_step' => $this->get_current_step(),
            'create_page_url' => admin_url('post-new.php?post_type=page'),
            'cf7_admin_url' => admin_url('admin.php?page=wpcf7'),
            'dashboard_url' => admin_url('admin.php?page=cf7-mate'),
            'pricing_url' => defined('CF7M_URL_PRICING') ? CF7M_URL_PRICING : '',
            'is_pro' => function_exists('cf7m_is_pro') && cf7m_is_pro(),
            'rebrand_seen' => $this->is_rebrand_seen(),
            'onboarding_completed' => $this->is_onboarding_completed(),
            'version' => defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0',
        ]);
    }

    public function render_onboarding_root()
    {
        // Only render when the onboarding script was explicitly enqueued (via "Run Setup Wizard").
        if (!wp_script_is('cf7m-onboarding', 'enqueued')) {
            return;
        }

        echo '<div id="cf7m-onboarding-root"></div>';
    }

    /**
     * Display a non-intrusive admin notice prompting the user to run the setup wizard.
     * Shown on all admin pages until the user completes or skips onboarding.
     *
     * @since 3.0.1
     */
    public function display_setup_notice()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if ($this->is_onboarding_completed() || $this->is_onboarding_skipped()) {
            return;
        }

        $setup_url    = esc_url(admin_url('admin.php?page=cf7-mate&cf7m_guided_setup=1'));
        $dismiss_nonce = wp_create_nonce('cf7m_skip_setup_notice');
        ?>
        <div id="cf7m-setup-notice" class="notice notice-info" style="display:flex;align-items:center;padding:16px 40px 16px 16px;gap:16px;border-left-color:#3044D7;background:#f0f2ff;">
            <div style="flex-shrink:0;width:40px;height:40px;background:#e0e4ff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;line-height:1;">
                &#9786;
            </div>
            <div style="flex:1;">
                <p style="margin:0 0 4px;font-size:14px;font-weight:600;color:#1e1e1e;">
                    <?php esc_html_e('You are nearly ready to start using CF7 Mate', 'cf7-styler-for-divi'); ?>
                </p>
                <p style="margin:0;color:#50575e;">
                    <?php esc_html_e('Go through the setup to configure your plugin.', 'cf7-styler-for-divi'); ?>
                </p>
            </div>
            <div style="flex-shrink:0;display:flex;gap:8px;">
                <a href="<?php echo esc_url($setup_url); ?>" class="button button-primary" style="background:#3044D7;border-color:#3044D7;">
                    <?php esc_html_e('Run Setup Wizard', 'cf7-styler-for-divi'); ?>
                </a>
                <button type="button" class="button cf7m-skip-setup">
                    <?php esc_html_e('Skip Setup', 'cf7-styler-for-divi'); ?>
                </button>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#cf7m-setup-notice').on('click', '.cf7m-skip-setup', function() {
                    var $notice = $('#cf7m-setup-notice');
                    $.post(ajaxurl, {
                        action: 'cf7m_skip_setup_notice',
                        nonce: '<?php echo esc_js($dismiss_nonce); ?>'
                    }, function() {
                        $notice.fadeOut(200, function() { $(this).remove(); });
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * AJAX handler: skip onboarding from the setup notice.
     *
     * @since 3.0.1
     */
    public function skip_setup_notice()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'cf7m_skip_setup_notice')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        update_option(self::ONBOARDING_SKIPPED_OPTION, '1');
        delete_option(self::ONBOARDING_STEP_OPTION);

        wp_send_json_success();
    }

    public function check_onboarding_status()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'cf7m_onboarding_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $should_show = !$this->is_onboarding_completed() && !$this->is_onboarding_skipped();
        $current_step = $this->get_current_step();

        wp_send_json_success([
            'should_show' => $should_show,
            'current_step' => $current_step,
        ]);
    }

    public function skip_onboarding()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'cf7m_onboarding_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        update_option(self::ONBOARDING_SKIPPED_OPTION, '1');
        delete_option(self::ONBOARDING_STEP_OPTION);

        wp_send_json_success();
    }

    public function next_step()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'cf7m_onboarding_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $step = isset($_POST['step']) ? absint(wp_unslash($_POST['step'])) : $this->get_current_step();
        update_option(self::ONBOARDING_STEP_OPTION, $step);

        wp_send_json_success([
            'step' => $step,
        ]);
    }

    public function complete_onboarding()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'cf7m_onboarding_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        // Save feature settings if provided
        if (isset($_POST['features'])) {
            $features_json = sanitize_text_field(wp_unslash($_POST['features']));
            $features = json_decode($features_json, true);

            if (is_array($features)) {
                $defaults = [
                    'cf7_module' => true,
                    'bricks_module' => true,
                    'elementor_module' => true,
                    'gutenberg_module' => true,
                    'grid_layout' => true,
                    'multi_column' => true,
                    'multi_step' => true,
                    'star_rating' => true,
                    'database_entries' => true,
                    'range_slider' => true,
                    'separator' => true,
                    'heading' => true,
                    'image' => true,
                    'icon' => true,
                    'ai_form_generator' => true,
                    'conditional' => true,
                ];

                $sanitized = [];
                foreach ($defaults as $key => $default) {
                    $sanitized[$key] = isset($features[$key]) ? (bool) $features[$key] : $default;
                }

                update_option('cf7m_features', $sanitized);
            }
        }

        update_option(self::ONBOARDING_COMPLETED_OPTION, '1');
        delete_option(self::ONBOARDING_STEP_OPTION);
        delete_option(self::ONBOARDING_SKIPPED_OPTION);

        wp_send_json_success();
    }

    /**
     * Dismiss rebrand notification.
     *
     * @since 3.0.0
     */
    public function dismiss_rebrand()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'cf7m_onboarding_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        update_option(self::REBRAND_SEEN_OPTION, '1');

        wp_send_json_success();
    }

    private function is_onboarding_completed()
    {
        return get_option(self::ONBOARDING_COMPLETED_OPTION, false) === '1';
    }

    private function is_onboarding_skipped()
    {
        return get_option(self::ONBOARDING_SKIPPED_OPTION, false) === '1';
    }

    /**
     * Check if rebrand notification has been seen.
     *
     * @since 3.0.0
     * @return bool
     */
    private function is_rebrand_seen()
    {
        return get_option(self::REBRAND_SEEN_OPTION, false) === '1';
    }

    private function get_current_step()
    {
        $step = get_option(self::ONBOARDING_STEP_OPTION, 1);
        return (int) $step;
    }

    /**
     * Get dynamic discount code based on current month.
     * Format: JAN2026, FEB2026, MAR2026, etc.
     *
     * @since 3.0.0
     * @return string
     */
    private function get_discount_code()
    {
        $month_names = [
            1 => 'JAN',
            2 => 'FEB',
            3 => 'MAR',
            4 => 'APR',
            5 => 'MAY',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AUG',
            9 => 'SEP',
            10 => 'OCT',
            11 => 'NOV',
            12 => 'DEC',
        ];

        $current_month = (int) gmdate('n');
        $current_year = gmdate('Y');
        $month_code = isset($month_names[$current_month]) ? $month_names[$current_month] : 'JAN';

        return $month_code . $current_year;
    }

    /**
     * Check if onboarding was skipped (for admin notice).
     *
     * @since 3.0.0
     * @return bool
     */
    public static function is_skipped()
    {
        return get_option(self::ONBOARDING_SKIPPED_OPTION, false) === '1';
    }

    /**
     * Reset onboarding (for admin notice "Complete Onboarding" button).
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
