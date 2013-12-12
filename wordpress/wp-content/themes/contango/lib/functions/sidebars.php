<?php
/** Register widget areas. */
add_action( 'widgets_init', 'contango_register_sidebars' );

/** Registers the the core sidebars */
function contango_register_sidebars() {

	/* Get the available core framework sidebars. */
	$sidebars = contango_sidebars();
	
	/* Loop through the supported sidebars. */
	foreach ( $sidebars as $key => $val ) {
		

		/* Set up some default sidebar arguments. */
		$defaults = array(
		  'before_widget'	=> '<div id="%1$s" class="widget %2$s widget-%2$s clearfix"><div class="widget-wrap widget-inside">',
		  'after_widget'	=> '</div></div>',
		  'before_title'	=> '<h3 class="widget-title">',
		  'after_title'	=> '</h3>'
		);
		
		/* Parse the sidebar arguments and defaults. */
		$args = wp_parse_args( $sidebars[$key], $defaults );
		
		/* Register the sidebar. */
		register_sidebar( $args );
		
	}

}

/** Returns an array of the core framework's available sidebars for use in theme */
function contango_sidebars() {

	/* Set up an array of sidebars. */
	$sidebars = array(
		
		'contango-primary-sidebar' => array(
			'name' => __( 'Contango Primary Sidebar', 'contango' ),
			'id' => 'contango-primary-sidebar',
			'description' => __( 'The main (primary) widget area, most often used as a sidebar.', 'contango' )
		)
	);

	/* Return the sidebars. */
	return $sidebars;
}