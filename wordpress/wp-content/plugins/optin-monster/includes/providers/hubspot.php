<?php
/**
 * Class Optin_Monster_Provider_HubSpot
 *
 * Handles the interaction between HubSpot and OptinMonster
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_HubSpot extends Optin_Monster_Provider {

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
    public $provider = 'hubspot';

    /**
     * Holds the Mad Mimi API instance.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $api = false;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Construct via the parent object.
        parent::__construct();

        // Load the HubSpot API.
        if ( ! class_exists( 'HubSpot_Lists' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/hubspot/class.lists.php';
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/hubspot/class.leads.php';
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/hubspot/class.contacts.php';
        }

    }

    /**
     * Authentication method for providers.
     *
     * @since 2.0.0
     *
     * @param array $args     Args to be passed for authentication.
     * @param int   $optin_id The optin ID to target.
     *
     * @return string The list dropdown HTML
     */
    public function authenticate( $args = array(), $optin_id ) {

        $this->api['portalid'] = $args['om-hubspot-portalid'];
        $this->api['access']   = $args['om-hubspot-access-token'];
        $this->api['refresh']  = $args['om-hubspot-refresh-token'];
        $this->api['expires']  = time() + (int) $args['om-hubspot-expires-in'];

        $providers                                           = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                              = uniqid();
        $providers[ $this->provider ][ $uniqid ]['portalid'] = trim( $this->api['portalid'] );
        $providers[ $this->provider ][ $uniqid ]['access']   = trim( $this->api['access'] );
        $providers[ $this->provider ][ $uniqid ]['refresh']  = trim( $this->api['refresh'] );
        $providers[ $this->provider ][ $uniqid ]['expires']  = trim( $this->api['expires'] );
        $providers[ $this->provider ][ $uniqid ]['label']    = trim( strip_tags( $args['om-account-label'] ) );
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
            $this->api['portalid'] = $args['portalid'];
            $this->api['access']   = $args['access'];
            $this->api['refresh']  = $args['refresh'];
            $this->api['expires']  = $args['expires'];
        }

        // If needed, get a new access token
        if ( time() > $this->api['expires'] ) {
            $this->api['access'] = $this->refresh_access_token( $uniqid, $args, $this->api['refresh'] );
        }

        // Instantiate the list API.
        $list_api = new HubSpot_Lists( $this->api['access'] );

        // Get only static lists (dynamic lists cannot be updated manually).
        $static  = $list_api->get_static_lists( null );
        $lists   = (array) $static->lists;

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
                        $output .= '<option value="' . $list->listId . '"' . selected( $list_id, $list->listId, false ) . '>' . $list->name . '</option>';
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

        // If needed, get a new access token
        if ( time() > $account['expires'] ) {
            $account['access'] = $this->refresh_access_token( $account['account_id'], $account, $account['refresh'] );
        }

        // Instantiate the APIs
        $this->api['contacts'] = new HubSpot_Contacts( $account['access'] );
        $this->api['lists']    = new HubSpot_Lists( $account['access'] );

        // Setup the data to be passed
        $data = array(
            'email' => $lead['lead_email'],
        );

        // Add name if set
        if ( isset( $lead['lead_name'] ) && 'false' != $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $data['firstname'] = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $data['lastname'] = $names[1];
            }
        }

        $data = apply_filters( 'optin_monster_pre_optin_hubspot', $data, $lead, $list_id, $this->api );

        // Create or update the contact in HubSpot
        try {
            $contact = $this->api['contacts']->get_contact_by_email($data['email']);
            if ( property_exists( $contact, 'status' ) && 'error' == $contact->status ) {
                $contact = $this->api['contacts']->create_contact( $data );
            } else {
                $this->api['contacts']->update_contact( $contact->vid, $data );
            }
        } catch ( HubSpot_Exception $e ) {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to HubSpot. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        // Add the new contact to the specified list
        try {
            $list = $this->api['lists']->add_contacts_to_list( array( $contact->vid ), $list_id );
        } catch ( HubSpot_Exception $e ) {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to HubSpot. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        return true;

    }

    /**
     * Retrieves a new access token if needed.
     *
     * @since 2.0.0
     *
     * @param string $uniqid        The integration ID.
     * @param arrsy $args           The HubSpot account args.
     * @param string $refresh_token The refresh token associated with the integration.
     *
     * @return string|WP_Error
     */
    private function refresh_access_token( $uniqid, $args, $refresh_token ) {

        // Setup the request.
        $base_url = 'https://api.hubapi.com/auth/v1/refresh';
        $params   = array(
            'method' => 'POST',
            'body'   => array(
                'refresh_token' => $refresh_token,
                'client_id'     => '4c4b2343-fd6a-11e3-aead-bddfb3c95bea',
                'grant_type'    => 'refresh_token'
            )
        );

        // Perform the request.
        $result = wp_remote_post( $base_url, $params );

        // Return an error if there was a problem with the request.
        if ( is_wp_error( $result ) || ( $result && $result['response']['code'] == 401 ) ) {
            return $this->error( 'refresh-error', __( 'There was a problem accessing HubSpot. Please contact the site owner.', 'optin-monster' ) );
        }

        $body = json_decode( $result['body'] );

        // Save the new account tokens.
        $providers                                           = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $providers[ $this->provider ][ $uniqid ]['portalid'] = $args['portalid'];
        $providers[ $this->provider ][ $uniqid ]['label']    = $args['label'];
        $providers[ $this->provider ][ $uniqid ]['access']   = $body->access_token;
        $providers[ $this->provider ][ $uniqid ]['refresh']  = $body->refresh_token;
        $providers[ $this->provider ][ $uniqid ]['expires']  = time() + $body->expires_in;

        update_option( 'optin_monster_providers', $providers );

        // Return the new access token for immediate use.
        return $body->access_token;

    }

}