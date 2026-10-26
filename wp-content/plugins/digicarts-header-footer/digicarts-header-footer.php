<?php
/**
 * Plugin Name: DIGICARTS Header & Footer
 * Description: Customizable storefront header and footer with AJAX search, a side cart drawer and a streamlined admin editor.
 * Version: 1.9.2
 * Author: DIGICARTS
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Text Domain: digicarts-header-footer
 * License: GPL-2.0-or-later
 */
defined('ABSPATH') || exit;
define('DCHF_PATH', plugin_dir_path(__FILE__));
define('DCHF_URL', plugin_dir_url(__FILE__));
function dchf_defaults() {
    return [
        'brand'=>get_bloginfo('name'),'logo_id'=>0,'logo_width'=>170,
        'accent'=>'#7546e8','text_color'=>'#24272e','muted_color'=>'#6b7079','background'=>'#ffffff','border_color'=>'#e5e7eb','max_width'=>1240,
        'show_topbar'=>0,'announcement'=>'Watch this space for our next offer.','announcement_url'=>'','language_label'=>'English','show_currency'=>1,
        'search_placeholder'=>'Find your next creative tool','search_button'=>'Search','category_label'=>'All categories','browse_label'=>'Explore categories',
        'account_label'=>'Account','cart_label'=>'Your cart','show_wishlist'=>0,'wishlist_url'=>'','promo_text'=>'10% OFF • Use code SAVE10','promo_url'=>'',
        'show_benefits'=>0,
        'benefit_1_icon'=>'download','benefit_1_title'=>'Made to download','benefit_1_text'=>'Files for your next project',
        'benefit_2_icon'=>'wallet','benefit_2_title'=>'Simple checkout','benefit_2_text'=>'All your finds in one order',
        'benefit_3_icon'=>'tag','benefit_3_title'=>'Know your license','benefit_3_text'=>'Usage details on every product',
        'benefit_4_icon'=>'chat','benefit_4_title'=>'Your own library','benefit_4_text'=>'Eligible files in your account',
        'benefit_5_icon'=>'grid','benefit_5_title'=>'Room to create','benefit_5_text'=>'A little head start for your idea',
        'about_title'=>'Good tools. Great ideas.','contact_intro'=>'','phone'=>'','email'=>'','address'=>'',
        'about_description'=>'Digital resources for your next big idea. Find a starting point, make it yours, and create something good.','info_title'=>'Policies','quick_title'=>'Your space','collections_title'=>'Explore collections',
        'show_newsletter'=>0,'newsletter_title'=>'A little creative inspiration.','newsletter_description'=>'Fresh finds and new ideas, straight to your inbox.',
        'newsletter_placeholder'=>'Your email address','newsletter_button'=>'Join the list','newsletter_note'=>'Product news, whenever there is something worth sharing.','newsletter_shortcode'=>'',
        'copyright'=>'© {year} {site}. All rights reserved.','payment_image_id'=>0,'payment_label'=>'Payment methods:','show_back_top'=>1,
        'show_socials'=>1,'instagram_url'=>'','facebook_url'=>'','youtube_url'=>'','x_url'=>'',
        'show_checkout'=>1,'checkout_title'=>'Simple checkout',
    ];
}
function dchf_options() { return wp_parse_args((array)get_option('dchf_settings',[]),dchf_defaults()); }
function dchf_shop_url() { return function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/'); }
function dchf_account_url() { return function_exists('wc_get_page_permalink')?wc_get_page_permalink('myaccount'):wp_login_url(); }
function dchf_downloads_url() { return function_exists('wc_get_account_endpoint_url')?wc_get_account_endpoint_url('downloads'):dchf_account_url(); }
function dchf_upgrade_footer_menus() {
    if (get_option('dchf_footer_three_menus')) return;
    $locations=get_nav_menu_locations();
    $options=dchf_options();
    add_option('dchf_footer_before_three_menus',['locations'=>$locations,'info_title'=>$options['info_title']],'',false);
    $policy_menu=absint($locations['footer'] ?? 0) ?: absint($locations['dchf_policies'] ?? 0);
    if ($policy_menu) $locations['dchf_information']=$policy_menu;
    unset($locations['footer'],$locations['dchf_policies']);
    set_theme_mod('nav_menu_locations',$locations);
    $options['info_title']='Policies';
    update_option('dchf_settings',$options);
    update_option('dchf_footer_three_menus',1,false);
}
add_action('after_setup_theme',function () {
    add_theme_support('custom-logo',['height'=>70,'width'=>240,'flex-width'=>true,'flex-height'=>true]);
    register_nav_menus(['primary'=>'DIGICARTS main navigation','dchf_utility'=>'DIGICARTS top-bar links','dchf_collections'=>'DIGICARTS footer collections','dchf_information'=>'DIGICARTS footer policies','dchf_quick'=>'DIGICARTS footer Your space']);
    dchf_upgrade_footer_menus();
});
add_action('wp_enqueue_scripts',function () {
    $options=dchf_options();
    wp_enqueue_style('digicarts-header-footer',DCHF_URL.'assets/header-footer.css',[],filemtime(DCHF_PATH.'assets/header-footer.css'));
    $css='.dchf-shell{';
    foreach (['accent','text_color','muted_color','background','border_color'] as $color) $css.='--dchf-'.str_replace('_','-',$color).':'.(sanitize_hex_color($options[$color])?:dchf_defaults()[$color]).';';
    $css.='--dchf-max-width:'.absint($options['max_width']).'px;--dchf-logo-width:'.absint($options['logo_width']).'px;}';
    wp_add_inline_style('digicarts-header-footer',$css);
    wp_enqueue_script('digicarts-header-footer',DCHF_URL.'assets/header-footer.js',['wp-data'],filemtime(DCHF_PATH.'assets/header-footer.js'),true);
    wp_localize_script('digicarts-header-footer','dchfStorefront',['searchUrl'=>esc_url_raw(rest_url('digicarts/v1/search'))]);
    wp_enqueue_script('digicarts-storefront',DCHF_URL.'assets/storefront.js',['digicarts-header-footer'],filemtime(DCHF_PATH.'assets/storefront.js'),true);
    if (class_exists('WooCommerce')) wp_enqueue_script('wc-cart-fragments');
});
require_once DCHF_PATH.'frontend.php';
require_once DCHF_PATH.'admin.php';
require_once DCHF_PATH.'newsletter.php';
require_once DCHF_PATH.'storefront.php';
require_once DCHF_PATH.'cart-drawer.php';
function dchf_render_header() { $options=dchf_options(); require DCHF_PATH.'templates/header.php'; }
function dchf_render_footer() { $options=dchf_options(); require DCHF_PATH.'templates/footer.php'; }
add_action('digicarts_header','dchf_render_header');
add_action('digicarts_footer','dchf_render_footer');
add_shortcode('digicarts_header',function () { ob_start(); dchf_render_header(); return ob_get_clean(); });
add_shortcode('digicarts_footer',function () { ob_start(); dchf_render_footer(); return ob_get_clean(); });
add_filter('plugin_action_links_'.plugin_basename(__FILE__),function ($links) {
    array_unshift($links,'<a href="'.esc_url(admin_url('admin.php?page=digicarts-header-footer')).'">Settings</a>'); return $links;
});
