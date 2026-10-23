<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Preserve the original public Divi integration class name.
class DCS_Builder_Module extends ET_Builder_Module
{
    public $module_credits = array(
        'module_uri' => 'https://plugpress.io/cf7-mate/',
        'author'     => 'PlugPress',
        'author_uri' => 'https://plugpress.io',
    );

    public $icon_path;

    public function _getResponsiveValues($optionName, $presetValues = null)
    {
        $presetValues = $presetValues ?? [];
        $responsiveEnabled = false;
        $mainData = $this->props[$optionName];
        $responsiveStatus = $this->props["{$optionName}_last_edited"] ?? null;

        if ($responsiveStatus) {
            $responsiveEnabled = et_pb_get_responsive_status($responsiveStatus);
        }

        // Handle preset conditional values
        if (empty($mainData) && isset($presetValues['conditional'])) {
            foreach ($presetValues['conditional']['values'] as $value) {
                $propValue = $this->props[$presetValues['conditional']['name']];
                if ($propValue === $value['a']) {
                    $mainData = $value['b'];
                    break;
                }
            }
        }

        // Handle preset default values
        $mainData = $mainData ?: ($presetValues['default'] ?? '');

        // If responsive is enabled, return an array of values for all devices.
        if ($responsiveEnabled) {
            return [
                'desktop' => $mainData,
                'tablet'  => $this->props["{$optionName}_tablet"] ?? '',
                'phone'   => $this->props["{$optionName}_phone"] ?? '',
            ];
        }

        // If not, return just the desktop value.
        return $mainData;
    }
}
