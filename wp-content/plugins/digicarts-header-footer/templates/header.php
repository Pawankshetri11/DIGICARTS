<?php defined('ABSPATH') || exit; $categories = dchf_product_categories(); ?>
<?php if ($options['show_topbar'] && $options['announcement']) : ?>
<aside class="dchf-shell dchf-discount-banner" aria-label="Store offers" data-banner-key="<?php echo esc_attr(substr(hash('sha256', $options['announcement'].'|'.$options['announcement_url']), 0, 16)); ?>">
    <div class="dchf-container dchf-discount-inner">
        <div class="dchf-discount-message"><?php echo dchf_icon('tag'); ?>
            <?php if ($options['announcement_url']) : ?><a href="<?php echo esc_url($options['announcement_url']); ?>"><?php echo esc_html($options['announcement']); ?></a>
            <?php else : ?><span><?php echo esc_html($options['announcement']); ?></span><?php endif; ?>
        </div>
        <button class="dchf-dismiss-banner" type="button" aria-label="Dismiss offer banner"><?php echo dchf_icon('close'); ?></button>
    </div>
</aside>
<?php endif; ?>
<header class="dchf-shell dchf-header">
<div class="dchf-container dchf-main-row">
    <div class="dchf-brand"><?php dchf_logo($options); ?></div>
    <div class="dchf-search-wrap">
        <form class="dchf-search" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
            <label class="dchf-sr" for="dchf-search-category">Product category</label>
            <select name="product_cat" id="dchf-search-category"><option value=""><?php echo esc_html($options['category_label']); ?></option><?php foreach ($categories as $category) : ?><option value="<?php echo esc_attr($category->slug); ?>" <?php selected(get_query_var('product_cat'), $category->slug); ?>><?php echo esc_html($category->name); ?></option><?php endforeach; ?></select>
            <label class="dchf-sr" for="dchf-search-input">Search products</label>
            <input id="dchf-search-input" name="s" type="search" maxlength="80" autocomplete="off" aria-controls="dchf-search-results" aria-expanded="false" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php echo esc_attr($options['search_placeholder']); ?>">
            <input type="hidden" name="post_type" value="product">
            <button type="submit"><?php echo dchf_icon('search'); ?><span><?php echo esc_html($options['search_button']); ?></span></button>
        </form>
        <div id="dchf-search-results" class="dchf-search-results" role="region" aria-label="Product search results" hidden></div>
        <span class="dchf-sr dchf-search-status" role="status" aria-live="polite"></span>
    </div>
    <div class="dchf-actions">
        <?php $logged_in = is_user_logged_in(); $account_text = $logged_in ? 'Dashboard' : $options['account_label']; ?>
        <a class="dchf-account<?php echo $logged_in ? ' dchf-account-logged-in' : ''; ?>" href="<?php echo esc_url(dchf_account_url()); ?>" aria-label="<?php echo esc_attr($account_text); ?>"><?php echo dchf_icon('user'); ?><span class="dchf-action-text"><?php if (!$logged_in) : ?><small>Login</small><?php endif; ?><strong><?php echo esc_html($account_text); ?></strong></span></a>
        <?php if ($options['show_wishlist']) : ?>
        <?php if ($options['wishlist_url']) : ?><a class="dchf-wishlist" href="<?php echo esc_url($options['wishlist_url']); ?>" aria-label="Wishlist"><?php echo dchf_icon('heart'); ?></a>
        <?php else : ?><button class="dchf-wishlist" type="button" data-open-wishlist aria-haspopup="dialog" aria-controls="dchf-wishlist-dialog" aria-label="Open wishlist"><?php echo dchf_icon('heart'); ?><span class="dchf-wishlist-count" hidden>0</span></button><?php endif; ?>
        <?php endif; ?>
        <?php echo dchf_cart_link(); ?>
        <button class="dchf-mobile-toggle" type="button" aria-expanded="false" aria-controls="dchf-navigation" aria-label="Toggle navigation"><?php echo dchf_icon('menu'); ?></button>
    </div>
</div>
<div class="dchf-navigation-row"><div class="dchf-container dchf-nav-inner">
    <div class="dchf-category-browser"><button class="dchf-browse-toggle" type="button" aria-expanded="false" aria-controls="dchf-category-list"><?php echo dchf_icon('menu'); ?><span><?php echo esc_html($options['browse_label']); ?></span><?php echo dchf_icon('chevron'); ?></button>
        <nav id="dchf-category-list" class="dchf-category-list" aria-label="Browse product categories" hidden><ul><?php foreach ($categories as $category) : $url=get_term_link($category); if (is_wp_error($url)) continue; ?><li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($category->name); ?></a></li><?php endforeach; ?><li><a href="<?php echo esc_url(dchf_shop_url()); ?>">All digital products</a></li></ul></nav>
    </div>
    <nav id="dchf-navigation" class="dchf-navigation" aria-label="Main navigation"><?php dchf_menu('primary',[['Home',home_url('/')],['Shop',dchf_shop_url()]]); ?></nav>
    <?php if ($options['promo_text']) : ?><div class="dchf-promo dchf-coupon-promo"><?php echo dchf_icon('coupon'); ?><?php if ($options['promo_url']) : ?><a href="<?php echo esc_url($options['promo_url']); ?>"><?php echo esc_html($options['promo_text']); ?></a><?php else : ?><span><?php echo esc_html($options['promo_text']); ?></span><?php endif; ?></div><?php endif; ?>
</div></div>
</header>
