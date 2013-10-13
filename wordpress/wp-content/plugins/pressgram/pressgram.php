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
 * @link      http://support.pressgr.am/
 * @copyright 2013 yo, gg, UaMV
 *
 * @wordpress-plugin
 * Plugin Name: Pressgram
 * Plugin URI:  http://support.pressgr.am/
 * Description: The official WordPress plugin for <a href="http://pressgr.am/">Pressgram</a>. De-clutters the home page / home screen from all those awesome images you're posting! Auto-categorizes images uploaded to your site from Pressgram and applies presets to these posts. Power tags allow you to control posting options directly from the app. Includes a widget to display your photos. Oh, and it also encourage digital publishers to be <strong>rebels with a cause</strong>. Viva la revolución!
 * Version:     2.0.4
 * Author:      <a href="http://yo.gg/">yo, gg</a> & UaMV
 * Text Domain: pressgram-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} // end if

register_deactivation_hook( __FILE__, array( 'Pressgram', 'remove_plugin_option' ) );

require_once( plugin_dir_path( __FILE__ ) . 'class-pressgram.php' );
Pressgram::get_instance();

require_once( plugin_dir_path( __FILE__ ) . 'class-widget.php' );