<?php
/**
 * Ajax class for the theme.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Handles account ajax actions for the webapp views.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_ajax {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $base, $account;

	public function __construct() {

		// Bring base class into scope.
		global $optin_monster_account;

		// Set class properties.
		$this->base		= optin_monster::get_instance();
		$this->account	= $optin_monster_account;

		// Process an ajax action based on the request.
		add_action( 'wp_ajax_om_verify_license', array( $this, 'verify_license' ) );
		add_action( 'wp_ajax_om_deactivate_license', array( $this, 'deactivate_license' ) );
		add_action( 'wp_ajax_remove_email_auth', array( $this, 'remove_email_auth' ) );
        add_action( 'wp_ajax_load_theme', array( $this, 'load_theme' ) );
        add_action( 'wp_ajax_lightbox_image_upload', array( $this, 'lightbox_image_upload' ) );
		add_action( 'wp_ajax_post_image_upload', array( $this, 'lightbox_image_upload' ) );
		add_action( 'wp_ajax_sidebar_image_upload', array( $this, 'lightbox_image_upload' ) );
        add_action( 'wp_ajax_remove_optin_image', array( $this, 'remove_optin_image' ) );
        add_action( 'wp_ajax_save_optin_config', array( $this, 'save_optin_config' ) );
        add_action( 'wp_ajax_save_optin_design', array( $this, 'save_optin_design' ) );
        add_action( 'wp_ajax_get_new_email_provider', array( $this, 'get_new_email_provider' ) );
        add_action( 'wp_ajax_get_email_provider', array( $this, 'get_email_provider' ) );
        add_action( 'wp_ajax_get_email_provider_segment', array( $this, 'get_email_provider_segment' ) );
        add_action( 'wp_ajax_get_email_provider_data', array( $this, 'get_email_provider_data' ) );
        add_action( 'wp_ajax_get_all_email_accounts', array( $this, 'get_all_email_accounts' ) );
        add_action( 'wp_ajax_connect_email', array( $this, 'connect_email' ) );
        add_action( 'wp_ajax_get_cc_auth_url', array( $this, 'get_cc_auth_url' ) );
        add_action( 'wp_ajax_delete_integration', array( $this, 'delete_integration' ) );
        add_action( 'wp_ajax_save_optin_output', array( $this, 'save_optin_output' ) );
        add_action( 'wp_ajax_nopriv_load_optinmonster', array( $this, 'load_optinmonster' ) );
        add_action( 'wp_ajax_load_optinmonster', array( $this, 'load_optinmonster' ) );
        add_action( 'wp_ajax_nopriv_do_optinmonster', array( $this, 'do_optinmonster' ) );
        add_action( 'wp_ajax_do_optinmonster', array( $this, 'do_optinmonster' ) );
        add_action( 'wp_ajax_nopriv_do_optinmonster_custom', array( $this, 'do_optinmonster_custom' ) );
        add_action( 'wp_ajax_do_optinmonster_custom', array( $this, 'do_optinmonster_custom' ) );
        add_action( 'wp_ajax_optinmonster_activate_addon', array( $this, 'activate_addon' ) );
		add_action( 'wp_ajax_optinmonster_deactivate_addon', array( $this, 'deactivate_addon' ) );
		add_action( 'wp_ajax_optinmonster_install_addon', array( $this, 'install_addon' ) );
		add_action( 'wp_ajax_optinmonster_canvas_shortcode', array( $this, 'canvas_shortcode' ) );
		add_action( 'wp_ajax_nopriv_optinmonster_canvas_shortcode', array( $this, 'canvas_shortcode' ) );

	}

	public function verify_license() {

        $license = stripslashes( $_POST['license'] );

        if ( empty( $license ) ) {
            echo json_encode( array( 'error' => 'Please enter a valid license.' ) );
            die;
        }

        // Perform a remote request to the server.
        $verify_key = $this->perform_remote_request( 'verify-optin-monster-license', array( 'tgm-updater-key' => $license ) );

		/** Return early is there is an error (but output no notices) */
		if ( is_wp_error( $verify_key ) ) {
			echo json_encode( array( 'error' => $verify_key->get_error_message() ) );
			die;
        }

        // Return early if returned false (error connecting to the API).
        if ( ! $verify_key ) {
            echo json_encode( array( 'error' => __( 'There was an error connecting to the OptinMonster API. Please try again.', 'optin-monster' ) ) );
			die;
        }

		/** Return early if there is an error verifying a key (but output no notices) */
		if ( isset( $verify_key->key_error ) ) {
    		echo json_encode( array( 'error' => $verify_key->key_error ) );
			die;
		}

		// If we have reached this point, save the license key.
		$om_license = (array) get_option( 'optin_monster_license' );
		$om_license['key'] = $license;
		update_option( 'optin_monster_license', $om_license );

		echo json_encode( array( 'success' => $verify_key->success ) );
		die;

	}

	public function deactivate_license() {

        $license = stripslashes( $_POST['license'] );

        if ( empty( $license ) ) {
            echo json_encode( array( 'error' => 'Please enter a valid license.' ) );
            die;
        }

        // Perform a remote request to the server.
        $verify_key = $this->perform_remote_request( 'deactivate-optin-monster-license', array( 'tgm-updater-key' => $license ) );

		/** Return early is there is an error (but output no notices) */
		if ( is_wp_error( $verify_key ) ) {
			echo json_encode( array( 'error' => $verify_key->get_error_message() ) );
			die;
        }

		/** Return early if there is an error verifying a key (but output no notices) */
		if ( isset( $verify_key->key_error ) ) {
    		echo json_encode( array( 'error' => $verify_key->key_error ) );
			die;
		}

		// If we have reached this point, save the license key.
		$om_license = (array) get_option( 'optin_monster_license' );
		$om_license['key'] = '';
		update_option( 'optin_monster_license', $om_license );

		echo json_encode( array( 'success' => $verify_key->success ) );
		die;

	}

	public function remove_email_auth() {

    	$provider = stripslashes( $_POST['provider'] );
    	$providers = $this->account->get_email_providers();
    	unset( $providers[$provider] );
    	update_option( 'optin_monster', $providers );
    	echo json_encode( true );
    	die;

	}

	public function generate_postname_hash( $length = 10, $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789' ) {

		$str   = '';
	    $count = strlen( $charset );
	    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	    $alpha_count = strlen( $alpha );

	    while ( $length-- ) {
	        $str .= $charset[mt_rand( 0, $count - 1 )];
	    }

	    return substr_replace( $str, $alpha[mt_rand( 0, $alpha_count - 1 )], 0, 1 );

	}

	public function load_theme() {

    	$type = stripslashes( $_POST['type'] );
    	$theme_type = stripslashes( $_POST['theme'] );
    	$optin = stripslashes( $_POST['optin'] );
    	$optin_id = stripslashes( $_POST['optin_id'] );
    	$plan = stripslashes( $_POST['plan'] );
    	$ssl = ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443;

    	// Save the theme type for helping with image uploads.
    	$meta = get_post_meta( $optin_id, '_om_meta', true );
        $meta['theme'] = $theme_type;
        update_post_meta( $optin_id, '_om_meta', $meta );

    	// Load the lightbox theme builder.
		require_once plugin_dir_path( $this->base->file ) . 'inc/templates/template.php';

    	$html = '<div id="om-' . $optin . '">';
        $theme  = new optin_monster_template( $type, $theme_type, $optin, $optin_id, 'customizer', $ssl );
        $html .= $theme->build_optin();
        $html .= '</div>';

        echo json_encode( $html );
        die;

	}

	public function lightbox_image_upload() {

		if ( ! function_exists( 'wp_handle_upload' ) ) require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) require_once ABSPATH . 'wp-admin/includes/image.php';
		$optin = absint( $_POST['optin'] );
		$upload = wp_handle_upload( $_FILES['async-upload'], array( 'test_form' => false ) );
		if ( $upload ) {
		    // Delete any previous post thumbnails.
		    wp_delete_attachment( get_post_thumbnail_id( $optin ), true );

		    $meta = get_post_meta( $optin, '_om_meta', true );
			$image = wp_get_image_editor( $upload['file'] );

			// If there is an error with getting the image, send back the error.
			if ( is_wp_error( $image ) ) {
			    echo json_encode( array( 'error' => $image->get_error_message() ) );
			    die;
            }

			if ( 'balance-theme' == $meta['theme'] )
			    $image->resize( 225, 175, true );
            else if ( 'case-study-theme' == $meta['theme'] )
                $image->resize( 280, 245, true );
            else if ( 'clean-slate-theme' == $meta['theme'] )
                $image->resize( 230, 195, true );
            else if ( 'bullseye-theme' == $meta['theme'] )
                $image->resize( 700, 350, true );
            else if ( 'transparent-theme' == $meta['theme'] )
                $image->resize( 700, 450, true );
			else if ( 'fabric-theme' == $meta['theme'] )
				$image->resize( 88, 134, true );
			else if ( 'postal-theme' == $meta['theme'] )
				$image->resize( 88, 134, true );
			else if ( 'banner-theme' == $meta['theme'] )
				$image->resize( 88, 134, true );
			$data = $image->save( $upload['file'] );
			$attachment = array(
				'post_type' => 'attachment',
				'post_mime_type' => $data['mime-type'],
				'post_title' => sanitize_title( $data['file'] ),
				'post_status' => 'inherit',
				'post_parent' => $optin
			);
			$attach_id = wp_insert_attachment( $attachment, $data['path'] );
			set_post_thumbnail( $optin, $attach_id );
			$upload['url'] = $upload['url'];
			$upload['id'] = $attach_id;
			echo json_encode( $upload );
		}
		else
			echo json_encode( array( 'error' => $upload ) );
		die;

	}

	public function remove_optin_image() {

		$optin = absint( $_POST['optin'] );
		wp_delete_attachment( get_post_thumbnail_id( $optin ), true );
        echo json_encode( true );
		die;

	}

	public function save_optin_config() {

		$type = stripslashes( $_POST['type'] );
		$optin_name = stripslashes( $_POST['optin'] );
		$meta = array();
		$data = array();
		$id = false;
		wp_parse_str( $_POST['data'], $data );

		switch ( $type ) :
			case 'lightbox' :
			case 'footer' :
			case 'slide' :
			case 'sidebar' :
			case 'post' :
			case 'canvas' :
				if ( ! $optin_name ) :
					// Modify, sanitize and save data.
					$hash 					= $this->generate_postname_hash();
					$optin 					= array();
					$optin['post_title'] 	= isset( $data['optin_campaign_title'] ) ? strip_tags( $data['optin_campaign_title'] ) : $hash;
					$optin['post_type'] 	= 'optin';
					$optin['post_status'] 	= 'publish';
					$optin['post_name'] 	= $hash . '-' . $type;
					$optin_id 				= wp_insert_post( $optin );
				else :
					$optin_name 			= get_posts( array( 'post_type' => 'optin', 'posts_per_page' => 1, 'name' => $optin_name ) );
					$optin_name				= $optin_name[0];
					$optin 					= array();
					$optin['ID']			= $optin_name->ID;
					$optin['post_title'] 	= isset( $data['optin_campaign_title'] ) ? strip_tags( $data['optin_campaign_title'] ) : $optin_name->post_name;
					$optin['post_type'] 	= 'optin';
					$optin['post_status'] 	= 'publish';
					$optin_id 				= wp_update_post( $optin );
				endif;

				// Save the config information as optin meta.
				$id = $optin_id;
				$meta = get_post_meta( $optin_id, '_om_meta', true );
				if ( ! is_array( $meta ) )
					$meta = array();

				// Save the type.
				$meta['type'] = $type;

				if ( empty( $meta['delay'] ) )
					$meta['delay'] = isset( $data['optin_delay'] ) ? absint( $data['optin_delay'] ) : 0;
				else
					$meta['delay'] = isset( $data['optin_delay'] ) ? absint( $data['optin_delay'] ) : $meta['delay'];

				if ( empty( $meta['cookie'] ) )
					$meta['cookie'] = isset( $data['optin_cookie'] ) ? absint( $data['optin_cookie'] ) : 7;
				else
					$meta['cookie'] = isset( $data['optin_cookie'] ) ? absint( $data['optin_cookie'] ) : $meta['cookie'];

				if ( empty( $meta['redirect'] ) )
					$meta['redirect'] = isset( $data['optin_redirect'] ) ? esc_url( $data['optin_redirect'] ) : false;
				else
					$meta['redirect'] = isset( $data['optin_redirect'] ) ? esc_url( $data['optin_redirect'] ) : $meta['redirect'];

				$meta['email']  = empty( $meta['email'] ) ? array() : $meta['email'];

				if ( empty( $meta['email']['provider'] ) )
					$meta['email']['provider'] = isset( $data['optin_email_provider'] ) && 'none' !== $data['optin_email_provider'] ? esc_attr( $data['optin_email_provider'] ) : false;
				else
					$meta['email']['provider'] = isset( $data['optin_email_provider'] ) && 'none' !== $data['optin_email_provider'] ? esc_attr( $data['optin_email_provider'] ) : $meta['email']['provider'];

				// If for some reason the user does not add any email provider information, fail and require them to.
				if ( ! $meta['email']['provider'] && 'canvas' !== $type ) {
					echo json_encode( array( 'error' => __( 'An email provider must be selected before you can continue.', 'optin-monster' ), 'provider' => $data['optin_email_provider'] ) );
					die;
				}

				if ( empty( $meta['email']['account'] ) )
					$meta['email']['account'] = isset( $data['optin_email_account'] ) && 'none' !== $data['optin_email_account'] ? esc_attr( $data['optin_email_account'] ) : false;
				else
					$meta['email']['account'] = isset( $data['optin_email_account'] ) && 'none' !== $data['optin_email_account'] ? esc_attr( $data['optin_email_account'] ) : $meta['email']['account'];

				// If we are using campaign monitor, we have to save the client too.
				if ( empty( $meta['email']['client_id'] ) )
					$meta['email']['client_id'] = isset( $data['client_list'] ) ? $data['client_list'] : false;
				else
					$meta['email']['client_id'] = isset( $data['client_list'] ) ? $data['client_list'] : $meta['email']['client_id'];

				// Load in the list ID and necessary segments if available.
				if ( empty( $meta['email']['list_id'] ) )
					$meta['email']['list_id'] = isset( $data['email_list'] ) ? $data['email_list'] : false;
				else
					$meta['email']['list_id'] = isset( $data['email_list'] ) ? $data['email_list'] : $meta['email']['list_id'];

				// Grab all segments and store in key.
				if ( isset( $data['email_segment_id'] ) ) {
					$meta['email']['segments'] = array();
					foreach ( $data['email_segment_id'] as $group_id ) {
					    if ( 'mailchimp' == $meta['email']['provider'] ) {
					        if ( isset( $data['email_segment_' . $group_id] ) ) {
						        $groups[] = $data['email_segment_' . $group_id];
                                $meta['email']['segments'][$group_id] = implode( ',', array_keys( (array) $data['email_segment_' . $group_id] ) );
                            }
                        } else if ( 'campaign-monitor' == $meta['email']['provider'] ) {
                            if ( isset( $data['email_segment'][$group_id] ) )
                                $meta['email']['segments'][] = $group_id;
                        }
					}
				} else {
					$meta['email']['segments'] = array();
				}

				if ( empty( $meta['allowed'] ) )
					$meta['allowed'] = isset( $data['optin_allowed'] ) ? strip_tags( trim( $data['optin_allowed'] ) ) : false;
				else
					$meta['allowed'] = isset( $data['optin_allowed'] ) ? strip_tags( trim( $data['optin_allowed'] ) ) : $meta['allowed'];

				$meta['double'] = isset( $data['optin_double'] ) ? 1 : 0;
				$meta['second'] = isset( $data['optin_second'] ) ? 1 : 0;
				$meta['logged_in'] = isset( $data['optin_logged_in'] ) ? 1 : 0;
				$meta['powered_by'] = isset( $data['optin_powered_by'] ) ? 1 : 0;

                if ( isset( $data['optin_custom_html'] ) && ! empty( $data['optin_custom_html'] ) ) {
                    // Allow filtering of final output to include necessary things like script tags.
                    $sanitized_html = apply_filters( 'optin_monster_custom_html', $data['optin_custom_html'], ( empty( $optin_name ) ? $hash . '-' . $type : $optin_name->post_name ) );

                    // Save the sanitized HTML string.
                    $meta['custom_html'] = trim( $data['optin_custom_html'] );
                    $meta['custom_html_modified'] = '';
                } else {
                    $meta['custom_html'] = false;
                    $meta['custom_html_modified'] = '';
                }

				// Update the post meta.
				update_post_meta( $optin_id, '_om_meta', $meta );
			break;
		endswitch;

		// Allow addons to modify data.
		do_action( 'optin_monster_save_config', $meta, $data, $id, $type );

		// Send back the hash so we can load in data on the config page.
		if ( empty( $optin_name ) ) {
		    // Delete the transient cache for the optin.
            delete_transient( 'om_optin_' . $hash . '-' . $type );
            delete_transient( 'om_optin_' . $optin_id );
            delete_transient( 'om_optin_meta_' . $hash . '-' . $type );
			echo json_encode( $hash . '-' . $type );
		} else {
		    // Delete the transient cache for the optin.
            delete_transient( 'om_optin_' . $optin_name->post_name );
            delete_transient( 'om_optin_' . $optin_id );
            delete_transient( 'om_optin_meta_' . $optin_name->post_name );
			echo json_encode( $optin_name->post_name );
        }

		die;

	}

	public function save_optin_design() {

		$type = stripslashes( $_POST['type'] );
		$optin = absint( $_POST['optin'] );
		$hash = stripslashes( $_POST['hash'] );
		$theme = stripslashes( $_POST['theme'] );
		$data = array();
        wp_parse_str( $_POST['data'], $data );

		// Save the lightbox design data.
		if ( 'lightbox' == $type ) {
    		require_once plugin_dir_path( $this->base->file ) . 'inc/save/save-' . $type . '-' . $theme . '.php';
    		$class = 'optin_monster_save_' . $type . '_' . str_replace( '-', '_', $theme );
    		$save  = new $class( $type, $theme, $optin, $data );
    		$save->save_optin();
        } else {
            // Provide an action hook to save other optin types.
            do_action( 'optin_monster_save_' . $type, $type, $theme, $optin, $data );
        }

        // Give a base hook to save every optin type.
        do_action( 'optin_monster_save_design', $type, $theme, $optin, $data );

		// Delete the transient cache for the optin.
		delete_transient( 'om_optin_' . $hash );
        delete_transient( 'om_optin_' . $optin );
        delete_transient( 'om_optin_meta_' . $hash );

		// Send back the hash so we can load in data on the config page.
		echo json_encode( $hash );
		die;

	}

	public function get_new_email_provider() {

		$provider = stripslashes( $_POST['email'] );

		echo json_encode( $this->get_new_email_provider_html( $provider ) );
		die;

	}

	public function get_email_provider() {

		$email_id = stripslashes( $_POST['email'] );
		$provider = stripslashes( $_POST['provider'] );
		$providers = (array) $this->account->get_email_providers();
		$type = stripslashes( $_POST['type'] );
		$ret = '';
		global $optin_monster;
		$optin_monster->type = $type;
		$optin_monster->provider_hash = $email_id;

		switch ( $provider ) :
		    case 'sendinblue' :
				if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
					if ( ! class_exists( 'Mailin' ) )
					    require_once plugin_dir_path( $this->base->file ) . 'inc/email/sendinblue/sendinblue.class.php';

					$api = new Mailin( 'https://api.sendinblue.com/v1.0',$providers[$provider][$email_id]['access_key'], $providers[$provider][$email_id]['secret_key'] );
					$lists = $api->get_lists();

                    // Send back necessary HTML for the user to make a selection for the list to subscribe to.
                    $ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
                    $ret .= '<select id="om-email-list" name="email_list">';

                    foreach ( (array) $lists['data'] as $list ) :
                    	$ret .= '<option value="' . $list["id"] . '">' . $list["name"] . '</option>';
                    endforeach;
                    $ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
		    case 'feedblitz' :
				if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
					// Load the Feedblitz API.
					libxml_use_internal_errors( true );
    				$api  = wp_remote_get( 'https://www.feedblitz.com/f.api/syndications?key=' . $providers['feedblitz'][$email_id]['api'] . '&summary=1', array( 'headers' => array( 'Content-Type' => 'application/xml', 'User-Agent' => 'OptinMonster API' ) ) );
    				$body = wp_remote_retrieve_body( $api );
                    $body = str_replace( '&amp;', 'OMamp;', $body );
                    $body = str_replace( '&', '&amp;', $body );
                    $body = str_replace( 'OMamp;', '&amp;', $body );
                    $res  = json_decode( json_encode( simplexml_load_string( $body ) ) );
                    if ( ! empty( $res->rsp->err->{'@attributes'}->msg ) ) {
                        echo json_encode( array( 'error' => $res->rsp->err->{'@attributes'}->msg . '.' ) );
                        die;
                    } else if ( ! $res ) {
                        echo json_encode( array( 'error' => __( 'There was an error connecting to the Feedblitz API. Please try again.', 'optin-monster' ) ) );
                        die;
                    } else {
						// Send back necessary HTML for the user to make a selection for the list to subscribe to.
						$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
						$ret .= '<select id="om-email-list" name="email_list">';
    					foreach ( $res->syndications->syndication as $list ) :
    						$ret .= '<option value="' . $list->id . '">' . $list->name . '</option>';
    					endforeach;
    					$ret .= '</select></div>';
					}
				else :
					$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
			case 'mailchimp' :
				if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
					// Load the MailChimp API.
					if ( ! class_exists( 'MCAPI' ) )
					    require_once plugin_dir_path( $this->base->file ) . 'inc/email/mailchimp/mailchimp.php';

					$api = new MCAPI( $providers['mailchimp'][$email_id]['api'] );
					$retval = $api->lists( array(), 0, 100 );

					if ( $api->errorCode ) :
						$ret .= '<p class="padding-top"><strong>There was an error connecting to the API. ' . $api->errorCode . '</strong></p>';
					else :
						// Send back necessary HTML for the user to make a selection for the list to subscribe to.
						$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
						$ret .= '<select id="om-email-list" name="email_list" data-email-provider="mailchimp">';
						foreach ( (array) $retval['data'] as $list ) :
							$ret .= '<option value="' . $list['id'] . '">' . $list['name'] . '</option>';
						endforeach;
						$ret .= '</select></div>';

						// Possibly list interest groupings (only for the first list).
						$i = 0;
						foreach ( (array) $retval['data'] as $list ) :
							if ( $i >= 1 ) break;
							$data = $api->listInterestGroupings( $list['id'] );
							if ( $data ) :
								$ret .= '<div id="om-email-segments"><p style="padding-top: 15px;"><strong>We also noticed that you have some segments in your list. You can select specific list segments for your optin below.</strong></p>';
								$n = 0;
								foreach ( $data as $group ) :
									if ( $n >= 1 )
										$ret .= '<p style="padding: 15px 0 0;" class="blue" data-group-id="' . $group['id'] . '"><strong>' . $group['name'] . '</strong></p>';
									else
										$ret .= '<span class="blue" data-group-id="' . $group['id'] . '"><strong>' . $group['name'] . '</strong></span><br />';
									$ret .= '<input type="hidden" name="email_segment_id[]" value="' . $group['id'] . '" />';
									foreach ( (array) $group['groups'] as $subgroup ) :
										$ret .= '<input id="' . sanitize_title_with_dashes( strtolower( $subgroup['name'] ) ) . '" type="checkbox" data-subgroup-name="' . $subgroup['name'] . '" data-group-id="' . $group['id'] . '" value="" name="email_segment_' . $group['id'] . '[' . $subgroup['name'] . ']" /> <label style="display: inline;" for="' . sanitize_title_with_dashes( strtolower( $subgroup['name'] ) ) . '">' . $subgroup['name'] . '</label><br />';
									endforeach;
									$n++;
								endforeach;
								$ret .= '</div>';
							endif;
							$i++;
						endforeach;
					endif;
				else :
					$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
			case 'madmimi' :
				if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
					// Load the Madmimi API.
					if ( ! class_exists( 'MadMimi' ) )
					    require_once plugin_dir_path( $this->base->file ) . 'inc/email/madmimi/MadMimi.class.php';

					$api = new MadMimi( $providers['madmimi'][$email_id]['username'], $providers['madmimi'][$email_id]['api'] );

					// If XML is not returned, we need to send an error message.
					libxml_use_internal_errors( true );
					$lists = simplexml_load_string( $api->Lists() );
					if ( ! $lists ) {
						echo json_encode( array( 'error' => 'Unable to authenticate to the Madmimi API. Please try again.' ) );
						die;
					} else {
						// Send back necessary HTML for the user to make a selection for the list to subscribe to.
						$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
						$ret .= '<select id="om-email-list" name="email_list">';
						foreach ( $lists->list as $list ) :
							$ret .= '<option value="' . $list['name'] . '">' . $list['name'] . '</option>';
						endforeach;
						$ret .= '</select></div>';
					}
				else :
					$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
			case 'constant-contact' :
				if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
					$response = wp_remote_get( 'https://api.constantcontact.com/v2/lists?api_key=fbstngt7u3tcvw827w66zyd3&access_token=' . $providers[$provider][$email_id]['token'] );
					$lists = json_decode( wp_remote_retrieve_body( $response ) );

                    // Send back necessary HTML for the user to make a selection for the list to subscribe to.
                    $ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
                    $ret .= '<select id="om-email-list" name="email_list">';
                    foreach ( (array) $lists as $list ) :
                    	$ret .= '<option value="' . $list->id . '">' . $list->name . '</option>';
                    endforeach;
                    $ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
			case 'aweber' :
				if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
					// Load the Aweber API.
					if ( ! class_exists( 'AWeberAPI' ) )
					    require_once plugin_dir_path( $this->base->file ) . 'inc/email/aweber/aweber_api.php';

					$api = new AWeberAPI( $providers[$provider][$email_id]['auth_key'], $providers[$provider][$email_id]['auth_token'] );
					$account = $api->getAccount( $providers[$provider][$email_id]['access_token'], $providers[$provider][$email_id]['access_secret'] );
					// Send back necessary HTML for the user to make a selection for the list to subscribe to.
					$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
					$ret .= '<select id="om-email-list" name="email_list">';
					foreach ( $account->lists as $offset => $list ) :
						$ret .= '<option value="' . $list->id . '">' . $list->name . '</option>';
					endforeach;
					$ret .= '</select></div>';

				else :
					$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
			case 'campaign-monitor' :
				if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
				    if ( ! class_exists( 'CS_Rest_General' ) )
					    require_once plugin_dir_path( $this->base->file ) . 'inc/email/campaign-monitor/csrest_general.php';

					$api = new CS_Rest_General( array( 'api_key' => $providers['campaign-monitor'][$email_id]['api'] ) );
					$retval = $api->get_clients();

					$ret .= '<div id="om-email-clients"><p class="padding-top"><strong>Sweet - we are connected! Select a client from your account to get started.</strong></p>';
					$ret .= '<select id="om-email-client" name="client_list">';
					foreach ( (array) $retval->response as $client ) :
						$ret .= '<option value="' . $client->ClientID . '">' . $client->Name . '</option>';
					endforeach;
					$ret .= '</select></div>';

					// Go ahead and grab lists from the first client found.
					$i = 0;
					foreach ( (array) $retval->response as $client ) :
						if ( $i >= 1 ) break;
						// Load the client API wrapper.
						if ( ! class_exists( 'CS_Rest_Clients' ) )
						    require_once plugin_dir_path( $this->base->file ) . 'inc/email/campaign-monitor/csrest_clients.php';

						$client = new CS_Rest_Clients( $client->ClientID, array( 'api_key' => $providers['campaign-monitor'][$email_id]['api'] ) );
						$lists  = $client->get_lists();

						// Send back necessary HTML for the user to make a selection for the list to subscribe to.
						$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
							$ret .= '<select id="om-email-list" name="email_list">';
							foreach ( (array) $lists->response as $list ) :
								$ret .= '<option value="' . $list->ListID . '">' . $list->Name . '</option>';
							endforeach;
						$ret .= '</select></div>';
						$i++;
					endforeach;
				else :
					$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
            case 'infusionsoft' :
                if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
                    // Load the Infusionsoft API.
    				if ( ! class_exists( 'iSDK' ) )
    				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/infusionsoft/isdk.php';

                    try {
                        $app = new iSDK();
                        $app->cfgCon( $providers['infusionsoft'][$email_id]['app'], $providers['infusionsoft'][$email_id]['api'], 'throw' );
                    } catch( iSDKException $e ){
                        echo json_encode( array( 'error' => sprintf( __( 'Sorry, but Infusionsoft was unable to grant access to your account data. Infusionsoft gave this response: <em>%s</em>. Please try entering your information again.', 'optin-monster' ), $e->getMessage() ) ) );
    					die;
                    }

    				// Retrieve a list of groups/tags from Infusionsoft to assign contacts to.
    				$page    = 0;
    				$all_res = array();
    				while ( true ) {
    				    $res     = $app->dsQuery( 'ContactGroup', 1000, $page, array( 'Id' => '%' ), array( 'Id', 'GroupName' ) );
    				    $all_res = array_merge( $all_res, $res );
    				    if ( count( $res ) < 1000 )
    				        break;

                        $page++;
    				}

    				// Send back necessary HTML for the user to make a selection for the list to subscribe to.
    				$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a tag from the list below. Successful optins will be added to this tag.</strong></p>';
    				$ret .= '<select id="om-email-list" name="email_list">';
    				foreach ( (array) $all_res as $i => $tag ) :
    					$ret .= '<option value="' . $tag['Id'] . '">' . $tag['GroupName'] . '</option>';
    				endforeach;
    				$ret .= '</select></div>';
                else :
                    $ret = $this->get_new_email_provider_html( $provider );
                endif;
				break;
            case 'getresponse' :
                if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
                    // Load the GetResponse API.
                    if ( ! class_exists( 'jsonRPCClient' ) )
                        require_once plugin_dir_path( $this->base->file ) . 'inc/email/getresponse/jsonrpc.php';

                    try {
                        $api = new jsonRPCClient( 'http://api2.getresponse.com' );
                        $campaigns = $api->get_campaigns( $providers['getresponse'][$email_id]['api'] );
                    } catch ( Exception $e ) {
                        echo json_encode( array( 'error' => sprintf( __( 'Sorry, but GetResponse was unable to grant access to your account data. GetResponse gave this response: <em>%s</em>. Please try entering your information again.', 'optin-monster' ), $e->getMessage() ) ) );
    					die;
                    }

                    // Send back necessary HTML for the user to make a selection for the list to subscribe to.
    				$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>'.__('Sweet - we are connected! Select a campaign from the list below. Successful optins will be added to this campaign.','optin-monster').'</strong></p>';
    				$ret .= '<select id="om-email-list" name="email_list">';
    				foreach ( (array) $campaigns as $id => $data ) :
    					$ret .= '<option value="' . $id . '">' . $data['name'] . '</option>';
    				endforeach;
    				$ret .= '</select></div>';
                else :
    				$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
            case 'icontact' :
                if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
                    // Load the iContact API.
                    if ( ! class_exists( 'iContactApi' ) )
                        require_once plugin_dir_path( $this->base->file ) . 'inc/email/icontact/iContactApi.php';

                    try {
                        iContactApi::getInstance()->setConfig(array(
                        	'appId'       => $providers['icontact'][$email_id]['app_id'],
                        	'apiPassword' => $providers['icontact'][$email_id]['app_pass'],
                        	'apiUsername' => $providers['icontact'][$email_id]['username']
                        ));
                        $icontact = iContactApi::getInstance();
                        $lists = $icontact->getLists();
                    } catch ( Exception $e ) {
                        $errors = $icontact->getErrors();
                        echo json_encode( array( 'error' => sprintf( __( 'Sorry, but iContact was unable to grant access to your account data. iContact gave this response: <em>%s</em>. Please try entering your information again.', 'optin-monster' ), $errors[0] ) ) );
    					die;
                    }

                    // Send back necessary HTML for the user to make a selection for the list to subscribe to.
    				$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a campaign from the options below. Successful optins will be added to this campaign.</strong></p>';
    				$ret .= '<select id="om-email-list" name="email_list">';
    				foreach ( $lists as $i => $data ) :
    					$ret .= '<option value="' . $data->listId . '">' . $data->name . '</option>';
    				endforeach;
    				$ret .= '</select></div>';
                else :
                    $ret = $this->get_new_email_provider_html( $provider );
                endif;
                break;
            case 'mailpoet' :
                $modelList = WYSIJA::get( 'list', 'model' );
                $wysijaLists = $modelList->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );

                // Send back necessary HTML for the user to make a selection for the list to subscribe to.
				$ret .= '<div id="om-email-lists"><p class="padding-top">'.__('<strong>Sweet - we are connected! Select an email list from the options below. Successful optins will be added to this list.','optin-monster').'</strong></p>';
				$ret .= '<select id="om-email-list" name="email_list">';
				foreach ( $wysijaLists as $list ) :
					$ret .= '<option value="' . $list['list_id'] . '">' . $list['name'] . '</option>';
				endforeach;
				$ret .= '</select></div>';
                break;
            case 'pardot' :
				if ( array_key_exists( $provider, $providers ) && isset( $providers[$provider][$email_id] ) ) :
                    // Load the Pardot API.
    				if ( ! class_exists( 'Pardot_OM_API' ) )
    				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/pardot/pardot-api-class.php';

                    // Attempt to connect to the Pardot API to retrieve lists.
    				$api = new Pardot_OM_API( array( 'email' => $providers['pardot'][$email_id]['email'], 'password' => $providers['pardot'][$email_id]['password'], 'user_key' => $providers['pardot'][$email_id]['user_key'] ) );
    				$api->authenticate( array( 'email' => $providers['pardot'][$email_id]['email'], 'password' => $providers['pardot'][$email_id]['password'], 'user_key' => $providers['pardot'][$email_id]['user_key'] ) );

    				if ( $api->error ) {
        				echo json_encode( array( 'error' => sprintf( __( 'Sorry, but Pardot was unable to grant access to your account data. Pardot gave this response: <em>%s</em>. Please try entering your information again.', 'optin-monster' ), $api->error ) ) );
    					die;
    				}

    				$lists = $api->get_campaigns();

					// Send back necessary HTML for the user to make a selection for the list to subscribe to.
					$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>'.__('Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-list" name="email_list">';
					foreach ( (array) $lists as $id => $list ) :
						$ret .= '<option value="' . $id . '">' . $list->name . '</option>';
					endforeach;
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $provider );
				endif;
				break;
		endswitch;

		echo json_encode( $ret );
		die;

	}

	public function get_email_provider_segment() {

		$list_id = $_POST['list'];
		$email_id = stripslashes( $_POST['email'] );
		$provider = stripslashes( $_POST['provider'] );
		$client = isset( $_POST['client'] ) ? stripslashes( $_POST['client'] ) : false;
		$providers = (array) $this->account->get_email_providers();
		$ret = '';

		switch ( $provider ) :
			case 'mailchimp' :
				// Load the MailChimp API.
				if ( ! class_exists( 'MCAPI' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/mailchimp/mailchimp.php';

				$api = new MCAPI( $providers['mailchimp'][$email_id]['api'] );
				$retval = $api->lists( array(), 0, 100 );

				if ( $api->errorCode ) :
					$ret .= '<p class="padding-top"><strong>'.__('There was an error connecting to the API. ','optin-monster') . $api->errorCode . '</strong></p>';
				else :
					$data = $api->listInterestGroupings( $list_id );
					if ( $data ) :
						$ret .= '<div id="om-email-segments"><p style="padding-top: 15px;"><strong>'.__('We also noticed that you have some segments in your list. You can select specific list segments for your optin below.','optin-monster').'</strong></p>';
						$n = 0;
						foreach ( $data as $group ) :
							if ( $n >= 1 )
								$ret .= '<p style="padding: 15px 0 0;" class="blue" data-group-id="' . $group['id'] . '"><strong>' . $group['name'] . '</strong></p>';
							else
								$ret .= '<span class="blue" data-group-id="' . $group['id'] . '"><strong>' . $group['name'] . '</strong></span><br />';
							$ret .= '<input type="hidden" name="email_segment_id[]" value="' . $group['id'] . '" />';
							foreach ( (array) $group['groups'] as $subgroup ) :
								$ret .= '<input id="' . sanitize_title_with_dashes( strtolower( $subgroup['name'] ) ) . '" type="checkbox" data-subgroup-name="' . $subgroup['name'] . '" data-group-id="' . $group['id'] . '" value="" name="email_segment_' . $group['id'] . '[' . $subgroup['name'] . ']" /> <label style="display: inline;" for="' . sanitize_title_with_dashes( strtolower( $subgroup['name'] ) ) . '">' . $subgroup['name'] . '</label><br />';
							endforeach;
							$n++;
						endforeach;
						$ret .= '</div>';
					endif;
				endif;
			break;
		endswitch;

		echo json_encode( $ret );
		die;

	}

	public function get_email_provider_data() {

		$provider = stripslashes( $_POST['provider'] );
		$email_id = stripslashes( $_POST['email'] );
		$client = stripslashes( $_POST['client'] );
		$providers = (array) $this->account->get_email_providers();
		$ret = '';

		switch ( $provider ) :
			case 'campaign-monitor' :
				// Load the client API wrapper.
				if ( ! class_exists( 'CS_Rest_Clients' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/campaign-monitor/csrest_clients.php';

				$client = new CS_Rest_Clients( $client, array( 'api_key' => $providers['campaign-monitor'][$email_id]['api'] ) );
				$lists  = $client->get_lists();

				// Send back necessary HTML for the user to make a selection for the list to subscribe to.
				$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>'.__('Select a list from the options below. Successful optins will be subscribed to the selected list.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-list" name="email_list">';
					foreach ( (array) $lists->response as $list ) :
						$ret .= '<option value="' . $list->ListID . '">' . $list->Name . '</option>';
					endforeach;
				$ret .= '</select></div>';
				break;
		endswitch;

		echo json_encode( $ret );
		die;

	}

	public function get_all_email_accounts() {

    	$email = stripslashes( $_POST['email'] );
    	$type = stripslashes( $_POST['type'] );
    	$optin = stripslashes( $_POST['optin'] );
		$ret = '';
		$providers = (array) $this->account->get_email_providers();

		switch ( $email ) :
		    case 'sendinblue' :
				if ( array_key_exists( $email, $providers ) ) :
					// Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select a SendinBlue account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="sendinblue">';
					$ret .= '<option value="none">'.__('Select your SendinBlue account...','optin-monster').'</option>';
					foreach ( (array) $providers['sendinblue'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $email );
				endif;
				break;
		    case 'feedblitz' :
				if ( array_key_exists( $email, $providers ) ) :
					// Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select a Feedblitz account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="feedblitz">';
					$ret .= '<option value="none">'.__('Select your Feedblitz account...','optin-monster').'</option>';
					foreach ( (array) $providers['feedblitz'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $email );
				endif;
				break;
			case 'mailchimp' :
				if ( array_key_exists( $email, $providers ) ) :
					// Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select a Mailchimp account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="mailchimp">';
					$ret .= '<option value="none">'.__('Select your Mailchimp account...','optin-monster').'</option>';
					foreach ( (array) $providers['mailchimp'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $email );
				endif;
				break;
			case 'madmimi' :
				if ( array_key_exists( $email, $providers ) ) :
					// Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select a Madmimi account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="madmimi">';
					$ret .= '<option value="none">'.__('Select your Madmimi account...','optin-monster').'</option>';
					foreach ( (array) $providers['madmimi'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $email );
				endif;
				break;
			case 'constant-contact' :
				if ( array_key_exists( $email, $providers ) ) :
					// Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select a Constant Contact account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="constant-contact">';
					$ret .= '<option value="none">'.__('Select your Constant Contact account...','optin-monster').'</option>';
					foreach ( (array) $providers['constant-contact'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $email );
				endif;
				break;
			case 'aweber' :
				if ( array_key_exists( $email, $providers ) ) :
					// Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select an AWeber account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="aweber">';
					$ret .= '<option value="none">'.__('Select your AWeber account...','optin-monster').'</option>';
					foreach ( (array) $providers['aweber'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $email );
				endif;
				break;
			case 'campaign-monitor' :
				if ( array_key_exists( $email, $providers ) ) :
					// Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select a Campaign Monitor account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="campaign-monitor">';
					$ret .= '<option value="none">'.__('Select your Campaign Monitor account...','optin-monster').'</option>';
					foreach ( (array) $providers['campaign-monitor'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $email );
				endif;
				break;
            case 'infusionsoft' :
                if ( array_key_exists( $email, $providers ) ) :
                    // Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select an Infusionsoft account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="infusionsoft">';
					$ret .= '<option value="none">'.__('Select your Infusionsoft account...','optin-monster').'</option>';
					foreach ( (array) $providers['infusionsoft'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
                else :
                    $ret = $this->get_new_email_provider_html( $email );
                endif;
				break;
            case 'getresponse' :
				if ( array_key_exists( $email, $providers ) ) :
					// Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select a GetResponse account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="getresponse">';
					$ret .= '<option value="none">'.__('Select your GetResponse account...','optin-monster').'</option>';
					foreach ( (array) $providers['getresponse'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
				else :
					$ret = $this->get_new_email_provider_html( $email );
				endif;
				break;
            case 'icontact' :
                if ( array_key_exists( $email, $providers ) ) :
                    // Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select an iContact account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="icontact">';
					$ret .= '<option value="none">'.__('Select your iContact account...','optin-monster').'</option>';
					foreach ( (array) $providers['icontact'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
                else :
                    $ret = $this->get_new_email_provider_html( $email );
    				break;
                endif;
                break;
            case 'mailpoet' :
                // Since MailPoet doesn't require authentication, go ahead and store account details here.
				$providers = get_option( 'optin_monster_providers' );
				if ( empty( $providers['mailpoet'] ) ) {
    				$uniqid = uniqid();
    				$label  = 'Default';
    				$providers['mailpoet'][$uniqid]['label'] = $label;
    				update_option( 'optin_monster_providers', $providers );
                }

				// Send back the response.
                $ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Your MailPoet (Wysija) settings have been populated.','optin-monster').'</strong></p>';
                $ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="mailpoet">';
				$ret .= '<option value="mailpoet">'.__('Default','optin-monster').'</option>';
				$ret .= '</select></div>';
                break;
            case 'pardot' :
                if ( array_key_exists( $email, $providers ) ) :
                    // Send back necessary HTML for the user to make a selection from the available accounts.
					$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>'.__('Select a Pardot account to use for this optin.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-account" name="optin_email_account" data-email-provider="pardot">';
					$ret .= '<option value="none">'.__('Select your Pardot account...','optin-monster').'</option>';
					foreach ( (array) $providers['pardot'] as $hash => $data ) :
						$ret .= '<option value="' . $hash . '">' . $data['label'] . '</option>';
					endforeach;
					$ret .= '<option value="new">'.__('Add a new account...','optin-monster').'</option>';
					$ret .= '</select></div>';
                else :
                    $ret = $this->get_new_email_provider_html( $email );
    				break;
                endif;
                break;
            case 'custom' :
                $value = false;

				// Send back necessary HTML for the user to make a selection for the list to subscribe to.
				$ret .= '<div id="om-email-accounts"><p class="padding-top"><strong>' . __( 'OptinMonster allows you to connect to any email provider that offers an HTML form for subscribing to your list.</strong> Paste the contents of the HTML code output generated by your provider in the field below.', 'optin-monster' ) . '</p>';
				    if ( empty( $optin ) ) {
				        $ret .= '<textarea id="om-custom-html-optin-code" name="optin_custom_html" placeholder="' . __( 'Paste your custom HTML form code here.', 'optin-monster' ) . '"></textarea>';
				    } else {
    				    $opt = get_posts( array( 'name' => $optin, 'post_type' => 'optin', 'posts_per_page' => 1 ) );
                        if ( ! empty( $opt[0]) ) $value = true;
    				    $meta = $value ? get_post_meta( $opt[0]->ID, '_om_meta', true ) : false;
    				    if ( $meta )
                            $ret .= '<textarea id="om-custom-html-optin-code" name="optin_custom_html" placeholder="' . __( 'Paste your custom HTML form code here.', 'optin-monster' ) . '">' . stripslashes( $meta['custom_html'] ) . '</textarea>';
                        else
                            $ret .= '<textarea id="om-custom-html-optin-code" name="optin_custom_html" placeholder="' . __( 'Paste your custom HTML form code here.', 'optin-monster' ) . '"></textarea>';
                    }
				$ret .= '</div>';
				break;
		endswitch;

		echo json_encode( $ret );
		die;

	}

	public function connect_email() {

		$provider = stripslashes( $_POST['type'] );
		$ret = '';
		$data = array();
		parse_str( $_POST['data'], $data );

		switch ( $provider ) :
		    case 'sendinblue' :
				// If no key was entered, return an error.
				if ( empty( $data['access_key'] ) || empty( $data['secret_key'] ) ) {
					echo json_encode( array( 'error' => __('No access key or secret key entered.','optin-monster') ) );
					die;
				}

				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}

				// Load the SendinBlue API.
				if ( ! class_exists( 'Mailin' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/sendinblue/sendinblue.class.php';

				$api = new Mailin('https://api.sendinblue.com/v1.0', $data['access_key'], $data['secret_key'] );

				$lists = $api->get_lists();

				if ( ! $lists ) {
					echo json_encode( array( 'error' => __('Unable to authenticate to the SendinBlue API. Please try again.','optin-monster') ) );
					die;
				} else {
					// Save the users API key for future reference for MailChimp.
					$providers = $this->account->get_email_providers();
					$uniqid = uniqid();
					$label = trim( strip_tags( $data['email_label'] ) );
					$providers['sendinblue'][$uniqid]['access_key'] = trim( $data['access_key'] );
					$providers['sendinblue'][$uniqid]['secret_key'] = trim( $data['secret_key'] );
					$providers['sendinblue'][$uniqid]['label'] = $label;
					update_option( 'optin_monster_providers', $providers );

					// Send back necessary HTML for the user to make a selection for the list to subscribe to.
					$ret .= '<div class="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
					$ret .= '<select id="om-email-list" name="email_list">';
					foreach ( (array) $lists['data'] as $list ) :
						$ret .= '<option value="' . $list["id"] . '">' . $list["name"] . '</option>';
					endforeach;
					$ret .= '</select></div>';

					echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
				}
				break;
		    case 'feedblitz' :
				// If no key was entered, return an error.
				if ( empty( $data['api_key'] ) ) {
					echo json_encode( array( 'error' => __('No username or API key entered.','optin-monster') ) );
					die;
				}

				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}

				// Load the Feedblitz API.
				libxml_use_internal_errors( true );
				$api  = wp_remote_get( 'https://www.feedblitz.com/f.api/syndications?key=' . $data['api_key'] . '&summary=1', array( 'headers' => array( 'Content-Type' => 'application/xml', 'User-Agent' => 'OptinMonster API' ) ) );
				$body = wp_remote_retrieve_body( $api );
                $body = str_replace( '&amp;', 'OMamp;', $body );
                $body = str_replace( '&', '&amp;', $body );
                $body = str_replace( 'OMamp;', '&amp;', $body );
                $res  = json_decode( json_encode( simplexml_load_string( $body ) ) );
				if ( ! empty( $res->rsp->err->{'@attributes'}->msg ) ) {
                    echo json_encode( array( 'error' => $res->rsp->err->{'@attributes'}->msg . '.' ) );
                    die;
                } else if ( ! $res ) {
                    echo json_encode( array( 'error' => __( 'There was an error connecting to the Feedblitz API. Please try again.', 'optin-monster' ) ) );
                    die;
                } else {
					// Save the users API key for future reference for MailChimp.
					$providers = $this->account->get_email_providers();
					$uniqid = uniqid();
					$label = trim( strip_tags( $data['email_label'] ) );
					$providers['feedblitz'][$uniqid]['api'] = trim( $data['api_key'] );
					$providers['feedblitz'][$uniqid]['label'] = $label;
					update_option( 'optin_monster_providers', $providers );

					// Send back necessary HTML for the user to make a selection for the list to subscribe to.
					$ret .= '<div class="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
					$ret .= '<select id="om-email-list" name="email_list">';
					foreach ( $res->syndications->syndication as $list ) :
						$ret .= '<option value="' . $list->id . '">' . $list->name . '</option>';
					endforeach;
					$ret .= '</select></div>';

					echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
				}
				break;
			case 'mailchimp' :
				// If no key was entered, return an error.
				if ( empty( $data['api_key'] ) ) {
					echo json_encode( array( 'error' => __('No API key entered.','optin-monster') ) );
					die;
				}

				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}
				// Load the MailChimp API.
				if ( ! class_exists( 'MCAPI' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/mailchimp/mailchimp.php';

				$api = new MCAPI( $data['api_key'] );
				$retval = $api->lists( array(), 0, 100 );
				if ( $api->errorCode ) {
					echo json_encode( array( 'error' => $api->errorMessage ) );
					die;
				} else {
					// Save the users API key for future reference for MailChimp.
					$providers = $this->account->get_email_providers();
					$uniqid = uniqid();
					$label = trim( strip_tags( $data['email_label'] ) );
					$providers['mailchimp'][$uniqid]['api'] = trim( $data['api_key'] );
					$providers['mailchimp'][$uniqid]['label'] = $label;
					update_option( 'optin_monster_providers', $providers );

					// Send back necessary HTML for the user to make a selection for the list to subscribe to.
					$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>'.__('Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-list" name="email_list">';
					foreach ( (array) $retval['data'] as $list ) :
						$ret .= '<option value="' . $list['id'] . '">' . $list['name'] . '</option>';
					endforeach;
					$ret .= '</select></div>';

					// Possibly list interest groupings (only for the first list).
					$i = 0;
					foreach ( (array) $retval['data'] as $list ) :
						if ( $i >= 1 ) break;
						$data = $api->listInterestGroupings( $list['id'] );
						if ( $data ) :
							$ret .= '<div id="om-email-segments"><p style="padding-top: 15px;"><strong>'.__('We also noticed that you have some segments in your list. You can select specific list segments for your optin below.','optin-monster').'</strong></p>';
							$n = 0;
							foreach ( $data as $group ) :
								if ( $n >= 1 )
									$ret .= '<p style="padding: 15px 0 0;" class="blue" data-group-id="' . $group['id'] . '"><strong>' . $group['name'] . '</strong></p>';
								else
									$ret .= '<span class="blue" data-group-id="' . $group['id'] . '"><strong>' . $group['name'] . '</strong></span><br />';
								$ret .= '<input type="hidden" name="email_segment_id[]" value="' . $group['id'] . '" />';
								foreach ( (array) $group['groups'] as $subgroup ) :
									$ret .= '<input id="' . sanitize_title_with_dashes( strtolower( $subgroup['name'] ) ) . '" type="checkbox" data-subgroup-name="' . $subgroup['name'] . '" data-group-id="' . $group['id'] . '" value="" name="email_segment_' . $group['id'] . '[' . $subgroup['name'] . ']" /> <label style="display: inline;" for="' . sanitize_title_with_dashes( strtolower( $subgroup['name'] ) ) . '">' . $subgroup['name'] . '</label><br />';
								endforeach;
								$n++;
							endforeach;
							$ret .= '</div>';
						endif;
						$i++;
					endforeach;
					echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
					die;
				}
				break;
			case 'madmimi' :
				// If no key was entered, return an error.
				if ( empty( $data['api_key'] ) || empty( $data['username'] ) ) {
					echo json_encode( array( 'error' => __('No username or API key entered.','optin-monster') ) );
					die;
				}

				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}

				// Load the Madmimi API.
				if ( ! class_exists( 'MadMimi' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/madmimi/MadMimi.class.php';

				$api = new MadMimi( $data['username'], $data['api_key'] );

				// If XML is not returned, we need to send an error message.
				libxml_use_internal_errors( true );
				$lists = simplexml_load_string( $api->Lists() );
				if ( ! $lists ) {
					echo json_encode( array( 'error' => __('Unable to authenticate to the Madmimi API. Please try again.','optin-monster') ) );
					die;
				} else {
					// Save the users API key for future reference for MailChimp.
					$providers = $this->account->get_email_providers();
					$uniqid = uniqid();
					$label = trim( strip_tags( $data['email_label'] ) );
					$providers['madmimi'][$uniqid]['api'] = trim( $data['api_key'] );
					$providers['madmimi'][$uniqid]['username'] = trim( $data['username'] );
					$providers['madmimi'][$uniqid]['label'] = $label;
					update_option( 'optin_monster_providers', $providers );

					// Send back necessary HTML for the user to make a selection for the list to subscribe to.
					$ret .= '<div class="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
					$ret .= '<select id="om-email-list" name="email_list">';
					foreach ( $lists->list as $list ) :
						$ret .= '<option value="' . $list['name'] . '">' . $list['name'] . '</option>';
					endforeach;
					$ret .= '</select></div>';

					echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
				}
				break;
			case 'campaign-monitor' :
				// If no key was entered, return an error.
				if ( empty( $data['api_key'] ) ) {
					echo json_encode( array( 'error' => __('No API key entered.','optin-monster') ) );
					die;
				}

				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}
				// Load the Campaign Monitor API.
				if ( ! class_exists( 'CS_Rest_General' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/campaign-monitor/csrest_general.php';

				$api = new CS_Rest_General( array( 'api_key' => $data['api_key'] ) );
				$retval = $api->get_clients();

				if ( ! $retval->was_successful() ) {
					echo json_encode( array( 'error' => $retval->response->Message . '.' ) );
					die;
				} else {
					// Save the users API key for future reference for Campaign Monitor.
					$providers = $this->account->get_email_providers();
					$uniqid = uniqid();
					$label = trim( strip_tags( $data['email_label'] ) );
					$providers['campaign-monitor'][$uniqid]['api'] = $data['api_key'];
					$providers['campaign-monitor'][$uniqid]['label'] = $label;
					update_option( 'optin_monster_providers', $providers );

					$ret .= '<div id="om-email-clients"><p class="padding-top"><strong>'.__('Sweet - we are connected! Select a client from your account to get started.','optin-monster').'</strong></p>';
					$ret .= '<select id="om-email-client" name="client_list">';
					foreach ( (array) $retval->response as $client ) :
						$ret .= '<option value="' . $client->ClientID . '">' . $client->Name . '</option>';
					endforeach;
					$ret .= '</select></div>';

					// Go ahead and grab lists from the first client found.
					$i = 0;
					foreach ( (array) $retval->response as $client ) :
						if ( $i >= 1 ) break;
						// Load the client API wrapper.
						if ( ! class_exists( 'CS_Rest_Clients' ) )
						    require_once plugin_dir_path( $this->base->file ) . 'inc/email/campaign-monitor/csrest_clients.php';

						$client = new CS_Rest_Clients( $client->ClientID, array( 'api_key' => $providers['campaign-monitor'][$uniqid]['api'] ) );
						$lists  = $client->get_lists();

						// Send back necessary HTML for the user to make a selection for the list to subscribe to.
						$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>'.__('Select a list from the options below. Successful optins will be subscribed to the selected list.','optin-monster').'</strong></p>';
							$ret .= '<select id="om-email-list" name="email_list">';
							foreach ( (array) $lists->response as $list ) :
								$ret .= '<option value="' . $list->ListID . '">' . $list->Name . '</option>';
							endforeach;
						$ret .= '</select></div>';
						$i++;
					endforeach;
					echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
					die;
				}
				break;
            case 'aweber' :
				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}

				// If no key was entered, return an error.
				if ( empty( $data['aweber_auth_code'] ) ) {
					echo json_encode( array( 'error' => __('No AWeber authorization code was entered.','optin-monster') ) );
					die;
				}

				// Load the AWeber API.
				if ( ! class_exists( 'AWeberAPI' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/aweber/aweber_api.php';

				$auth_tokens = strip_tags( stripslashes( $data['aweber_auth_code'] ) );
				list( $auth_key, $auth_token, $req_key, $req_token, $oauth ) = explode( '|', $auth_tokens );
				$aweber = new AWeberAPI( $auth_key, $auth_token );
				$aweber->user->requestToken = $req_key;
				$aweber->user->tokenSecret = $req_token;
				$aweber->user->verifier = $oauth;

				// Attempt to grab an authorization token or produce an error.
				try {
					list( $access_token, $access_token_secret ) = $aweber->getAccessToken();
				} catch ( AWeberException $e ) {
					echo json_encode( array( 'error' => sprintf( __( 'Sorry, but AWeber was unable to verify your authorization token. AWeber gave this response: <em>%s</em>. Please try entering your authorization token again.', 'optin-monster' ), $e->getMessage() ) ) );
					die;
				}

				// Now try to access the account. If this fails, we need more permissions.
				try {
					$account = $aweber->getAccount();
				} catch ( AWeberException $e ) {
					echo json_encode( array( 'error' => sprintf( __( 'Sorry, but AWeber was unable to grant access to your account data. AWeber gave this response: <em>%s</em>. Please try entering your authorization token again.', 'optin-monster' ), $e->getMessage() ) ) );
					die;
				}

				// Success! Now we can store the AWeber auth data.
				$providers = $this->account->get_email_providers();
				$uniqid = uniqid();
				$label = trim( strip_tags( $data['email_label'] ) );
				$providers['aweber'][$uniqid]['auth_key']      = $auth_key;
				$providers['aweber'][$uniqid]['auth_token']    = $auth_token;
				$providers['aweber'][$uniqid]['access_token']  = $access_token;
				$providers['aweber'][$uniqid]['access_secret'] = $access_token_secret;
				$providers['aweber'][$uniqid]['label']         = $label;
				update_option( 'optin_monster_providers', $providers );

				// Send back necessary HTML for the user to make a selection for the list to subscribe to.
				$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.</strong></p>';
				$ret .= '<select id="om-email-list" name="email_list">';
				foreach ( $account->lists as $offset => $list ) :
					$ret .= '<option value="' . $list->id . '">' . $list->name . '</option>';
				endforeach;
				$ret .= '</select></div>';

				echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
                break;
            case 'infusionsoft' :
				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}

				// If no key was entered, return an error.
				if ( empty( $data['api_key'] ) ) {
					echo json_encode( array( 'error' => __('No API key was entered.','optin-monster' ) ) );
					die;
				}

				// If no application name was entered, return an error.
				if ( empty( $data['app_name'] ) ) {
					echo json_encode( array( 'error' => __('No application name was entered.','optin-monster' ) ) );
					die;
				}

				// Load the Infusionsoft API.
				if ( ! class_exists( 'iSDK' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/infusionsoft/isdk.php';

                try {
                    $app = new iSDK();
                    $app->cfgCon( $data['app_name'], $data['api_key'], 'throw' );
                } catch( iSDKException $e ){
                    echo json_encode( array( 'error' => sprintf( __( 'Sorry, but Infusionsoft was unable to grant access to your account data. Infusionsoft gave this response: <em>%s</em>. Please try entering your information again.', 'optin-monster' ), $e->getMessage() ) ) );
					die;
                }

                // Success! Now we can store the AWeber auth data.
				$providers = $this->account->get_email_providers();
				$uniqid = uniqid();
				$label = trim( strip_tags( $data['email_label'] ) );
				$providers['infusionsoft'][$uniqid]['app']   = $data['app_name'];
				$providers['infusionsoft'][$uniqid]['api']   = $data['api_key'];
				$providers['infusionsoft'][$uniqid]['label'] = $label;
				update_option( 'optin_monster_providers', $providers );

				// Retrieve a list of groups/tags from Infusionsoft to assign contacts to.
				$page    = 0;
				$all_res = array();
				while ( true ) {
				    $res     = $app->dsQuery( 'ContactGroup', 1000, $page, array( 'Id' => '%' ), array( 'Id', 'GroupName' ) );
				    $all_res = array_merge( $all_res, $res );
				    if ( count( $res ) < 1000 )
				        break;

                    $page++;
				}

				// Send back necessary HTML for the user to make a selection for the list to subscribe to.
				$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>'.__('Sweet - we are connected! Select a tag from the list below. Successful optins will be added to this tag.','optin-monster').'</strong></p>';
				$ret .= '<select id="om-email-list" name="email_list">';
				foreach ( (array) $all_res as $i => $tag ) :
					$ret .= '<option value="' . $tag['Id'] . '">' . $tag['GroupName'] . '</option>';
				endforeach;
				$ret .= '</select></div>';

				echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
                break;
            case 'getresponse' :
                // If no key was entered, return an error.
				if ( empty( $data['api_key'] ) ) {
					echo json_encode( array( 'error' => __('No API key entered.','optin-monster') ) );
					die;
				}

				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}

				// Load the GetResponse API.
                if ( ! class_exists( 'jsonRPCClient' ) )
                    require_once plugin_dir_path( $this->base->file ) . 'inc/email/getresponse/jsonrpc.php';

                try {
                    $api = new jsonRPCClient( 'http://api2.getresponse.com' );
                    $campaigns = $api->get_campaigns( $data['api_key'] );
                } catch ( Exception $e ) {
                    echo json_encode( array( 'error' => sprintf( __( 'Sorry, but GetResponse was unable to grant access to your account data. GetResponse gave this response: <em>%s</em>. Please try entering your information again.', 'optin-monster' ), $e->getMessage() ) ) );
					die;
                }

                // Success! Now we can store the data.
				$providers = $this->account->get_email_providers();
				$uniqid = uniqid();
				$label = trim( strip_tags( $data['email_label'] ) );
				$providers['getresponse'][$uniqid]['api']   = $data['api_key'];
				$providers['getresponse'][$uniqid]['label'] = $label;
				update_option( 'optin_monster_providers', $providers );

                // Send back necessary HTML for the user to make a selection for the list to subscribe to.
				$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>'.__('Sweet - we are connected! Select a campaign from the list below. Successful optins will be added to this campaign.','optin-monster').'</strong></p>';
				$ret .= '<select id="om-email-list" name="email_list">';
				foreach ( (array) $campaigns as $id => $data ) :
					$ret .= '<option value="' . $id . '">' . $data['name'] . '</option>';
				endforeach;
				$ret .= '</select></div>';

				echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
                break;
            case 'icontact' :
                // If no key was entered, return an error.
				if ( empty( $data['username'] ) ) {
					echo json_encode( array( 'error' => __('No iContact username entered.','optin-monster') ) );
					die;
				}

				// If no key was entered, return an error.
				if ( empty( $data['app_id'] ) ) {
					echo json_encode( array( 'error' => __('No app ID entered.','optin-monster') ) );
					die;
				}

				// If no key was entered, return an error.
				if ( empty( $data['app_pass'] ) ) {
					echo json_encode( array( 'error' => __('No app password entered.','optin-monster') ) );
					die;
				}

				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}

                // Load the iContact API.
                if ( ! class_exists( 'iContactApi' ) )
                    require_once plugin_dir_path( $this->base->file ) . 'inc/email/icontact/iContactApi.php';

                try {
                    iContactApi::getInstance()->setConfig(array(
                    	'appId'       => $data['app_id'],
                    	'apiPassword' => $data['app_pass'],
                    	'apiUsername' => $data['username']
                    ));
                    $icontact = iContactApi::getInstance();
                    $lists = $icontact->getLists();
                } catch ( Exception $e ) {
                    $errors = $icontact->getErrors();
                    echo json_encode( array( 'error' => sprintf( __( 'Sorry, but iContact was unable to grant access to your account data. iContact gave this response: <em>%s</em>. Please try entering your information again.', 'optin-monster' ), $errors[0] ) ) );
					die;
                }

                // Success! Now we can store the data.
				$providers = $this->account->get_email_providers();
				$uniqid = uniqid();
				$label = trim( strip_tags( $data['email_label'] ) );
				$providers['icontact'][$uniqid]['username'] = $data['username'];
				$providers['icontact'][$uniqid]['app_id']   = $data['app_id'];
				$providers['icontact'][$uniqid]['app_pass'] = $data['app_pass'];
				$providers['icontact'][$uniqid]['label']    = $label;
				update_option( 'optin_monster_providers', $providers );

                // Send back necessary HTML for the user to make a selection for the list to subscribe to.
				$ret .= '<div id="om-email-lists"><p class="padding-top"><strong>'.__('Sweet - we are connected! Select a campaign from the options below. Successful optins will be added to this campaign.','optin-monster').'</strong></p>';
				$ret .= '<select id="om-email-list" name="email_list">';
				foreach ( $lists as $i => $data ) :
					$ret .= '<option value="' . $data->listId . '">' . $data->name . '</option>';
				endforeach;
				$ret .= '</select></div>';

				echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
                break;
            case 'pardot' :
				// If no key was entered, return an error.
				if ( empty( $data['email'] ) || empty( $data['password'] ) || empty( $data['user_key'] ) ) {
					echo json_encode( array( 'error' => __('No email address, password or user key was entered.','optin-monster') ) );
					die;
				}

				// If no label was entered, return an error.
				if ( empty( $data['email_label'] ) ) {
					echo json_encode( array( 'error' => __('No account label was entered.','optin-monster') ) );
					die;
				}

				// Load the Pardot API.
				if ( ! class_exists( 'Pardot_OM_API' ) )
				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/pardot/pardot-api-class.php';

                // Attempt to connect to the Pardot API to retrieve lists.
				$api = new Pardot_OM_API( array( 'email' => $data['email'], 'password' => $data['password'], 'user_key' => $data['user_key'] ) );
				$api->authenticate( array( 'email' => $data['email'], 'password' => $data['password'], 'user_key' => $data['user_key'] ) );

				// If there is an error, output and return early.
				if ( $api->error ) {
    				echo json_encode( array( 'error' => $api->error ) );
					die;
				}

				$lists = $api->get_campaigns();

				// Save the users API key for future reference for MailChimp.
				$providers = $this->account->get_email_providers();
				$uniqid = uniqid();
				$label = trim( strip_tags( $data['email_label'] ) );
				$providers['pardot'][$uniqid]['email'] = trim( $data['email'] );
				$providers['pardot'][$uniqid]['password'] = trim( $data['password'] );
				$providers['pardot'][$uniqid]['user_key'] = trim( $data['user_key'] );
				$providers['pardot'][$uniqid]['label'] = $label;
				update_option( 'optin_monster_providers', $providers );

				// Send back necessary HTML for the user to make a selection for the list to subscribe to.
				$ret .= '<div class="om-email-lists"><p class="padding-top"><strong>'.__('Sweet - we are connected! Select a list from the options below. Successful optins will be subscribed to the selected list.','optin-monster').'</strong></p>';
				$ret .= '<select id="om-email-list" name="email_list">';
				foreach ( $lists as $id => $list ) :
					$ret .= '<option value="' . $id . '">' . $list->name . '</option>';
				endforeach;
				$ret .= '</select></div>';

				echo json_encode( array( 'success' => $ret, 'email_id' => $uniqid, 'email_label' => $label ) );
				break;
		endswitch;

		die;

	}

	public function get_cc_auth_url() {

    	$type = stripslashes( $_POST['type'] );
    	$label = stripslashes( $_POST['label'] );
    	$optin = $_POST['optin'];

    	// Build out the URL.
    	if ( '' !== trim( $optin ) )
    	    $auth_url = 'https://oauth2.constantcontact.com/oauth2/oauth/siteowner/authorize?response_type=token&client_id=fbstngt7u3tcvw827w66zyd3&redirect_uri=http://optinmonster.com/cc-verify-oauth/?cc_return_uri=' . admin_url( 'admin.php' ) . 'omquestionpage=optin-monsteromampersandtab=optinsomampersandedit=' . $optin . 'omampersandconfig=trueomampersandtype=' . $type . 'omampersandlabel=' . urlencode( $label ) . 'omampersandcc_auth=true';
        else
            $auth_url = 'https://oauth2.constantcontact.com/oauth2/oauth/siteowner/authorize?response_type=token&client_id=fbstngt7u3tcvw827w66zyd3&redirect_uri=http://optinmonster.com/cc-verify-oauth/?cc_return_uri=' . admin_url( 'admin.php' ) . 'omquestionpage=optin-monsteromampersandtab=optinsomampersandconfig=trueomampersandtype=' . $type . 'omampersandlabel=' . urlencode( $label ) . 'omampersandcc_auth=true';

    	echo json_encode( $auth_url );
    	die;

	}

	public function delete_integration() {

    	$hash = stripslashes( $_POST['hash'] );
    	$provider = stripslashes( $_POST['provider'] );
    	$providers = get_option( 'optin_monster_providers' );

    	if ( isset( $providers[$provider][$hash] ) )
    	    unset( $providers[$provider][$hash] );

        update_option( 'optin_monster_providers', $providers );
        echo json_encode( true );
        die;

	}

	public function save_optin_output() {

    	$optin_id = absint( $_POST['optin'] );
    	$hash = stripslashes( $_POST['hash'] );
    	$user = wp_get_current_user();
		$data = array();
        wp_parse_str( $_POST['data'], $data );

        // Grab the optin and update the meta.
        $meta = get_post_meta( $optin_id, '_om_meta', true );

        // Sanitize the option values.
        $meta['display']['user']       = $user->ID;
        $meta['display']['enabled']    = isset( $data['om-enabled'] ) ? 1 : 0;
        $meta['display']['global']     = isset( $data['om-global'] )  ? 1 : 0;
		$meta['display']['never']      = isset( $data['om-never'] ) ? stripslashes_deep( $data['om-never'] ) : array();
        $meta['display']['exclusive']  = isset( $data['om-exclusive'] ) ? stripslashes_deep( $data['om-exclusive'] ) : array();
        $meta['display']['categories'] = isset( $data['post_category'] ) ? stripslashes_deep( $data['post_category'] ) : array();
        $meta['display']['show']       = isset( $data['om-show'] ) ? stripslashes_deep( $data['om-show'] ) : array();
        $meta['display']['automatic']  = isset( $data['om-automatic'] ) ? 1 : 0;

        // Delete the transient cache for the optin.
        delete_transient( 'om_optin_' . $hash );
        delete_transient( 'om_optin_' . $optin_id );
        delete_transient( 'om_optin_meta_' . $hash );

        // Finally, update the option.
        update_post_meta( $optin_id, '_om_meta', $meta );
        echo json_encode( true );
        die;

	}

	/**
	 * Queries the remote URL via wp_remote_post and returns a json decoded response.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action The name of the $_POST action var
	 * @param array $body The content to retrieve from the remote URL
	 * @param array $headers The headers to send to the remote URL
	 * @param string $return_format The format for returning content from the remote URL
	 * @return string|boolean Json decoded response on success, false on failure
	 */
	public function perform_remote_request( $action, $body = array(), $headers = array(), $return_format = 'json' ) {

		/** Build body */
		$body = wp_parse_args( $body, array(
			'tgm-updater-action' 		=> $action,
			'tgm-updater-wp-version' 	=> get_bloginfo( 'version' ),
			'tgm-updater-referer' 		=> site_url()
		) );
		$body = http_build_query( $body, '', '&' );

		/** Build headers */
		$headers = wp_parse_args( $headers, array(
			'Content-Type' 		=> 'application/x-www-form-urlencoded',
			'Content-Length' 	=> strlen( $body )
		) );

		/** Setup variable for wp_remote_post */
		$post = array(
			'headers' 	=> $headers,
			'body' 		=> $body
		);

		/** Perform the query and retrieve the response */
		$response 		= wp_remote_post( esc_url_raw( 'http://optinmonster.com/' ), $post );
		$response_code 	= wp_remote_retrieve_response_code( $response );
		$response_body 	= wp_remote_retrieve_body( $response );

		/** Bail out early if there are any errors */
		if ( 200 != $response_code || is_wp_error( $response_body ) )
			return false;

		/** Return body content if not json, else decode json */
		if ( 'json' != $return_format )
			return $response_body;
		else
			return json_decode( $response_body );

		return false;

	}

	public function get_new_email_provider_html( $provider ) {

    	$ret = '';

    	switch ( $provider ) :
    	    case 'sendinblue' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<p class="padding-top"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-sendinblue/" title="%s" target="_blank"><em>%s</em></a></p>', __( 'We need your SendinBlue access key, secret key and an account label (to manage multiple SendinBlue accounts) to connect your account and a particular list to this optin. Please enter them in the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="access_key" value="" placeholder="%s" /><br /><input type="text" name="secret_key" value="" placeholder="%s" /><br /><input id="email-label" type="text" name="email_label" value="" placeholder="%s" style="margin-top: 5px;" /></p>', __( 'SendinBlue Access Key', 'optin-monster' ), __( 'SendinBlue Secret key', 'optin-monster' ), __( 'Custom SendinBlue Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="sendinblue">%s</a></p>', __( 'Connect to Sendinblue', 'optin-monster' ) );
				$ret .= '</form>';
				break;
    	    case 'feedblitz' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<p class="padding-top"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-feedblitz/" title="%s" target="_blank"><em>%s</em></a></p>', __( 'We need your Feedblitz API key and an account label (to manage multiple Feedblitz accounts) to connect your account and a particular list to this optin. Please enter them in the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="api_key" value="" placeholder="%s" style="margin-top:5px;" /><br /><input id="email-label" type="text" name="email_label" value="" placeholder="%s" style="margin-top: 5px;" /></p>', __( 'Feedblitz API Key', 'optin-monster' ), __( 'Custom Feedblitz Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="feedblitz">%s</a></p>', __( 'Connect to Feedblitz', 'optin-monster' ) );
				$ret .= '</form>';
				break;
			case 'mailchimp' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<p class="padding-top"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-mailchimp/" title="%s" target="_blank"><em>%s</em></a></p>', __( 'We need your MailChimp API key and an account label (to manage multiple MailChimp accounts) to connect your account and a particular list to this optin. Please enter them in the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="api_key" value="" placeholder="%s" /><br /><input id="email-label" type="text" name="email_label" value="" placeholder="%s" style="margin-top: 5px;" /></p>', __( 'Mailchimp API Key', 'optin-monster' ), __( 'Custom Mailchimp Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="mailchimp">%s</a></p>', __( 'Connect to MailChimp', 'optin-monster' ) );
				$ret .= '</form>';
				break;
			case 'madmimi' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<p class="padding-top"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-madmimi/" title="%s" target="_blank"><em>%s</em></a></p>', __( 'We need your Madmimi email or username, API key and account label (to manage multiple Madmimi accounts) to connect your account and a particular list to this optin. Please enter them in the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="username" value="" placeholder="%s" /><br /> ', __( 'Madmimi Email or Username', 'optin-monster' ) );
					$ret .= sprintf( '<input type="text" name="api_key" value="" placeholder="%s" style="margin-top:5px;" /><br /><input id="email-label" type="text" name="email_label" value="" placeholder="%s" style="margin-top: 5px;" /></p>', __( 'Madmimi API Key', 'optin-monster' ), __( 'Custom Madmimi Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="madmimi">%s</a></p>', __( 'Connect to Madmimi', 'optin-monster' ) );
				$ret .= '</form>';
				break;
			case 'constant-contact' :
				$ret .= '<form id="om-email-creds">';
                	$ret .= sprintf( '<div class="alert alert-success" style="margin: 15px 0 10px;"><p class="no-padding"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-constant-contact/" title="%s" target="_blank"><em>%s</em></a></p></div>', __( 'Because Constant Contact requires external authentication, you will need to register our application with Constant Contact before you can proceed.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
                	$ret .= sprintf( '<p><input id="email-label" type="text" name="email_label" value="" placeholder="%s" /></p>', __( 'Custom Constant Contact Account Label', 'optin-monster' ) );
                	$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green cc-auth" data-email-provider="constant-contact">%s</a></p>', __( 'Register OptinMonster with Your Constant Contact Account', 'optin-monster' ) );
                $ret .= '</form>';
				break;
			case 'aweber' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<div class="alert alert-success" style="margin: 15px 0 10px;"><p class="no-padding"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-aweber/" title="%s" target="_blank"><em>%s</em></a></p></div>', __( 'Because AWeber requires external authentication, you will need to register our application with AWeber before you can proceed. Register our app, copy the verification code and paste it in the field below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input id="email-label" type="text" name="email_label" value="" placeholder="%s" /></p>', __( 'Custom AWeber Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><input id="aweber-auth-code" type="text" name="aweber_auth_code" value="" placeholder="%s" /></p>', __( 'AWeber Auth Code (paste after clicking "Register" below)', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green aweber-auth" data-email-provider="aweber">%s</a></p>', __( 'Register OptinMonster with Your AWeber Account', 'optin-monster' ) );
				$ret .= '</form>';
				break;
			case 'campaign-monitor' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<p class="padding-top"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-campaign-monitor/" title="%s" target="_blank"><em>%s</em></a></p>', __( 'We need your Campaign Monitor API key and an account label (to manage multiple Cmapaign Monitor accounts) to connect your account and a particular list to this optin. Please enter them in the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="api_key" value="" placeholder="%s" /><br /><input id="email-label" type="text" name="email_label" value="" placeholder="%s" style="margin-top: 5px;" /></p>', __( 'Campaign Monitor API Key', 'optin-monster' ), __( 'Custom Campaign Monitor Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="campaign-monitor">%s</a></p>', __( 'Connect to Campaign Monitor', 'optin-monster' ) );
				$ret .= '</form>';
				break;
            case 'infusionsoft' :
                $ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<p class="padding-top"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-infusionsoft/" title="" target="_blank"><em>%s</em></a></p>', __( 'We need your Infusionsoft application name, API key and an account label (to manage multiple Infusionsoft accounts) to connect your account to this optin. Please enter them in the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="app_name" value="" placeholder="%s" /><br /><input type="text" name="api_key" value="" placeholder="%s" /><br /><input id="email-label" type="text" name="email_label" value="" placeholder="%s" style="margin-top: 5px;" /></p>', __( 'Infusionsoft Account Subdomain', 'optin-monster' ), __( 'Infusionsoft API Key', 'optin-monster' ), __( 'Custom Infusionsoft Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="infusionsoft">%s</a></p>', __( 'Connect to Infusionsoft', 'optin-monster' ) );
				$ret .= '</form>';
				break;
            case 'getresponse' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<p class="padding-top"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-getresponse/" title="%s" target="_blank"><em>%s</em></a></p>', __( 'We need your GetResponse API key and an account label (to manage multiple GetResponse accounts) to connect your account and a particular list to this optin. Please enter them in the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="api_key" value="" placeholder="%s" /><br /><input id="email-label" type="text" name="email_label" value="" placeholder="%s" style="margin-top: 5px;" /></p>', __( 'GetResponse API Key', 'optin-monster' ), __( 'Custom GetResponse Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="getresponse">%s</a></p>', __( 'Connect to GetResponse', 'optin-monster' ) );
				$ret .= '</form>';
				break;
            case 'icontact' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<div class="alert alert-success" style="margin: 15px 0 10px;"><p class="no-padding"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-icontact/" title="%s" target="_blank"><em>%s</em></a></p></div>', __( 'Because iContact requires you to create an external application for your account, you will need to do that before you can proceed. Register your app, then fill out the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="username" value="" placeholder="%s" /></p>', __( 'iContact Username', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="app_id" value="" placeholder="%s" /></p>', __( 'iContact Application ID', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="app_pass" value="" placeholder="%s" /></p>', __( 'iContact Application Password', 'optin-monster' ) );
					$ret .= sprintf( '<p><input id="email-label" type="text" name="email_label" value="" placeholder="%s" /></p>', __( 'Custom iContact Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="icontact">%s</a> <a href="https://app.icontact.com/icp/core/registerapp/" class="button button-secondary button-large" title="%s" target="_blank">%s</a></p>', __( 'Connect to iContact', 'optin-monster' ), __( 'Create Your iContact App', 'optin-monster' ), __( 'Create Your iContact App', 'optin-monster' ) );
				$ret .= '</form>';
				break;
            case 'mailpoet' :
                $ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<div class="alert alert-success" style="margin: 15px 0 10px;"><p class="no-padding"><strong>%s</strong></p></div>', __( 'You need to install and activate the MailPoet (Wysija) plugin for this option to be available.', 'optin-monster' ) );
				$ret .= '</form>';
				break;
            case 'pardot' :
                case 'madmimi' :
				$ret .= '<form id="om-email-creds">';
					$ret .= sprintf( '<p class="padding-top"><strong>%s</strong> <a href="http://optinmonster.com/docs/connect-optinmonster-pardot/" title="%s" target="_blank"><em>%s</em></a></p>', __( 'We need your Pardot email, password, user key and account label (to manage multiple Pardot accounts) to connect your account and a particular list to this optin. Please enter them in the fields below.', 'optin-monster' ), __( 'Documentation Help', 'optin-monster' ), __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) );
					$ret .= sprintf( '<p><input type="text" name="email" value="" placeholder="%s" /><br /> ', __( 'Pardot Email', 'optin-monster' ) );
					$ret .= sprintf( '<input type="password" name="password" value="" placeholder="%s" style="margin-top:5px;" /><br /><input type="password" name="user_key" value="" placeholder="%s" style="margin-top:5px;" /><br /><input id="email-label" type="text" name="email_label" value="" placeholder="%s" style="margin-top: 5px;" /></p>', __( 'Pardot Password', 'optin-monster' ), __( 'Pardot User Key', 'optin-monster' ), __( 'Custom Pardot Account Label', 'optin-monster' ) );
					$ret .= sprintf( '<p><a href="#" class="button button-primary button-large green connect-api" data-email-provider="pardot">%s</a></p>', __( 'Connect to Pardot', 'optin-monster' ) );
				$ret .= '</form>';
				break;
            default :
                $ret .= apply_filters( 'optin_monster_new_email_provider_html', $provider );
                break;
		endswitch;

		return $ret;

	}

	/**
	 * Activates an Addon via Ajax.
	 *
	 * @since 1.0.6
	 */
	public function activate_addon() {

		/** Do a security check first */
		check_ajax_referer( 'optinmonster_activate_addon', 'nonce' );

		/** Activate the plugin */
		if ( isset( $_POST['plugin'] ) ) {
			$activate = activate_plugin( $_POST['plugin'] );

			if ( is_wp_error( $activate ) ) {
				echo json_encode( array( 'error' => $activate->get_error_message() ) );
				die;
			}
		}

		echo json_encode( true );
		die;

	}

	/**
	 * Deactivates an Addon via Ajax.
	 *
	 * @since 1.0.6
	 */
	public function deactivate_addon() {

		/** Do a security check first */
		check_ajax_referer( 'optinmonster_deactivate_addon', 'nonce' );

		/** Deactivate the plugin */
		if ( isset( $_POST['plugin'] ) )
			$deactivate = deactivate_plugins( $_POST['plugin'] );

		echo json_encode( true );
		die;

	}

	/**
	 * Installs an Addon via Ajax.
	 *
	 * @since 1.0.6
	 *
	 * @global string $hook_suffix The current pagehook suffx
	 */
	public function install_addon() {

		/** Do a security check first */
		check_ajax_referer( 'optinmonster_install_addon', 'nonce' );

		/** Install the plugin */
		if ( isset( $_POST['plugin'] ) ) {
			/** Here we go - we will use WP_Filesystem to install the plugin from Amazon S3 */
			$download_url 	= $_POST['plugin'];
			global $hook_suffix; // Have to declare this in order to avoid an undefined index notice, doesn't do anything

			/** Set the current screen to avoid undefined notices */
			set_current_screen();

			/** Prepare variables for request_filesystem_credentials */
			$method = '';
			$url 	= add_query_arg(
				array(
					'page' => 'optin-monster',
					'tab'  => 'addons'
				),
				admin_url( 'admin.php' )
			);

			/** Start output bufferring to catch the filesystem form if credentials are needed */
			ob_start();
			if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, null ) ) ) {
				$form = ob_get_clean();
				echo json_encode( array( 'form' => $form ) );
				die;
			}

			if ( ! WP_Filesystem( $creds ) ) {
				ob_start();
				request_filesystem_credentials( $url, $method, true, false, null ); // Setup WP_Filesystem
				$form = ob_get_clean();
				echo json_encode( array( 'form' => $form ) );
				die;
			}

			/** We do not need any extra credentials if we have gotten this far, so let's install the plugin */
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes
			require_once plugin_dir_path( optin_monster::get_instance()->file ) . 'inc/common/skin.php'; // Need to customize the upgrader skin

			/** Create a new Plugin_Upgrader instance */
			$installer = new Plugin_Upgrader( $skin = new optin_monster_skin() );
			$installer->install( $download_url );

			/** Flush the cache and return the newly installed plugin basename */
			wp_cache_flush();
			if ( $installer->plugin_info() ) {
				$plugin_basename = $installer->plugin_info();
				echo json_encode( array( 'plugin' => $plugin_basename ) );
				die;
			}
		}

		echo json_encode( true );
		die;

	}

	public function canvas_shortcode() {

		$parsed_text = stripcslashes($_POST['text']);
		$parsed_text = str_replace( array( 'ajax="true"', 'ajax=true' ), '', $parsed_text );
		$parsed_text = do_shortcode( $parsed_text );

		echo json_encode($parsed_text);
		die;
	}

}

// Instantiate the class.
global $optin_monster_ajax;
$optin_monster_ajax = new optin_monster_ajax();