<?php
/**
 * Multi-Step Forms Module.
 *
 * @package CF7_Mate\Features\Multi_Steps
 * @since 3.0.0
 */

namespace CF7_Mate\Features\Multi_Steps;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Shortcode_Atts_Trait;
use CF7_Mate\Pro\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Multi_Steps extends Pro_Feature_Base
{
    use Shortcode_Atts_Trait;
    use Singleton;

    /** @inheritdoc */
    protected function __construct()
    {
        parent::__construct();
    }

    /** @inheritdoc */
    protected function init()
    {
        add_filter('wpcf7_form_elements', [$this, 'process_shortcodes'], 20, 1);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generators'], 25);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /** @var string[] Step titles collected during shortcode processing. */
    private $step_titles = [];

    /** @var string Progress style collected from the first step. */
    private $progress_style = 'circles';

    /**
     * Process [cf7m-step] shortcodes in form markup.
     *
     * @param string $form
     * @return string
     */
    public function process_shortcodes($form)
    {
        if (strpos($form, '[cf7m-step') === false) {
            return $form;
        }

        $step_count          = 0;
        $this->step_titles   = [];
        $this->progress_style = 'circles';

        $form = preg_replace_callback('/\[cf7m-step\s*([^\]]*)\](.*?)\[\/cf7m-step\]/s', function ($matches) use (&$step_count) {
            $step_count++;
            $atts  = $this->parse_atts($matches[1]);
            /* translators: %d: step number */
            $title = isset($atts['title']) ? $atts['title'] : sprintf(__('Step %d', 'cf7-styler-for-divi'), $step_count);

            $this->step_titles[] = $title;

            // Capture style from the first step only.
            if ($step_count === 1 && isset($atts['style'])) {
                $allowed = ['circles', 'progress-bar', 'connected'];
                $this->progress_style = in_array($atts['style'], $allowed, true) ? $atts['style'] : 'circles';
            }

            $inner_content = $matches[2];

            return sprintf(
                '<div class="cf7m-step %s" data-step="%d" data-title="%s">%s</div>',
                $step_count === 1 ? 'active' : '',
                $step_count,
                esc_attr($title),
                $inner_content
            );
        }, $form);

        if ($step_count > 0) {
            $progress = $this->build_progress_html($step_count);

            $nav  = '<div class="cf7m-steps-nav">';
            $nav .= '<button type="button" class="cf7m-prev-step" style="display:none;">' . esc_html__('Previous', 'cf7-styler-for-divi') . '</button>';
            $nav .= '<button type="button" class="cf7m-next-step">' . esc_html__('Next', 'cf7-styler-for-divi') . '</button>';
            $nav .= '</div>';

            $form = sprintf(
                '<div class="cf7m-multistep-form" data-total-steps="%d" data-progress-style="%s">%s<div class="cf7m-steps-container">%s</div>%s</div>',
                $step_count,
                esc_attr($this->progress_style),
                $progress,
                $form,
                $nav
            );
        }

        return $form;
    }

    /**
     * Build progress indicator HTML based on style.
     *
     * @param int $step_count Total number of steps.
     * @return string
     */
    private function build_progress_html($step_count)
    {
        switch ($this->progress_style) {
            case 'progress-bar':
                return $this->build_progress_bar($step_count);
            case 'connected':
                return $this->build_progress_connected($step_count);
            default:
                return $this->build_progress_circles($step_count);
        }
    }

    /**
     * Default circles progress indicator.
     */
    private function build_progress_circles($step_count)
    {
        $html = '<div class="cf7m-steps-progress">';
        for ($i = 1; $i <= $step_count; $i++) {
            $html .= sprintf(
                '<span class="cf7m-progress-step %s" data-step="%d">%d</span>',
                $i === 1 ? 'active' : '',
                $i,
                $i
            );
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Progress bar style indicator.
     */
    private function build_progress_bar($step_count)
    {
        $pct   = $step_count > 1 ? round(1 / $step_count * 100) : 100;
        $label = sprintf(
            /* translators: %1$d current step, %2$d total steps */
            __('Step %1$d/%2$d', 'cf7-styler-for-divi'),
            1,
            $step_count
        );

        $html  = '<div class="cf7m-steps-progress cf7m-progress--bar">';
        $html .= '<div class="cf7m-progress-bar-track"><div class="cf7m-progress-bar-fill" style="width:' . $pct . '%"></div></div>';
        $html .= '<div class="cf7m-progress-bar-label">' . esc_html($label) . '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Connected circles with labels progress indicator.
     */
    private function build_progress_connected($step_count)
    {
        $html = '<div class="cf7m-steps-progress cf7m-progress--connected">';
        for ($i = 1; $i <= $step_count; $i++) {
            if ($i > 1) {
                $html .= '<div class="cf7m-progress-connector"></div>';
            }
            /* translators: %d: step number */
            $title = isset($this->step_titles[$i - 1]) ? $this->step_titles[$i - 1] : sprintf(__('Step %d', 'cf7-styler-for-divi'), $i);
            $html .= sprintf(
                '<div class="cf7m-progress-item %s" data-step="%d"><span class="cf7m-progress-step">%d</span><span class="cf7m-progress-label">%s</span></div>',
                $i === 1 ? 'active' : '',
                $i,
                $i,
                esc_html($title)
            );
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Register CF7 tag generators for [cf7m-step].
     */
    public function add_tag_generators()
    {
        if (class_exists('WPCF7_TagGenerator')) {
            \WPCF7_TagGenerator::get_instance()->add(
                'cf7m-step',
                __('step', 'cf7-styler-for-divi'),
                [$this, 'tag_generator_step'],
                ['version' => '2']
            );
        }
    }

    /**
     * Tag generator callback for [cf7m-step].
     *
     * @param \WPCF7_ContactForm $contact_form
     * @param string $options
     */
    public function tag_generator_step($contact_form, $options = '')
    {
        ?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e('Multi-Step', 'cf7-styler-for-divi'); ?></legend>
                <table class="form-table"><tbody>
                    <tr><th><label><?php esc_html_e('Step Title', 'cf7-styler-for-divi'); ?></label></th><td><input type="text" name="title" class="oneline" placeholder="Step 1" /></td></tr>
                    <tr><th><label><?php esc_html_e('Progress Style', 'cf7-styler-for-divi'); ?></label></th><td>
                        <select name="style" class="oneline">
                            <option value="circles"><?php esc_html_e('Circles (default)', 'cf7-styler-for-divi'); ?></option>
                            <option value="progress-bar"><?php esc_html_e('Progress Bar', 'cf7-styler-for-divi'); ?></option>
                            <option value="connected"><?php esc_html_e('Connected with Labels', 'cf7-styler-for-divi'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Add this to the first [cf7m-step] tag only.', 'cf7-styler-for-divi'); ?></p>
                    </td></tr>
                </tbody></table>
            </fieldset>
        </div>
        <div class="insert-box">
            <input type="text" name="cf7m-step" class="tag code" readonly="readonly" onfocus="this.select()" value='[cf7m-step title:"Step 1"]

[/cf7m-step]' />
            <div class="submitbox"><input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'cf7-styler-for-divi'); ?>" /></div>
        </div>
        <script>
        (function(){
            var box = document.currentScript.closest('.cf7m-tg-pane') || document.currentScript.parentElement;
            var titleEl = box.querySelector('input[name="title"]');
            var styleEl = box.querySelector('select[name="style"]');
            var tagEl   = box.querySelector('input.tag.code');
            function rebuild(){
                var t = (titleEl.value || 'Step 1').replace(/"/g, '\\"');
                var s = styleEl.value;
                var tag = '[cf7m-step';
                if(s && s !== 'circles') tag += ' style:"' + s + '"';
                tag += ' title:"' + t + '"]\n\n[/cf7m-step]';
                tagEl.value = tag;
            }
            if(titleEl) titleEl.addEventListener('input', rebuild);
            if(styleEl) styleEl.addEventListener('change', rebuild);
        })();
        </script>
        <?php
    }

    /**
     * Enqueue multi-step front-end assets.
     */
    public function enqueue_assets()
    {
        if (!Pro_Feature_Base::page_has_cf7_form()) {
            return;
        }

        $version = defined('CF7M_VERSION') ? CF7M_VERSION : '3.0.0';
        wp_enqueue_style(
            'cf7m-pro-forms',
            CF7M_PLUGIN_URL . 'assets/pro/css/cf7m-pro-forms.css',
            [],
            $version
        );
        wp_enqueue_script(
            'cf7m-multi-steps',
            CF7M_PLUGIN_URL . 'assets/pro/js/cf7m-multi-steps.js',
            [],
            $version,
            true
        );
        wp_localize_script('cf7m-multi-steps', 'cf7mMultiSteps', [
            'required' => __('Please fill out this field.', 'cf7-styler-for-divi'),
            /* translators: %1$d current step, %2$d total steps */
            'stepLabel' => __('Step %1$d/%2$d', 'cf7-styler-for-divi'),
        ]);
    }
}
