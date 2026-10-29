<?php

/**
 * Divi 5 builder: register CF7 Styler module with the dependency tree.
 *
 * @package CF7_Mate\Lite\Builders\Divi5
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once CF7M_PLUGIN_PATH . 'includes/pro/design-presets.php';

// Divi 5 registers its class autoloader on init (priority 0), from wherever the
// theme, Extra or the Divi Builder plugin lives, and then fires this action.
add_action(
    'divi_module_library_modules_dependency_tree',
    function ($dependency_tree) {
        require_once __DIR__ . '/CF7Styler/CF7Styler.php';
        $dependency_tree->add_dependency(new \CF7_Mate\Modules\CF7Styler\CF7Styler());
    },
    5
);

add_filter('divi.moduleLibrary.conversion.moduleConversionOutlineFile', function ($file_path, $module_name) {
    if ('cf7-mate/cf7-styler' === $module_name && defined('CF7M_MODULES_JSON_PATH')) {
        return CF7M_MODULES_JSON_PATH . 'cf7-styler/conversion-outline.json';
    }
    return $file_path;
}, 9, 2);

/**
 * Normalize broken D5 attr structures for CF7 Styler modules.
 *
 * When a page was converted from D4→D5 using the old (broken) conversion
 * outline that lacked .* breakpoint wildcards, module.advanced attributes
 * were stored as plain scalars (e.g. formId = "123") instead of the
 * required breakpoint structure (formId = { desktop: { value: "123" } }).
 *
 * Divi's Immutable.js store calls setIn() on these values when the user
 * interacts with the settings panel, causing "d.setIn is not a function".
 *
 * This filter runs BEFORE the VB client receives the content, so it
 * ensures all attrs have the correct structure.
 */
add_filter('divi_visual_builder_settings_data_post_content', function ($content, $post_id) {
    if (!is_string($content) || strpos($content, 'cf7-mate/cf7-styler') === false) {
        return $content;
    }

    if (!function_exists('parse_blocks') || !function_exists('serialize_blocks')) {
        return $content;
    }

    $blocks  = parse_blocks($content);
    $changed = false;

    $blocks = cf7m_normalize_blocks_recursive($blocks, $changed);

    return $changed ? serialize_blocks($blocks) : $content;
}, 10, 2);

/**
 * Normalize attrs at render time (frontend + VB server-side preview).
 *
 * This complements the VB content filter above by also fixing attrs when the
 * module is rendered server-side via render_callback (e.g. frontend page load).
 */
add_filter('divi_module_library_register_module_attrs', function ($module_attrs, $filter_args) {
    if (($filter_args['name'] ?? '') !== 'cf7-mate/cf7-styler') {
        return $module_attrs;
    }

    $adv = $module_attrs['module']['advanced'] ?? null;
    if (!is_array($adv)) {
        return $module_attrs;
    }

    $fixed = false;
    foreach ($adv as $key => $value) {
        if (is_array($value)) {
            continue;
        }
        $adv[$key] = ['desktop' => ['value' => $value]];
        $fixed     = true;
    }

    if ($fixed) {
        $module_attrs['module']['advanced'] = $adv;
    }

    return $module_attrs;
}, 10, 2);

/**
 * Walk blocks recursively and normalize CF7 Styler attrs.
 *
 * @param array $blocks  Parsed blocks array.
 * @param bool  $changed Reference flag set to true when any block is modified.
 * @return array Modified blocks.
 */
function cf7m_normalize_blocks_recursive(array $blocks, bool &$changed): array {
    foreach ($blocks as $i => $block) {
        if (!empty($block['innerBlocks'])) {
            $blocks[$i]['innerBlocks'] = cf7m_normalize_blocks_recursive($block['innerBlocks'], $changed);
        }

        if (($block['blockName'] ?? '') !== 'cf7-mate/cf7-styler') {
            continue;
        }

        $adv = $block['attrs']['module']['advanced'] ?? null;
        if (!is_array($adv)) {
            continue;
        }

        $fixed = false;
        foreach ($adv as $key => $value) {
            // Already has the correct breakpoint structure.
            if (is_array($value)) {
                continue;
            }
            // Scalar value → wrap in { desktop: { value: ... } }.
            $adv[$key] = ['desktop' => ['value' => $value]];
            $fixed     = true;
        }

        if ($fixed) {
            $blocks[$i]['attrs']['module']['advanced'] = $adv;
            $changed = true;
        }
    }

    return $blocks;
}
