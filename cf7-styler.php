<?php
/*
Plugin Name: CF7 Mate
Plugin URI: https://plugpress.io/cf7-mate/
Description: Build Contact Form 7 forms with AI, style them visually with no code, add multi-step flows, and save every submission.
Version: 4.1.1
Author: PlugPress
Author URI:  https://plugpress.io
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cf7-styler-for-divi
Domain Path: /languages
Requires at least: 6.0
Requires PHP: 7.4
Requires Plugins: contact-form-7

*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Keep one runtime when the legacy full Pro plugin is active, including multisite.
$cf7m_legacy_pro = 'cf7-mate-pro/cf7-mate-pro.php';
$cf7m_active = (array) get_option('active_plugins', []);
$cf7m_network_active = is_multisite() ? (array) get_site_option('active_sitewide_plugins', []) : [];
if (in_array($cf7m_legacy_pro, $cf7m_active, true) || isset($cf7m_network_active[$cf7m_legacy_pro])) {
    update_option('cf7m_edition_origin', 'pro', false);
    add_action('admin_notices', static function () {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        echo '<div class="notice notice-warning"><p>';
        echo esc_html__('CF7 Mate now includes all features for free. Deactivate the legacy CF7 Mate Pro plugin, then activate CF7 Mate to complete the switch. Your forms and saved responses are preserved.', 'cf7-styler-for-divi');
        echo '</p></div>';
    });
    return;
}
unset($cf7m_legacy_pro, $cf7m_active, $cf7m_network_active);

if (!defined('CF7M_VERSION')) {
    define('CF7M_VERSION', '4.1.1');
}

define('CF7M_BASENAME', plugin_basename(__FILE__));
define('CF7M_BASENAME_DIR', plugin_basename(__DIR__));
define('CF7M_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CF7M_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CF7M_MODULES_JSON_PATH', CF7M_PLUGIN_PATH . 'modules-json/');
define('CF7M_URL_MAIN', 'https://plugpress.io/cf7-mate/');
define('CF7M_URL_DOCS', 'https://plugpress.io/docs-category/cf7-mate/');
define('CF7M_URL_SUPPORT', 'https://plugpress.io/support/');
define('CF7M_URL_COMMUNITY', 'https://facebook.com/groups/plugpress');

require_once CF7M_PLUGIN_PATH . 'includes/feature-settings.php';
\CF7_Mate\Feature_Settings::migrate();
require_once CF7M_PLUGIN_PATH . 'includes/plugin.php';
