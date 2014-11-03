<?php
/**
 * Plugin Name: OptinMonster
 * Plugin URI:	http://optinmonster.com
 * Description: OptinMonster is the best lead generation plugin for WordPress.
 * Author:		Thomas Griffin
 * Author URI:	http://thomasgriffin.io
 * Version:		2.1.1
 * Text Domain: optin-monster
 * Domain Path: languages
 *
 * OptinMonster is licensed under a split-GPL license. The PHP and HTML
 * used in OptinMonster are licensed under the GPL license, v2 or later.
 * The JS, CSS, imagery, iconography and branding are licensed under a
 * proprietary license for unlimited personal use and cannot be redistributed
 * without the express written consent of Retyp, LLC.
 *
 * OptinMonster is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OptinMonster. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define necessary plugin constants.
define( 'OPTINMONSTER_APIURL', plugins_url( 'assets/js/api.js', __FILE__ ) );

/**
 * Main plugin class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author	Thomas Griffin
 */
class Optin_Monster {

	/**
	 * Holds the class object.
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $version = '2.1.1';

	/**
	 * The name of the plugin.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'OptinMonster';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'optin-monster';

	/**
	 * Plugin file.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// Fire a hook before the class is setup.
		do_action( 'optin_monster_pre_init' );

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the ajax handler.
		add_action( 'after_setup_theme', array( $this, 'load_ajax_handler' ), 11 );

		// Load the plugin widget.
		add_action( 'widgets_init', array( $this, 'widget' ) );

		// Load the plugin.
		add_action( 'init', array( $this, 'init' ), 0 );

	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 2.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = 'optin-monster';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		require plugin_dir_path( __FILE__ ) . 'includes/vendor/optinmonster/optinmonster.php';

	}

	/**
	 * Loads the ajax router for handling any OptinMonster ajax actions.
	 *
	 * @since 2.0.0
	 */
	public function load_ajax_handler() {

		require plugin_dir_path( __FILE__ ) . 'includes/global/router.php';
		$optin_monster_router = new Optin_Monster_Router();

	}

	/**
	 * Registers the OptinMonster widget.
	 *
	 * @since 2.0.0
	 */
	public function widget() {

		register_widget( 'Optin_Monster_Widget' );

	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		// Run hook once OptinMonster has been initialized.
		do_action( 'optin_monster_init' );

		// Always make sure our global option is set.
		$this->set_option();

		// Load admin only components.
		if ( is_admin() ) {
			$this->require_admin();

			// If not processing an ajax request, load the updater.
			if ( ! defined( 'DOING_AJAX' ) || defined( 'DOING_AJAX' ) && ! DOING_AJAX ) {
				$this->require_updater();
			}
		}

		// Load global components.
		$this->require_global();

		// Always make sure our global lead table exists.
		$this->set_tables();

		// Always make sure our preview frame page exists.
		$this->set_preview();

		 // Load the reporter after everything else.
		 if ( is_admin() ) {
			  // If not processing an ajax request, load the reporter.
			  if ( ! defined( 'DOING_AJAX' ) || defined( 'DOING_AJAX' ) && ! DOING_AJAX ) {
				   $this->require_reporter();
			  }
		 }

		 // Run hook once OptinMonster has been fully loaded.
		 do_action( 'optin_monster_loaded' );

	}

	/**
	 * Sets our global option if it is not found in the DB.
	 *
	 * @since 2.0.0
	 */
	public function set_option() {

		$option = get_option( 'optin_monster' );
		if ( ! $option || empty( $option ) ) {
			$option = Optin_Monster::default_options();
			update_option( 'optin_monster', $option );
		}

	}

