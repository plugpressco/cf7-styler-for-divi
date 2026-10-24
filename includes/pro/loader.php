<?php

/**
 * Advanced feature loader. All supported features are free.
 *
 * @package CF7_Mate
 * @since 3.0.0
 */

namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Premium_Loader
{
    private static $instance = null;

    private static $features = [
        'multi_column'    => [
            'file'  => 'multi-column/module.php',
            'class' => 'CF7_Mate\Features\Multi_Column\Multi_Column',
        ],
        'multi_step'      => [
            'file'  => 'multi-steps/module.php',
            'class' => 'CF7_Mate\Features\Multi_Steps\Multi_Steps',
        ],
        'database_entries' => [
            'file'  => 'entries/module.php',
            'class' => 'CF7_Mate\Features\Entries\Entries',
        ],
        // Calculator hidden until rework.
        // 'calculator'      => [
        //     'file'  => 'calculator/module.php',
        //     'class' => 'CF7_Mate\Features\Calculator\Calculator',
        // ],
        'conditional'     => [
            'file'  => 'conditional/module.php',
            'class' => 'CF7_Mate\Features\Conditional\Conditional',
        ],
        'presets'           => [
            'file'  => 'presets/module.php',
            'class' => 'CF7_Mate\Features\Presets\Presets',
        ],
        'webhook'           => [
            'file'  => 'webhook/module.php',
            'class' => 'CF7_Mate\Features\Webhook\Webhook',
        ],
        'analytics'         => [
            'file'  => 'analytics/module.php',
            'class' => 'CF7_Mate\Features\Analytics\Analytics',
        ],
        'form_scheduling'   => [
            'file'  => 'scheduling/module.php',
            'class' => 'CF7_Mate\Features\Scheduling\Scheduling',
        ],
        'email_routing'     => [
            'file'  => 'email-routing/module.php',
            'class' => 'CF7_Mate\Features\Email_Routing\Email_Routing',
        ],
        'redirect'          => [
            'file'  => 'redirect/module.php',
            'class' => 'CF7_Mate\Features\Redirect\Redirect',
        ],
        'partial_save'      => [
            'file'  => 'partial-save/module.php',
            'class' => 'CF7_Mate\Features\Partial_Save\Partial_Save',
        ],
    ];

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_bootstrap();
        $this->load_features();

        // Enqueue tag generator scripts for CF7 admin.
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

    }

    public function enqueue_admin_scripts($hook)
    {
        // Only load on CF7 edit pages.
        if (!cf7m_is_cf7_editor_hook($hook)) {
            return;
        }

        $version = defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0';

        wp_enqueue_script(
            'cf7m-tag-generators',
            CF7M_PLUGIN_URL . 'assets/pro/js/cf7m-tag-generators.js',
            [],
            $version,
            true
        );
    }

    private function load_bootstrap()
    {
        $base = CF7M_PLUGIN_PATH . 'includes/pro/';

        $files = [
            'Traits/singleton.php',
            'Traits/shortcode-atts.php',
            'feature-base.php',
            'form-tag-feature.php',
        ];

        foreach ($files as $file) {
            $path = $base . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    private function load_features()
    {
        $features = Feature_Settings::get();
        $path     = CF7M_PLUGIN_PATH . 'includes/pro/features/';

        foreach (self::$features as $key => $config) {
            if (empty($features[$key])) {
                continue;
            }

            $file_path = $path . $config['file'];
            if (!file_exists($file_path)) {
                continue;
            }

            require_once $file_path;

            if (class_exists($config['class']) && method_exists($config['class'], 'instance')) {
                $config['class']::instance();
            }
        }
    }

    public static function is_feature_enabled($feature)
    {
        return Feature_Settings::enabled($feature);
    }

    public static function get_all_features()
    {
        return Feature_Settings::get();
    }
}

Premium_Loader::instance();
