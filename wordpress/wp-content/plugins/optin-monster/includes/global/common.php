<?php
/**
 * Common class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Common {

    /**
     * Holds the class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Prevent WPSEO from stripping out needed query args for the Preview frame.
        add_filter( 'wpseo_whitelist_permalink_vars', array( $this, 'whitelist_query_args' ) );

    }

    /**
     * Whitelists preview frame query args so that they aren't stripped in the Preview frame.
     *
     * @since 2.0.0
     *
     * @return array An array of whitelisted query args.
     */
    public function whitelist_query_args( $args ) {

        return array_merge( $args, array( 'om_logged_out', 'om_preview_frame', 'om_preview_optin' ) );

    }

    /**
     * Gets all the available email service provider integrations.
     *
     * @since 2.0.0
     *
     * @param bool $option Whether to return the option or native data.
     * @return array       An array of email service provider integrations.
     */
    public function get_email_providers( $option = false ) {

        if ( $option ) {
            return get_option( 'optin_monster_providers' );
        }

        $providers = array(
            array(
                'name'  => __( 'Select your provider...', 'optin-monster' ),
                'value' => 'none'
            ),
            array(
                'name'  => 'Custom HTML Optin Form',
                'value' => 'custom'
            ),
            array(
                'name'  => 'ActiveCampaign',
                'value' => 'activecampaign'
            ),
            array(
                'name'  => 'AWeber',
                'value' => 'aweber'
            ),
            array(
                'name'  => 'Campaign Monitor',
                'value' => 'campaign-monitor'
            ),
            array(
                'name'  => 'Constant Contact',
                'value' => 'constant-contact'
            ),
            array(
                'name'  => 'Customer.io',
                'value' => 'customerio'
            ),
            array(
                'name'  => 'Emma',
                'value' => 'emma'
            ),
            array(
                'name'  => 'Feedblitz',
                'value' => 'feedblitz'
            ),
            array(
                'name'  => 'GetResponse',
                'value' => 'getresponse'
            ),
            array(
                'name'  => 'HubSpot',
                'value' => 'hubspot'
            ),
            array(
                'name'  => 'iContact',
                'value' => 'icontact'
            ),
            array(
                'name'  => 'Infusionsoft',
                'value' => 'infusionsoft'
            ),
            array(
                'name'  => 'Mad Mimi',
                'value' => 'madmimi'
            ),
            array(
                'name'  => 'MailChimp',
                'value' => 'mailchimp'
            ),
	        array(
		        'name'  => 'MailerLite',
		        'value' => 'mailerlite'
	        ),
	        array(
		        'name'  => 'Marketo',
		        'value' => 'marketo'
	        ),
            array(
                'name'  => 'Pardot',
                'value' => 'pardot'
            ),
            array(
                'name'  => 'SendinBlue',
                'value' => 'sendinblue'
            ),
            array(
                'name'  => 'TotalSend',
                'value' => 'totalsend'
            ),
        );

        // If MailPoet is active, add as a provider.
        if ( class_exists( 'WYSIJA' ) ) {
            $providers[] = array(
                'name'  => 'MailPoet (Wysija)',
                'value' => 'mailpoet'
            );
        }

        return apply_filters( 'optin_monster_providers', $providers );

    }

    /**
     * API method for cropping images.
     *
     * @since 2.0.0
     *
     * @global object $wpdb The $wpdb database object.
     *
     * @param string $url      The URL of the image to resize.
     * @param int $width       The width for cropping the image.
     * @param int $height      The height for cropping the image.
     * @param bool $crop       Whether or not to crop the image (default yes).
     * @param string $align    The crop position alignment.
     * @param bool $retina     Whether or not to make a retina copy of image.
     * @param array $data      Array of optin data (optional).
     * @return WP_Error|string Return WP_Error on error, URL of resized image on success.
     */
    public function resize_image( $url, $width = null, $height = null, $crop = true, $align = 'c', $quality = 100, $retina = false, $data = array() ) {

        global $wpdb;

        // Get common vars.
        $args   = array( $url, $width, $height, $crop, $align, $quality, $retina, $data );
        $common = $this->get_image_info( $args );

        // Unpack variables if an array, otherwise return WP_Error.
        if ( is_wp_error( $common ) ) {
            return $common;
        } else {
            extract( $common );
        }

        // If the destination width/height values are the same as the original, don't do anything.
        if ( $orig_width === $dest_width && $orig_height === $dest_height ) {
            return $url;
        }

        // If the file doesn't exist yet, we need to create it.
        if ( ! file_exists( $dest_file_name ) ) {
            // We only want to resize Media Library images, so we can be sure they get deleted correctly when appropriate.
            $get_attachment = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid='%s'", $url ) );

            // Load the WordPress image editor.
            $editor = wp_get_image_editor( $file_path );

            // If an editor cannot be found, the user needs to have GD or Imagick installed.
            if ( is_wp_error( $editor ) ) {
                return new WP_Error( 'optin-monster-error-no-editor', __( 'No image editor could be selected. Please verify with your webhost that you have either the GD or Imagick image library compiled with your PHP install on your server.', 'optin-monster' ) );
            }

            // Set the image editor quality.
            $editor->set_quality( $quality );

            // If cropping, process cropping.
            if ( $crop ) {
                $src_x = $src_y = 0;
                $src_w = $orig_width;
                $src_h = $orig_height;

                $cmp_x = $orig_width / $dest_width;
                $cmp_y = $orig_height / $dest_height;

                // Calculate x or y coordinate and width or height of source
                if ( $cmp_x > $cmp_y ) {
                    $src_w = round( $orig_width / $cmp_x * $cmp_y );
                    $src_x = round( ($orig_width - ($orig_width / $cmp_x * $cmp_y)) / 2 );
                } else if ( $cmp_y > $cmp_x ) {
                    $src_h = round( $orig_height / $cmp_y * $cmp_x );
                    $src_y = round( ($orig_height - ($orig_height / $cmp_y * $cmp_x)) / 2 );
                }

                // Positional cropping.
                if ( $align && $align != 'c' ) {
                    if ( strpos( $align, 't' ) !== false || strpos( $align, 'tr' ) !== false || strpos( $align, 'tl' ) !== false ) {
                        $src_y = 0;
                    }

                    if ( strpos( $align, 'b' ) !== false || strpos( $align, 'br' ) !== false || strpos( $align, 'bl' ) !== false ) {
                        $src_y = $orig_height - $src_h;
                    }

                    if ( strpos( $align, 'l' ) !== false ) {
                        $src_x = 0;
                    }

                    if ( strpos ( $align, 'r' ) !== false ) {
                        $src_x = $orig_width - $src_w;
                    }
                }

                // Crop the image.
                $editor->crop( $src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height );
            } else {
                // Just resize the image.
                $editor->resize( $dest_width, $dest_height );
            }

            // Save the image.
            $saved = $editor->save( $dest_file_name );

            // Print possible out of memory errors.
            if ( is_wp_error( $saved ) ) {
                @unlink( $dest_file_name );
                return $saved;
            }

            // Add the resized dimensions and alignment to original image metadata, so the images
            // can be deleted when the original image is delete from the Media Library.
            if ( $get_attachment ) {
                $metadata = wp_get_attachment_metadata( $get_attachment[0]->ID );

                if ( isset( $metadata['image_meta'] ) ) {
                    $md = $saved['width'] . 'x' . $saved['height'];

                    if ( $crop ) {
                        $md .= $align ? "_${align}" : "_c";
                    }

                    $metadata['image_meta']['resized_images'][] = $md;
                    wp_update_attachment_metadata( $get_attachment[0]->ID, $metadata );
                }
            }

            // Set the resized image URL.
            $resized_url = str_replace( basename( $url ), basename( $saved['path'] ), $url );
        } else {
            // Set the resized image URL.
            $resized_url = str_replace( basename( $url ), basename( $dest_file_name ), $url );
        }

        // Return the resized image URL.
        return $resized_url;

    }

    /**
     * Helper method to return common information about an image.
     *
     * @since 2.0.0
     *
     * @param array $args      List of resizing args to expand for gathering info.
     * @return WP_Error|string Return WP_Error on error, array of data on success.
     */
    public function get_image_info( $args ) {

        // Unpack arguments.
        list( $url, $width, $height, $crop, $align, $quality, $retina, $data ) = $args;

        // Return an error if no URL is present.
        if ( empty( $url ) ) {
            return new WP_Error( 'optin-monster-error-no-url', __( 'No image URL specified for cropping.', 'optin-monster' ) );
        }

        // Get the image file path.
        $urlinfo       = parse_url( $url );
        $wp_upload_dir = wp_upload_dir();

        // Interpret the file path of the image.
        if ( preg_match( '/\/[0-9]{4}\/[0-9]{2}\/.+$/', $urlinfo['path'], $matches ) ) {
            $file_path = $wp_upload_dir['basedir'] . $matches[0];
        } else {
            $pathinfo    = parse_url( $url );
            $uploads_dir = is_multisite() ? '/files/' : '/wp-content/';
            $file_path   = ABSPATH . str_replace( dirname( $_SERVER['SCRIPT_NAME'] ) . '/', '', strstr( $pathinfo['path'], $uploads_dir ) );
            $file_path   = preg_replace( '/(\/\/)/', '/', $file_path );
        }

        // Attempt to stream and import the image if it does not exist based on URL provided.
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'optin-monster-error-no-file', __( 'No file could be found for the image URL specified.', 'optin-monster' ) );
        }

        // Get original image size.
        $size = @getimagesize( $file_path );

        // If no size data obtained, return an error.
        if ( ! $size ) {
            return new WP_Error( 'optin-monster-error-no-size', __( 'The dimensions of the original image could not be retrieved for cropping.', 'optin-monster' ) );
        }

        // Set original width and height.
        list( $orig_width, $orig_height, $orig_type ) = $size;

        // Generate width or height if not provided.
        if ( $width && ! $height ) {
            $height = floor( $orig_height * ($width / $orig_width) );
        } else if ( $height && ! $width ) {
            $width = floor( $orig_width * ($height / $orig_height) );
        } else if ( ! $width && ! $height ) {
            return new WP_Error( 'optin-monster-error-no-size', __( 'The dimensions of the original image could not be retrieved for cropping.', 'optin-monster' ) );
        }

        // Allow for different retina image sizes.
        $retina = $retina ? ( $retina === true ? 2 : $retina ) : 1;

        // Destination width and height variables
        $dest_width  = $width * $retina;
        $dest_height = $height * $retina;

        // Some additional info about the image.
        $info = pathinfo( $file_path );
        $dir  = $info['dirname'];
        $ext  = $info['extension'];
        $name = wp_basename( $file_path, ".$ext" );

        // Suffix applied to filename
        $suffix = "${dest_width}x${dest_height}";

        // Set alignment information on the file.
        if ( $crop ) {
            $suffix .= ( $align ) ? "_${align}" : "_c";
        }

        // Get the destination file name
        $dest_file_name = "${dir}/${name}-${suffix}.${ext}";

        // Return the info.
        return array(
            'dir'            => $dir,
            'name'           => $name,
            'ext'            => $ext,
            'suffix'         => $suffix,
            'orig_width'     => $orig_width,
            'orig_height'    => $orig_height,
            'orig_type'      => $orig_type,
            'dest_width'     => $dest_width,
            'dest_height'    => $dest_height,
            'file_path'      => $file_path,
            'dest_file_name' => $dest_file_name,
        );

    }

    /**
     * Helper method to flush optin caches once a optin is updated.
     *
     * @since 2.0.0
     *
     * @param int $post_id The current post ID.
     * @param string $slug The unique optin slug.
     */
    public function flush_optin_caches( $post_id, $slug = '' ) {

        // Delete known optin caches.
        delete_transient( '_om_cache_' . $post_id );
        delete_transient( '_om_cache_split_' . $post_id );
        delete_transient( '_om_cache_all' );

        // Possibly delete slug optin cache if available.
        if ( ! empty( $slug ) ) {
            delete_transient( '_om_cache_' . $slug );
        }

        // Grab split tests. If we have any, flush their caches as well.
        $splits = $this->base->get_split_tests( $post_id );
        if ( $splits ) {
	        foreach ( $splits as $split ) {
		        delete_transient( '_om_cache_' . $split->ID );
		        delete_transient( '_om_cache_' . $split->post_name );
	        }
        }

        // Run a hook for Addons to access.
        do_action( 'optin_monster_flush_caches', $post_id, $slug );

    }

    /**
     * Helper method to return the max execution time for scripts.
     *
     * @since 2.0.0
     *
     * @param int $time The max execution time available for PHP scripts.
     */
    public function get_max_execution_time() {

        $time = ini_get( 'max_execution_time' );
        return ! $time || empty( $time ) ? (int) 0 : $time;

    }

    /**
     * Returns the status of an optin.
     *
     * @since 2.0.0
     *
     * @param array $meta     The meta for the optin.
     * @param bool $link      Whether or not to return status string or message.
     * @return string $status The status of the optin.
     */
    public function get_optin_status( $meta, $link = false ) {

        // See if the optin is enabled or not.
        $enabled = isset( $meta['display']['enabled'] ) && $meta['display']['enabled'] ? true : false;
        $status  = '';

        // If enabled, then we need to see if any other setting has been set. If so, it is active, otherwise, put it in staging.
        if ( $enabled ) {
            $live = false;

            // Check if the optin is loaded in the global scope or not.
            if ( isset( $meta['display']['global'] ) && $meta['display']['global'] ) {
                $live = true;
            }

            // Check if the optin is loaded on a particular post or not.
            if ( isset( $meta['display']['exclusive'] ) && ! empty( $meta['display']['exclusive'] ) ) {
                $live = true;
            }

            // Check if the optin is never loaded on a particular post or not.
            if ( isset( $meta['display']['never'] ) && ! empty( $meta['display']['never'] ) ) {
                $live = true;
            }

            // Check if the optin is loaded on particular post categories.
            if ( isset( $meta['display']['categories'] ) && ! empty( $meta['display']['categories'] ) ) {
                $live = true;
            }

            // Check if the optin is loaded on a particular archive type pages or not.
            if ( isset( $meta['display']['show'] ) && ! empty( $meta['display']['show'] ) ) {
                $live = true;
            }

            // Check if the optin is loaded automatically after posts (for the after post optin).
            if ( isset( $meta['display']['automatic'] ) && $meta['display']['automatic'] ) {
                $live = true;
            }

            // If live is true, it is live, otherwise it is in staging.
            if ( $live ) {
                $status = 'live';
            } else {
                $status = isset( $meta['type'] ) && 'sidebar' == $meta['type'] ? 'live' : 'staging';
            }

            // Allow the status to be filtered.
            $status = apply_filters( 'optin_monster_optin_status', $status, $live, $meta );
        } else {
            $status = 'disabled';
        }

        // If we want the status for a link, return it now.
        if ( $link ) {
            return $status;
        }

        // Set message based on status.
        $message = '';
        switch ( $status ) {
            case 'live' :
                $message = '<span class="om-green">' . __( 'Live', 'optin-monster' ) . '</span>';
                break;
            case 'staging' :
                $message = '<span class="om-orange">' . __( 'Staging', 'optin-monster' ) . '</span>';
                break;
            case 'disabled' :
                $message = '<span class="om-red">' . __( 'Disabled', 'optin-monster' ) . '</span>';
        }

        return $message;

    }

    /**
     * Returns the status link of an optin.
     *
     * @since 2.0.0
     *
     * @param array $meta     The meta for the optin.
     * @param int $optin_id   The current optin ID.
     * @return string $status The status link of the optin.
     */
    public function get_optin_status_link( $meta, $optin_id ) {

        $status = $this->get_optin_status( $meta, true );
        $link   = '';
        $split  = get_post_meta( $optin_id, '_om_is_clone', true );
        if ( ! empty( $split ) ) {
	        $split_id = absint( $split );
        }

        switch ( $status ) {
            case 'live' :
            	if ( ! empty( $split ) ) {
	            	$link = '<span class="om-disable"><a href="' . add_query_arg( array( 'om_view' => 'split', 'om_optin_id' => $split_id, 'om_optin_split' => $optin_id, 'om_action' => 'disable' ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . __( 'Disable this optin', 'optin-monster' ) . '">' . __( 'Disable', 'optin-monster' ) . '</a></span>';
            	} else {
	            	$link = '<span class="om-disable"><a href="' . add_query_arg( array( 'om_view' => 'overview', 'om_optin_id' => $optin_id, 'om_action' => 'disable' ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . __( 'Disable this optin', 'optin-monster' ) . '">' . __( 'Disable', 'optin-monster' ) . '</a></span>';
            	}
                break;
            case 'staging' :
                $link = '<span class="om-output-settings"><a href="' . add_query_arg( array( 'om_view' => 'edit', 'om_optin_id' => $optin_id ), admin_url( 'admin.php?page=optin-monster-settings#!optin-monster-panel-output' ) ) . '" title="' . __( 'Manage output settings for this optin', 'optin-monster' ) . '">' . __( 'Output Settings', 'optin-monster' ) . '</a></span>';
                break;
            case 'disabled' :
            	if ( ! empty( $split ) ) {
	            	$link = '<span class="om-enable"><a href="' . add_query_arg( array( 'om_view' => 'split', 'om_optin_id' => $split_id, 'om_optin_split' => $optin_id, 'om_action' => 'live' ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . __( 'Make this optin go live', 'optin-monster' ) . '">' . __( 'Go Live', 'optin-monster' ) . '</a></span>';
            	} else {
	            	$link = '<span class="om-enable"><a href="' . add_query_arg( array( 'om_view' => 'overview', 'om_optin_id' => $optin_id, 'om_action' => 'live' ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . __( 'Make this optin go live', 'optin-monster' ) . '">' . __( 'Go Live', 'optin-monster' ) . '</a></span>';
            	}
                break;
        }

        return $link;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Common ) ) {
            self::$instance = new Optin_Monster_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$optin_monster_common = Optin_Monster_Common::get_instance();