<?php

namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Admin
{
    private static $instance;

    const CF7_PARENT_SLUG     = 'cf7-mate';
    const RESPONSES_PAGE_SLUG = 'cf7-mate-responses';
    const ANALYTICS_PAGE_SLUG = 'cf7-mate-analytics';
    const SETTINGS_PAGE_SLUG  = 'cf7-mate';

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu'], 20);
        add_filter('cf7m_admin_app_localize', [$this, 'inject_license_data'], 10, 2);
        add_filter('plugin_action_links_' . CF7M_BASENAME, [$this, 'add_plugin_action_links']);
    }

    public function add_plugin_action_links($links)
    {
        $dash_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=' . self::SETTINGS_PAGE_SLUG)),
            esc_html__('CF7Mate', 'cf7-styler-for-divi')
        );
        array_unshift($links, $dash_link);
        return $links;
    }

    public function add_menu()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $menu_label  = apply_filters( 'cf7m_admin_menu_label', __( 'CF7Mate', 'cf7-styler-for-divi' ) );
        $menu_icon   = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCI+PHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0yNCAwQzM3LjI1NDggMCA0OCAxMC43NDUyIDQ4IDI0QzQ4IDM3LjI1NDggMzcuMjU0OCA0OCAyNCA0OEMxMC43NDUyIDQ4IDAgMzcuMjU0OCAwIDI0QzAgMTAuNzQ1MiAxMC43NDUyIDAgMjQgMFpNMTYuOCAxMkMxNC4xNDkgMTIgMTIgMTQuMTQ5IDEyIDE2LjhWMzEuMkMxMiAzMy44NTEgMTQuMTQ5IDM2IDE2LjggMzZIMjEuNkMyNC4yNTEgMzYgMjYuNCAzMy44NTEgMjYuNCAzMS4yVjE2LjhDMjYuNCAxNC4xNDkgMjQuMjUxIDEyIDIxLjYgMTJIMTYuOFpNMzIuNCAxMkMzMC40MTE4IDEyIDI4LjggMTMuNjExOCAyOC44IDE1LjZWMzIuNEMyOC44IDM0LjM4ODIgMzAuNDExOCAzNiAzMi40IDM2QzM0LjM4ODIgMzYgMzYgMzQuMzg4MiAzNiAzMi40VjE1LjZDMzYgMTMuNjExOCAzNC4zODgyIDEyIDMyLjQgMTJaIiBmaWxsPSIjYTdhYWFkIi8+PC9zdmc+';

        add_menu_page(
            $menu_label,
            $menu_label,
            'manage_options',
            self::SETTINGS_PAGE_SLUG,
            [$this, 'render_settings_page'],
            $menu_icon,
            26
        );

        // First submenu mirrors the parent so the top-level label reads "Settings".
        add_submenu_page(
            self::SETTINGS_PAGE_SLUG,
            $menu_label,
            __( 'Settings', 'cf7-styler-for-divi' ),
            'manage_options',
            self::SETTINGS_PAGE_SLUG,
            [$this, 'render_settings_page']
        );
    }

    public function render_responses_page()
    {
        $this->render_app_root([
            'app'          => 'responses',
            'current_page' => 'responses',
        ]);
    }

    public function render_analytics_page()
    {
        $this->render_app_root([
            'app'          => 'analytics',
            'current_page' => 'analytics',
        ]);
    }

    public function render_settings_page()
    {
        $this->render_app_root([
            'app'          => 'settings',
            'current_page' => 'settings',
        ]);
    }

    public function render_app_root(array $options = [])
    {
        $app          = isset($options['app']) ? $options['app'] : 'settings';
        $current_page = isset($options['current_page']) ? $options['current_page'] : 'settings';

        $script_handle = 'cf7m-admin';
        $script_file   = 'dist/js/admin.js';
        $style_handle  = 'cf7m-admin';
        $style_file    = 'dist/css/admin.css';

        $script_path = CF7M_PLUGIN_PATH . $script_file;
        $style_path  = CF7M_PLUGIN_PATH . $style_file;
        $script_ver  = CF7M_VERSION . (file_exists($script_path) ? '.' . filemtime($script_path) : '');
        $style_ver   = CF7M_VERSION . (file_exists($style_path) ? '.' . filemtime($style_path) : '');

        wp_enqueue_script(
            $script_handle,
            CF7M_PLUGIN_URL . $script_file,
            ['wp-i18n', 'wp-element', 'wp-api-fetch', 'wp-dom-ready', 'wp-components'],
            $script_ver,
            true
        );

        wp_enqueue_style(
            $style_handle,
            CF7M_PLUGIN_URL . $style_file,
            ['wp-components'],
            $style_ver
        );

        // Allow pro (or add-ons) to enqueue their own scripts/styles.
        do_action('cf7m_admin_enqueue_scripts', $app);

        $builders = $this->detect_builders();
        $onboarding_skipped     = get_option('cf7m_onboarding_skipped', '') === '1';
        $onboarding_completed   = get_option('cf7m_onboarding_completed', '') === '1';
        $show_guided_setup_link = $onboarding_skipped && !$onboarding_completed;

        $localize = [
            'root'                   => esc_url_raw(get_rest_url()),
            'ajax_url'               => admin_url('admin-ajax.php'),
            'is_pro'                 => cf7m_is_pro(),
            'pricing_url'            => CF7M_URL_PRICING,
            'docs_url'               => defined('CF7M_URL_DOCS') ? CF7M_URL_DOCS : '',
            'support_url'            => defined('CF7M_URL_SUPPORT') ? CF7M_URL_SUPPORT : '',
            'community_url'          => defined('CF7M_URL_COMMUNITY') ? CF7M_URL_COMMUNITY : '',
            'builders'               => $builders,
            'promo_code'             => '',
            'promo_text'             => '',
            'nonce'                  => wp_create_nonce('wp_rest'),
            'pluginUrl'              => CF7M_PLUGIN_URL,
            'show_guided_setup_link' => $show_guided_setup_link,
            'version'                => defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0',
            'cf7_admin_url'          => admin_url('admin.php?page=wpcf7'),
            'dash_url'               => admin_url('admin.php?page=' . self::SETTINGS_PAGE_SLUG),
            'responses_url'          => admin_url('admin.php?page=' . self::RESPONSES_PAGE_SLUG),
            'analytics_url'          => admin_url('admin.php?page=' . self::ANALYTICS_PAGE_SLUG),
            'currentPage'            => $current_page,
            'app'                    => $app,
        ];

        $localize = apply_filters('cf7m_admin_app_localize', $localize, $options);

        wp_localize_script($script_handle, 'dcsCF7Styler', $localize);

        echo '<div id="cf7-mate-app-root"></div>';
    }

    /**
     * Detect installed/active page builders so the Features UI can show only
     * the relevant builder modules.
     */
    private function detect_builders()
    {
        // Divi: parent theme or child whose parent is Divi.
        $theme        = wp_get_theme();
        $parent_theme = $theme->parent();
        $theme_name   = strtolower((string) $theme->get('Name'));
        $parent_name  = $parent_theme ? strtolower((string) $parent_theme->get('Name')) : '';
        $divi_active  = function_exists('et_setup_theme')
            || in_array('divi', [$theme_name, $parent_name], true)
            || in_array('extra', [$theme_name, $parent_name], true);

        // Bricks: theme name or constant defined by the theme.
        $bricks_active = defined('BRICKS_VERSION')
            || in_array('bricks', [$theme_name, $parent_name], true);

        // Elementor: plugin loaded.
        $elementor_active = did_action('elementor/loaded') > 0
            || class_exists('Elementor\\Plugin');

        return [
            'divi'      => (bool) $divi_active,
            'bricks'    => (bool) $bricks_active,
            'elementor' => (bool) $elementor_active,
            'gutenberg' => true, // native WP block editor — always available.
        ];
    }

    public function inject_license_data($localize, $options)
    {
        if (class_exists('CF7_Mate\License\License_Manager')) {
            $license_manager = \CF7_Mate\License\License_Manager::instance();
            $status = $license_manager->get_status();
            $status['is_agency'] = class_exists('CF7_Mate\Pro\White_Label')
                && \CF7_Mate\Pro\White_Label::is_agency_plan();
            $localize['license'] = $status;
        }
        return $localize;
    }
}
