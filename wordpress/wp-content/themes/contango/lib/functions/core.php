<?php
/** Function for setting the content width of a theme. */
function contango_set_content_width( $width = '' ) {
	global $content_width;
	$content_width = absint( $width );
}

/** Function for getting the theme's content width. */
function contango_get_content_width() {
	global $content_width;
	return $content_width;
}

/** Function for getting the theme's data */
function contango_theme_data() {
	global $contango;
	
	/** If the parent theme data isn't set, let grab it. */
	if ( empty( $contango->theme_data ) ) {
		
		$contango_theme_data = array();
		$theme_data = wp_get_theme( 'contango' );
		$contango_theme_data['Name'] = $theme_data->get( 'Name' );
		$contango_theme_data['ThemeURI'] = $theme_data->get( 'ThemeURI' );
		$contango_theme_data['AuthorURI'] = $theme_data->get( 'AuthorURI' );
		$contango_theme_data['Description'] = $theme_data->get( 'Description' );
		
		$contango->theme_data = $contango_theme_data;
	
	}

	/** Return the parent theme data. */
	return $contango->theme_data;
}