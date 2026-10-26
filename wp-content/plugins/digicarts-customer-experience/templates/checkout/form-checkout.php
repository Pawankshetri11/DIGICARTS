<?php defined('ABSPATH') || exit; do_action('woocommerce_before_checkout_form',$checkout); ?>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="Checkout">
    <div class="dce-checkout-layout">
        <div class="dce-checkout-details">
            <?php do_action('woocommerce_checkout_before_customer_details'); ?>
            <div id="customer_details"><div><?php do_action('woocommerce_checkout_billing'); ?></div><div class="dce-shipping-details"><?php do_action('woocommerce_checkout_shipping'); ?></div></div>
            <?php do_action('woocommerce_checkout_after_customer_details'); ?>
        </div>
        <aside class="dce-checkout-summary">
            <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
            <div class="dce-section-heading"><span class="dce-step">02</span><div><h3 id="order_review_heading">Your order</h3><p>Review your items and choose how to pay.</p></div></div>
            <?php do_action('woocommerce_checkout_before_order_review'); ?>
            <div id="order_review" class="woocommerce-checkout-review-order"><?php do_action('woocommerce_checkout_order_review'); ?></div>
            <?php do_action('woocommerce_checkout_after_order_review'); ?>
        </aside>
    </div>
</form>
<?php do_action('woocommerce_after_checkout_form',$checkout); ?>
