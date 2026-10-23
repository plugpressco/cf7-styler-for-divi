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
            'cf7_module' => ['name' => __('Divi', 'cf7-styler-for-divi'), 'desc' => __('Style forms inside Divi 4 and Divi 5.', 'cf7-styler-for-divi'), 'builder' => 'divi'],
            'bricks_module' => ['name' => __('Bricks', 'cf7-styler-for-divi'), 'desc' => __('Style forms inside Bricks.', 'cf7-styler-for-divi'), 'builder' => 'bricks'],
            'elementor_module' => ['name' => __('Elementor', 'cf7-styler-for-divi'), 'desc' => __('Style forms inside Elementor.', 'cf7-styler-for-divi'), 'builder' => 'elementor'],
            'gutenberg_module' => ['name' => __('Block Editor', 'cf7-styler-for-divi'), 'desc' => __('Style forms in the WordPress block editor.', 'cf7-styler-for-divi'), 'builder' => 'gutenberg'],
            'grid_layout' => ['name' => __('Grid layout', 'cf7-styler-for-divi'), 'desc' => __('Arrange fields in a responsive grid.', 'cf7-styler-for-divi')],
            'multi_column' => ['name' => __('Multi-column layout', 'cf7-styler-for-divi'), 'desc' => __('Arrange fields side by side in columns.', 'cf7-styler-for-divi')],
            'multi_step' => ['name' => __('Multi-step forms', 'cf7-styler-for-divi'), 'desc' => __('Split forms into steps with progress and validation.', 'cf7-styler-for-divi')],
            'conditional' => ['name' => __('Conditional logic', 'cf7-styler-for-divi'), 'desc' => __('Show or hide fields based on answers.', 'cf7-styler-for-divi')],
            'star_rating' => ['name' => __('Star rating', 'cf7-styler-for-divi'), 'desc' => __('Collect ratings with a keyboard-accessible star field.', 'cf7-styler-for-divi')],
            'range_slider' => ['name' => __('Range slider', 'cf7-styler-for-divi'), 'desc' => __('Collect numeric values with a slider.', 'cf7-styler-for-divi')],
            'phone_number' => ['name' => __('Phone number', 'cf7-styler-for-divi'), 'desc' => __('International phone input with a country selector.', 'cf7-styler-for-divi')],
            'separator' => ['name' => __('Separator', 'cf7-styler-for-divi'), 'desc' => __('Add a divider between fields.', 'cf7-styler-for-divi')],
            'heading' => ['name' => __('Heading', 'cf7-styler-for-divi'), 'desc' => __('Add headings to form sections.', 'cf7-styler-for-divi')],
            'image' => ['name' => __('Image', 'cf7-styler-for-divi'), 'desc' => __('Insert an image inside a form.', 'cf7-styler-for-divi')],
            'icon' => ['name' => __('Icon', 'cf7-styler-for-divi'), 'desc' => __('Add icons inside forms.', 'cf7-styler-for-divi')],
            'ai_form_generator' => ['name' => __('AI form generator', 'cf7-styler-for-divi'), 'desc' => __('Generate forms using your own AI provider key. Provider charges apply.', 'cf7-styler-for-divi')],
            'presets' => ['name' => __('Style presets', 'cf7-styler-for-divi'), 'desc' => __('Apply predefined form styles.', 'cf7-styler-for-divi')],
            'database_entries' => ['name' => __('Form responses', 'cf7-styler-for-divi'), 'desc' => __('Store submissions, IP addresses and browser details in your WordPress database.', 'cf7-styler-for-divi')],
            'analytics' => ['name' => __('Form analytics', 'cf7-styler-for-divi'), 'desc' => __('Track form views locally. Submission counts require Form responses.', 'cf7-styler-for-divi')],
            'webhook' => ['name' => __('Webhooks', 'cf7-styler-for-divi'), 'desc' => __('Send submissions and visitor details to the destinations you configure.', 'cf7-styler-for-divi')],
            'form_scheduling' => ['name' => __('Form scheduling', 'cf7-styler-for-divi'), 'desc' => __('Open and close forms on a schedule.', 'cf7-styler-for-divi')],
            'email_routing' => ['name' => __('Email routing', 'cf7-styler-for-divi'), 'desc' => __('Route notifications according to field values.', 'cf7-styler-for-divi')],
            'redirect' => ['name' => __('Redirect after submission', 'cf7-styler-for-divi'), 'desc' => __('Choose a destination after a successful submission.', 'cf7-styler-for-divi')],
            'partial_save' => ['name' => __('Save and continue', 'cf7-styler-for-divi'), 'desc' => __('Let visitors resume in the same browser for up to seven days. Enable per form.', 'cf7-styler-for-divi')],
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
