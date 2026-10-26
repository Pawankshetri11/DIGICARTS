<?php
/**
 * Plugin Name: DIGICARTS Store Pages
 * Description: Shop, about and contact pages for the digital store.
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 */
defined('ABSPATH') || exit;
define('DSP_PATH',plugin_dir_path(__FILE__));
function dsp_catalog() { return function_exists('is_shop') && (is_shop() || is_product_taxonomy() || (is_search() && get_query_var('post_type')==='product')); }
function dsp_page_url($slug) { $page=get_page_by_path($slug); return $page ? get_permalink($page) : home_url('/'.$slug.'/'); }
function dsp_categories() {
    $terms=get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>0]);
    if (is_wp_error($terms)) return [];
    $default=(int)get_option('default_product_cat');
    return array_values(array_filter($terms,static fn($term)=>(int)$term->term_id!==$default));
}
function dsp_icon($name) {
    if (function_exists('dchf_icon')) return dchf_icon($name);
    return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>';
}
function dsp_art($slug) {
    $types=['website-templates'=>'web','design-assets'=>'design','ebooks-guides'=>'book','productivity'=>'planner'];
    $type=$types[$slug] ?? 'design';
    include DSP_PATH.'templates/art.php';
}
function dsp_product_thumbnail() {
    global $product;
    $art=$product ? $product->get_meta('_dsp_demo_art') : '';
    if ($art && !$product->get_image_id()) {
        echo '<div class="dsp-demo-thumbnail" aria-hidden="true">';
        dsp_art($art);
        echo '<span class="dsp-demo-label">Demo product</span></div>';
    } else {
        woocommerce_template_loop_product_thumbnail();
    }
}
function dsp_catalog_pagination() {
    $pages=max(1,(int)wc_get_loop_prop('total_pages'));
    $current=max(1,(int)wc_get_loop_prop('current_page'));
    echo '<div class="dsp-pagination"><span class="dsp-page-count">'.esc_html(sprintf('Page %1$d of %2$d',$current,$pages)).'</span>';
    if ($pages>1) {
        woocommerce_pagination();
    } else {
        echo '<nav class="woocommerce-pagination" aria-label="Product pagination"><ul class="page-numbers"><li><span class="dsp-page-disabled" aria-disabled="true">&larr;</span></li><li><span class="page-numbers current" aria-current="page"><span class="screen-reader-text">Page </span>1</span></li><li><span class="dsp-page-disabled" aria-disabled="true">&rarr;</span></li></ul></nav>';
    }
    echo '</div>';
}
function dsp_category_copy($slug) {
    return ['website-templates'=>'A thoughtful starting point for your next corner of the web.','design-assets'=>'The details that make your work feel like you.','ebooks-guides'=>'Fresh perspectives, practical ideas and something new to learn.','productivity'=>'Make room for focused work and your next good idea.'][$slug] ?? 'Find digital resources for your next project.';
}
function dsp_catalog_sorting() {
    $options=['menu_order'=>'Default sorting','price'=>'Price: low to high','price-desc'=>'Price: high to low','date'=>'Latest arrivals','popularity'=>'Most popular'];
    if (wc_review_ratings_enabled()) $options['rating']='Highest rated';
    if (is_search()) $options=['relevance'=>'Relevance']+$options;
    $default=is_search()?'relevance':apply_filters('woocommerce_default_catalog_orderby',get_option('woocommerce_default_catalog_orderby','menu_order'));
    $selected=isset($_GET['orderby']) && is_string($_GET['orderby'])?wc_clean(wp_unslash($_GET['orderby'])):$default;
    if (!isset($options[$selected])) $selected=is_search()?'relevance':'menu_order';
    $action=is_product_taxonomy()?get_term_link(get_queried_object()):wc_get_page_permalink('shop');
    if (is_wp_error($action)) $action=wc_get_page_permalink('shop');
    ?>
    <form class="woocommerce-ordering dsp-ordering" method="get" action="<?php echo esc_url($action); ?>">
        <label for="dsp-orderby">Sort by</label>
        <select id="dsp-orderby" class="orderby" name="orderby">
            <?php foreach ($options as $value=>$label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($selected,$value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?>
        </select>
        <?php wc_query_string_form_fields(null,['orderby','order','submit','paged','product-page']); ?>
        <button type="submit" class="dsp-button dsp-button-outline">Apply</button>
    </form>
    <?php
}
add_action('init',function () {
    // Empty price inputs mean no limit, rather than a zero-price maximum.
    foreach (['min_price','max_price'] as $key) {
        if (isset($_GET[$key]) && is_string($_GET[$key]) && trim($_GET[$key])==='') unset($_GET[$key]);
    }
    foreach (['about','contact'] as $type) add_shortcode('digicarts_'.$type,static function () use ($type) { ob_start(); include DSP_PATH.'templates/'.$type.'.php'; return ob_get_clean(); });
});
add_filter('template_include',function ($template) {
    if (dsp_catalog()) return DSP_PATH.'templates/shop.php';
    return is_front_page() ? DSP_PATH.'templates/home.php' : $template;
},99);
add_filter('body_class',function ($classes) {
    if (dsp_catalog()) $classes[]='dsp-catalog';
    if (is_page(['about-us','contact-us'])) $classes[]='dsp-page';
    return $classes;
});
add_action('wp_enqueue_scripts',function () {
    if (!is_front_page() && !dsp_catalog() && !is_page(['about-us','contact-us']) && !dsp_is_policy_page()) return;
    wp_enqueue_style('digicarts-store-pages',plugin_dir_url(__FILE__).'assets/pages.css',[],filemtime(DSP_PATH.'assets/pages.css'));
    if (is_front_page() && !dsp_catalog()) {
        wp_enqueue_style('digicarts-home',plugin_dir_url(__FILE__).'assets/home.css',['digicarts-store-pages'],filemtime(DSP_PATH.'assets/home.css'));
        wp_enqueue_script('wc-add-to-cart');
    }
    $accent=function_exists('dchf_options') ? sanitize_hex_color(dchf_options()['accent']) : '#7546e8';
    wp_add_inline_style('digicarts-store-pages','.dsp-root{--dsp-accent:'.($accent ?: '#7546e8').';}');
},50);
add_filter('loop_shop_per_page',static fn($count)=>dsp_catalog()?12:$count,30);
add_filter('loop_shop_columns',static fn($count)=>dsp_catalog()?3:$count,30);
add_filter('option_woocommerce_shop_page_display',static fn($value)=>dsp_catalog()?'':$value);
add_filter('option_woocommerce_category_archive_display',static fn($value)=>dsp_catalog()?'':$value);
require_once DSP_PATH.'contact.php';
require_once DSP_PATH.'policies.php';
require_once DSP_PATH.'home.php';
require_once DSP_PATH.'setup.php';
register_activation_hook(__FILE__,'dsp_activate');
