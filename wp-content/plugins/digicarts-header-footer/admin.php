<?php
defined('ABSPATH') || exit;
add_action('admin_menu',function () {
    add_menu_page('DIGICARTS Header & Footer','Header & Footer','manage_options','digicarts-header-footer','dchf_admin_page','dashicons-editor-kitchensink',59);
    add_submenu_page('digicarts-header-footer','Header & Footer settings','Settings','manage_options','digicarts-header-footer','dchf_admin_page');
});

/** Only settings used by the current storefront are exposed in the editor. */
function dchf_editor_sections() {
    return [
        'brand'=>['title'=>'Brand & style','description'=>'Your logo, brand color and layout.','fields'=>[
            'brand'=>['Store name','text'],
            'logo_id'=>['Current logo','image','This logo appears in your header and footer. Change it whenever you need.'],
            'accent'=>['Brand color','color','Used for buttons, links and highlights.'],
            'logo_width'=>['Logo image width','number','Width in pixels. Applies to an uploaded logo.'],
            'max_width'=>['Content width','number','Footer content width in pixels. The header has 160px of extra space.'],
        ]],
        'header'=>['title'=>'Header','description'=>'Search and the coupon message beside your navigation.','fields'=>[
            'menu_primary'=>['Header menu','menu','Select the menu shown below the search bar.'],
            'search_placeholder'=>['Search placeholder','text'],
            'browse_label'=>['Browse categories label','text'],
            'promo_text'=>['Coupon message','text','For example: 10% OFF • Use code SAVE10. Leave empty to hide it.'],
            'promo_url'=>['Coupon link','url','Optional destination when someone clicks the message.'],
        ]],
        'footer'=>['title'=>'Footer','description'=>'Store description and your three footer menus: Collections, Policies and Your space.','fields'=>[
            'about_title'=>['Short headline','text'],
            'about_description'=>['Store description','textarea'],
            'collections_title'=>['Collections heading','text'],
            'menu_dchf_collections'=>['Collections menu','menu'],
            'info_title'=>['Policies heading','text'],
            'menu_dchf_information'=>['Policies menu','menu'],
            'quick_title'=>['Your space heading','text'],
            'menu_dchf_quick'=>['Your space menu','menu'],
            'copyright'=>['Copyright','text','Use {year} for the current year and {site} for your store name.'],
            'show_back_top'=>['Floating back-to-top button','checkbox'],
        ]],
        'socials'=>['title'=>'Social icons','description'=>'Connect your profiles. Empty links keep the placeholder icons.','fields'=>[
            'show_socials'=>['Show social icons','checkbox'],
            'instagram_url'=>['Instagram','url'], 'facebook_url'=>['Facebook','url'],
            'youtube_url'=>['YouTube','url'], 'x_url'=>['X','url'],
        ]],
        'payments'=>['title'=>'Checkout logos','description'=>'The Simple checkout block in your footer.','fields'=>[
            'show_checkout'=>['Show checkout logos','checkbox'],
            'checkout_title'=>['Section heading','text'],
            'payment_image_id'=>['Custom payment image','image','Optional. Leave empty to show UPI, Visa, RuPay and Mastercard in one row.'],
            'payment_label'=>['Custom image description','text','Alternative text for an uploaded payment image.'],
        ]],
    ];
}
function dchf_sanitize_settings($input) {
    $input = is_array($input) ? $input : [];
    // Never blank settings from other sections or removed controls when this form is saved.
    $output = dchf_options();
    $locations = get_nav_menu_locations();
    $updated_locations = $locations;
    foreach (dchf_editor_sections() as $section) {
        foreach ($section['fields'] as $key=>$field) {
            if (!array_key_exists($key,$input) || !is_scalar($input[$key])) continue;
            $value = $input[$key]; $type = $field[1];
            if ($type === 'menu') {
                if (!current_user_can('edit_theme_options')) continue;
                $menu_id = absint($value);
                if (!$menu_id || wp_get_nav_menu_object($menu_id)) $updated_locations[substr($key,5)] = $menu_id;
                continue;
            }
            if ($type === 'checkbox') $output[$key] = empty($value) ? 0 : 1;
            elseif ($type === 'image') $output[$key] = absint($value);
            elseif ($key === 'logo_width') $output[$key] = max(80,min(320,absint($value)));
            elseif ($key === 'max_width') $output[$key] = max(1000,min(2000,absint($value)));
            elseif ($type === 'color') $output[$key] = sanitize_hex_color($value) ?: $output[$key];
            elseif ($type === 'url') $output[$key] = esc_url_raw($value,['http','https']);
            elseif ($type === 'email') $output[$key] = sanitize_email($value);
            elseif ($type === 'textarea') $output[$key] = sanitize_textarea_field($value);
            else $output[$key] = sanitize_text_field($value);
        }
    }
    if ($updated_locations !== $locations) set_theme_mod('nav_menu_locations',$updated_locations);
    return $output;
}
add_action('admin_init',function () { register_setting('dchf_group','dchf_settings',['sanitize_callback'=>'dchf_sanitize_settings','type'=>'array','default'=>dchf_defaults()]); });
add_action('admin_enqueue_scripts',function ($hook) {
    if ($hook !== 'toplevel_page_digicarts-header-footer') return;
    wp_enqueue_media();
    wp_enqueue_style('dchf-admin',DCHF_URL.'assets/admin.css',[],filemtime(DCHF_PATH.'assets/admin.css'));
    wp_enqueue_script('dchf-admin',DCHF_URL.'assets/admin.js',['jquery'],filemtime(DCHF_PATH.'assets/admin.js'),true);
});
function dchf_field($key,$label,$type='text',$help='') {
    $options=dchf_options(); $value=$options[$key]??''; $id='dchf-'.$key; $name='dchf_settings['.$key.']';
    $described = $help ? ' aria-describedby="'.esc_attr($id.'-help').'"' : '';
    $group_class = in_array($key,['collections_title','info_title','quick_title'],true) ? ' dchf-menu-heading-field' : '';
    echo '<div class="dchf-field dchf-field-'.esc_attr($type).$group_class.'"><label for="'.esc_attr($id).'">'.esc_html($label).'</label><div class="dchf-field-control">';
    if ($type === 'menu') {
        $locations = get_nav_menu_locations();
        $value = absint($locations[substr($key,5)] ?? 0);
        $menus = wp_get_nav_menus();
        echo '<select id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.$described.'><option value="0">Use default links</option>';
        foreach ($menus as $menu) echo '<option value="'.absint($menu->term_id).'" '.selected($value,$menu->term_id,false).'>'.esc_html($menu->name).'</option>';
        echo '</select><div class="dchf-menu-actions">';
        $edit_url = $value ? add_query_arg(['action'=>'edit','menu'=>$value],admin_url('nav-menus.php')) : admin_url('nav-menus.php');
        echo '<a class="dchf-edit-menu" data-menu-base="'.esc_url(admin_url('nav-menus.php')).'" href="'.esc_url($edit_url).'" target="_blank" rel="noopener">Edit menu links ↗</a><a href="'.esc_url(add_query_arg(['action'=>'edit','menu'=>0],admin_url('nav-menus.php'))).'" target="_blank" rel="noopener">Create a menu ↗</a></div>';
    } elseif ($type === 'checkbox') {
        echo '<input type="hidden" name="'.esc_attr($name).'" value="0"><input type="checkbox" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="1" '.checked($value,1,false).$described.'><span class="dchf-toggle-label">Enabled</span>';
    } elseif ($type === 'textarea') {
        echo '<textarea rows="3" id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.$described.'>'.esc_textarea($value).'</textarea>';
    } elseif ($type === 'image') {
        $is_logo = $key === 'logo_id';
        $fallback = '<span>No image selected</span>';
        if ($is_logo) {
            $default_logo_options = $options;
            $default_logo_options['logo_id'] = 0;
            ob_start();
            dchf_logo($default_logo_options,true);
            $fallback = ob_get_clean();
        } elseif ($key === 'payment_image_id') {
            $fallback = '<div class="dchf-editor-payment-preview" aria-label="Default payment logos">';
            foreach (['upi'=>'UPI','visa'=>'Visa','rupay'=>'RuPay','mastercard'=>'Mastercard'] as $mark=>$payment_name) {
                $fallback .= '<img src="'.esc_url(DCHF_URL.'assets/payments/'.$mark.'.svg').'" width="76" height="34" alt="'.esc_attr($payment_name).'">';
            }
            $fallback .= '</div>';
        }
        $preview = $value ? wp_get_attachment_image(absint($value),'medium') : $fallback;
        echo '<div class="dchf-image-field"><template class="dchf-image-fallback">'.$fallback.'</template><input type="hidden" name="'.esc_attr($name).'" value="'.absint($value).'"><div class="dchf-image-preview">'.$preview.'</div><div class="dchf-image-actions"><button type="button" id="'.esc_attr($id).'" class="button dchf-select-image"'.$described.'>'.($is_logo?'Change logo':'Choose image').'</button><button type="button" class="button-link-delete dchf-remove-image"'.(!$value?' hidden':'').'>'.($is_logo?'Restore default logo':'Remove').'</button></div></div>';
    } else {
        $limits = $key==='logo_width' ? ' min="80" max="320" step="1"' : ($key==='max_width' ? ' min="1000" max="2000" step="1"' : '');
        echo '<input type="'.esc_attr($type).'" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="'.esc_attr($value).'"'.$limits.$described.($type==='url'?' placeholder="https://"':'').'>';
        if ($type==='color') echo '<output class="dchf-color-value" for="'.esc_attr($id).'">'.esc_html($value).'</output>';
    }
    if ($help) echo '<p class="description" id="'.esc_attr($id.'-help').'">'.esc_html($help).'</p>';
    echo '</div></div>';
}
function dchf_admin_page() {
    if (!current_user_can('manage_options')) return;
    $sections = dchf_editor_sections();
    ?>
    <div class="wrap dchf-admin">
        <div class="dchf-admin-heading"><div><span class="dchf-editor-eyebrow">DIGICARTS / STORE EDITOR</span><h1>Header & Footer</h1><p>Keep your storefront looking the way you want.</p></div><a class="button dchf-preview-link" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">View website <span aria-hidden="true">↗</span></a></div>
        <?php settings_errors(); ?>
        <form id="dchf-settings-form" action="options.php" method="post">
            <?php settings_fields('dchf_group'); ?>
            <div class="dchf-editor-layout">
                <nav class="dchf-admin-nav" aria-label="Editor sections">
                    <?php foreach ($sections as $key=>$section) : ?><a id="dchf-tab-<?php echo esc_attr($key); ?>" href="#dchf-<?php echo esc_attr($key); ?>" data-panel="dchf-<?php echo esc_attr($key); ?>"><?php echo esc_html($section['title']); ?><span aria-hidden="true">›</span></a><?php endforeach; ?>
                </nav>
                <div class="dchf-editor-panels">
                    <?php foreach ($sections as $key=>$section) : ?>
                    <section id="dchf-<?php echo esc_attr($key); ?>" class="dchf-admin-card" aria-labelledby="dchf-tab-<?php echo esc_attr($key); ?>">
                        <div class="dchf-panel-heading"><h2><?php echo esc_html($section['title']); ?></h2><p><?php echo esc_html($section['description']); ?></p></div>
                        <?php if ($key==='header') : ?><p class="dchf-editor-note">Sticky navigation, AJAX search and the side cart drawer are already enabled. Signed-in customers see the Dashboard link.</p><?php endif; ?>
                        <?php foreach ($section['fields'] as $field_key=>$field) dchf_field($field_key,$field[0],$field[1],$field[2]??''); ?>
                        <?php if ($key==='payments') : ?><p class="dchf-editor-note">These logos are displayed in the footer. Manage payment methods in <a href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=checkout')); ?>">WooCommerce payment settings</a>.</p><?php endif; ?>
                    </section>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="dchf-save-bar"><p id="dchf-save-status" role="status" aria-live="polite">Changes across all tabs are saved together.</p><?php submit_button('Save changes','primary','submit',false); ?></div>
        </form>
    </div>
    <?php
}
