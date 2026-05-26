<?php

class DCS_CF7Styler extends ET_Builder_Module
{
	public $module_credits = array(
		'module_uri' => 'https://cf7mate.com',
		'author'     => 'PlugPress',
		'author_uri' => 'https://cf7mate.com',
	);

	public $icon_path;

	public function init()
	{
		$this->vb_support       = 'on';
		$this->slug             = 'dvppl_cf7_styler';
		$this->name             = esc_html__('CF7 Styler', 'cf7-styler-for-divi');
		$this->main_css_element = '%%order_class%%';

		$this->settings_modal_toggles = array(
			'general'  => array(
				'toggles' => array(
					'general' => esc_html__('General', 'cf7-styler-for-divi'),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'common'         => esc_html__('Common', 'cf7-styler-for-divi'),
					'form_header'    => array(
						'title'             => esc_html__('Form Header', 'cf7-styler-for-divi'),
						'tabbed_subtoggles' => true,
						'sub_toggles'       => array(
							'common_tab' => array(
								'name' => esc_html__('Common', 'cf7-styler-for-divi'),
							),
							'title_tab'  => array(
								'name' => esc_html__('Title', 'cf7-styler-for-divi'),
							),
							'text_tab'   => array(
								'name' => esc_html__('Text', 'cf7-styler-for-divi'),
							),
						),
					),
					'form_text'      => array(
						'title'             => esc_html__('Form Text', 'cf7-styler-for-divi'),
						'tabbed_subtoggles' => true,
						'sub_toggles'       => array(
							'field_tab'       => array(
								'name' => esc_html__('Field', 'cf7-styler-for-divi'),
							),
							'label_tab'       => array(
								'name' => esc_html__('Label', 'cf7-styler-for-divi'),
							),

							'placeholder_tab' => array(
								'name' => esc_html__('Placeholder', 'cf7-styler-for-divi'),
							),
						),
					),
					'form_field'     => esc_html__('Fields', 'cf7-styler-for-divi'),
					'radio_checkbox' => esc_html__('Radio & Checkbox', 'cf7-styler-for-divi'),
					'submit_button'  => esc_html__('Button', 'cf7-styler-for-divi'),
					'suc_err_msg'    => esc_html__('Message', 'cf7-styler-for-divi'),
				),
			),
		);

		$this->custom_css_fields = array(
			'cf7_fields' => array(
				'label'    => esc_html__('Form Fields', 'cf7-styler-for-divi'),
				'selector' => '%%order_class%% .dipe-cf7-styler input',
			),
			'cf7_labels' => array(
				'label'    => esc_html__('Form Label', 'cf7-styler-for-divi'),
				'selector' => '%%order_class%% .dipe-cf7-styler label',
			),
		);
	}

