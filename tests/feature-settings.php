<?php
/** Settings/migration regression tests, runnable without a WordPress installation. */
define('ABSPATH', __DIR__ . '/');
$GLOBALS['cf7m_test_options'] = [];
$GLOBALS['cf7m_test_cleared'] = [];
function get_option($key, $default = false) { return $GLOBALS['cf7m_test_options'][$key] ?? $default; }
function update_option($key, $value, $autoload = null) { $GLOBALS['cf7m_test_options'][$key] = $value; return true; }
function add_option($key, $value, $deprecated = '', $autoload = null) { if (!array_key_exists($key, $GLOBALS['cf7m_test_options'])) { update_option($key, $value); } }
function wp_clear_scheduled_hook($hook) { $GLOBALS['cf7m_test_cleared'][] = $hook; }
function delete_transient($key) { $GLOBALS['cf7m_test_cleared'][] = $key; }
function rest_sanitize_boolean($value) { return is_string($value) ? !in_array(strtolower($value), ['false', '0', ''], true) : (bool) $value; }
function __($text, $domain = '') { return $text; }
require dirname(__DIR__) . '/includes/feature-settings.php';
use CF7_Mate\Feature_Settings as Settings;
$checks = 0;
function check($value, $message) { global $checks; $checks++; if (!$value) { throw new RuntimeException($message); } }
function reset_options($values = []) { $GLOBALS['cf7m_test_options'] = $values; $GLOBALS['cf7m_test_cleared'] = []; }
Settings::migrate();
check(!Settings::enabled('database_entries') && !Settings::enabled('analytics'), 'Fresh installs must opt in to collection');
check(Settings::enabled('redirect') && Settings::enabled('multi_step'), 'Supported rendering features are available');
check(!Settings::enabled('calculator'), 'Unfinished calculator must not run');
check(count(Settings::metadata()) === count(Settings::defaults()), 'Metadata and runtime keys agree');
Settings::save(['database_entries' => true, 'multi_step' => false]);
Settings::save(['redirect' => false, 'analytics' => 'false', 'injected' => true]);
check(Settings::enabled('database_entries') && !Settings::enabled('multi_step'), 'Partial saves preserve unrelated true/false settings');
check(!Settings::enabled('analytics') && !Settings::enabled('injected'), 'Boolean input is normalized and unknown features ignored');
$before = $GLOBALS['cf7m_test_options'];
Settings::migrate();
check($before === $GLOBALS['cf7m_test_options'], 'Migration must be idempotent after user changes');
reset_options(['cf7m_current_version' => '3.0.8', 'cf7m_features' => ['database_entries' => true, 'analytics' => true, 'webhook' => true, 'multi_step' => false], 'cf7m_ai_settings' => ['api_key' => 'fixture'], 'cf7m_views_12' => 8]);
Settings::migrate();
check(!Settings::enabled('database_entries') && !Settings::enabled('webhook'), 'Locked Lite defaults must not start collecting/sending data');
check(!Settings::enabled('multi_step'), 'Lite disabled choices survive');
check(get_option('cf7m_ai_settings')['api_key'] === 'fixture' && get_option('cf7m_views_12') === 8, 'Existing credentials and data survive');
check(get_option('cf7m_pre_free_features')['database_entries'] === true, 'Original settings retained for rollback');
check(in_array('cf7m_daily_license_check', $GLOBALS['cf7m_test_cleared'], true), 'Obsolete license cron is cleared');
reset_options(['cf7m_current_version' => '1.0.2', 'cf7m_features' => ['analytics' => false, 'multi_step' => false], 'cf7m_white_label' => ['enabled' => true]]);
Settings::migrate();
check(Settings::enabled('database_entries') && Settings::enabled('webhook'), 'Pro default-on behavior is preserved');
check(!Settings::enabled('analytics') && !Settings::enabled('multi_step'), 'Explicit Pro false choices survive');
check(get_option('cf7m_white_label')['enabled'], 'Retired branding options remain available for rollback');
reset_options(['cf7m_current_version' => '3.0.8', 'cf7m_edition_origin' => 'pro']);
Settings::migrate();
check(Settings::enabled('database_entries'), 'Captured Pro origin survives a manual handoff');
echo "Passed $checks settings and migration assertions.\n";
