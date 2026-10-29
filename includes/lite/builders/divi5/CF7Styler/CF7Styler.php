<?php

namespace CF7_Mate\Modules\CF7Styler;

if (!defined('ABSPATH')) {
    exit;
}


use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;

class CF7Styler implements DependencyInterface
{
    private static function get_attr_value(array $attrs, array $path, string $breakpoint = 'desktop'): string
    {
        $node = $attrs;
        foreach ($path as $key) {
            if (!is_array($node) || !array_key_exists($key, $node)) {
                return '';
            }
            $node = $node[$key];
        }

        if (!is_array($node) || !isset($node[$breakpoint]['value'])) {
            return '';
        }

        $value = $node[$breakpoint]['value'];
        if (is_bool($value)) {
            return $value ? 'on' : 'off';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    private static function get_raw_attr_value(array $attrs, array $path, string $breakpoint = 'desktop')
    {
        $node = $attrs;
        foreach ($path as $key) {
            if (!is_array($node) || !array_key_exists($key, $node)) {
                return null;
            }
            $node = $node[$key];
        }

        return is_array($node) && isset($node[$breakpoint]) && is_array($node[$breakpoint]) && array_key_exists('value', $node[$breakpoint])
            ? $node[$breakpoint]['value']
            : null;
    }

    /**
     * Default values from module.json. Divi merges them into the attributes it
     * renders, so a value equal to its default must not hide a design preset.
     */
    private static function get_schema_default(string $key)
    {
        static $defaults = null;
        if (null === $defaults) {
            $defaults = [];
            $json     = defined('CF7M_MODULES_JSON_PATH') ? CF7M_MODULES_JSON_PATH . 'cf7-styler/module.json' : '';
            $data     = $json && is_readable($json) ? json_decode((string) file_get_contents($json), true) : null;
            foreach ($data['attributes']['module']['default']['advanced'] ?? [] as $name => $value) {
                if (is_array($value) && array_key_exists('value', $value['desktop'] ?? [])) {
                    $defaults[$name] = $value['desktop']['value'];
                }
            }
        }

        return $defaults[$key] ?? null;
    }

    private static function get_effective_value(array $attrs, $preset, array $path, string $preset_key, string $breakpoint = 'desktop'): string
    {
        $value = self::get_attr_value($attrs, $path, $breakpoint);
        // Tablet and phone rules only print values set for that breakpoint.
        if ('desktop' !== $breakpoint) {
            return $value;
        }
        $has_preset_value = $preset && isset($preset['styles'][$preset_key]) && is_scalar($preset['styles'][$preset_key]);
        if ($value !== '' && $value !== '0' && $value !== '0px' && !($has_preset_value && $value === (string) self::get_schema_default($preset_key))) {
            return $value;
        }
        if ($has_preset_value) {
            return (string) $preset['styles'][$preset_key];
        }
        return $value;
    }

    /**
     * Padding declarations from a pipe string (Divi 4 and presets), a
     * { padding: { top, right, bottom, left } } object, a plain sides object
     * or the single side the 4.1.0 settings field saved.
     */
    private static function spacing_to_css($value, string $suffix = '', string $property = 'padding'): string
    {
        if (is_array($value)) {
            if (isset($value[$property]) && is_array($value[$property])) {
                $value = $value[$property];
            } elseif (isset($value['side'], $value['value']) && is_string($value['side'])) {
                $value = [$value['side'] => $value['value']];
            }
            $sides = [];
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                $sides[] = isset($value[$side]) && is_scalar($value[$side]) ? (string) $value[$side] : '';
            }
        } elseif (is_string($value) && $value !== '') {
            $sides = array_pad(array_slice(explode('|', $value), 0, 4), 4, '');
        } else {
            return '';
        }

        $declarations = '';
        foreach (['top', 'right', 'bottom', 'left'] as $i => $side) {
            $length = self::sanitize_css_length(trim($sides[$i]));
            if ($length !== '') {
                $declarations .= "{$property}-{$side}:{$length}{$suffix};";
            }
        }
        return $declarations;
    }

    private static function get_effective_spacing(array $attrs, $preset, string $key, string $breakpoint, string $suffix = '', string $property = 'padding'): string
    {
        $value = self::get_raw_attr_value($attrs, ['module', 'advanced', $key], $breakpoint);
        if ('desktop' !== $breakpoint) {
            return self::spacing_to_css($value, $suffix, $property);
        }
        $preset_value = $preset['styles'][$key] ?? null;
        $is_default   = self::spacing_to_css($value, '', $property) === self::spacing_to_css(self::get_schema_default($key), '', $property);
        if (null !== $preset_value && (null === $value || '' === $value || $is_default)) {
            return self::spacing_to_css($preset_value, $suffix, $property);
        }
        return self::spacing_to_css($value, $suffix, $property);
    }

    private static function sanitize_css_color(string $value): string
    {
        $value = trim(wp_strip_all_tags($value));
        if ($value === '') {
            return '';
        }
        if (preg_match('/^var\(--[A-Za-z0-9\-_]+\)$/', $value)) {
            return $value;
        }
        if (in_array($value, ['transparent', 'currentColor', 'inherit', 'initial', 'unset'], true)) {
            return $value;
        }
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) {
            return $value;
        }
        if (preg_match('/^(rgb|rgba|hsl|hsla)\([0-9,\s.%]+\)$/', $value)) {
            return $value;
        }
        return '';
    }

