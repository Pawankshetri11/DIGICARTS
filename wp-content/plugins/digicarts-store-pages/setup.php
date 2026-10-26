<?php
defined('ABSPATH') || exit;
function dsp_remove_collections_page() {
    $page=get_page_by_path('collections');
    $target_path=untrailingslashit(wp_parse_url(home_url('/collections/'),PHP_URL_PATH));
    $removed=[];
    foreach (wp_get_nav_menus() as $menu) foreach (wp_get_nav_menu_items($menu->term_id) ?: [] as $item) {
        $path=untrailingslashit((string)wp_parse_url($item->url,PHP_URL_PATH));
        $local=wp_parse_url($item->url,PHP_URL_HOST)===wp_parse_url(home_url('/'),PHP_URL_HOST);
        if (($page && $item->object==='page' && (int)$item->object_id===(int)$page->ID) || ($local && ($path===$target_path || wp_parse_url($item->url,PHP_URL_FRAGMENT)==='collections'))) {
            $removed[]=$item;
            wp_delete_post($item->ID,true);
        }
    }
    if ($removed) add_option('dsp_removed_collection_links',$removed,'',false);
    if ($page && $page->post_status!=='trash') wp_trash_post($page->ID);
}
function dsp_activate() {
    dsp_remove_collections_page();
    $pages=[];
    foreach (['about-us'=>['About Us','about'],'contact-us'=>['Contact Us','contact']] as $slug=>$config) {
        $page=get_page_by_path($slug);
        $content='<!-- wp:shortcode -->[digicarts_'.$config[1].']<!-- /wp:shortcode -->';
        if ($page) {
            add_post_meta($page->ID,'_dsp_original_content',wp_slash($page->post_content),true);
            $id=wp_update_post(['ID'=>$page->ID,'post_status'=>'publish','post_content'=>$content],true);
        } else $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$config[0],'post_name'=>$slug,'post_content'=>$content],true);
        if (is_wp_error($id)) wp_die(esc_html($id->get_error_message()));
        $pages[$slug]=$id;
    }
    $snapshot=[];
    foreach (wp_get_nav_menus() as $menu) $snapshot[$menu->term_id]=wp_get_nav_menu_items($menu->term_id) ?: [];
    add_option('dsp_original_navigation',$snapshot,'',false);
    foreach ($snapshot as $menu_id=>$items) foreach ($items as $item) {
        $title=strtolower(trim(wp_strip_all_tags($item->title)));
        $fragment=wp_parse_url($item->url,PHP_URL_FRAGMENT);
        if ($title==='how it works' || $fragment==='how-it-works') { wp_delete_post($item->ID,true); continue; }
        $url=null;
        if ($fragment==='faq') $url=get_permalink($pages['contact-us']).'#questions';
        if ($url) update_post_meta($item->ID,'_menu_item_url',esc_url_raw($url));
    }
    $old_page=get_page_by_path('how-it-works');
    if ($old_page && $old_page->post_status!=='trash') wp_trash_post($old_page->ID);
    $locations=get_nav_menu_locations();
    $quick=absint($locations['dchf_quick']??0);
    if (!$quick) {
        $existing=wp_get_nav_menu_object('DIGICARTS Quick links');
        $quick=$existing?$existing->term_id:wp_create_nav_menu('DIGICARTS Quick links');
        if (is_wp_error($quick)) wp_die(esc_html($quick->get_error_message()));
        $locations['dchf_quick']=$quick; set_theme_mod('nav_menu_locations',$locations);
    }
    $items=wp_get_nav_menu_items($quick) ?: [];
    foreach (['about-us'=>'About Us','contact-us'=>'Contact Us'] as $slug=>$label) {
        $found=0;
        foreach ($items as $item) if ((int)$item->object_id===$pages[$slug] || strtolower(trim($item->title))===strtolower($label)) { $found=$item->ID; break; }
        wp_update_nav_menu_item($quick,$found,['menu-item-title'=>$label,'menu-item-object'=>'page','menu-item-object-id'=>$pages[$slug],'menu-item-type'=>'post_type','menu-item-status'=>'publish']);
    }
    dsp_publish_policies();
    // Include the new pages in this activation's routing setup.
    flush_rewrite_rules(false);
}
