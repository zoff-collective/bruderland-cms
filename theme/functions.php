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

add_action('init', 'bruderland_register_post_types');
add_theme_support('post-thumbnails'); 

?>