    private static function sanitize_css_length(string $value): string
    {
        $value = trim(wp_strip_all_tags($value));
        if ($value === '' || $value === '0' || $value === '0px') {
            return $value === '' ? '' : '0';
        }
        // Support negative values (e.g., -5px for letter-spacing) and unitless values (e.g., 1.5 for line-height)
        if (preg_match('/^-?[0-9.]+(px|em|rem|%|vh|vw|vmin|vmax|ch|ex)?$/', $value)) {
            return $value;
        }
        if (in_array($value, ['initial', 'inherit', 'unset'], true)) {
            return $value;
        }
        return '';
    }

    private static function sanitize_font_weight(string $value): string
    {
        $value = trim(wp_strip_all_tags($value));
        $valid_weights = ['300', '400', '500', '600', '700', '800'];
        if (in_array($value, $valid_weights, true)) {
            return $value;
        }
        return '';
    }

    private static function sanitize_text_transform(string $value): string
    {
        $value = trim(wp_strip_all_tags($value));
        $valid_transforms = ['none', 'uppercase', 'lowercase', 'capitalize'];
        if (in_array($value, $valid_transforms, true)) {
            return $value;
        }
        return '';
    }

    public function load()
    {
        $module_json_folder_path = CF7M_MODULES_JSON_PATH . 'cf7-styler/';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                if (!class_exists('\ET\Builder\Packages\ModuleLibrary\ModuleRegistration')) {
                    return;
                }

                \ET\Builder\Packages\ModuleLibrary\ModuleRegistration::register_module(
                    $module_json_folder_path,
                    array(
                        'render_callback' => array(CF7Styler::class, 'render_callback'),
                    )
                );
            },
            5 // High priority to register early
        );
    }

    private static function build_css(array $attrs, $design_preset, string $scope_id, string $cr_custom_styles, string $use_form_button_fullwide, string $bp): string
    {
        $css = '';
        $form_header_bg = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formHeaderBg'], 'formHeaderBg', $bp));
        $form_header_img_bg     = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formHeaderImgBg'], 'formHeaderImgBg', $bp));
        $form_header_icon_color = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formHeaderIconColor'], 'formHeaderIconColor', $bp));
        $form_header_bottom     = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formHeaderBottom'], 'formHeaderBottom', $bp));
        $form_header_padding    = self::get_effective_spacing($attrs, $design_preset, 'formHeaderPadding', $bp);

        if ($form_header_bg !== '') {
            $css .= "#{$scope_id} .dipe-form-header-container{background-color:{$form_header_bg};}";
        }
        if ($form_header_bottom !== '') {
            $css .= "#{$scope_id} .dipe-form-header-container{margin-bottom:{$form_header_bottom};}";
        }
        if ($form_header_padding !== '') {
            $css .= "#{$scope_id} .dipe-form-header-container{{$form_header_padding}}";
        }
        if ($form_header_img_bg !== '') {
            $css .= "#{$scope_id} .dipe-form-header-icon,#{$scope_id} .dipe-form-header-image{background-color:{$form_header_img_bg};}";
        }
        if ($form_header_icon_color !== '') {
            $css .= "#{$scope_id} .dipe-form-header-icon span{color:{$form_header_icon_color};}";
        }

        $form_bg = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formBg'], 'formBg', $bp));
        $form_padding = self::get_effective_spacing($attrs, $design_preset, 'formPadding', $bp);
        if ($form_bg !== '') {
            $css .= "#{$scope_id} .dipe-cf7-styler{background-color:{$form_bg};}";
        }
        if ($form_padding !== '') {
            $css .= "#{$scope_id} .dipe-cf7-styler{{$form_padding}}";
        }

        $field_bg = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formBackgroundColor'], 'formBackgroundColor', $bp));
        $field_active   = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formFieldActiveColor'], 'formFieldActiveColor', $bp));
        $field_height   = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formFieldHeight'], 'formFieldHeight', $bp));
        $field_padding  = self::get_effective_spacing($attrs, $design_preset, 'formFieldPadding', $bp, ' !important');
        $field_spacing  = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formFieldSpacing'], 'formFieldSpacing', $bp));
        $label_spacing  = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formLabelSpacing'], 'formLabelSpacing', $bp));

        $field_selector = "#{$scope_id} .cf7m-cf7-styler input:not([type=submit]),#{$scope_id} .cf7m-cf7-styler select,#{$scope_id} .cf7m-cf7-styler textarea,#{$scope_id} .dipe-cf7 input:not([type=submit]),#{$scope_id} .dipe-cf7 select,#{$scope_id} .dipe-cf7 textarea";
        if ($field_bg !== '') {
            $css .= "{$field_selector}{background-color:{$field_bg} !important;}";
        }
        if ($field_padding !== '') {
            $css .= "{$field_selector}{{$field_padding}}";
        }
        if ($field_active !== '') {
            $css .= "#{$scope_id} .dipe-cf7 .wpcf7 input:not([type=submit]):focus,#{$scope_id} .dipe-cf7 .wpcf7 select:focus,#{$scope_id} .dipe-cf7 .wpcf7 textarea:focus{border-color:{$field_active} !important;}";
        }
        if ($field_height !== '') {
            $css .= "#{$scope_id} .wpcf7-form-control-wrap select,#{$scope_id} .wpcf7-form-control-wrap input[type=text],#{$scope_id} .wpcf7-form-control-wrap input[type=email],#{$scope_id} .wpcf7-form-control-wrap input[type=number],#{$scope_id} .wpcf7-form-control-wrap input[type=tel]{height:{$field_height} !important;}";
        }
        if ($field_spacing !== '') {
            $css .= "#{$scope_id} .dipe-cf7 .wpcf7 form>p,#{$scope_id} .dipe-cf7 .wpcf7 form>div,#{$scope_id} .dipe-cf7 .wpcf7 form>label,#{$scope_id} .dipe-cf7 .wpcf7 form .dp-col>p,#{$scope_id} .dipe-cf7 .wpcf7 form .dp-col>div,#{$scope_id} .dipe-cf7 .wpcf7 form .dp-col>label{margin-bottom:{$field_spacing} !important;}";
        }
        if ($label_spacing !== '') {
            // Field container (wrap) gets margin-top so label and input block are spaced.
            $css .= "#{$scope_id} .wpcf7-form-control-wrap{margin-top:{$label_spacing} !important;}";
        }

        if ($cr_custom_styles === 'on') {
            $cr_size        = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'crSize'], 'crSize', $bp));
            $cr_border_size = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'crBorderSize'], 'crBorderSize', $bp));
            $cr_bg          = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'crBackgroundColor'], 'crBackgroundColor', $bp));
            $cr_selected    = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'crSelectedColor'], 'crSelectedColor', $bp));
            $cr_border      = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'crBorderColor'], 'crBorderColor', $bp));
            $cr_label       = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'crLabelColor'], 'crLabelColor', $bp));

            if ($cr_size !== '' || $cr_border_size !== '') {
                $w = $cr_size !== '' ? $cr_size : '14px';
                $b = $cr_border_size !== '' ? $cr_border_size : '1px';
                $css .= "#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-checkbox input[type=\"checkbox\"] + span:before,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-acceptance input[type=\"checkbox\"] + span:before,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-radio input[type=\"radio\"] + span:before{width:{$w} !important;height:{$w} !important;border-width:{$b} !important;}";
            }
            if ($cr_bg !== '') {
                $css .= "#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-checkbox input[type=\"checkbox\"] + span:before,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-acceptance input[type=\"checkbox\"] + span:before,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-radio input[type=\"radio\"]:not(:checked) + span:before{background-color:{$cr_bg} !important;}";
                $css .= "#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-radio input[type=\"radio\"]:checked + span:before{box-shadow:inset 0 0 0 4px {$cr_bg} !important;}";
            }
            if ($cr_selected !== '') {
                $css .= "#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-checkbox input[type=\"checkbox\"]:checked + span:before,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-acceptance input[type=\"checkbox\"]:checked + span:before{color:{$cr_selected} !important;}";
                $css .= "#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-radio input[type=\"radio\"]:checked + span:before{background-color:{$cr_selected} !important;}";
            }
            if ($cr_border !== '') {
                $css .= "#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-checkbox input[type=\"checkbox\"] + span:before,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-radio input[type=\"radio\"] + span:before,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-acceptance input[type=\"checkbox\"] + span:before{border-color:{$cr_border} !important;}";
            }
            if ($cr_label !== '') {
                $css .= "#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-checkbox label,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-radio label,#{$scope_id} .dipe-cf7.dipe-cf7-cr .wpcf7-list-item-label{color:{$cr_label} !important;}";
            }
        }

        $msg_padding = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7MessagePadding'], 'cf7MessagePadding', $bp));
        $msg_margin_top    = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7MessageMarginTop'], 'cf7MessageMarginTop', $bp));
        $msg_align         = self::get_attr_value($attrs, ['module', 'advanced', 'cf7MessageAlignment'], 'desktop') ?: 'left';
        $msg_color         = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7MessageColor'], 'cf7MessageColor', $bp));
        $msg_bg            = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7MessageBgColor'], 'cf7MessageBgColor', $bp));
        $msg_border_hl     = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7BorderHighlightColor'], 'cf7BorderHighlightColor', $bp));
        $success_color     = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7SuccessMessageColor'], 'cf7SuccessMessageColor', $bp));
        $success_bg        = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7SuccessMessageBgColor'], 'cf7SuccessMessageBgColor', $bp));
        $success_border    = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7SuccessBorderColor'], 'cf7SuccessBorderColor', $bp));
        $error_color       = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7ErrorMessageColor'], 'cf7ErrorMessageColor', $bp));
        $error_bg          = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7ErrorMessageBgColor'], 'cf7ErrorMessageBgColor', $bp));
        $error_border      = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'cf7ErrorBorderColor'], 'cf7ErrorBorderColor', $bp));

        if ('desktop' === $bp) {
            $css .= "#{$scope_id} .wpcf7 form .wpcf7-response-output,#{$scope_id} .wpcf7 form span.wpcf7-not-valid-tip{text-align:" . esc_attr($msg_align) . ";}";
        }
        if ($msg_color !== '') {
            $css .= "#{$scope_id} .dipe-cf7 span.wpcf7-not-valid-tip{color:{$msg_color} !important;}";
        }
        if ($msg_bg !== '') {
            $css .= "#{$scope_id} .dipe-cf7 span.wpcf7-not-valid-tip{background-color:{$msg_bg} !important;}";
        }
        if ($msg_border_hl !== '') {
            $css .= "#{$scope_id} .dipe-cf7 span.wpcf7-not-valid-tip{border:2px solid {$msg_border_hl} !important;}";
        }
        if ($success_color !== '') {
            $css .= "#{$scope_id} .wpcf7 form.sent .wpcf7-response-output{color:{$success_color} !important;}";
        }
        if ($success_bg !== '') {
            $css .= "#{$scope_id} .wpcf7 form.sent .wpcf7-response-output{background-color:{$success_bg} !important;}";
        }
        if ($success_border !== '') {
            $css .= "#{$scope_id} .wpcf7 form.sent .wpcf7-response-output{border-color:{$success_border} !important;}";
        }
        if ($error_color !== '') {
            $css .= "#{$scope_id} .wpcf7 form .wpcf7-response-output{color:{$error_color} !important;}";
        }
        if ($error_bg !== '') {
            $css .= "#{$scope_id} .wpcf7 form .wpcf7-response-output{background-color:{$error_bg} !important;}";
        }
        if ($error_border !== '') {
            $css .= "#{$scope_id} .wpcf7 form .wpcf7-response-output{border-color:{$error_border} !important;}";
        }
        if ($msg_padding !== '') {
            $css .= "#{$scope_id} span.wpcf7-not-valid-tip{padding:{$msg_padding} !important;}";
        }
        if ($msg_margin_top !== '') {
            $css .= "#{$scope_id} span.wpcf7-not-valid-tip{margin-top:{$msg_margin_top} !important;}";
        }

        $field_border_color = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formFieldBorderColor'], 'formFieldBorderColor', $bp));
        $field_border_width  = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formFieldBorderWidth'], 'formFieldBorderWidth', $bp));
        $field_border_radius = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formFieldBorderRadius'], 'formFieldBorderRadius', $bp));
        $field_text_color    = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formFieldTextColor'], 'formFieldTextColor', $bp));

        if ($field_border_color !== '') {
            $css .= "{$field_selector}{border-color:{$field_border_color} !important;}";
        }
        if ($field_border_width !== '' && $field_border_width !== '0' && $field_border_width !== '0px') {
            $css .= "{$field_selector}{border-width:{$field_border_width} !important;border-style:solid;}";
        }
        if ($field_border_radius !== '' && $field_border_radius !== '0') {
            $css .= "{$field_selector}{border-radius:{$field_border_radius} !important;}";
        }
        if ($field_text_color !== '') {
            $css .= "{$field_selector}{color:{$field_text_color} !important;}";
        }

        $label_color = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'formLabelColor'], 'formLabelColor', $bp));
        if ($label_color !== '') {
            $css .= "#{$scope_id} .dipe-cf7 label,#{$scope_id} .cf7m-cf7-styler label{color:{$label_color} !important;}";
        }

        // Field Font Styles
        $field_font_size = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'fieldFontSize'], $bp));
        $field_font_weight = self::sanitize_font_weight(self::get_attr_value($attrs, ['module', 'advanced', 'fieldFontWeight'], $bp));
        $field_line_height = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'fieldLineHeight'], $bp));
        $field_letter_spacing = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'fieldLetterSpacing'], $bp));
        $field_text_transform = self::sanitize_text_transform(self::get_attr_value($attrs, ['module', 'advanced', 'fieldTextTransform'], $bp));

        if ($field_font_size !== '') {
            $css .= "{$field_selector}{font-size:{$field_font_size} !important;}";
        }
        if ($field_font_weight !== '') {
            $css .= "{$field_selector}{font-weight:{$field_font_weight} !important;}";
        }
        if ($field_line_height !== '') {
            $css .= "{$field_selector}{line-height:{$field_line_height} !important;}";
        }
        if ($field_letter_spacing !== '') {
            $css .= "{$field_selector}{letter-spacing:{$field_letter_spacing} !important;}";
        }
        if ($field_text_transform !== '' && $field_text_transform !== 'none') {
            $css .= "{$field_selector}{text-transform:{$field_text_transform} !important;}";
        }

        // Label Font Styles
        $label_selector = "#{$scope_id} .dipe-cf7 label,#{$scope_id} .cf7m-cf7-styler label";
        $label_font_size = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'labelFontSize'], $bp));
        $label_font_weight = self::sanitize_font_weight(self::get_attr_value($attrs, ['module', 'advanced', 'labelFontWeight'], $bp));
        $label_line_height = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'labelLineHeight'], $bp));
        $label_letter_spacing = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'labelLetterSpacing'], $bp));
        $label_text_transform = self::sanitize_text_transform(self::get_attr_value($attrs, ['module', 'advanced', 'labelTextTransform'], $bp));

        if ($label_font_size !== '') {
            $css .= "{$label_selector}{font-size:{$label_font_size} !important;}";
        }
        if ($label_font_weight !== '') {
            $css .= "{$label_selector}{font-weight:{$label_font_weight} !important;}";
        }
        if ($label_line_height !== '') {
            $css .= "{$label_selector}{line-height:{$label_line_height} !important;}";
        }
        if ($label_letter_spacing !== '') {
            $css .= "{$label_selector}{letter-spacing:{$label_letter_spacing} !important;}";
        }
        if ($label_text_transform !== '' && $label_text_transform !== 'none') {
            $css .= "{$label_selector}{text-transform:{$label_text_transform} !important;}";
        }

        // Placeholder Color
        $placeholder_color = self::sanitize_css_color(self::get_attr_value($attrs, ['module', 'advanced', 'placeholderColor'], $bp));
        if ($placeholder_color !== '') {
            $css .= "#{$scope_id} .dipe-cf7 input::placeholder,#{$scope_id} .dipe-cf7 textarea::placeholder,#{$scope_id} .cf7m-cf7-styler input::placeholder,#{$scope_id} .cf7m-cf7-styler textarea::placeholder{color:{$placeholder_color} !important;}";
        }

        // Header Title Font Styles
        $header_title_selector = "#{$scope_id} .dipe-form-header-title,#{$scope_id} .cf7m-form-header-title";
        $header_title_font_size = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'headerTitleFontSize'], $bp));
        $header_title_font_weight = self::sanitize_font_weight(self::get_attr_value($attrs, ['module', 'advanced', 'headerTitleFontWeight'], $bp));
        $header_title_line_height = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'headerTitleLineHeight'], $bp));
        $header_title_letter_spacing = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'headerTitleLetterSpacing'], $bp));
        $header_title_text_transform = self::sanitize_text_transform(self::get_attr_value($attrs, ['module', 'advanced', 'headerTitleTextTransform'], $bp));
        $header_title_text_color = self::sanitize_css_color(self::get_attr_value($attrs, ['module', 'advanced', 'headerTitleTextColor'], $bp));

        if ($header_title_font_size !== '') {
            $css .= "{$header_title_selector}{font-size:{$header_title_font_size} !important;}";
        }
        if ($header_title_font_weight !== '') {
            $css .= "{$header_title_selector}{font-weight:{$header_title_font_weight} !important;}";
        }
        if ($header_title_line_height !== '') {
            $css .= "{$header_title_selector}{line-height:{$header_title_line_height} !important;}";
        }
        if ($header_title_letter_spacing !== '') {
            $css .= "{$header_title_selector}{letter-spacing:{$header_title_letter_spacing} !important;}";
        }
        if ($header_title_text_transform !== '' && $header_title_text_transform !== 'none') {
            $css .= "{$header_title_selector}{text-transform:{$header_title_text_transform} !important;}";
        }
        if ($header_title_text_color !== '') {
            $css .= "{$header_title_selector}{color:{$header_title_text_color} !important;}";
        }

        // Header Text Font Styles
        $header_text_selector = "#{$scope_id} .dipe-form-header-text,#{$scope_id} .cf7m-form-header-text";
        $header_text_font_size = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'headerTextFontSize'], $bp));
        $header_text_font_weight = self::sanitize_font_weight(self::get_attr_value($attrs, ['module', 'advanced', 'headerTextFontWeight'], $bp));
        $header_text_line_height = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'headerTextLineHeight'], $bp));
        $header_text_letter_spacing = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'headerTextLetterSpacing'], $bp));
        $header_text_text_transform = self::sanitize_text_transform(self::get_attr_value($attrs, ['module', 'advanced', 'headerTextTextTransform'], $bp));
        $header_text_text_color = self::sanitize_css_color(self::get_attr_value($attrs, ['module', 'advanced', 'headerTextTextColor'], $bp));

        if ($header_text_font_size !== '') {
            $css .= "{$header_text_selector}{font-size:{$header_text_font_size} !important;}";
        }
        if ($header_text_font_weight !== '') {
            $css .= "{$header_text_selector}{font-weight:{$header_text_font_weight} !important;}";
        }
        if ($header_text_line_height !== '') {
            $css .= "{$header_text_selector}{line-height:{$header_text_line_height} !important;}";
        }
        if ($header_text_letter_spacing !== '') {
            $css .= "{$header_text_selector}{letter-spacing:{$header_text_letter_spacing} !important;}";
        }
        if ($header_text_text_transform !== '' && $header_text_text_transform !== 'none') {
            $css .= "{$header_text_selector}{text-transform:{$header_text_text_transform} !important;}";
        }
        if ($header_text_text_color !== '') {
            $css .= "{$header_text_selector}{color:{$header_text_text_color} !important;}";
        }

        $button_bg = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'buttonBg'], 'buttonBg', $bp));
        $button_color        = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'buttonColor'], 'buttonColor', $bp));
        $button_padding      = self::get_effective_spacing($attrs, $design_preset, 'buttonPadding', $bp, ' !important');
        $button_border_color = self::sanitize_css_color(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'buttonBorderColor'], 'buttonBorderColor', $bp));
        $button_border_width = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'buttonBorderWidth'], 'buttonBorderWidth', $bp));
        $button_border_radius = self::sanitize_css_length(self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'buttonBorderRadius'], 'buttonBorderRadius', $bp));

        $button_selector = "#{$scope_id} .dipe-cf7 input[type=submit],#{$scope_id} .cf7m-cf7-styler input[type=submit],#{$scope_id} .dipe-cf7 .cf7m-button,#{$scope_id} .cf7m-cf7-styler .cf7m-button";

        if ($button_bg !== '') {
            $css .= "{$button_selector}{background-color:{$button_bg} !important;}";
        }
        if ($button_color !== '') {
            $css .= "{$button_selector}{color:{$button_color} !important;}";
        }
        if ($button_padding !== '') {
            $css .= "{$button_selector}{{$button_padding}}";
        }
        if ($button_border_color !== '') {
            $css .= "{$button_selector}{border-color:{$button_border_color} !important;}";
        }
        if ($button_border_width !== '' && $button_border_width !== '0' && $button_border_width !== '0px') {
            $css .= "{$button_selector}{border-width:{$button_border_width} !important;border-style:solid;}";
        }
        if ($button_border_radius !== '' && $button_border_radius !== '0') {
            $css .= "{$button_selector}{border-radius:{$button_border_radius} !important;}";
        }
        if ('desktop' === $bp && $use_form_button_fullwide === 'on') {
            $css .= "{$button_selector}{width:100% !important;}";
        }

        // Button Font Styles
        $button_font_size = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'buttonFontSize'], $bp));
        $button_font_weight = self::sanitize_font_weight(self::get_attr_value($attrs, ['module', 'advanced', 'buttonFontWeight'], $bp));
        $button_line_height = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'buttonLineHeight'], $bp));
        $button_letter_spacing = self::sanitize_css_length(self::get_attr_value($attrs, ['module', 'advanced', 'buttonLetterSpacing'], $bp));
        $button_text_transform = self::sanitize_text_transform(self::get_attr_value($attrs, ['module', 'advanced', 'buttonTextTransform'], $bp));

        if ($button_font_size !== '') {
            $css .= "{$button_selector}{font-size:{$button_font_size} !important;}";
        }
        if ($button_font_weight !== '') {
            $css .= "{$button_selector}{font-weight:{$button_font_weight} !important;}";
        }
        if ($button_line_height !== '') {
            $css .= "{$button_selector}{line-height:{$button_line_height} !important;}";
        }
        if ($button_letter_spacing !== '') {
            $css .= "{$button_selector}{letter-spacing:{$button_letter_spacing} !important;}";
        }
        if ($button_text_transform !== '' && $button_text_transform !== 'none') {
            $css .= "{$button_selector}{text-transform:{$button_text_transform} !important;}";
        }

        $css .= self::build_element_css($attrs, $design_preset, "#{$scope_id}", $bp);

        return $css;
    }

    /**
     * Heading, icon, image, range slider and star rating fields inside the
     * form. Matches the builder preview in styles.jsx.
     */
    private static function build_element_css(array $attrs, $design_preset, string $scope, string $bp): string
    {
        $value = static function (string $key) use ($attrs, $design_preset, $bp): string {
            return self::get_effective_value($attrs, $design_preset, ['module', 'advanced', $key], $key, $bp);
        };
        // All-zero spacing is the default; printing it would override the elements' own spacing.
        $spacing = static function (string $key, string $property) use ($attrs, $design_preset, $bp): string {
            $css = self::get_effective_spacing($attrs, $design_preset, $key, $bp, ' !important', $property);
            return preg_match('/:(?!0 !important;)/', $css) ? $css : '';
        };

        $image_border_width  = self::sanitize_css_length($value('proImageBorderWidth'));
        $image_border_radius = self::sanitize_css_length($value('proImageBorderRadius'));
        $image_height        = self::sanitize_css_length($value('proImageHeight'));
        $star_size           = self::sanitize_css_length($value('proStarSize'));
        $star_color          = self::sanitize_css_color($value('proStarColor'));
        $star_active         = self::sanitize_css_color($value('proStarActiveColor'));
        $thumb_color         = self::sanitize_css_color($value('proRangeThumbColor'));
        $track_color         = self::sanitize_css_color($value('proRangeTrackColor'));
        $range_height        = self::sanitize_css_length($value('proRangeHeight'));

        $rules = [
            "{$scope} .cf7m-heading" => [
                'font-size'   => self::sanitize_css_length($value('proHeadingFontSize')),
                'font-weight' => self::sanitize_font_weight($value('proHeadingFontWeight')),
                'color'       => self::sanitize_css_color($value('proHeadingTextColor')),
            ],
            "{$scope} .cf7m-icon" => [
                'font-size' => self::sanitize_css_length($value('proIconSize')),
                'color'     => self::sanitize_css_color($value('proIconColor')),
            ],
            "{$scope} .cf7m-image" => [
                'width'         => self::sanitize_css_length($value('proImageWidth')),
                'height'        => $image_height,
                'border-color'  => self::sanitize_css_color($value('proImageBorderColor')),
                'border-width'  => '0' !== $image_border_width ? $image_border_width : '',
                'border-radius' => '0' !== $image_border_radius ? $image_border_radius : '',
            ],
            // As specific as the form field rule, which also matches range inputs.
            "{$scope} .dipe-cf7 input.cf7m-range-input,{$scope} .cf7m-cf7-styler input.cf7m-range-input" => [
                'background' => $track_color,
                'height'     => $range_height,
            ],
            "{$scope} .cf7m-range-input::-webkit-slider-thumb" => ['background' => $thumb_color],
            "{$scope} .cf7m-range-input::-moz-range-thumb"     => ['background' => $thumb_color],
            "{$scope} .cf7m-star-rating .cf7m-star" => [
                'color'     => $star_color,
                'font-size' => $star_size,
            ],
            "{$scope} .cf7m-star-rating .cf7m-star.cf7m-star--on,{$scope} .cf7m-star-rating .cf7m-star.filled,{$scope} .cf7m-star-rating .cf7m-star.hover" => [
                'color' => $star_active,
            ],
            // Stars are masked shapes drawn from these variables.
            "{$scope} .cf7m-star-rating" => [
                '--cf7m-star-off'  => $star_color,
                '--cf7m-star-on'   => $star_active,
                '--cf7m-star-size' => $star_size,
            ],
        ];

        $css = '';
        foreach ($rules as $selector => $declarations) {
            foreach ($declarations as $property => $declaration) {
                if ('' !== $declaration) {
                    $css .= "{$selector}{{$property}:{$declaration} !important;}";
                }
            }
        }

        if ('' !== $image_height) {
            $css .= "{$scope} .cf7m-image{object-fit:cover;}";
        }
        if ('' !== $image_border_width && '0' !== $image_border_width) {
            $css .= "{$scope} .cf7m-image{border-style:solid;}";
        }
        if ('' !== $track_color) {
            $css .= "{$scope} .cf7m-range-input{--cf7m-range-track:{$track_color};}";
        }
        if ('' !== $thumb_color) {
            $css .= "{$scope} .cf7m-range-slider{--cf7m-range-thumb:{$thumb_color};}";
        }
        if ('' !== $range_height) {
            $css .= "{$scope} .dipe-cf7 input.cf7m-range-input,{$scope} .cf7m-cf7-styler input.cf7m-range-input{padding-top:0 !important;padding-bottom:0 !important;}";
        }
        if ('' !== $star_size) {
            $css .= "{$scope} .cf7m-star-rating .cf7m-star-svg{width:1em;height:1em;}";
        }

        $spacing_rules = [
            ["{$scope} .cf7m-heading", 'proHeadingMargin', 'margin'],
            ["{$scope} .cf7m-icon", 'proIconMargin', 'margin'],
            ["{$scope} .cf7m-image", 'proImagePadding', 'padding'],
            ["{$scope} .cf7m-image", 'proImageMargin', 'margin'],
            ["{$scope} .cf7m-range-slider", 'proRangeMargin', 'margin'],
            ["{$scope} .cf7m-star-rating", 'proStarMargin', 'margin'],
        ];
        foreach ($spacing_rules as $spacing_rule) {
            $declarations = $spacing($spacing_rule[1], $spacing_rule[2]);
            if ('' !== $declarations) {
                $css .= "{$spacing_rule[0]}{{$declarations}}";
            }
        }

        return $css;
    }

    public static function render_callback($attrs, $content = '', $block = null, $elements = null)
    {
        // Fallback: ensure styles are enqueued even if wp_enqueue_scripts ran too early.
        if (!wp_style_is('cf7-styler-for-divi-d5-frontend-style', 'enqueued')) {
            wp_enqueue_style(
                'cf7-styler-for-divi-d5-frontend-style',
                CF7M_PLUGIN_URL . 'dist/css/bundle.css',
                [],
                CF7M_VERSION
            );
        }
        if (function_exists('wpcf7_enqueue_styles')) {
            wpcf7_enqueue_styles();
        }

        $attrs = is_array($attrs) ? $attrs : [];

        // Backward compat: merge old cf7.advanced.* into module.advanced.* for
        // modules converted before the attribute restructuring.
        if (isset($attrs['cf7']['advanced']) && is_array($attrs['cf7']['advanced'])) {
            if (!isset($attrs['module']['advanced']) || !is_array($attrs['module']['advanced'])) {
                $attrs['module']['advanced'] = [];
            }
            $attrs['module']['advanced'] += $attrs['cf7']['advanced'];
        }

        $scope_id = function_exists('wp_unique_id') ? wp_unique_id('cf7m-cf7-styler-') : ('cf7m-cf7-styler-' . uniqid());

        $design_preset_slug = self::get_attr_value($attrs, ['module', 'advanced', 'designPreset'], 'desktop');
        $design_preset = function_exists('cf7m_get_design_preset_by_slug') && $design_preset_slug !== ''
            ? cf7m_get_design_preset_by_slug($design_preset_slug)
            : null;

        // A post ID or CF7's hash ID; nothing else may reach the shortcode.
        $form_id = preg_replace('/[^A-Za-z0-9]/', '', self::get_attr_value($attrs, ['module', 'advanced', 'formId'], 'desktop'));
        if ($form_id === '') {
            $form_id = '0';
        }

        $use_form_header = self::get_attr_value($attrs, ['module', 'advanced', 'useFormHeader'], 'desktop') === 'on';
        $header_title = self::get_attr_value($attrs, ['module', 'advanced', 'formHeaderTitle'], 'desktop');
        $header_text = self::get_attr_value($attrs, ['module', 'advanced', 'formHeaderText'], 'desktop');
        $use_icon = self::get_attr_value($attrs, ['module', 'advanced', 'useIcon'], 'desktop') === 'on';
        $header_image = self::get_attr_value($attrs, ['module', 'advanced', 'headerImage'], 'desktop');
        $header_icon = self::get_attr_value($attrs, ['module', 'advanced', 'headerIcon'], 'desktop');
        // The Divi 5 icon picker saves { unicode, type, weight }; Divi 4 saved "unicode||type||weight".
        $header_icon_raw = self::get_raw_attr_value($attrs, ['module', 'advanced', 'headerIcon'], 'desktop');
        if (is_array($header_icon_raw) && isset($header_icon_raw['unicode']) && is_string($header_icon_raw['unicode'])) {
            $header_icon = implode('||', [$header_icon_raw['unicode'], (string) ($header_icon_raw['type'] ?? 'divi'), (string) ($header_icon_raw['weight'] ?? '400')]);
        }
        $button_alignment = self::get_attr_value($attrs, ['module', 'advanced', 'buttonAlignment'], 'desktop') ?: 'left';
        $use_form_button_fullwide = self::get_attr_value($attrs, ['module', 'advanced', 'useFormButtonFullwidth'], 'desktop') ?: 'off';
        $cr_custom_styles = self::get_effective_value($attrs, $design_preset, ['module', 'advanced', 'crCustomStyles'], 'crCustomStyles') ?: 'off';

        $button_class = 'on' !== $use_form_button_fullwide ? $button_alignment : 'fullwidth';
        $cr_custom_class = 'on' === $cr_custom_styles ? 'dipe-cf7-cr cf7m-cf7-cr' : '';

        $form_header = '';
        $has_header_media = ($use_icon && $header_icon !== '') || (!$use_icon && $header_image !== '');
        if ($use_form_header && ($header_title !== '' || $header_text !== '' || $has_header_media)) {
            $media_html = '';

            if ($use_icon && $header_icon !== '') {
                $icon_processed = function_exists('et_pb_process_font_icon') ? et_pb_process_font_icon($header_icon) : $header_icon;
                $icon_processed = esc_html($icon_processed);

                if (function_exists('cf7m_inject_fa_icons')) {
                    cf7m_inject_fa_icons($header_icon);
                }

                $media_html = sprintf(
                    '<div class="dipe-form-header-icon cf7m-form-header-icon"><span class="et-pb-icon">%1$s</span></div>',
                    $icon_processed
                );
            } elseif (!$use_icon && $header_image !== '') {
                $media_html = sprintf(
                    '<div class="dipe-form-header-image cf7m-form-header-image"><img src="%1$s" alt="" /></div>',
                    esc_url($header_image)
                );
            }

            $title_html = !empty($header_title) ? sprintf(
                '<h2 class="dipe-form-header-title cf7m-form-header-title">%1$s</h2>',
                esc_html($header_title)
            ) : '';
            $text_html = !empty($header_text) ? sprintf(
                '<div class="dipe-form-header-text cf7m-form-header-text">%1$s</div>',
                esc_html($header_text)
            ) : '';
            $info_html = ($title_html || $text_html) ? sprintf(
                '<div class="dipe-form-header-info cf7m-form-header-info">%1$s%2$s</div>',
                $title_html,
                $text_html
            ) : '';

            $form_header = sprintf(
                '<div class="dipe-form-header-container cf7m-form-header-container">
                    <div class="dipe-form-header cf7m-form-header">
                        %1$s%2$s
                    </div>
                </div>',
                $media_html,
                $info_html
            );
        }

        if ($form_id === '0' || empty($form_id)) {
            $form_html = '<p class="cf7m-cf7-styler__placeholder">' . esc_html__('Please select a Contact Form 7 form.', 'cf7-styler-for-divi') . '</p>';
        } else {
            $form_html = do_shortcode(sprintf('[contact-form-7 id="%1$s"]', $form_id));
        }

        $css = self::build_css($attrs, $design_preset, $scope_id, $cr_custom_styles, $use_form_button_fullwide, 'desktop');
        foreach (['tablet' => 980, 'phone' => 767] as $breakpoint => $max_width) {
            $breakpoint_css = self::build_css($attrs, $design_preset, $scope_id, $cr_custom_styles, $use_form_button_fullwide, $breakpoint);
            if ($breakpoint_css !== '') {
                $css .= "@media only screen and (max-width:{$max_width}px){{$breakpoint_css}}";
            }
        }

        $container_classes = sprintf(
            'dipe-cf7-container dipe-cf7-button-%1$s cf7m-cf7-container cf7m-cf7-button-%1$s',
            esc_attr($button_class)
        );

        $wrapper_classes = sprintf(
            'dipe-cf7 dipe-cf7-styler cf7m-cf7 cf7m-cf7-styler %s',
            esc_attr($cr_custom_class)
        );

        $output  = '<div id="' . esc_attr($scope_id) . '" class="' . $container_classes . '">';
        if ($css !== '') {
            $output .= '<style>' . $css . '</style>';
        }
        $output .= $form_header;
        $output .= '<div class="' . $wrapper_classes . '">';
        $output .= $form_html;
        $output .= '</div>';
        $output .= '</div>';

        // Divi's module wrapper carries the order class, the Design tab options
        // (background, spacing, border, sizing, visibility) and Custom CSS.
        if (!($block instanceof \WP_Block) || !($elements instanceof ModuleElements)) {
            return $output;
        }

        $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);

        return Module::render(
            [
                'orderIndex'         => $block->parsed_block['orderIndex'],
                'storeInstance'      => $block->parsed_block['storeInstance'],
                'attrs'              => $attrs,
                'elements'           => $elements,
                'id'                 => $block->parsed_block['id'],
                'name'               => $block->block_type->name,
                'moduleCategory'     => $block->block_type->category,
                'classnamesFunction' => [self::class, 'module_classnames'],
                'stylesComponent'    => [self::class, 'module_styles'],
                'parentAttrs'        => $parent->attrs ?? [],
                'parentId'           => $parent->id ?? '',
                'parentName'         => $parent->blockName ?? '',
                'children'           => $output,
            ]
        );
    }

    public static function module_classnames(array $args): void
    {
        $attrs = $args['attrs'] ?? [];
        $args['classnamesInstance']->add(
            ElementClassnames::classnames(
                [
                    'attrs' => array_merge(
                        $attrs['module']['decoration'] ?? [],
                        ['link' => $attrs['module']['advanced']['link'] ?? []]
                    ),
                ]
            )
        );
    }

    public static function module_styles(array $args): void
    {
        $attrs = $args['attrs'] ?? [];
        $block = \WP_Block_Type_Registry::get_instance()->get_registered('cf7-mate/cf7-styler');

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => [
                    $args['elements']->style(
                        [
                            'attrName'   => 'module',
                            'styleProps' => [
                                'disabledOn' => [
                                    'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null,
                                ],
                            ],
                        ]
                    ),
                    CssStyle::style(
                        [
                            'selector'  => $args['orderClass'],
                            'attr'      => $attrs['css'] ?? [],
                            'cssFields' => $block->customCssFields ?? [],
                        ]
                    ),
                ],
            ]
        );
    }
}
