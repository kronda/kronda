<?php
/** Primary Menu Callback */
function contango_primary_menu_cb() {
	$args = array(
		'menu_class' => 'menu clearfix'
	);
	wp_page_menu( $args );		 
}

/** Primary Menu */
if ( has_nav_menu( 'contango-primary-menu' ) ) {

  $args = array(
  
	  'container' => 'div', 
	  'container_class' => 'menu clearfix', 
	  'theme_location' => 'contango-primary-menu',
	  'menu_class' => 'sf-menu',
	  'depth' => 5,
	  'fallback_cb' => 'contango_primary_menu_cb'
			  
  );

  wp_nav_menu( $args );

} else {

  contango_primary_menu_cb();	

}
?>