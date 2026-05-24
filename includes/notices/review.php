<?php

namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Review_Notice
{
    private static $instance = null;

    const NOTICE_ID = 'dcs_review_notice';
    const DISMISSED_OPTION = 'dcs_review_notice_dismissed';
    const INSTALL_DATE_OPTION = 'cf7m_install_date';
    const REVIEW_DELAY = 7 * 24 * 60 * 60;

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
        add_action('admin_notices', [$this, 'display_notice']);
        add_action('wp_ajax_dcs_dismiss_review_notice', [$this, 'dismiss_notice']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts($hook)
    {
        // Don't load on CF7 Mate admin pages
        if ($this->is_cf7_mate_page()) {
            return;
        }

        // Only load on admin pages
        if (!in_array($hook, ['index.php', 'plugins.php', 'edit.php', 'post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_script(
            'cf7m-admin-notice',
            CF7M_PLUGIN_URL . 'dist/js/admin-notice.js',
            ['jquery'],
            CF7M_VERSION,
            true
        );

        wp_localize_script('cf7m-admin-notice', 'dcs_admin_notice', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dcs_dismiss_notice'),
            'notice_id' => self::NOTICE_ID
        ]);
    }

    public function display_notice()
    {
        // Don't show on CF7 Mate admin pages
        if ($this->is_cf7_mate_page()) {
            return;
        }

        // Check if notice should be displayed
        if (!$this->should_display_notice()) {
            return;
        }

        // Check if user has dismissed the notice
        if ($this->is_notice_dismissed()) {
            return;
        }

        $this->render_notice();
    }

    /**
     * Check if current page is a CF7 Mate admin page.
     */
    private function is_cf7_mate_page()
    {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return in_array($page, ['cf7-mate', 'cf7-mate-responses', 'cf7-mate-analytics'], true);
    }

    private function should_display_notice()
    {
        $install_date = get_option(self::INSTALL_DATE_OPTION);

        if (!$install_date) {
            return false;
        }

        $current_time = time();
        $time_since_install = $current_time - $install_date;

        return $time_since_install >= self::REVIEW_DELAY;
    }

    private function is_notice_dismissed()
    {
        return get_user_meta(get_current_user_id(), self::DISMISSED_OPTION, true) === '1';
    }

    private function render_notice()
    {
        $notice_id  = esc_attr(self::NOTICE_ID);
        $review_url = 'https://wordpress.org/support/plugin/cf7-styler-for-divi/reviews/#new-post';
        $support_url = 'https://cf7mate.com/support';
?>
        <div id="<?php echo $notice_id; ?>" class="cf7m-rn-wrap notice">
            <div class="cf7m-rn">
                <!-- Icon -->
                <svg class="cf7m-rn__icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M10 1l2.39 5.26 5.61.82-4.06 3.95.96 5.58L10 14.27 5.1 16.61l.96-5.58L2 7.08l5.61-.82L10 1z" fill="#3858e9" fill-opacity=".15" stroke="#3858e9" stroke-width="1.4" stroke-linejoin="round"/>
                </svg>

                <!-- Text block -->
                <div class="cf7m-rn__body">
                    <span class="cf7m-rn__label" id="cf7m-rn-label">
                        <?php
                        printf(
                            /* translators: %s: plugin name */
                            esc_html__('How are you finding %s?', 'cf7-styler-for-divi'),
                            '<strong>CF7 Mate</strong>'
                        );
                        ?>
                    </span>

                    <!-- Star rating -->
                    <span class="cf7m-rn__stars" id="cf7m-rn-stars" role="group" aria-label="<?php esc_attr_e('Rate CF7 Mate', 'cf7-styler-for-divi'); ?>">
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <button type="button" class="cf7m-rn__star" data-rating="<?php echo $i; ?>" aria-label="<?php echo esc_attr(sprintf(__('%d star', 'cf7-styler-for-divi'), $i)); ?>">
                            <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M10 1l2.39 5.26 5.61.82-4.06 3.95.96 5.58L10 14.27 5.1 16.61l.96-5.58L2 7.08l5.61-.82L10 1z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <?php endfor; ?>
                    </span>

                    <!-- CTA (shown after high rating) -->
                    <a href="<?php echo esc_url($review_url); ?>" target="_blank" rel="noopener noreferrer"
                       class="cf7m-rn__cta" id="cf7m-rn-cta" style="display:none">
                        <?php esc_html_e('Leave a review', 'cf7-styler-for-divi'); ?>
                        <svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2 10L10 2M10 2H4M10 2v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>

                <!-- Dismiss -->
                <button type="button" class="cf7m-rn__later" data-action="dismiss">
                    <?php esc_html_e('Not now', 'cf7-styler-for-divi'); ?>
                </button>
                <button type="button" class="cf7m-rn__close" data-action="dismiss" aria-label="<?php esc_attr_e('Dismiss', 'cf7-styler-for-divi'); ?>">
                    <svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>

        <style>
            /* Strip WordPress default notice styles */
            .cf7m-rn-wrap.notice {
                padding: 0 !important;
                border-left: none !important;
                background: #fff !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 8px !important;
                box-shadow: 0 1px 3px rgba(0,0,0,.06) !important;
                margin: 12px 20px 4px 0 !important;
                overflow: hidden !important;
            }

            /* Remove WP's dismiss button — we have our own */
            .cf7m-rn-wrap .notice-dismiss { display: none !important; }

            .cf7m-rn {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 14px;
                min-height: 44px;
            }

            .cf7m-rn__icon {
                width: 18px;
                height: 18px;
                flex-shrink: 0;
            }

            .cf7m-rn__body {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
                min-width: 0;
                flex-wrap: wrap;
            }

            .cf7m-rn__label {
                font-size: 13px;
                color: #374151;
                white-space: nowrap;
            }
            .cf7m-rn__label strong {
                color: #111827;
                font-weight: 600;
            }

            /* Stars */
            .cf7m-rn__stars {
                display: inline-flex;
                gap: 2px;
                align-items: center;
            }

            .cf7m-rn__star {
                background: none;
                border: none;
                padding: 2px;
                cursor: pointer;
                color: #d1d5db;
                transition: color 0.1s ease, transform 0.1s ease;
                display: inline-flex;
                align-items: center;
                line-height: 1;
            }
            .cf7m-rn__star svg {
                width: 18px;
                height: 18px;
                display: block;
            }
            .cf7m-rn__star:hover,
            .cf7m-rn__star.is-lit {
                color: #f59e0b;
                transform: scale(1.15);
            }
            .cf7m-rn__star.is-lit svg path {
                fill: #f59e0b;
                stroke: #f59e0b;
            }

            /* CTA link */
            .cf7m-rn__cta {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: 13px;
                font-weight: 600;
                color: #3858e9;
                text-decoration: none;
                white-space: nowrap;
                transition: opacity 0.15s;
            }
            .cf7m-rn__cta:hover { opacity: 0.8; color: #3858e9; }
            .cf7m-rn__cta svg { width: 11px; height: 11px; }

            /* Not now */
            .cf7m-rn__later {
                background: none;
                border: none;
                padding: 4px 8px;
                font-size: 12px;
                color: #9ca3af;
                cursor: pointer;
                white-space: nowrap;
                border-radius: 4px;
                transition: color 0.15s;
                margin-left: auto;
            }
            .cf7m-rn__later:hover { color: #6b7280; }

            /* Close ×  */
            .cf7m-rn__close {
                background: none;
                border: none;
                padding: 4px;
                cursor: pointer;
                color: #9ca3af;
                display: inline-flex;
                align-items: center;
                border-radius: 4px;
                transition: color 0.15s, background 0.15s;
                flex-shrink: 0;
            }
            .cf7m-rn__close svg { width: 12px; height: 12px; display: block; }
            .cf7m-rn__close:hover { color: #374151; background: #f3f4f6; }
        </style>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $notice  = $('#<?php echo esc_js(self::NOTICE_ID); ?>');
            var $stars   = $notice.find('.cf7m-rn__star');
            var $label   = $notice.find('#cf7m-rn-label');
            var $starWrap = $notice.find('#cf7m-rn-stars');
            var $cta     = $notice.find('#cf7m-rn-cta');
            var ajaxUrl  = <?php echo json_encode(admin_url('admin-ajax.php')); ?>;
            var nonce    = <?php echo json_encode(wp_create_nonce('dcs_dismiss_notice')); ?>;
            var reviewUrl = <?php echo json_encode($review_url); ?>;
            var supportUrl = <?php echo json_encode($support_url); ?>;

            function dismiss() {
                $notice.fadeOut(250, function() { $(this).remove(); });
                $.post(ajaxUrl, { action: 'dcs_dismiss_review_notice', nonce: nonce });
            }

            /* Star hover */
            $stars.on('mouseenter', function() {
                var rating = parseInt($(this).data('rating'), 10);
                $stars.each(function() {
                    var r = parseInt($(this).data('rating'), 10);
                    $(this).toggleClass('is-lit', r <= rating);
                });
            }).on('mouseleave', function() {
                $stars.removeClass('is-lit');
            });

            /* Star click */
            $stars.on('click', function() {
                var rating = parseInt($(this).data('rating'), 10);
                if (rating >= 4) {
                    $label.html('<?php echo esc_js(__('Thanks! One click and you\'re done.', 'cf7-styler-for-divi')); ?>');
                    $starWrap.hide();
                    $cta.show();
                    window.open(reviewUrl, '_blank', 'noopener');
                    setTimeout(dismiss, 4000);
                } else {
                    $label.html('<?php echo esc_js(__('Thanks for the feedback! We\'d love to help.', 'cf7-styler-for-divi')); ?> <a href="' + supportUrl + '" target="_blank" rel="noopener" style="color:#3858e9;font-weight:600;text-decoration:none;"><?php echo esc_js(__('Contact support →', 'cf7-styler-for-divi')); ?></a>');
                    $starWrap.hide();
                    setTimeout(dismiss, 5000);
                }
            });

            /* Dismiss buttons */
            $notice.on('click', '[data-action="dismiss"]', function(e) {
                e.preventDefault();
                dismiss();
            });
        });
        </script>
<?php
    }

    public function dismiss_notice()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'dcs_dismiss_notice')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        update_user_meta(get_current_user_id(), self::DISMISSED_OPTION, '1');

        wp_send_json_success();
    }
}
