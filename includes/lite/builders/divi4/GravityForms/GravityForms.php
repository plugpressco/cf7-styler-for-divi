<?php

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Preserve the original public Divi integration class name.
class DCS_GFStyler extends ET_Builder_Module
{
    public $module_credits = array(
        'module_uri' => 'https://plugpress.io/cf7-mate/',
        'author'     => 'PlugPress',
        'author_uri' => 'https://plugpress.io',
    );

    public $vb_support = 'on';
    public $icon_path;

    public function init()
    {
        $this->name             = esc_html__('Gravity Forms Styler', 'cf7-styler-for-divi');
        $this->slug             = 'tfs_gravity_forms_styler';
        $this->icon_path        = plugin_dir_path(__FILE__) . '';
        $this->main_css_element = '%%order_class%%';

        $this->settings_modal_toggles = array(
            'general'  => array(
                'toggles' => array(
                    'main_content' => esc_html__('Content', 'cf7-styler-for-divi'),
                ),
            ),
            'advanced' => array(
                'toggles' => array(
                    'form_input_style'    => array(
                        'title'             => esc_html__('Form Fields', 'cf7-styler-for-divi'),
                        'tabbed_subtoggles' => true,
                        'sub_toggles'       => array(
                            'common' => array(
                                'name' => esc_html__('Common', 'cf7-styler-for-divi'),
                            ),
                            'label'   => array(
                                'name' => esc_html__('Label', 'cf7-styler-for-divi'),
                            ),
                            'field'  => array(
                                'name' => esc_html__('Input Text', 'cf7-styler-for-divi'),
                            ),
                        ),
                    ),

                    'form_radio_check'    => esc_html__('Radio & Checkbox', 'cf7-styler-for-divi'),

                    'form_error_style'    => array(
                        'title'             => esc_html__('Success / Error Message', 'cf7-styler-for-divi'),
                        'tabbed_subtoggles' => true,
                        'sub_toggles'       => array(
                            'success'  => array(
                                'name' => esc_html__('Success', 'cf7-styler-for-divi'),
                            ),
                            'error'   => array(
                                'name' => esc_html__('Error', 'cf7-styler-for-divi'),
                            ),
                        ),
                    ),

                    'form_button' => esc_html__('Button', 'cf7-styler-for-divi'),
                ),
            ),
        );
    }

    public static function get_gf()
    {
        if (!class_exists('GFForms')) {
            return ['-1' => __('You have not added any Gravity Forms yet.', 'cf7-styler-for-divi')];
        }

        $gfforms = \RGFormsModel::get_forms(null, 'title');

        if (!is_array($gfforms) || empty($gfforms)) {
            return ['-1' => __('You have not added any Gravity Forms yet.', 'cf7-styler-for-divi')];
        }

        $forms = [0 => esc_html__('Select', 'cf7-styler-for-divi')];
        foreach ($gfforms as $form) {
            $forms[$form->id] = $form->title;
        }

        return $forms;
    }

    public function get_fields()
    {
        return array_merge(
            $this->register_gf_general_fields(),
            $this->register_gf_input_style_fields(),
            $this->register_gf_radio_checkbox_fields(),
            $this->register_gf_error_style_fields(),
            $this->register_gf_computed_fields()
        );
    }

