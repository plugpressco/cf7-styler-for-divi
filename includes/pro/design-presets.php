<?php

/**
 * Design presets for CF7 Styler for Divi.
 * Loads preset definitions from JSON; presets define font, border, button, etc.
 *
 * @package CF7_Mate
 * @since 3.0.0
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Get all design presets (from JSON, filterable).
 *
 * @return array<int, array{slug: string, name: string, description: string, styles: array<string, string>}>
 */
function cf7m_get_design_presets()
{
	$path = defined('CF7M_PLUGIN_PATH') ? CF7M_PLUGIN_PATH . 'src/divi5/modules/cf7-styler/design-presets.json' : '';
	if (! $path || ! is_readable($path)) {
		return array();
	}
	$json = file_get_contents($path);
	if ($json === false) {
		return array();
	}
	$data = json_decode($json, true);
	if (! is_array($data) || empty($data['presets'])) {
		return array();
	}
	$presets = array_values($data['presets']);
	return apply_filters('cf7m_design_presets', $presets);
}

/**
 * Get a single design preset by slug.
 *
 * @param string $slug Preset slug (e.g. 'classic', 'typeform').
 * @return array{slug: string, name: string, description: string, styles: array<string, string>}|null
 */
function cf7m_get_design_preset_by_slug($slug)
{
	if (empty($slug)) {
		return null;
	}
	$presets = cf7m_get_design_presets();
	foreach ($presets as $preset) {
		if (isset($preset['slug']) && $preset['slug'] === $slug) {
			return $preset;
		}
	}
	return null;
}
