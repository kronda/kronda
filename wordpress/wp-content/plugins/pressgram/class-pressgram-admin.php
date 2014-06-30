<?php
/**
 * Amen
 *
 * @package   Pressgram
 * @author    UaMV
 * @license   GPL-2.0+
 * @link      http://vandercar.net/wp
 * @copyright 2013 UaMV
 */

/**
 * Pressgram_Admin
 *
 * Handles the admin section
 *
 * @package Pressgram
 * @author  UaMV
 */
class Pressgram_Admin {

	/*---------------------------------------------------------------------------------*
	 * Attributes
	 *---------------------------------------------------------------------------------*/

	/**
	 * Instance of this class.
	 *
	 * @since    1.0
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
	 * Pressgram category.
	 *
	 * @since    2.0.0
	 *
	 * @var      string
	 */
	protected $pressgram_current_category;

	/**
	 * Fine control options.
	 *
	 * @since    2.0.0
	 *
	 * @var      array
	 */
	protected $options;

	/**
	 * Category inclusion options.
	 *
	 * @since    2.0.0
	 *
	 * @var      array
	 */
	protected $inclusion;

	/**
	 * Pressgram post relations.
	 *
	 * @since    2.1.0
	 *
	 * @var      array
	 */
	protected $pressgram_post_relation;

	/*---------------------------------------------------------------------------------*
	 * Consturctor / The Singleton Pattern
	 *---------------------------------------------------------------------------------*/

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0
	 */
	private function __construct() {

		global $pagenow;

		// check if plugin has updated and respond accordingly
		add_action( 'admin_init', array( $this, 'check_plugin_update' ) );

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
		

		// Set the category being currently edited
		if ( isset( $_GET['pfc_current_category'] ) && in_array( $_GET['pfc_current_category'], $this->pressgram_categories, TRUE ) ) {
			$this->pressgram_current_category = $_GET['pfc_current_category'];
		} elseif ( isset( $_GET['pfc_current_category'] ) && ! in_array( $_GET['pfc_current_category'], $this->pressgram_categories, TRUE ) ) {
			$this->pressgram_current_category = NULL;
		} elseif ( isset( $_POST['pfc_current_cat'] ) && $_POST['pfc_current_cat'] != '-1' ) {
			$this->pressgram_current_category = $_POST['pfc_current_cat'];
		} elseif ( isset( $_POST['pfc_current_cat'] ) && $_POST['pfc_current_cat'] == '-1' ) {
			$this->pressgram_current_category = NULL;
		} else {
			$this->pressgram_current_category = $this->pressgram_categories[0];
		}
		//$this->pressgram_current_category = isset( $_GET['pressgram_category'] ) ? $_GET['pressgram_category'] : $this->pressgram_categories[0];

		// Set fine control options
		$this->options = get_option( 'pressgram_fine_control_' . $this->pressgram_current_category, array() );
		
		$this->options['post_type'] = isset( $this->options['post_type'] ) ? $this->options['post_type'] : 'post';
		$this->options['post_status'] = isset( $this->options['post_status'] ) ? $this->options['post_status'] : 'publish';
		$this->options['post_format'] = isset( $this->options['post_format'] ) ? $this->options['post_format'] : 'standard';
		$this->options['featured_img'] = isset( $this->options['featured_img'] ) ? $this->options['featured_img'] : FALSE;
		$this->options['comments'] = isset( $this->options['comments'] ) ? $this->options['comments'] : FALSE;
		$this->options['pings'] = isset( $this->options['pings'] ) ? $this->options['pings'] : FALSE;
		$this->options['strip']['image'] = isset( $this->options['strip']['image'] ) ? $this->options['strip']['image'] : FALSE;
		$this->options['gallery'] = isset( $this->options['gallery'] ) ? $this->options['gallery'] : FALSE;
		
		// Set Pressgram post relations
		$this->pressgram_post_relation = get_option( 'pressgram_post_relation', array() );

		// load activation notice to guide users to the next step
		add_action( 'admin_notices', array( $this, 'display_plugin_activation_message' ) );

		// add notices on plugin activation
		//register_activation_hook( PRESSGRAM_DIR_PATH . 'pressgram.php', array( $this, 'add_wpsn_notices' ) );

		// remove active plugin marker
		register_deactivation_hook( PRESSGRAM_DIR_PATH . 'pressgram.php', array( $this, 'remove_activation_marker' ) );

		if ( 'options-media.php' == $pagenow || 'options.php' == $pagenow ) {
			// register the settings
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// call to enqueue admin scripts and styles
			add_action( 'admin_enqueue_scripts', array( $this, 'add_stylesheets_and_javascript' ) );

			// Add jquery to process selected post type on media settings page
			add_action( 'admin_footer', array( $this, 'process_selected_post_type' ) );

		}

		// ajax action callback for counting sessions
		add_action( 'wp_ajax_pressgram_category', array( &$this, 'control_available_categories' ) );  // if logged-in

		if ( 'post.php' == $pagenow ) {

			// Add misc meta field to Publicize metabox on post edit pages
			add_action( 'post_submitbox_misc_actions', array( $this, 'post_page_metabox' ) );

			// Save Pressgram post metadata on post save
			add_action( 'save_post', array( $this, 'save_post_metabox_data' ) );

		}

	} // end constructor

