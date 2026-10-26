<?php defined('ABSPATH') || exit;
if (has_action('digicarts_footer')) { do_action('digicarts_footer'); } else { ?>
<footer class="container"><p>© <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?></p></footer>
<?php } wp_footer(); ?></body></html>
