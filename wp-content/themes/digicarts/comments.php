<?php defined('ABSPATH') || exit; if (post_password_required()) return; ?>
<section class="comments-area"><?php if (have_comments()) : ?><h2>Conversation</h2><ol class="comment-list"><?php wp_list_comments(['style'=>'ol','short_ping'=>true]); ?></ol><?php the_comments_navigation(); endif; comment_form(); ?></section>
