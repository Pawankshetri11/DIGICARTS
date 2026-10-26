<?php
/**
 * Plugin Name: DIGICARTS Customer Experience
 * Description: Simple digital checkout and a customer dashboard for orders and downloads.
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 */
defined('ABSPATH') || exit;
define('DCE_PATH', plugin_dir_path(__FILE__));

function dce_activate() {
    if (!function_exists('wc_get_page_id')) return;
    $settings = [
        'woocommerce_enable_guest_checkout'=>'yes',
        'woocommerce_enable_signup_and_login_from_checkout'=>'yes',
        'woocommerce_enable_checkout_login_reminder'=>'yes',
        'woocommerce_registration_generate_username'=>'yes',
        'woocommerce_registration_generate_password'=>'no',
        'woocommerce_enable_myaccount_registration'=>'yes',
        'woocommerce_downloads_require_login'=>'no',
    ];
    $previous=[];
    foreach ($settings as $key=>$value) $previous[$key]=get_option($key);
    add_option('dce_previous_setup',$previous,'',false);
    $page_id=wc_get_page_id('checkout');
    $page=$page_id>0 ? get_post($page_id) : null;
    if ($page && has_block('woocommerce/checkout',$page->post_content)) {
        add_post_meta($page_id,'_dce_original_checkout_content',wp_slash($page->post_content),true);
        $replace=function ($blocks) use (&$replace) {
            foreach ($blocks as &$block) {
                if ($block['blockName']==='woocommerce/checkout') {
                    $block=['blockName'=>'core/shortcode','attrs'=>[],'innerBlocks'=>[],'innerHTML'=>'[woocommerce_checkout]','innerContent'=>['[woocommerce_checkout]']];
                } elseif (!empty($block['innerBlocks'])) $block['innerBlocks']=$replace($block['innerBlocks']);
            }
            unset($block);
            return $blocks;
        };
        $result=wp_update_post(['ID'=>$page_id,'post_content'=>wp_slash(serialize_blocks($replace(parse_blocks($page->post_content))))],true);
        if (is_wp_error($result)) wp_die(esc_html($result->get_error_message()));
    }
    foreach ($settings as $key=>$value) update_option($key,$value);
}
register_activation_hook(__FILE__,'dce_activate');

function dce_checkout_request() {
    return (function_exists('is_checkout') && is_checkout()) || (isset($_GET['wc-ajax']) && is_string($_GET['wc-ajax']) && in_array(sanitize_key(wp_unslash($_GET['wc-ajax'])), ['checkout','update_order_review'], true));
}

function dce_optional_address_fields($fields) {
    if (!dce_checkout_request()) return $fields;
    foreach ($fields as $key=>&$field) {
        $field['required'] = $key === 'first_name';
        if ($key === 'first_name') $field['label'] = 'Name';
    }
    unset($field);
    return $fields;
}
add_filter('woocommerce_default_address_fields','dce_optional_address_fields',100);
add_filter('woocommerce_get_country_locale_default','dce_optional_address_fields',100);
add_filter('woocommerce_get_country_locale_base','dce_optional_address_fields',100);
add_filter('woocommerce_get_country_locale',function ($locale) {
    if (!dce_checkout_request()) return $locale;
    foreach ($locale as &$country) {
        foreach (['last_name','company','country','address_1','address_2','city','state','postcode'] as $field) $country[$field]['required'] = false;
        $country['first_name']['required'] = true;
        $country['first_name']['label'] = 'Name';
    }
    unset($country);
    return $locale;
},100);

add_filter('woocommerce_checkout_fields',function ($fields) {
    $required = ['billing_first_name','billing_email','billing_phone'];
    foreach (['billing','shipping','order'] as $group) {
        if (empty($fields[$group])) continue;
        foreach ($fields[$group] as $key=>&$field) $field['required'] = in_array($key,$required,true);
        unset($field);
    }
    $fields['billing']['billing_first_name'] = array_merge($fields['billing']['billing_first_name'] ?? [],[
        'type'=>'text','label'=>'Name','required'=>true,'priority'=>10,'class'=>['form-row-wide'],'autocomplete'=>'name',
    ]);
    $fields['billing']['billing_email'] = array_merge($fields['billing']['billing_email'] ?? [],[
        'type'=>'email','label'=>'Email address','required'=>true,'priority'=>20,'class'=>['form-row-first'],'validate'=>['email'],'autocomplete'=>'email',
    ]);
    $fields['billing']['billing_phone'] = array_merge($fields['billing']['billing_phone'] ?? [],[
        'type'=>'tel','label'=>'Phone number','required'=>true,'priority'=>30,'class'=>['form-row-last'],'validate'=>['phone'],'autocomplete'=>'tel',
    ]);
    foreach (['billing_last_name','billing_company','billing_country','billing_address_1','billing_address_2','billing_city','billing_state','billing_postcode'] as $index=>$key) {
        if (isset($fields['billing'][$key])) $fields['billing'][$key]['priority'] = 50 + $index * 10;
    }
    if (!is_user_logged_in()) {
        unset($fields['account']['account_username']);
        $fields['account']['account_password'] = [
            'type'=>'password','label'=>'Create a password','required'=>true,'class'=>['form-row-wide'],
            'autocomplete'=>'new-password','priority'=>10,
            'description'=>'Use this password to sign in and access your orders and downloads.',
        ];
    }
    return $fields;
},100);
add_filter('woocommerce_checkout_registration_enabled','__return_true',100);
add_filter('woocommerce_checkout_registration_required','__return_false',100);
add_filter('woocommerce_create_account_default_checked','__return_false',100);

