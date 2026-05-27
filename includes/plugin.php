<?php

namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
{
    private static $instance = null;

    const BASENAME = CF7M_BASENAME;
    const TEXT_DOMAIN = 'cf7-styler-for-divi';

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->maybe_load_divi5_modules();
        add_action('plugins_loaded', [$this, 'maybe_load_divi5_modules'], 99);

        $this->init();
    }

    private function init()
    {
        $this->include_files();
        $this->define_hooks();
        $this->init_components();
    }

    private function include_files()
    {
        $core_files = [
            'functions.php',
            'assets.php',
            'rest-api.php',
            'notices/review.php',
            'notices/upgrade.php',

            'admin/admin.php',
            'admin/onboarding.php',
        ];

        foreach ($core_files as $file) {
            $filepath = CF7M_PLUGIN_PATH . 'includes/' . $file;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }

        // Load lite features (star rating, range slider, separator, image, icon, grid) – free build.
        $lite_loader = CF7M_PLUGIN_PATH . 'includes/lite/loader.php';
        if (file_exists($lite_loader)) {
            require_once $lite_loader;
        }

        // Bootstrap License Manager early (before plugins_loaded priority 5) so cf7m_is_pro() can call it.
        $this->bootstrap_license_manager();

        // Load Premium Features early so form-tag registration (wpcf7_init) is hooked before CF7 runs.
        add_action('plugins_loaded', [$this, 'load_premium_loader'], 5);

        // Load builder integrations (Elementor, Bricks) when respective builder is available.
        $this->load_builder_integrations();
    }

    /**
     * Load builder integrations: Elementor and Bricks (each has its own loader).
     */
    private function load_builder_integrations()
    {
        if ($this->is_feature_enabled('elementor_module')) {
            $elementor_loader = CF7M_PLUGIN_PATH . 'includes/lite/builders/elementor/loader.php';
            if (file_exists($elementor_loader)) {
                require_once $elementor_loader;
            }
        }
        if ($this->is_feature_enabled('bricks_module')) {
            $bricks_loader = CF7M_PLUGIN_PATH . 'includes/lite/builders/bricks/loader.php';
            if (file_exists($bricks_loader)) {
                require_once $bricks_loader;
            }
        }

        if ($this->is_feature_enabled('gutenberg_module')) {
            $gutenberg_loader = CF7M_PLUGIN_PATH . 'includes/lite/builders/gutenberg/loader.php';
            if (file_exists($gutenberg_loader)) {
                require_once $gutenberg_loader;
            }
        }
    }

    private function bootstrap_license_manager()
    {
        // Bootstrap whenever pro code is available — pro build OR dev mode bypass.
        if (!function_exists('cf7m_is_pro') || !cf7m_is_pro()) {
            return;
        }

        // Load Singleton trait first (needed by License_Manager)
        $trait_file = CF7M_PLUGIN_PATH . 'includes/pro/Traits/singleton.php';
        if (file_exists($trait_file)) {
            require_once $trait_file;
        }

        $license_file = CF7M_PLUGIN_PATH . 'includes/pro/license/class-license-manager.php';
        if (!file_exists($license_file)) {
            return;
        }

        require_once $license_file;
        \CF7_Mate\License\License_Manager::instance();
    }

    public function load_premium_loader()
    {
        if (!cf7m_is_pro()) {
            return;
        }
        $premium_loader = CF7M_PLUGIN_PATH . 'includes/pro/loader.php';
        if (!file_exists($premium_loader)) {
            return;
        }
        require_once $premium_loader;
    }

    private function is_feature_enabled($feature)
    {
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
        ];
        $saved = get_option('cf7m_features', []);
        $features = wp_parse_args($saved, $defaults);

        return isset($features[$feature]) ? (bool) $features[$feature] : false;
    }

    private function define_hooks()
    {
        register_activation_hook(self::BASENAME, [$this, 'on_activation']);

        add_action('et_builder_ready', [$this, 'load_modules'], 11);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_cf7_tag_admin_styles'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_cf7_tag_admin_scripts'], 20);
    }

    public function enqueue_cf7_tag_admin_styles($hook)
    {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'wpcf7') === false) {
            return;
        }
        $url = CF7M_PLUGIN_URL . 'assets/css/cf7m-tag-admin.css';
        if (!file_exists(CF7M_PLUGIN_PATH . 'assets/css/cf7m-tag-admin.css')) {
            return;
        }
        wp_enqueue_style(
            'cf7m-tag-admin',
            $url,
            [],
            CF7M_VERSION
        );
    }

    public function enqueue_cf7_tag_admin_scripts($hook)
    {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'wpcf7') === false) {
            return;
        }
        $path = CF7M_PLUGIN_PATH . 'assets/js/cf7m-tag-generator.js';
        if (!file_exists($path)) {
            return;
        }
        wp_enqueue_script(
            'cf7m-tag-generator',
            CF7M_PLUGIN_URL . 'assets/js/cf7m-tag-generator.js',
            [],
            CF7M_VERSION,
            true
        );
    }

    public function on_activation()
    {
        $this->maybe_set_install_date();
        $this->update_plugin_version();
    }

    private function maybe_set_install_date()
    {
        if (!get_option('cf7m_install_date')) {
            $legacy = get_option('divi_cf7_styler_install_date');
            update_option('cf7m_install_date', $legacy ? $legacy : time());
        }
    }

    private function update_plugin_version()
    {
        update_option('cf7m_current_version', CF7M_VERSION);
    }


    public function load_modules()
    {
        if (!class_exists('ET_Builder_Element')) {
            return;
        }

        $divi4_base = CF7M_PLUGIN_PATH . 'includes/lite/builders/divi4/loader.php';
        if (file_exists($divi4_base)) {
            require_once $divi4_base;
        }

        $is_d5 = function_exists('et_builder_d5_enabled') && et_builder_d5_enabled();
        if ($this->is_feature_enabled('cf7_module') && !$is_d5) {
            require_once CF7M_PLUGIN_PATH . 'includes/lite/builders/divi4/CF7Styler/CF7Styler.php';
        }

        if ($this->should_load_deprecated_modules()) {
            require_once CF7M_PLUGIN_PATH . 'includes/lite/builders/divi4/FluentForms/FluentForms.php';
            require_once CF7M_PLUGIN_PATH . 'includes/lite/builders/divi4/GravityForms/GravityForms.php';
        }
    }

    private function should_load_deprecated_modules()
    {
        $install_date = get_option('cf7m_install_date') ?: get_option('divi_cf7_styler_install_date');

        // If no install date, user is new - don't load deprecated modules
        if (!$install_date) {
            return false;
        }

        $version_3_release_date = strtotime('2026-01-01');

        return $install_date < $version_3_release_date;
    }

    private function init_components()
    {
        // Review request notice (free only — gated on dismissal state internally).
        if (class_exists(__NAMESPACE__ . '\Admin_Review_Notice')) {
            Admin_Review_Notice::instance();
        }

        // Subtle Pro upgrade notice (free only — self-skips when cf7m_is_pro()).
        if (class_exists(__NAMESPACE__ . '\Admin_Upgrade_Notice')) {
            Admin_Upgrade_Notice::instance();
        }

        // Initialize onboarding
        if (class_exists(__NAMESPACE__ . '\Onboarding')) {
            Onboarding::instance();
        }

        // Initialize admin dashboard
        if (class_exists(__NAMESPACE__ . '\Admin')) {
            Admin::get_instance();
        }
    }

    public function maybe_load_divi5_modules()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        if (!$this->is_feature_enabled('cf7_module')) {
            return;
        }

        $divi5_loader = CF7M_PLUGIN_PATH . 'includes/lite/builders/divi5/loader.php';
        if (!file_exists($divi5_loader)) {
            return;
        }

        require_once $divi5_loader;
        $loaded = true;
    }
}

Plugin::instance();
