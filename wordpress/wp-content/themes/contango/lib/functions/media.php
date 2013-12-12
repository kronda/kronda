<?php
/** Register Contango Core scripts. */
add_action( 'wp_enqueue_scripts', 'contango_register_scripts', 1 );

/** Load Contango Core scripts. */
add_action( 'wp_enqueue_scripts', 'contango_enqueue_scripts' );

/** Register JavaScript and Stylesheet files for the framework. */
function contango_register_scripts() {

	/** Register the 'Superfish Plugin' scripts. */
	wp_register_script( 'contango-js-superfish', esc_url( CONTANGO_JS_URI . 'superfish/superfish-combine.min.js' ), array( 'jquery' ), '1.5.9', true );
	
	/** Register the 'common' scripts. */
	wp_register_script( 'contango-js-common', esc_url( CONTANGO_JS_URI . 'common.js' ), array( 'jquery' ), '1.0', true );
	
	/** Register '960.css' for grid. */
	wp_register_style( 'contango-css-960', esc_url( CONTANGO_CSS_URI . '960.css' ) );
	
	/** Register Google Fonts. */
	$protocol = is_ssl()? 'https' : 'http';
	wp_register_style( 'contango-google-fonts', esc_url( $protocol . '://fonts.googleapis.com/css?family=Open+Sans|Nixie+One' ) );
}

/** Tells WordPress to load the scripts needed for the framework using the wp_enqueue_script() function. */
function contango_enqueue_scripts() {

	/** Load the comment reply script on singular posts with open comments if threaded comments are supported. */
	if ( is_singular() && get_option( 'thread_comments' ) && comments_open() ) {
		wp_enqueue_script( 'comment-reply' );
	}

	/** Load the 'Superfish Plugin' scripts. */
	wp_enqueue_script( 'contango-js-superfish' );
	
	/** Load the 'common' scripts. */
	wp_enqueue_script( 'contango-js-common' );
	
	/** Load '960.css' for grid. */
	wp_enqueue_style( 'contango-css-960' );
	
	/** Load Google Fonts. */
	wp_enqueue_style( 'contango-google-fonts' );
}