add_filter('woocommerce_locate_template',function ($template,$name) {
    $overrides = ['checkout/form-checkout.php','checkout/form-billing.php','myaccount/my-account.php','myaccount/navigation.php','myaccount/dashboard.php','myaccount/form-login.php','myaccount/form-lost-password.php','myaccount/form-reset-password.php','myaccount/lost-password-confirmation.php'];
    return in_array($name,$overrides,true) ? DCE_PATH.'templates/'.$name : $template;
},30,2);

add_filter('body_class',function ($classes) {
    if (function_exists('is_account_page') && is_account_page()) $classes[] = is_user_logged_in() ? 'dce-account' : 'dce-auth';
    if (function_exists('is_account_page') && is_account_page() && !is_user_logged_in() && !is_wc_endpoint_url('lost-password')) $classes[]='dce-login-page';
    if (function_exists('is_account_page') && is_account_page() && is_wc_endpoint_url('lost-password')) {
        $classes[]='dce-password-page';
        if (!in_array('dce-auth',$classes,true)) $classes[]='dce-auth';
    }
    if (function_exists('is_checkout') && is_checkout()) $classes[] = 'dce-checkout';
    return $classes;
});
add_action('wp_enqueue_scripts',function () {
    if (!function_exists('is_account_page') || (!is_account_page() && !is_checkout())) return;
    wp_enqueue_style('dce-customer',plugin_dir_url(__FILE__).'assets/customer.css',[],filemtime(DCE_PATH.'assets/customer.css'));
    $accent = function_exists('dchf_options') ? sanitize_hex_color(dchf_options()['accent']) : '#7546e8';
    wp_add_inline_style('dce-customer','body{--dce-accent:'.($accent ?: '#7546e8').';}');
    if (is_checkout()) wp_enqueue_script('dce-checkout',plugin_dir_url(__FILE__).'assets/checkout.js',['jquery','wc-checkout'],filemtime(DCE_PATH.'assets/checkout.js'),true);
    if (is_account_page() && !is_user_logged_in()) wp_enqueue_script('dce-auth',plugin_dir_url(__FILE__).'assets/auth.js',[],filemtime(DCE_PATH.'assets/auth.js'),true);
},40);

function dce_icon($name) {
    $icons = [
        'dashboard'=>'<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        'orders'=>'<path d="M6 3h12v18l-3-2-3 2-3-2-3 2Z"/><path d="M9 8h6M9 12h6"/>',
        'downloads'=>'<path d="M12 3v12m-4-4 4 4 4-4M4 16v4h16v-4"/>',
        'edit-account'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'edit-address'=>'<path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'payment-methods'=>'<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18M7 15h3"/>',
        'customer-logout'=>'<path d="M9 4H4v16h5M9 12h12m-4-4 4 4-4 4"/>',
        'arrow'=>'<path d="M5 12h14m-5-5 5 5-5 5"/>',
        'lock'=>'<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
    ];
    return '<svg class="dce-icon" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$name] ?? $icons['arrow']).'</svg>';
}

add_filter('woocommerce_account_menu_items',function ($items) {
    $labels = ['dashboard'=>'Overview','orders'=>'My orders','downloads'=>'My downloads','edit-account'=>'Account details','edit-address'=>'Saved addresses'];
    foreach ($labels as $key=>$label) if (isset($items[$key])) $items[$key] = $label;
    $ordered=[];
    foreach (['dashboard','downloads','orders','edit-account','edit-address','payment-methods'] as $key) if (isset($items[$key])) { $ordered[$key]=$items[$key]; unset($items[$key]); }
    return $ordered + $items;
},20);
