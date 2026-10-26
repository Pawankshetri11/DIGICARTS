<?php
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    if (!class_exists('WooCommerce')) return;
    wp_enqueue_style('digicarts-cart-drawer', DCHF_URL.'assets/cart-drawer.css', ['digicarts-header-footer'], filemtime(DCHF_PATH.'assets/cart-drawer.css'));
    wp_enqueue_script('digicarts-cart-drawer', DCHF_URL.'assets/cart-drawer.js', ['jquery','digicarts-header-footer'], filemtime(DCHF_PATH.'assets/cart-drawer.js'), true);
    $auto_open = WC()->session && WC()->session->get('dchf_open_cart');
    if ($auto_open) WC()->session->__unset('dchf_open_cart');
    wp_localize_script('digicarts-cart-drawer', 'dchfCart', [
        'api'=>esc_url_raw(rest_url('wc/store/v1/cart')),
        'cartUrl'=>wc_get_cart_url(), 'shopUrl'=>dchf_shop_url(), 'checkoutUrl'=>wc_get_checkout_url(),
        'autoOpen'=>(bool)$auto_open, 'couponsEnabled'=>wc_coupons_enabled(),
        'taxInclusive'=>get_option('woocommerce_tax_display_cart') === 'incl',
        'countries'=>WC()->countries->get_shipping_countries(), 'states'=>WC()->countries->get_states(),
    ]);
}, 20);

// Keep the WooCommerce cart page assignment for compatibility, but use the drawer in the storefront.
add_action('template_redirect', function () {
    if (function_exists('is_cart') && is_cart() && !wp_doing_ajax() && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        wp_safe_redirect(add_query_arg('open-cart','1',dchf_shop_url()));
        exit;
    }
});
add_action('woocommerce_add_to_cart', function () {
    if (!wp_doing_ajax() && !(defined('REST_REQUEST') && REST_REQUEST) && WC()->session) WC()->session->set('dchf_open_cart',true);
});
add_filter('woocommerce_add_to_cart_redirect', function ($url) {
    if (get_option('woocommerce_cart_redirect_after_add') === 'yes') return add_query_arg('open-cart','1',dchf_shop_url());
    return $url;
});
add_action('wp_footer', function () {
    if (!class_exists('WooCommerce')) return;
    ?>
    <dialog id="dchf-cart-drawer" class="dchf-shell dchf-cart-drawer" aria-labelledby="dchf-cart-title">
        <div class="dchf-drawer-heading"><div><span class="dchf-drawer-eyebrow">YOUR DIGITAL FINDS</span><h2 id="dchf-cart-title">Your cart <span class="dchf-drawer-count">0</span></h2></div><button type="button" class="dchf-drawer-close" aria-label="Close cart"><?php echo dchf_icon('close'); ?></button></div>
        <div class="dchf-drawer-message" role="status" aria-live="polite" hidden></div>
        <div class="dchf-drawer-content"><p class="dchf-cart-loading">Loading your cart…</p></div>
        <div class="dchf-drawer-checkout" hidden><div class="dchf-drawer-total"><span>Total</span><strong></strong></div><a class="dchf-checkout-link" href="<?php echo esc_url(wc_get_checkout_url()); ?>">Continue to checkout <span aria-hidden="true">→</span></a><button type="button" class="dchf-continue-shopping">Continue shopping</button></div>
    </dialog>
    <?php
});