	public function get_fields()
	{
		return array(
			'use_form_header'              => array(
				'label'       => esc_html__('Show Form Header', 'cf7-styler-for-divi'),
				'type'        => 'yes_no_button',
				'options'     => array(
					'on'  => esc_html__('Yes', 'cf7-styler-for-divi'),
					'off' => esc_html__('No', 'cf7-styler-for-divi'),
				),
				'default'     => 'off',
				'toggle_slug' => 'general',
				'affects'     => array(
					'title_font',
					'title_text_color',
					'title_line_height',
					'title_font_size',
					'title_all_caps',
					'title_letter_spacing',
					'title_text_shadow',
					'text_font',
					'text_text_color',
					'text_line_height',
					'text_font_size',
					'text_all_caps',
					'text_letter_spacing',
					'text_text_shadow',
				),
			),

			'form_header_title'            => array(
				'label'       => esc_html__('Header Title', 'cf7-styler-for-divi'),
				'type'        => 'text',
				'show_if'     => array(
					'use_form_header' => 'on',
				),
				'toggle_slug' => 'general',
			),

			'form_header_text'             => array(
				'label'       => esc_html__('Header Text', 'cf7-styler-for-divi'),
				'type'        => 'text',
				'show_if'     => array(
					'use_form_header' => 'on',
				),
				'toggle_slug' => 'general',
			),

			'use_icon'                     => array(
				'label'       => esc_html__('Use Icon', 'cf7-styler-for-divi'),
				'type'        => 'yes_no_button',
				'options'     => array(
					'on'  => esc_html__('Yes', 'cf7-styler-for-divi'),
					'off' => esc_html__('No', 'cf7-styler-for-divi'),
				),
				'show_if'     => array(
					'use_form_header' => 'on',
				),
				'default'     => 'off',
				'toggle_slug' => 'general',
			),

			'header_img'                   => array(
				'label'              => esc_html__('Header Image', 'cf7-styler-for-divi'),
				'type'               => 'upload',
				'upload_button_text' => esc_attr__('Upload an image', 'cf7-styler-for-divi'),
				'choose_text'        => esc_attr__('Choose an Image', 'cf7-styler-for-divi'),
				'update_text'        => esc_attr__('Set As Image', 'cf7-styler-for-divi'),
				'show_if'            => array(
					'use_icon'        => 'off',
					'use_form_header' => 'on',
				),
				'toggle_slug'        => 'general',
			),

			'header_icon'                  => array(
				'label'       => esc_html__('Header Icon', 'cf7-styler-for-divi'),
				'type'        => 'select_icon',
				'show_if'     => array(
					'use_form_header' => 'on',
					'use_icon'        => 'on',
				),
				'toggle_slug' => 'general',
			),

			'form_header_bg'               => array(
				'label'        => esc_html__('Form Header Background', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'use_form_header' => 'on',
				),
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'form_header',
				'sub_toggle'   => 'common_tab',
			),

			'form_header_padding'          => array(
				'label'          => esc_html__('Header Padding', 'cf7-styler-for-divi'),
				'type'           => 'custom_padding',
				'default'        => '0px|0px|0px|0px',
				'show_if'        => array(
					'use_form_header' => 'on',
				),
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'form_header',
				'sub_toggle'     => 'common_tab',
				'mobile_options' => true,
			),

			'form_header_bottom'           => array(
				'label'          => esc_html__('Bottom Spacing', 'cf7-styler-for-divi'),
				'type'           => 'range',
				'default_unit'   => 'px',
				'default'        => '0px',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'show_if'        => array(
					'use_form_header' => 'on',
				),
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'form_header',
				'sub_toggle'     => 'common_tab',
			),

			'form_header_img_bg'           => array(
				'label'        => esc_html__('Header Image/Icon Background', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'use_form_header' => 'on',
				),
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'form_header',
				'sub_toggle'   => 'common_tab',
			),

			'form_header_icon_color'       => array(
				'label'        => esc_html__('Header Icon Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'use_form_header' => 'on',
					'use_icon'        => 'on',
				),
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'form_header',
				'sub_toggle'   => 'common_tab',
			),

			'form_bg'                      => array(
				'label'        => esc_html__('Form Background', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'common',
			),

			'form_padding'                 => array(
				'label'          => esc_html__('Form Padding', 'cf7-styler-for-divi'),
				'type'           => 'custom_padding',
				'default'        => '0px|0px|0px|0px',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'common',
				'mobile_options' => true,
			),

			'use_form_button_fullwidth'    => array(
				'label'       => esc_html__('Fullwidth Button', 'cf7-styler-for-divi'),
				'type'        => 'yes_no_button',
				'options'     => array(
					'on'  => esc_html__('Yes', 'cf7-styler-for-divi'),
					'off' => esc_html__('No', 'cf7-styler-for-divi'),
				),
				'default'     => 'off',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'common',
			),

			'button_alignment'             => array(
				'label'       => esc_html__('Button Alignment', 'cf7-styler-for-divi'),
				'type'        => 'select',
				'options'     => array(
					'left'   => esc_html__('Left', 'cf7-styler-for-divi'),
					'center' => esc_html__('Center', 'cf7-styler-for-divi'),
					'right'  => esc_html__('Right', 'cf7-styler-for-divi'),
				),
				'show_if'     => array(
					'use_form_button_fullwidth' => 'off',
				),
				'default'     => 'left',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'common',
			),

			'cf7'  => array(
				'label'            => esc_html__('Select Form', 'cf7-styler-for-divi'),
				'type'             => 'select',
				'option_category'  => 'layout',
				'options'          => cf7m_get_contact_forms(),
				'description'      => esc_html__('Choose a contact form to display.', 'cf7-styler-for-divi'),
				'computed_affects' => array(
					'__cf7form',
				),
				'toggle_slug'      => 'general',
			),

			'form_field_height'            => array(
				'label'          => esc_html__('Common Text Fields Height', 'cf7-styler-for-divi'),
				'description'    => esc_html__('Here you can define static height for the common text fields.', 'cf7-styler-for-divi'),
				'type'           => 'range',
				'default_unit'   => 'px',
				'mobile_options' => true,
				'range_settings' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'form_field',
			),

			'form_field_padding'           => array(
				'label'          => esc_html__('Form Field Padding', 'cf7-styler-for-divi'),
				'description'    => esc_html__('Here you can define a custom padding for each field.', 'cf7-styler-for-divi'),
				'type'           => 'custom_padding',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'form_field',
				'default'        => '10px|15px|10px|15px',
				'mobile_options' => true,
			),

			'form_background_color'        => array(
				'label'        => esc_html__('Form Field Background Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'default'      => '#f5f5f5',
				'toggle_slug'  => 'form_field',
				'tab_slug'     => 'advanced',
			),

			'form_field_active_color'      => array(
				'label'        => esc_html__('Form Field Active Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'form_field',
			),

			'form_field_spacing'           => array(
				'label'          => esc_html__('Form Field Spacing Bottom', 'cf7-styler-for-divi'),
				'description'    => esc_html__('Set how much space the form field will take at the bottom.', 'cf7-styler-for-divi'),
				'type'           => 'range',
				'default_unit'   => 'px',
				'default'        => '20px',
				'mobile_options' => true,
				'range_settings' => array(
					'min'  => '0',
					'max'  => '200',
					'step' => '1',
				),
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'form_field',
			),

			'form_label_spacing'           => array(
				'label'          => esc_html__('Form Label Spacing Bottom', 'cf7-styler-for-divi'),
				'description'    => esc_html__('Set how much space the form label will take at the bottom.', 'cf7-styler-for-divi'),
				'type'           => 'range',
				'default_unit'   => 'px',
				'default'        => '7px',
				'mobile_options' => true,
				'range_settings' => array(
					'min'  => '0',
					'max'  => '200',
					'step' => '1',
				),
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'form_text',
				'sub_toggle'     => 'label_tab',
			),

			'cr_custom_styles'             => array(
				'label'            => esc_html__('Enable Custom Styles', 'cf7-styler-for-divi'),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => array(
					'on'  => esc_html__('Yes', 'cf7-styler-for-divi'),
					'off' => esc_html__('No', 'cf7-styler-for-divi'),
				),
				'default'          => 'off',
				'computed_affects' => array(
					'__cf7form',
				),
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'radio_checkbox',
			),

			'cr_size'                      => array(
				'label'           => esc_html__('Size', 'cf7-styler-for-divi'),
				'type'            => 'range',
				'option_category' => 'layout',
				'default_unit'    => 'px',
				'default'         => '20px',
				'range_settings'  => array(
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'radio_checkbox',
				'show_if'         => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_background_color'          => array(
				'label'        => esc_html__('Background Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'radio_checkbox',
				'show_if'      => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_selected_color'            => array(
				'label'        => esc_html__('Selected Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'default'      => '#222222',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'radio_checkbox',
				'show_if'      => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_border_color'              => array(
				'label'        => esc_html__('Border Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'default'      => '#222222',
				'toggle_slug'  => 'radio_checkbox',
				'tab_slug'     => 'advanced',
				'show_if'      => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_border_size'               => array(
				'label'           => esc_html__('Border Size', 'cf7-styler-for-divi'),
				'type'            => 'range',
				'option_category' => 'layout',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'radio_checkbox',
				'default_unit'    => 'px',
				'default'         => '1px',
				'range_settings'  => array(
					'min'  => '0',
					'max'  => '5',
					'step' => '1',
				),
				'show_if'         => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_label_color'               => array(
				'label'        => esc_html__('Label Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'radio_checkbox',
				'show_if'      => array(
					'cr_custom_styles' => 'on',
				),
			),

			// Success / Error Message.
			'cf7_message_padding'          => array(
				'label'          => esc_html__('Message Padding', 'cf7-styler-for-divi'),
				'type'           => 'range',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'suc_err_msg',
				'default_unit'   => 'px',
				'default'        => '0px',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				),
			),

			'cf7_message_margin_top'       => array(
				'label'          => esc_html__('Message Margin Top', 'cf7-styler-for-divi'),
				'type'           => 'range',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'suc_err_msg',
				'default_unit'   => 'px',
				'default'        => '0px',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				),
			),
			'cf7_message_alignment'        => array(
				'label'            => esc_html__('Message Text Alignment', 'cf7-styler-for-divi'),
				'description'      => esc_html__('Align message text to the left, right or center.', 'cf7-styler-for-divi'),
				'type'             => 'text_align',
				'option_category'  => 'layout',
				'options'          => et_builder_get_text_orientation_options(array('justified')),
				'options_icon'     => 'text_align',
				'default'          => 'left',
				'default_on_front' => 'left',
				'toggle_slug'      => 'suc_err_msg',
				'tab_slug'         => 'advanced',
			),
			'cf7_message_color'            => array(
				'label'        => esc_html__('Message Text Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			'cf7_message_bg_color'         => array(
				'label'        => esc_html__('Message Background Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			'cf7_border_highlight_color'   => array(
				'label'        => esc_html__('Border Highlight Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			// Success.
			'cf7_success_message_color'    => array(
				'label'        => esc_html__('Success Message Text Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			'cf7_success_message_bg_color' => array(
				'label'        => esc_html__('Success Message Background Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			'cf7_success_border_color'     => array(
				'label'        => esc_html__('Success Border Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			// Error.
			'cf7_error_message_color'      => array(
				'label'        => esc_html__('Error Message Text Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			'cf7_error_message_bg_color'   => array(
				'label'        => esc_html__('Error Message Background Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			'cf7_error_border_color'       => array(
				'label'        => esc_html__('Error Border Color', 'cf7-styler-for-divi'),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'suc_err_msg',
			),

			'__cf7form'                    => array(
				'type'                => 'computed',
				'computed_callback'   => array('DCS_CF7Styler', 'get_cf7_html'),
				'computed_depends_on' => array(
					'cf7',
				),
			),
		);
	}

	public function get_advanced_fields_config()
	{

		$advanced_fields                = [];
		$advanced_fields['fonts']       = [];
		$advanced_fields['text']        = [];
		$advanced_fields['text_shadow'] = [];

		$advanced_fields['fonts']['form_field_font'] = array(
			'label'       => esc_html__('Field', 'cf7-styler-for-divi'),
			'css'         => array(
				'main'      => implode(
					', ',
					array(
						"{$this->main_css_element} .dipe-cf7 .wpcf7 input:not([type=submit])",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 input::placeholder",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 select",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 textarea",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 textarea::placeholder",
					)
				),
				'important' => array(
					'font',
					'size',
					'letter-spacing',
					'line-height',
					'text-align',
					'all_caps',
				),
			),
			'toggle_slug' => 'form_text',
			'sub_toggle'  => 'field_tab',
		);

		$advanced_fields['fonts']['labels'] = array(
			'label'       => esc_html__('Label', 'cf7-styler-for-divi'),
			'css'         => array(
				'main'      => "{$this->main_css_element} .dipe-cf7 .wpcf7 label",
				'important' => 'all',
			),
			'toggle_slug' => 'form_text',
			'sub_toggle'  => 'label_tab',
		);

		$advanced_fields['fonts']['placeholder'] = array(
			'label'       => esc_html__('Placeholder', 'cf7-styler-for-divi'),
			'css'         => array(
				'main'      => implode(
					', ',
					array(
						"{$this->main_css_element} .dipe-cf7 .wpcf7 input::placeholder",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 textarea::placeholder",
					)
				),
				'important' => 'all',
			),
			'toggle_slug' => 'form_text',
			'sub_toggle'  => 'placeholder_tab',
		);

		$advanced_fields['fonts']['title'] = array(
			'label'            => esc_html__('Title', 'cf7-styler-for-divi'),
			'css'              => array(
				'main'      => '%%order_class%% .dipe-form-header-title',
				'important' => 'all',
			),
			'depends_show_if'  => 'on',
			'hide_text_align'  => true,
			'hide_text_shadow' => true,
			'toggle_slug'      => 'form_header',
			'sub_toggle'       => 'title_tab',
		);

		$advanced_fields['fonts']['text'] = array(
			'label'            => esc_html__('Text', 'cf7-styler-for-divi'),
			'css'              => array(
				'main'      => '%%order_class%% .dipe-form-header-text',
				'important' => 'all',
			),
			'depends_show_if'  => 'on',
			'hide_text_align'  => true,
			'hide_text_shadow' => true,
			'toggle_slug'      => 'form_header',
			'sub_toggle'       => 'text_tab',
		);

		$advanced_fields['button']['submit_button'] = array(
			'label'          => esc_html__('Button', 'cf7-styler-for-divi'),
			'css'            => array(
				'main'      => '%%order_class%% .wpcf7-form input[type=submit]',
				'important' => 'all',
			),
			'box_shadow'     => array(
				'css' => array(
					'main' => '%%order_class%% .wpcf7-form input[type=submit]',
				),
			),
			'margin_padding' => array(
				'css' => array(
					'main'      => '%%order_class%% .wpcf7-form input[type=submit]',
					'important' => 'all',
				),
			),
			'toggle_slug'    => 'submit_button',
			'hide_icon'      => true,
			'use_alignment'  => false,
		);

		$advanced_fields['borders']['default'] = array();

		$advanced_fields['borders']['field'] = array(
			'label_prefix' => esc_html__('Field', 'cf7-styler-for-divi'),
			'toggle_slug'  => 'form_field',
			'css'          => array(
				'main'      => array(
					'border_radii'  => sprintf(
						'
						%1$s .dipe-cf7-styler .wpcf7 input:not([type=submit]),
						%1$s .dipe-cf7-styler .wpcf7 input[type=email],
						%1$s .dipe-cf7-styler .wpcf7 input[type=text],
						%1$s .dipe-cf7-styler .wpcf7 input[type=url],
						%1$s .dipe-cf7-styler .wpcf7 input[type=tel],
						%1$s .dipe-cf7-styler .wpcf7 input[type=date],
						%1$s .dipe-cf7-styler .wpcf7 select,
						%1$s .dipe-cf7-styler .wpcf7 textarea',
						$this->main_css_element
					),

					'border_styles' => sprintf(
						'
						%1$s .dipe-cf7-styler .wpcf7 input:not([type=submit]),
						%1$s .dipe-cf7-styler .wpcf7 input[type=email],
						%1$s .dipe-cf7-styler .wpcf7 input[type=text],
						%1$s .dipe-cf7-styler .wpcf7 input[type=url],
						%1$s .dipe-cf7-styler .wpcf7 input[type=tel],
						%1$s .dipe-cf7-styler .wpcf7 input[type=date],
						%1$s .dipe-cf7-styler .wpcf7 select,
						%1$s .dipe-cf7-styler .wpcf7 textarea
						',
						$this->main_css_element
					),
				),

				'important' => 'all',
			),
		);

		return $advanced_fields;
	}

	public function get_cf7_shortcode($args)
	{
		// Divi stores all module props as strings — coerce to int so we
		// reliably detect "no form selected" (empty string / "0") and pass
		// a clean integer to CF7's shortcode handler.
		$cf7_id = isset($this->props['cf7']) ? absint($this->props['cf7']) : 0;

		if (!$cf7_id) {
			return esc_html__('Please select a Contact Form 7.', 'cf7-styler-for-divi');
		}

		$cf7_shortcode = do_shortcode(sprintf('[contact-form-7 id="%d"]', $cf7_id));

		// Strip pro-only [cf7m-presets] wrapper tags if the pro module is inactive.
		if (strpos($cf7_shortcode, '[cf7m-presets') !== false || strpos($cf7_shortcode, '[/cf7m-presets]') !== false) {
			$cf7_shortcode = preg_replace('/\[cf7m-presets[^\]]*\]|\[\/cf7m-presets\]/i', '', $cf7_shortcode);
		}

		return $cf7_shortcode;
	}

	public static function get_cf7_html($args)
	{

		$cf7_shortcode        = new self();
		$cf7_shortcode->props = $args;
		$output               = $cf7_shortcode->get_cf7_shortcode(array());
		return $output;
	}

	public function render($attrs, $content, $render_slug)
	{

		// Ensure the frontend stylesheet loads even when the module renders
		// from a Divi Theme Builder layout, Divi Library global module, or
		// any other context where the shortcode is not in $post->post_content
		// (the Assets::enqueue_scripts has_shortcode() check misses those).
		if ( ! wp_style_is( 'cf7-styler-for-divi', 'enqueued' ) && defined( 'CF7M_PLUGIN_URL' ) ) {
			wp_enqueue_style(
				'cf7-styler-for-divi',
				CF7M_PLUGIN_URL . 'dist/css/bundle-4.css',
				array(),
				defined( 'CF7M_VERSION' ) ? CF7M_VERSION : false
			);
		}

		$this->apply_css($render_slug);

		$cf7_fields                = $this->props['cf7'];
		$cr_custom_styles          = $this->props['cr_custom_styles'];
		$use_form_header           = $this->props['use_form_header'];
		$form_header_title         = $this->props['form_header_title'];
		$form_header_text          = $this->props['form_header_text'];
		$use_form_button_fullwidth = $this->props['use_form_button_fullwidth'];
		$button_alignment          = $this->props['button_alignment'];

		$form_header = '';

		if ('on' === $use_form_header) {

			$header_img  = '' !== $this->props['header_img'] ? $this->props['header_img'] : false;
			$image       = $header_img ? sprintf('<div class="dipe-form-header-image"><img src="%1$s" alt=""/></div>', $header_img) : '';
			$header_icon = esc_attr(et_pb_process_font_icon($this->props['header_icon']));
			$icon        = sprintf('<div class="dipe-form-header-icon"> <span class="et-pb-icon">%1$s</span> </div> ', $header_icon);
			$icon_image  = 'on' === $this->props['use_icon'] ? $icon : $image;
			$title       = isset($form_header_title) ? sprintf('<h2 class="dipe-form-header-title">%1$s</h2>', $form_header_title) : '';
			$text        = isset($form_header_text) ? sprintf('<div class="dipe-form-header-text">%1$s</div>', $form_header_text) : '';
			$header_info = $title || $text ? sprintf('<div class="dipe-form-header-info">%1$s%2$s</div>', $title, $text) : '';
			cf7m_inject_fa_icons($this->props['header_icon']);

			$form_header = sprintf(
				'<div class="dipe-form-header-container">
                	<div class="dipe-form-header">
                		%1$s%2$s
                	</div>
                </div>',
				$icon_image,
				$header_info
			);
		}

		$cr_custom_class = 'on' === $cr_custom_styles ? 'dipe-cf7-cr' : '';

		return sprintf(
			'<div class="dipe-cf7-container dipe-cf7-button-%4$s">
				%3$s
				<div class="dipe-cf7 dipe-cf7-styler %2$s">
					%1$s
				</div>
			</div>',
			$this->get_cf7_shortcode(array()),
			$cr_custom_class,
			$form_header,
			'on' !== $use_form_button_fullwidth ? $button_alignment : 'fullwidth'
		);
	}

	// Todo: Refactor this.
	public function apply_css($render_slug)
	{

		$this->render_header_css($render_slug);
		$this->render_form_header_padding($render_slug);
		$this->render_form_padding($render_slug);

		$form_background_color        = $this->props['form_background_color'];
		$form_background_color_hover  = $this->get_hover_value('form_background_color');
		$form_field_active_color      = $this->props['form_field_active_color'];
		$cr_custom_styles             = $this->props['cr_custom_styles'];
		$cr_size                      = $this->props['cr_size'];
		$cr_border_size               = $this->props['cr_border_size'];
		$cr_background_color          = $this->props['cr_background_color'];
		$cr_selected_color            = $this->props['cr_selected_color'];
		$cr_border_color              = $this->props['cr_border_color'];
		$cr_label_color               = $this->props['cr_label_color'];
		$cf7_message_color            = $this->props['cf7_message_color'];
		$cf7_message_alignment        = $this->props['cf7_message_alignment'];
		$cf7_message_bg_color         = $this->props['cf7_message_bg_color'];
		$cf7_border_highlight_color   = $this->props['cf7_border_highlight_color'];
		$cf7_success_message_color    = $this->props['cf7_success_message_color'];
		$cf7_success_message_bg_color = $this->props['cf7_success_message_bg_color'];
		$cf7_success_border_color     = $this->props['cf7_success_border_color'];
		$cf7_error_message_color      = $this->props['cf7_error_message_color'];
		$cf7_error_message_bg_color   = $this->props['cf7_error_message_bg_color'];
		$cf7_error_border_color       = $this->props['cf7_error_border_color'];
		$cf7_message_padding          = $this->props['cf7_message_padding'];
		$cf7_message_margin_top       = $this->props['cf7_message_margin_top'];
		$use_form_button_fullwidth    = $this->props['use_form_button_fullwidth'];
		$form_field_height            = $this->props['form_field_height'];

		$this->get_responsive_styles(
			'form_label_spacing',
			'%%order_class%% .dipe-cf7-container .wpcf7-form-control:not(.wpcf7-submit)',
			array(
				'primary'   => 'margin-top',
				'important' => true,
			),
			array('default' => '7px'),
			$render_slug
		);

		$this->get_responsive_styles(
			'form_field_spacing',
			'%%order_class%% .dipe-cf7 .wpcf7 form>p, .dipe-cf7 .wpcf7 form>div, .dipe-cf7 .wpcf7 form>label
            %%order_class%% .dipe-cf7 .wpcf7 form .dp-col>p, .dipe-cf7 .wpcf7 form .dp-col>div, .dipe-cf7 .wpcf7 form .dp-col>label',
			array(
				'primary'   => 'margin-bottom',
				'important' => true,
			),
			array('default' => '20px'),
			$render_slug
		);

		if ('' !== $form_field_height) {
			$this->get_responsive_styles(
				'form_field_height',
				'%%order_class%% .wpcf7-form-control-wrap select, %%order_class%% .wpcf7-form-control-wrap input[type=text], %%order_class%% .wpcf7-form-control-wrap input[type=email], %%order_class%% .wpcf7-form-control-wrap input[type=number], %%order_class%% .wpcf7-form-control-wrap input[type=tel]',
				array(
					'primary'   => 'height',
					'important' => true,
				),
				array('default' => 'initial'),
				$render_slug
			);
		}

		$this->get_responsive_styles(
			'form_field_padding',
			'%%order_class%% .dipe-cf7-container .wpcf7 input:not([type="submit"]):not([type="checkbox"]):not([type="radio"]), %%order_class%% .dipe-cf7-container .wpcf7 select, %%order_class%% .dipe-cf7-container .wpcf7 textarea',
			array(
				'primary'   => 'padding',
				'important' => true,
			),
			array('default' => '10px|15px|10px|15px'),
			$render_slug
		);

		if ('on' === $use_form_button_fullwidth) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7 input[type=submit], %%order_class%% .wpcf7-form button.wpcf7-submit',
					'declaration' => 'width: 100% !important;',
				)
			);
		}

		if ('' !== $form_background_color) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7 input:not([type=submit]), %%order_class%% .dipe-cf7 select, %%order_class%% .dipe-cf7 textarea, %%order_class%% .dipe-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"]:not(:checked) + span:before',

					'declaration' => sprintf(
						'background-color: %1$s%2$s;',
						esc_attr($form_background_color),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				)
			);
		}

		if ('' !== $form_field_active_color) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7 input:not([type=submit]):focus, %%order_class%% .dipe-cf7 .wpcf7 select:focus, %%order_class%% .dipe-cf7 .wpcf7 textarea:focus',
					'declaration' => sprintf(
						'border-color: %1$s%2$s;',
						esc_attr($form_field_active_color),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				)
			);
		}

		if ('on' === $cr_custom_styles) {

			if ('' !== $cr_size || '' !== $cr_border_size) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"] + span:before',
						'declaration' => sprintf(
							'width: %1$s%2$s; height: %1$s%2$s; border-width:%3$s%2$s;',
							esc_attr($cr_size),
							et_is_builder_plugin_active() ? ' !important' : '',
							esc_attr($cr_border_size)
						),
					)
				);
			}

			if ('' !== $cr_size && is_numeric($cr_size)) {
				$font_size = $cr_size / 1.2;
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-acceptance input[type=checkbox]:checked + span:before, %%order_class%% .dipe-cf7 .wpcf7-checkbox input[type=checkbox]:checked + span:before',
						'declaration' => sprintf(
							'font-size: ',
							esc_attr($font_size),
							et_is_builder_plugin_active() ? ' !important' : ''
						),
					)
				);
			}

			if ('' !== $cr_background_color) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"]:not(:checked) + span:before',
						'declaration' => sprintf(
							'background-color: %1$s%2$s;',
							esc_attr($cr_background_color),
							et_is_builder_plugin_active() ? ' !important' : ''
						),
					)
				);
			}

			if ('' !== $cr_background_color) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"]:checked + span:before',
						'declaration' => sprintf(
							'box-shadow:inset 0px 0px 0px 4px %1$s%2$s;',
							esc_attr($cr_background_color),
							et_is_builder_plugin_active() ? ' !important' : ''
						),
					)
				);
			}