	/*---------------------------------------------------------------------------------*
	 * Public Functions
	 *---------------------------------------------------------------------------------*/

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0
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
	 * Displays a plugin message as soon as the plugin is activated.
	 *
	 * @since    2.1.0
	 */
	public function display_plugin_activation_message() {

		if ( ! get_option( 'pressgram_activated' ) ) {

			// Show the notice
			$html = '<div class="updated">';
				$html .= '<a href="http://pressgr.am"><img src="' . PRESSGRAM_DIR_URL . 'pressgram-logo.png" style="float: left; width: 2em; height: 2em; margin-right: 0.4em; margin-top: 0.4em" /></a>';
				$html .= '<p style="display: inline-block">';
					$html .= __( "<strong>Awesome!</strong> You're almost there - <a href='options-media.php'>click here</a> to select a Pressgram category, set fine control options and manage post relations.", 'pressgram-locale' );
				$html .= '</p>';
			$html .= '</div><!-- /.updated -->';

			echo $html;

			update_option( 'pressgram_activated', TRUE );

		} // end if

	} // end display_plugin_activation_message

	/**
	 * Deletes activation marker so it can be displayed when the plugin is reinstalled or reactivated
	 *
	 * @since    2.1.0
	 */
	public static function remove_activation_marker() {

		delete_option( 'pressgram_activated' );

	} // end remove_activation_marker

	/**
	 * Check for plugin update and updates notices
	 *
	 * @since    1.0.0
	 */
	public function check_plugin_update() {

		// if current version is 2.2.0 and previos is older, then transfer options
		(float) PRESSGRAM_VERSION > (float) get_option( 'pressgram_db_version' ) ? $this->add_wpsn_notices() : FALSE;

		//'2.2.0' == PRESSGRAM_VERSION && (float) PRESSGRAM_VERSION > (float) get_option( 'pressgram_db_version' ) && 0 != (float) get_option( 'pressgram_db_version' ) ? $this->transfer_options() : FALSE;

	} // end check_plugin_update

