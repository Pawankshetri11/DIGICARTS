<?php
defined('ABSPATH') || exit;

// Public, read-only search: only published products visible in search are returned.
add_action('rest_api_init', function () {
    register_rest_route('digicarts/v1', '/search', [
        'methods'=>'GET', 'permission_callback'=>'__return_true',
        'args'=>[
            'q'=>['required'=>true, 'sanitize_callback'=>'sanitize_text_field', 'validate_callback'=>function ($value) { return is_string($value) && mb_strlen(trim($value)) >= 2 && mb_strlen($value) <= 80; }],
            'category'=>['default'=>'', 'sanitize_callback'=>'sanitize_title'],
        ],
        'callback'=>function ($request) {
            if (!function_exists('wc_get_product')) return rest_ensure_response(['products'=>[]]);
            $tax = ['relation'=>'AND'];
            $excluded = ['exclude-from-search'];
            if (get_option('woocommerce_hide_out_of_stock_items') === 'yes') $excluded[] = 'outofstock';
            $tax[] = ['taxonomy'=>'product_visibility','field'=>'name','terms'=>$excluded,'operator'=>'NOT IN'];
            if ($request['category']) $tax[] = ['taxonomy'=>'product_cat','field'=>'slug','terms'=>$request['category']];
            $query = new WP_Query([
                'post_type'=>'product','post_status'=>'publish','has_password'=>false,
                's'=>$request['q'],'posts_per_page'=>6,'no_found_rows'=>true,'tax_query'=>$tax,
            ]);
            $products = [];
            foreach ($query->posts as $post) {
                $product = wc_get_product($post->ID);
                if (!$product) continue;
                $products[] = dchf_product_data($product);
            }
            return rest_ensure_response(['products'=>$products]);
        },
    ]);
});

function dchf_product_data($product) {
    return [
        'id'=>$product->get_id(),
        'name'=>html_entity_decode(wp_strip_all_tags($product->get_name()), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'url'=>get_permalink($product->get_id()),
        'image'=>wp_get_attachment_image_url($product->get_image_id(),'woocommerce_thumbnail') ?: wc_placeholder_img_src(),
        'price'=>html_entity_decode(wp_strip_all_tags($product->get_price_html()), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
    ];
}

function dchf_wishlist_product_button() {
    global $product;
    if (!$product || !dchf_options()['show_wishlist'] || dchf_options()['wishlist_url']) return;
    echo '<button type="button" class="dchf-save-product" data-dchf-product="'.esc_attr(wp_json_encode(dchf_product_data($product))).'" aria-pressed="false">'.dchf_icon('heart').'<span>Save to wishlist</span></button>';
}
add_action('woocommerce_after_shop_loop_item','dchf_wishlist_product_button',15);
add_action('woocommerce_single_product_summary','dchf_wishlist_product_button',35);

add_action('wp_footer', function () {
    if (!dchf_options()['show_wishlist'] || dchf_options()['wishlist_url']) return;
    ?>
    <dialog id="dchf-wishlist-dialog" class="dchf-shell dchf-wishlist-dialog" aria-labelledby="dchf-wishlist-title">
        <div class="dchf-wishlist-heading"><div><h2 id="dchf-wishlist-title">Your wishlist</h2><p>Saved on this browser for your next visit.</p></div><button type="button" class="dchf-wishlist-close" aria-label="Close wishlist"><?php echo dchf_icon('close'); ?></button></div>
        <div class="dchf-wishlist-items"></div>
        <p class="dchf-wishlist-feedback" role="status"></p>
        <a class="dchf-wishlist-shop" href="<?php echo esc_url(dchf_shop_url()); ?>">Explore digital products</a>
    </dialog>
    <?php
});
