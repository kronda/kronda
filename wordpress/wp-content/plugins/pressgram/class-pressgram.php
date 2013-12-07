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
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '2.1.2';

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
	protected $pressgram_category;

	/**
	 * Fine control options.
	 *
	 * @since    2.0.0
	 *
	 * @var      array
	 */
	protected $options;

	/**
	 * Pressgram post relations.
	 *
	 * @since    2.1.0
	 *
	 * @var      array
	 */
	protected $pressgram_post_relation;

	/**
	 * Pressgram post.
	 *
	 * @since    2.1.0
	 *
	 * @var      array
	 */
	protected $pressgram_post;

	/*---------------------------------------------------------------------------------*
	 * Consturctor / The Singleton Pattern
	 *---------------------------------------------------------------------------------*/

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     2.1.0
	 */
	private function __construct() {

		// Initialize Pressgram post array
		$this->pressgram_post = array();

		// Set Pressgram category
		$this->pressgram_category = get_option( 'pressgram_category' );

		// Set fine control options
		$this->options = get_option( 'pressgram_fine_control', array() );
		$this->options['post_type'] = isset( $this->options['post_type'] ) ? $this->options['post_type'] : 'post';
		$this->options['post_status'] = isset( $this->options['post_status'] ) ? $this->options['post_status'] : 'publish';
		$this->options['post_format'] = isset( $this->options['post_format'] ) ? $this->options['post_format'] : 'standard';
		$this->options['featured_img'] = isset( $this->options['featured_img'] ) ? $this->options['featured_img'] : FALSE;
		$this->options['img_link'] = isset( $this->options['img_link'] ) ? $this->options['img_link'] : 'none';
		$this->options['img_align'] = isset( $this->options['img_align'] ) ? $this->options['img_align'] : 'none';
		$this->options['comments'] = isset( $this->options['comments'] ) ? $this->options['comments'] : FALSE;
		$this->options['pings'] = isset( $this->options['pings'] ) ? $this->options['pings'] : FALSE;
		$this->options['tag_post'] = isset( $this->options['tag_post'] ) ? $this->options['tag_post'] : FALSE;
		$this->options['strip'] = isset( $this->options['strip'] ) ? $this->options['strip'] : array( 'hashtags' => FALSE, 'text' => FALSE, 'image' => FALSE );
		$this->options['strip']['hashtags'] = isset( $this->options['strip']['hashtags'] ) ? $this->options['strip']['hashtags'] : FALSE;
		$this->options['strip']['text'] = isset( $this->options['strip']['text'] ) ? $this->options['strip']['text'] : FALSE;
		$this->options['strip']['image'] = isset( $this->options['strip']['image'] ) ? $this->options['strip']['image'] : FALSE;
		$this->options['show']['home'] = isset( $this->options['show']['home'] ) ? $this->options['show']['home'] : FALSE;
		$this->options['show']['feed'] = isset( $this->options['show']['feed'] ) ? $this->options['show']['feed'] : FALSE;

		// Set Pressgram post relations
		$this->pressgram_post_relation = get_option( 'pressgram_post_relation', array() );

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load up an administration notice to guide users to the next step
		add_action( 'admin_notices', array( $this, 'display_plugin_activation_message' ) );

		// Process responses to the notices
		add_action( 'admin_init', array( $this, 'process_notice_response' ) );

		// Initializes the Pressgram Category setting and field
		add_action( 'admin_init', array( $this, 'register_pressgram_fields' ) );

		// Load the administrative Stylesheets and JavaScript
		add_action( 'admin_enqueue_scripts', array( $this, 'add_stylesheets_and_javascript' ) );

		// Modify query_posts to exclude posts from the Pressgram category
		add_action( 'pre_get_posts', array( $this, 'exclude_pressgram_category_posts' ) );

		// Add jquery to process selected post type on media settings page
		add_action( 'admin_footer', array( $this, 'process_selected_post_type' ) );

		// Set categories, tags, and check power tags, adjusting options as needed
		add_action( 'transition_post_status', array( $this, 'process_taxonomies' ), 5, 3 );

		// Apply fine control to new posts categorized in selected Pressgram category
		add_action( 'transition_post_status', array( $this, 'apply_fine_control' ), 15, 3 );

		// Add misc meta field to Publicize metabox on post edit pages
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_page_metabox' ) );

		// Save Pressgram post metadata on post save
		add_action( 'save_post', array( $this, 'save_pressgram_post_metabox_data' ) );

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
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = 'pressgram';
		$locale = apply_filters( 'pressgram-locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	} // end load_plugin_textdomain

	/**
	 * Displays a plugin message as soon as the plugin is activated.
	 *
	 * @since    2.1.0
	 */
	public function display_plugin_activation_message() {

		if ( ! get_option( 'has_activated_pressgram' ) ) {

			// Show the notice
			$html = '<div class="updated">';
				$html .= '<a href="http://pressgr.am"><img src="' . plugin_dir_url( __FILE__ ) . 'pressgram-logo.png" style="float: left; width: 2em; height: 2em; margin-right: 0.4em; margin-top: 0.4em" /></a>';
				$html .= '<p style="display: inline-block">';
					$html .= __( "<strong>Awesome!</strong> You're almost there - <a href='options-media.php#pressgram-section'>click here</a> to select a Pressgram category, set fine control options and manage post relations.", 'pressgram-locale' );
				$html .= '</p>';
			$html .= '</div><!-- /.updated -->';

			echo $html;

			update_option( 'has_activated_pressgram', TRUE );

		} // end if

	} // end display_plugin_activation_message

	/**
	 * Deletes the option for the plugin activation so that it can be displayed when the plugin is reinstalled or
	 * when it's reactivated.
	 *
	 * @since    2.1.0
	 */
	public static function remove_plugin_option() {
		delete_option( 'has_activated_pressgram' );
	} // end has_activated_message

	/**
	 * Returns the active plugin notices for display on the settings page summary.
	 *
	 * @since    2.1.0
	 */
	public function get_notices() {

		$current_user = wp_get_current_user();

		// Get the notice options from the user
		$notices = get_user_meta( $current_user->ID, 'pressgram_notices', TRUE );

		// If not yet set, then set the usermeta as an array
		! is_array( $notices ) ? add_user_meta( $current_user->ID, 'pressgram_notices', array() ) : FALSE;

		// Create specific notices if they do not exist, otherwise set to current notice state
		$notices['activation'] = isset( $notices['activation'] ) ? $notices['activation'] : array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );
		$notices['support'] = isset( $notices['support'] ) ? $notices['support'] : array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );
		$notices['social'] = isset( $notices['social'] ) ? $notices['social'] : array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );
		$notices['review'] = isset( $notices['review'] ) ? $notices['review'] : array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );
		$notices['photos'] = isset( $notices['photos'] ) ? $notices['photos'] : array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );

		// Update the users meta
		update_user_meta( $current_user->ID, 'pressgram_notices', $notices );

		// Set the variable that will hold the html
		$html = '<div id="pressgram-outer-container">';

		$html .= '<div id="pressgram-container" style="height: 72px;"><div class="update-nag pressgram-notice info">';
			//$html .= '<a href="http://pressgr.am"><img src="' . plugin_dir_url( __FILE__ ) . 'pressgram-logo.png" style="float: left; width: 2em; height: 2em; margin-right: 0.9em; margin-top: 0.5em" /></a>';
			$html .= '<p>';
				$html .= __( '<a href="http://wordpress.org/plugins/pressgram">Plugin</a> developed by <a href="http://pressgr.am">Pressgram</a> & <a href="http://vandercar.net">UaMV</a>. <span style="float:right;"><a href="' . wp_nonce_url( 'options-media.php?pressgram-action=undismiss&notif=info', 'pressgram-undismiss-all-notices' ) . '">Reactivate All Notices</a></span>', 'pressgram-locale' );
			$html .= '</p>';
		$html .= '</div></div><!-- /.updated -->';

		// Show the support notice
		if ( $notices['support']['trigger'] && $notices['support']['time'] < time() ) {

			$html .= '<div id="pressgram-container" style="height: 72px;"><div class="update-nag pressgram-notice support">';
				//$html .= '<a href="http://pressgr.am"><img src="' . plugin_dir_url( __FILE__ ) . 'pressgram-logo.png" style="float: left; width: 2em; height: 2em; margin-right: 0.9em; margin-top: 0.5em" /></a>';
				$html .= '<p>';
					$html .= __( 'Require assistance? Find (or give) support at <a href="http://wordpress.org/support/plugin/pressgram/">wordpress.org</a> and <a href="http://help.pressgr.am">help.pressgr.am</a>.<span style="float:right;"><a href="' . wp_nonce_url( 'options-media.php?pressgram-action=dismiss&notif=support&duration=forever', 'pressgram-dismiss-support-forever' ) . '">Dismiss</a></span>', 'pressgram-locale' );
				$html .= '</p>';
			$html .= '</div></div><!-- /.updated -->';

		} // end if

		// Show the connect with Pressgram community notice
		if ( $notices['social']['trigger'] && $notices['social']['time'] < time() ) {
			
			$html .= '<div id="pressgram-container"><div class="update-nag pressgram-notice social">';
				$html .= '<p>';
					$html .= __( '<strong>Hello, ' . $current_user->display_name . '!</strong> Have you connected with others in the Pressgram community?<br />Discover other digital rebels and the ways in which they are using Pressgram to tell their visual story.<br /><br />', 'pressgram-locale' );
				$html .= '';
					$html .= __( '<a href="http://blog.pressgr.am">Blog</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
					$html .= __( '<a href="http://twitter.com/pressgram">@pressgram</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
					$html .= __( '<a href="https://plus.google.com/105715666878799743742?prsrc=3">Google +</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
					$html .= __( '<a href="http://facebook.com/pressgram">Facebook</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
					$html .= __( '<a href="http://pressgram.net">Discover</a>', 'pressgram-locale' );
					$html .= '<span style="float:right;">';
					$html .= __( '<a href="' . wp_nonce_url( 'options-media.php?pressgram-action=dismiss&notif=social&duration=month', 'pressgram-dismiss-social-month' ) . '">Hide For One Month</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
					$html .= __( '<a href="' . wp_nonce_url( 'options-media.php?pressgram-action=dismiss&notif=social&duration=forever', 'pressgram-dismiss-social-forever' ) . '">Dismiss Forever</a>', 'pressgram-locale' );
					$html .= '</span>';
				$html .= '</p>';
			$html .= '</div></div><!-- /.updated -->';
		}

		// Show the review the plugin and app notice
		if ( $notices['review']['trigger'] && $notices['review']['time'] < time() ) {

			$html .= '<div id="pressgram-container"><div class="update-nag pressgram-notice review">';
				$html .= '<p>';
					$html .= __( 'We do hope the Pressgram plugin has been serving you and your readers well.<br />Your experience could assist other users. Consider yourself invited to rate and review these tools.<br /><br />', 'pressgram-locale' );
				$html .= '';
					$html .= __( '<a href="http://wordpress.org/support/view/plugin-reviews/pressgram#postform">Review Plugin</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
					$html .= __( '<a href="http://bit.ly/pressgramapp">Review iOS App</a>', 'pressgram-locale' );
					$html .= '<span style="float:right;">';
					$html .= __( '<a href="' . wp_nonce_url( 'options-media.php?pressgram-action=dismiss&notif=review&duration=month', 'pressgram-dismiss-review-month' ) . '">Hide For One Month</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
					$html .= __( '<a href="' . wp_nonce_url( 'options-media.php?pressgram-action=dismiss&notif=review&duration=forever', 'pressgram-dismiss-review-forever' ) . '">Dismiss Forever</a>', 'pressgram-locale' );
					$html .= '</span>';
				$html .= '</p>';
			$html .= '</div></div><!-- /.updated -->';
		}

		// Show the random photo vault notice
		if ( $notices['photos']['trigger'] && $notices['photos']['time'] < time() ) {

			$random_photo_content = '';
			
			// The arguments
			$args = array(
				'post_type'      => get_post_types( array( 'public' => TRUE ) ),
				'meta_key'       => '_pressgram_post',
				'meta_value'     => TRUE,
				'posts_per_page' => 5,
				'orderby'        => 'rand',
				'author'         => $current_user->ID,
				);

			// Get the pressgram posts
			$pressgram_array = get_posts( $args );

			// The Loop to link images to their post
			foreach ( $pressgram_array as $pressgram_post ) {

				// Do this if featured image is set
				if ( '' != get_the_post_thumbnail( $pressgram_post->ID ) ) {
					$random_photo_content .= '<a href="' . get_permalink( $pressgram_post->ID ) . '" title="' . esc_attr( $pressgram_post->post_title ) . '">' . get_the_post_thumbnail( $pressgram_post->ID, array( 100, 100 ) ) . '</a>';
				} else { // Otherwise
					// Get the first attachment image
					$attachment = get_children( "post_parent=$pressgram_post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );  // get child attachments of type image
						
					// Get post ID of attachment
					$attachment_ID = current( array_keys( $attachment ) );

					$random_photo_content .= '<a href="' . get_permalink( $pressgram_post->ID ) . '" title="' . esc_attr( $pressgram_post->post_title ) . '">' . wp_get_attachment_image( $attachment_ID, array( 100, 100 ), FALSE, array( 'class' => 'wp-post-image' ) ) . '</a>';
				}
			}

			$html .= '<div id="pressgram-container"><div class="update-nag pressgram-notice photos">';
				$html .= '<p>';
					$html .= $random_photo_content;
				$html .= '';
					$html .= '<span style="float:right;">';
					$html .= __( '<a href="' . wp_nonce_url( 'options-media.php?pressgram-action=dismiss&notif=photos&duration=forever', 'pressgram-dismiss-photos-forever' ) . '">Hide Photo Vault</a>', 'pressgram-locale' );
					$html .= '</span>';
				$html .= '</p>';
			$html .= '</div></div><!-- /.updated -->';
		}

		$html .= '</div>';

		return $html;
	} // end display_notices

	/**
	 * Process any responses to the displayed notices.
	 *
	 * @since    2.1.0
	 */
	public function process_notice_response() {
		
		// Check if user has responded to notice
		if ( isset( $_GET['pressgram-action'] ) ) {
			
			$current_user = wp_get_current_user();

			// Get the notice options from the user
			$notices = get_user_meta( $current_user->ID, 'pressgram_notices', TRUE );

			// If they've postponed the review and duration is set
			if ( 'dismiss' == $_GET['pressgram-action'] && isset( $_GET['duration'] ) && isset( $_GET['notif'] ) ) {
				
				if ( 'support' == $_GET['notif'] ) {
					check_admin_referer( 'pressgram-dismiss-support-forever' ) ? $notices['support']['time'] = time() + 31536000 : FALSE;
				} elseif ( 'social' == $_GET['notif'] ) {
					// Do the stuff
					switch ( $_GET['duration'] ) {
						case 'month':
							check_admin_referer( 'pressgram-dismiss-social-month' ) ? $notices['social']['time'] = time() + 2592000 : FALSE;
							break;
						case 'forever':
							check_admin_referer( 'pressgram-dismiss-social-forever' ) ? $notices['social']['trigger'] = FALSE : FALSE;
							break;
						default:
							break;
					}
				} elseif ( 'review' == $_GET['notif'] ) {
					// Do the stuff
					switch ( $_GET['duration'] ) {
						case 'month':
							check_admin_referer( 'pressgram-dismiss-review-month' ) ? $notices['review']['time'] = time() + 2592000 : FALSE;
							break;
						case 'forever':
							check_admin_referer( 'pressgram-dismiss-review-forever' ) ? $notices['review']['trigger'] = FALSE : FALSE;
							break;
						default:
							break;
					}
				} elseif ( 'photos' == $_GET['notif'] ) {
					// Do the stuff
					switch ( $_GET['duration'] ) {
						case 'month':
							check_admin_referer( 'pressgram-dismiss-photos-month' ) ? $notices['photos']['time'] = time() + 2592000 : FALSE;
							break;
						case 'forever':
							check_admin_referer( 'pressgram-dismiss-photos-forever' ) ? $notices['photos']['trigger'] = FALSE : FALSE;
							break;
						default:
							break;
					}
				}

				// Update the option
				update_user_meta( $current_user->ID, 'pressgram_notices', $notices );

			} elseif ( 'undismiss' == $_GET['pressgram-action'] && isset( $_GET['notif'] ) && check_admin_referer( 'pressgram-undismiss-all-notices' ) ) {
				$notices['activation'] = array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );
				$notices['support'] = array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );
				$notices['social'] = array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );
				$notices['review'] = array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );
				$notices['photos'] = array( 'trigger' => TRUE, 'time' => ( time() - 5 ) );

				update_user_meta( $current_user->ID, 'pressgram_notices', $notices );
			}
		}
	} // end process_notice_response

	/**
	 * Registers the plugin's administrative stylesheets and JavaScript
	 *
	 * @since    2.1.0
	 */
	public function add_stylesheets_and_javascript() {
		wp_enqueue_style( 'pressgram-admin-style', plugin_dir_url( __FILE__ ) . 'css/pressgram-admin.css', array(), $this->version, 'screen' );
		
		wp_enqueue_style( 'pressgram-select2', plugins_url( '/pressgram/css/lib/select2.css' ) );

		wp_enqueue_script( 'pressgram-select2', plugins_url( '/pressgram/js/lib/select2.min.js' ) );
		wp_enqueue_script( 'pressgram', plugins_url( '/pressgram/js/admin.min.js' ), array( 'jquery', 'pressgram-select2' ), $this->version, false );

	} // end add_stylesheets_and_javascript

	/**
	 * Registers the Pressgram Category and Fine Control setting and field with the WordPress Settings API.
	 *
	 * @since    2.1.0
	 */
	public function register_pressgram_fields() {

		// First, register a settings section
		add_settings_section( 'pressgram', 'Pressgram', array( $this, 'display_pressgram_section' ), 'media' );

		// Then, register the settings for the Pressgram fields
		register_setting( 'media', 'pressgram_category', 'esc_attr' );
		register_setting( 'media', 'pressgram_fine_control' );
		register_setting( 'media', 'pressgram_post_relation' );

		// Now introduce the settings fields
		add_settings_field(
			'pressgram_category',
			__( 'Category' , 'pressgram-locale' ),
			array( $this, 'display_pressgram_category' ) ,
			'media',
			'pressgram'
		);
		add_settings_field(
			'pressgram_fine_control',
			__( 'Fine Control' , 'pressgram-locale' ),
			array( $this, 'display_pressgram_fine_control' ) ,
			'media',
			'pressgram'
		);
		add_settings_field(
			'pressgram_post_relation',
			__( 'Post Relations' , 'pressgram-locale' ),
			array( $this, 'display_pressgram_post_relations' ) ,
			'media',
			'pressgram'
		);

	} // end register_pressgram_options

	 /**
	 * Renders the intro to the Pressgram section of the media page.
	 *
	 * @since    2.1.0
	 */
	public function display_pressgram_section() {

		// Build the section intro
		$html = '<div id="pressgram-section">';
			$html .= $this->get_notices();
			$html .= '<a href="http://pressgr.am"><img src="' . plugin_dir_url( __FILE__ ) . 'pressgram-logo.png" style="border-radius:100%;"/></a>';
			$html .= 'Select a category for Pressgram, which will ...<br />&nbsp;&nbsp;&nbsp; ';
			$html .= '(1) be automatically assigned to all Pressgram uploads,<br />&nbsp;&nbsp;&nbsp; ';
			$html .= '(2) enable application of your custom fine control settings, and<br />&nbsp;&nbsp;&nbsp; ';
			$html .= '(3) mark post relation with Pressgram.';
		$html .= '</div>';

		// Echo the section description
		echo $html;
	}

	/**
	 * Renders the select option for the category and allows users to select what category that want to use
	 * as the Pressgram category.
	 *
	 * @since    1.0.0
	 */
	public function display_pressgram_category() {

		// Build up the list of available categories
		$categories = $this->get_categories();
		$html =  '<select id="pressgram_category" name="pressgram_category">';

			$html .= '<option value="default"' . selected( 'default', $this->pressgram_category, FALSE ) . '>' . __( 'Select a category...', 'pressgram-locale' ) . '</option>';

			foreach ( $categories as $category ) {
				$html .= '<option value="' . $category->cat_ID . '"' . selected( $category->cat_ID, $this->pressgram_category, FALSE ) . '>' . $category->name . '</option>';
			} // end foreach

		$html .= '</select>';

		$html .= '&nbsp;';
		$html .= '<p class="description">' . __( 'Or you can <a href="edit-tags.php?taxonomy=category">create a new category</a>.', 'pressgram-locale' ) . '</p>';

		echo $html;

	} // end display_pressgram_category

	/**
	 * Renders the options for the fine control of Pressgram posts tagged with the Pressgram category
	 * including options for type, status, format, featured image, alignment, link, comments, pings
	 * tags, and content
	 *
	 * @since    2.0.2
	 */
	public function display_pressgram_fine_control() {

		$html = '<fieldset>';

		// Build the list of strip options
		$html .= 'Include above categorized posts ... ';
		$this->options['show']['home'] = isset( $this->options['show']['home'] ) ? $this->options['show']['home'] : FALSE;
		
		$html .= '<input type="checkbox" id="pressgram_fine_control_show_home" name="pressgram_fine_control[show][home]" value="1"';
			$html .= $this->options['show']['home'] ? ' checked="checked">' : '>';
			$html .= __( 'on home page <em>and/or</em> ', 'pressgram-locale' ) . '</input>';
		$this->options['show']['feed'] = isset( $this->options['show']['feed'] ) ? $this->options['show']['feed'] : FALSE;
		$html .= '<input type="checkbox" id="pressgram_fine_control_show_feed" name="pressgram_fine_control[show][feed]" value="1"';
			$html .= $this->options['show']['feed'] ? ' checked="checked">' : '>';
			$html .= __( 'in feeds', 'pressgram-locale' ) . '</input>';
		$html .= '<br /><span class="description">(note: will likely only work if Post Type is set to Post)</span>';

		$html .= '<br /><br />';

		// Build up the list of post types
		$post_types = get_post_types( array( 'public' => TRUE ) );
		$html .= '<label>' . __( 'Post Type:', 'pressgram-locale' );
			$html .= ' <select id="pressgram_fine_control_post_type" name="pressgram_fine_control[post_type]">';

				foreach( $post_types as $post_type ) {
					$supported_taxonomies = get_object_taxonomies( $post_type );
					if ( in_array( 'category', $supported_taxonomies ) ) {
						$post_format_support = post_type_supports( $post_type, 'post-formats' ) ? 'support' : 'no-support';
						$featured_image_support = post_type_supports( $post_type, 'thumbnail' ) ? 'support' : 'no-support';
						$comment_support = post_type_supports( $post_type, 'comments' ) ? 'support' : 'no-support';
						$trackback_support = post_type_supports( $post_type, 'trackbacks' ) ? 'support' : 'no-support';
						$tag_support = in_array( 'post_tag', $supported_taxonomies ) ? 'support' : 'no-support';

						$html .= '<option value="' . $post_type . '" pfsupport="' . $post_format_support . '" pfisupport="' . $featured_image_support . '" pcsupport="' . $comment_support . '" ptsupport="' . $trackback_support . '" ptgsupport="' . $tag_support . '"';
							$html .= $this->options['post_type'] == $post_type ? ' selected="selected"' : '';
						 	$html .= '>' . get_post_type_object( $post_type )->labels->singular_name . '</option>';
					}
				}
				$html .= '<option value="attachment" pfsupport="no-support" pfisupport="no-support" pcsupport="no-support" ptsupport="no-support" ptgsupport="no-support"';
					$html .= $this->options['post_type'] == 'attachment' ? ' selected="selected">' : '>';
					$html .= __( 'Unattached Media', 'pressgram-locale' ) . '</option>';

			$html .= '</select>';
		$html .= '</label>';

		$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

		// Build up the list of post statuses
		$html .= '<label>' . __( 'Post Status:', 'pressgram-locale' );
			$html .= ' <select id="pressgram_fine_control_post_status" name="pressgram_fine_control[post_status]">';

				if ( 'attachment' != $this->options['post_type'] ) {
					$html .= '<option value="publish"';
						$html .= $this->options['post_status'] == 'publish' ? ' selected="selected"' : '';
						$html .= '>' . __( 'Published', 'pressgram-locale' ) . '</option>';
					$html .= '<option value="pending"';
						$html .= $this->options['post_status'] == 'pending' ? ' selected="selected"' : '';
						$html .= '>' . __( 'Pending', 'pressgram-locale' ) . '</option>';
					$html .= '<option value="draft"';
						$html .= $this->options['post_status'] == 'draft' ? ' selected="selected"' : '';
						$html .= '>' . __( 'Draft', 'pressgram-locale' ) . '</option>';
					$html .= '<option value="private"';
						$html .= $this->options['post_status'] == 'private' ? 'selected="selected"' : '';
						$html .= '>' . __( 'Private', 'pressgram-locale' ) . '</option>';
				} else {
					$html .= '<option value="inherit"';
						$html .= $this->options['post_status'] == 'inherit' ? ' selected="selected"' : '';
						$html .= '>' . __( 'Inherit', 'pressgram-locale' ) . '</option>';
				}

			$html .= '</select>';
		$html .= '</label>';

		$html .= '<br />';

		// Build up the list of post formats
		$post_formats = get_theme_support( 'post-formats' );
		$html .= '<label>' . __( 'Post Format:', 'pressgram-locale' );
			$html .= ' <select id="pressgram_fine_control_post_format" name="pressgram_fine_control[post_format]">';

			if ( ( is_array( $post_formats ) || $post_formats ) && post_type_supports( $this->options['post_type'], 'post-formats' ) ) {
				$post_formats = is_array( $post_formats ) ? $post_formats : array( array() );
				array_push( $post_formats[0], 'standard' );

				foreach ( $post_formats[0] as $post_format ) {
					$html .= '<option value="' . $post_format . '"';
					$html .= $this->options['post_format'] == $post_format ? ' selected="selected"' : '';
					$html .= '>' . ucfirst( $post_format ) . '</option>';
				}
			} else {
				$html .= '<option value="standard" selected="selected">' . __( 'Standard', 'pressgram-locale' ) . '</option>';
			}

			$html .= '</select>';
		$html .= '</label>';

		$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

		// Build the field for featured image support
		$this->options['featured_img'] = isset( $this->options['featured_img'] ) ? $this->options['featured_img'] : FALSE;
		if ( current_theme_supports( 'post-thumbnails' ) && post_type_supports( $this->options['post_type'], 'thumbnail' ) ) {
			$html .= '<input type="checkbox" id="pressgram_fine_control_featured_img" name="pressgram_fine_control[featured_img]" value="1"';
				$html .= $this->options['featured_img'] ? ' checked="checked"></input>' : '></input>';
			$html .= '<span id="featured_img_support">' . __( ' Set featured image', 'pressgram-locale' ) . '</span>';
			$html .= '<span id="featured_img_no_support" class="description" style="display:none;">' . __( 'Featured image is not currently supported', 'pressgram-locale' ) . '</span>';
		} else {
			$html .= '<input type="hidden" id="pressgram_fine_control_featured_img" name="pressgram_fine_control[featured_img]" value="0" />';
			$html .= '<span id="featured_img_support" style="display:none;">' . __( ' Set featured image', 'pressgram-locale' ) . '</span>';
			$html .= '<span id="featured_img_no_support" class="description">' . __( 'Featured image is not currently supported', 'pressgram-locale' ) . '</span>';
		}
		
		$html .= '<br />';

		// Build the list of image alignment
		$html .= '<label>' . __( 'Image Alignment:', 'pressgram-locale' );
			$html .= ' <select id="pressgram_fine_control_align" name="pressgram_fine_control[img_align]"';
				$html .= 'attachment' == $this->options['post_type'] ? ' disabled="disabled">' : '>';

				if ( 'attachment' != $this->options['post_type'] ) {
					$html .= '<option value="none"';
						$html .= $this->options['img_align'] == 'none' ? ' selected="selected"' : '';
						$html .= '>' . __( 'None', 'pressgram-locale' ) . '</option>';
					$html .= '<option value="left"';
						$html .= $this->options['img_align'] == 'left' ? ' selected="selected"' : '';
						$html .= '>' . __( 'Left', 'pressgram-locale' ) . '</option>';
					$html .= '<option value="center"';
						$html .= $this->options['img_align'] == 'center' ? ' selected="selected"' : '';
						$html .= '>' . __( 'Center', 'pressgram-locale' ) . '</option>';
					$html .= '<option value="right"';
						$html .= $this->options['img_align'] == 'right' ? 'selected="selected"' : '';
						$html .= '>' . __( 'Right', 'pressgram-locale' ) . '</option>';
				} else {
					$html .= '<option value="0">' . __( 'Not Supported', 'pressgram-locale' ) . '</option>';
				}

			$html .= '</select>';
		$html .= '</label>';

		$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

		// Build the list of image links
		$html .= '<label>' . __( 'Link Image To:', 'pressgram-locale' );
			$html .= ' <select id="pressgram_fine_control_link" name="pressgram_fine_control[img_link]"';
				$html .= 'attachment' == $this->options['post_type'] ? ' disabled="disabled">' : '>';

				if ( 'attachment' != $this->options['post_type'] ) {
					$html .= '<option value="none"';
						$html .= $this->options['img_link'] == 'none' ? ' selected="selected"' : '';
						$html .= '>' . __( 'None', 'pressgram-locale' ) . '</option>';
					$html .= '<option value="link"';
						$html .= $this->options['img_link'] == 'link' ? ' selected="selected"' : '';
						$html .= '>' . __( 'Media File', 'pressgram-locale' ) . '</option>';
					$html .= '<option value="post"';
						$html .= $this->options['img_link'] == 'post' ? ' selected="selected"' : '';
						$html .= '>' . __( 'Attachment Page', 'pressgram-locale' ) . '</option>';
				} else {
					$html .= '<option value="0">' . __( 'Not Supported', 'pressgram-locale' ) . '</option>';
				}

			$html .= '</select>';
		$html .= '</label>';

		$html .= '<br /><br />';

		// Build the field for comment support
		$this->options['comments'] = isset( $this->options['comments'] ) ? $this->options['comments'] : FALSE;
		$html .= '<input type="checkbox" id="pressgram_fine_control_comments" name="pressgram_fine_control[comments]" value="1"';
			$html .= $this->options['comments'] ? ' checked="checked"></input>' : '></input>';
		$html .= '<span> ' . __( 'Allow Comments', 'pressgram-locale' ) . '</span>';
		$html .= '<span id="comments_no_support" class="description"';
			$html .= ( ! post_type_supports( $this->options['post_type'], 'comments' ) || 'attachment' == $this->options['post_type'] ) ? '>' : ' style="display:none;">';
			$html .= __( ' (not supported by current post type)', 'pressgram-locale' ) . '</span>';

		$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

		// Build the field for trackback support
		$this->options['pings'] = isset( $this->options['pings'] ) ? $this->options['pings'] : FALSE;
		$html .= '<input type="checkbox" id="pressgram_fine_control_pings" name="pressgram_fine_control[pings]" value="1"';
			$html .= $this->options['pings'] ? ' checked="checked"></input>' : '></input>';
		$html .= '<span> ' . __( 'Allow Trackbacks', 'pressgram-locale' ) . '</span>';
		$html .= '<span id="pings_no_support" class="description"';
			$html .= ( ! post_type_supports( $this->options['post_type'], 'pings' ) || 'attachment' == $this->options['post_type'] ) ? '>' : ' style="display:none;">';
			$html .= __( ' (not supported by current post type)', 'pressgram-locale' ) . '</span>';
		
		$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

		// Build the field for post tag support
		$this->options['tag_post'] = isset( $this->options['tag_post'] ) ? $this->options['tag_post'] : FALSE;

		$supported_taxonomies = get_object_taxonomies( $this->options['post_type'] );
		if ( in_array( 'post_tag', $supported_taxonomies ) ) {
			$html .= '<input type="checkbox" id="pressgram_fine_control_tag_post" name="pressgram_fine_control[tag_post]" value="1"';
				$html .= $this->options['tag_post'] ? ' checked="checked"></input>' : '></input>';
			$html .= '<span id="post_tag_support"> ' . __( 'Translate #hashtags to post tags', 'pressgram-locale' ) . '</span>';
			$html .= '<span id="post_tag_no_support" class="description" style="display:none;"> ' . __( 'Post Type does not support tags', 'pressgram-locale' ) . '</span>';
		} else {
			$html .= '<input type="hidden" id="pressgram_fine_control_tag_post" name="pressgram_fine_control[tag_post]" value="0" /><span id="post_tag_support" style="display:none;">' . __( 'Translate #hashtags to post tags', 'pressgram-locale' ) . '</span>';
			$html .= '<span id="post_tag_no_support" class="description">' . __( 'Post Type does not support tags', 'pressgram-locale' ) . '</span>';
		}

		$html .= '<br /><br />';

		// Build the list of strip options
		$this->options['strip']['hashtags'] = isset( $this->options['strip']['hashtags'] ) ? $this->options['strip']['hashtags'] : FALSE;
		$html .= '<input type="checkbox" id="pressgram_fine_control_strip_hashtags" name="pressgram_fine_control[strip][hashtags]" value="1"';
			$html .= $this->options['strip']['hashtags'] ? ' checked="checked">' : '>';
			$html .= __( 'Remove #hashtags', 'pressgram-locale' ) . '</input>';
			$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';
		$this->options['strip']['text'] = isset( $this->options['strip']['text'] ) ? $this->options['strip']['text'] : FALSE;
		$html .= '<input type="checkbox" id="pressgram_fine_control_strip_text" name="pressgram_fine_control[strip][text]" value="1"';
			$html .= $this->options['strip']['text'] ? ' checked="checked">' : '>';
			$html .= __( 'Remove non-#hashtag text', 'pressgram-locale' ) . '</input>';
			$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';
		$this->options['strip']['image'] = isset( $this->options['strip']['image'] ) ? $this->options['strip']['image'] : FALSE;
		$html .= '<input type="checkbox" id="pressgram_fine_control_strip_image" name="pressgram_fine_control[strip][image]" value="1"';
			$html .= $this->options['strip']['image'] ? ' checked="checked">' : '>';
			$html .= __( 'Remove image', 'pressgram-locale' ) . '</input>';

		echo $html;
	} // end display_pressgram_fine_control

	/**
	 * Renders the options for the post type relation for Pressgram posts
	 * which will allow display of Pressgram Post checkbox in Publicize metabox
	 *
	 * @since    2.1.0
	 */
	public function display_pressgram_post_relations() {

		$html = '<fieldset>';

		// Build the list of post types
		$html .= 'Allow Pressgram post relation control for the following post types:';
		$html .= '<br />';
		$html .= '<span class="description">(post relations control images featured in the Pressgram widget)</span>';
		$html .= '<br /><br />';

		$post_types = get_post_types( array( 'public' => TRUE ) );

		foreach ( $post_types as $post_type ) {
			if ( 'attachment' != $post_type && 'page' != $post_type ) {
				$pressgram_relation_exists = isset( $this->pressgram_post_relation[ $post_type ] ) ? $this->pressgram_post_relation[ $post_type ] : FALSE;
				$html .= '<input type="checkbox" id="pressgram_post_relation_' . $post_type . '" name="pressgram_post_relation[' . $post_type . ']" value="1" ' . checked( $pressgram_relation_exists, TRUE, FALSE ) . ' /> <label for="pressgram_post_relation_' . $post_type . '">' . get_post_type_object( $post_type )->labels->singular_name . '</label><br />';
			}
		}

		$html .= '</fieldset>';

		echo $html;
	}

	/**
	 * Renders jquery script to control options upon post type selection
	 *
	 * @since    2.1.0
	 */
	public function process_selected_post_type() {

		$post_types = get_post_types( array( 'public' => TRUE ) );
		$post_formats = NULL != get_theme_support( 'post-formats' ) ? get_theme_support( 'post-formats' ) : array( array() );
		array_push( $post_formats[0], 'standard' ); ?>

		<script type="text/javascript" language="javascript">
			jQuery(document).ready(function($) {

				$pfcPostType = $("#pressgram_fine_control_post_type");
				$pfcPostStatus = $("#pressgram_fine_control_post_status");
				$pfcPostFormat = $("#pressgram_fine_control_post_format");
				$pfcFeaturedImg = $('#pressgram_fine_control_featured_img');
				$pfcImgAlign = $('#pressgram_fine_control_align');
				$pfcImgLink = $('#pressgram_fine_control_link');
				$pfcComments = $("#pressgram_fine_control_comments");
				$pfcPings = $("#pressgram_fine_control_pings"); 
				$pfcTags = $('#pressgram_fine_control_tag_post');

				$pfcPostType.change(function() {
					<?php
					foreach( $post_types as $post_type ) { ?>
						if ($(this).val() == <?php echo '"' . $post_type . '"'; ?>) {
							if ($(this).find(':selected').val() == 'attachment') {
								$("select[id='pressgram_fine_control_post_status'] option").remove();
								$("<option value='inherit'><?php _e( 'Inherit', 'pressgram-locale' ); ?></option>").appendTo($pfcPostStatus);
							} else {
								$("select[id='pressgram_fine_control_post_status'] option").remove();
								$("<option value='publish' <?php echo ( $this->options['post_status'] == 'publish' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Published', 'pressgram-locale' ); ?></option>").appendTo($pfcPostStatus);
								$("<option value='pending' <?php echo ( $this->options['post_status'] == 'pending' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Pending', 'pressgram-locale' ); ?></option>").appendTo($pfcPostStatus);
								$("<option value='draft' <?php echo ( $this->options['post_status'] == 'draft' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Draft', 'pressgram-locale' ); ?></option>").appendTo($pfcPostStatus);
								$("<option value='private' <?php echo ( $this->options['post_status'] == 'private' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Private', 'pressgram-locale' ); ?></option>").appendTo($pfcPostStatus);
							}

							if ($(this).find(':selected').attr('pfsupport') == 'no-support') {
								$("select[id='pressgram_fine_control_post_format'] option").remove();
								$("<option value='standard'><?php _e( 'Standard', 'pressgram-locale' ); ?></option>").appendTo($pfcPostFormat);
							} else {
								$("select[id='pressgram_fine_control_post_format'] option").remove();
								<?php
								foreach ( $post_formats[0] as $post_format ) {
									?>
									$("<option value='<?php echo $post_format; ?>' <?php echo ( $this->options['post_format'] == $post_format ) ? 'selected=\'selected\'' : ''; ?>><?php echo ucfirst( $post_format ); ?></option>").appendTo($pfcPostFormat);
									<?php
								}
								?>
							}

							if ($(this).find(':selected').attr('pfisupport') == 'no-support') {
								$pfcFeaturedImg.attr('value', '0').attr('type', 'hidden');
								$('#featured_img_support').hide();
								$('#featured_img_no_support').show();
							} else {
								$pfcFeaturedImg.attr('value', '1').attr('type', 'checkbox');
								$('#featured_img_support').show();
								$('#featured_img_no_support').hide();
							}

							if ($(this).find(':selected').val() == 'attachment') {
								$("select[id='pressgram_fine_control_align'] option").remove();
								$("<option value='0'><?php _e( 'Not Supported', 'pressgram-locale' ); ?></option>").appendTo($pfcImgAlign);
								$pfcImgAlign.attr('disabled', 'disabled');
							} else {
								$("select[id='pressgram_fine_control_align'] option").remove();
								$("<option value='none' <?php echo ( $this->options['img_align'] == 'none' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'None', 'pressgram-locale' ); ?></option>").appendTo($pfcImgAlign);
								$("<option value='left' <?php echo ( $this->options['img_align'] == 'left' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Left', 'pressgram-locale' ); ?></option>").appendTo($pfcImgAlign);
								$("<option value='center' <?php echo ( $this->options['img_align'] == 'center' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Center', 'pressgram-locale' ); ?></option>").appendTo($pfcImgAlign);
								$("<option value='right' <?php echo ( $this->options['img_align'] == 'right' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Right', 'pressgram-locale' ); ?></option>").appendTo($pfcImgAlign);
								$pfcImgAlign.removeAttr('disabled');
							}

							if ($(this).find(':selected').val() == 'attachment') {
								$("select[id='pressgram_fine_control_link'] option").remove();
								$("<option value='0'><?php _e( 'Not Supported', 'pressgram-locale' ); ?></option>").appendTo($pfcImgLink);
								$pfcImgLink.attr('disabled', 'disabled');
							} else {
								$("select[id='pressgram_fine_control_link'] option").remove();
								$("<option value='none' <?php echo ( $this->options['img_link'] == 'none' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'None', 'pressgram-locale' ); ?></option>").appendTo($pfcImgLink);
								$("<option value='link' <?php echo ( $this->options['img_link'] == 'link' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Media File', 'pressgram-locale' ); ?></option>").appendTo($pfcImgLink);
								$("<option value='post' <?php echo ( $this->options['img_link'] == 'post' ) ? 'selected=\'selected\'' : ''; ?>><?php _e( 'Attachment Page', 'pressgram-locale' ); ?></option>").appendTo($pfcImgLink);
								$pfcImgLink.removeAttr('disabled');
							}

							if ($(this).find(':selected').attr('pcsupport') == 'no-support') {
								$('#comments_no_support').show();
							} else {
								$('#comments_no_support').hide();
							}

							if ($(this).find(':selected').attr('ptsupport') == 'no-support') {
								$('#pings_no_support').show();
							} else {
								$('#pings_no_support').hide();
							}

							if ($(this).find(':selected').attr('ptgsupport') == 'no-support') {
								$pfcTags.attr('value', '0').attr('type', 'hidden');
								$('#post_tag_support').hide();
								$('#post_tag_no_support').show();
							} else {
								$pfcTags.attr('value', '1').attr('type', 'checkbox');
								$('#post_tag_support').show();
								$('#post_tag_no_support').hide();
							}

							if ($(this.selected)) {
								$('#pressgram_post_relation_<?php echo $post_type; ?>').attr('checked', 'checked');
							}
						} <?php
					} ?>
				});

				$('.pressgram-notice').hover(
					function() { $('#pressgram-outer-container').css('min-width', '800px') },
					function() { $('#pressgram-outer-container').not('.active').css('min-width', '62px') }
				);

				$('.pressgram-notice').click(function() {
					$(this).toggleClass('selected');
					$('#pressgram-outer-container').toggleClass('active');
				}).find('a','img').click(function(e) {
					e.stopPropagation();
				});
			});
		</script>
		<?php
	} // end process_selected_post_type

	/**
	 * Checks whether published posts are newly created from and originate from an XMLRPC request
	 * from the Pressgram application
	 *
	 * @since    2.0.5
	 */

	public function do_pressgram( $new_status, $old_status, $post ) {

		// Check if post is transitioning from new or auto-draft to publish
		if ( ( 'new' == $old_status || 'auto-draft' == $old_status ) && ( 'publish' == $new_status ) && defined('XMLRPC_REQUEST') && strpos( $post->post_content, 'pressgram-image-file' ) ) {
			return TRUE;
		} else {
			return FALSE;
		}
	} // end do_pressgram

	/**
	 * Categorizes Pressgram posts in the Pressgram category and checks for power tags
	 * resetting options as needed. Sets tags.
	 *
	 * @since    2.0.5
	 */

	public function process_taxonomies( $new_status, $old_status, $post ) {

		// Check if post is in Pressgram category and if it is transitioning from new or auto-draft to publish
		if ( $this->do_pressgram( $new_status, $old_status, $post ) ) {
			
			// Get app assigned categories 
			$app_categories = wp_get_object_terms( $post->ID, 'category', array( 'fields' => 'all' ) );

			// Add all app categories to array (exclude uncategorized category)
			$categories = array();
			foreach ( $app_categories as $key => $category_object ) {
				'Pressgram' != $category_object->name ? $categories[ $key ] = $category_object->term_id : FALSE;
			}

			// Add selected Pressgram category to the array
			array_push( $categories, $this->pressgram_category );

			// Re-key the array
			$categories = array_values( $categories );

			// Assign categories to this pressgram post
			$this->pressgram_post['categories'] = $categories;

			// Get app assigned tags
			$app_tags = wp_get_object_terms( $post->ID, 'post_tag', array( 'fields' => 'all' ) );

			// Begin processing of tags
			$tags = array();
			foreach ( $app_tags as $key => $tag_object ) {

				// Check if tag is power tag
				$power_tag = preg_match( '/_[tsfialcphr]:/', trim( $tag_object->name ) );

				// Apply power tags
				if ( $power_tag ) {

					// Get tag ID (single identifying letter)
					$power_tag_ID = $tag_object->name{1};

					// Get value by trimming first three characters from power tag
					$power_tag = substr( trim( $tag_object->name ), 3 );

					switch ( $power_tag_ID ) {

						// Apply post type
						case 't':
							// If fine control post type is attachment, change post status from inherit to publish
							if ( 'attachment' == $this->options['post_type'] && 'attachment' != $power_tag ) {
								$this->options['post_status'] = 'publish';
							}
							$this->options['post_type'] = $power_tag;
							break;

						// Apply post status
						case 's':
							$this->options['post_status'] = $power_tag;
							break;

						// Apply post format
						case 'f':
							$this->options['post_format'] = $power_tag;
							break;

						// Apply featured image
						case 'i':
							$this->options['featured_img'] = 't' == $power_tag ? TRUE : FALSE;
							break;

						// Apply image alignment
						case 'a':
							$this->options['img_align'] = $power_tag;
							break;

						// Apply image link
						case 'l':
							$this->options['img_link'] = $power_tag;
							break;
						
						// Apply comment status
						case 'c':
							$this->options['comments'] = 't' == $power_tag ? TRUE : FALSE;
							break;
						
						// Apply ping status
						case 'p':
							$this->options['pings'] = 't' == $power_tag ? TRUE : FALSE;
							break;
						
						// Apply transferrence of hashtags to post tags
						case 'h':
							$this->options['tag_post'] = 't' == $power_tag ? TRUE : FALSE;
							break;
						
						// Apply removal of various content
						case 'r':
							'hashtags' == $power_tag ? $this->options['strip']['hashtags'] = TRUE : FALSE;
							'text' == $power_tag ? $this->options['strip']['text'] = TRUE : FALSE;
							'image' == $power_tag ? $this->options['strip']['image'] = TRUE : FALSE;
							'img' == $power_tag ? $this->options['strip']['image'] = TRUE : FALSE;
							break;

						default:
							break;
					}

					// remove power tag from available tags in WordPress
					wp_delete_term( $tag_object->term_id, 'post_tag' );
				} else {

					// if not a power tag, add to array
					$tags[ $key ] = $tag_object->name;
				}
			}

			// Set this pressgram post tags
			$this->pressgram_post['tags'] = $tags;
		}
	} // end process_taxonomies

	/**
	 * Checks whether published posts are newly created from and originiate from an XMLRPC request
	 * Categorizes such posts in the Pressgram category and filters posts through the fine control settings
	 *
	 * @since    2.0.5
	 */

	public function apply_fine_control( $new_status, $old_status, $post ) {

		// Check if post is in Pressgram category and if it is transitioning from new or auto-draft to publish
		if ( $this->do_pressgram( $new_status, $old_status, $post ) ) {

			// Retrieve the new post object
			$post = get_post( $post->ID );

			// Reset post categories, overwriting all existing categories
			wp_set_post_terms( $post->ID, $this->pressgram_post['categories'], 'category' );

			// Reset post tags
			wp_set_post_tags( $post->ID, $this->pressgram_post['tags'], FALSE );

			// Get the first attachment image
			$attachment = get_children( "post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );  // get child attachments of type image
				
			// Get post ID of attachment
			$attachment_ID = current( array_keys( $attachment ) );

			// Parse the content of the post
			$parsed_content = $this->parse_content( $post->post_content );

			// Translate hashtags to post tags
			$supported_taxonomies = get_object_taxonomies( $this->options['post_type'] );
			( in_array( 'post_tag', $supported_taxonomies ) && $this->options['tag_post'] ) ? wp_set_post_tags( $post->ID, $parsed_content['tags'], TRUE ) : FALSE;

			// Strip unwanted content of the post
			$content = $this->strip_content( $post->post_content, $parsed_content );

			// Hyperlink the image
			switch ( $this->options['img_link'] ) {
				case 'link':
					$content = str_replace( $parsed_content['img'], '<a href="' . wp_get_attachment_url( $attachment_ID ) . '">' . $parsed_content['img'] . '</a>', $content );
					break;
				
				case 'post':
					$content = str_replace( $parsed_content['img'], '<a href="' . get_attachment_link( $attachment_ID ) . '">' . $parsed_content['img'] . '</a>', $content );
					// Save parsed content to the database
					break;
				
				default:
					break;
			}

			// Set desired alignment
			$content = str_replace( 'pressgram-image-file', 'pressgram-image-file align' . $this->options['img_align'], $content );

			// Check that fine control post type is not set as attachment
			if ( 'attachment' != $this->options['post_type'] ) {

				// Set the post format (if not standard)
				'standard' != $this->options['post_format'] ? set_post_format( $post->ID, $this->options['post_format'] ) : FALSE;

				// Set featured image (if selected as fine control option)
				$this->options['featured_img'] ? set_post_thumbnail( $post->ID, $attachment_ID ) : FALSE;
				
				// Set post array
				$post = array(
					'ID'             => $post->ID,
					'post_status'    => $this->options['post_status'],
					'post_content'   => $content,
					'post_type'      => $this->options['post_type'],
					'comment_status' => $this->options['comments'] ? 'open' : 'closed',
					'ping_status'    => $this->options['pings'] ? 'open' : 'closed',
					);

				// Update the post
				wp_update_post( $post );

				// Add pressgram_post meta
				add_post_meta( $post['ID'], '_pressgram_post', TRUE, TRUE );
				// Add pressgram_image meta
				add_post_meta( $attachment_ID, '_pressgram_image', TRUE, TRUE );

				// If post is not published, remove Jetpack publicized flag
				'publish' != $this->options['post_status'] ? delete_post_meta( $post['ID'], '_wpas_done_all' ) : FALSE;
			} else {
				
				// Set the attachment post array
				$attachment = array(
					'ID'           => $attachment_ID,
					'post_status'  => 'inherit',
					'post_excerpt' => $content,
					'post_parent'  => '',
					);

				// Update the attachment post
				wp_update_post( $attachment );

				// Add pressgram_image meta
				add_post_meta( $attachment['ID'], '_pressgram_image', TRUE, TRUE );

				// Delete the published parent post, if attachment post (skip the trash)
				wp_delete_post( $post->ID, TRUE );
			}
		}
	}

	/**
	 * Split content into various parts, returning an array of parts.
	 *
	 * @since    2.1.0
	 */
	public function parse_content( $content ) {
		// Get Pressgram image container div element
		$div_exists = preg_match( '/<div class=.pressgram-image-block.>\s*\n*.*div>/sim', $content, $match );
		$div = $match[0];
		$div = str_replace( array( '\r\n', '\r', '\n', '\t' ), '', $div );

		// Get image element
		$img_exists = preg_match( '/<img[^>]*>/sim', $div, $match );
		$img = $match[0];

		// Get image link
		$img_link_exists = preg_match( '/(?<=src=.)[^"\']*/sim', $img, $match );
		$img_link = $match[0];

		// Get body content
		$body = trim( strip_tags( str_replace( $div, '', $content ), '<a></a>' ) );

		// Get hashtags
		$hashtags_count = preg_match_all( '/(?=#).[^\s#.?!,\'"]*/sim', $content, $matches );
		$hashtags = $matches[0];

		// Process hashtag array to tag array
		if ( ! empty( $hashtags[0] ) ) {
			foreach ( $hashtags as $index => $hashtag ) {

				// Strip # from hashtag
				$tags[ $index ] = str_replace( '#', '', $hashtag );
			}
		} else {

			// Initialize tags array if no hashtags exist
			$tags = array();
		}

		// Parse body content to text array
		$text_string = $body;
		$text_array = array();
		if ( ! empty( $hashtags[0] ) ) {
			foreach ( $hashtags as $index => $hashtag ) {
				// Split text string at hashtag
				$split_by_hashtag = explode( $hashtag, $text_string );

				// Push first string of split to text_array
				'' != trim( $split_by_hashtag[0] ) ? array_push( $text_array, trim( $split_by_hashtag[0] ) ) : FALSE;
				$text_string = $split_by_hashtag[1];
			}

			// Push final string of splits to text array
			'' != trim( $split_by_hashtag[1] ) ? array_push( $text_array, trim( $split_by_hashtag[1] ) ) : FALSE;
		}

		// Define parsed content array
		$parsed_content = array(
			'div'      => $div,
			'img'      => $img,
			'img_link' => $img_link,
			'body'     => $body,
			'hashtags' => $hashtags,  // array of hashtags
			'tags'     => $tags,  // array of tags
			'text'     => $text_array,  // array of text sections
			);

		return $parsed_content;
	} // end parse_content

	/**
	 * Strips the content of the post based on users fine control settings.
	 *
	 * @since    2.0.0
	 */
	public function strip_content( $content, $parsed_content ) {
		// Strip image if desired or if post type is attachment
		$stripped_content = ( $this->options['strip']['image'] || 'attachment' == $this->options['post_type'] ) ? str_replace( $parsed_content['div'], '', $content ) : $content;

		// Strip hashtags if desired
		$stripped_content = $this->options['strip']['hashtags'] ? str_replace( $parsed_content['hashtags'], '', $stripped_content ) : $stripped_content;

		// Strip text if desired
		$stripped_content = $this->options['strip']['text'] ? str_replace( $parsed_content['text'], '', $stripped_content ) : $stripped_content;

		// Return the stripped content
		return $stripped_content;
	} // end strip_content

	/**
	 * Adds a checkbox to the Publicize metabox on post pages so that
	 * posts can be marked as Pressgram posts and featured in widget
	 *
	 * @since    2.1.0
	 */
	public function post_page_metabox() {

		// Get the global post object
		global $post;

		// Check if Pressgram post relation is enabled for post type
		$pressgram_relation_exists = isset( $this->pressgram_post_relation[ $post->post_type ] ) ? $this->pressgram_post_relation[ $post->post_type ] : FALSE;
		
		// Show checkbox if relation exists
		if ( $pressgram_relation_exists ) {
			// Get the specific _pressgram_post metadata
			$is_pressgram_post = get_post_meta( $post->ID, '_pressgram_post', TRUE );

			// Display the checkbox (css located in pressgram-admin.css)
			$html = '<div class="misc-pub-section pressgram-post" id="pressgram-post-meta">';
				$html .= '<input type="checkbox" name="_pressgram_post" id="_pressgram_post" value="1" ';
				$html .= checked( $is_pressgram_post, TRUE, FALSE );
				$html .= ' /> <label for="_pressgram_post">Pressgram Post</label> <span class="description">(feature in widget)</span>';
			$html .= '</div>';

			echo $html;
		}
	} // end post_page_metabox

	/**
	 * Saves metadata related to Pressgram post checkbox on Publicize metabox
	 *
	 * @since    2.1.0
	 */
	public function save_pressgram_post_metabox_data( $post_ID ) {

		// Get the global pagenow
		global $pagenow;

		// Get the post type
		$post_type = get_post_type( $post_ID );

		// Check if Pressgram post relation is enabled for post type
		$pressgram_relation_exists = isset( $this->pressgram_post_relation[ $post_type ] ) ? $this->pressgram_post_relation[ $post_type ] : FALSE;

		// Show checkbox if relation exists and save request is coming from post edit page or bulk_edit
		if ( $pressgram_relation_exists && ( 'post.php' == $pagenow ) ) {

			// Get the posted data for whether post is marked as Pressgram
			$is_pressgram_post = isset( $_POST['_pressgram_post'] ) ? TRUE : FALSE;

			// Update the post meta
			update_post_meta( $post_ID, '_pressgram_post', $is_pressgram_post );
		}
	}

	/**
	 * Modifies the query for the main loop excluding all posts from the selected
	 * Pressgram category so that they do not appear in the main loop.
	 *
	 * @since    2.1.0
	 */
	public function exclude_pressgram_category_posts( $wp_query ) {

			// If it's a feed or home, check if it should be shown ... otherwise, hide.
			if ( ( is_feed() && ! $this->options['show']['feed'] ) || ( is_home() && ! $this->options['show']['home'] ) ) {
				
				// Add the category to an array of excluded categories. In this case, though, it's really just one.
				$exclude = array( $this->pressgram_category );

				// This is a cleaner way to write: $wp_query->set('category__not_in', $excluded);
				set_query_var( 'category__not_in', $exclude );

			} // end if

	} // end exclude_pressgram_category_posts

	/*---------------------------------------------------------------------------------*
	 * Helper Functions
	 *---------------------------------------------------------------------------------*/

	/**
	 * Retrieves the entire list of categories for this blog.
	 *
	 * @return   array    The array of categories that are defined in this blog.
	 * @since    1.0.0
	 */
	private function get_categories() {

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