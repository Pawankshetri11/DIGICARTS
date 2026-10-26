<?php
defined('ABSPATH') || exit;

function dsp_home_text($key,$default) {
    return get_theme_mod($key,$default);
}

add_action('customize_register',function ($customizer) {
    if (!$customizer->get_section('dc_home')) {
        $customizer->add_section('dc_home',['title'=>'DIGICARTS homepage','priority'=>30]);
    }
    foreach ([
        'dsp_home_shop_label'=>['Shop button','Explore the shop'],
        'dsp_home_categories_title'=>['Categories heading','What will you make next?'],
        'dsp_home_products_title'=>['Products heading','Fresh finds for your next idea.'],
        'dsp_home_story_title'=>['About section heading','A little less starting from scratch.'],
        'dsp_home_story_text'=>['About section description','A new website. A more focused week. An idea that finally takes shape. DigiCarts brings digital resources together so you can find your starting point and make it your own.'],
        'dsp_home_cta_title'=>['Final section heading','Your next good idea starts here.'],
        'dsp_home_cta_text'=>['Final section description','Find a resource that fits your project. Give it your own spin.'],
    ] as $key=>$field) {
        $customizer->add_setting($key,['default'=>$field[1],'sanitize_callback'=>'sanitize_text_field']);
        $customizer->add_control($key,['label'=>$field[0],'section'=>'dc_home','type'=>str_ends_with($key,'_text')?'textarea':'text']);
    }
},20);
