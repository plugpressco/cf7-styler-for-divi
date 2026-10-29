<?php
/** Run in an isolated WordPress installation as an administrator with all features enabled. */
if (!defined('ABSPATH') || !current_user_can('manage_options')) { return; }
$checks = [];
$expect = static function ($value, $message) use (&$checks) { $checks[] = ['passed'=>(bool) $value, 'check'=>$message]; };
$request = static function ($method, $path, $params = []) {
    $r = new WP_REST_Request($method, '/cf7-styler/v1' . $path);
    $r->set_body_params($params);
    $r->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    return rest_do_request($r);
};
$expect(!defined('CF7M_IS_PRO_VERSION') && !defined('CF7M_DEV_MODE'), 'No Pro or development flag required');
$expect(!class_exists('CF7_Mate\\License\\License_Manager') && !wp_next_scheduled('cf7m_daily_license_check'), 'No license runtime or daily license event');
foreach (['Multi_Column\\Multi_Column','Multi_Steps\\Multi_Steps','Entries\\Entries','Conditional\\Conditional','Presets\\Presets','Webhook\\Webhook','Analytics\\Analytics','Scheduling\\Scheduling','Email_Routing\\Email_Routing','Redirect\\Redirect','Partial_Save\\Partial_Save'] as $class) {
    $expect(class_exists('CF7_Mate\\Features\\'.$class), 'Advanced module loaded: '.$class);
}
foreach (['/settings/features','/settings/webhook','/entries','/analytics/stats'] as $path) {
    $expect($request('GET',$path)->get_status() === 200, 'Administrator access without license: '.$path);
}
$routes = rest_get_server()->get_routes();
$export_args = $routes['/cf7-styler/v1/entries/export'][0]['args'];
$expect(!is_wp_error(rest_validate_value_from_schema(500,$export_args['per_page'])), 'Export accepts the 500-response limit requested by the UI');
$expect(is_wp_error(rest_validate_value_from_schema(501,$export_args['per_page'])), 'Export rejects requests above its bounded limit');
$expect(is_wp_error(rest_validate_value_from_schema(500,$routes['/cf7-styler/v1/entries'][0]['args']['per_page'])), 'Normal response pagination remains bounded at 100');
$expect($request('GET','/license/status')->get_status() === 404, 'License endpoint retired');
$before = CF7_Mate\Feature_Settings::get();
$updated = $request('POST','/settings/features',['features'=>['redirect'=>false]])->get_data();
$expect(!empty($updated['features']['database_entries']) && $updated['features']['redirect'] === false, 'REST partial settings update preserves other choices');
CF7_Mate\Feature_Settings::save($before);
$old_brand = get_option('cf7m_white_label', []);
$expect($request('GET','/settings/white-label')->get_status() === 404, 'White label endpoint retired');
$expect(apply_filters('cf7m_admin_menu_label','CF7 Mate') === 'CF7 Mate', 'Retired branding cannot change CF7 Mate identity');
$expect(get_option('cf7m_white_label', []) === $old_brand, 'Retired branding data is retained');
$form = WPCF7_ContactForm::get_template();
$form->set_title('CF7 Mate runtime fixture');
$form->set_properties(['form'=>'[cf7m-presets style="sky"][cf7m-step title="First"][text* your-name][/cf7m-step][cf7m-step title="Second"][email* your-email][cf7m-if field:your-name is:Support][textarea* support-details][/cf7m-if][submit "Send"][/cf7m-step][/cf7m-presets]']);
$form_id = $form->save();
$expect($form_id > 0, 'Existing CF7 editor creates a normal form');
$html = do_shortcode('[contact-form-7 id="'.$form_id.'"]');
$expect(strpos($html,'cf7m-multistep-form') !== false && strpos($html,'cf7-mate-preset--sky') !== false, 'Multi-step and preset wrappers render together');
update_post_meta($form_id, '_cf7m_schedule_enabled', '1');
update_post_meta($form_id, '_cf7m_schedule_end', '2000-01-01 00:00:00');
update_post_meta($form_id, '_cf7m_schedule_msg', 'Fixture closed');
$closed = do_shortcode('[contact-form-7 id="'.$form_id.'"]');
$abort = false;
CF7_Mate\Features\Scheduling\Scheduling::instance()->maybe_abort($form, $abort);
$expect($abort, 'Scheduling also blocks submission server-side');
$expect(strpos($closed, 'Fixture closed') !== false && strpos($closed, 'name="your-name"') === false, 'Scheduling closes the form using its stored window');
update_post_meta($form_id, '_cf7m_schedule_enabled', '0');
$conditional = apply_filters('wpcf7_form_elements', '[cf7m-if field:"your-name" is:"Fixture"]Conditional content[/cf7m-if]');
$expect(strpos($conditional, '[cf7m-if') === false && strpos($conditional, 'data-cf7m-if') !== false, 'Conditional markup becomes executable field rules');
$conditions = CF7_Mate\Features\Conditional\Conditional::instance();
$probe = WPCF7_ContactForm::get_template();
$probe->set_properties(['form'=>'[text* always][select reason "General" "Support"][cf7m-if field:reason is:Support][textarea* details][cf7m-if field:level gte:2][text* nested][/cf7m-if][/cf7m-if]']);
$expect($conditions->hidden_fields($probe, ['reason'=>'General','level'=>'3']) === ['details','nested'], 'Hidden parent excludes nested required fields on the server');
$expect($conditions->hidden_fields($probe, ['reason'=>'Support','level'=>'1']) === ['nested'], 'Visible parent retains its required fields while hiding a nested condition');
$expect($conditions->hidden_fields($probe, ['reason'=>'Support','level'=>'2']) === [], 'Matching nested conditions retain all validation');
$nested = $conditions->process_shortcodes($probe->prop('form'));
$expect(substr_count($nested, 'class="cf7m-condition"') === 2 && strpos($nested,'[cf7m-if') === false && strpos($nested,'[/cf7m-if]') === false, 'Nested conditional markup pairs all closing tags');
$probe->set_properties(['form'=>'[text* shared][cf7m-if field:reason is:Support][text* shared][text* hidden][/cf7m-if]']);
$expect($conditions->hidden_fields($probe, ['reason'=>'General']) === ['hidden'], 'A repeated visible field cannot have its validation suppressed');
$probe->set_properties(['form'=>'[cf7m-if field:reason is:Support][text* hidden]']);
$expect($conditions->hidden_fields($probe, ['reason'=>'General']) === [], 'Malformed conditions fail closed without disabling validation');
$probe->set_properties(['form'=>'[cf7m-if field:choices checked:true][text* checked-field][/cf7m-if][cf7m-if field:choices any:"One, Two"][text* any-field][/cf7m-if]']);
$expect($conditions->hidden_fields($probe, ['choices'=>[]]) === ['checked-field','any-field'], 'Unchecked multi-value fields hide dependent fields');
$expect($conditions->hidden_fields($probe, ['choices'=>['Two']]) === [], 'Checked multi-value fields match checked and any operators');
$form = WPCF7_ContactForm::get_instance($form_id);
$columns = apply_filters('wpcf7_form_elements', '[cf7m-row gap:20][cf7m-col width:50]Column content[/cf7m-col][/cf7m-row]');
$expect(strpos($columns, '[cf7m-row') === false && strpos($columns, 'Column content') !== false, 'Advanced columns render without raw tags');
update_post_meta($form_id, '_cf7m_redirect', wp_json_encode(['enabled'=>true,'url'=>'https://example.test/thanks','new_tab'=>false,'delay_ms'=>0,'rules'=>[]]));
$redirect_html = do_shortcode('[contact-form-7 id="'.$form_id.'"]');
$expect(strpos($redirect_html,'cf7m-redirect-config') !== false && strpos($redirect_html,'example.test') !== false, 'Redirect configuration is available without entitlement');
$legacy = apply_filters('wpcf7_form_elements','[dcs_row][dcs_col_half]Legacy[/dcs_col_half][/dcs_row]');
$expect(strpos($legacy,'[dcs_') === false && strpos($legacy,'cf7m-col-md-6') !== false, 'Legacy saved column shortcodes survive');
$grid = apply_filters('wpcf7_form_elements','[dipe_row][dipe_one_half]Grid[/dipe_one_half][/dipe_row][audio src="https://example.test/a.mp3"]');
$expect(strpos($grid,'cf7m-col-md-6') !== false && strpos($grid,'[dipe_') === false, 'Grid tags render');
$expect(strpos($grid,'[audio') !== false && strpos($grid,'wp-audio') === false, 'Grid layout runs no other shortcodes');
$echoed = apply_filters('wpcf7_form_elements','<input type="text" name="a" value="[cf7m-heading text:x] [dcs_row class:y] [audio src=&quot;z&quot;]"><textarea name="b">[cf7m-icon icon:star][dipe_row]R[/dipe_row]</textarea>');
$expect(!preg_match('/<(h[1-6]|span|div|audio|a)\b/', $echoed), 'Submitted values echoed after failed validation stay text');
$expect(substr_count($echoed,'&#91;') === 6 && strpos($echoed,'name="a" value="&#91;cf7m-heading') !== false, 'Brackets in echoed values are encoded');
$token = md5('cf7mate-runtime-token');
$expect($request('POST','/partial-save',['form_id'=>$form_id,'token'=>$token,'data'=>['your-name'=>'Fixture']])->get_status() === 400, 'Partial save rejects a form that has not opted in');
update_post_meta($form_id,'_cf7m_partial_save_enabled','1');
$expect($request('POST','/partial-save',['form_id'=>$form_id,'token'=>$token,'data'=>['your-name'=>'Fixture','MixedCase'=>'Preserved','interests[]'=>['Design','Support'],'updates[]'=>[]]])->get_status() === 200, 'Optional partial save accepts a normal payload');
$restored = $request('GET','/partial-save/'.$token)->get_data()['data'];
$expect($restored['your-name'] === 'Fixture', 'Partial save restores existing values');
$expect($restored['MixedCase'] === 'Preserved' && $restored['interests'] === ['Design','Support'] && $restored['updates'] === [], 'Partial save preserves case, multiple selections and unchecked groups');
$expect($request('POST','/partial-save',['form_id'=>$form_id,'token'=>$token,'data'=>['nested'=>[['bad'=>'value']]]])->get_status() === 400, 'Partial save rejects nested objects');
$expect($request('POST','/partial-save',['form_id'=>$form_id,'token'=>$token,'data'=>['large'=>str_repeat('x',65537)]])->get_status() === 400, 'Partial save bounds payload size');
$expect($request('DELETE','/partial-save/'.$token)->get_status() === 200, 'Partial save can delete the saved progress');
$expect($request('POST','/analytics/view',['form_id'=>$form_id])->get_data()['ok'] === true, 'Local view tracking works');
$expect(apply_filters('wpcf7_autop_or_not', true) === false && apply_filters('wpcf7_autop_or_not', true, ['for'=>'form']) === false, 'Grid markup keeps auto-p off in forms');
$expect(apply_filters('wpcf7_autop_or_not', true, ['for'=>'mail']) === true, 'HTML mail keeps CF7 auto-p');
if (function_exists('wpcf7_mail_html_body_autop')) {
    $expect(strpos(wpcf7_mail_html_body_autop("Line one\nLine two"), '<br') !== false, 'HTML mail body keeps its line breaks');
}
$expect((int)get_option('cf7m_views_'.$form_id) === 1, 'View count persisted locally');
$form = WPCF7_ContactForm::get_instance($form_id);
// Deliver no mail and contact no destinations; verify the actual CF7 submission hook saves once.
$posted_before = $_POST;
$_POST = ['your-name'=>'Runtime Fixture','your-email'=>'fixture@example.test'];
add_filter('wpcf7_spam', '__return_false', 999);
$previous_urls = get_option('cf7m_webhook_urls', []);
update_option('cf7m_webhook_urls', ['https://example.com/cf7mate-fixture']);
update_post_meta($form_id, '_cf7m_email_rules', wp_json_encode([['field'=>'your-name','operator'=>'is','value'=>'Runtime Fixture','emails'=>['team@example.test']]]));
$captured = [];
$http_sink = static function($pre, $args, $url) use (&$captured) {
    $captured[] = ['url'=>$url,'args'=>$args];
    return ['headers'=>[],'body'=>'{}','response'=>['code'=>200,'message'=>'OK'],'cookies'=>[]];
};
add_filter('pre_http_request', $http_sink, 10, 3);
$result = $form->submit(['skip_mail'=>true]);
remove_filter('pre_http_request', $http_sink, 10);
update_option('cf7m_webhook_urls', $previous_urls);
$expect(count($captured) === 1 && $captured[0]['url'] === 'https://example.com/cf7mate-fixture', 'Webhook sends once to the configured destination through the local request sink');
$expect($form->prop('mail')['recipient'] === 'team@example.test', 'Conditional email routing selects the matching recipient');
$expect($result['status'] === 'mail_sent', 'CF7 accepts a submission with a hidden required conditional field and mail safely skipped');
$entries = get_posts(['post_type'=>'cf7m_entry','posts_per_page'=>-1,'meta_key'=>'_cf7m_form_id','meta_value'=>$form_id]);
$expect(count($entries) === 1, 'An actual CF7 submission creates exactly one response');
if ($entries) {
    $stored = json_decode(get_post_meta($entries[0]->ID, '_cf7m_data', true), true);
    $expect(($stored['your-name'] ?? '') === 'Runtime Fixture', 'Stored response preserves the submitted fields');
}
// CF7 handles one submission per request; reset it so each fixture form submits.
$next_submission = static function () { $instance = new ReflectionProperty('WPCF7_Submission', 'instance'); $instance->setAccessible(true); $instance->setValue(null, null); };
$text_form = WPCF7_ContactForm::get_template();
$text_form->set_title('CF7 Mate text fixture');
$text_form->set_properties(['form'=>'[text your-name][textarea your-message][submit "Send"]']);
$text_form_id = $text_form->save();
$message = "She said \"hi\"\r\nZoë café — 東京";
$_POST = ['your-name'=>'Zoë', 'your-message'=>$message];
$next_submission();
$text_form->submit(['skip_mail'=>true]);
$text_entries = get_posts(['post_type'=>'cf7m_entry','posts_per_page'=>1,'meta_key'=>'_cf7m_form_id','meta_value'=>$text_form_id]);
$stored = $text_entries ? json_decode(get_post_meta($text_entries[0]->ID, '_cf7m_data', true), true) : [];
$expect(($stored['your-message'] ?? '') === $message && ($stored['your-name'] ?? '') === 'Zoë', 'Stored response keeps quotes, line breaks and non-ASCII text');
$phone_form = WPCF7_ContactForm::get_template();
$phone_form->set_title('CF7 Mate phone fixture');
$phone_form->set_properties(['form'=>'[cf7m-phone* phone default:GB placeholder:"7400 123456"][submit "Send"]']);
$phone_form_id = $phone_form->save();
$expect(strpos(do_shortcode('[contact-form-7 id="'.$phone_form_id.'"]'), 'placeholder="7400 123456"') !== false, 'Phone placeholder keeps quoted spaces');
$_POST = ['phone'=>'+44 ', 'phone__country'=>'GB'];
$next_submission();
$phone_empty = $phone_form->submit(['skip_mail'=>true]);
$expect($phone_empty['status'] === 'validation_failed', 'Required phone rejects only the dial code');
$_POST = ['phone'=>'+44 7400 123456', 'phone__country'=>'GB'];
$next_submission();
$expect($phone_form->submit(['skip_mail'=>true])['status'] === 'mail_sent', 'Required phone accepts a full number');
$_POST = $posted_before;
$admin_id = get_current_user_id();
wp_set_current_user(0);
foreach (['/settings/features','/settings/webhook','/entries','/analytics/stats'] as $path) {
    $expect(in_array($request('GET',$path)->get_status(),[401,403],true), 'Anonymous access denied: '.$path);
}
wp_set_current_user($admin_id);
$fixture_page = wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'CF7 Mate browser fixture','post_content'=>'[contact-form-7 id="'.$form_id.'"]']);
$failed = array_values(array_filter($checks, static function($check) {return !$check['passed'];}));
return ['passed'=>count($checks)-count($failed),'failed'=>$failed,'checks'=>$checks,'form_id'=>$form_id,'page_url'=>get_permalink($fixture_page),'php'=>PHP_VERSION,'wp'=>$GLOBALS['wp_version']];
