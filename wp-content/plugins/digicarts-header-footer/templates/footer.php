<?php defined('ABSPATH') || exit; $footer_categories = dchf_product_categories(); ?>
<footer class="dchf-shell dchf-footer dchf-footer-refresh">
<?php if ($options['show_newsletter']) : ?>
<section class="dchf-footer-invite" id="dchf-newsletter-signup" aria-labelledby="dchf-invite-title"><div class="dchf-container dchf-invite-layout">
    <div class="dchf-invite-copy"><span class="dchf-invite-mark" aria-hidden="true"><?php echo dchf_icon('chat'); ?></span><h2 id="dchf-invite-title"><?php echo esc_html($options['newsletter_title']); ?></h2><p><?php echo nl2br(esc_html($options['newsletter_description'])); ?></p></div>
    <div class="dchf-invite-form">
    <?php if ($options['newsletter_shortcode']) { echo do_shortcode($options['newsletter_shortcode']); } else { ?>
        <?php $status=isset($_GET['dchf_signup'])&&is_string($_GET['dchf_signup'])?sanitize_key(wp_unslash($_GET['dchf_signup'])):''; $messages=['success'=>'Thanks! Your email has been added to our updates list.','invalid'=>'Please enter a valid email address and confirm your consent.','error'=>'We could not save your signup. Please refresh the page and try again.','wait'=>'Please wait a minute before trying again.']; if (isset($messages[$status])) : ?><p class="dchf-form-message" role="status"><?php echo esc_html($messages[$status]); ?></p><?php endif; ?>
        <form class="dchf-newsletter-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="dchf_subscribe"><?php wp_nonce_field('dchf_subscribe','dchf_nonce'); ?>
            <div class="dchf-newsletter-inputs"><label class="dchf-sr" for="dchf-email">Your email address</label><input type="email" name="dchf_email" id="dchf-email" autocomplete="email" placeholder="<?php echo esc_attr($options['newsletter_placeholder']); ?>" required><button type="submit"><?php echo esc_html($options['newsletter_button']); ?><span aria-hidden="true">↗</span></button></div>
            <label class="dchf-consent"><input type="checkbox" name="dchf_consent" value="1" required><span>I agree to receive product news and store updates.</span></label>
            <div class="dchf-honeypot" aria-hidden="true"><label for="dchf-website">Leave this field blank</label><input type="text" name="dchf_website" id="dchf-website" tabindex="-1" autocomplete="off"></div>
        </form>
    <?php } ?>
    <?php if ($options['newsletter_note']) : ?><p class="dchf-invite-note"><?php echo nl2br(esc_html($options['newsletter_note'])); ?></p><?php endif; ?>
    </div>
</div></section>
<?php endif; ?>
<div class="dchf-container dchf-footer-directory">
    <section class="dchf-footer-brand" aria-label="About the store">
        <?php dchf_logo($options); ?>
        <?php if ($options['about_title']) : ?><h2><?php echo esc_html($options['about_title']); ?></h2><?php endif; ?>
        <?php if ($options['about_description']) : ?><p><?php echo nl2br(esc_html($options['about_description'])); ?></p><?php endif; ?>
        <?php if ($options['show_socials']) : ?><div class="dchf-footer-socials" aria-label="Social media">
            <?php foreach (['instagram'=>'Instagram','facebook'=>'Facebook','youtube'=>'YouTube','x'=>'X'] as $social=>$label) : $social_url=$options[$social.'_url']; ?>
                <?php if ($social_url) : ?><a href="<?php echo esc_url($social_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($label); ?>"><?php echo dchf_icon($social); ?></a>
                <?php else : ?><span class="dchf-social-placeholder" role="img" aria-label="<?php echo esc_attr($label.' — profile coming soon'); ?>" title="<?php echo esc_attr($label.' — coming soon'); ?>"><?php echo dchf_icon($social); ?></span><?php endif; ?>
            <?php endforeach; ?>
        </div><?php endif; ?>
    </section>
    <nav class="dchf-directory-links" aria-label="Product collections"><h2><?php echo esc_html($options['collections_title']); ?></h2><?php
        $collection_links=[];
        foreach ($footer_categories as $category) { $url=get_term_link($category); if (!is_wp_error($url)) $collection_links[]=[$category->name,$url]; }
        $collection_links[]=['Shop all products',dchf_shop_url()];
        dchf_menu('dchf_collections',$collection_links,1);
    ?></nav>
    <nav class="dchf-directory-links" aria-label="Policies"><h2><?php echo esc_html($options['info_title']); ?></h2><?php
        $policies=[];
        $dmca_page=get_page_by_path('dmca');
        if ($dmca_page && $dmca_page->post_status==='publish') $policies[]=['DMCA',get_permalink($dmca_page)];
        if (get_privacy_policy_url()) $policies[]=['Privacy Policy',get_privacy_policy_url()];
        foreach (['terms-and-conditions'=>'Terms & Conditions','refund-policy'=>'Refund Policy'] as $slug=>$label) {
            $policy_page=get_page_by_path($slug);
            if ($policy_page && $policy_page->post_status==='publish') $policies[]=[$label,get_permalink($policy_page)];
        }
        dchf_menu('dchf_information',$policies,1);
    ?></nav>
    <div class="dchf-footer-account-column">
        <nav class="dchf-directory-links" aria-label="Account links"><h2><?php echo esc_html($options['quick_title']); ?></h2><?php dchf_menu('dchf_quick',[['My account',dchf_account_url()],['About Us',home_url('/about-us/')],['Contact Us',home_url('/contact-us/')]],1); ?></nav>
        <?php if ($options['show_checkout']) : ?><section class="dchf-checkout-block" aria-label="Payment methods"><h2><?php echo esc_html($options['checkout_title']); ?></h2>
            <?php if ($options['payment_image_id']) : ?><div class="dchf-checkout-custom"><?php echo wp_get_attachment_image(absint($options['payment_image_id']),'medium',false,['alt'=>$options['payment_label']]); ?></div>
            <?php else : ?><div class="dchf-payment-marks"><?php foreach (['upi'=>'UPI','visa'=>'Visa','rupay'=>'RuPay','mastercard'=>'Mastercard'] as $mark=>$label) : ?><span class="dchf-payment-mark"><img src="<?php echo esc_url(DCHF_URL.'assets/payments/'.$mark.'.svg'); ?>" alt="<?php echo esc_attr($label); ?>" width="76" height="34" loading="lazy"></span><?php endforeach; ?></div><?php endif; ?>
        </section><?php endif; ?>
    </div>
</div>
<?php if ($options['show_benefits']) : ?><div class="dchf-container"><div class="dchf-footer-promises">
    <?php for ($i=1; $i<=5; $i++) : if (!$options["benefit_{$i}_title"] && !$options["benefit_{$i}_text"]) continue; ?><div class="dchf-footer-promise"><?php echo dchf_icon($options["benefit_{$i}_icon"]); ?><div><h3><?php echo esc_html($options["benefit_{$i}_title"]); ?></h3><p><?php echo esc_html($options["benefit_{$i}_text"]); ?></p></div></div><?php endfor; ?>
</div></div><?php endif; ?>
<div class="dchf-container dchf-footer-legal">
    <div class="dchf-footer-copyright"><p><?php echo esc_html(str_replace(['{year}','{site}'],[wp_date('Y'),get_bloginfo('name')],$options['copyright'])); ?></p></div>
</div>
<?php if ($options['show_back_top']) : ?><button type="button" class="dchf-back-top" aria-label="Back to top" title="Back to top"><?php echo dchf_icon('up'); ?></button><?php endif; ?>
</footer>
