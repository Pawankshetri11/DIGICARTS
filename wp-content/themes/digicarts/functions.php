<?php
defined('ABSPATH') || exit;
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('woocommerce', ['thumbnail_image_width'=>600,'single_image_width'=>1000,'product_grid'=>['default_columns'=>3,'default_rows'=>4]]);
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
});
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('digicarts', get_stylesheet_uri(), [], filemtime(get_template_directory().'/style.css'));
});
function dc_shop_url() { return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/'); }
function dc_icon($name) {
    $paths = [
        'arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'diagonal'=>'<path d="M6 18 18 6M6 6h12v12"/>',
        'bag'=>'<path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
        'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'download'=>'<path d="M12 3v12m-5-5 5 5 5-5M5 16v5h14v-5"/>',
        'spark'=>'<path d="m12 2 2.7 7.3L22 12l-7.3 2.7L12 22l-2.7-7.3L2 12l7.3-2.7L12 2Z"/>',
        'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    ];
    return '<svg class="dc-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['arrow']).'</svg>';
}
function dc_categories() {
    return [
        ['slug'=>'website-templates','title'=>'Website templates','caption'=>'A better starting point for the web.','style'=>'web','label'=>'BUILD SOMETHING'],
        ['slug'=>'design-assets','title'=>'Design assets','caption'=>'Small details. A big creative difference.','style'=>'design','label'=>'MAKE IT YOURS'],
        ['slug'=>'ebooks-guides','title'=>'E-books & guides','caption'=>'Fresh perspectives, one page at a time.','style'=>'book','label'=>'KEEP EXPLORING'],
        ['slug'=>'productivity','title'=>'Productivity','caption'=>'Make a little room for your best work.','style'=>'planner','label'=>'FIND YOUR FOCUS'],
    ];
}
function dc_category_url($slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if ($term) { $url=get_term_link($term); if (!is_wp_error($url)) return $url; }
    return add_query_arg('product_cat', $slug, dc_shop_url());
}
add_action('customize_register', function ($customizer) {
    $customizer->add_section('dc_home',['title'=>'DIGICARTS homepage','priority'=>30]);
    foreach (['dc_eyebrow'=>['Eyebrow','FOR THE IDEAS YOU CAN’T WAIT TO MAKE'],'dc_heading'=>['Hero heading','A head start for your next'],'dc_accent'=>['Highlighted heading','great thing.'],'dc_description'=>['Hero description','Templates, creative assets and a little inspiration. Find digital tools that help you turn “what if” into what’s next.']] as $key=>$setting) {
        $customizer->add_setting($key,['default'=>$setting[1],'sanitize_callback'=>'sanitize_text_field']);
        $customizer->add_control($key,['label'=>$setting[0],'section'=>'dc_home','type'=>$key==='dc_description'?'textarea':'text']);
    }
});
if (class_exists('WooCommerce')) {
    remove_action('woocommerce_before_main_content','woocommerce_output_content_wrapper',10);
    remove_action('woocommerce_after_main_content','woocommerce_output_content_wrapper_end',10);
    remove_action('woocommerce_sidebar','woocommerce_get_sidebar',10);
    add_action('woocommerce_before_main_content',function () { echo '<main id="main" class="container shop-main">'; },10);
    add_action('woocommerce_after_main_content',function () { echo '</main>'; },10);
    add_filter('loop_shop_columns',function () { return 3; });
    add_action('woocommerce_shop_loop_header',function () {
        echo '<p class="shop-intro">Good tools for your next big idea. Find something that makes it yours.</p>';
        get_template_part('template-parts/shop-filter');
    },20);
    add_action('woocommerce_single_product_summary',function () {
        global $product;
        if ($product && $product->is_downloadable()) echo '<div class="download-note">'.dc_icon('download').'<span><strong>Made to download.</strong> Access your files when your order is eligible for download.</span></div>';
    },35);
    remove_action('woocommerce_no_products_found','wc_no_products_found',10);
    add_action('woocommerce_no_products_found',function () {
        $search=get_search_query();
        echo '<div class="empty-collection">'.dc_icon('spark').'<h2>'.esc_html($search?'No matches this time.':'Good things are taking shape.').'</h2><p>'.esc_html($search?'Try another search or explore a different collection.':'Our digital collection is on its way. Come back soon for tools to make your next idea happen.').'</p><a class="dc-button" href="'.esc_url($search || is_product_category()?dc_shop_url():home_url('/#collections')).'">Explore collections '.dc_icon('arrow').'</a></div>';
    });
}
