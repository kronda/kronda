<?php
/** Register nav menus. */
add_action( 'init', 'contango_register_menus' );

/** Registers the the core menus */
function contango_register_menus() {

	/* Register the 'primary' menu. */
	register_nav_menu( 'contango-primary-menu', __( 'Contango Primary Menu', 'contango' ) );
	
}