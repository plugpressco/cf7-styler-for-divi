<?php
/*
Plugin Name: Styler Mate for Contact Form 7
Plugin URI: https://cf7mate.com
Description: CF7 Mate is a plugin for Contact Form 7 that allows you to style your forms.
Version: 3.0.5
Author: PlugPress
Author URI:  https://plugpress.co
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

// Auto-deactivate the lite build when CF7 Mate Pro is also active.
// Pro loads first (alphabetical), so we detect it via its main plugin file.
if (in_array('cf7-mate-pro/cf7-mate-pro.php', (array) get_option('active_plugins', []), true)) {
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    deactivate_plugins(plugin_basename(__FILE__));
    add_action('admin_notices', function () {
        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo esc_html__('Styler Mate for Contact Form 7 (Lite) has been deactivated because CF7 Mate Pro is active.', 'cf7-styler-for-divi');
        echo '</p></div>';
    });
    return; // Stop loading the lite plugin in this request.
}

if (!defined('CF7M_VERSION')) {
    define('CF7M_VERSION', '3.0.5');
}
if (!defined('CF7M_IS_PRO_VERSION')) {
    define('CF7M_IS_PRO_VERSION', false);
}

define('CF7M_BASENAME', plugin_basename(__FILE__));
define('CF7M_BASENAME_DIR', plugin_basename(__DIR__));
define('CF7M_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CF7M_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CF7M_MODULES_JSON_PATH', CF7M_PLUGIN_PATH . 'modules-json/');
define('CF7M_URL_MAIN', 'https://cf7mate.com');
define('CF7M_URL_DOCS', 'https://cf7mate.com/docs');
define('CF7M_URL_SUPPORT', 'https://cf7mate.com/support');
define('CF7M_URL_COMMUNITY', 'https://facebook.com/groups/plugpress');
define('CF7M_URL_PRICING', 'https://cf7mate.com/pricing');

require_once CF7M_PLUGIN_PATH . 'includes/plugin.php';
