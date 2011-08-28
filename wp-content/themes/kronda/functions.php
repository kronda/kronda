<?php

/*
	See TwentyTen functions php for original helper functions
*/

define('WP_CONTENT', get_bloginfo('wpurl').'/wp-content');
define('CHILD_TEMPLATE_DIRECTORY', dirname( get_bloginfo('stylesheet_url')) );

function add_custom_js() {
	if ( !is_admin() ) {

		wp_register_script('jquery.flexslider-min',
		       '/wp-content/themes/kronda/js/jquery.flexslider-min.js',
		       array('jquery'),
		 	   '1.4');
		
			wp_register_script('flexie.min',
			       '/wp-content/themes/kronda/js/flexie.min.js',
			       array('jquery'),
			 	   '1.0.3');
		
		wp_register_script('jquery.tweet',
		       '/wp-content/themes/kronda/js/jquery.tweet.js',
		       array('jquery'),
		       '1.0');
	
		wp_register_script('scripts',
		       '/wp-content/themes/kronda/js/scripts.js',
		       array('jquery','jquery.tweet'),
		       '1.0');
	
		// enqueue the scripts
		wp_enqueue_script('jquery.flexslider-min');
		wp_enqueue_script('flexie.min'); 		
		wp_enqueue_script('jquery.tweet');	
		wp_enqueue_script('scripts');
	
	}
} 
 
function ie7_update(){
	echo '<!--[if lt IE 8]>
<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE8.js"></script>
<![endif]-->';
}

 
add_action('init', 'add_custom_js');
add_action('wp_head','ie7_update');