    private function register_gf_general_fields()
    {

        return array(
            'gf_form_id' => array(
                'label'           => esc_html__('Select Gravity Forms', 'cf7-styler-for-divi'),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => self::get_gf(),
                'description'     => esc_html__('Choose the Gravity Forms you want to display.', 'cf7-styler-for-divi'),
                'computed_affects' => array(
                    '__gravity_forms',
                ),
                'toggle_slug'     => 'main_content',
            ),

            'form_ajax_option' => array(
                'label'           => esc_html__('Enable AJAX Form Submission', 'cf7-styler-for-divi'),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => array(
                    'yes' => esc_html__('Yes', 'cf7-styler-for-divi'),
                    'no'  => esc_html__('No', 'cf7-styler-for-divi'),
                ),
                'description'     => esc_html__('Enable AJAX for the form.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'main_content',
            ),
        );
    }

    private function register_gf_input_style_fields()
    {

        return array(
            'form_input_padding' => array(
                'label'           => esc_html__('Padding', 'cf7-styler-for-divi'),
                'type'            => 'range',
                'option_category' => 'basic_option',
                'default'         => '10px',
                'range_settings'  => array(
                    'min'  => '0',
                    'max'  => '50',
                    'step' => '1',
                ),
                'mobile_options'   => true,
                'validate_unit'   => true,
                'description'     => esc_html__('Choose the Gravity Forms input padding.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_input_style',
                'tab_slug'        => 'advanced',
                'sub_toggle'      => 'common',
            ),

            'form_input_bgcolor' => array(
                'label'           => esc_html__('Background Color', 'cf7-styler-for-divi'),
                'type'            => 'color-alpha',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms input background color.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_input_style',
                'tab_slug'        => 'advanced',
                'sub_toggle'      => 'common',
            ),

            'form_required_color' => array(
                'label'           => esc_html__('Required Color', 'cf7-styler-for-divi'),
                'type'            => 'color-alpha',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms required color.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_input_style',
                'tab_slug'        => 'advanced',
                'sub_toggle'      => 'common',
            ),
        );
    }

    private function register_gf_radio_checkbox_fields()
    {

        return array(
            'gf_radio_check_size' => array(
                'label'           => esc_html__('Radio & Checkbox Size', 'cf7-styler-for-divi'),
                'type'            => 'range',
                'option_category' => 'basic_option',
                'default'         => '20px',
                'range_settings'  => array(
                    'min'  => '10',
                    'max'  => '50',
                    'step' => '1',
                ),
                'mobile_options'   => true,
                'validate_unit'   => true,
                'description'     => esc_html__('Choose the Gravity Forms radio & checkbox size.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_radio_check',
                'tab_slug'        => 'advanced',
            ),

            'gf_radio_check_bgcolor' => array(
                'label'           => esc_html__('Background Color', 'cf7-styler-for-divi'),
                'type'            => 'color-alpha',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms radio & checkbox background color.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_radio_check',
                'tab_slug'        => 'advanced',
            ),

            'gf_selected_color' => array(
                'label'           => esc_html__('Selected Color', 'cf7-styler-for-divi'),
                'type'            => 'color-alpha',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms selected color.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_radio_check',
                'tab_slug'        => 'advanced',
            ),

            'gf_select_color' => array(
                'label'           => esc_html__('Label Color', 'cf7-styler-for-divi'),
                'type'            => 'color-alpha',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms label color.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_radio_check',
                'tab_slug'        => 'advanced',
            ),
        );
    }

    private function register_gf_error_style_fields()
    {

        return array(
            'form_error_padding' => array(
                'label'           => esc_html__('Field Validation Padding', 'cf7-styler-for-divi'),
                'type'            => 'custom_padding',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms field validation padding.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_error_style',
                'tab_slug'        => 'advanced',
                'sub_toggle'      => 'error',
            ),

            'form_error_bgcolor' => array(
                'label'           => esc_html__('Form Error Field Background Color', 'cf7-styler-for-divi'),
                'type'            => 'color-alpha',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms error field background color.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_error_style',
                'tab_slug'        => 'advanced',
                'sub_toggle'      => 'error',
            ),

            'form_valid_padding' => array(
                'label'           => esc_html__('Form Valid Message Padding', 'cf7-styler-for-divi'),
                'type'            => 'custom_padding',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms valid message padding.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_error_style',
                'tab_slug'        => 'advanced',
                'sub_toggle'      => 'success',
            ),

            'form_valid_bgcolor' => array(
                'label'           => esc_html__('Form Valid Background Color', 'cf7-styler-for-divi'),
                'type'            => 'color-alpha',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Choose the Gravity Forms valid background color.', 'cf7-styler-for-divi'),
                'toggle_slug'     => 'form_error_style',
                'tab_slug'        => 'advanced',
                'sub_toggle'      => 'success',
            ),
        );
    }

    private function register_gf_computed_fields()
    {
        return array(
            '__gravity_forms'                    => array(
                'type'                => 'computed',
                'computed_callback'   => array('DCS_GFStyler', 'gf_styler_html'),
                'computed_depends_on' => array(
                    'gf_form_id',
                ),
            ),
        );
    }

    public function get_advanced_fields_config()
    {

        $advanced_fields                    = array();
        $advanced_fields['text']            = array();
        $advanced_fields['fonts']           = array();
        $advanced_fields['text_shadow']     = array();
        $advanced_fields['link_options']    = array();

        // Text
        $advanced_fields['fonts']['input_label'] = array(
            'label'       => esc_html__('Label', 'cf7-styler-for-divi'),
            'css'         => array(
                'main' => '
                    div%%order_class%% .tfs-gf-styler .gfield_label,
                    div%%order_class%% .tfs-gf-styler .gform-field-label,
					div%%order_class%% .tfs-gf-styler .gfield_checkbox li label,
					div%%order_class%% .tfs-gf-styler .ginput_container_consent label,
					div%%order_class%% .tfs-gf-styler .gfield_radio li label,
					div%%order_class%% .tfs-gf-styler .gsection_title,
					div%%order_class%% .tfs-gf-styler .gfield_html,
					div%%order_class%% .tfs-gf-styler .ginput_product_price,
					div%%order_class%% .tfs-gf-styler .ginput_product_price_label,
					div%%order_class%% .tfs-gf-styler .gf_progressbar_title,
					div%%order_class%% .tfs-gf-styler .gf_page_steps,
					div%%order_class%% .tfs-gf-styler .gfield_checkbox div label,
					div%%order_class%% .tfs-gf-styler .gfield_radio div label
            '
            ),
            'toggle_slug' => 'form_input_style',
            'tab_slug'    => 'advanced',
            'sub_toggle'  => 'label',
        );

        $advanced_fields['fonts']['input_field'] = array(
            'label'       => esc_html__('Field', 'cf7-styler-for-divi'),
            'css'         => array(
                'main' => '
                    div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="button"]):not([type="image"]):not([type="file"]),
					div%%order_class%% .tfs-gf-styler .ginput_container select,
					div%%order_class%% .tfs-gf-styler .ginput_container .chosen-single,
					div%%order_class%% .tfs-gf-styler .ginput_container textarea,
					div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield input::placeholder,
					div%%order_class%% .tfs-gf-styler .ginput_container textarea::placeholder,
					div%%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"]:checked + label:before,
					div%%order_class%% .tfs-gf-styler .ginput_container_consent input[type="checkbox"] + label:before'
            ),
            'toggle_slug' => 'form_input_style',
            'tab_slug'    => 'advanced',
            'sub_toggle'  => 'field',
        );

        $advanced_fields['fonts']['error'] = array(
            'css'         => array(
                'main' => '
                    div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_validation_errors, 
                    %%order_class%% .tfs-gf-styler .gform_wrapper div.gform_validation_errors'
            ),
            'toggle_slug' => 'form_error_style',
            'tab_slug'    => 'advanced',
            'sub_toggle'  => 'error',
        );

        $advanced_fields['fonts']['success'] = array(
            'css'         => array(
                'main' => '%%order_class%% .tfs-gf-styler .gform_confirmation_message'
            ),
            'toggle_slug' => 'form_error_style',
            'tab_slug'    => 'advanced',
            'sub_toggle'  => 'success',
        );

        // Borders
        $advanced_fields['borders']['input_field'] = array(
            'label_prefix' => esc_html__('Field', 'cf7-styler-for-divi'),
            'css' => array(
                'main'      => array(
                    'border_radii'  => ('
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=email],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=text],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=password],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=url],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=tel],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=number],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=date],
                        %%order_class%% .tfs-gf-styler .gform_wrapper select,
                        %%order_class%% .tfs-gf-styler .gform_wrapper .chosen-single,
                        %%order_class%% .tfs-gf-styler .gform_wrapper .chosen-choices,
                        %%order_class%% .tfs-gf-styler .gform_wrapper .chosen-container .chosen-drop,
                        %%order_class%% .tfs-gf-styler .gform_wrapper textarea,
                        %%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"] + label:before,
                        %%order_class%% .tfs-gf-styler .ginput_container_consent input[type="checkbox"] + label:before
                    '),
                    'border_styles' => (' 
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=email],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=text],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=password],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=url],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=tel],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=number],
                        %%order_class%% .tfs-gf-styler .gform_wrapper input[type=date],
                        %%order_class%% .tfs-gf-styler .gform_wrapper select,
                        %%order_class%% .tfs-gf-styler .gform_wrapper .chosen-single,
                        %%order_class%% .tfs-gf-styler .gform_wrapper textarea,
                        %%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"] + label:before,
                        %%order_class%% .tfs-gf-styler .ginput_container_consent input[type="checkbox"] + label:before,
                        %%order_class%% .tfs-gf-styler .gfield_radio input[type="radio"] + label:before,
                    '),
                ),
                'important' => 'all',
            ),
            'toggle_slug' => 'form_input_style',
            'tab_slug'    => 'advanced',
            'sub_toggle'  => 'common',
        );

        $advanced_fields['borders']['radio_check'] = array(
            'css' => array(
                'main'      => array(
                    'border_radii'  => ('
                        %%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"] + label:before,
					    %%order_class%% .tfs-gf-styler .ginput_container_consent input[type="checkbox"] + label:before,
					    %%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"],
					    %%order_class%% .tfs-gf-styler .ginput_container_consent input[type="checkbox"]
                    '),
                    'border_styles' => ('
                        %%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"] + label:before,
                        %%order_class%% .tfs-gf-styler .gfield_radio input[type="radio"] + label:before,
                        %%order_class%% .tfs-gf-styler .gfield_radio .gfield_radio .gchoice_label label:before,
                        %%order_class%% .tfs-gf-styler .ginput_container_consent input[type="checkbox"] + label:before,
                        %%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"],
                        %%order_class%% .tfs-gf-styler .gfield_radio input[type="radio"],
                        %%order_class%% .tfs-gf-styler .ginput_container_consent input[type="checkbox"]
                    '),
                ),
                'important' => 'all',
            ),
            'toggle_slug' => 'form_radio_check',
            'tab_slug'    => 'advanced',
        );

        $advanced_fields['borders']['error'] = array(
            'css' => array(
                'main'      => array(
                    'border_radii'  => ('div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_validation_errors'
                    ),
                    'border_styles' => ('div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_validation_errors'),
                ),
                'important' => 'all',
            ),
            'toggle_slug' => 'form_error_style',
            'tab_slug'    => 'advanced',
            'sub_toggle'  => 'error',
        );

        $advanced_fields['borders']['success'] = array(
            'css' => array(
                'main'      => array(
                    'border_radii'  => ('div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_confirmation_message'),
                    'border_styles' => ('div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_confirmation_message'),
                ),
                'important' => 'all',
            ),
            'toggle_slug' => 'form_error_style',
            'tab_slug'    => 'advanced',
            'sub_toggle'  => 'success',
        );

        // Button
        $advanced_fields['button']['submit_button'] = array(
            'label'          => esc_html__('Button', 'cf7-styler-for-divi'),
            'css'            => array(
                'main'      => '%%order_class%% .tfs-gf-styler input[type="submit"], %%order_class%% .tfs-gf-styler input[type="button"], %%order_class%% .tfs-gf-styler .gf_progressbar_percentage span, %%order_class%% .tfs-gf-styler .percentbar_blue span',
                'important' => 'all',
            ),
            'box_shadow'     => array(
                'css' => array(
                    'main' => '%%order_class%% .tfs-gf-styler input[type="submit"], %%order_class%% .tfs-gf-styler input[type="button"]',
                ),
            ),
            'margin_padding' => array(
                'css' => array(
                    'main'      => '%%order_class%% .tfs-gf-styler input[type="submit"], %%order_class%% .tfs-gf-styler input[type="button"]',
                    'important' => 'all',
                ),
            ),
            'hide_icon'      => true,
            'toggle_slug' => 'form_button',
            'tab_slug'    => 'advanced',
        );

        return $advanced_fields;
    }

    public static function gf_styler_html($args = array(), $conditional_tags = array(), $current_page = array())
    {
        $gf_form_id = $args['gf_form_id'];
        if (!class_exists('GFForms')) {
            return esc_html__('Please install Gravity Forms plugin.', 'cf7-styler-for-divi');
        }

        return self::generate_form_output($gf_form_id);
    }

    public function render($attrs, $content, $render_slug)
    {

        $this->dt_generate_styles($render_slug);

        $gf_form_id = $this->props['gf_form_id'];

        $ajax = $this->props['form_ajax_option'] === 'yes' ? true : false;

        if (!class_exists('GFForms')) {
            return esc_html__('Please install Gravity Forms plugin.', 'cf7-styler-for-divi');
        }

        $output = '<div class="tfs-gf-styler">';
        $output .= self::generate_form_output($gf_form_id, $ajax);
        $output .= '</div>';

        return $output;
    }

    private static function generate_form_output($gf_form_id, $ajax = false)
    {
        if ('0' !== $gf_form_id && $gf_form_id) {
            return do_shortcode(sprintf('[gravityform id="%1$s" ajax="' . $ajax . '" title="false"]', absint($gf_form_id)));
        }
        return esc_html__('Please select a Gravity Form.', 'cf7-styler-for-divi');
    }

    public function dt_generate_styles($render_slug)
    {

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_input_padding',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gform_wrapper form .gform_body input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="button"]):not([type="image"]):not([type="file"]), 
                div%%order_class%% .tfs-gf-styler .gform_wrapper textarea, 
                div%%order_class%% .tfs-gf-styler .ginput_container select
            ',
            'css_property'   => 'padding',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_input_padding',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gfield_radio input[type="radio"] + label:before
            ',
            'css_property'   => 'height',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_input_padding',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gfield_radio input[type="radio"] + label:before
            ',
            'css_property'   => 'width',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_input_bgcolor',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gform_wrapper input[type=email],
                div%%order_class%% .tfs-gf-styler .gform_wrapper input[type=text],
                div%%order_class%% .tfs-gf-styler .gform_wrapper input[type=password],
                div%%order_class%% .tfs-gf-styler .gform_wrapper input[type=url],
                div%%order_class%% .tfs-gf-styler .gform_wrapper input[type=tel],
                div%%order_class%% .tfs-gf-styler .gform_wrapper input[type=number],
                div%%order_class%% .tfs-gf-styler .gform_wrapper input[type=date],
                div%%order_class%% .tfs-gf-styler .gform_wrapper select,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .chosen-container-single .chosen-single,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .chosen-container-multi .chosen-choices,
                div%%order_class%% .tfs-gf-styler .gform_wrapper textarea,
                div%%order_class%% .tfs-gf-styler .gfield_checkbox input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gfield_radio input[type="radio"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gf_progressbar,
                div%%order_class%% .tfs-gf-styler .ginput_container_consent input[type="checkbox"] + label:before',
            'css_property'   => 'background-color',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_required_color',
            'selector'       => 'div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_required',
            'css_property'   => 'color',
            'render_slug'    => $render_slug,
        ]);

        // Radio & Checkbox
        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'gf_radio_check_size',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_checkbox input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio input[type="radio"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio .gchoice_label label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .ginput_container_consent input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_checkbox input[type="checkbox"],
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio input[type="radio"],
                div%%order_class%% .tfs-gf-styler .gform_wrapper .ginput_container_consent input[type="checkbox"]',
            'css_property'   => 'width',
            'render_slug'    => $render_slug,
            'important'      => true,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'gf_radio_check_size',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_checkbox input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio input[type="radio"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio .gchoice_label label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .ginput_container_consent input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_checkbox input[type="checkbox"],
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio input[type="radio"],
                div%%order_class%% .tfs-gf-styler .gform_wrapper .ginput_container_consent input[type="checkbox"]',
            'css_property'   => 'height',
            'render_slug'    => $render_slug,
            'important'      => true,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'gf_radio_check_bgcolor',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_checkbox input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio input[type="radio"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio .gchoice_label label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .ginput_container_consent input[type="checkbox"] + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_checkbox input[type="checkbox"],
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_radio input[type="radio"],
                div%%order_class%% .tfs-gf-styler .gform_wrapper .ginput_container_consent input[type="checkbox"]
            ',
            'css_property'   => 'background-color',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'gf_selected_color',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_checkbox input[type="checkbox"]:checked + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .gfield_checkbox input[type="checkbox"]:checked:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .ginput_container_consent input[type="checkbox"]:checked + label:before,
                div%%order_class%% .tfs-gf-styler .gform_wrapper .ginput_container_consent input[type="checkbox"]:checked:before
             ',
            'css_property'   => 'color',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'gf_selected_color',
            'selector'       => 'div%%order_class%% .tfs-gf-styler .gform_wrapper input[type=radio]:checked:before',
            'css_property'   => 'background-color',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'gf_select_color',
            'selector'       => '
                div%%order_class%% .tfs-gf-styler .gfield_checkbox div label
                div%%order_class%% .tfs-gf-styler .gfield_radio div label
                div%%order_class%% .tfs-gf-styler .ginput_container_consent label
                div%%order_class%% .tfs-gf-styler .gfield_checkbox li label
                div%%order_class%% .tfs-gf-styler .gfield_radio li label',
            'css_property'   => 'color',
            'render_slug'    => $render_slug,
        ]);

        if ($this->props['form_error_padding']) {
            $value = explode('|', $this->props['form_error_padding']);
            $this->props['form_error_padding'] = ($value[0] ? $value[0] : 0) . ' ' . ($value[1] ? $value[1] : 0) . ' ' . ($value[2] ? $value[2] : 0) . ' ' . ($value[3] ? $value[3] : 0);
        }

        if ($this->props['form_valid_padding']) {
            $value = explode('|', $this->props['form_valid_padding']);
            $this->props['form_valid_padding'] = ($value[0] ? $value[0] : 0) . ' ' . ($value[1] ? $value[1] : 0) . ' ' . ($value[2] ? $value[2] : 0) . ' ' . ($value[3] ? $value[3] : 0);
        }

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_error_padding',
            'selector'       => 'div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_validation_errors',
            'css_property'   => 'padding',
            'type'           => 'custom_margin',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_error_bgcolor',
            'selector'       => 'div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_validation_errors',
            'css_property'   => 'background-color',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_valid_padding',
            'selector'       => 'div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_confirmation_message',
            'css_property'   => 'padding',
            'type'           => 'custom_margin',
            'render_slug'    => $render_slug,
        ]);

        $this->generate_styles([
            'hover'          => false,
            'base_attr_name' => 'form_valid_bgcolor',
            'selector'       => 'div%%order_class%% .tfs-gf-styler .gform_wrapper div.gform_confirmation_message',
            'css_property'   => 'background-color',
            'render_slug'    => $render_slug,
        ]);
    }
}

new DCS_GFStyler();