	/**
	 * Define WP Side Notices for use in plugin
	 *
	 * @since    2.2.0
	 */
	public function add_wpsn_notices() {

		$side_notices = new WP_Side_Notice( 'pressgram' );

		$info_notice = '<a href="http://wordpress.org/plugins/pressgram">Plugin</a> developed by <a href="http://pressgr.am">Pressgram</a> & <a href="http://vandercar.net">UaMV</a>.';

		$support_notice = 'Require assistance? Visit the <a href="http://wordpress.org/support/plugin/pressgram/">support forum</a>. All is well? Consider <a href="http://wordpress.org/support/view/plugin-reviews/pressgram#postform">reviewing the plugin</a> or <a href="http://bit.ly/pressgramapp">the iOS app</a>.';

		$social_notice = __( '<strong>Hello!</strong> Have you connected with others in the Pressgram community?<br />Discover other digital rebels and the ways in which they are using Pressgram to tell their visual story.<br /><br />', 'pressgram-locale' );
		$social_notice .= __( '<a href="http://blog.pressgr.am">Blog</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
		$social_notice .= __( '<a href="http://twitter.com/pressgram">@pressgram</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
		$social_notice .= __( '<a href="https://plus.google.com/105715666878799743742?prsrc=3">Google +</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
		$social_notice .= __( '<a href="http://facebook.com/pressgram">Facebook</a>&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;', 'pressgram-locale' );
		$social_notice .= __( '<a href="http://pressgram.net">Discover</a>', 'pressgram-locale' );

		$pressgram_notices = array(
			'pressgram-info' => array(
				'name' => 'pressgram-info',
				'trigger' => TRUE,
				'time' => time() - 5,
				'dismiss' => 'undismiss',
				'content' => $info_notice,
				'style' => array( 'height' => '72px', 'color' => '#FD9F3A', 'icon' => 'f348' ),
				'location' => array( 'options-media.php' ),
				),
			'pressgram-support' => array(
				'name' => 'pressgram-support',
				'trigger' => TRUE,
				'time' => time() - 5,
				'dismiss' => '',
				'content' => $support_notice,
				'style' => array( 'height' => '72px', 'color' => '#FD9F3A', 'icon' => 'f240' ),
				'location' => array( 'options-media.php' ),
				),
			'pressgram-social' => array(
				'name' => 'pressgram-social',
				'trigger' => TRUE,
				'time' => time() - 5,
				'dismiss' => 'month,forever',
				'content' => $social_notice,
				'style' => array( 'height' => '130px', 'color' => '#FD9F3A', 'icon' => 'f319' ),
				'location' => array( 'options-media.php' ),
				),
			'pressgram-photos' => array(
				'name' => 'pressgram-photos',
				'trigger' => TRUE,
				'time' => time() - 5,
				'dismiss' => 'forever',
				'content' => '',
				'style' => array( 'height' => '145px', 'color' => '#FD9F3A', 'icon' => 'f128' ),
				'location' => array( 'options-media.php' ),
				),
			);

		// remove the old notices
		method_exists('WP_Side_Notice', 'remove' ) ? $side_notices->remove() : FALSE;
		
		// add each notice defined above
		foreach ( $pressgram_notices as $notice => $args ) {
			$side_notices->add( $args );
		}

		update_option( 'pressgram_db_version', PRESSGRAM_VERSION );

	} // end display_notices

	/**
	 * Registers the plugin's administrative stylesheets and JavaScript
	 *
	 * @since    2.1.3
	 */
	public function add_stylesheets_and_javascript() {
		wp_enqueue_style( 'pressgram-admin-style', PRESSGRAM_DIR_URL . 'css/pressgram-admin.css', array(), PRESSGRAM_VERSION, 'screen' );
		
		wp_enqueue_style( 'pressgram-select2', PRESSGRAM_DIR_URL . 'css/lib/select2.css' );

		wp_enqueue_script( 'pressgram-select2', PRESSGRAM_DIR_URL . 'js/lib/select2.min.js' );
		wp_enqueue_script( 'pressgram', PRESSGRAM_DIR_URL . 'js/admin.min.js' , array( 'jquery', 'pressgram-select2' ), PRESSGRAM_VERSION, false );

	} // end add_stylesheets_and_javascript

	/**
	 * Registers the Pressgram Category and Fine Control setting and field with the WordPress Settings API.
	 *
	 * @since    2.1.0
	 */
	public function register_settings() {

		// First, register a settings section
		add_settings_section( 'pressgram', 'Pressgram', array( $this, 'display_section' ), 'media' );

		// Then, register the settings for the Pressgram fields
		register_setting( 'media', 'pressgram_inclusion' );
		! is_null( $this->pressgram_current_category ) ? register_setting( 'media', 'pressgram_fine_control_' . $this->pressgram_current_category ) : FALSE;
		register_setting( 'media', 'pressgram_post_relation' );

		// Now introduce the settings fields
		
		add_settings_field(
			'pressgram_fine_control_' . $this->pressgram_current_category,
			__( 'Fine Control Categories' , 'pressgram-locale' ),
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

		add_settings_field(
			'pressgram_inclusion',
			__( 'Inclusion Control' , 'pressgram-locale' ),
			array( $this, 'display_pressgram_inclusion' ) ,
			'media',
			'pressgram'
		);

	} // end register_pressgram_options

	 /**
	 * Renders the intro to the Pressgram section of the media page.
	 *
	 * @since    2.1.0
	 */
	public function display_section() {

		// Assemble notices for the user
		$notices = new WP_Side_Notice( 'pressgram', 700 );

		// Filter photo vault to include queried images
		add_filter( 'pressgram-photos_side_notice_content', array( $this, 'add_photo_vault' ), 10, 3 );

		// Display the notices
		$notices->display();

		// Assemble the section description
		$html = '<div id="pressgram-section">';
			$html .= '<a href="http://pressgr.am"><img src="' . plugin_dir_url( __FILE__ ) . 'pressgram-logo.png" style="border-radius:100%;"/></a>';
			$html .= 'Add categories, which, when assigned in Pressgram, will ...<br />&nbsp;&nbsp;&nbsp; ';
			$html .= '(1) enable application of the corresponding fine control settings, and<br />&nbsp;&nbsp;&nbsp; ';
			$html .= '(2) mark post relation with Pressgram.';
		$html .= '</div>';

		$html .= '<br /></br />';

		$html .= $this->display_pressgram_category();

		// Echo the section description
		echo $html;
	}

	/**
	 * Queries random pressgram photos to include in photo vault notice.
	 *
	 * @since    2.2.0
	 */
	public function add_photo_vault() {

		$current_user = wp_get_current_user();

		$photos_notice = '';
			
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
				$photos_notice .= '<a href="' . get_permalink( $pressgram_post->ID ) . '" title="' . esc_attr( $pressgram_post->post_title ) . '">' . get_the_post_thumbnail( $pressgram_post->ID, array( 100, 100 ) ) . '</a>';
			} else { // Otherwise
				// Get the first attachment image
				$attachment = get_children( "post_parent=$pressgram_post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );  // get child attachments of type image
					
				// Get post ID of attachment
				$attachment_ID = current( array_keys( $attachment ) );

				$photos_notice .= '<a href="' . get_permalink( $pressgram_post->ID ) . '" title="' . esc_attr( $pressgram_post->post_title ) . '">' . wp_get_attachment_image( $attachment_ID, array( 100, 100 ), FALSE, array( 'class' => 'wp-post-image' ) ) . '</a>';
			}
		}

		return $photos_notice;

	}

	/**
	 * Renders the options for the fine control of Pressgram posts tagged with the Pressgram category
	 * including options for type, status, format, featured image, alignment, link, comments, pings
	 * tags, and content
	 *
	 * @since    2.1.3
	 */
	public function build_pressgram_fine_control( $cat_ID = NULL ) {

		if ( ! is_null( $cat_ID ) ) {
			$html = '<fieldset id="pressgram-fine-control-cat-' . $cat_ID . '-fields">';

			// Build up the list of post types
			$post_types = get_post_types( array( 'public' => TRUE ) );
			$html .= '<label>' . __( 'Post Type:', 'pressgram-locale' );
				$html .= ' <select id="pressgram_fine_control_post_type" name="pressgram_fine_control_' . $this->pressgram_current_category . '[post_type]">';

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
						// remove the post type from array
						unset( $post_types[ $post_type ] );
						}
					}
					if ( ! empty( $post_types ) ) {
						$html .= '<optgroup label="Without Category Support">';
						foreach( $post_types as $post_type ) {
								$post_format_support = post_type_supports( $post_type, 'post-formats' ) ? 'support' : 'no-support';
								$featured_image_support = post_type_supports( $post_type, 'thumbnail' ) ? 'support' : 'no-support';
								$comment_support = post_type_supports( $post_type, 'comments' ) ? 'support' : 'no-support';
								$trackback_support = post_type_supports( $post_type, 'trackbacks' ) ? 'support' : 'no-support';
								$tag_support = in_array( 'post_tag', $supported_taxonomies ) ? 'support' : 'no-support';

								$html .= '<option value="' . $post_type . '" pfsupport="' . $post_format_support . '" pfisupport="' . $featured_image_support . '" pcsupport="' . $comment_support . '" ptsupport="' . $trackback_support . '" ptgsupport="' . $tag_support . '"';
									$html .= $this->options['post_type'] == $post_type ? ' selected="selected"' : '';
								 	$html .= '>';
								 	$html .= 'attachment' == $post_type ? 'Unattached ' : '';
								 	$html .= get_post_type_object( $post_type )->labels->singular_name . '</option>';
						}
						$html .= '</optgroup>';
					}

				$html .= '</select>';
			$html .= '</label>';

			$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

			// Build up the list of post statuses
			$html .= '<label>' . __( 'Post Status:', 'pressgram-locale' );
				$html .= ' <select id="pressgram_fine_control_post_status" name="pressgram_fine_control_' . $this->pressgram_current_category . '[post_status]">';

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

			$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

			// Build up the list of post formats
			$post_formats = get_theme_support( 'post-formats' );
			$html .= '<label>' . __( 'Post Format:', 'pressgram-locale' );
				$html .= ' <select id="pressgram_fine_control_post_format" name="pressgram_fine_control_' . $this->pressgram_current_category . '[post_format]">';

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

			$html .= '<br /><br />';

			// Build the field for featured image support
			$this->options['featured_img'] = isset( $this->options['featured_img'] ) ? $this->options['featured_img'] : FALSE;
			if ( current_theme_supports( 'post-thumbnails' ) && post_type_supports( $this->options['post_type'], 'thumbnail' ) ) {
				$html .= '<label><input type="checkbox" id="pressgram_fine_control_featured_img" name="pressgram_fine_control_' . $this->pressgram_current_category . '[featured_img]" value="1"';
					$html .= $this->options['featured_img'] ? ' checked="checked"></input>' : '></input>';
				$html .= '<span id="featured_img_support">' . __( ' Set first image as featured', 'pressgram-locale' ) . '</span>';
				$html .= '<span id="featured_img_no_support" class="description" style="display:none;">' . __( 'Featured image is not currently supported', 'pressgram-locale' ) . '</span></label>';
			} else {
				$html .= '<label><input type="hidden" id="pressgram_fine_control_featured_img" name="pressgram_fine_control_' . $this->pressgram_current_category . '[featured_img]" value="0" />';
				$html .= '<span id="featured_img_support" style="display:none;">' . __( ' Set first image as featured', 'pressgram-locale' ) . '</span>';
				$html .= '<span id="featured_img_no_support" class="description">' . __( 'Featured image is not currently supported', 'pressgram-locale' ) . '</span></label>';
			}

			$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';
			
			$this->options['strip']['image'] = isset( $this->options['strip']['image'] ) ? $this->options['strip']['image'] : FALSE;
			$html .= '<label><input type="checkbox" id="pressgram_fine_control_strip_image" name="pressgram_fine_control_' . $this->pressgram_current_category . '[strip][image]" value="1"';
				$html .= $this->options['strip']['image'] ? ' checked="checked">' : '>';
				$html .= __( ' Remove first image', 'pressgram-locale' ) . '</input></label>';

			$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';
			
			$this->options['gallery'] = isset( $this->options['gallery'] ) ? $this->options['gallery'] : FALSE;
			$html .= '<label><input type="checkbox" id="pressgram_fine_control_gallery" name="pressgram_fine_control_' . $this->pressgram_current_category . '[gallery]" value="1"';
				$html .= $this->options['gallery'] ? ' checked="checked">' : '>';
				$html .= __( ' Create gallery if multiple images are posted', 'pressgram-locale' ) . '</input></label>';

			$html .= '<br /><br />';

			// Build the field for comment support
			$this->options['comments'] = isset( $this->options['comments'] ) ? $this->options['comments'] : FALSE;
			$html .= '<label><input type="checkbox" id="pressgram_fine_control_comments" name="pressgram_fine_control_' . $this->pressgram_current_category . '[comments]" value="1"';
				$html .= $this->options['comments'] ? ' checked="checked"></input>' : '></input>';
			$html .= '<span> ' . __( 'Allow Comments', 'pressgram-locale' ) . '</span>';
			$html .= '<span id="comments_no_support" class="description"';
				$html .= ( ! post_type_supports( $this->options['post_type'], 'comments' ) || 'attachment' == $this->options['post_type'] ) ? '>' : ' style="display:none;">';
				$html .= __( ' (not supported by current post type)', 'pressgram-locale' ) . '</span></label>';

			$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

			// Build the field for trackback support
			$this->options['pings'] = isset( $this->options['pings'] ) ? $this->options['pings'] : FALSE;
			$html .= '<label><input type="checkbox" id="pressgram_fine_control_pings" name="pressgram_fine_control_' . $this->pressgram_current_category . '[pings]" value="1"';
				$html .= $this->options['pings'] ? ' checked="checked"></input>' : '></input>';
			$html .= '<span> ' . __( 'Allow Trackbacks', 'pressgram-locale' ) . '</span>';
			$html .= '<span id="pings_no_support" class="description"';
				$html .= ( ! post_type_supports( $this->options['post_type'], 'pings' ) || 'attachment' == $this->options['post_type'] ) ? '>' : ' style="display:none;">';
				$html .= __( ' (not supported by current post type)', 'pressgram-locale' ) . '</span></label>';

		} else {
			$html = '';
		}
		return $html;

	} // end display_pressgram_fine_control

		/**
	 * Renders the options for the fine control of Pressgram posts tagged with the Pressgram category
	 * including options for type, status, format, featured image, alignment, link, comments, pings
	 * tags, and content
	 *
	 * @since    2.1.3
	 */
	public function build_fine_control_overview( $cat_ID ) {

		$html = '<table id="pfc-overview" style="display:none;"><tbody>';

		foreach ( $this->pressgram_categories as $cat_ID ) {
			$fine_control = get_option( 'pressgram_fine_control_' . $cat_ID );

			$active = $cat_ID == $this->pressgram_current_category ? ' pfc-active' : '';
			$html .= '<tr class="' . $active . '">';
				$html .= '<td><strong>' . get_category( $cat_ID )->name . '</strong></td>';
				if ( ! is_null( $fine_control ) ) {
					$html .= '<td>' . get_post_type_object( $fine_control['post_type'] )->labels->singular_name . '</td>';
					$html .= '<td>' . ucfirst( $fine_control['post_status'] ) . '</td>';
					$html .= '<td>' . ucfirst( $fine_control['post_format'] ) . '</td>';
					$html .= '<td>' . ( $fine_control['featured_img'] ? 'Featured Image' : '' ) . '</td>';
					$html .= '<td>' . ( $fine_control['strip']['img'] ? 'Remove First Image' : '' ) . '</td>';
					$html .= '<td>' . ( $fine_control['comments'] ? 'Comments' : '' ) . '</td>';
					$html .= '<td>' . ( $fine_control['pings'] ? 'Trackbacks' : '' ) . '</td>';
				}
			$html .= '</tr>';
		}

		$html .= '</tbody></table>';

		return $html;

	}

	/**
	 * Renders the select option for the category and allows users to select what category that want to use
	 * as the Pressgram category.
	 *
	 * @since    1.0.0
	 */
	public function control_available_categories() {

		// get the ajax posted category ID
		$pressgram_cat_ID = $_POST['pressgram_category'];

		// get the ajax posted state (1=add, 0=remove)
		$pressgram_cat_state = $_POST['pressgram_category_state'];

		// if requesting to add category and category has not already been added.
		if ( '1' == $pressgram_cat_state && ! in_array( $pressgram_cat_ID, $this->pressgram_categories, TRUE ) ) {

			// push the category to the fine controlled pressgram categories
			array_push( $this->pressgram_categories, $pressgram_cat_ID );

			// update the fine controlled pressgram categories
			update_option( 'pressgram_categories', $this->pressgram_categories );

			// initialize the fine controls for the added category
			$fine_control = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'post_format'=> 'standard',
				'featured_img'=> FALSE,
				'comments'=> FALSE,
				'pings'=> FALSE,
				'strip' => array(
					'image' => FALSE,
					),
				'show' => array( 
					'home'=> FALSE,
					'feed'=> FALSE,
					// Show option to display on home if post includes non-Pressgram category and a Pressgram category
					'multi_category_homepage'=> FALSE,
					// Show option to display in feed if post includes non-Pressgram category and a Pressgram category
					'multi_category_feed' => FALSE,
					),
				);

			// update the fine control options
			update_option( 'pressgram_fine_control_' . $pressgram_cat_ID, $fine_control );

			// return success to the browser
			echo TRUE;

		} elseif ( '0' == $pressgram_cat_state ) {

			// cycle through fine controlled pressgrm categories and remove if present
			foreach ( $this->pressgram_categories as $key => $category ) {
				if ( $pressgram_cat_ID == $category ) {
					unset( $this->pressgram_categories[ $key ] );
				}
			}
			$this->pressgram_categories = array_values( $this->pressgram_categories );

			// update the fine controlled categories
			update_option( 'pressgram_categories', $this->pressgram_categories );

			// remove from the fine control options for this category
			delete_option( 'pressgram_fine_control_' . $pressgram_cat_ID );
			
			echo TRUE;
		}

		die();

	}

