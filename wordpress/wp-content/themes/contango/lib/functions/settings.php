<?php
/** Contango Default Settings. */	
function contango_settings_default()  {
	
	$default = array(
			
		'contango_nav_style' => 'numeric',
		
		'contango_post_style' => 'content',
		'contango_featured_image_control' => 'manual',
		
		'contango_copyright_control' => 0,
		'contango_copyright' => '',
		
		'contango_reset_control' => 0
		
	);
	
	return $default;
	
}

/** Loads the Contango theme setting. */
function contango_get_settings() {
	global $contango;

	/* If the settings array hasn't been set, call get_option() to get an array of theme settings. */
	if ( !isset( $contango->settings ) ) {
		$contango->settings = wp_parse_args( get_option( 'contango_options', contango_settings_default() ), contango_settings_default() );
	}
	
	/** return settings. */
	return $contango->settings;
}