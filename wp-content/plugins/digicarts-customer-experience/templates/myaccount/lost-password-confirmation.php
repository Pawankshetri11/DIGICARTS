<?php defined('ABSPATH') || exit; do_action('woocommerce_before_lost_password_confirmation_message'); ?>
<section class="dce-auth-card dce-password-card dce-reset-confirmation" aria-labelledby="dce-password-title">
    <header class="dce-auth-heading"><span class="dce-auth-symbol"><?php echo dce_icon('lock'); ?></span><h1 id="dce-password-title">Check your inbox</h1><p role="status">Your password reset request has been received.</p></header>
    <p class="dce-reset-copy"><?php echo esc_html(apply_filters('woocommerce_lost_password_confirmation_message','If the details match an account, look for a password reset email. Follow its link to choose your new password.')); ?></p>
    <div class="dce-reset-hint">Can’t find it? Check your spam folder and allow a few minutes for delivery before requesting another link.</div>
    <a class="dce-reset-login button" href="<?php echo esc_url(add_query_arg('account_tab','login',wc_get_page_permalink('myaccount'))); ?>">Back to log in <?php echo dce_icon('arrow'); ?></a>
    <p class="dce-auth-back"><a href="<?php echo esc_url(wc_get_account_endpoint_url('lost-password')); ?>">Request another link</a></p>
</section>
<?php do_action('woocommerce_after_lost_password_confirmation_message'); ?>
