<?php
defined('ABSPATH') || exit;
$endpoint=WC()->query->get_current_endpoint();
$labels=wc_get_account_menu_items();
$title=$endpoint ? ($labels[$endpoint] ?? ucwords(str_replace('-',' ',$endpoint))) : 'Your dashboard';
?>
<header class="dce-account-heading"><div><p class="dce-eyebrow">YOUR DIGITAL SPACE</p><h1><?php echo esc_html($title); ?></h1><p>Everything you need, all in one place.</p></div><a class="dce-outline-button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Explore the store <?php echo dce_icon('arrow'); ?></a></header>
<div class="dce-account-layout">
    <?php do_action('woocommerce_account_navigation'); ?>
    <div class="woocommerce-MyAccount-content"><?php do_action('woocommerce_account_content'); ?></div>
</div>
