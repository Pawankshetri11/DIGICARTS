<?php
defined('ABSPATH') || exit;
$registration = get_option('woocommerce_enable_myaccount_registration') === 'yes';
$posted_register = isset($_POST['register']);
$posted_login = isset($_POST['login']);
$requested_tab = isset($_GET['account_tab']) && is_string($_GET['account_tab']) ? sanitize_key(wp_unslash($_GET['account_tab'])) : 'login';
$active = $registration && ($posted_register || (!$posted_login && $requested_tab === 'register')) ? 'register' : 'login';
$posted_value = static function ($key) { return isset($_POST[$key]) && is_string($_POST[$key]) ? esc_attr(wp_unslash($_POST[$key])) : ''; };
do_action('woocommerce_before_customer_login_form');
?>
<section class="dce-auth-card" data-active-tab="<?php echo esc_attr($active); ?>" data-submitted="<?php echo ($posted_register || $posted_login) ? 'true' : 'false'; ?>" aria-label="Customer account">
    <header class="dce-auth-heading"><span class="dce-auth-symbol"><?php echo dce_icon('edit-account'); ?></span><h1>Your creative space</h1><p>Your orders, downloads and next big ideas.</p></header>
    <?php if ($registration) : ?>
    <nav class="dce-auth-tabs" aria-label="Login or register">
        <a id="dce-tab-login" href="<?php echo esc_url(add_query_arg('account_tab','login',wc_get_page_permalink('myaccount'))); ?>" data-auth-tab="login" class="<?php echo $active==='login'?'is-active':''; ?>">Log in</a>
        <a id="dce-tab-register" href="<?php echo esc_url(add_query_arg('account_tab','register',wc_get_page_permalink('myaccount'))); ?>" data-auth-tab="register" class="<?php echo $active==='register'?'is-active':''; ?>">Create account</a>
    </nav>
    <?php endif; ?>
    <div id="dce-panel-login" class="dce-auth-panel" <?php echo $active!=='login'?'hidden':''; ?>>
        <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
            <?php do_action('woocommerce_login_form_start'); ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="username">Email or username <span class="required" aria-hidden="true">*</span></label><input type="text" class="woocommerce-Input input-text" name="username" id="username" autocomplete="username" value="<?php echo $posted_login ? $posted_value('username') : ''; ?>" required aria-required="true"></p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="password">Password <span class="required" aria-hidden="true">*</span></label><input type="password" class="woocommerce-Input input-text" name="password" id="password" autocomplete="current-password" required aria-required="true"></p>
            <?php do_action('woocommerce_login_form'); ?>
            <div class="dce-auth-options"><label class="woocommerce-form__label woocommerce-form-login__rememberme"><input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" <?php checked($posted_login && !empty($_POST['rememberme'])); ?>><span>Remember me</span></label><a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgot password?</a></div>
            <p class="form-row dce-auth-submit"><?php wp_nonce_field('woocommerce-login','woocommerce-login-nonce'); ?><button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="Log in">Log in <?php echo dce_icon('arrow'); ?></button></p>
            <?php do_action('woocommerce_login_form_end'); ?>
        </form>
    </div>
    <?php if ($registration) : ?>
    <div id="dce-panel-register" class="dce-auth-panel" <?php echo $active!=='register'?'hidden':''; ?>>
        <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>
            <?php do_action('woocommerce_register_form_start'); ?>
            <?php if (get_option('woocommerce_registration_generate_username')==='no') : ?><p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="reg_username">Username <span class="required" aria-hidden="true">*</span></label><input type="text" class="woocommerce-Input input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo $posted_register ? $posted_value('username') : ''; ?>" required aria-required="true"></p><?php endif; ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="reg_email">Email address <span class="required" aria-hidden="true">*</span></label><input type="email" class="woocommerce-Input input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo $posted_register ? $posted_value('email') : ''; ?>" required aria-required="true"></p>
            <?php if (get_option('woocommerce_registration_generate_password')==='no') : ?><p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="reg_password">Create a password <span class="required" aria-hidden="true">*</span></label><input type="password" class="woocommerce-Input input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true"></p><?php else : ?><p class="dce-auth-description">We'll email you a link to set your password.</p><?php endif; ?>
            <?php do_action('woocommerce_register_form'); ?>
            <p class="woocommerce-form-row form-row dce-auth-submit"><?php wp_nonce_field('woocommerce-register','woocommerce-register-nonce'); ?><button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="Register">Create account <?php echo dce_icon('arrow'); ?></button></p>
            <?php do_action('woocommerce_register_form_end'); ?>
        </form>
    </div>
    <?php endif; ?>
</section>
<p class="dce-auth-back"><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Continue browsing <?php echo dce_icon('arrow'); ?></a></p>
<?php do_action('woocommerce_after_customer_login_form'); ?>
