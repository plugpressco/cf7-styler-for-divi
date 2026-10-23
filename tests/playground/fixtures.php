<?php
/** Local Playground fixture. Never installed in a customer site or release ZIP. */
if (!defined('CF7M_PLAYGROUND') || !CF7M_PLAYGROUND) { return; }

add_filter('wpcf7_skip_mail', '__return_true');
add_filter('wpcf7_spam', '__return_false', 999);
add_filter('pre_wp_mail', static function () { return true; });
add_filter('pre_http_request', static function ($pre, $args, $url) {
    $host = wp_parse_url($url, PHP_URL_HOST);
    $ai_hosts = ['api.openai.com', 'api.anthropic.com', 'generativelanguage.googleapis.com', 'openrouter.ai'];
    if (!in_array($host, $ai_hosts, true) && $url !== 'https://example.com/cf7mate-playground') { return $pre; }
    $log = get_option('cf7m_playground_requests', []);
    $log[] = ['host'=>$host, 'kind'=>in_array($host,$ai_hosts,true)?'ai':'webhook', 'time'=>time()];
    update_option('cf7m_playground_requests', array_slice($log,-20), false);
    $text = '<label>Name [text* your-name]</label><label>Email [email* your-email]</label><label>Message [textarea your-message]</label>[submit "Send"]';
    if (strpos((string)($args['body']??''), 'one word') !== false) { $text = 'OK'; }
    $data = ['choices'=>[['message'=>['content'=>$text]]], 'content'=>[['type'=>'text','text'=>$text]], 'candidates'=>[['content'=>['parts'=>[['text'=>$text]]]]]];
    return ['headers'=>[],'body'=>wp_json_encode($data),'response'=>['code'=>200,'message'=>'OK'],'cookies'=>[]];
}, 10, 3);

add_action('rest_api_init', static function () {
    register_rest_route('cf7mate-playground/v1','/state',[
        'methods'=>'GET',
        'permission_callback'=>static function () { return current_user_can('manage_options'); },
        'callback'=>static function () {
            global $wp_version;
            return ['php'=>PHP_VERSION,'wordpress'=>$wp_version,'fixtures'=>get_option('cf7m_playground_fixtures',[]),
                'features'=>\CF7_Mate\Feature_Settings::get(), 'onboarding_completed'=>get_option('cf7m_onboarding_completed'),
                'outbound_sink'=>get_option('cf7m_playground_requests',[]), 'white_label'=>get_option('cf7m_white_label', [])];
        }
    ]);
});