			if ('' !== $cr_selected_color) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox input[type="checkbox"]:checked + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"]:checked + span:before',
						'declaration' => sprintf(
							'color: %1$s%2$s;',
							esc_attr($cr_selected_color),
							et_is_builder_plugin_active() ? ' !important' : ''
						),
					)
				);
			}

			if ('' !== $cr_selected_color) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"]:checked + span:before',
						'declaration' => sprintf(
							'background-color: %1$s%2$s;',
							esc_attr($cr_selected_color),
							et_is_builder_plugin_active() ? ' !important' : ''
						),
					)
				);
			}

			if ('' !== $cr_border_color) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox input[type=radio] + span:before, %%order_class%% .dipe-cf7 .wpcf7-radio input[type=checkbox] + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before',
						'declaration' => sprintf(
							'border-color: %1$s%2$s;',
							esc_attr($cr_border_color),
							et_is_builder_plugin_active() ? ' !important' : ''
						),
					)
				);
			}

			if ('' !== $cr_label_color) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox label, %%order_class%% .wpcf7-radio label',
						'declaration' => sprintf(
							'color: %1$s%2$s;',
							esc_attr($cr_label_color),
							et_is_builder_plugin_active() ? ' !important' : ''
						),
					)
				);
			}
		}

		if ('' !== $cf7_message_color) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7 span.wpcf7-not-valid-tip',
					'declaration' => sprintf(
						'color: %1$s%2$s;',
						esc_attr($cf7_message_color),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				)
			);
		}

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .wpcf7 form .wpcf7-response-output, %%order_class%% .wpcf7 form span.wpcf7-not-valid-tip',
				'declaration' => sprintf(
					'text-align: %1$s;',
					$cf7_message_alignment
				),
			)
		);

		if ('' !== $cf7_message_bg_color) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7 span.wpcf7-not-valid-tip',
					'declaration' => sprintf(
						'background-color: %1$s%2$s;',
						esc_attr($cf7_message_bg_color),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				)
			);
		}

		if ('' !== $cf7_border_highlight_color) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7 span.wpcf7-not-valid-tip',
					'declaration' => sprintf(
						'border: 2px solid %1$s%2$s;',
						esc_attr($cf7_border_highlight_color),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				)
			);
		}

		if ('' !== $cf7_success_message_color) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-mail-sent-ok',
					'declaration' => sprintf(
						'color: %1$s%2$s;',
						esc_attr($cf7_success_message_color),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				)
			);
		}

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .wpcf7 form.sent .wpcf7-response-output',
				'declaration' => sprintf(
					'background-color: %1$s !important;',
					esc_attr($cf7_success_message_bg_color)
				),
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .wpcf7 form.sent .wpcf7-response-output',
				'declaration' => sprintf(
					'border-color: %1$s%2$s;',
					esc_attr($cf7_success_border_color),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .wpcf7 form .wpcf7-response-output',
				'declaration' => sprintf(
					'color: %1$s !important;',
					esc_attr($cf7_error_message_color)
				),
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .wpcf7 form .wpcf7-response-output',
				'declaration' => sprintf(
					'background-color: %1$s !important;',
					esc_attr($cf7_error_message_bg_color)
				),
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .wpcf7 form .wpcf7-response-output',
				'declaration' => sprintf(
					'border-color: %1$s !important;',
					esc_attr($cf7_error_border_color)
				),
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% span.wpcf7-not-valid-tip',
				'declaration' => sprintf(
					'padding: %1$s !important;',
					esc_attr($cf7_message_padding)
				),
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% span.wpcf7-not-valid-tip',
				'declaration' => sprintf(
					'margin-top: %1$s !important;',
					esc_attr($cf7_message_margin_top)
				),
			)
		);
	}

	public function render_header_css($render_slug)
	{

		$form_header_bg         = $this->props['form_header_bg'];
		$form_header_bottom     = $this->props['form_header_bottom'];
		$form_header_img_bg     = $this->props['form_header_img_bg'];
		$form_header_icon_color = $this->props['form_header_icon_color'];
		$form_bg                = $this->props['form_bg'];

		if (class_exists('ET_Builder_Module_Helper_Style_Processor') && method_exists('ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon')) {
			$this->generate_styles(
				array(
					'utility_arg'    => 'icon_font_family',
					'render_slug'    => $render_slug,
					'base_attr_name' => 'header_icon',
					'important'      => true,
					'selector'       => '%%order_class%% .dipe-form-header-icon .et-pb-icon',
					'processor'      => array(
						'ET_Builder_Module_Helper_Style_Processor',
						'process_extended_icon',
					),
				)
			);
		}

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .dipe-form-header-container',
				'declaration' => "background-color: {$form_header_bg};",
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .dipe-form-header-container',
				'declaration' => "margin-bottom: {$form_header_bottom};",
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .dipe-form-header-icon, %%order_class%% .dipe-form-header-image',
				'declaration' => "background-color: {$form_header_img_bg};",
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .dipe-form-header-icon span',
				'declaration' => "color: {$form_header_icon_color};",
			)
		);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .dipe-cf7-styler',
				'declaration' => "background-color: {$form_bg};",
			)
		);
	}

	public function render_form_header_padding($render_slug)
	{

		$form_header_padding                   = $this->props['form_header_padding'];
		$form_header_padding_tablet            = $this->props['form_header_padding_tablet'];
		$form_header_padding_phone             = $this->props['form_header_padding_phone'];
		$form_header_padding_last_edited       = $this->props['form_header_padding_last_edited'];
		$form_header_padding_responsive_status = et_pb_get_responsive_status($form_header_padding_last_edited);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .dipe-form-header-container',
				'declaration' => self::process_padding($form_header_padding, false),
			)
		);

		if ($form_header_padding_tablet && $form_header_padding_responsive_status) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-form-header-container',
					'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
					'declaration' => self::process_padding($form_header_padding_tablet, false),
				)
			);
		}

		if ($form_header_padding_phone && $form_header_padding_responsive_status) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-form-header-container',
					'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
					'declaration' => self::process_padding($form_header_padding_phone, false),
				)
			);
		}
	}

	public function render_form_padding($render_slug)
	{

		$form_padding                   = $this->props['form_padding'];
		$form_padding_tablet            = $this->props['form_padding_tablet'];
		$form_padding_phone             = $this->props['form_padding_phone'];
		$form_padding_last_edited       = $this->props['form_padding_last_edited'];
		$form_padding_responsive_status = et_pb_get_responsive_status($form_padding_last_edited);

		ET_Builder_Element::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .dipe-cf7-styler',
				'declaration' => self::process_padding($form_padding, false),
			)
		);

		if ($form_padding_tablet && $form_padding_responsive_status) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7-styler',
					'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
					'declaration' => self::process_padding($form_padding_tablet, false),
				)
			);
		}

		if ($form_padding_phone && $form_padding_responsive_status) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipe-cf7-styler',
					'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
					'declaration' => self::process_padding($form_padding_phone, false),
				)
			);
		}
	}

	public static function process_padding($val = '0|0|0|0', $imp = false)
	{

		$_val = explode('|', $val);

		$padding_top    = '';
		$padding_right  = '';
		$padding_bottom = '';
		$padding_left   = '';
		$imp_text       = '';

		if ($imp) {
			$imp_text = '!important';
		}

		if ('' !== $_val[0]) {
			$padding_top = 'padding-top:' . $_val[0] . $imp_text . ';';
		}

		if ('' !== $_val[1]) {
			$padding_right = 'padding-right:' . $_val[1] . $imp_text . ';';
		}

		if ('' !== $_val[2]) {
			$padding_bottom = 'padding-bottom:' . $_val[2] . $imp_text . ';';
		}

		if ('' !== $_val[3]) {
			$padding_left = 'padding-left:' . $_val[3] . $imp_text . ';';
		}

		return esc_html("{$padding_top} {$padding_right} {$padding_bottom} {$padding_left}");
	}

	public static function process_flex_style($val, $type, $important)
	{
		$flex_val = 'center';
		if ('left' === $val) {
			$flex_val = 'flex-start';
		} elseif ('right' === $val) {
			$flex_val = 'flex-end';
		}
		return sprintf(
			'%1$s:%2$s%3$s;',
			$type,
			$flex_val,
			$important ? '!important;' : ''
		);
	}

	public static function process_margin_padding(
		$val,
		$type,
		$imp
	) {
		$_top     = '';
		$_right   = '';
		$_bottom  = '';
		$_left    = '';
		$imp_text = '';
		$_val     = explode('|', $val);

		if ($imp) {
			$imp_text = '!important';
		}

		if (isset($_val[0]) && !empty($_val[0])) {
			$_top = "{$type}-top:" . $_val[0] . $imp_text . ';';
		}

		if (isset($_val[1]) && !empty($_val[1])) {
			$_right = "{$type}-right:" . $_val[1] . $imp_text . ';';
		}

		if (isset($_val[2]) && !empty($_val[2])) {
			$_bottom = "{$type}-bottom:" . $_val[2] . $imp_text . ';';
		}

		if (isset($_val[3]) && !empty($_val[3])) {
			$_left = "{$type}-left:" . $_val[3] . $imp_text . ';';
		}

		return esc_html("{$_top} {$_right} {$_bottom} {$_left}");
	}

	public function get_conditional_responsive_styles($styles, $data, $style)
	{
		$important = isset($styles['important']) ? $styles['important'] : false;

		if ('padding' === $style || 'margin' === $style) {
			return $this->process_margin_padding($data, $style, $important);
		} elseif ('align-self' === $style || 'align-items' === $style || 'justify-content' === $style) {
			return $this->process_flex_style($data, $style, $important);
		} elseif ('flex' === $style) {
			return 'flex: 0 0 ' . $data . ';';
		} else {
			return sprintf(
				'%1$s:%2$s%3$s;',
				$style,
				$data,
				$important ? '!important;' : ''
			);
		}
	}

	protected function get_responsive_styles(
		$opt_name,
		$selector,
		$styles,
		$pre_values,
		$render_slug
	) {

		$is_enabled = false;
		$style      = isset($styles['primary']) ? $styles['primary'] : '';
		$_data      = $this->props[$opt_name];

		if (isset($this->props["{$opt_name}_last_edited"])) {
			$is_enabled = et_pb_get_responsive_status($this->props["{$opt_name}_last_edited"]);
		}

		if (empty($_data) && !empty($pre_values)) {
			$is_default = true;
			if (!empty($pre_values['conditional'])) {
				foreach ($pre_values['conditional']['values'] as $value) {
					$property_val = $this->props[$pre_values['conditional']['name']];
					if ($property_val === $value['a']) {
						$_data      = $value['b'];
						$is_default = false;
					}
				}
			}

			if ($is_default) {
				$_data = isset($pre_values['default']) ? $pre_values['default'] : '';
			}
		}

		if (!empty($_data)) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => $selector,
					'declaration' => $this->get_conditional_responsive_styles($styles, $_data, $style),
				)
			);

			if (!empty($styles['secondary'])) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => $selector,
						'declaration' => $styles['secondary'],
					)
				);
			}
		}

		if ($is_enabled) {

			$_data_tablet = $this->props["{$opt_name}_tablet"];
			$_data_phone  = $this->props["{$opt_name}_phone"];

			if (!empty($_data_tablet)) {

				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => $selector,
						'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
						'declaration' => $this->get_conditional_responsive_styles($styles, $_data_tablet, $style),
					)
				);

				if (!empty($styles['secondary'])) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => $selector,
							'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
							'declaration' => $styles['secondary'],
						)
					);
				}
			}

			if (!empty($_data_phone)) {

				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => $selector,
						'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
						'declaration' => $this->get_conditional_responsive_styles($styles, $_data_phone, $style),
					)
				);

				if (!empty($styles['secondary'])) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => $selector,
							'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
							'declaration' => $styles['secondary'],
						)
					);
				}
			}
		}
	}

	/** @deprecated Use cf7m_get_contact_forms() instead. */
	public static function get_contact_form7()
	{
		return cf7m_get_contact_forms();
	}
}

new DCS_CF7Styler();