	/**
	 * Sets our global lead table if it is not found in the DB.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb The WordPress database object.
	 */
	public function set_tables() {

		// If not in the admin, return early.
		if ( ! is_admin() ) {
			return;
		}

		// If the table does not exist, create it now.
		global $wpdb;
		if ( ! ( $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "om_leads'" ) === $wpdb->prefix . 'om_leads' ) ) {
			// Create the leads table.
			$leads = new Optin_Monster_Lead_Datastore( $wpdb );
			$leads->create_table();
		}

	}

	/**
	 * Sets our global preview frame page for handling previews in the customizer.
	 *
	 * @since 2.0.2
	 *
	 * @global object $wpdb The WordPress database object.
	 */
	public function set_preview() {

		// If not in the admin, return early.
		if ( ! is_admin() ) {
			return;
		}

		// If the option exists, return early.
		$preview_page = get_option( 'optin_monster_preview_page' );
		if ( $preview_page && get_post( $preview_page ) ) {
			return;
		}

		// Generate the custom preview page to handle preview areas if it does not exist.
		global $wpdb;
		$preview_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '%s' AND post_type = '%s' LIMIT 1", 'optin-monster-preview-page', 'page' ) );
		if ( ! $preview_found ) {
			$args = array(
				'post_type'		 => 'page',
				'post_name'		 => 'optin-monster-preview-page',
				'post_author'	 => 1,
				'post_title'	 => __( 'OptinMonster Preview Page', 'optin-monster' ),
				'post_status'	 => 'private',
				'post_content'	 => $this->get_preview_content(),
				'comment_status' => 'closed'
			);
			$preview_id = wp_insert_post( $args );

			// If successful, update our option so that we can know which page is used for the preview output.
			if ( $preview_id ) {
				update_option( 'optin_monster_preview_page', $preview_id );
			}
		}

	}

	/**
	 * Retrieves the preview content for our Preview frame page.
	 *
	 * @since 2.0.2
	 *
	 * @return string A string of preview content for the preview page.
	 */
	public function get_preview_content() {

		ob_start();
		?>
		<p><?php _e( 'This is the OptinMonster preview page. Unless otherwise changed, all OptinMonster optin previews will be handled on this page. After post and sidebar optins will be handled in this content section (sidebar will appear here because you can never know what sidebars are enabled and the widgets inside of them). All other popup type optins will function as normal. Some optin settings are overridden for the preview area in order to make the preview experience better.', 'optin-monster' ); ?></p>
		<p><?php _e( 'This is an actual (but private) page of your site, so you are getting an exact replica of what you will see when you finally decide to push your optin live. If you have any questions, be sure to head over to the <a href="http://optinmonster.com/" title="OptinMonster" target="_blank">OptinMonster</a> website for docs, tutorials and support. Thanks for choosing OptinMonster - here\'s to converting more visitors into subscribers and customers!', 'optin-monster' ); ?></p>
		<?php
		return ob_get_clean();

	}

	/**
	 * Loads all admin related files into scope.
	 *
	 * @since 2.0.0
	 */
	public function require_admin() {

		require plugin_dir_path( __FILE__ ) . 'includes/admin/actions.php';
		require plugin_dir_path( __FILE__ ) . 'includes/admin/common.php';
		require plugin_dir_path( __FILE__ ) . 'includes/admin/license.php';
		require plugin_dir_path( __FILE__ ) . 'includes/admin/menu.php';

	}

	/**
	 * Loads all updater related files and functions into scope.
	 *
	 * @since 2.0.0
	 *
	 * @return null Return early if the license key is not set or there are key errors.
	 */
	public function require_updater() {

		// Retrieve the license key. If it is not set, return early.
		$key = $this->get_license_key();
		if ( ! $key ) {
			return;
		}

		// If there are any errors with the key itself, return early.
		if ( $this->get_license_key_errors() ) {
			return;
		}

		// Load the updater class.
		require plugin_dir_path( __FILE__ ) . 'includes/admin/updater.php';

		// Go ahead and initialize the updater.
		$args = array(
			'plugin_name' => $this->plugin_name,
			'plugin_slug' => $this->plugin_slug,
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
			'remote_url'  => 'http://optinmonster.com/',
			'version'	  => $this->version,
			'key'		  => $key
		);
		$optin_monster_updater = new Optin_Monster_Updater( $args );

		// Fire a hook for Addons to register their updater since we know the key is present.
		do_action( 'optin_monster_updater', $key );

	}

	/**
	 * Loads the reporter to send useful data to AwesomeMotive
	 *
	 * @since 2.1.0
	 */
	public function require_reporter() {

		// Make sure we have permission to collect the data
		$option = get_option( 'optin_monster' );
		if ( ! isset( $option['allow_reporting'] ) || ! $option['allow_reporting'] ) {
			return;
		}

		// Perform the reporting if needed
		if ( false === ( $need_to_report = get_transient( 'awesomemotive_reporter_expires' ) ) ) {
			require plugin_dir_path( __FILE__ ) . 'includes/admin/reporter.php';

			// Create the reporter instance
			$reporter = new Optin_Monster_Reporter();

			// Send the data to AwesomeMotive
			$response = $reporter->send();

			// Make sure we don't do this again for a week
			set_transient( 'awesomemotive_reporter_expires', $reporter->getData(), WEEK_IN_SECONDS );
		}

	}

	/**
	 * Loads all global files into scope.
	 *
	 * @since 2.0.0
	 */
	public function require_global() {

		require plugin_dir_path( __FILE__ ) . 'includes/global/ajax.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/common.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/datastore-interface.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/legacy.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/output.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/posttype.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/preview.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/provider.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/shortcode.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/theme.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/widget.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/lead-datastore.php';

	}

	/**
	 * Returns a optin based on ID.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id	  The optin ID used to retrieve a optin.
	 * @return array|bool Array of optin data or false if none found.
	 */
	public function get_optin( $id ) {

		// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
		if ( false === ( $optin = get_transient( '_om_cache_' . $id ) ) ) {
			$optin = $this->_get_optin( $id );
			if ( $optin ) {
				set_transient( '_om_cache_' . $id, $optin, DAY_IN_SECONDS );
			}
		}

		// Return the optin data.
		return $optin;

	}

	/**
	 * Internal method that returns a optin based on ID.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id	  The optin ID used to retrieve a optin.
	 * @return array|bool Array of optin data or false if none found.
	 */
	public function _get_optin( $id ) {

		return get_post( $id );

	}

	/**
	 * Returns a optin based on slug.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The optin slug used to retrieve a optin.
	 * @return array|bool  Array of optin data or false if none found.
	 */
	public function get_optin_by_slug( $slug ) {

		// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
		if ( false === ( $optin = get_transient( '_om_cache_' . $slug ) ) ) {
			$optin = $this->_get_optin_by_slug( $slug );
			if ( $optin ) {
				set_transient( '_om_cache_' . $slug, $optin, DAY_IN_SECONDS );
			}
		}

		// Return the optin data.
		return $optin;

	}

	/**
	 * Internal method that returns a optin based on slug.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The optin slug used to retrieve a optin.
	 * @return array|bool  Array of optin data or false if none found.
	 */
	public function _get_optin_by_slug( $slug ) {

		$optin = get_posts(
			array(
				'post_type'		 => 'optin',
				'posts_per_page' => 1,
				'no_found_rows'	 => true,
				'cache_results'	 => false,
				'name'			 => $slug
			)
		);
		if ( empty( $optin ) ) {
			return false;
		}

		// Return the optin data.
		return $optin[0];

	}

	/**
	 * Returns all optins created on the site.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Array of args to modify the query for retrieiving optins.
	 * @return array|bool Array of optin data or false if none found.
	 */
	public function get_optins( $args = array() ) {

		// If args have been passed, this is a custom query - we don't want to cache it.
		if ( ! empty( $args ) ) {
			return $this->_get_optins( $args );
		}

		// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
		if ( false === ( $optins = get_transient( '_om_cache_all' ) ) ) {
			$optins = $this->_get_optins( $args );
			if ( $optins ) {
				set_transient( '_om_cache_all', $optins, DAY_IN_SECONDS );
			}
		}

		// Return the optin data.
		return $optins;

	}

	/**
	 * Internal method that returns all optins created on the site.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Array of args to modify the query for retreiving optins.
	 * @return array|bool Array of optin data or false if none found.
	 */
	public function _get_optins( $args = array() ) {

		$optins = get_posts(
			wp_parse_args(
				$args,
				array(
					'post_type'		=> 'optin',
					'orderby'		=> 'menu_order',
					'order'			=> 'ASC',
					'no_found_rows' => true,
					'cache_results' => false,
					'nopaging'		=> true,
					'meta_query'	=> array(
						array(
							'key'	  => '_om_is_clone',
							'compare' => 'NOT EXISTS',
							'value'	  => ''
						)
					)
				)
			)
		);
		if ( empty( $optins ) ) {
			return false;
		}

		// Return the optin data.
		return $optins;

	}

	/**
	 * Returns all split tests for a particular optin.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id	  The optin ID to target.
	 * @return array|bool Array of optin data or false if none found.
	 */
	public function get_split_tests( $id ) {

		// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
		if ( false === ( $optins = get_transient( '_om_cache_split_' . $id ) ) ) {
			$optins = $this->_get_split_tests( $id );
			if ( $optins ) {
				set_transient( '_om_cache_split_' . $id, $optins, DAY_IN_SECONDS );
			}
		}

		// Return the optin data.
		return $optins;

	}

	/**
	 * Internal method that returns all split tests for a particular optin.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id	  The optin ID to target.
	 * @return array|bool Array of optin data or false if none found.
	 */
	public function _get_split_tests( $id ) {

		$optin = $this->get_optin( $id );
		if ( ! $optin ) {
			return false;
		}

		$clones = get_post_meta( $optin->ID, '_om_has_clone', true );
		if ( empty( $clones ) ) {
			return false;
		}

		// Return the split test objects.
		$objects = array();
		foreach ( $clones as $clone ) {
			$objects[] = $this->get_optin( $clone );
		}

		return $objects;

	}

	/**
	 * Returns the license key for OptinMonster.
	 *
	 * @since 2.0.0
	 *
	 * @return string $key The user's license key for OptinMonster.
	 */
	public function get_license_key() {

		$option = get_option( 'optin_monster' );
		$key	= false;
		if ( empty( $option['key'] ) ) {
			if ( defined( 'OPTINMONSTER_LICENSE_KEY' ) ) {
				$key = OPTINMONSTER_LICENSE_KEY;
			}
		} else {
			$key = $option['key'];
		}

		return apply_filters( 'optin_monster_license_key', $key );

	}

	/**
	 * Returns the license key type for OptinMonster.
	 *
	 * @since 2.0.0
	 *
	 * @return string $type The user's license key type for OptinMonster.
	 */
	public function get_license_key_type() {

		$option = get_option( 'optin_monster' );
		return isset( $option['type'] ) ? $option['type'] : '';

	}

	/**
	 * Returns possible license key error flag.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if there are license key errors, false otherwise.
	 */
	public function get_license_key_errors() {

		$option = get_option( 'optin_monster' );
		return isset( $option['is_expired'] ) && $option['is_expired'] || isset( $option['is_disabled'] ) && $option['is_disabled'] || isset( $option['is_invalid'] ) && $option['is_invalid'];

	}

	/**
	 * Loads the default plugin options.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of default plugin options.
	 */
	public static function default_options() {

		$ret = array(
			'key'					  => '',
			'type'					  => '',
			'is_expired'			  => false,
			'is_disabled'			  => false,
			'is_invalid'			  => false,
			'cookie'				  => 0,
			'leads'					  => 1,
			'affiliate_link'		  => '',
			'affiliate_link_position' => 'under',
			'admin_preview'			  => 0,
			'allow_reporting'		  => 1,
		);

		return apply_filters( 'optin_monster_default_options', $ret );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return object The Optin_Monster object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster ) ) {
			self::$instance = new Optin_Monster();
		}

		return self::$instance;

	}

}

