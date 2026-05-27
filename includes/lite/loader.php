<?php

/**
 * Lite features loader (Star Rating, Range Slider, Separator, Image, Icon, Grid).
 * Ships with free build; runs on every request.
 *
 * @package CF7_Mate
 * @since 3.0.0
 */

namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Lite_Loader
{
    private static $instance = null;

    private static $defaults = [
        'star_rating'       => true,
        'range_slider'      => true,
        'separator'         => true,
        'image'             => true,
        'icon'              => true,
        'grid_layout'       => true,
        'ai_form_generator' => true,
        'phone_number'      => true,
        'heading'           => true,
    ];

    private static $features = [
        'star_rating'   => [
            'file'  => 'star-rating/module.php',
            'class' => 'CF7_Mate\Lite\Features\Star_Rating\Star_Rating',
        ],
        'range_slider'  => [
            'file'  => 'range-slider/module.php',
            'class' => 'CF7_Mate\Lite\Features\Range_Slider\Range_Slider',
        ],
        'separator'     => [
            'file'  => 'separator/module.php',
            'class' => 'CF7_Mate\Lite\Features\Separator\Separator',
        ],
        'image'         => [
            'file'  => 'image/module.php',
            'class' => 'CF7_Mate\Lite\Features\Image\Image',
        ],
        'icon'          => [
            'file'  => 'icon/module.php',
            'class' => 'CF7_Mate\Lite\Features\Icon\Icon',
        ],
        'grid_layout'       => [
            'file'  => 'grid/module.php',
            'class' => 'CF7_Mate\Lite\Features\Grid\Grid',
        ],
        'ai_form_generator' => [
            'file'  => 'ai-form-generator/module.php',
            'class' => 'CF7_Mate\Lite\Features\AI_Form_Generator\AI_Form_Generator',
        ],
        'phone_number'  => [
            'file'  => 'phone-number/module.php',
            'class' => 'CF7_Mate\Lite\Features\Phone_Number\Phone_Number',
        ],
        'heading'       => [
            'file'  => 'heading/module.php',
            'class' => 'CF7_Mate\Lite\Features\Heading\Heading',
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

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        add_filter('wpcf7_form_elements', [$this, 'strip_unprocessed_preset_tags'], 15);

        // Backward compat for forms saved with the pre-3.0 plugin (dcs_ prefix).
        // Runs at priority 5 — earlier than star/range/phone (20) so any
        // [cf7m-*] tags inside legacy columns still get processed afterwards.
        add_filter('wpcf7_form_elements', [$this, 'transform_legacy_dcs_shortcodes'], 5);
    }

    public function enqueue_admin_scripts($hook)
    {
        if ('toplevel_page_wpcf7' !== $hook && 'contact_page_wpcf7-new' !== $hook) {
            return;
        }

        $version = defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0';
        $path = CF7M_PLUGIN_PATH . 'assets/lite/js/cf7m-tag-generators-lite.js';
        if (!file_exists($path)) {
            return;
        }

        wp_enqueue_script(
            'cf7m-tag-generators-lite',
            CF7M_PLUGIN_URL . 'assets/lite/js/cf7m-tag-generators-lite.js',
            [],
            $version,
            true
        );
    }

    public function strip_unprocessed_preset_tags($form)
    {
        if (strpos($form, '[cf7m-presets') === false) {
            return $form;
        }
        return preg_replace('/\[cf7m-presets[^\]]*\]|\[\/cf7m-presets\]/i', '', $form);
    }

    /**
     * Translate legacy [dcs_row] / [dcs_col_*] shortcodes (used by CF7 Styler
     * for Divi 2.x and earlier) into the current cf7m-row / cf7m-col-* div
     * markup. Forms saved with the old version otherwise render the shortcode
     * brackets as plain text on the front-end.
     *
     * Maps:
     *   [dcs_row class:foo]              -> <div class="cf7m-row dfs-row foo">
     *   [dcs_col_half]…[/dcs_col_half]   -> <div class="cf7m-col-md-6 …">…</div>
     *   [dcs_col_third]                  -> cf7m-col-md-4
     *   [dcs_col_two_third]              -> cf7m-col-md-8
     *   [dcs_col_quarter]                -> cf7m-col-md-3
     *   [dcs_col_three_quarter]          -> cf7m-col-md-9
     *   [dcs_col_full]                   -> cf7m-col-12
     *
     * Both class:NAME (CF7 form-tag syntax) and class="NAME" attribute
     * forms are recognised; smart quotes are normalised first so forms
     * saved by a rich-text editor still parse.
     */
    public function transform_legacy_dcs_shortcodes($form)
    {
        if (strpos($form, '[dcs_') === false) {
            return $form;
        }

        // Normalise smart quotes inside bracketed tags so class="x" parses
        // even when a WYSIWYG saved curly quotes.
        $form = preg_replace_callback(
            '/\[[^\[\]]*\]/',
            function ($m) {
                return str_replace(
                    ["\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x98", "\xe2\x80\x99"],
                    ['"', '"', "'", "'"],
                    $m[0]
                );
            },
            $form
        );

        $extract_class = function ($attr_str) {
            if (preg_match('/class\s*=\s*"([^"]+)"/i', $attr_str, $m)) {
                return trim($m[1]);
            }
            if (preg_match("/class\\s*=\\s*'([^']+)'/i", $attr_str, $m)) {
                return trim($m[1]);
            }
            if (preg_match('/class\s*:\s*([^\s\]]+)/i', $attr_str, $m)) {
                return trim($m[1]);
            }
            return '';
        };

        // Row open / close.
        $form = preg_replace_callback(
            '/\[dcs_row\b([^\]]*)\]/i',
            function ($m) use ($extract_class) {
                $extra = $extract_class($m[1]);
                $cls   = 'cf7m-row dfs-row' . ($extra !== '' ? ' ' . esc_attr($extra) : '');
                return '<div class="' . $cls . '">';
            },
            $form
        );
        $form = preg_replace('/\[\/dcs_row\]/i', '</div>', $form);

        // Column open / close — sizes mapped to a 12-column grid.
        $col_sizes = [
            'half'          => '6',
            'third'         => '4',
            'two_third'     => '8',
            'quarter'       => '3',
            'three_quarter' => '9',
            'full'          => '12',
        ];

        foreach ($col_sizes as $name => $cols) {
            $form = preg_replace_callback(
                '/\[dcs_col_' . preg_quote($name, '/') . '\b([^\]]*)\]/i',
                function ($m) use ($extract_class, $cols) {
                    $extra = $extract_class($m[1]);
                    // Emit both new (cf7m-*) and legacy (dfs-*) class aliases so
                    // any user CSS targeting the old names keeps working.
                    $cls = 'cf7m-col cf7m-col-md-' . $cols
                         . ' dfs-col dfs-col-md-' . $cols;
                    if ($extra !== '') {
                        $cls .= ' ' . esc_attr($extra);
                    }
                    return '<div class="' . $cls . '">';
                },
                $form
            );
            $form = preg_replace(
                '/\[\/dcs_col_' . preg_quote($name, '/') . '\]/i',
                '</div>',
                $form
            );
        }

        return $form;
    }

    private function load_bootstrap()
    {
        $base = CF7M_PLUGIN_PATH . 'includes/lite/';

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
        $saved    = get_option('cf7m_features', []);
        $features = wp_parse_args($saved, self::$defaults);
        $path     = CF7M_PLUGIN_PATH . 'includes/lite/features/';

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
}

Lite_Loader::instance();
