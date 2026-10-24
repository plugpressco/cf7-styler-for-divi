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
    const SADDLE_PAGE_SLUG    = 'cf7-mate-saddle';

    /** Contact Form 7's top-level menu: `wpcf7-dashboard` since CF7 6.2, `wpcf7` before. */
    const MENU_PARENTS = ['wpcf7-dashboard', 'wpcf7'];

    /** Routable pages without their own menu item; reached through the app's tabs. */
    const TAB_PAGES = [self::RESPONSES_PAGE_SLUG, self::ANALYTICS_PAGE_SLUG, self::SADDLE_PAGE_SLUG];

    /** @var string Parent the visible item was added to. */
    private $parent = 'wpcf7';

    /** @var array<string, array{app: string, current_page: string}> Screens keyed by hook suffix. */
    private $screens = [];

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
        add_action('admin_init', [$this, 'dismiss_migration_notice']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_app'], 20);
        add_action('admin_head', [$this, 'hide_tab_pages']);
        add_filter('parent_file', [$this, 'highlight_parent']);
        add_filter('submenu_file', [$this, 'highlight_submenu']);
        add_filter('plugin_action_links_' . CF7M_BASENAME, [$this, 'add_plugin_action_links']);
    }

    public function dismiss_migration_notice()
    {
        if (isset($_GET['cf7m_dismiss_free_notice'], $_GET['_wpnonce']) && current_user_can('manage_options')
            && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'cf7m_free_notice')) {
            update_option('cf7m_free_notice_dismissed', true, false);
            wp_safe_redirect(admin_url('admin.php?page=' . self::SETTINGS_PAGE_SLUG));
            exit;
        }
    }

    /** Sites upgraded from Lite see a one-line note on Features until they dismiss it. */
    private function free_notice()
    {
        $show = !get_option('cf7m_free_notice_dismissed') && get_option('cf7m_edition_origin') === 'lite';
        return [
            'show'        => $show,
            'dismiss_url' => $show ? wp_nonce_url(admin_url('admin.php?page=' . self::SETTINGS_PAGE_SLUG . '&cf7m_dismiss_free_notice=1'), 'cf7m_free_notice') : '',
        ];
    }

    public function add_plugin_action_links($links)
    {
        $dash_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=' . self::SETTINGS_PAGE_SLUG)),
            esc_html__('Settings', 'cf7-styler-for-divi')
        );
        array_unshift($links, $dash_link);
        return $links;
    }

    public function add_menu()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $admin_page_hooks;
        // Requires Plugins keeps CF7 active on WordPress 6.5+; older sites without CF7's menu fall back to Settings.
        $this->parent = 'options-general.php';
        foreach (self::MENU_PARENTS as $slug) {
            if (isset($admin_page_hooks[$slug])) {
                $this->parent = $slug;
                break;
            }
        }

        $menu_label = apply_filters('cf7m_admin_menu_label', __('CF7 Mate', 'cf7-styler-for-divi'));

        $this->register($menu_label, $menu_label, self::SETTINGS_PAGE_SLUG, 'settings', 'settings');
        $this->register(__('Responses', 'cf7-styler-for-divi'), '', self::RESPONSES_PAGE_SLUG, 'responses', 'responses');
        $this->register(__('Analytics', 'cf7-styler-for-divi'), '', self::ANALYTICS_PAGE_SLUG, 'analytics', 'analytics');
        $this->register(__('Saddle', 'cf7-styler-for-divi'), '', self::SADDLE_PAGE_SLUG, 'settings', 'saddle');
    }

    /**
     * Tab pages are registered under the CF7 menu so WordPress resolves their
     * hook and title, then removed before the menu prints. Removing them any
     * earlier would let core reset the highlighted parent.
     */
    public function hide_tab_pages()
    {
        foreach (self::TAB_PAGES as $slug) {
            remove_submenu_page($this->parent, $slug);
        }
    }

    private function register($page_title, $menu_title, $slug, $app, $current_page)
    {
        $hook = add_submenu_page($this->parent, $page_title, $menu_title, 'manage_options', $slug, [$this, 'render_app_root']);
        if ($hook) {
            $this->screens[$hook] = ['app' => $app, 'current_page' => $current_page];
        }
    }

    private function is_hidden_page()
    {
        global $plugin_page;
        return in_array($plugin_page, self::TAB_PAGES, true);
    }

    /** Hidden pages open the Contact menu. */
    public function highlight_parent($parent_file)
    {
        return $this->is_hidden_page() ? $this->parent : $parent_file;
    }

    /** Hidden pages highlight the CF7 Mate item. */
    public function highlight_submenu($submenu_file)
    {
        return $this->is_hidden_page() ? self::SETTINGS_PAGE_SLUG : $submenu_file;
    }

    public function render_app_root()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div id="cf7-mate-app-root"></div>';
    }

    public function enqueue_app($hook)
    {
        if (!isset($this->screens[$hook]) || !current_user_can('manage_options')) {
            return;
        }
        $app          = $this->screens[$hook]['app'];
        $current_page = $this->screens[$hook]['current_page'];

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
            ['wp-i18n', 'wp-element', 'wp-api-fetch', 'wp-dom-ready'],
            $script_ver,
            true
        );

        wp_enqueue_style($style_handle, CF7M_PLUGIN_URL . $style_file, [], $style_ver);

        // Allow integrations to enqueue their own scripts/styles.
        do_action('cf7m_admin_enqueue_scripts', $app);

        $onboarding_done = get_option(Onboarding::ONBOARDING_COMPLETED_OPTION, '') === '1'
            || get_option(Onboarding::ONBOARDING_SKIPPED_OPTION, '') === '1';

        $localize = [
            'root'             => esc_url_raw(get_rest_url()),
            'ajax_url'         => admin_url('admin-ajax.php'),
            'docs_url'         => defined('CF7M_URL_DOCS') ? CF7M_URL_DOCS : '',
            'support_url'      => defined('CF7M_URL_SUPPORT') ? CF7M_URL_SUPPORT : '',
            'builders'         => $this->detect_builders(),
            'feature_metadata' => Feature_Settings::metadata(),
            'features'         => Feature_Settings::get(),
            'nonce'            => wp_create_nonce('wp_rest'),
            'pluginUrl'        => CF7M_PLUGIN_URL,
            'setup'            => [
                'show'  => !$onboarding_done,
                'nonce' => wp_create_nonce('cf7m_onboarding_nonce'),
            ],
            'free_notice'      => $this->free_notice(),
            'version'          => defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0',
            'cf7_admin_url'    => admin_url('admin.php?page=wpcf7'),
            'dash_url'         => admin_url('admin.php?page=' . self::SETTINGS_PAGE_SLUG),
            'responses_url'    => admin_url('admin.php?page=' . self::RESPONSES_PAGE_SLUG),
            'saddle'           => Saddle_Setup::config(),
            'saddle_url'       => admin_url('admin.php?page=' . self::SADDLE_PAGE_SLUG),
            'analytics_url'    => admin_url('admin.php?page=' . self::ANALYTICS_PAGE_SLUG),
            'currentPage'      => $current_page,
            'app'              => $app,
        ];

        $localize = apply_filters('cf7m_admin_app_localize', $localize, ['app' => $app, 'current_page' => $current_page]);

        wp_localize_script($script_handle, 'dcsCF7Styler', $localize);
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

}
