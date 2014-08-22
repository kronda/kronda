<?php
class optin_monster_ajax_handler {

    public function __construct() {

        // Bring base class into scope.
		global $optin_monster_account;

		// Set class properties.
		$this->base	   = optin_monster::get_instance();
		$this->account = $optin_monster_account;

        // Route to the correct action
        if ( ! empty( $_POST['optin_monster_ajax_action'] ) ) {
            header( "X-Robots-Tag: noindex, nofollow", true );
	        $this->action = $_POST['optin_monster_ajax_action'];
	        add_action( 'init', array( $this, 'ajax_router' ) );
        }

    }

	/**
	 * Routes the request to the correct action
	 */
	public function ajax_router() {

		switch ( $this->action ) {
			case 'load_optinmonster' :
				$this->load_optinmonster();
				break;
			case 'do_optinmonster' :
				$this->do_optinmonster();
				break;
			case 'do_optinmonster_custom' :
				$this->do_optinmonster_custom();
				break;
			case 'track_optinmonster' :
				$this->track_optinmonster();
				break;
		}

	}

    public function load_optinmonster( $data = false ) {

        global $wpdb;
        $table = $wpdb->prefix . 'om_hits_log';

        // If the data is not false, don't grab from $_POST but rather from data passed.
        if ( $data ) {
        	$this->hash = $data['hash'];
        	$this->referer = wp_get_referer();
    		$this->ua = stripslashes( $_SERVER['HTTP_USER_AGENT'] );
        } else {
            $this->hash = stripslashes( $_POST['optin'] );
        	$this->referer = stripslashes( $_POST['referer'] );
    		$this->ua = stripslashes( strip_tags( $_POST['user_agent'] ) );
        }

		// If the optin does not exist, return early.
		$optin = get_posts( array( 'post_type' => 'optin', 'name' => $this->hash, 'posts_per_page' => 1 ) );
		if ( ! $optin ) {
		    if ( $data ) {
		        return false;
            } else {
			    echo json_encode( false );
                exit;
            }
		}

		// If this optin is being split tested, grab the other optin and randomly choose which one to display.
        $meta = get_post_meta( $optin[0]->ID, '_om_meta', true );

		if ( isset( $meta['has_clone'] ) ) {
            $cloned_optin = get_posts( array( 'post_type' => 'optin', 'p' => $meta['has_clone'], 'posts_per_page' => 1 ) );

    		// If the clone is not active, revert back to the main optin.
    		$clone_meta = get_post_meta( $meta['has_clone'], '_om_meta', true );
    		if ( empty( $clone_meta['display']['enabled'] ) || ! $clone_meta['display']['enabled'] ) {
    		    $this->optin = $optin[0];
                $this->meta  = $meta;
    		} else {
    		    if ( empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] ) {
                    $this->optin = $cloned_optin[0];
                    $this->meta  = $cloned_meta;
    		    } else {
                    // Set the clone in the optin array and chose randomly from it.
            		$optin[1]    = $cloned_optin[0];
            		$this->optin = $optin[rand()%count($optin)];
                    $this->meta  = get_post_meta( $this->optin->ID, '_om_meta', true );
                }
            }
		} else {
		    $this->optin = $optin[0];
            $this->meta  = $meta;
        }

		// Load the theme builder.
		require_once plugin_dir_path( $this->base->file ) . 'inc/templates/template.php';

		$option = get_option( 'optin_monster_license' );
		$test   = get_post_meta( $this->optin->ID, '_om_test_mode', true );
		$test   = empty( $test ) ? false : true;

