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
 * Pressgram_Engine
 *
 * Handles the admin section
 *
 * @package Pressgram
 * @author  UaMV
 */
class Pressgram_Engine {

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
	 * Fine control options.
	 *
	 * @since    2.0.0
	 *
	 * @var      array
	 */
	protected $options;

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
	 * @since     1.0
	 */
	private function __construct() {

		// Initialize Pressgram post array
		$this->pressgram_post = array();

		// Set Pressgram category
		$this->pressgram_categories = get_option( 'pressgram_categories' );

		// Set categories, tags, and check power tags, adjusting options as needed
		add_action( 'transition_post_status', array( $this, 'process_powertags' ), 5, 3 );

		// Apply fine control to new posts categorized in selected Pressgram category
		add_action( 'transition_post_status', array( $this, 'apply_fine_control' ), 15, 3 );

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
	 * Checks whether published posts are newly created and from the Pressgram application
	 *
	 * @since    2.0.5
	 */

	public function initialize_fine_control_options( $cat_ID ) {

		// Set fine control options
		$this->options = get_option( 'pressgram_fine_control_' . $cat_ID , array() );
		$this->options['post_type'] = isset( $this->options['post_type'] ) ? $this->options['post_type'] : 'post';
		$this->options['post_status'] = isset( $this->options['post_status'] ) ? $this->options['post_status'] : 'publish';
		$this->options['post_format'] = isset( $this->options['post_format'] ) ? $this->options['post_format'] : 'standard';
		$this->options['featured_img'] = isset( $this->options['featured_img'] ) ? $this->options['featured_img'] : FALSE;
		$this->options['comments'] = isset( $this->options['comments'] ) ? $this->options['comments'] : FALSE;
		$this->options['pings'] = isset( $this->options['pings'] ) ? $this->options['pings'] : FALSE;
		$this->options['strip']['image'] = isset( $this->options['strip']['image'] ) ? $this->options['strip']['image'] : FALSE;
		$this->options['gallery'] = isset( $this->options['gallery'] ) ? $this->options['gallery'] : FALSE;

	}

	/**
	 * Checks whether published posts are newly created and from the Pressgram application
	 *
	 * @since    2.0.5
	 */

	public function do_pressgram( $new_status, $old_status, $post ) {

		// Check if post is transitioning from new or auto-draft (or from draft, if local defined) to publish
		if ( ( PRESSGRAM_LOCAL && 'draft' == $old_status && 'publish' == $new_status ) || ( ( 'new' == $old_status || 'auto-draft' == $old_status ) && ( 'publish' == $new_status || 'pending' == $new_status ) && ( 'attachment' != $post->post_type ) ) ) {

			// Check if restricted to posts with specific string present
			if ( '' != PRESSGRAM_RESTRICTION && ! strpos( $post->post_content, PRESSGRAM_RESTRICTION ) ) {
				return FALSE;
			} else {

				// Loop through fine controlled categories
				foreach ( $this->pressgram_categories as $cat_ID ) {

					// if post is in a category
					if ( in_category( $cat_ID, $post->ID ) ) {

						// initialize the fine control settings
						is_null( $this->options ) ? $this->initialize_fine_control_options( $cat_ID ) : FALSE;

						return TRUE;
						break;
					}

				}

				// if looped through and found no categories in post
				return FALSE;

			}

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

	public function process_powertags( $new_status, $old_status, $post ) {

		// Check if post is in Pressgram category and if it is transitioning from new or auto-draft to publish
		if ( $this->do_pressgram( $new_status, $old_status, $post ) ) {

			// Get app assigned tags
			$app_tags = wp_get_object_terms( $post->ID, 'post_tag', array( 'fields' => 'all' ) );

			// Begin processing of tags
			$tags = array();
			foreach ( $app_tags as $key => $tag_object ) {

				// Check if tag is power tag
				$power_tag = preg_match( '/\{.*(?=:):/', trim( $tag_object->name ), $match );

				// Apply power tags
				if ( $power_tag ) {

					// Get tag ID or type
					$power_tag_ID = str_replace( array( '{', ':' ), array( '', '' ), $match[0] );

					// Get value by trimming first three characters from power tag
					$power_tag_value = str_replace( array( $match[0], '}' ), array( '', '' ), $tag_object->name );

					switch ( $power_tag_ID ) {

						// Apply post type
						case 'post.type':
							// If fine control post type is attachment, change post status from inherit to publish
							if ( 'attachment' == $this->options['post_type'] && 'attachment' != $power_tag ) {
								$this->options['post_status'] = 'publish';
							}
							$this->options['post_type'] = $power_tag_value;
							break;

						// Apply post status
						case 'post.status':
							$this->options['post_status'] = $power_tag_value;
							break;

						// Apply post format
						case 'post.format':
							$this->options['post_format'] = $power_tag_value;
							break;

						// Apply featured image
						case 'featured.img':
							if ( 'set' == strtolower( $power_tag_value ) ) {
								$this->options['featured_img'] = TRUE;
							} elseif ( 'unset' == strtolower( $power_tag_value ) ) {
								$this->options['featured_img'] = FALSE;
							}
							break;

						// Apply comment status
						case 'comments':
							if ( 'open' == strtolower( $power_tag_value ) ) {
								$this->options['comments'] = TRUE;
							} elseif ( 'closed' == strtolower( $power_tag_value ) ) {
								$this->options['comments'] = FALSE;
							}
							break;
						
						// Apply ping status
						case 'pings':
							if ( 'open' == strtolower( $power_tag_value ) ) {
								$this->options['pings'] = TRUE;
							} elseif ( 'closed' == strtolower( $power_tag_value ) ) {
								$this->options['pings'] = FALSE;
							}
							break;

						// Apply image removal
						case 'remove.img':
							if ( 'set' == strtolower( $power_tag_value ) ) {
								$this->options['strip']['image'] = TRUE;
							} elseif ( 'unset' == strtolower( $power_tag_value ) ) {
								$this->options['strip']['image'] = FALSE;
							}
							break;

						// Apply gallery
						case 'gallery':
							if ( 'set' == strtolower( $power_tag_value ) ) {
								$this->options['gallery'] = TRUE;
							} elseif ( 'unset' == strtolower( $power_tag_value ) ) {
								$this->options['gallery'] = FALSE;
							}
							break;
						
						default:
							break;
					}

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

			// Reset post tags
			wp_set_post_tags( $post->ID, $this->pressgram_post['tags'], FALSE );

			// Get the attachment images
			$attachments = get_children( "post_parent=$post->ID&post_type=attachment&post_mime_type=image" );  // get child attachments of type image
			
			// Get post ID of attachment
			$attachment_ID = current( array_keys( $attachment ) );

			// Strip images
			if ( $this->options['strip']['image'] ) {
				$post->post_content = $this->strip_content( $post->post_content, 1 );
			} elseif ( 'attachment' == $this->options['post_type'] || ( $this->options['gallery'] && count( $attachments ) > 1 ) ) {
				$post->post_content = $this->strip_content( $post->post_content, 2 );
			}

			// Add gallery if more than one attached image
			if ( $this->options['gallery'] && count( $attachments ) > 1 ) {

				// Set max columns to nine
				$columns = 9;

				// Find suitable column number
				do {
					$mod = count( $attachments ) % $columns;
					$columns --;
				} while ( $mod != 0 );

				// Append the gallery shortcode
				$post->post_content .= "\r\n\r\n[gallery link=file columns=" . ( $columns + 1 ) . "]" ;
			}

			// Loop through the attachments
			foreach ( $attachments as $attachment_ID => $attachment ) {
				// Add pressgram_image meta
				add_post_meta( $attachment_ID, '_pressgram_image', TRUE, TRUE );
			}
			
			// Check that fine control post type is not set as attachment
			if ( 'attachment' != $this->options['post_type'] ) {

				// Set the post format (if not standard)
				'standard' != $this->options['post_format'] ? set_post_format( $post->ID, $this->options['post_format'] ) : FALSE;

				// Rekey the attachment array
				$rekeyed_attachments = array_reverse( $attachments );

				// Set featured image (if selected as fine control option)
				$this->options['featured_img'] ? set_post_thumbnail( $post->ID, $rekeyed_attachments[0]->ID ) : FALSE;
				
				// Set post array
				$post = array(
					'ID'             => $post->ID,
					'post_status'    => $this->options['post_status'],
					'post_content'   => $post->post_content,
					'post_type'      => $this->options['post_type'],
					'comment_status' => $this->options['comments'] ? 'open' : 'closed',
					'ping_status'    => $this->options['pings'] ? 'open' : 'closed',
					);

				// Update the post
				wp_update_post( $post );

				// Add pressgram_post meta
				add_post_meta( $post['ID'], '_pressgram_post', TRUE, TRUE );
				
				// If post is not published, remove Jetpack publicized flag
				'publish' != $this->options['post_status'] ? delete_post_meta( $post['ID'], '_wpas_done_all' ) : FALSE;

			} else {
				
				// Loop through the attachments
				foreach ( $attachments as $attachment_ID => $attachment ) {

					// Set the attachment post array
					$attachment_post = array(
						'ID'           => $attachment_ID,
						'post_status'  => 'inherit',
						'post_excerpt' => '',
						'post_content' => $post->post_content,
						'post_parent'  => '',
						);

					// Update the attachment post
					wp_update_post( $attachment_post );

				}

				// Delete the published parent post, if attachment post (skip the trash)
				wp_delete_post( $post->ID, TRUE );

			}
		}
	}

	/**
	 * Strips content
	 *
	 * @since    2.0.5
	 */

	public function strip_content( $content, $arg ) {

		$img_count = preg_match_all( '/<a.[^>]*><img.[^>]*><\/a>/sim', $content, $images );

		if ( 1 == $arg ) {

			// strip the first image
			$content = str_replace( array( $images[0][0], '<p></p>' ), array( '', ''), $content );

		} elseif ( 2 == $arg ) {

			foreach ( $images[0] as $index => $image ) {
				// strip the images
				$content = str_replace( array( $images[0][ $index ], '<p></p>' ), array( '', '' ), $content );
			}

		}

		return $content;

	}


} // end class