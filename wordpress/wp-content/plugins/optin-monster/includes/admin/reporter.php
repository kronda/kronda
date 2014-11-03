<?php
/**
 * Reporter class
 *
 * @since   2.1.0
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@channeleaton.com>
 */
class Optin_Monster_Reporter {

	/**
	 * @var array
	 * @since 2.1.0
	 */
	protected $data;

	/**
	 * @var array
	 * @since 2.1.0
	 */
	protected $auth;

	/**
	 * @var object
	 * @since 2.1.0
	 */
	protected $base;

	/**
	 * @var object
	 * @since 2.1.0
	 */
	protected $common;

	/**
	 * @var object
	 * @since 2.1.0
	 */
	protected $admin;

	public function __construct() {

		// Get some useful class instances.
		$this->base   = Optin_Monster::get_instance();
		$this->common = Optin_Monster_Common::get_instance();
		$this->admin  = Optin_Monster_Menu_Admin::get_instance();

		// Make sure get_plugins() is available.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// See if we've done this before and save the auth info.
		$this->auth = get_option( 'awesomemotive_auth' );

		// Find out what kind of request we'll be making.
		$this->request_type = $this->get_request_type();

		// Gather the required data.
		$this->data = $this->gather_data();

	}

	/**
	 * Send the data to the collection server
	 *
	 * @since 2.1.0
	 * @return array|mixed
	 */
	public function send() {

		// Set the destination URL.
		$url = 'https://trends.optinmonster.com/api/v1/site';

		// Bring in the data.
		$body = $this->data;

		// Add the site ID to the URL and token to the body if we're updating.
		if ( 'PUT' == $this->request_type ) {
			$url           = $url . '/' . $this->auth['id'];
			$body['token'] = $this->auth['token'];
		}

		// JSON encode the body for sending.
		$body = json_encode( $body );

		// Setup the request arguments.
		$args = array(
			'method'  => $this->request_type,
			'headers' => array(
				'Content-Type'   => 'application/json',
				'Content-Length' => strlen( $body ),
			),
			'body'    => $body,
		);

		// Disable SSL checking.
		add_filter( 'http_request_args', array( $this, 'http_request_args' ), 10, 2 );

		// Onward!
		$response = wp_remote_request( $url, $args );

		// Decode the returned data.
		$returned = json_decode( wp_remote_retrieve_body( $response ) );

		// Save the auth data if it was a first-time request.
		if ( 'POST' == $this->request_type && property_exists( $returned, 'data' ) ) {
			update_option( 'awesomemotive_auth', (array) $returned->data );
		}

		return $returned;

	}

	/**
	 * Public getter method for the site data.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	public function getData() {

		return $this->data;

	}

	/**
	 * Returns the type of request to use.
	 *
	 * @since 2.1.0
	 * @return string
	 */
	private function get_request_type() {

		return false === $this->auth ? 'POST' : 'PUT';

	}

	/**
	 * Gathers the required data for the request.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	private function gather_data() {

		// Collect the site data.
		$site = array(
			'name'         => get_bloginfo( 'name' ),
			'url'          => get_bloginfo( 'url' ),
			'email'        => get_bloginfo( 'admin_email' ),
			'wp_version'   => get_bloginfo( 'version' ),
			'active_theme' => get_template(),
			'multisite'    => is_multisite(),
		);

		// Collect the product data.
		$product = array(
			'name'         => 'OptinMonster',
			'version'      => $this->base->version,
			'license'      => $this->base->get_license_key(),
			'addons'       => $this->get_active_addons(),
			'integrations' => $this->get_providers(),
			'optins'       => $this->get_optins(),
		);

		// Collect the server data.
		$server = array(
			'operating_system' => ( function_exists( 'php_uname' ) ? php_uname( 's' ) : 'Unknown' ),
			'server'           => ( isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown' ),
			'php_version'      => ( defined( 'PHP_VERSION' ) ? PHP_VERSION : 'Unknown' ),
		);

		// Collect the plugin data.
		$plugins = $this->get_plugins();

		// Put all of that data into one array.
		return array(
			'site'    => $site,
			'product' => $product,
			'server'  => $server,
			'plugins' => $plugins,
		);

	}

	/**
	 * Collects the active plugin data.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	private function get_plugins() {

		// Get installed plugins.
		$all_plugins = get_plugins();

		$plugins = array();
		foreach( $all_plugins as $file => $data ) {
			// Only save the plugin if it's active.
			if ( is_plugin_active( $file ) ) {
				$plugins[] = array(
					'name' => $data['Name'],
					'slug' => basename( $file, '.php' ),
				);
			}
		}

		return $plugins;

	}

	/**
	 * Collects the active provider accounts.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	private function get_providers() {

		$raw_providers = $this->common->get_email_providers( true );
		$providers     = array();

		foreach ( (array) $raw_providers as $slug => $data ) {
			$providers[] = $slug;
		}

		return $providers;

	}

	/**
	 * Collects data from the active OptinMonster addons.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	private function get_active_addons() {

		$addons        = (array) $this->admin->get_addons();
		$active_addons = array();

		foreach ( $addons as $addon ) {
			$basename = $this->admin->get_plugin_basename_from_slug( $addon->slug );
			if ( is_plugin_active( $basename ) ) {
				$data            = get_plugin_data( WP_PLUGIN_DIR . '/' . $basename );
				$active_addons[] = array(
					'name'    => $addon->title,
					'version' => $data['Version'],
				);
			}
		}

		return $active_addons;

	}

	/**
	 * Collects data about the optins.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	private function get_optins() {

		if ( ! class_exists( 'Optin_Monster_Track_Datastore' ) ) {
			require plugin_dir_path( $this->base->file ) . 'includes/global/track-datastore.php';
		}

		$optin_posts = $this->base->get_optins();
		$optins      = array();

		foreach ( $optin_posts as $optin ) {
			$datastore = new Optin_Monster_Track_Datastore( $optin->ID );
			$meta      = get_post_meta( $optin->ID, '_om_meta', true );
			$optins[]  = array(
				'type'           => ( isset( $meta['type'] ) ? $meta['type'] : 'Unknown' ),
				'theme'          => ( isset( $meta['theme'] ) ? $meta['theme'] : 'Unknown' ),
				'impressions'    => $datastore->get_impressions(),
				'conversions'    => $datastore->get_conversions(),
				'affiliate_link' => ( isset( $meta['powered_by'] ) ? $meta['powered_by'] : 0 ),
			);
		}

		return $optins;

	}

	/**
	 * Disables SSL verification to prevent download package failures.
	 *
	 * @since 2.1.0
	 *
	 * @param array $args  Array of request args.
	 * @param string $url  The URL to be pinged.
	 * @return array $args Amended array of request args.
	 */
	public function http_request_args( $args, $url ) {

		// If this is an SSL request and we are performing a reporting routine, disable SSL verification.
		if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'trends.optinmonster.com' ) ) {
			$args['sslverify'] = false;
		}

		return $args;

	}

}