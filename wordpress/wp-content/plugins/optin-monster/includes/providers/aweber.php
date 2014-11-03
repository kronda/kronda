<?php
/**
 * AWeber provider class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Provider_AWeber extends Optin_Monster_Provider {

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
    public $provider = 'aweber';

    /**
     * Holds the AWeber API instance.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $api = false;

    /**
     * The AWeber access token
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $access_token;

    /**
     * The AWeber access token secret
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $access_token_secret;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Construct via the parent object.
        parent::__construct();

        // Load the AWeber API.
        if ( ! class_exists( 'AWeberAPI' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/aweber/aweber_api.php';
        }

    }

    /**
     * Authentication method for providers.
     *
     * @since 2.0.0
     *
     * @param array $args     Data submitted by the user to be passed for authentication.
     * @param int   $optin_id The optin ID being used.
     *
     * @return  string|object Output of the email lists or WP_Error.
     */
    public function authenticate( $args = array(), $optin_id ) {

        list( $auth_key, $auth_token, $req_key, $req_token, $oauth ) = explode( '|', $args['om-auth-code'] );

        $this->api                     = new AWeberAPI( $auth_key, $auth_token );
        $this->api->user->requestToken = $req_key;
        $this->api->user->tokenSecret  = $req_token;
        $this->api->user->verifier     = $oauth;

        // Retrieve an access token
        try {
            list( $this->access_token, $this->access_token_secret ) = $this->api->getAccessToken();
        } catch ( AWeberException $e ) {
            return $this->error( 'api-error', sprintf( __( 'Sorry, but AWeber was unable to verify your authorization token. AWeber gave the following response: <em>%s</em>', 'optin-monster' ),
                $e->getMessage()
            ) );
        }

        // Verify we can connect to AWeber
        try {
            $account = $this->api->getAccount();
        } catch ( AWeberException $e ) {
            return $this->error( 'api-error', sprintf( __( 'Sorry, but AWeber was unable to grant access to your account. AWeber gave the following response: <em>%s</em>', 'optin-monster' ),
                $e->getMessage()
            ) );
        }

        // Save the account data for future reference.
        $providers                                                = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                                   = uniqid();
        $providers[ $this->provider ][ $uniqid ]['auth_key']      = $auth_key;
        $providers[ $this->provider ][ $uniqid ]['auth_token']    = $auth_token;
        $providers[ $this->provider ][ $uniqid ]['access_token']  = $this->access_token;
        $providers[ $this->provider ][ $uniqid ]['access_secret'] = $this->access_token_secret;
        $providers[ $this->provider ][ $uniqid ]['label']         = trim( strip_tags( $args['om-account-label'] ) );
        update_option( 'optin_monster_providers', $providers );

        // Store the account reference in the optin data.
        $this->save_account( $optin_id, $this->provider, $uniqid );

        // Return all of the list output from AWeber.
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
            $this->api                 = new AWeberAPI( $args['auth_key'], $args['auth_token'] );
            $this->access_token        = $args['access_token'];
            $this->access_token_secret = $args['access_secret'];
        }

        // Make sure we can still connect to AWeber
        try {
            $account = $this->api->getAccount( $this->access_token, $this->access_token_secret );
            $lists   = $account->loadFromUrl( '/accounts/' . $account->id . '/lists' );
        } catch ( AWeberException $e ) {
            return $this->error( 'api-error', sprintf( __( 'Sorry, but AWeber was unable to grant access to your account. AWeber gave the following response: <em>%s</em>', 'optin-monster' ),
                $e->getMessage()
            ) );
        }

        return $this->build_list_html( $lists->data['entries'], $list_id );

    }

    /**
     * Method for building out the list selection HTML.
     *
     * @since 2.0.0
     *
     * @param array  $lists   Lists for the email provider.
     * @param string $list_id The list identifier
     * @param string $optin_id The current optin ID
     *
     * @return string $html HTML string for selecting lists.
     */
    public function build_list_html( $lists, $list_id = '', $optin_id = '' ) {

        $output = '<div class="optin-monster-field-box optin-monster-provider-lists optin-monster-clear">';
            $output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-provider-list">' . __( 'Email provider list', 'optin-monster' ) . '</label><br />';
                $output .= '<select id="optin-monster-provider-list" name="optin_monster[provider_list]">';
                    foreach ( $lists as $offset => $data ) {
                        $output .= '<option value="' . $data['id'] . '"' . selected( $list_id, $data['id'], false ) . '>' . $data['name'] . '</option>';
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

        // Instantiate the AWeber API.
        $this->api = new AWeberAPI( $account['auth_key'], $account['auth_token'] );

        // Setup the data to be passed to AWeber.
        $data = array(
            'email' => $lead['lead_email'],
        );
        if ( $lead['lead_name'] && 'false' !== $lead['lead_name'] ) {
            $data['name'] = $lead['lead_name'];
        }
        $data = apply_filters( 'optin_monster_pre_optin_aweber', $data, $lead, $list_id, $this->api );

        // Send the lead to AWeber.
        try {
            $acct       = $this->api->getAccount( $account['access_token'], $account['access_secret'] );
            $subscriber = $this->lean_create( $acct->id, $list_id, $data );
            if ( ! $subscriber ) {
                return $this->error( 'optin-error', __( 'There was an error adding the contact to AWeber. Please try again.', 'optin-monster' ) );
            }
        } catch ( AWeberAPIException $e ) {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data. AWeber returned the following message: %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        return true;

    }

    /**
     * Custom create implementation to reduce overhead of adding a subscriber
     * to AWeber.
     *
     * @since 2.0.3.1
     *
     * @param int  $account_id The account ID to target.
     * @param string $list_id  The list identifier.
     * @param array  $data     The lead information. Should be sanitized.
     * @return bool            True on successful optin, false otherwise.
     */
    protected function lean_create( $account_id, $list_id, $data ) {

        // Prepare variables.
        $url  = '/accounts/' . $account_id . '/lists/' . $list_id . '/subscribers';
        $data = array_merge( array( 'ws.op' => 'create' ), $data );

        // Make the lean request.
        $ret  = $this->api->adapter->request( 'POST', $url, $data, array( 'return' => 'headers' ) );

        // If we receive a proper response, the request succeeded.
        if ( is_array( $ret ) && isset( $ret['Status-Code'] ) && 201 == $ret['Status-Code'] ) {
            return true;
        }

        // Otherwise, the request failed.
        return false;

    }

}