	/**
	 * Renders the select option for the category and allows users to select what category that want to use
	 * as the Pressgram category.
	 *
	 * @since    1.0.0
	 */
	public function display_pressgram_category() {

		// Build up the list of available categories
		$categories = Pressgram::get_categories();
		$html =  '<select id="pressgram_category" name="pressgram_category">';

			$html .= '<option value="default">' . __( 'Select a category...', 'pressgram-locale' ) . '</option>';

			foreach ( $categories as $category ) {
				$html .= '<option value="' . $category->cat_ID . '" data-category-name="' . $category->name . '">' . $category->name . '</option>';
			} // end foreach

		$html .= '</select>';

		$html .= '<div id="add-pressgram-category" data-category-control="1"><div class="dashicons dashicons-plus"></div>Add</div><div id="remove-pressgram-category" data-category-control="0"><div class="dashicons dashicons-minus"></div>Remove</div>';

		$html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;';

		$html .= '<span class="description">' . __( 'Or you can <a href="edit-tags.php?taxonomy=category">create a new category</a>.', 'pressgram-locale' ) . '</span>';

		return $html;

	} // end display_pressgram_category

	/**
	 * Renders the options for the fine control of Pressgram posts tagged with the Pressgram category
	 * including options for type, status, format, featured image, alignment, link, comments, pings
	 * tags, and content
	 *
	 * @since    2.1.3
	 */
	public function display_pressgram_inclusion() {

		$html = '<fieldset id="pressgram-inclusion-fields">';

			// Build the list of strip inclusion
			$html .= 'Include posts (of above enable types) having been assigned to a fine control category ... <br /><br />';
				$this->inclusion['home'] = isset( $this->inclusion['home'] ) ? $this->inclusion['home'] : FALSE;
				$html .= '<label><input type="checkbox" id="pressgram_inclusion_show_home" name="pressgram_inclusion[home]" value="1"';
					$html .= checked( $this->inclusion['home'], 1, FALSE ) . '>';
					$html .= __( ' On home page <span class="description">(always)</span>', 'pressgram-locale' ) . '</input></label><br />';
				$this->inclusion['multi_category_home'] = isset( $this->inclusion['multi_category_home'] ) ? $this->inclusion['multi_category_home'] : FALSE;
				$html .= '<label><input type="checkbox" id="pressgram_inclusion_show_multi_category_home" name="pressgram_inclusion[multi_category_home]" value="1"';
					$html .= checked( $this->inclusion['multi_category_home'], 1, FALSE ) . '>';
					$html .= __( ' On home page <span class="description">(only if other non-fine-control categories exist for the post)</span></label>', 'pressgram-locale' ) . '</input>';

			$html .= '<br /><br />';
				
			$this->inclusion['feed'] = isset( $this->inclusion['feed'] ) ? $this->inclusion['feed'] : FALSE;
				$html .= '<label><input type="checkbox" id="pressgram_inclusion_show_feed" name="pressgram_inclusion[feed]" value="1"';
					$html .= checked( $this->inclusion['feed'], 1, FALSE ) . '>';
					$html .= __( ' In feeds <span class="description">(always)</span>', 'pressgram-locale' ) . '</input></label><br />';
				$this->inclusion['multi_category_feed'] = isset( $this->inclusion['multi_category_feed'] ) ? $this->inclusion['multi_category_feed'] : FALSE;
				$html .= '<label><input type="checkbox" id="pressgram_inclusion_show_multi_category_feed" name="pressgram_inclusion[multi_category_feed]" value="1"';
					$html .= checked( $this->inclusion['multi_category_feed'], 1, FALSE ) . '>';
					$html .= __( ' In feeds <span class="description">(only if other non-fine-control categories exist for the post)</span></label>', 'pressgram-locale' ) . '</input>';

		$html .= '</fieldset>';

		echo $html;

	}

