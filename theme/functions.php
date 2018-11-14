<?php

function bruderland_register_post_types() {
  register_post_type('episodes',
    array(
      'labels' => array(
        'name' => 'Episodes',
        'singular_name' => 'Episode'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array(
          'slug' => 'episodes'
        ),
        'show_in_rest' => true,
        'supports' => array(
          'title',
          'thumbnail',
          'revisions',
        )
    )
  );
}

// see https://plugins.trac.wordpress.org/browser/wp-gatsby/trunk/class-wp-gatsby.php
function trigger_netlify_deploy() {
  wp_remote_post('https://api.netlify.com/build_hooks/5be58c5573f2cf648d1dacd5');
}

function cleanup_admin() {
  remove_menu_page('edit.php');
  remove_menu_page('edit.php?post_type=page');
  remove_menu_page('edit-comments.php');
  remove_menu_page('upload.php');
}

add_theme_support('post-thumbnails');

add_action('init', 'bruderland_register_post_types');
add_action('save_post', 'trigger_netlify_deploy');
add_action('admin_menu','cleanup_admin');

?>
