<?php
/**
 * kronda functions and definitions
 *
 * @package kronda
 */

/**
 * Clean up the output of the <head> block
 */
function kronda_clean_header() {
  remove_action( 'wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
  remove_action( 'wp_head', 'feed_links', 2 );       // Display the links to the general feeds: Post and Comment Feed
  remove_action( 'wp_head', 'rsd_link' );            // Display the link to the Really Simple Discovery service endpoint, EditURI link
  remove_action( 'wp_head', 'wlwmanifest_link' );    // Display the link to the Windows Live Writer manifest file.
  remove_action( 'wp_head', 'index_rel_link' );      // index link
  remove_action( 'wp_head', 'wp_generator' );        // Display the XHTML generator that is generated on the wp_head hook, WP version
}
add_action( 'init', 'kronda_clean_header' );

function kronda_nofollow_cat_posts($text) {
global $post;
        if( in_category(1) ) { // SET CATEGORY ID HERE
                $text = stripslashes(wp_rel_nofollow($text));
        }
        return $text;
}
add_filter('the_content', 'kronda_nofollow_cat_posts');

function kronda_add_body_class( $classes ) {
  global $post;
  if ( isset( $post ) ) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  if ( !is_front_page() ) {
    $classes[] = 'not-home';
  }
  return $classes;
}

add_filter( 'body_class', 'kronda_add_body_class' );

function kronda_add_facebook_meta_tags() {
  echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />';
  echo '<meta property="og:title" content="' . bloginfo('name') . '"/>';
}

add_action('wp_head', 'kronda_add_facebook_meta_tags');