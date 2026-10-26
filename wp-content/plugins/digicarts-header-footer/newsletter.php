<?php
defined('ABSPATH') || exit;
add_action('init',function () {
    register_post_type('dchf_subscriber',[
        'labels'=>['name'=>'Newsletter subscribers','singular_name'=>'Subscriber','menu_name'=>'Subscribers'],
        'public'=>false,'publicly_queryable'=>false,'exclude_from_search'=>true,'show_in_rest'=>false,
        'show_ui'=>false,'show_in_menu'=>false,'supports'=>['title'],'rewrite'=>false,
        'map_meta_cap'=>false,'capabilities'=>[
            'edit_post'=>'manage_options','read_post'=>'manage_options','delete_post'=>'manage_options',
            'edit_posts'=>'manage_options','edit_others_posts'=>'manage_options','publish_posts'=>'manage_options',
            'read_private_posts'=>'manage_options','delete_posts'=>'manage_options','delete_private_posts'=>'manage_options',
            'delete_published_posts'=>'manage_options','delete_others_posts'=>'manage_options',
            'edit_private_posts'=>'manage_options','edit_published_posts'=>'manage_options','create_posts'=>'do_not_allow',
        ],
    ]);
});
function dchf_newsletter_submit() {
    $options=dchf_options();
    $nonce=isset($_POST['dchf_nonce'])&&is_string($_POST['dchf_nonce'])?sanitize_text_field(wp_unslash($_POST['dchf_nonce'])):'';
    $back=wp_validate_redirect(wp_get_referer(),home_url('/'));
    $back=remove_query_arg('dchf_signup',$back);
    $redirect=function ($status) use ($back) { wp_safe_redirect(add_query_arg('dchf_signup',$status,preg_replace('/#.*$/','',$back)).'#dchf-newsletter-signup'); exit; };
    if (!$options['show_newsletter'] || $options['newsletter_shortcode'] || !wp_verify_nonce($nonce,'dchf_subscribe')) $redirect('error');
    if (!empty($_POST['dchf_website'])) $redirect('success');
    $email=isset($_POST['dchf_email'])&&is_string($_POST['dchf_email'])?sanitize_email(wp_unslash($_POST['dchf_email'])):'';
    if (!is_email($email) || empty($_POST['dchf_consent'])) $redirect('invalid');
    $hash=hash('sha256',strtolower($email));
    $existing=get_posts(['post_type'=>'dchf_subscriber','post_status'=>'private','name'=>'subscriber-'.$hash,'numberposts'=>1,'fields'=>'ids']);
    if ($existing) $redirect('success');
    $rate_key='dchf_signup_'.hash_hmac('sha256',sanitize_text_field($_SERVER['REMOTE_ADDR']??''),wp_salt('nonce'));
    if (get_transient($rate_key)) $redirect('wait');
    set_transient($rate_key,1,MINUTE_IN_SECONDS);
    $id=wp_insert_post(['post_type'=>'dchf_subscriber','post_status'=>'private','post_title'=>$email,'post_name'=>'subscriber-'.$hash,'meta_input'=>['dchf_consent_at'=>current_time('mysql',true)]],true);
    $redirect(is_wp_error($id)?'error':'success');
}
add_action('admin_post_nopriv_dchf_subscribe','dchf_newsletter_submit');
add_action('admin_post_dchf_subscribe','dchf_newsletter_submit');
