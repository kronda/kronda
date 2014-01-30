<?php
/**
 * This is child themes functions.php file. All modifications should be made in this file.
 *
 * All style changes should be in child themes style.css file.
 *
 * @package    Kepler
 * @version    1.0.1
 * @author     Ruairi Phelan <rory@cyberdesigncraft.com>
 * @copyright  Copyright (c) 2013, Ruairi Phelan
 * @link       http://cyberdesigncraft.com/about/
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Adds the child theme setup function to the 'after_setup_theme' hook. */
add_action( 'after_setup_theme', 'kepler_theme_setup', 11 );

/**
 * Setup function.  All child themes should run their setup within this function.  The idea is to add/remove 
 * filters and actions after the parent theme has been set up.  This function provides you that opportunity.
 *
 * @since  1.0
 * @access public
 * @return void
 */
function kepler_theme_setup() {

	/* Change default background color. */
	add_theme_support(
	'custom-header',
	array(
		'default-image'      => '',
		'default-text-color' => '272727',
	));

	add_theme_support(
	'custom-background',
	array(
		'default-color' => 'eeeeee',
		'default-image' => '',
	));
	
	/* Change primary color. */
	add_filter( 'theme_mod_color_primary', 'kepler_primary_color' );
	
}

/**
 * Change primary color
 *
 * @since 1.0
 * @access public
 * @param  string  $hex
 * @return string
 */
function kepler_primary_color( $color ) {

	return $color ? $color : '272727';
	
}