register_activation_hook( __FILE__, 'optin_monster_activation_hook' );
/**
 * Fired when the plugin is activated.
 *
 * @since 2.0.0
 *
 * @global int $wp_version		The version of WordPress for this install.
 * @global object $wpdb			The WordPress database object.
 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
 */
function optin_monster_activation_hook( $network_wide ) {

	global $wp_version;
	if ( version_compare( $wp_version, '3.5.1', '<' ) && ! defined( 'OPTINMONSTER_FORCE_ACTIVATION' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( sprintf( __( 'Sorry, but your version of WordPress does not meet OptinMonster\'s required version of <strong>3.5.1</strong> to run properly. The plugin has been deactivated. <a href="%s">Click here to return to the Dashboard</a>.', 'optin-monster' ), get_admin_url() ) );
	}

	// Load the datastore interfaces.
	require plugin_dir_path( __FILE__ ) . 'includes/global/datastore-interface.php';
	require plugin_dir_path( __FILE__ ) . 'includes/global/lead-datastore.php';

	$instance = Optin_Monster::get_instance();

	global $wpdb;
	if ( is_multisite() && $network_wide ) {
		$site_list = $wpdb->get_results( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" );
		foreach ( (array) $site_list as $site ) {
			switch_to_blog( $site->blog_id );

			// Set default license option.
			$option = get_option( 'optin_monster' );
			if ( ! $option || empty( $option ) ) {
				update_option( 'optin_monster', Optin_Monster::default_options() );
			}

			$leads = new Optin_Monster_Lead_Datastore( $wpdb );
			$leads->create_table();

			restore_current_blog();
		}
	} else {
		// Set default license option.
		$option = get_option( 'optin_monster' );
		if ( ! $option || empty( $option ) ) {
			update_option( 'optin_monster', Optin_Monster::default_options() );
		}

		// Create the leads table.
		$leads = new Optin_Monster_Lead_Datastore( $wpdb );
		$leads->create_table();
	}

}

register_uninstall_hook( __FILE__, 'optin_monster_uninstall_hook' );
/**
 * Fired when the plugin is uninstalled.
 *
 * @since 2.0.0
 *
 * @global object $wpdb The WordPress database object.
 */
function optin_monster_uninstall_hook() {

	// Load the datastore interfaces.
	require plugin_dir_path( __FILE__ ) . 'includes/global/datastore-interface.php';
	require plugin_dir_path( __FILE__ ) . 'includes/global/lead-datastore.php';

	$instance = Optin_Monster::get_instance();

	global $wpdb;
	if ( is_multisite() ) {
		$site_list = $wpdb->get_results( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" );
		foreach ( (array) $site_list as $site ) {
			switch_to_blog( $site->blog_id );
			delete_option( 'optin_monster' );
			delete_option( 'optin_monster_preview_page' );
			restore_current_blog();
		}
	} else {
		delete_option( 'optin_monster' );
		delete_option( 'optin_monster_preview_page' );

		// Drop the leads table.
		$leads = new Optin_Monster_Lead_Datastore( $wpdb );
		$leads->remove_table();
	}

}

// Load the main plugin class.
$optin_monster = Optin_Monster::get_instance();

// Conditionally load the template tag.
if ( ! function_exists( 'optin_monster' ) ) {
	/**
	 * Primary template tag for outputting OptinMonster optins in templates.
	 *
	 * @since 2.0.0
	 *
	 * @param int	 $id	 The ID of the optin to load.
	 * @param string $type	 The type of field to query.
	 * @param array	 $args	 Associative array of args to be passed.
	 * @param bool	 $return Flag to echo or return the optin HTML.
	 */
	function optin_monster( $id, $type = 'id', $args = array(), $return = false ) {

		// If we have args, build them into a shortcode format.
		$args_string = '';
		if ( ! empty( $args ) ) {
			foreach ( (array) $args as $key => $value ) {
				$args_string .= ' ' . $key . '="' . $value . '"';
			}
		}

		// Build the shortcode.
		$shortcode = ! empty( $args_string ) ? '[optin-monster ' . $type . '="' . $id . '"' . $args_string . ']' : '[optin-monster ' . $type . '="' . $id . '"]';

		// Return or echo the shortcode output.
		if ( $return ) {
			return do_shortcode( $shortcode );
		} else {
			echo do_shortcode( $shortcode );
		}

	}
}

// Backwards compat for the v1 template tag.
if ( ! function_exists( 'optin_monster_tag' ) ) {
	/**
	 * Primary template tag for outputting OptinMonster optins in templates (v1).
	 *
	 * @since 2.0.0
	 *
	 * @param int	 $string The post name of the optin to load.
	 * @param bool	 $return Flag to echo or return the optin HTML.
	 */
	function optin_monster_tag( $id, $return = false ) {

		// Return the v2 template tag.
		return optin_monster( $id, 'slug', array(), $return );

	}
}