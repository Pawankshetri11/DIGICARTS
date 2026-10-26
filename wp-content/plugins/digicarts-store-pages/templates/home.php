<?php
defined('ABSPATH') || exit;
$shop_url=wc_get_page_permalink('shop');
$categories=dsp_categories();
$category_order=['website-templates','design-assets','ebooks-guides','productivity'];
usort($categories,static function ($a,$b) use ($category_order) {
    $a_order=array_search($a->slug,$category_order,true);
    $b_order=array_search($b->slug,$category_order,true);
    return ($a_order===false?99:$a_order)<=>($b_order===false?99:$b_order);
});
$items=wc_get_products(['status'=>'publish','limit'=>4,'orderby'=>'date','order'=>'DESC','visibility'=>'catalog']);
get_header();
?>
<main id="main" class="dsp-root dsp-home" tabindex="-1">
    <section class="dh-hero" aria-labelledby="dh-title">
        <div class="dh-wrap dh-hero-grid">
            <div class="dh-hero-copy">
                <p class="dh-eyebrow"><span aria-hidden="true"></span><?php echo esc_html(dsp_home_text('dc_eyebrow','FOR THE IDEAS YOU CAN’T WAIT TO MAKE')); ?></p>
                <h1 id="dh-title"><?php echo esc_html(dsp_home_text('dc_heading','A head start for your next')); ?> <em><?php echo esc_html(dsp_home_text('dc_accent','great thing.')); ?></em></h1>
                <p class="dh-intro"><?php echo esc_html(dsp_home_text('dc_description','Templates, creative assets and a little inspiration. Find digital tools that help you turn “what if” into what’s next.')); ?></p>
                <div class="dh-actions"><a class="dsp-button" href="<?php echo esc_url($shop_url); ?>"><?php echo esc_html(dsp_home_text('dsp_home_shop_label','Explore the shop')); ?><span aria-hidden="true">↗</span></a><a class="dh-text-link" href="#home-categories">Find your category <span aria-hidden="true">↓</span></a></div>
                <div class="dh-hero-details"><span><?php echo dsp_icon('grid'); ?> Digital resources</span><span><?php echo dsp_icon('user'); ?> Guest checkout available</span></div>
            </div>
            <div class="dh-hero-visual" aria-hidden="true">
                <div class="dh-visual-heading"><span>THE DIGICARTS EDIT</span><span>GOOD TOOLS. GREAT IDEAS.</span></div>
                <div class="dh-visual-canvas">
                    <div class="dh-orbit"></div>
                    <div class="dh-hero-web"><div class="dh-window-bar"><span><i></i><i></i><i></i></span><small>your next big idea</small><b>↗</b></div><div class="dh-window-body"><div class="dh-window-nav"><strong>form & feeling.</strong><span>Work &nbsp; Studio &nbsp; Contact</span></div><p>MAKE SPACE FOR SOMETHING NEW</p><strong class="dh-window-title">Create<br>your next.</strong><div class="dh-window-bottom"><span class="dh-sculpture"></span><div><span>01 / A fresh perspective</span><b>Less ordinary.<br>More you.</b><i>Explore the possibilities ↗</i></div></div></div></div>
                    <div class="dh-hero-planner"><div class="dh-planner-binding"><i></i><i></i><i></i><i></i><i></i></div><small>ONE THING AT A TIME</small><strong>Make room<br>for good ideas.</strong><div><span>01</span> A fresh start <b>✓</b></div><div><span>02</span> A little focus <b>✓</b></div><div><span>03</span> Something yours <b>○</b></div><footer>PLAN LESS. MAKE MORE. <span>↗</span></footer></div>
                    <div class="dh-creative-stamp"><span>✳</span><small>MADE FOR<br>YOUR NEXT.</small></div>
                    <div class="dh-file-tag"><?php echo dsp_icon('download'); ?><span>A starting point.<br><strong>Endless possibilities.</strong></span></div>
                </div>
                <p class="dh-visual-caption">A little inspiration for whatever comes next. <span>↗</span></p>
            </div>
        </div>
    </section>

    <?php if ($categories) : ?>
    <section class="dh-wrap dh-section" id="home-categories" aria-labelledby="dh-category-title">
        <div class="dh-section-heading"><div><p class="dh-eyebrow">FIND YOUR STARTING POINT</p><h2 id="dh-category-title"><?php echo esc_html(dsp_home_text('dsp_home_categories_title','What will you make next?')); ?></h2></div><a class="dh-text-link" href="<?php echo esc_url($shop_url); ?>">Browse all products <span aria-hidden="true">↗</span></a></div>
        <div class="dh-categories">
            <?php foreach ($categories as $index=>$category) : $url=get_term_link($category); if (is_wp_error($url)) continue; ?>
            <a class="dh-category" href="<?php echo esc_url($url); ?>"><div class="dh-category-art"><?php dsp_art($category->slug); ?><span class="dh-category-number" aria-hidden="true"><?php echo esc_html(sprintf('%02d',$index+1)); ?></span></div><div class="dh-category-title"><h3><?php echo esc_html($category->name); ?></h3><span aria-hidden="true">↗</span></div><p><?php echo esc_html(dsp_category_copy($category->slug)); ?></p></a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($items) : ?>
    <section class="dh-products-section" aria-labelledby="dh-products-title"><div class="dh-wrap dh-section">
        <div class="dh-section-heading"><div><p class="dh-eyebrow">THE LATEST IN STORE</p><h2 id="dh-products-title"><?php echo esc_html(dsp_home_text('dsp_home_products_title','Fresh finds for your next idea.')); ?></h2></div><a class="dh-text-link" href="<?php echo esc_url(add_query_arg('orderby','date',$shop_url)); ?>">View all products <span aria-hidden="true">↗</span></a></div>
        <div class="dh-products dh-products-<?php echo esc_attr(min(4,count($items))); ?>">
            <?php foreach ($items as $item) : $demo=$item->get_meta('_dsp_demo_product')==='yes'; $art=$item->get_meta('_dsp_demo_art'); ?>
            <article class="dh-product">
                <a class="dh-product-image" href="<?php echo esc_url($item->get_permalink()); ?>" aria-label="<?php echo esc_attr($item->get_name()); ?>">
                    <?php if ($demo && $art && !$item->get_image_id()) { dsp_art($art); } else { echo $item->get_image('woocommerce_thumbnail',['loading'=>'lazy']); } ?>
                    <?php if ($demo) : ?><span class="dh-product-badge">Demo product</span><?php elseif ($item->is_on_sale()) : ?><span class="dh-product-badge">On sale</span><?php endif; ?>
                </a>
                <div class="dh-product-copy"><div class="dh-product-title"><h3><a href="<?php echo esc_url($item->get_permalink()); ?>"><?php echo esc_html($item->get_name()); ?></a></h3><span class="dh-price"><?php echo wp_kses_post($item->get_price_html()); ?></span></div><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($item->get_short_description()),19)); ?></p><div class="dh-product-actions"><a class="dh-text-link" href="<?php echo esc_url($item->get_permalink()); ?>">View details <span aria-hidden="true">↗</span></a><?php global $product; $previous_product=$product; $product=$item; woocommerce_template_loop_add_to_cart(); $product=$previous_product; ?></div></div>
            </article>
            <?php endforeach; ?>
        </div>
    </div></section>
    <?php endif; ?>

    <section class="dh-wrap dh-section dh-story" aria-labelledby="dh-story-title">
        <div class="dh-story-copy"><p class="dh-eyebrow">A LITTLE HELP. A LOT OF POSSIBILITY.</p><h2 id="dh-story-title"><?php echo esc_html(dsp_home_text('dsp_home_story_title','A little less starting from scratch.')); ?></h2><p><?php echo esc_html(dsp_home_text('dsp_home_story_text','A new website. A more focused week. An idea that finally takes shape. DigiCarts brings digital resources together so you can find your starting point and make it your own.')); ?></p><a class="dh-text-link" href="<?php echo esc_url(dsp_page_url('about-us')); ?>">Get to know DigiCarts <span aria-hidden="true">↗</span></a></div>
        <div class="dh-story-features">
            <div><span class="dh-feature-icon"><?php echo dsp_icon('grid'); ?></span><div><h3>Find your kind of useful.</h3><p>Explore templates, design assets, guides and resources for more focused work.</p></div></div>
            <div><span class="dh-feature-icon"><?php echo dsp_icon('tag'); ?></span><div><h3>Know what you’re getting.</h3><p>Check the included files, compatibility and licence details on each product.</p></div></div>
            <div><span class="dh-feature-icon"><?php echo dsp_icon('chat'); ?></span><div><h3>A question before you start?</h3><p>Tell us what you need. <a href="<?php echo esc_url(dsp_page_url('contact-us')); ?>">Get in touch <span aria-hidden="true">↗</span></a></p></div></div>
        </div>
    </section>

    <section class="dh-wrap dh-finish" aria-labelledby="dh-finish-title"><div class="dh-finish-symbol" aria-hidden="true">✳</div><div><p class="dh-eyebrow">MAKE SOMETHING YOURS</p><h2 id="dh-finish-title"><?php echo esc_html(dsp_home_text('dsp_home_cta_title','Your next good idea starts here.')); ?></h2><p><?php echo esc_html(dsp_home_text('dsp_home_cta_text','Find a resource that fits your project. Give it your own spin.')); ?></p></div><a class="dsp-button" href="<?php echo esc_url($shop_url); ?>">Explore the shop <span aria-hidden="true">↗</span></a></section>
</main>
<?php get_footer(); ?>
