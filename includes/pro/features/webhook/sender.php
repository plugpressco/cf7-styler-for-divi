<?php
/**
 * Webhook Sender – POST form submission data to configured URLs on CF7 submit.
 *
 * @package CF7_Mate\Features\Webhook
 * @since 3.0.0
 */

namespace CF7_Mate\Features\Webhook;

use CF7_Mate\Premium_Loader;

if (!defined('ABSPATH')) {
    exit;
}

class Webhook_Sender
{
    public function __construct()
    {
        add_action('wpcf7_before_send_mail', [$this, 'send_to_webhooks'], 10, 2);
    }

    /**
     * Send submission payload to all configured webhook URLs.
     *
     * @param \WPCF7_ContactForm $contact_form
     * @param bool              $abort
     */
    public function send_to_webhooks($contact_form, &$abort)
    {
        if (!Premium_Loader::is_feature_enabled('webhook')) {
            return;
        }

        $urls = $this->get_webhook_urls();
        if (empty($urls)) {
            return;
        }

        $submission = \WPCF7_Submission::get_instance();
        if (!$submission) {
            return;
        }

        $data = $submission->get_posted_data();
        $clean = [];
        foreach ($data as $key => $value) {
            if (strpos($key, '_wpcf7') === 0 || strpos($key, '_cf7m_') === 0) {
                continue;
            }
            $clean[$key] = $value;
        }

        $payload = [
            'form_id'       => $contact_form->id(),
            'form_title'    => $contact_form->title(),
            'submitted_at'  => current_time('c'),
            'posted_data'   => $clean,
            'ip'            => $this->get_client_ip(),
            'user_agent'    => $this->get_user_agent(),
        ];

        $body = wp_json_encode($payload);
        $args = [
            'timeout'  => 15,
            'blocking' => false,
            'headers'  => [
                'Content-Type' => 'application/json',
                'X-CF7-Mate-Webhook' => '1',
            ],
            'body'     => $body,
        ];

        foreach ($urls as $url) {
            $url = trim($url);
            if ($url === '' || !$this->is_valid_webhook_url($url)) {
                continue;
            }
            wp_safe_remote_post($url, $args);
        }
    }

    /**
     * @return array List of webhook URLs from options.
     */
    private function get_webhook_urls()
    {
        $urls = get_option('cf7m_webhook_urls', []);
        if (!is_array($urls)) {
            return [];
        }
        return array_values(array_filter($urls, 'is_string'));
    }

    /**
     * @param string $url
     * @return bool
     */
    private function is_valid_webhook_url($url)
    {
        return (bool) wp_http_validate_url($url);
    }

    private function get_client_ip()
    {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $raw = sanitize_text_field(wp_unslash($_SERVER[$key]));
                $ip  = trim(explode(',', $raw)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '';
    }

    private function get_user_agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT'])
            ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))
            : '';
    }
}
