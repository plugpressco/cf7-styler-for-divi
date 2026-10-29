<?php

namespace CF7_Mate\Lite\Features\Grid;

use CF7_Mate\Lite\Feature_Base;
use CF7_Mate\Lite\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Grid extends Feature_Base
{
    use Singleton;

    const ROW_TAG = 'dipe_row';

    const COLUMN_MAP = [
        'dipe_one'          => 'cf7m-col-12',
        'dipe_one_half'     => 'cf7m-col-12 cf7m-col-md-6 cf7m-col-lg-6',
        'dipe_one_third'    => 'cf7m-col-12 cf7m-col-md-4 cf7m-col-lg-4',
        'dipe_one_fourth'   => 'cf7m-col-12 cf7m-col-md-3 cf7m-col-lg-3',
        'dipe_two_third'    => 'cf7m-col-12 cf7m-col-md-8 cf7m-col-lg-8',
        'dipe_three_fourth' => 'cf7m-col-12 cf7m-col-md-9 cf7m-col-lg-9',
    ];

    const TAG_LABELS = [
        self::ROW_TAG => [
            'title'       => 'row',
            'description' => 'Generate a row shortcode',
        ],
        'dipe_one' => [
            'title'       => '1-col',
            'description' => 'Generate a full width column',
        ],
        'dipe_one_half' => [
            'title'       => '1/2-col',
            'description' => 'Generate a half width column',
        ],
        'dipe_one_third' => [
            'title'       => '1/3-col',
            'description' => 'Generate a one-third width column',
        ],
        'dipe_one_fourth' => [
            'title'       => '1/4-col',
            'description' => 'Generate a one-fourth width column',
        ],
        'dipe_two_third' => [
            'title'       => '2/3-col',
            'description' => 'Generate a two-thirds width column',
        ],
        'dipe_three_fourth' => [
            'title'       => '3/4-col',
            'description' => 'Generate a three-fourths width column',
        ],
    ];

    protected function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->register_shortcodes();
        add_action('wpcf7_admin_init', [$this, 'register_tag_generators'], 99);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    private function register_shortcodes()
    {
        add_filter('wpcf7_autop_or_not', [$this, 'disable_form_autop'], 10, 2);
        add_filter('wpcf7_form_elements', [$this, 'render_grid_tags']);

        add_shortcode(self::ROW_TAG, [$this, 'render_row']);
        foreach (array_keys(self::COLUMN_MAP) as $tag) {
            add_shortcode($tag, [$this, 'render_column']);
        }
    }

    /**
     * Grid markup needs CF7's auto-p off in forms. Newer CF7 also asks for HTML
     * mail ('for' => 'mail'); keep its default there so line breaks survive.
     */
    public function disable_form_autop($autop, $options = [])
    {
        if (is_array($options) && isset($options['for']) && 'mail' === $options['for']) {
            return $autop;
        }

        return false;
    }

    public function enqueue_admin_scripts()
    {
        wp_enqueue_script(
            'dfs-admin-js',
            CF7M_PLUGIN_URL . 'dist/js/utils.js',
            ['jquery'],
            CF7M_VERSION,
            true
        );
    }

    public function register_tag_generators()
    {
        if (!class_exists('WPCF7_TagGenerator')) {
            return;
        }
        $tg = \WPCF7_TagGenerator::get_instance();

        foreach (self::get_translated_labels() as $tag => $labels) {
            $tg->add(
                $tag,
                $labels['title'],
                [$this, 'tag_generator_panel'],
                [
                    'title'       => $labels['title'],
                    'description' => $labels['description'],
                    'version'     => '2',
                ]
            );
        }
    }

    private static function get_translated_labels()
    {
        return [
            self::ROW_TAG => [
                'title'       => __('row', 'cf7-styler-for-divi'),
                'description' => __('Generate a row shortcode', 'cf7-styler-for-divi'),
            ],
            'dipe_one' => [
                'title'       => __('1-col', 'cf7-styler-for-divi'),
                'description' => __('Generate a full width column', 'cf7-styler-for-divi'),
            ],
            'dipe_one_half' => [
                'title'       => __('1/2-col', 'cf7-styler-for-divi'),
                'description' => __('Generate a half width column', 'cf7-styler-for-divi'),
            ],
            'dipe_one_third' => [
                'title'       => __('1/3-col', 'cf7-styler-for-divi'),
                'description' => __('Generate a one-third width column', 'cf7-styler-for-divi'),
            ],
            'dipe_one_fourth' => [
                'title'       => __('1/4-col', 'cf7-styler-for-divi'),
                'description' => __('Generate a one-fourth width column', 'cf7-styler-for-divi'),
            ],
            'dipe_two_third' => [
                'title'       => __('2/3-col', 'cf7-styler-for-divi'),
                'description' => __('Generate a two-thirds width column', 'cf7-styler-for-divi'),
            ],
            'dipe_three_fourth' => [
                'title'       => __('3/4-col', 'cf7-styler-for-divi'),
                'description' => __('Generate a three-fourths width column', 'cf7-styler-for-divi'),
            ],
        ];
    }

    public function tag_generator_panel($contact_form, $args = '')
    {
        $args = wp_parse_args($args, ['id' => '']);
        $type = $args['id'];
        $tag  = '[' . $type . '][/' . $type . ']';
        // Standard insert-box markup: CF7 inserts the tag itself (inline handlers are stripped by CF7 6).
?>
        <div class="insert-box">
            <input type="text" name="<?php echo esc_attr($type); ?>" class="tag code" readonly="readonly" onfocus="this.select()" value="<?php echo esc_attr($tag); ?>">
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>">
            </div>
        </div>
<?php
    }

    /**
     * Render only the grid tags. Running every shortcode over the form would
     * also run shortcodes from other plugins, including any a visitor typed.
     */
    public function render_grid_tags($form)
    {
        if (strpos($form, '[dipe_') === false) {
            return $form;
        }

        $pattern = get_shortcode_regex(array_merge([self::ROW_TAG], array_keys(self::COLUMN_MAP)));

        return preg_replace_callback("/$pattern/", 'do_shortcode_tag', $form);
    }

    public function render_row($attrs, $content = null)
    {
        return $this->render_wrapper('cf7m-row', $attrs, $content);
    }

    public function render_column($attrs, $content = null, $tag = '')
    {
        $classes = isset(self::COLUMN_MAP[$tag]) ? self::COLUMN_MAP[$tag] : 'cf7m-col-12';
        return $this->render_wrapper('cf7m-col ' . $classes, $attrs, $content);
    }

    private function render_wrapper($class, $attrs, $content)
    {
        $attrs = shortcode_atts([], (array) $attrs);
        return sprintf(
            '<div class="%s">%s</div>',
            esc_attr($class),
            $this->render_grid_tags((string) $content)
        );
    }
}
