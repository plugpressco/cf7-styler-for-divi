<?php
/** Isolated-site ability checks; never run on a customer site. */
if (!defined('ABSPATH') || !current_user_can('manage_options')) { return; }
$checks = [];
$expect = static function ($value, $message) use (&$checks) { $checks[] = ['passed' => (bool) $value, 'check' => $message]; };
if (!function_exists('wp_get_ability')) {
    $expect(class_exists('CF7_Mate\Saddle_Abilities') && class_exists('WPCF7_ContactForm'), 'Older WordPress runs CF7 Mate without the Abilities API');
    return ['checks' => $checks];
}
$names = ['list-forms', 'get-form', 'create-form', 'update-form'];
foreach ($names as $name) {
    $expect(null !== wp_get_ability('cf7-mate/' . $name), 'Ability registered: ' . $name);
}
$create = wp_get_ability('cf7-mate/create-form');
$update = wp_get_ability('cf7-mate/update-form');
$get = wp_get_ability('cf7-mate/get-form');
$list = wp_get_ability('cf7-mate/list-forms');
$expect($get->get_meta()['annotations']['readonly'] && $update->get_meta()['annotations']['destructive'], 'Reads are read-only and overwrites require Saddle confirmation');
$catalog = apply_filters('saddle_integrations', []);
$expect($catalog['cf7-mate']['prefix'] === 'cf7-mate/', 'CF7 Mate enrols its own namespace in Saddle');
$form = $create->execute(['title' => 'Agent fixture', 'form' => '<label>Email [email* your-email]</label>[submit "Send"]']);
$expect(!is_wp_error($form) && !empty($form['form_id']) && is_string($form['form']), 'An agent creates a real form through the CF7 controller');
if (is_wp_error($form) || empty($form['form_id'])) { return ['checks' => $checks, 'error' => is_wp_error($form) ? $form->get_error_message() : $form]; }
$id = $form['form_id'];
$native = wpcf7_contact_form($id);
$mail = $native->prop('mail');
update_post_meta($id, '_cf7m_redirect_enabled', '1');
$edit = $update->execute(['form_id' => $id, 'form' => '<label>Your name [text* your-name]</label><label>Email [email* your-email]</label>[submit "Send"]']);
$expect(!is_wp_error($edit) && strpos($edit['form'], '[text* your-name]') !== false, 'An agent edits form markup');
$expect(wpcf7_contact_form($id)->prop('mail') === $mail && get_post_meta($id, '_cf7m_redirect_enabled', true) === '1', 'Agent edits retain Mail and CF7 Mate settings');
$read = $get->execute(['form_id' => $id]);
$expect(!is_wp_error($read) && isset($read['features'], $read['shortcode']) && !isset($read['mail'], $read['responses'], $read['api_key']), 'Reads provide markup and guidance without responses or provider keys');
$found = $list->execute(['search' => 'Agent fixture', 'page' => 1]);
$expect(!is_wp_error($found) && in_array($id, array_column($found['forms'], 'id'), true) && $found['per_page'] === 20, 'Search returns a bounded form list');
$expect(is_wp_error($update->execute(['form_id' => 0, 'form' => 'Invalid'])), 'Invalid update IDs cannot create another form');
$expect(is_wp_error($create->execute(['title' => 'Invalid', 'form' => str_repeat('x', 50001)])), 'Oversized templates are rejected');
$expect(is_wp_error($get->execute(['form_id' => 999999999])), 'Missing forms return an error');
$user = get_current_user_id();
wp_set_current_user(0);
$expect(is_wp_error($get->execute(['form_id' => $id])) && is_wp_error($create->execute(['title' => 'Denied', 'form' => '[submit "Send"]'])), 'Anonymous users cannot read or create forms');
wp_set_current_user($user);
if (class_exists('Saddle_Integrations')) {
    $expect(!Saddle_Integrations::is_approved('cf7-mate'), 'Saddle integration stays off until the owner enables it');
}
return ['checks' => $checks];
