<?php defined('ABSPATH') || exit; ?>
<div class="woocommerce-billing-fields dce-checkout-card">
    <div class="dce-section-heading"><span class="dce-step">01</span><div><h3>Your details</h3><p>Your order confirmation will be sent to this email.</p></div></div>
    <?php do_action('woocommerce_before_checkout_billing_form',$checkout); ?>
    <div class="woocommerce-billing-fields__field-wrapper">
        <?php $fields=$checkout->get_checkout_fields('billing'); $main=['billing_first_name','billing_email','billing_phone'];
        foreach ($main as $key) if (isset($fields[$key])) woocommerce_form_field($key,$fields[$key],$checkout->get_value($key)); ?>
        <div class="clear"></div>
        <details class="dce-optional-details"><summary>Address & other details <span>Optional</span></summary><div class="dce-optional-fields">
            <?php foreach ($fields as $key=>$field) if (!in_array($key,$main,true)) woocommerce_form_field($key,$field,$checkout->get_value($key)); ?>
            <div class="clear"></div>
        </div></details>
    </div>
    <?php do_action('woocommerce_after_checkout_billing_form',$checkout); ?>
</div>
<?php if (!is_user_logged_in() && $checkout->is_registration_enabled()) : ?>
<div class="woocommerce-account-fields dce-checkout-card dce-create-account">
    <p class="form-row form-row-wide create-account"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox" for="createaccount"><input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" type="checkbox" name="createaccount" value="1" <?php checked((bool)$checkout->get_value('createaccount')); ?>><span>Create an account <small>Optional</small></span></label></p>
    <p class="dce-subtext">Keep your orders and eligible downloads together in your dashboard. You can also continue as a guest.</p>
    <?php do_action('woocommerce_before_checkout_registration_form',$checkout); ?>
    <div class="create-account">
        <?php foreach ($checkout->get_checkout_fields('account') as $key=>$field) woocommerce_form_field($key,$field,$checkout->get_value($key)); ?>
        <div class="clear"></div>
    </div>
    <?php do_action('woocommerce_after_checkout_registration_form',$checkout); ?>
</div>
<?php elseif (is_user_logged_in()) : ?>
<div class="dce-signed-in"><?php echo dce_icon('edit-account'); ?><span>Signed in as <strong><?php echo esc_html(wp_get_current_user()->display_name); ?></strong>. Your order will appear in your dashboard.</span></div>
<?php endif; ?>
