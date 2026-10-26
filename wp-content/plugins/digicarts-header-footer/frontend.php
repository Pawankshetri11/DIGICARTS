<?php
defined('ABSPATH') || exit;
function dchf_icon($name) {
    $paths=[
        'coupon'=>'<g fill="currentColor" stroke="none"><circle cx="12" cy="4" r="4"/><circle cx="17.65" cy="6.35" r="4"/><circle cx="20" cy="12" r="4"/><circle cx="17.65" cy="17.65" r="4"/><circle cx="12" cy="20" r="4"/><circle cx="6.35" cy="17.65" r="4"/><circle cx="4" cy="12" r="4"/><circle cx="6.35" cy="6.35" r="4"/><circle cx="12" cy="12" r="8"/></g><g stroke="#fff" stroke-width="1.8" fill="none"><path d="m8.5 15.5 7-7"/><circle cx="8.6" cy="8.6" r="1.4"/><circle cx="15.4" cy="15.4" r="1.4"/></g>',
        'instagram'=>'<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".9" fill="currentColor" stroke="none"/>',
        'facebook'=>'<path d="M14 21v-8h3l.5-4H14V7c0-1 .5-1.5 1.5-1.5H18V2.2A26 26 0 0 0 15 2c-3 0-5 1.8-5 5v2H7v4h3v8Z" fill="currentColor" stroke="none"/>',
        'youtube'=>'<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 6 3-6 3Z" fill="currentColor" stroke="none"/>',
        'x'=>'<path d="M4 3h4l12 18h-4ZM20 3l-7 8M4 21l7-8"/>',
        'close'=>'<path d="m6 6 12 12M18 6 6 18"/>',
        'bag'=>'<path class="dchf-icon-tone" d="M6 8h12l1 11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2Z"/><path d="M6 8h12l1 11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2ZM9 9V6a3 3 0 0 1 6 0v3"/>',
        'user'=>'<circle class="dchf-icon-tone" cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="9"/><circle cx="12" cy="9" r="3"/><path d="M6.5 19a5.5 5.5 0 0 1 11 0"/>',
        'heart'=>'<path class="dchf-icon-tone" d="M12 20S3 14.7 3 8.7A4.7 4.7 0 0 1 12 7a4.7 4.7 0 0 1 9 1.7C21 14.7 12 20 12 20Z"/><path d="M12 20S3 14.7 3 8.7A4.7 4.7 0 0 1 12 7a4.7 4.7 0 0 1 9 1.7C21 14.7 12 20 12 20Z"/>',
        'chevron'=>'<path d="m8 10 4 4 4-4"/>','up'=>'<path d="M12 19V5m-5 5 5-5 5 5"/>','menu'=>'<path d="M4 7h16M4 12h11M4 17h16"/>',
        'search'=>'<circle class="dchf-icon-tone" cx="10.5" cy="10.5" r="6.5"/><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'download'=>'<rect class="dchf-icon-tone" x="4" y="14" width="16" height="7" rx="2"/><path d="M12 3v12m-4-4 4 4 4-4M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>',
        'wallet'=>'<rect class="dchf-icon-tone" x="3" y="5" width="18" height="14" rx="3"/><rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18M7 15h3"/>',
        'tag'=>'<path class="dchf-icon-tone" d="M12 3H5a2 2 0 0 0-2 2v7l9 9 9-9Z"/><path d="M12 3H5a2 2 0 0 0-2 2v7l9 9 9-9Z"/><circle cx="7.5" cy="7.5" r="1"/><path d="m11 14 2 2 4-4"/>',
        'chat'=>'<path class="dchf-icon-tone" d="M7 4h10a4 4 0 0 1 4 4v6a4 4 0 0 1-4 4h-6l-5 3v-3a4 4 0 0 1-3-4V8a4 4 0 0 1 4-4Z"/><path d="M7 4h10a4 4 0 0 1 4 4v6a4 4 0 0 1-4 4h-6l-5 3v-3a4 4 0 0 1-3-4V8a4 4 0 0 1 4-4ZM8 9h8M8 13h5"/>',
        'grid'=>'<rect class="dchf-icon-tone" x="3" y="3" width="7" height="7" rx="2"/><rect class="dchf-icon-tone" x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
    ];
    return '<svg class="dchf-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.($paths[$name]??$paths['grid']).'</svg>';
}
function dchf_cart_link() {
    $options=dchf_options(); $cart=function_exists('WC')?WC()->cart:null;
    $count=$cart?$cart->get_cart_contents_count():0;
    $total=$cart?$cart->get_cart_subtotal():(function_exists('wc_price')?wc_price(0):'');
    $url=function_exists('wc_get_cart_url')?wc_get_cart_url():dchf_shop_url();
    return '<a class="dchf-cart" aria-haspopup="dialog" aria-controls="dchf-cart-drawer" href="'.esc_url($url).'" aria-label="'.esc_attr(sprintf(_n('Shopping cart, %d item','Shopping cart, %d items',$count,'digicarts-header-footer'),$count)).'"><span class="dchf-cart-icon">'.dchf_icon('bag').'<span class="dchf-count">'.esc_html($count).'</span></span><span class="dchf-action-text"><small>'.esc_html($options['cart_label']).'</small><strong class="dchf-cart-total">'.wp_kses_post($total).'</strong></span></a>';
}
add_filter('woocommerce_add_to_cart_fragments',function ($fragments) { $fragments['.dchf-shell a.dchf-cart']=dchf_cart_link(); return $fragments; });
function dchf_logo($options,$preview=false) {
    $image_id=absint($options['logo_id'])?:absint(get_theme_mod('custom_logo'));
    echo $preview ? '<span class="dchf-logo">' : '<a class="dchf-logo" href="'.esc_url(home_url('/')).'" aria-label="'.esc_attr($options['brand'].' home').'">';
    if ($image_id) echo wp_get_attachment_image($image_id,'full',false,['alt'=>$options['brand'],'loading'=>'eager']);
    else echo '<span class="dchf-logo-mark" aria-hidden="true"><i></i><i></i><i></i><i></i></span><span>'.esc_html($options['brand']).'</span>';
    echo $preview ? '</span>' : '</a>';
}
function dchf_product_categories() {
    if (!taxonomy_exists('product_cat')) return [];
    $categories=get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>0,'orderby'=>'name']);
    if (is_wp_error($categories)) return [];
    $uncategorized=(int)get_option('default_product_cat');
    return array_values(array_filter($categories,fn($term)=>$term->term_id!==$uncategorized));
}
function dchf_menu($location,$fallback=[],$depth=2) {
    $locations=get_nav_menu_locations(); $items=isset($locations[$location])?wp_get_nav_menu_items($locations[$location]):[];
    echo '<ul class="dchf-menu">';
    if ($items) {
        $children=[]; foreach ($items as $item) $children[(int)$item->menu_item_parent][]=$item;
        $render=function ($parent,$level) use (&$render,$children,$location,$depth) {
            foreach ($children[$parent]??[] as $item) {
                $has_children=!empty($children[$item->ID])&&$level<$depth;
                echo '<li'.($has_children?' class="dchf-has-submenu"':'').'><a href="'.esc_url($item->url).'"'.($item->target==='_blank'?' target="_blank" rel="noopener noreferrer"':'').'>'.esc_html($item->title).'</a>';
                if ($has_children) {
                    $id='dchf-menu-'.sanitize_html_class($location).'-'.$item->ID;
                    echo '<button class="dchf-submenu-toggle" type="button" aria-expanded="false" aria-controls="'.esc_attr($id).'" aria-label="'.esc_attr('Open '.$item->title.' submenu').'">'.dchf_icon('chevron').'</button><ul id="'.esc_attr($id).'" class="dchf-submenu">';
                    $render($item->ID,$level+1); echo '</ul>';
                }
                echo '</li>';
            }
        }; $render(0,1);
    } else foreach ($fallback as [$label,$url]) echo '<li><a href="'.esc_url($url).'">'.esc_html($label).'</a></li>';
    echo '</ul>';
}
