<?php

$PRODUCTION_URL = 'https://develop--bruderland.netlify.com';

function bruderland_register_post_types() {
  register_post_type('episodes',
    array(
      'labels' => array(
        'name' => 'Episoden',
        'singular_name' => 'Episode'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array(
          'slug' => 'episodes'
        ),
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-images-alt',
        'supports' => array(
          'title',
          'thumbnail',
          'revisions',
        )
    )
  );

  register_post_type('protagonists',
    array(
      'labels' => array(
        'name' => 'Protagonist*innen',
        'singular_name' => 'Protagonist*in'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array(
          'slug' => 'protagonists'
        ),
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-admin-users',
        'supports' => array(
          'title',
          'revisions',
        )
    )
  );

  register_post_type('countries',
    array(
      'labels' => array(
        'name' => 'Länder',
        'singular_name' => 'Land'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array(
          'slug' => 'country'
        ),
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-admin-site',
        'supports' => array(
          'title',
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
  remove_menu_page('edit-comments.php');
}

// Register custom toolbars
function acf_toolbar($toolbars) {
  $toolbars['Richtext'] = array();
  $toolbars['Richtext'][1] = array('formatselect', 'link', 'undo', 'redo');

  unset($toolbars['Basic']);

  $toolbars['Basic'] = array();
  $toolbars['Basic'][1] = array('link', 'undo', 'redo');

  return $toolbars;
}

// Reduce formatselect to viable options
function tinymce_formatselect($settings) {
  $settings['block_formats'] = 'Absatz=p;Überschrift(1)=h2;Überschrift(2)=h3';

  return $settings;
}

function remove_page_editor_support() {
  remove_post_type_support('page', 'editor');
}

function custom_visit_site_url($wp_admin_bar) {
  global $PRODUCTION_URL;

  // Get a reference to the view-site node to modify.
  $node = $wp_admin_bar->get_node('view-site');

  $node->meta['target'] = '_blank';
  $node->meta['rel'] = 'noopener noreferrer';
  $node->href = $PRODUCTION_URL;

  $wp_admin_bar->add_node($node);

  // Site name node
  $node = $wp_admin_bar->get_node('site-name');

  $node->meta['target'] = '_blank';
  $node->meta['rel'] = 'noopener noreferrer';
  $node->href = $PRODUCTION_URL;

  $wp_admin_bar->add_node($node);
}

function update_post_links($permalink, $post) {
  global $PRODUCTION_URL;

  if(get_post_type($post) == 'episodes') {
    $permalink = home_url('/episodes/'.$post->post_name);
  }

  return $permalink;
}

add_filter('acf/fields/wysiwyg/toolbars' , 'acf_toolbar');
add_filter('tiny_mce_before_init', 'tinymce_formatselect');

add_action('init', 'bruderland_register_post_types');
add_action('save_post', 'trigger_netlify_deploy');
add_action('admin_menu','cleanup_admin');
add_action('admin_head', 'remove_page_editor_support');
add_action('admin_bar_menu', 'custom_visit_site_url', 80);
add_filter('post_type_link', 'update_post_links', 10, 2) ;

?>
