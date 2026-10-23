<?php
/**
 * Base for CF7 form-tag Pro features (cf7m-star, cf7m-range, etc.).
 *
 * @package CF7_Mate\Pro
 * @since 3.0.0
 */

namespace CF7_Mate\Pro;

if (!defined('ABSPATH')) {
    exit;
}

abstract class CF7_Form_Tag_Feature extends Pro_Feature_Base
{
    protected function init()
    {
        add_action('wpcf7_init', [$this, 'register_form_tag']);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generator'], 25);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        foreach ($this->get_form_tag_names() as $tag_name) {
            add_filter('wpcf7_validate_' . $tag_name, [$this, 'validate_field'], 10, 2);
        }
    }

    abstract protected function get_form_tag_names(): array;

    abstract protected function get_tag_generator_id(): string;

    abstract protected function get_tag_generator_title(): string;

    public function register_form_tag()
    {
        if (!function_exists('wpcf7_add_form_tag')) {
            return;
        }
        
        // Skip if tag already registered (main plugin handles basic registration)
        if (class_exists('\WPCF7_FormTagsManager')) {
            $tags = $this->get_form_tag_names();
            $first_tag = is_array($tags) ? reset($tags) : $tags;
            if (\WPCF7_FormTagsManager::get_instance()->tag_type_exists($first_tag)) {
                return;
            }
        }
        
        wpcf7_add_form_tag(
            $this->get_form_tag_names(),
            [$this, 'render_form_tag'],
            ['name-attr' => true]
        );
    }

    public function add_tag_generator()
    {
        if (class_exists('WPCF7_TagGenerator')) {
            \WPCF7_TagGenerator::get_instance()->add(
                $this->get_tag_generator_id(),
                $this->get_tag_generator_title(),
                [$this, 'tag_generator_callback'],
                ['version' => '2']
            );
        }
    }

    abstract public function render_form_tag($tag): string;

    abstract public function validate_field($result, $tag);

    abstract public function tag_generator_callback($contact_form, $options = ''): void;

    abstract public function enqueue_assets(): void;

    protected function has_cf7_form_on_page(): bool
    {
        return Pro_Feature_Base::page_has_cf7_form();
    }
}
