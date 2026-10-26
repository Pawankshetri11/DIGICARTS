<?php
defined('ABSPATH') || exit;
function dsp_policy_pages() {
    return ['dmca'=>['DMCA & Copyright Policy','DMCA'],'privacy'=>['Privacy Policy','Privacy Policy'],'terms-and-conditions'=>['Terms & Conditions','Terms & Conditions'],'refund-policy'=>['Refund Policy','Refund Policy']];
}
function dsp_is_policy_page() { return is_page(array_keys(dsp_policy_pages())); }
add_filter('body_class',function ($classes) { if (dsp_is_policy_page()) $classes[]='dsp-policy-page'; return $classes; });
add_filter('the_content',function ($content) {
    if (!dsp_is_policy_page() || !is_main_query() || !in_the_loop()) return $content;
    return '<div class="dsp-root dsp-policy"><div class="dsp-policy-content">'.$content.'</div></div>';
},20);

/** Seed editable WordPress pages once; later admin edits remain authoritative. */
function dsp_publish_policies() {
    if (get_option('dsp_policies_installed')) return;
    $ids=[];
    $tokens=[
        '{{contact}}'=>esc_url(dsp_page_url('contact-us')),
        '{{privacy}}'=>esc_url(dsp_page_url('privacy')),
        '{{terms}}'=>esc_url(dsp_page_url('terms-and-conditions')),
        '{{refund}}'=>esc_url(dsp_page_url('refund-policy')),
        '{{dmca}}'=>esc_url(home_url('/dmca/')),
    ];
    foreach (dsp_policy_pages() as $slug=>$labels) {
        $page=get_page_by_path($slug);
        $source=file_get_contents(DSP_PATH.'content/'.$slug.'.html');
        $content=strtr($source,$tokens);
        $post=['post_type'=>'page','post_title'=>$labels[0],'post_name'=>$slug,'post_status'=>'publish','post_content'=>wp_slash($content)];
        if ($page) {
            add_post_meta($page->ID,'_dsp_policy_original_content',wp_slash($page->post_content),true);
            $post['ID']=$page->ID;
        }
        $id=wp_insert_post($post,true);
        if (is_wp_error($id)) wp_die(esc_html($id->get_error_message()));
        $ids[$slug]=$id;
    }
    update_option('wp_page_for_privacy_policy',$ids['privacy']);
    $locations=get_nav_menu_locations();
    $menu_id=absint($locations['dchf_information']??0);
    if (!$menu_id || !wp_get_nav_menu_object($menu_id)) {
        $existing=wp_get_nav_menu_object('DIGICARTS Policies');
        $menu_id=$existing?$existing->term_id:wp_create_nav_menu('DIGICARTS Policies');
        if (is_wp_error($menu_id)) wp_die(esc_html($menu_id->get_error_message()));
        $locations['dchf_information']=$menu_id;
        set_theme_mod('nav_menu_locations',$locations);
    }
    $items=wp_get_nav_menu_items($menu_id) ?: [];
    $used=[]; $position=1;
    foreach (dsp_policy_pages() as $slug=>$labels) {
        $item_id=0;
        foreach ($items as $item) {
            if (($item->object==='page' && (int)$item->object_id===$ids[$slug]) || untrailingslashit($item->url)===untrailingslashit(get_permalink($ids[$slug]))) { $item_id=$item->ID; break; }
        }
        $result=wp_update_nav_menu_item($menu_id,$item_id,[
            'menu-item-title'=>$labels[1],'menu-item-object'=>'page','menu-item-object-id'=>$ids[$slug],
            'menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-position'=>$position++,'menu-item-parent-id'=>0,
        ]);
        if (is_wp_error($result)) wp_die(esc_html($result->get_error_message()));
        $used[]=$result;
    }
    foreach ($items as $item) if (!in_array($item->ID,$used,true)) wp_update_post(['ID'=>$item->ID,'menu_order'=>$position++]);
    update_option('dsp_policies_installed',1,false);
}
