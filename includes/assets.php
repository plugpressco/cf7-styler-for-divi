<?php

namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Assets
{
    private static $instance;

    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Assets)) {
            self::$instance = new Assets;
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_builder_scripts']);
        add_action('divi_visual_builder_assets_before_enqueue_scripts', [$this, 'enqueue_d5_vb_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_d5_frontend_assets']);
    }

    public function enqueue_scripts()
    {
        $in_vb = function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled();
        if (!$in_vb) {
            global $post;
            if (!$post || !is_singular()) {
                return;
            }
            $has_cf7 = has_shortcode($post->post_content, 'contact-form-7')
                || (strpos($post->post_content, 'dvppl_cf7_styler') !== false)
                || (function_exists('has_block') && has_block('cf7m/styler', $post));
            if (!$has_cf7) {
                return;
            }
        }
        wp_enqueue_style(
            'cf7-styler-for-divi',
            CF7M_PLUGIN_URL . 'dist/css/bundle-4.css',
            [],
            CF7M_VERSION
        );
    }

    public function enqueue_builder_scripts()
    {
        if (!function_exists('et_core_is_fb_enabled') || !et_core_is_fb_enabled()) {
            return;
        }

        if (function_exists('et_builder_d5_enabled') && et_builder_d5_enabled()) {
            return;
        }

        wp_enqueue_style(
            'cf7-styler-for-divi-builder',
            CF7M_PLUGIN_URL . 'dist/css/bundle-4.css',
            [],
            CF7M_VERSION
        );

        wp_enqueue_script(
            'cf7-styler-for-divi-builder',
            CF7M_PLUGIN_URL . 'dist/js/bundle-4.js',
            ['jquery', 'lodash', 'react', 'react-dom'],
            CF7M_VERSION,
            true
        );

        // Legacy FF/GF modules — only for installs before v3.0.0 (2026-01-01).
        $install_date = get_option('cf7m_install_date') ?: get_option('divi_cf7_styler_install_date');
        if ($install_date && $install_date < strtotime('2026-01-01')) {
            wp_enqueue_style(
                'cf7-styler-for-divi-lagecy',
                CF7M_PLUGIN_URL . 'dist/css/lagecy.css',
                [],
                CF7M_VERSION
            );
            wp_enqueue_script(
                'cf7-styler-for-divi-lagecy',
                CF7M_PLUGIN_URL . 'dist/js/lagecy.js',
                ['jquery', 'cf7-styler-for-divi-builder'],
                CF7M_VERSION,
                true
            );
        }
    }

    public function enqueue_d5_vb_assets()
    {
        if (!class_exists('\ET\Builder\VisualBuilder\Assets\PackageBuildManager')) {
            return;
        }

        $url = CF7M_PLUGIN_URL;

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'cf7-styler-for-divi-builder-bundle-script',
            'version' => CF7M_VERSION,
            'script'  => [
                'src'                => "{$url}dist/js/bundle.js",
                'deps'               => ['divi-module-library', 'divi-vendor-wp-hooks'],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'cf7-styler-for-divi-builder-bundle-style',
            'version' => CF7M_VERSION,
            'style'   => [
                'src'                => "{$url}dist/css/bundle.css",
                'deps'               => [],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);
    }

    public function enqueue_d5_frontend_assets()
    {
        if (!function_exists('et_builder_d5_enabled') || !et_builder_d5_enabled()) {
            return;
        }
        if (function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled()) {
            return;
        }

        wp_enqueue_style(
            'cf7-styler-for-divi-d5-frontend-style',
            CF7M_PLUGIN_URL . 'dist/css/bundle.css',
            [],
            CF7M_VERSION
        );

        if (function_exists('wpcf7_enqueue_styles')) {
            wpcf7_enqueue_styles();
        }
    }
}

Assets::instance();
