<?php
/** Permission-checked CF7 domain abilities; Saddle supplies the optional MCP transport. */
namespace CF7_Mate;

if (!defined('ABSPATH')) {
    exit;
}

class Saddle_Abilities
{
    public function __construct()
    {
        add_action('wp_abilities_api_categories_init', [$this, 'category']);
        add_action('wp_abilities_api_init', [$this, 'register']);
        add_filter('saddle_integrations', [$this, 'enrol']);
    }

    public function category()
    {
        if (function_exists('wp_register_ability_category')) {
            wp_register_ability_category('cf7-mate', [
                'label' => 'CF7 Mate',
                'description' => __('Create and edit Contact Form 7 forms with CF7 Mate fields and styles.', 'cf7-styler-for-divi'),
            ]);
        }
    }

    public function enrol($catalog)
    {
        $catalog['cf7-mate'] = [
            'prefix' => 'cf7-mate/',
            'title' => 'CF7 Mate',
            'description' => __('Find, create and edit Contact Form 7 forms. No access to submitted responses or provider keys.', 'cf7-styler-for-divi'),
            'author' => 'PlugPress',
            'url' => 'https://wordpress.org/plugins/cf7-styler-for-divi/',
        ];
        return $catalog;
    }

    public function register()
    {
        if (!function_exists('wp_register_ability') || !class_exists('WPCF7_ContactForm')) {
            return;
        }
        $id = ['type' => 'integer', 'minimum' => 1];
        $title = ['type' => 'string', 'minLength' => 1, 'maxLength' => 200];
        $form = ['type' => 'string', 'minLength' => 1, 'maxLength' => 50000, 'description' => 'Contact Form 7 form-template markup, including labels and a submit tag. Use get-form to see enabled CF7 Mate features first.'];
        $definitions = [
            'list-forms' => [__('Find forms', 'cf7-styler-for-divi'), __('List Contact Form 7 form IDs and titles. Search before creating a form to avoid duplicates. Does not return submissions.', 'cf7-styler-for-divi'), ['search' => ['type' => 'string', 'maxLength' => 200], 'page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10000]], [], true],
            'get-form' => [__('Read a form', 'cf7-styler-for-divi'), __('Read one form template, shortcode, enabled CF7 Mate features and styling guidance before editing. Mail configuration, responses and API keys are not returned.', 'cf7-styler-for-divi'), ['form_id' => $id], ['form_id'], true],
            'create-form' => [__('Create a form', 'cf7-styler-for-divi'), __('Create a Contact Form 7 form from title and form-template markup. Returns its shortcode and configuration warnings. Does not insert it into a page, change notification settings or send mail. Review the native Mail tab before publishing.', 'cf7-styler-for-divi'), ['title' => $title, 'form' => $form], ['title', 'form'], false],
            'update-form' => [__('Edit a form', 'cf7-styler-for-divi'), __('Replace an existing form template, optionally changing its title. Read the form first. Existing Mail, Messages and CF7 Mate per-form settings are preserved. Changes affect every page using this form; Saddle asks for confirmation.', 'cf7-styler-for-divi'), ['form_id' => $id, 'title' => $title, 'form' => $form], ['form_id', 'form'], false],
        ];
        foreach ($definitions as $name => $definition) {
            list($label, $description, $properties, $required, $read) = $definition;
            wp_register_ability('cf7-mate/' . $name, [
                'label' => $label,
                'description' => $description,
                'category' => 'cf7-mate',
                'input_schema' => ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false],
                'output_schema' => ['type' => 'object', 'additionalProperties' => true],
                'permission_callback' => [$this, 'permission'],
                'execute_callback' => function ($input) use ($name) { return $this->execute($name, $input); },
                'meta' => ['show_in_rest' => true, 'annotations' => ['readonly' => $read, 'destructive' => 'update-form' === $name, 'idempotent' => $read || 'update-form' === $name]],
            ]);
        }
    }

    public function permission($input = [])
    {
        return current_user_can('manage_options') && current_user_can('wpcf7_edit_contact_forms')
            && (empty($input['form_id']) || current_user_can('wpcf7_edit_contact_form', (int) $input['form_id']));
    }

    public function execute($operation, $input)
    {
        if (!$this->permission($input)) {
            return new \WP_Error('cf7m_forbidden', __('You cannot manage these forms.', 'cf7-styler-for-divi'), ['status' => 403]);
        }
        $id = isset($input['form_id']) ? (int) $input['form_id'] : 0;
        if (!in_array($operation, ['list-forms', 'get-form', 'create-form', 'update-form'], true)) {
            return new \WP_Error('cf7m_invalid_operation', __('Unknown form operation.', 'cf7-styler-for-divi'));
        }
        if (in_array($operation, ['get-form', 'update-form'], true) && $id < 1) {
            return new \WP_Error('cf7m_invalid_form_id', __('Choose an existing form.', 'cf7-styler-for-divi'));
        }
        $path = '/contact-form-7/v1/contact-forms' . ($id ? '/' . $id : '');
        $write = in_array($operation, ['create-form', 'update-form'], true);
        if ($write && (!isset($input['form']) || !is_string($input['form']) || '' === trim($input['form']) || strlen($input['form']) > 50000)) {
            return new \WP_Error('cf7m_invalid_form', __('Provide a form template of at most 50 KB.', 'cf7-styler-for-divi'));
        }
        $request = new \WP_REST_Request($write ? 'POST' : 'GET', $path);
        // Let CF7's own controller enforce its capabilities, sanitization and save hooks.
        $params = $write ? ['context' => 'save', 'form' => $input['form']] : [];
        if (isset($input['title'])) {
            $params['title'] = $input['title'];
        }
        if ('list-forms' === $operation) {
            $params = ['per_page' => 20, 'offset' => (max(1, (int) ($input['page'] ?? 1)) - 1) * 20, 'search' => $input['search'] ?? ''];
        }
        $request->set_body_params($params);
        $response = rest_do_request($request);
        if ($response->is_error()) {
            return $response->as_error();
        }
        $data = $response->get_data();
        if ('list-forms' === $operation) {
            return ['forms' => $data, 'per_page' => 20, 'page' => (int) ($input['page'] ?? 1)];
        }
        $result = [
            'form_id' => (int) $data['id'],
            'title' => $data['title'],
            'form' => $data['properties']['form']['content'],
            'shortcode' => '[contact-form-7 id="' . (int) $data['id'] . '"]',
            'edit_url' => admin_url('admin.php?page=wpcf7&post=' . (int) $data['id'] . '&action=edit'),
            'features' => Feature_Settings::get(),
            'guidance' => 'Use accessible labels, required-field tags and [submit "Send"]. If presets is enabled, wrap the form in [cf7m-presets style="sky"]...[/cf7m-presets]. Use enabled CF7 Mate features only. Review the native Mail tab, then embed the shortcode in a page and style it using the site theme or CF7 Mate builder module. Never send a test submission without the owner asking.',
        ];
        if (isset($data['config_errors'])) {
            $result['config_errors'] = $data['config_errors'];
        }
        return $result;
    }
}
