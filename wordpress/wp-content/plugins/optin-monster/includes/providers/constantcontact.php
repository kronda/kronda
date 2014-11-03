<?php
/**
 * Class Optin_Monster_Provider_ConstantContact.
 *
 * Handles the interaction between Constant Contact and OptinMonster.
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_ConstantContact extends Optin_Monster_Provider {

    /**
     * Path to the file.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Slug of the provider.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $provider = 'constant-contact';

    /**
     * Holds the Constant Contact API instance.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $api = false;

    /**
     * Holds the global API key for OptinMonster.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $api_key = 'vsch38zpe6hm4wfb59t79nzh';

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Construct via the parent object.
        parent::__construct();

    }

    /**
     * Authentication method for providers.
     *
     * @since 2.0.0
     *
     * @param array $args     Args to be passed for authentication.
     * @param int   $optin_id The optin ID to target.
     *
     * @return string The client & list dropdown HTML
     */
    public function authenticate( $args = array(), $optin_id ) {

        $this->api['access']  = $args['om-access-token'];
        $this->api['expires'] = time() + $args['om-expires-in'];

        // Save the account data for future reference.
        $providers                                          = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                             = uniqid();
        $providers[ $this->provider ][ $uniqid ]['token']   = $this->api['access'];
        $providers[ $this->provider ][ $uniqid ]['expires'] = $this->api['expires'];
        $providers[ $this->provider ][ $uniqid ]['label']   = trim( strip_tags( $args['om-account-label'] ) );
        update_option( 'optin_monster_providers', $providers );

        // Store the account reference in the optin data.
        $this->save_account( $optin_id, $this->provider, $uniqid );

        return $this->get_lists();

    }

    /**
     * Retrieval method for getting lists.
     *
     * @since 2.0.0
     *
     * @param array  $args     Args to be passed for list retrieval.
     * @param string $list_id  The list ID to check for selection.
     * @param string $uniqid   The account ID to target.
     * @param string $optin_id The current optin ID
     *
     * @return  string|WP_Error Output of the email lists or WP_Error.
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        if ( ! $this->api ) {
            if ( isset( $args['token'] ) ) {
                $this->api['access']  = $args['token'];
            }

            if ( isset( $args['expires'] ) ) {
                $this->api['expires'] = $args['expires'];
            }
        }

        $request = wp_remote_get( 'https://api.constantcontact.com/v2/lists?api_key=' . $this->api_key . '&access_token=' . $this->api['access'] );
        $lists   = json_decode( $request['body'] );

        return $this->build_list_html( $lists, $list_id );

    }

    /**
     * Method for building out the list selection HTML.
     *
     * @since 2.0.0
     *
     * @param array  $lists    Lists for the email provider.
     * @param string $list_id  The list identifier
     * @param string $optin_id The current optin ID
     *
     * @return string $html HTML string for selecting lists.
     */
    public function build_list_html( $lists, $list_id = '', $optin_id = '' ) {

        $output = '<div class="optin-monster-field-box optin-monster-provider-lists optin-monster-clear">';
            $output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-provider-list">' . __( 'Email provider list', 'optin-monster' ) . '</label><br />';
                $output .= '<select id="optin-monster-provider-list" name="optin_monster[provider_list]">';
                    foreach ( $lists as $list ) {
                        $output .= '<option value="' . $list->id . '"' . selected( $list_id, $list->id, false ) . '>' . $list->name . '</option>';
                    }
                $output .= '</select>';
            $output .= '</p>';
        $output .= '</div>';

        return $output;

    }

    /**
     * Method for opting into the email service provider.
     *
     * @since 2.0.0
     *
     * @param array  $account Args to be passed when opting in.
     * @param string $list_id The list identifier.
     * @param array  $lead    The lead information. Should be sanitized.
     *
     * @return bool|WP_Error True on successful optin.
     */
    public function optin( $account = array(), $list_id, $lead ) {

        // Check to see if the lead already exists in Constant Contact.
        $response = wp_remote_get( 'https://api.constantcontact.com/v2/contacts?api_key=' . $this->api_key . '&access_token=' . $account['token'] . '&email=' . $lead['lead_email'] );
        $contact  = json_decode( wp_remote_retrieve_body( $response ) );

        // Return early if there was a problem.
        if ( isset( $contact->error_key ) ) {
            return $this->error( 'api-error', sprintf( __( 'Sorry, but Constant Contact was unable to grant access to your account. The following error key was returned: <em>%s</em>.', 'optin-monster' ),
                $contact->error_key
            ) );
        }

        // If we have a previous contact, only update the list association.
        if ( ! empty( $contact->results ) ) {
            $args = array();
            $data = $contact->results[0];
            
            // Check if they are already assigned to lists.
            if ( ! empty( $data->lists ) ) {
	            foreach ( $data->lists as $i => $list ) {
	            	// If they are already assigned to this list, return early.
		            if ( isset( $list->id ) && $list_id == $list->id ) {
			            return true;
		            }
	            }
	            
	            // Otherwise, add them to the list.
	            $new_list 		  					= new stdClass;
	            $new_list->id 	  					= $list_id;
	            $new_list->status 				    = 'ACTIVE';
	            $data->lists[count( $data->lists )] = $new_list;
            } else {
            	// Add the contact to the list.
	            $data->lists      = array();
	            $new_list 		  = new stdClass;
	            $new_list->id 	  = $list_id;
	            $new_list->status = 'ACTIVE';
	            $data->lists[0]   = $new_list;
            }

            $data = apply_filters( 'optin_monster_pre_optin_constant-contact', $data, $lead, $list_id, null );

            $args['body']                      = json_encode( $data );
            $args['method']                    = 'PUT';
            $args['headers']['Content-Type']   = 'application/json';
            $args['headers']['Content-Length'] = strlen( $args['body'] );
            $args                              = apply_filters( 'optin_monster_do_optinmonster_args_contant-contact', $args );
            $update                            = wp_remote_request( 'https://api.constantcontact.com/v2/contacts/' . $contact->results[0]->id . '?api_key=' . $this->api_key . '&access_token=' . $account['token'] . '&action_by=ACTION_BY_VISITOR', $args );
            $res                               = json_decode( wp_remote_retrieve_body( $update ) );

            if ( isset( $res->error_key ) ) {
                return $this->error( 'api-error', sprintf( __( 'Sorry, but Constant Contact was unable to save your entry. The following error key was returned: <em>%s</em>.', 'optin-monster' ),
                    $res->error_key
                ) );
            } else {
                return true;
            }
        } else { // Setup data to add a new contact.
            $args                                         = $data = array();
            $data['email_addresses']                      = array();
            $data['email_addresses'][0]['id']             = $list_id;
            $data['email_addresses'][0]['status']         = 'ACTIVE';
            $data['email_addresses'][0]['confirm_status'] = 'CONFIRMED';
            $data['email_addresses'][0]['email_address']  = $lead['lead_email'];
            $data['lists']                                = array();
            $data['lists'][0]['id']                       = $list_id;
            if ( $lead['lead_name'] && 'false' !== $lead['lead_name'] ) {
                $names = explode( ' ', $lead['lead_name'] );
                if ( isset( $names[0] ) ) {
                    $data['first_name'] = $names[0];
                }
                if ( isset( $names[1] ) ) {
                    $data['last_name'] = $names[1];
                }
            }

            $data = apply_filters( 'optin_monster_pre_optin_constant-contact', $data, $lead, $list_id, null );

            $args['body']                      = json_encode( $data );
            $args['headers']['Content-Type']   = 'application/json';
            $args['headers']['Content-Length'] = strlen( json_encode( $data ) );
            $args                              = apply_filters( 'optin_monster_do_optinmonster_args_contant-contact', $args );
            $create                            = wp_remote_post( 'https://api.constantcontact.com/v2/contacts?api_key=' . $this->api_key . '&access_token=' . $account['token'] . '&action_by=ACTION_BY_VISITOR', $args );

            if ( isset( $create->error_key ) ) {
                return $this->error( 'api-error', sprintf( __( 'Sorry, but Constant Contact was unable to save your entry. The following error key was returned: <em>%s</em>.', 'optin-monster' ),
                    $create->error_key
                ) );
            } else {
                return true;
            }
        }

    }
}