<?php

namespace CF7_Mate\License;

if (!defined('ABSPATH')) {
    exit;
}

class Updater {
    private $plugin_file;
    private $plugin_data;
    private $license_manager;

    const UPDATE_SERVER = 'https://updates.cf7mate.com/info.json';
    const TRANSIENT_UPDATE_INFO = 'cf7m_update_info';
    const TRANSIENT_TTL = 12 * \HOUR_IN_SECONDS;

    public function __construct(string $plugin_file, array $plugin_data, License_Manager $license_manager) {
        $this->plugin_file      = $plugin_file;
        $this->plugin_data      = $plugin_data;
        $this->license_manager  = $license_manager;

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_action('upgrader_process_complete', [$this, 'purge_cache'], 10, 2);
    }

    /**
     * Check for plugin updates.
     *
     * @param object $transient Update transient.
     * @return object Modified transient.
     */
    public function check_for_update($transient) {
        if (!is_object($transient)) {
            return $transient;
        }

        // Only provide updates for valid licenses
        if (!$this->license_manager->is_valid()) {
            return $transient;
        }

        $update_info = $this->get_update_info();
        if (!$update_info) {
            return $transient;
        }

        $current_version = isset($this->plugin_data['version']) ? $this->plugin_data['version'] : '0';
        $new_version     = isset($update_info->version) ? $update_info->version : '0';

        // Only add to response if there's a newer version
        if (version_compare($new_version, $current_version, '>')) {
            $update_obj = (object) [
                'slug'        => 'cf7-mate-pro',
                'plugin'      => $this->plugin_file,
                'new_version' => $new_version,
                'tested'      => isset($update_info->tested) ? $update_info->tested : '',
                'package'     => isset($update_info->download_url) ? $update_info->download_url : '',
                'requires'    => isset($update_info->requires) ? $update_info->requires : '6.0',
                'requires_php' => isset($update_info->requires_php) ? $update_info->requires_php : '7.4',
            ];

            if (!isset($transient->response)) {
                $transient->response = [];
            }

            $transient->response[$this->plugin_file] = $update_obj;
        }

        return $transient;
    }

    /**
     * Get plugin info for the "View details" modal.
     *
     * @param false|object|array $result  The result object/array from the API call.
     * @param string             $action  The type of information being requested.
     * @param object             $args    Plugin API arguments.
     * @return false|object|array Modified result.
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (isset($args->slug) && $args->slug !== 'cf7-mate-pro') {
            return $result;
        }

        $update_info = $this->get_update_info();
        if (!$update_info) {
            return $result;
        }

        $info = (object) [
            'name'         => isset($this->plugin_data['name']) ? $this->plugin_data['name'] : 'CF7 Mate Pro',
            'slug'         => 'cf7-mate-pro',
            'version'      => isset($update_info->version) ? $update_info->version : '',
            'author'       => 'PlugPress',
            'author_url'   => 'https://plugpress.io',
            'requires'     => isset($update_info->requires) ? $update_info->requires : '6.0',
            'requires_php' => isset($update_info->requires_php) ? $update_info->requires_php : '7.4',
            'download_link' => isset($update_info->download_url) ? $update_info->download_url : '',
            'sections'     => [
                'description' => isset($update_info->description) ? wp_kses_post($update_info->description) : __('Premium features for CF7 Mate.', 'cf7-styler-for-divi'),
                'changelog'   => isset($update_info->changelog) ? wp_kses_post($update_info->changelog) : '',
            ],
        ];

        return $info;
    }

    /**
     * Purge update cache after upgrade.
     *
     * @param object $upgrader WP_Upgrader instance.
     * @param array  $options  Upgrade arguments.
     */
    public function purge_cache($upgrader, $options) {
        if (isset($options['action']) && $options['action'] === 'update' && isset($options['type']) && $options['type'] === 'plugin') {
            delete_transient(self::TRANSIENT_UPDATE_INFO);
        }
    }

    /**
     * Get cached or fresh update info.
     *
     * @return false|object Update info or false if unavailable.
     */
    private function get_update_info() {
        $cached = get_transient(self::TRANSIENT_UPDATE_INFO);
        if ($cached !== false) {
            return $cached;
        }

        $info = $this->fetch_update_info();
        if ($info) {
            set_transient(self::TRANSIENT_UPDATE_INFO, $info, self::TRANSIENT_TTL);
        }

        return $info;
    }

    /**
     * Fetch update info from the update server.
     *
     * @return false|object Update info or false on failure.
     */
    private function fetch_update_info() {
        $url = add_query_arg([
            'slug'        => 'cf7-mate-pro',
            'site_url'    => home_url(),
            'license_key' => $this->get_masked_key(),
        ], self::UPDATE_SERVER);

        $response = wp_remote_get($url, [
            'timeout'     => 15,
            'httpversion' => '1.1',
            'headers'     => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 400) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        return is_object($data) ? $data : false;
    }

    /**
     * Get masked license key for update server validation.
     *
     * @return string Masked key or empty string.
     */
    private function get_masked_key(): string {
        return $this->license_manager->get_masked_key();
    }
}
