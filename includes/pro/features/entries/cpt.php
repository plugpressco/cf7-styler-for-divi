<?php
/**
 * Entries CPT – register post type and meta.
 *
 * @package CF7_Mate\Features\Entries
 * @since 3.0.0
 */

namespace CF7_Mate\Features\Entries;

if (!defined('ABSPATH')) {
    exit;
}

class Entries_CPT
{
    const POST_TYPE = 'cf7m_entry';

    public function __construct()
    {
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'register_meta']);
    }

    public function register_post_type()
    {
        $labels = [
            'name'               => __('Form Entries', 'cf7-styler-for-divi'),
            'singular_name'       => __('Form Entry', 'cf7-styler-for-divi'),
            'menu_name'          => __('Form Entries', 'cf7-styler-for-divi'),
            'not_found'           => __('No entries found', 'cf7-styler-for-divi'),
            'not_found_in_trash'  => __('No entries found in trash', 'cf7-styler-for-divi'),
        ];

        register_post_type(self::POST_TYPE, [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => false,
            'show_in_menu'       => false,
            'show_in_rest'       => false,
            'supports'           => ['title', 'custom-fields'],
            'has_archive'        => false,
            'rewrite'            => false,
            'query_var'          => false,
        ]);
    }

    public function register_meta()
    {
        $object_type = self::POST_TYPE;
        register_post_meta($object_type, '_cf7m_form_id', [
            'type'              => 'integer',
            'description'       => __('Contact Form 7 ID', 'cf7-styler-for-divi'),
            'single'            => true,
            'sanitize_callback' => 'absint',
        ]);
        register_post_meta($object_type, '_cf7m_form_title', [
            'type'              => 'string',
            'description'       => __('Form title at submission', 'cf7-styler-for-divi'),
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        register_post_meta($object_type, '_cf7m_status', [
            'type'              => 'string',
            'description'       => __('Entry status (new, read, trash)', 'cf7-styler-for-divi'),
            'single'            => true,
            'sanitize_callback' => [$this, 'sanitize_status'],
        ]);
        register_post_meta($object_type, '_cf7m_data', [
            'type'              => 'string',
            'description'       => __('Form submission data as JSON', 'cf7-styler-for-divi'),
            'single'            => true,
            'sanitize_callback' => [$this, 'sanitize_json'],
        ]);
        register_post_meta($object_type, '_cf7m_created', [
            'type'              => 'string',
            'description'       => __('Submission timestamp', 'cf7-styler-for-divi'),
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        register_post_meta($object_type, '_cf7m_ip', [
            'type'              => 'string',
            'description'       => __('Submitter IP', 'cf7-styler-for-divi'),
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        register_post_meta($object_type, '_cf7m_ua', [
            'type'              => 'string',
            'description'       => __('Submitter user agent', 'cf7-styler-for-divi'),
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
        ]);
    }

    public function sanitize_status($value)
    {
        $allowed = ['new', 'read', 'trash'];
        return in_array($value, $allowed, true) ? $value : 'new';
    }

    public function sanitize_json($value)
    {
        if (!is_string($value)) {
            return '{}';
        }
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? wp_json_encode($decoded, JSON_UNESCAPED_UNICODE) : '{}';
    }
}