	/**
	 * Renders the options for the fine control of Pressgram posts tagged with the Pressgram category
	 * including options for type, status, format, featured image, alignment, link, comments, pings
	 * tags, and content
	 *
	 * @since    2.1.3
	 */
	public function display_pressgram_fine_control() {

		$html = '<div class="wrap" style="position:relative;width:94%;"><h2 id="pressgram_fine_control_categories" class="nav-tab-wrapper">';

		$html .= '<input type="text" id="pfc-current-cat" name="pfc_current_cat" value="' . $this->pressgram_current_category . '" style="visibility:hidden;position:absolute;"></input>';

		$html .= '<div id="view-pfc-overview" class="dashicons dashicons-list-view"></div>';

		foreach ( $this->pressgram_categories as $pressgram_cat_ID ) {
			$active = $pressgram_cat_ID == $this->pressgram_current_category ? ' nav-tab-active' : '';
			$html .= '<a href="' . admin_url( 'options-media.php?pfc_current_category=' . $pressgram_cat_ID ) . '" id="pressgram-fine-control-' . $pressgram_cat_ID . '" class="nav-tab ' . $active . '"">' . get_category( $pressgram_cat_ID )->name . '</a>';
		}

		$html .= '</h2>';

		$html .= $this->build_pressgram_fine_control( $this->pressgram_current_category );

		$html .= '</div>';

		$html .= $this->build_fine_control_overview( $pressgram_cat_ID );

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
		$html .= '<span class="description">This relation will feature posts in widget AND allow post inclusion via inclusion control below.</span>';
		$html .= '<br />';
		$html .= 'Enable for following post types:';
		$html .= '<br /><br />';

		$post_types = get_post_types( array( 'public' => TRUE ) );

		sort( $post_types );

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

				$("#powertag_generator").click(function() {
					$("#generate_powertags").attr('value','1');
				})

				$pfcPostType = $("#pressgram_fine_control_post_type");
				$pfcPostStatus = $("#pressgram_fine_control_post_status");
				$pfcPostFormat = $("#pressgram_fine_control_post_format");
				$pfcFeaturedImg = $('#pressgram_fine_control_featured_img');
				$pfcImgLink = $('#pressgram_fine_control_link');
				$pfcComments = $("#pressgram_fine_control_comments");
				$pfcPings = $("#pressgram_fine_control_pings");

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

							if ($(this.selected)) {
								$('#pressgram_post_relation_<?php echo $post_type; ?>').attr('checked', 'checked');
							}
						} <?php
					} ?>
				});
			});
		</script>
		<?php
	} // end process_selected_post_type


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
	public function save_post_metabox_data( $post_ID ) {

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

} // end class