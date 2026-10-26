<?php defined('ABSPATH') || exit; $customer=wp_get_current_user(); $name=$customer->first_name ?: $customer->display_name; do_action('woocommerce_before_account_navigation'); ?>
<nav class="woocommerce-MyAccount-navigation dce-account-nav" aria-label="Account pages">
    <div class="dce-profile"><span class="dce-avatar" aria-hidden="true"><?php echo esc_html(mb_strtoupper(mb_substr($name,0,1))); ?></span><div><strong><?php echo esc_html($name); ?></strong><span><?php echo esc_html($customer->user_email); ?></span></div></div>
    <ul><?php foreach (wc_get_account_menu_items() as $endpoint=>$label) : ?><li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?>"><a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" <?php echo wc_is_current_account_menu_item($endpoint)?'aria-current="page"':''; ?>><?php echo dce_icon($endpoint); ?><span><?php echo esc_html($label); ?></span><?php if (wc_is_current_account_menu_item($endpoint)) : ?><span class="dce-nav-dot" aria-hidden="true"></span><?php endif; ?></a></li><?php endforeach; ?></ul>
    <p class="dce-nav-caption">A little space for your next big idea.</p>
</nav>
<?php do_action('woocommerce_after_account_navigation'); ?>
