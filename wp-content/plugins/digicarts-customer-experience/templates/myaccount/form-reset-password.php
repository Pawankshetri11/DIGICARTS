<?php defined('ABSPATH') || exit; do_action('woocommerce_before_reset_password_form'); ?>
<section class="dce-auth-card dce-password-card" aria-labelledby="dce-password-title">
    <header class="dce-auth-heading"><span class="dce-auth-symbol"><?php echo dce_icon('lock'); ?></span><h1 id="dce-password-title">Choose a new password</h1><p><?php echo wp_kses_post(apply_filters('woocommerce_reset_password_message','Use a strong password that you don’t use on other websites.')); ?></p></header>
    <form method="post" class="woocommerce-ResetPassword lost_reset_password">
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="password_1">New password <span class="required" aria-hidden="true">*</span></label><input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_1" id="password_1" autocomplete="new-password" required aria-required="true"></p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="password_2">Confirm new password <span class="required" aria-hidden="true">*</span></label><input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_2" id="password_2" autocomplete="new-password" required aria-required="true"></p>
        <input type="hidden" name="reset_key" value="<?php echo esc_attr($args['key']); ?>"><input type="hidden" name="reset_login" value="<?php echo esc_attr($args['login']); ?>">
        <?php do_action('woocommerce_resetpassword_form'); ?>
        <p class="woocommerce-form-row form-row dce-auth-submit"><input type="hidden" name="wc_reset_password" value="true"><button type="submit" class="woocommerce-Button button" value="Save">Save new password <?php echo dce_icon('arrow'); ?></button></p>
        <?php wp_nonce_field('reset_password','woocommerce-reset-password-nonce'); ?>
    </form>
    <p class="dce-auth-back"><a href="<?php echo esc_url(add_query_arg('account_tab','login',wc_get_page_permalink('myaccount'))); ?>">Back to log in</a></p>
</section>
<?php do_action('woocommerce_after_reset_password_form'); ?>