		// Prepare the data response.
		$this->ssl = ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443;
		$theme = new optin_monster_template( $this->meta['type'], $this->meta['theme'], $this->optin->post_name, $this->optin->ID, 'live', $this->ssl );
		$this->data['html']	  = $theme->build_optin();
		$this->data['type']   = $this->meta['type'];
		$this->data['theme']  = $this->meta['theme'];
		$this->data['id']	  = $this->optin->ID;
		$this->data['cookie'] = $this->meta['cookie'];
		$this->data['delay']  = $this->meta['delay'];
		$this->data['hash']   = $this->optin->post_name;
		$this->data['second'] = $this->meta['second'];
		$this->data['test']   = $test;
		$this->data['exit']   = isset( $this->meta['exit'] ) ? $this->meta['exit'] : false;
		$this->data['custom'] = isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ? true : false;
		$this->data['global_cookie'] = isset( $option['global_cookie'] ) ? $option['global_cookie'] : false;

		// Prepare any fonts that are to be loaded.
		$this->data['fonts'] = ! empty( $this->meta['fonts'] ) ? urlencode( implode( '|', $this->meta['fonts'] ) ) : false;
        $this->data = apply_filters('optin_monster_load_optinmonster_bottom', $this->data );

		// Send back the appropriate JSONP response.
		if ( $data ) {
    		return $this->data;
		} else {
		    echo json_encode( $this->data );
            exit;
        }

    }

    public function do_optinmonster() {

        global $wpdb;
        $table = $wpdb->prefix . 'om_hits_log';
    	$this->hash = apply_filters( 'optin_monster_do_optinmonster_hash', stripslashes( $_POST['optin'] ) );
    	$this->optin = apply_filters( 'optin_monster_do_optinmonster_optin', stripslashes( $_POST['optin_id'] ) );
    	$this->referer = apply_filters( 'optin_monster_do_optinmonster_referer', stripslashes( $_POST['referer'] ) );
		$this->ua = apply_filters( 'optin_monster_do_optinmonster_ua', stripslashes( strip_tags( $_POST['user_agent'] ) ) );

		// If we have reached this point, we need to grab the meta and start doing cool optin stuff.
		$this->meta  = apply_filters( 'optin_monster_do_optinmonster_meta', get_post_meta( $this->optin, '_om_meta', true ) );
		$this->email = apply_filters( 'optin_monster_do_optinmonster_email', stripslashes( $_POST['email'] ) );
		$this->name  = apply_filters( 'optin_monster_do_optinmonster_name', stripslashes( $_POST['name'] ) );
		$this->email_id = apply_filters( 'optin_monster_do_optinmonster_email_id', $this->meta['email']['account'] );
		$this->data  = apply_filters( 'optin_monster_do_optinmonster_data', $this->merge_vars = array() );
		$this->retval = apply_filters( 'optin_monster_do_optinmonster_retval', $this->api = false );
		global $optin_monster_account;
		$this->account = $optin_monster_account;
		$this->providers = $this->account->get_email_providers();

    	// Load in the email provider API.
		switch ( $this->meta['email']['provider'] ) {
		    case 'sendinblue' :
                if ( array_key_exists( 'sendinblue', $this->providers ) ) :
                    // Load the Sendinblue API.
                    if ( ! class_exists( 'Mailin' ) )
                        require_once plugin_dir_path( $this->base->file ) . 'inc/email/sendinblue/sendinblue.class.php';

                    $this->api = new Mailin( 'https://api.sendinblue.com/v1.0',$this->providers['sendinblue'][$this->meta['email']['account']]['access_key'], $this->providers['sendinblue'][$this->meta['email']['account']]['secret_key'] );

                    if ( $this->name && 'false' !== $this->name ){
                        $names = explode( ' ', $this->name );
                        if ( isset( $names[0] ) ){
                            $info['NAME'] = $names[0];
                        }
                        if ( isset( $names[1] ) ){
                            $info['SURNAME'] = $names[1];
                        }
                    }
                    $blacklisted = 0;
                    $listid_unlink = array();
                    $this->data = apply_filters( 'optin_monster_do_optinmonster_data_sendinblue', $this->data );
                    $this->api->create_update_user( $this->email, $info, $blacklisted, (array)$this->meta['email']['list_id'], $listid_unlink );
                    $this->data['success'] = true;
                else :
                    $this->data['error'] = 'No email provider selected for this optin.';
                endif;

                break;
		    case 'feedblitz' :
				if ( array_key_exists( 'feedblitz', $this->providers ) ) :
				    libxml_use_internal_errors( true );
				    $headers  = array( 'Content-Type' => 'application/x-www-form-urlencoded', 'User-Agent' => 'OptinMonster API', 'Referer' => wp_get_referer() );
				    $user     = wp_remote_get( 'https://www.feedblitz.com/f.api/user?key=' . $this->providers['feedblitz'][$this->meta['email']['account']]['api'], array( 'headers' => $headers ) );
    				$user_obj = json_decode( json_encode( simplexml_load_string( wp_remote_retrieve_body( $user ) ) ) );
    				$body     = array(
    				    'EMAIL'         => $this->email,
    				    'EMAIL_'        => '',
    				    'EMAIL_ADDRESS' => '',
    				    'FEEDID'        => $this->meta['email']['list_id'],
    				    'CIDS'          => 1,
    				    'PUBLISHER'     => $user_obj->user->id
    				);
    				$body      = http_build_query( $body, '', '&' );
    				$headers['Content-Length'] = strlen( $body );
    				$this->api = wp_remote_post( 'http://www.feedblitz.com/f/f.fbz?AddNewUserDirect', array( 'headers' => $headers, 'body' => $body ) );
                    $this->data['success'] = true;
				else :
				    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
				endif;

				break;
			case 'mailchimp' :
			    if ( array_key_exists( 'mailchimp', $this->providers ) ) :
			        if ( ! class_exists( 'MCAPI' ) )
    				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/mailchimp/mailchimp.php';

    				$this->api = new MCAPI( $this->providers['mailchimp'][$this->meta['email']['account']]['api'] );
    				$this->merge_vars = array( 'GROUPINGS' => array() );
    				if ( $this->name && 'false' !== $this->name ) {
    				    $names = explode( ' ', $this->name );
    				    if ( isset( $names[0] ) )
    				        $this->merge_vars['FNAME'] = $names[0];
    				    if ( isset( $names[1] ) )
    				        $this->merge_vars['LNAME'] = $names[1];
    				}
    				$double = apply_filters( 'optin_monster_mailchimp_double', true );

    				if ( ! empty( $this->meta['email']['segments'] ) ) {
    					$i = 0;
    					foreach ( $this->meta['email']['segments'] as $group_id => $segments ) {
    						$this->merge_vars['GROUPINGS'][$i]['id'] = $group_id;
    						$this->merge_vars['GROUPINGS'][$i]['groups'] = $segments;
    						$i++;
    					}
    				}

                    $this->merge_vars = apply_filters( 'optin_monster_mailchimp_merge_vars', $this->merge_vars, $this->email, $this->api );
                    $this->retval = $this->api->listSubscribe( $this->meta['email']['list_id'], $this->email, $this->merge_vars, 'html', (bool) $double );
                    $this->data = apply_filters( 'optin_monster_do_optinmonster_data_mailchimp', $this->data );
    				if ( $this->api->errorCode )
    					$this->data['error'] = $this->api->errorMessage;
    				else
    					$this->data['success'] = $this->merge_vars;
                else :
                    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
                endif;

				break;
            case 'madmimi' :
				if ( array_key_exists( 'madmimi', $this->providers ) ) :
					// Load the Madmimi API.
					if ( ! class_exists( 'MadMimi' ) )
					    require_once plugin_dir_path( $this->base->file ) . 'inc/email/madmimi/MadMimi.class.php';

					$this->api = new MadMimi( $this->providers['madmimi'][$this->meta['email']['account']]['username'], $this->providers['madmimi'][$this->meta['email']['account']]['api'] );
					$info = array( 'email' => $this->email, 'add_list' => $this->meta['email']['list_id'] );
					if ( $this->name && 'false' !== $this->name ){
                        $names = explode( ' ', $this->name );
                        if ( isset( $names[0] ) ){
                            $info['firstName'] = $names[0];
                        }
                        if ( isset( $names[1] ) ){
                            $info['lastName'] = $names[1];
                        }
                    }
                    $this->data = apply_filters( 'optin_monster_do_optinmonster_data_madmimi', $this->data );
                    $this->api->AddUser( $info );
                    $this->data['success'] = true;
				else :
				    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
				endif;

				break;
            case 'aweber' :
                if ( array_key_exists( 'aweber', $this->providers ) ) :
                    // Load the AWeber API.
                    if ( ! class_exists( 'AWeberAPI' ) )
    				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/aweber/aweber_api.php';

    				$api = new AWeberAPI( $this->providers['aweber'][$this->meta['email']['account']]['auth_key'], $this->providers['aweber'][$this->meta['email']['account']]['auth_token'] );
    				try {
    				    $account = $api->getAccount( $this->providers['aweber'][$this->meta['email']['account']]['access_token'], $this->providers['aweber'][$this->meta['email']['account']]['access_secret'] );
    				    foreach ( $account->lists as $offset => $list ) {
        				    if ( $this->meta['email']['list_id'] == $list->id ) {
            				    $list = $account->loadFromUrl( '/accounts/' . $account->id . '/lists/' . $list->id );
            				    $params = array( 'email' => $this->email );
            				    if ( $this->name && 'false' !== $this->name ){
            				        $params['name'] = $this->name;
                                }
            				    $subscribers = $list->subscribers;
                                $this->data = apply_filters( 'optin_monster_do_optinmonster_data_aweber', $this->data );
            				    $new_subscriber = $subscribers->create( $params );
            				    $this->data['success'] = true;
            				    break;
                            }
    				    }
    				} catch(AWeberAPIException $e) {
        				$this->data['error'] = $e->message;
    				}
				else :
                    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
                endif;

                break;
            case 'constant-contact' :
                if ( array_key_exists( 'constant-contact', $this->providers ) ) :
					$response = wp_remote_get( 'https://api.constantcontact.com/v2/contacts?api_key=fbstngt7u3tcvw827w66zyd3&access_token=' . $this->providers['constant-contact'][$this->meta['email']['account']]['token'] . '&email=' . $this->email );
					$contact = json_decode( wp_remote_retrieve_body( $response ) );
					if ( ! empty( $contact->results ) ) {
					    $args = $body = array();
    				    $body['email_addresses'] = array();
    				    $body['email_addresses'][0]['id'] = $this->meta['email']['list_id'];
    				    $body['email_addresses'][0]['status'] = 'ACTIVE';
    				    $body['email_addresses'][0]['action_by'] = 'ACTION_BY_VISITOR';
    				    $body['email_addresses'][0]['confirm_status'] = 'CONFIRMED';
    				    $body['email_addresses'][0]['email_address'] = $this->email;
    				    $body['lists'] = array();
    				    $body['lists'][0]['id'] = $this->meta['email']['list_id'];
    				    if ( $this->name && 'false' !== $this->name ) {
    				        $names = explode( ' ', $this->name );
    				        if ( isset( $names[0] ) ) {
    				            $body['first_name'] = $names[0];
    				        }
    				        if ( isset( $names[1] ) ) {
        				        $body['last_name'] = $names[1];
    				        }
    				    }

                        $args['body'] = json_encode( $body );
                        $args['method'] = 'PUT';
    				    $args['headers']['Content-Type'] = 'application/json';
    				    $args['headers']['Content-Length'] = strlen( json_encode( $body ) );
                        $this->data = apply_filters( 'optin_monster_do_optinmonster_data_constantcontact', $this->data );
                        $args = apply_filters( 'optin_monster_do_optinmonster_args_contantcontact', $args );
    					$update = wp_remote_request( 'https://api.constantcontact.com/v2/contacts/' . $contact->results[0]->id . '?api_key=fbstngt7u3tcvw827w66zyd3&access_token=' . $this->providers['constant-contact'][$this->meta['email']['account']]['token'], $args );
                        $res = json_decode( wp_remote_retrieve_body( $update ) );
    					$this->data['success'] = true;
					} else {
					    $args = $body = array();
					    $body['email_addresses'] = array();
					    $body['email_addresses'][0]['id'] = $this->meta['email']['list_id'];
					    $body['email_addresses'][0]['status'] = 'ACTIVE';
					    $body['email_addresses'][0]['email_address'] = $this->email;
					    $body['lists'] = array();
					    $body['lists'][0]['id'] = $this->meta['email']['list_id'];
					    if ( $this->name && 'false' !== $this->name )
					        $body['first_name'] = $this->name;

                        $args['body'] = json_encode( $body );

					    $args['headers']['Content-Type'] = 'application/json';
					    $args['headers']['Content-Length'] = strlen( json_encode( $body ) );
                        $this->data = apply_filters( 'optin_monster_do_optinmonster_data_constantcontact', $this->data );
                        $args = apply_filters( 'optin_monster_do_optinmonster_args_contantcontact', $args );
    					$create = wp_remote_post( 'https://api.constantcontact.com/v2/contacts?api_key=fbstngt7u3tcvw827w66zyd3&access_token=' . $this->providers['constant-contact'][$this->meta['email']['account']]['token'], $args );
    					$this->data['success'] = true;
					}
                else :
                    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
                endif;

                break;
            case 'campaign-monitor' :
                if ( array_key_exists( 'campaign-monitor', $this->providers ) ) :
                    if ( ! class_exists( 'CS_Rest_Subscribers' ) )
                        require_once plugin_dir_path( $this->base->file ) . 'inc/email/campaign-monitor/csrest_subscribers.php';

					$list = new CS_Rest_Subscribers( $this->meta['email']['list_id'], array( 'api_key' => $this->providers['campaign-monitor'][$this->meta['email']['account']]['api'] ) );
					$this->data = apply_filters( 'optin_monster_do_optinmonster_data_campaignmonitor', $this->data );
                    if ( $this->name && 'false' !== $this->name )
					    $result = $list->add( array( 'EmailAddress' => $this->email, 'Name' => $this->name, 'Resubscribe' => true, 'CustomFields' => array( array( 'Key' => 'OptinMonster', 'Value' => true ) ) ) );
                    else
                        $result = $list->add( array( 'EmailAddress' => $this->email, 'Resubscribe' => true, 'CustomFields' => array( array( 'Key' => 'OptinMonster', 'Value' => true ) ) ) );
					if ( $result->was_successful() ) {
    					$this->data['success'] = true;
					} else {
					    $this->data['error'] = $result->response->Message;
                    }
                else :
                    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
                endif;

                break;
            case 'infusionsoft' :
                if ( array_key_exists( 'infusionsoft', $this->providers ) ) :
                    if ( ! class_exists( 'iSDK' ) )
                        require_once plugin_dir_path( $this->base->file ) . 'inc/email/infusionsoft/isdk.php';
                    $this->data = apply_filters( 'optin_monster_do_optinmonster_data_infusionsoft', $this->data );
					try {
                        $app = new iSDK();
                        $app->cfgCon( $this->providers['infusionsoft'][$this->meta['email']['account']]['app'], $this->providers['infusionsoft'][$this->meta['email']['account']]['api'], 'throw' );
                    } catch( iSDKException $e ){
                        $this->data['error'] = 'There was an error processing your information. Please try again.';
                        echo json_encode( $this->data );
                        exit;
                    }

					if ( $this->name && 'false' !== $this->name ){
                        $names = explode( ' ', $this->name );
                        if ( isset( $names[0] ) && isset( $names[1] ) ) {
                            $first = $names[0];
                            $entry = array( 'FirstName' => $names[0], 'LastName' => $names[1], 'Email' => $this->email );
                        }
                        else{
                            $entry = array( 'FirstName' => $this->name, 'Email' => $this->email );
                        }
                    }
                    else{
                        $entry = array( 'Email' => $this->email );
                    }

                    try {
                        $bool = $app->findByEmail( $this->email, array( 'Id' ) );
                        if ( isset( $bool[0] ) && ! empty( $bool[0]['Id'] ) ) {
                            $app->updateCon( $bool[0]['Id'], $entry );
                            $group_add = $app->grpAssign( $bool[0]['Id'], $this->meta['email']['list_id'] );
                        } else {
                            $contact_id = $app->addCon( $entry );
                            $group_add  = $app->grpAssign( $contact_id, $this->meta['email']['list_id'] );
                        }
                    } catch ( iSDKException $e ) {
                        $this->data['error'] = __( 'There was an error processing your information. Please try again.', 'optin-monster' );
                        echo json_encode( $this->data );
                        exit;
                    }

					$this->data['success'] = true;
                else :
                    $this->data['error'] = 'No email provider selected for this optin.';
                endif;

                break;
            case 'getresponse' :
                if ( array_key_exists( 'getresponse', $this->providers ) ) :
                    // Load the GetResponse API.
                    if ( ! class_exists( 'jsonRPCClient' ) )
                        require_once plugin_dir_path( $this->base->file ) . 'inc/email/getresponse/jsonrpc.php';

                    $this->data = apply_filters( 'optin_monster_do_optinmonster_data_getresponse', $this->data );
                    try {
                        $api = new jsonRPCClient( 'http://api2.getresponse.com' );

                        if ( $this->name && 'false' !== $this->name )
                            $res = $api->add_contact( $this->providers['getresponse'][$this->meta['email']['account']]['api'], array( 'campaign' => $this->meta['email']['list_id'], 'name' => $this->name, 'email' => $this->email, 'cycle_day' => 0 ) );
                        else
                            $res = $api->add_contact( $this->providers['getresponse'][$this->meta['email']['account']]['api'], array( 'campaign' => $this->meta['email']['list_id'], 'email' => $this->email, 'cycle_day' => 0 ) );

                        $this->data['success'] = true;
                    } catch ( Exception $e ) {
	                    $message = $e->getMessage();
	                    if ( strpos( $message, 'Contact already added to target campaign' ) !== false ){
		                    $this->data['success'] = true;
	                    } else {
		                    $this->data['error'] = $e->getMessage();
	                    }
                    }
                else :
                    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
                endif;

                break;
            case 'icontact' :
                $this->data = apply_filters( 'optin_monster_do_optinmonster_data_icontact', $this->data );
                if ( array_key_exists( 'icontact', $this->providers ) ) :
                    // Load the iContact API.
                    if ( ! class_exists( 'iContactApi' ) )
                        require_once plugin_dir_path( $this->base->file ) . 'inc/email/icontact/iContactApi.php';

                    try {
                        iContactApi::getInstance()->setConfig(array(
                        	'appId'       => $this->providers['icontact'][$this->meta['email']['account']]['app_id'],
                        	'apiPassword' => $this->providers['icontact'][$this->meta['email']['account']]['app_pass'],
                        	'apiUsername' => $this->providers['icontact'][$this->meta['email']['account']]['username']
                        ));
                        $icontact = iContactApi::getInstance();
                        if ( $this->name && 'false' !== $this->name ){
                            $first = null;
                            $last = null;
                            $names = explode( ' ', $this->name );
                            if ( isset( $names[0] ) ) {
                                $first = $names[0];
                            }
                            if ( isset( $names[1] ) ) {
                                $last = $names[1];
                            }
                            $res = $icontact->addContact( $this->email, 'normal', null, $first, $last );
                        }
                        else{
                            $res = $icontact->addContact( $this->email );
                        }

                        // Subscribe the contact to the list.
                        $sub = $icontact->subscribeContactToList( $res->contactId, $this->meta['email']['list_id'] );

                        $this->data['success'] = true;
                    } catch ( Exception $e ) {
                        $errors = $icontact->getErrors();
                        $this->data['error'] = $errors[0];
                    }
                else :
                    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
                endif;

                break;
            case 'mailpoet' :
                // Populate data submitted.
                if ( $this->name && 'false' !== $this->name ){
                    $names = explode( ' ', $this->name );
                    if ( isset( $names[0] ) && isset( $names[1] ) ) {
                        $userData = array( 'email' => $this->email, 'firstname' => $names[0], 'lastname' => $names[1] );
                    }
                    else{
                        $userData = array( 'email' => $this->email, 'firstname' => $this->name );
                    }
                }
                else{
                    $userData = array( 'email' => $this->email );
                }
                $data = array(
                  'user'      => $userData,
                  'user_list' => array( 'list_ids' => array( $this->meta['email']['list_id'] ) )
                );

                // Add subscriber to MailPoet.
                $userHelper = WYSIJA::get( 'user', 'helper' );
                $userHelper->addSubscriber( $data );

                $this->data['success'] = true;
                break;
            case 'pardot' :
                $this->data = apply_filters( 'optin_monster_do_optinmonster_data_pardot', $this->data );
                if ( array_key_exists( 'pardot', $this->providers ) ) :
                    // Load the Pardot API.
    				if ( ! class_exists( 'Pardot_OM_API' ) )
    				    require_once plugin_dir_path( $this->base->file ) . 'inc/email/pardot/pardot-api-class.php';

                    // Attempt to connect to the Pardot API to retrieve lists.
    				$api = new Pardot_OM_API( array( 'email' => $this->providers['pardot'][$this->meta['email']['account']]['email'], 'password' => $this->providers['pardot'][$this->meta['email']['account']]['password'], 'user_key' => $this->providers['pardot'][$this->meta['email']['account']]['user_key'] ) );
    				$api->authenticate( array( 'email' => $this->providers['pardot'][$this->meta['email']['account']]['email'], 'password' => $this->providers['pardot'][$this->meta['email']['account']]['password'], 'user_key' => $this->providers['pardot'][$this->meta['email']['account']]['user_key'] ) );

    				// Populate data submitted.
                    if ( $this->name && 'false' !== $this->name ){
                        $names = explode( ' ', $this->name );
                        if ( isset( $names[0] ) && isset( $names[1] ) ) {
                            $url = 'https://pi.pardot.com/api/prospect/version/3/do/upsert/email/' . $this->email . '?campaign_id=' . $this->meta['email']['list_id'] . '&first_name=' . $names[0] . '&last_name=' . $names[1] . '&api_key=' . $api->api_key . '&user_key=' . $this->providers['pardot'][$this->meta['email']['account']]['user_key'];
                        }
                        else{
                            $url = 'https://pi.pardot.com/api/prospect/version/3/do/upsert/email/' . $this->email . '?campaign_id=' . $this->meta['email']['list_id'] . '&first_name=' . $this->name . '&api_key=' . $api->api_key . '&user_key=' . $this->providers['pardot'][$this->meta['email']['account']]['user_key'];
                        }
                    }
                    else{
                        $url = 'https://pi.pardot.com/api/prospect/version/3/do/upsert/email/' . $this->email . '?campaign_id=' . $this->meta['email']['list_id'] . '&api_key=' . $api->api_key . '&user_key=' . $this->providers['pardot'][$this->meta['email']['account']]['user_key'];
                    }
                    $contact  = wp_remote_post( $url );
    				$xml_resp = new SimpleXMLElement( wp_remote_retrieve_body( $contact ) );
    				$response = json_decode( json_encode( $xml_resp ) );

    				if ( isset( $response->err ) )
    				    $this->data['error'] = (string) $response->err;
    				else
    				    $this->data['success'] = true;
                else :
                    $this->data['error'] = __( 'No email provider selected for this optin.', 'optin-monster' );
                endif;
                break;
		}

		// If the user has specified a redirect, set it now.
		if ( ! empty( $this->meta['redirect'] ) )
		    $this->data['redirect'] = esc_url( $this->meta['redirect'] );

        // Allow the data to be filtered before sending back and counting conversions.
        $this->data = apply_filters( 'optin_monster_conversion_data', $this->data, $this );

		// If there is an error or the data is empty for some reason, send it back early.
		if ( empty( $this->data ) || isset( $this->data['error'] ) ) {
			// Send back the appropriate JSONP response.
			echo json_encode( $this->data );

			// Exit and kill the process.
			exit;
		} else {
			// Save the conversion to the DB if reporting is active.
            global $optin_monster;
            if ( $optin_monster->is_reporting_active() )
			    $update_hits = $wpdb->insert( $table, array( 'hit_date' => current_time( 'mysql' ), 'optin_id' => $this->optin, 'hit_type' => 'conversion', 'referer' => esc_url( $this->referer ), 'user_agent' => esc_attr( $this->ua ) ) );
			// Increment the optin counter.
            $counter = get_post_meta( $this->optin, 'om_conversions', true );
            update_post_meta( $this->optin, 'om_conversions', (int) $counter + 1 );

			// Send back the appropriate JSONP response.
			echo json_encode( $this->data );

			// Exit and kill the process.
			exit;
		}

    }

    public function do_optinmonster_custom() {

        global $wpdb;
        $table = $wpdb->prefix . 'om_hits_log';
    	$this->optin = stripslashes( $_POST['optin_id'] );
    	$this->referer = stripslashes( $_POST['referer'] );
		$this->ua = stripslashes( strip_tags( $_POST['user_agent'] ) );
		$this->meta = get_post_meta( $this->optin, '_om_meta', true );

		// Save the conversion to the DB if reporting is active.
		global $optin_monster;
		if ( $optin_monster->is_reporting_active() )
		    $update_hits = $wpdb->insert( $table, array( 'hit_date' => current_time( 'mysql' ), 'optin_id' => $this->optin, 'hit_type' => 'conversion', 'referer' => esc_url( $this->referer ), 'user_agent' => esc_attr( $this->ua ) ) );

		// Increment the optin counter.
        $counter = get_post_meta( $this->optin, 'om_conversions', true );
        update_post_meta( $this->optin, 'om_conversions', (int) $counter + 1 );

		// If the user has specified a redirect, send back that response.
		if ( ! empty( $this->meta['redirect'] ) )
		    echo json_encode( array( 'redirect' => esc_url( $this->meta['redirect'] ) ) );
        else
		    echo json_encode( true );

		// Exit and kill the process.
		exit;

	}

	public function track_optinmonster() {

    	global $wpdb;
        $table = $wpdb->prefix . 'om_hits_log';
    	$this->optin = (int) stripslashes( $_POST['optin'] );
    	$this->referer = stripslashes( $_POST['referer'] );
		$this->ua = stripslashes( strip_tags( $_POST['user_agent'] ) );

		// Save the conversion to the DB if reporting is active.
		global $optin_monster;
		if ( $optin_monster->is_reporting_active() )
		    $update_hits = $wpdb->insert( $table, array( 'hit_date' => current_time( 'mysql' ), 'optin_id' => $this->optin, 'hit_type' => 'impression', 'referer' => esc_url( $this->referer ), 'user_agent' => esc_attr( $this->ua ) ) );

		// Increment the optin counter.
        $counter = get_post_meta( $this->optin, 'om_counter', true );
        update_post_meta( $this->optin, 'om_counter', (int) $counter + 1 );

		// Exit.
        echo json_encode( true );
		exit;

	}

}

// Instantiate the class.
global $optin_monster_ajax_handler;
$optin_monster_ajax_handler = new optin_monster_ajax_handler();