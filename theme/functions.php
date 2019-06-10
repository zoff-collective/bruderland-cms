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

  register_post_type('background',
    array(
      'labels' => array(
        'name' => 'Hintergrund',
        'singular_name' => 'Hintergrund'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array(
          'slug' => 'background'
        ),
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-media-document',
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

  if(get_post_type($post) == 'protagonists') {
    $permalink = home_url('/protagonists/'.$post->post_name);
  }

  return $permalink;
}

function exclude_episodes_from_admin($query) {
  if (!is_admin()) {
    return $query;
  }

  global $pagenow, $post_type;

  $current_user = wp_get_current_user();

  // hide the debugging episode for everyone but myself
  if ($current_user->user_login != 'gustav' && $pagenow == 'edit.php' && $post_type == 'episodes') {
    $query-> query_vars['post__not_in'] = array(216);
  }
}

if( function_exists('acf_add_options_page') ) {
  acf_add_options_page(array(
    'page_title' 	=> 'Theme General Settings',
    'menu_title'	=> 'Theme Settings',
    'menu_slug' 	=> 'theme-general-settings',
    'capability'	=> 'edit_posts',
    'redirect'		=> false
  ));
}

function netlify_states($wp_admin_bar) {
  $build_status = array(
    'id' => 'netlify-build',
    'title' => '<img src="https://api.netlify.com/api/v1/badges/944aedd3-db8f-4b5d-8b52-cc9a0db1dfa0/deploy-status" style="vertical-align: middle; margin-top: -3px;" />',
    'href' => null,
  );

  $publish = array(
    'id'    => 'netlify-publish',
    'title' => 'Update bruderland.de',
    'href'  => 'https://api.netlify.com/build_hooks/5cfd6aedd593c4f587f0f1fa',
    'meta' => array(
      'onclick' => '(function(e) {e.preventDefault(); if (window.confirm("Bruderland.de wirklich mit den aktuellen Daten updaten?")) { fetch(e.target.href, { method: "POST" }).then(() => window.location.reload())} })(event)'
    )
  );

  $wp_admin_bar->add_node($publish);
  $wp_admin_bar->add_node($build_status);
}

add_filter('acf/fields/wysiwyg/toolbars' , 'acf_toolbar');
add_filter('tiny_mce_before_init', 'tinymce_formatselect');

add_action('init', 'bruderland_register_post_types');
add_action('save_post', 'trigger_netlify_deploy');
add_action('admin_menu','cleanup_admin');
add_action('admin_head', 'remove_page_editor_support');
add_action('admin_bar_menu', 'custom_visit_site_url', 80);
add_action('admin_bar_menu', 'netlify_states', 100);
add_filter('post_type_link', 'update_post_links', 10, 2) ;
add_filter('parse_query', 'exclude_episodes_from_admin');

?>
