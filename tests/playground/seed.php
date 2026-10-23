<?php
/** Called only by the local Playground Blueprint. */
require '/wordpress/wp-load.php';
if (!defined('CF7M_PLAYGROUND') || !CF7M_PLAYGROUND) { throw new RuntimeException('Playground fixtures are not enabled.'); }
$forms = [
    'contact' => ['title'=>'Playground: Contact', 'markup'=>'<label>Your name [text* your-name]</label><label>Your email [email* your-email]</label><label>Message [textarea your-message]</label>[submit "Send message"]'],
    'wizard' => ['title'=>'Playground: Multi-step and conditional', 'markup'=>'[cf7m-presets style="minimal"][cf7m-step title="About you"]<label>Your name [text* your-name]</label><label>Your email [email* your-email]</label><label>Reason [select reason "General" "Support"]</label>[/cf7m-step][cf7m-step title="Details"][cf7m-if field:reason is:Support]<label>Support details [textarea* support-details]</label>[/cf7m-if]<label>Additional notes [textarea notes]</label>[submit "Send request"][/cf7m-step][/cf7m-presets]'],
    'resume' => ['title'=>'Playground: Save and continue', 'markup'=>'<label>Your name [text* your-name]</label><label>Your email [email* your-email]</label><label>Project details [textarea project-details]</label><label>Reason [select reason "General" "Support"]</label>[cf7m-if field:reason is:Support]<label>Support details [textarea support-details]</label>[/cf7m-if]<label>Interests [select interests multiple "Design" "Development" "Support"]</label>[checkbox updates default:1 "Send updates"][submit "Submit project"]'],
    'closed' => ['title'=>'Playground: Closed registration', 'markup'=>'<label>Your name [text* your-name]</label>[submit "Register"]'],
];
$fixtures=[];
foreach ($forms as $key=>$config) {
    $form=WPCF7_ContactForm::get_template(); $form->set_title($config['title']); $form->set_properties(['form'=>$config['markup']]); $id=$form->save();
    if ($key==='resume') { update_post_meta($id,'_cf7m_partial_save_enabled','1'); }
    if ($key==='closed') {
        update_post_meta($id,'_cf7m_schedule_enabled','1'); update_post_meta($id,'_cf7m_schedule_end','2000-01-01 00:00:00');
        update_post_meta($id,'_cf7m_schedule_msg','Registration is closed. Please try our contact form.');
    }
    $page=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$config['title'],'post_content'=>'[contact-form-7 id="'.$id.'"]']);
    $fixtures[$key]=['form_id'=>$id,'page_id'=>$page,'url'=>get_permalink($page)];
}
update_option('cf7m_playground_fixtures',$fixtures,false);
