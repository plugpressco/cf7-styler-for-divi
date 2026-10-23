<?php

namespace CF7_Mate\Lite;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Feature_Base
{
    protected function __construct()
    {
        $this->init();
    }

    abstract protected function init();

    public static function page_has_cf7_form(): bool
    {
        if (function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled()) {
            return true;
        }
        global $post;
        if (!$post || !is_singular()) {
            return false;
        }
        foreach (['_elementor_data', '_bricks_page_content_2', '_bricks_page_header_2', '_bricks_page_footer_2'] as $key) {
            $builder_data = get_post_meta($post->ID, $key, true);
            $builder_data = is_string($builder_data) ? $builder_data : wp_json_encode($builder_data);
            if (strpos((string) $builder_data, 'cf7') !== false) {
                return true;
            }
        }
        if (has_shortcode($post->post_content, 'contact-form-7')) {
            return true;
        }
        if (strpos($post->post_content, 'dvppl_cf7_styler') !== false) {
            return true;
        }
        if (strpos($post->post_content, 'cf7-mate/cf7-styler') !== false) {
            return true;
        }
        if (function_exists('has_block') && has_block('cf7m/styler', $post)) {
            return true;
        }
        return false;
    }
}
