<?php
/**
 * Pressgram
 *
 * The official WordPress plugin for Pressgram. De-clutters the home page / home screen from all those awesome images
 * you're posting! Auto-categorizes images uploaded to your site from Pressgram and applies presets to these posts.
 * Power tags allow you to control posting options directly from the app. Includes a widget to display your photos.
 * Oh, and it also encourage digital publishers to be rebels with a cause. Viva la revolución!
 *
 * @package   Pressgram
 * @author    yo, gg <info@press.gram>, UaMV
 * @license   GPL-2.0+
 * @link      http://blog.pressgr.am/support/
 * @copyright 2013 yo, gg, UaMV
 *
 * @wordpress-plugin
 * Plugin Name: Pressgram
 * Plugin URI:  http://blog.pressgr.am/support/
 * Description: The official WordPress plugin for <a href="http://pressgr.am/">Pressgram</a> helps you publish pictures worth 1,000 words. Be a <strong>rebel with a cause</strong>. Viva la revolución!
 * Version:     2.2.3
 * Author:      yo, gg & UaMV
 * Text Domain: pressgram-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} // end if

/************************************
* Define static variables
************************************/
// Set PRESSGRAM_LOCAL to true to check processing on localhost.
// Instead of processing any xml-rpc post, it will process posts transitioning from draft to publish
! defined( 'PRESSGRAM_LOCAL' ) ? define( 'PRESSGRAM_LOCAL', FALSE ) : FALSE;
! defined( 'PRESSGRAM_RESTRICTION') ? define( 'PRESSGRAM_RESTRICTION', '' ) : FALSE;
! defined( 'PRESSGRAM_RESTRICT_TO_XMLRPC' ) ? define( 'PRESSGRAM_RESTRICT_TO_XMLRPC', TRUE ) : FALSE;

define( 'PRESSGRAM_VERSION', '2.2.3' );
define( 'PRESSGRAM_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRESSGRAM_DIR_URL', plugin_dir_url( __FILE__ ) );

/************************************
* Include files
************************************/
require_once( PRESSGRAM_DIR_PATH . 'class-pressgram.php' );
//require_once( PRESSGRAM_DIR_PATH . 'class-pressgram-role.php' ); // for possible further development
require_once( PRESSGRAM_DIR_PATH . 'class-widget.php' );

// admin files
is_admin() ? require_once( PRESSGRAM_DIR_PATH . 'wp-side-notice/class-wp-side-notice.php' ) : FALSE;
is_admin() ? require_once( PRESSGRAM_DIR_PATH . 'class-pressgram-admin.php' ) : FALSE;

// xml-rpc files
! PRESSGRAM_RESTRICT_TO_XMLRPC || defined( 'XMLRPC_REQUEST' ) || PRESSGRAM_LOCAL ? require_once( PRESSGRAM_DIR_PATH . 'class-pressgram-engine.php' ) : FALSE;

/************************************
* Get class instances
************************************/
Pressgram::get_instance();
//Pressgram_Role::get_instance(); // for possible further development

// admin class
is_admin() ? Pressgram_Admin::get_instance() : FALSE;

// xml-rpc class
! PRESSGRAM_RESTRICT_TO_XMLRPC || defined( 'XMLRPC_REQUEST' ) || PRESSGRAM_LOCAL ? Pressgram_Engine::get_instance() : FALSE;