<?php
/**
 * Pressgram
 *
 * @package   Pressgram
 * @author    yo, gg <info@press.gram>, UaMV
 * @license   GPL-2.0+
 * @link      http://pressgr.am/
 * @copyright 2013 yo, gg, UaMV
 */

/**
 * Pressgram
 *
 * Allows users to select which category that they want to use as their Pressgram category,
 * configure custom fine control settings and set active post relations. Also applies all
 * presets on XML-RPC post from the Pressgram application.
 *
 * @package Pressgram
 * @author  yo, gg <info@press.gram>
 */
class Pressgram_Role {

	/*---------------------------------------------------------------------------------*
	 * Attributes
	 *---------------------------------------------------------------------------------*/

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/*---------------------------------------------------------------------------------*
	 * Consturctor / The Singleton Pattern
	 *---------------------------------------------------------------------------------*/

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     2.1.0
	 */
	private function __construct() {

		// Prevent Pressgram contributors from logging in if not XML-RPC
		add_action( 'wp_login', array( $this, 'prevent_login' ), 10, 2 );

	} // end constructor

	/*---------------------------------------------------------------------------------*
	 * Public Functions
	 *---------------------------------------------------------------------------------*/

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		} // end if

		return self::$instance;

	} // end get_instance


	/**
	 * Saves metadata related to Pressgram post checkbox on Publicize metabox
	 *
	 * @since    2.1.0
	 */
	public function prevent_login( $user_login, $user ) {

		! defined( 'XMLRPC_REQUEST' ) && user_can( $user, 'pressgram_contribution_only' ) ? wp_logout() : FALSE;

	}
}