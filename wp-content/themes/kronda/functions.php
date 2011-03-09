<?php

/*
	See TwentyTen functions php for original helper functions
*/

define('WP_CONTENT', get_bloginfo('wpurl').'/wp-content');
define('CHILD_TEMPLATE_DIRECTORY', dirname( get_bloginfo('stylesheet_url')) );

function add_custom_js() {
	if ( !is_admin() ) {

		wp_register_script('jquery.lightbox.min',
		       '/wp-content/themes/kronda/js/jquery.lightbox.min.js',
		       array('jquery'),
		 	   '0.5');
	
		wp_register_script('easySlider1.7',
		       '/wp-content/themes/kronda/js/easySlider1.7.js',
		       array('jquery'),
		       '1.7');
	
		wp_register_script('jquery.tweet',
		       '/wp-content/themes/kronda/js/jquery.tweet.js',
		       array('jquery'),
		       '1.0');
	
		wp_register_script('scripts',
		       '/wp-content/themes/kronda/js/scripts.js',
		       array('jquery','jquery.lightbox.min','easySlider1.7','jquery.tweet'),
		       '1.0');
	
		// enqueue the scripts
		wp_enqueue_script('jquery.lightbox.min'); 		
		wp_enqueue_script('easySlider1.7');
		wp_enqueue_script('jquery.tweet');	
		wp_enqueue_script('scripts');
	
	}
} 
   
add_action('init', 'add_custom_js');