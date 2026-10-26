<?php defined('ABSPATH') || exit; do_action('woocommerce_before_lost_password_form'); ?>
<section class="dce-auth-card dce-password-card" aria-labelledby="dce-password-title">
    <header class="dce-auth-heading"><span class="dce-auth-symbol"><?php echo dce_icon('lock'); ?></span><h1 id="dce-password-title">Forgot your password?</h1><p><?php echo wp_kses_post(apply_filters('woocommerce_lost_password_message','Enter your email or username. We’ll send you a link to reset your password.')); ?></p></header>
    <form method="post" class="woocommerce-ResetPassword lost_reset_password">
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="user_login">Email or username <span class="required" aria-hidden="true">*</span></label><input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" value="<?php echo isset($_POST['user_login']) && is_string($_POST['user_login']) ? esc_attr(wp_unslash($_POST['user_login'])) : ''; ?>" required aria-required="true"></p>
        <?php do_action('woocommerce_lostpassword_form'); ?>
        <p class="woocommerce-form-row form-row dce-auth-submit"><input type="hidden" name="wc_reset_password" value="true"><button type="submit" class="woocommerce-Button button" value="Reset password">Send reset link <?php echo dce_icon('arrow'); ?></button></p>
        <?php wp_nonce_field('lost_password','woocommerce-lost-password-nonce'); ?>
    </form>
    <p class="dce-auth-back"><a href="<?php echo esc_url(add_query_arg('account_tab','login',wc_get_page_permalink('myaccount'))); ?>">Back to log in</a></p>
</section>
<?php do_action('woocommerce_after_lost_password_form'); ?>
