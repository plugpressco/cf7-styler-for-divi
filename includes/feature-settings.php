<?php
/** Shared feature settings and the free-edition upgrade. */
namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Feature_Settings
{
    const MIGRATION_VERSION = '1';

    public static function definitions()
    {
        return [
            'cf7_module' => ['name' => __('Divi', 'cf7-styler-for-divi'), 'desc' => __('Style forms in Divi 4 and Divi 5.', 'cf7-styler-for-divi'), 'builder' => 'divi'],
            'bricks_module' => ['name' => __('Bricks', 'cf7-styler-for-divi'), 'desc' => __('Style forms in Bricks.', 'cf7-styler-for-divi'), 'builder' => 'bricks'],
            'elementor_module' => ['name' => __('Elementor', 'cf7-styler-for-divi'), 'desc' => __('Style forms in Elementor.', 'cf7-styler-for-divi'), 'builder' => 'elementor'],
            'gutenberg_module' => ['name' => __('Block editor', 'cf7-styler-for-divi'), 'desc' => __('Style forms in the block editor.', 'cf7-styler-for-divi'), 'builder' => 'gutenberg'],
            'grid_layout' => ['name' => __('Grid layout', 'cf7-styler-for-divi'), 'desc' => __('Arrange fields in a grid.', 'cf7-styler-for-divi')],
            'multi_column' => ['name' => __('Columns', 'cf7-styler-for-divi'), 'desc' => __('Place fields side by side.', 'cf7-styler-for-divi')],
            'multi_step' => ['name' => __('Multi-step forms', 'cf7-styler-for-divi'), 'desc' => __('Split a form into steps.', 'cf7-styler-for-divi')],
            'conditional' => ['name' => __('Conditional logic', 'cf7-styler-for-divi'), 'desc' => __('Show or hide fields based on answers.', 'cf7-styler-for-divi')],
            'star_rating' => ['name' => __('Star rating', 'cf7-styler-for-divi'), 'desc' => __('A star rating field that works with a keyboard.', 'cf7-styler-for-divi')],
            'range_slider' => ['name' => __('Range slider', 'cf7-styler-for-divi'), 'desc' => __('Pick a number with a slider.', 'cf7-styler-for-divi')],
            'phone_number' => ['name' => __('Phone number', 'cf7-styler-for-divi'), 'desc' => __('Phone field with a country picker.', 'cf7-styler-for-divi')],
            'separator' => ['name' => __('Separator', 'cf7-styler-for-divi'), 'desc' => __('A divider between fields.', 'cf7-styler-for-divi')],
            'heading' => ['name' => __('Heading', 'cf7-styler-for-divi'), 'desc' => __('Headings inside forms.', 'cf7-styler-for-divi')],
            'image' => ['name' => __('Image', 'cf7-styler-for-divi'), 'desc' => __('Images inside forms.', 'cf7-styler-for-divi')],
            'icon' => ['name' => __('Icon', 'cf7-styler-for-divi'), 'desc' => __('Icons inside forms.', 'cf7-styler-for-divi')],
            'ai_form_generator' => ['name' => __('AI form generator', 'cf7-styler-for-divi'), 'desc' => __('Describe a form and get the code. Uses your own provider key.', 'cf7-styler-for-divi')],
            'presets' => ['name' => __('Style presets', 'cf7-styler-for-divi'), 'desc' => __('Ready-made form styles.', 'cf7-styler-for-divi')],
            'database_entries' => ['name' => __('Form responses', 'cf7-styler-for-divi'), 'desc' => __('Save submissions, IP address and browser in WordPress.', 'cf7-styler-for-divi')],
            'analytics' => ['name' => __('Form analytics', 'cf7-styler-for-divi'), 'desc' => __('Count form views on this site.', 'cf7-styler-for-divi')],
            'webhook' => ['name' => __('Webhooks', 'cf7-styler-for-divi'), 'desc' => __('Send submissions to the URLs you add.', 'cf7-styler-for-divi')],
            'form_scheduling' => ['name' => __('Scheduling', 'cf7-styler-for-divi'), 'desc' => __('Open and close forms on a schedule.', 'cf7-styler-for-divi')],
            'email_routing' => ['name' => __('Email routing', 'cf7-styler-for-divi'), 'desc' => __('Send mail to different people based on answers.', 'cf7-styler-for-divi')],
            'redirect' => ['name' => __('Redirect', 'cf7-styler-for-divi'), 'desc' => __('Go to a page after a successful submission.', 'cf7-styler-for-divi')],
            'partial_save' => ['name' => __('Save and continue', 'cf7-styler-for-divi'), 'desc' => __('Visitors can finish later in the same browser for up to seven days.', 'cf7-styler-for-divi')],
        ];
    }

    public static function defaults()
    {
        return [
            'cf7_module' => true,
            'bricks_module' => true,
            'elementor_module' => true,
            'gutenberg_module' => true,
            'grid_layout' => true,
            'multi_column' => true,
            'multi_step' => true,
            'conditional' => true,
            'star_rating' => true,
            'range_slider' => true,
            'phone_number' => true,
            'separator' => true,
            'heading' => true,
            'image' => true,
            'icon' => true,
            'ai_form_generator' => true,
            'presets' => true,
            'database_entries' => false,
            'analytics' => false,
            'webhook' => false,
            'form_scheduling' => true,
            'email_routing' => true,
            'redirect' => true,
            'partial_save' => false,
        ];
    }

    public static function get()
    {
        $saved = get_option('cf7m_features', []);
        $saved = is_array($saved) ? $saved : [];
        return array_replace(self::defaults(), array_intersect_key($saved, self::defaults()));
    }

    public static function enabled($key)
    {
        $settings = self::get();
        return !empty($settings[$key]);
    }

    /** Partial updates preserve every unrelated toggle. Unknown keys never become runnable features. */
    public static function save(array $input)
    {
        $settings = self::get();
        foreach (self::defaults() as $key => $default) {
            if (array_key_exists($key, $input) && is_scalar($input[$key])) {
                $settings[$key] = rest_sanitize_boolean($input[$key]);
            }
        }
        update_option('cf7m_features', $settings, false);
        return $settings;
    }

    public static function metadata()
    {
        $items = [];
        foreach (self::definitions() as $key => $definition) {
            $items[] = array_merge(['id' => $key, 'default' => self::defaults()[$key]], $definition);
        }
        return $items;
    }

    /** Runs on normal updates too; preserves data and never contacts the retired license service. */
    public static function migrate()
    {
        if (get_option('cf7m_free_migration_version') === self::MIGRATION_VERSION) {
            return;
        }
        $saved = get_option('cf7m_features', []);
        $saved = is_array($saved) ? $saved : [];
        $previous = (string) get_option('cf7m_current_version', '');
        $origin = get_option('cf7m_edition_origin', '');
        // Pro releases used the independent 1.x version series.
        if ($origin !== 'pro') {
            $origin = preg_match('/^1\./', $previous) ? 'pro' : ($previous ? 'lite' : 'fresh');
        }
        $settings = array_replace(self::defaults(), array_intersect_key($saved, self::defaults()));
        if ($origin === 'pro') {
            foreach (['database_entries', 'analytics', 'webhook'] as $key) {
                if (!array_key_exists($key, $saved)) {
                    $settings[$key] = true;
                }
            }
        } else {
            // Locked Lite settings previously contained true values without running these modules.
            foreach (['database_entries', 'analytics', 'webhook', 'partial_save'] as $key) {
                $settings[$key] = false;
            }
        }
        add_option('cf7m_pre_free_features', $saved, '', false);
        update_option('cf7m_features', $settings, false);
        update_option('cf7m_edition_origin', $origin, false);
        wp_clear_scheduled_hook('cf7m_daily_license_check');
        delete_transient('cf7m_license_valid');
        delete_transient('cf7m_update_info');
        update_option('cf7m_free_migration_version', self::MIGRATION_VERSION, false);
    }
}
