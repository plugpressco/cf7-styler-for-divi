<?php
/** Optional Saddle recommendations. No installation, activation or connection side effects. */
namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Saddle_Setup
{
    public static function config()
    {
        $active = defined('SADDLE_VERSION');
        return [
            'active' => $active,
            'analytics_active' => defined('SADDLE_ANALYTICS_VERSION'),
            'abilities_available' => function_exists('wp_register_ability'),
            'setup_url' => $active ? admin_url('admin.php?page=saddle-connections') : 'https://wordpress.org/plugins/saddle/',
            'analytics_url' => defined('SADDLE_ANALYTICS_VERSION') ? admin_url('admin.php?page=saddle-analytics') : 'https://wordpress.org/plugins/saddle-analytics/',
            'settings_url' => $active ? admin_url('admin.php?page=saddle-settings') : 'https://saddle.to/docs/',
            'docs_url' => 'https://saddle.to/docs/',
            'support_url' => 'https://wordpress.org/support/plugin/saddle/',
            'page_url' => admin_url('admin.php?page=cf7-mate-saddle'),
        ];
    }
}
