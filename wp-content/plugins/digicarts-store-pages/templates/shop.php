<?php
defined('ABSPATH') || exit;
get_header('shop');
remove_action('woocommerce_before_main_content','woocommerce_breadcrumb',20);
do_action('woocommerce_before_main_content');
$categories=dsp_categories(); $term=is_product_taxonomy()?get_queried_object():null;
$heading=is_search()?'Search products':($term?$term->name:'Shop');
$query_text=get_search_query();
$current_term=$term && $term->taxonomy==='product_cat' ? $term->slug : '';
$has_products=woocommerce_product_loop();
remove_action('woocommerce_after_shop_loop','woocommerce_pagination',10);
add_action('woocommerce_after_shop_loop','dsp_catalog_pagination',10);
remove_action('woocommerce_before_shop_loop_item_title','woocommerce_template_loop_product_thumbnail',10);
add_action('woocommerce_before_shop_loop_item_title','dsp_product_thumbnail',10);
?>
<div class="dsp-root dsp-shop">
    <h1 class="screen-reader-text"><?php echo esc_html($heading); ?></h1>
    <?php if ($query_text || $has_products || wc_notice_count()) : ?>
    <div class="dsp-catalog-topbar">
        <div class="dsp-catalog-summary">
            <?php if ($query_text) : ?><p class="dsp-search-caption">Results for <strong>“<?php echo esc_html($query_text); ?>”</strong></p><?php endif; ?>
            <?php if ($has_products) : ?>
                <div class="dsp-shop-toolbar"><?php remove_action('woocommerce_before_shop_loop','woocommerce_catalog_ordering',30); do_action('woocommerce_before_shop_loop'); ?></div>
            <?php else : woocommerce_output_all_notices(); endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="dsp-shop-layout">
        <aside class="dsp-filters" aria-label="Product filters"><div class="dsp-filter-heading"><h2>Browse & find</h2><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Reset</a></div><div class="dsp-sidebar-sort"><?php dsp_catalog_sorting(); ?></div><h3>Collections</h3><nav class="dsp-category-nav" aria-label="Shop collections"><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" <?php echo !$term?'aria-current="page"':''; ?>>All products <?php echo dsp_icon('grid'); ?></a><?php foreach ($categories as $category) : $url=get_term_link($category); if (is_wp_error($url)) continue; ?><a href="<?php echo esc_url($url); ?>" <?php echo $current_term===$category->slug?'aria-current="page"':''; ?>><?php echo esc_html($category->name); ?><span><?php echo esc_html($category->count); ?></span></a><?php endforeach; ?></nav>
            <form class="dsp-price-filter" method="get" action="<?php echo esc_url($term?get_term_link($term):wc_get_page_permalink('shop')); ?>"><h3>Price range <small><?php echo esc_html(get_woocommerce_currency()); ?></small></h3><div><label>Min<input type="number" min="0" step="any" name="min_price" placeholder="0" value="<?php echo isset($_GET['min_price']) && is_scalar($_GET['min_price'])?esc_attr(wc_format_decimal(wp_unslash($_GET['min_price']))):''; ?>"></label><span>—</span><label>Max<input type="number" min="0" step="any" name="max_price" placeholder="Any" value="<?php echo isset($_GET['max_price']) && is_scalar($_GET['max_price'])?esc_attr(wc_format_decimal(wp_unslash($_GET['max_price']))):''; ?>"></label></div><?php wc_query_string_form_fields(null,['min_price','max_price','paged','product-page','submit']); ?><button type="submit" class="dsp-button dsp-button-outline">Apply price</button></form>
            
            <div class="dsp-filter-note"><?php echo dsp_icon('download'); ?><strong>Digital by design.</strong><p>Check each product for file formats, compatibility and licence details.</p><a href="<?php echo esc_url(dsp_page_url('contact-us')); ?>">Need a hand? ↗</a></div>
        </aside>
        <section class="dsp-results" aria-label="Products">
            <?php if ($has_products) : ?>
                <?php woocommerce_product_loop_start(); if (wc_get_loop_prop('total')) { while (have_posts()) { the_post(); do_action('woocommerce_shop_loop'); wc_get_template_part('content','product'); } } woocommerce_product_loop_end(); do_action('woocommerce_after_shop_loop'); ?>
            <?php else : ?>
                <div class="dsp-empty-products"><span class="dsp-empty-icon"><?php echo dsp_icon($query_text?'search':'grid'); ?></span><p class="dsp-eyebrow"><?php echo $query_text?'A DIFFERENT STARTING POINT':'GOOD THINGS TAKE SHAPE'; ?></p><h2><?php echo ($query_text || isset($_GET['min_price']) || isset($_GET['max_price']))?'No matches this time.':'Your next find is on its way.'; ?></h2><p><?php echo ($query_text || isset($_GET['min_price']) || isset($_GET['max_price']))?'Try another keyword, collection or price range.':'We’re putting this digital collection together. Check back for new resources for your next project.'; ?></p><a class="dsp-button" href="<?php echo esc_url($query_text?wc_get_page_permalink('shop'):dsp_page_url('about-us')); ?>"><?php echo $query_text?'Browse all products':'About DIGICARTS'; ?> <span aria-hidden="true">↗</span></a></div>
            <?php endif; ?>
            <div class="dsp-shop-bottom"><span><?php echo dsp_icon('tag'); ?> Know what you’re getting.</span><p>Every product’s description is the place to find its included files and usage details.</p></div>
        </section>
    </div>
</div>
<?php do_action('woocommerce_after_main_content'); get_footer('shop'); ?>
