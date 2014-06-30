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
class Pressgram {

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

	/**
	 * Pressgram category.
	 *
	 * @since    2.0.0
	 *
	 * @var      string
	 */
	protected $pressgram_categories;

	/**
	 * Category inclusion options.
	 *
	 * @since    2.0.0
	 *
	 * @var      array
	 */
	protected $inclusion;

	/*---------------------------------------------------------------------------------*
	 * Consturctor / The Singleton Pattern
	 *---------------------------------------------------------------------------------*/

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     2.1.0
	 */
	private function __construct() {

		// Set Pressgram category
		$this->pressgram_categories = get_option( 'pressgram_categories', array() );

		// Set category inclusion
		$this->inclusion = get_option( 'pressgram_inclusion', array() );

		$this->inclusion['home'] = isset( $this->inclusion['home'] ) ? $this->inclusion['home'] : FALSE;
		$this->inclusion['feed'] = isset( $this->inclusion['feed'] ) ? $this->inclusion['feed'] : FALSE;
		// Show option to display on home if post includes non-Pressgram category and a Pressgram category
		$this->inclusion['multi_category_home'] = isset( $this->inclusion['multi_category_home'] ) ? $this->inclusion['multi_category_home'] : FALSE;
		// Show option to display in feed if post includes non-Pressgram category and a Pressgram category
		$this->inclusion['multi_category_feed'] = isset( $this->inclusion['multi_category_feed'] ) ? $this->inclusion['multi_category_feed'] : FALSE;
		

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Modify query_posts to exclude posts from the Pressgram category
		add_action( 'pre_get_posts', array( $this, 'exclude_pressgram_category_posts' ) );

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

	public function load_plugin_textdomain() {

		$domain = 'pressgram';
		$locale = apply_filters( 'pressgram-locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	} // end load_plugin_textdomain

	/**
	 * Modifies the query for the main loop excluding all posts from the selected
	 * Pressgram category so that they do not appear in the main loop.
	 *
	 * @since    2.1.3
	 */
	public function exclude_pressgram_category_posts( $wp_query ) {

		// If it's a feed or home, check if the Pressgram posts should be hidden from main stream
		if ( ( is_feed() && ! $this->inclusion['feed'] ) 
			|| ( is_home() && ! $this->inclusion['home'] ) ) {
			
			// Check to see if non-Pressgram multi-category posts should be shown on home or feed
			if( ( is_feed() && $this->inclusion['multi_category_feed'] ) 
				|| ( is_home() && $this->inclusion['multi_category_home'] ) ) {
							
				// Retrieve list of all categories for blog, excluding the pressgram category(ies)
				$non_pressgram_category_list = get_categories( array( 'exclude' => implode( ',', $this->pressgram_categories ) ) );
				
				// Define non_pressgram_category_list_ids array for non_pressgram_category_list_ids
				$non_pressgram_category_list_ids = array();
		
				// Populate non_pressgram_category_list_ids array so we know which posts to query
				foreach ( $non_pressgram_category_list as $current_category )
				{ 
					// Grab id for category list
					$non_pressgram_category_list_ids[] = $current_category->term_id;
				}

				// Update query_posts constraint for posts to pull by category
				set_query_var( 'category__in', $non_pressgram_category_list_ids );

				$this->include_pressgram_post_type( $wp_query );
		
			}// end if
			else{
	
				// Add the category to an array of excluded categories. In this case, though, it's really just one.
				$exclude = $this->pressgram_categories;
				
				// This is a cleaner way to write: $wp_query->set('category__not_in', $excluded);
				set_query_var( 'category__not_in', $exclude );
				
			} //end else
			
		} elseif ( ( is_feed() && $this->inclusion['feed'] )
			|| ( is_home() && $this->inclusion['home'] ) ) {

			$this->include_pressgram_post_type( $wp_query );

		}
	} // end exclude_pressgram_category_posts

	/**
	 * Modifies the query for the main loop to include posts from the selected
	 * Pressgram post type so that they appear, if requested.
	 *
	 * @since    2.1.3
	 */
	public function include_pressgram_post_type( $wp_query ) {

		// Get post types currently included in the query
		$included_post_types = get_query_var( 'post_type' );

		// If not set (default of post), then initialize the array
		! is_array( $included_post_types ) ? $included_post_types = array( 'post' ) : FALSE;

		$pressgram_post_relation = get_option( 'pressgram_post_relation', array() );
		
		if ( is_array( $pressgram_post_relation ) ) {

			foreach ( $pressgram_post_relation as $post_type => $value ) {
		
				// Add our post type, if not already included
				! in_array( $post_type, $included_post_types ) ? array_push( $included_post_types, $post_type ) : FALSE;

			}
			
		}

		set_query_var( 'post_type', $included_post_types );
	} // end include_pressgram_post_type


	/*---------------------------------------------------------------------------------*
	 * Helper Functions
	 *---------------------------------------------------------------------------------*/

	/**
	 * Retrieves the entire list of categories for this blog.
	 *
	 * @return   array    The array of categories that are defined in this blog.
	 * @since    1.0.0
	 */
	public static function get_categories() {

		$categories = array();

		// Get an array of the categories
		$args = array(
			'type'           => 'post',
			'child_of'       => 0,
			'parent'         => '',
			'orderby'        => 'name',
			'order'          => 'ASC',
			'hide_empty'     => 0,
			'hierarchical'   => 1,
			'exclude'        => '',
			'include'        => '',
			'number'         => '',
			'taxonomy'       => 'category',
			'pad_counts'     => false
		);
		$categories = get_categories( $args );

		return $categories;

	} // end get_categories

} // end class