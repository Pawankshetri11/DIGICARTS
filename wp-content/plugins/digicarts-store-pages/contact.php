<?php
defined('ABSPATH') || exit;
function dsp_contact_topics() { return ['product'=>'A product question','order'=>'Help with an order','download'=>'Download support','license'=>'Licensing & usage','refund'=>'Refund request','copyright'=>'Copyright / DMCA report','privacy'=>'Privacy request','other'=>'Something else']; }
add_action('init',function () {
    register_post_type('dsp_enquiry',[
        'labels'=>['name'=>'Store enquiries','singular_name'=>'Enquiry','menu_name'=>'Enquiries'],
        'public'=>false,'publicly_queryable'=>false,'exclude_from_search'=>true,'show_in_rest'=>false,
        'show_ui'=>true,'show_in_menu'=>'woocommerce','supports'=>['title','editor'],'rewrite'=>false,
        'map_meta_cap'=>false,'capabilities'=>[
            'edit_post'=>'manage_woocommerce','read_post'=>'manage_woocommerce','delete_post'=>'manage_woocommerce',
            'edit_posts'=>'manage_woocommerce','edit_others_posts'=>'manage_woocommerce','publish_posts'=>'manage_woocommerce',
            'read_private_posts'=>'manage_woocommerce','delete_posts'=>'manage_woocommerce','delete_private_posts'=>'manage_woocommerce',
            'delete_published_posts'=>'manage_woocommerce','delete_others_posts'=>'manage_woocommerce',
            'edit_private_posts'=>'manage_woocommerce','edit_published_posts'=>'manage_woocommerce','create_posts'=>'do_not_allow',
        ],
    ]);
});
add_action('add_meta_boxes_dsp_enquiry',function () {
    add_meta_box('dsp-enquiry-details','Customer details',function ($post) {
        foreach (['_dsp_email'=>'Email','_dsp_topic'=>'Topic','_dsp_order_reference'=>'Order reference'] as $key=>$label) {
            $value=get_post_meta($post->ID,$key,true);
            echo '<p><strong>'.esc_html($label).':</strong> '.esc_html($value ?: 'Not provided').'</p>';
        }
    },'dsp_enquiry','side');
});
add_filter('manage_dsp_enquiry_posts_columns',function ($columns) { return ['cb'=>$columns['cb'],'title'=>'Customer / topic','dsp_email'=>'Email','date'=>'Received']; });
add_action('manage_dsp_enquiry_posts_custom_column',function ($column,$post_id) { if ($column==='dsp_email') echo esc_html(get_post_meta($post_id,'_dsp_email',true)); },10,2);
add_action('template_redirect',function () { if (is_page('contact-us')) { if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE',true); nocache_headers(); } });
function dsp_submit_contact() {
    $redirect=static function ($status) { wp_safe_redirect(add_query_arg('message',$status,dsp_page_url('contact-us')).'#contact-form'); exit; };
    $read=static fn($key)=>isset($_POST[$key]) && is_string($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
    if (!wp_verify_nonce(sanitize_text_field($read('dsp_nonce')),'dsp_contact')) $redirect('expired');
    if ($read('company_website')!=='') $redirect('received');
    $name=sanitize_text_field($read('contact_name')); $email=sanitize_email($read('contact_email'));
    $topic=sanitize_key($read('contact_topic')); $message=sanitize_textarea_field($read('contact_message'));
    $reference=sanitize_text_field($read('order_reference')); $topics=dsp_contact_topics();
    if (!$name || mb_strlen($name)>120 || !is_email($email) || !isset($topics[$topic]) || mb_strlen($message)<10 || mb_strlen($message)>5000 || mb_strlen($reference)>80) $redirect('invalid');
    $rate='dsp_contact_'.hash_hmac('sha256',sanitize_text_field($_SERVER['REMOTE_ADDR']??''),wp_salt('nonce'));
    if (get_transient($rate)) $redirect('wait');
    set_transient($rate,1,MINUTE_IN_SECONDS);
    $id=wp_insert_post(wp_slash([
        'post_type'=>'dsp_enquiry','post_status'=>'private','post_title'=>$name.' — '.$topics[$topic],
        'post_content'=>$message,'meta_input'=>['_dsp_email'=>$email,'_dsp_topic'=>$topics[$topic],'_dsp_order_reference'=>$reference],
    ]),true);
    if (is_wp_error($id) || !$id) { delete_transient($rate); $redirect('error'); }
    $redirect('received');
}
add_action('admin_post_dsp_contact','dsp_submit_contact');
add_action('admin_post_nopriv_dsp_contact','dsp_submit_contact');
