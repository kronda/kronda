<?php
/**
 * child functions and definitions
 *
 * @package child
 */

/**
 * Clean up the output of the <head> block
 */
function child_clean_header() {
  remove_action( 'wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
  remove_action( 'wp_head', 'feed_links', 2 );       // Display the links to the general feeds: Post and Comment Feed
  remove_action( 'wp_head', 'rsd_link' );            // Display the link to the Really Simple Discovery service endpoint, EditURI link
  remove_action( 'wp_head', 'wlwmanifest_link' );    // Display the link to the Windows Live Writer manifest file.
  remove_action( 'wp_head', 'index_rel_link' );      // index link
  remove_action( 'wp_head', 'wp_generator' );        // Display the XHTML generator that is generated on the wp_head hook, WP version
}
add_action( 'init', 'child_clean_header' );

function child_nofollow_cat_posts($text) {
global $post;
        if( in_category(1) ) { // SET CATEGORY ID HERE
                $text = stripslashes(wp_rel_nofollow($text));
        }
        return $text;
}
add_filter('the_content', 'child_nofollow_cat_posts');

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

/** Exclude Aside category from posts */

function kronda_exclude_post_formats_from_blog( $query ) {

  if( $query->is_main_query() && $query->is_home() ) {
    $tax_query = array( array(
      'taxonomy' => 'post_format',
      'field' => 'slug',
      'terms' => array( 'post-format-quote', 'post-format-aside' ),
      'operator' => 'NOT IN',
    ) );
    $query->set( 'tax_query', $tax_query );
  }

}
add_action( 'pre_get_posts', 'kronda_exclude_post_formats_from_blog' );
 