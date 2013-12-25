<?php
/*
**************************************************************************
Plugin Name:    WP-Syntax Editor Integration Plugin
Plugin URI:     http://www.effinger.org/blog/2009/12/30/wp-syntax-editor-integration-plugin-wp-syntax-im-wordpress-editor-nutzen/
Description:    Integrates WP-Syntax into the visual and html editor of WordPress by adding a button in each editor mode
Version:        0.2
Author:         Markus Effinger
Author URI:     http://www.effinger.org/
**************************************************************************

Copyright (C) 2009 Markus Effinger

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*************************************************************************
*/

// Make sure the TinyMCE editor is okay with "pre" tag containing lang and line attributes
function wp_syntax_quicktag_tiny_mce_before_init($init) {
    $init['extended_valid_elements'] .= ',pre[escaped=true|lang|line]';
    return $init;
}

// Add javascript if quicktags are shown on page (will finally add our button and the code to execute when we click on it)
function wpsyntaxintegration_addquicktags() {
	wp_enqueue_script('jquery');
	wp_enqueue_script(
		'wp_syntax_quickcode',
		plugin_dir_url(__FILE__) . 'wp-syntax-quicktag.js',
		array('quicktags')
	);
}

function wpsyntaxintegration_addbuttons() {
   // Don't bother doing this stuff if the current user lacks permissions
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
 
   // Add only in Rich Editor mode
   if ( get_user_option('rich_editing') == 'true') {
     add_filter("mce_external_plugins", "add_wpsyntaxintegration_tinymce_plugin");
     add_filter('mce_buttons', 'register_wpsyntaxintegration_button');
   }
}
 
function register_wpsyntaxintegration_button($buttons) {
   array_push($buttons, "separator", "wpsyntaxintegration");
   return $buttons;
}
 
// Load the TinyMCE plugin
function add_wpsyntaxintegration_tinymce_plugin($plugin_array) {
   $plugin_array['wpsyntaxintegration'] = plugin_dir_url(__FILE__) . 'wp-syntax-tinymce.js';
   return $plugin_array;
}
 
// init process for button control
add_action( 'init', 'wpsyntaxintegration_addbuttons' );
// load quicktag javascript if needed - why is there no separate action in the wordpress API?
if ( in_array( $pagenow , array('post.php', 'post-new.php', 'page.php', 'page-new.php') ) ) {
	add_action('admin_print_scripts', 'wpsyntaxintegration_addquicktags');
}
// TinyMCE Editor Adjustments (allow pre tag extensions)
add_filter( 'tiny_mce_before_init', 'wp_syntax_quicktag_tiny_mce_before_init' );

?>
