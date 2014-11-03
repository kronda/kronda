<?php
/**
 * Class Optin_Monster_Provider_Customerio.
 *
 * Handles the interaction between Customer.io and OptinMonster.
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_Customerio extends Optin_Monster_Provider {

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
    public $provider = 'customerio';

    /**
     * Holds the Customer.io API instance.
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

        $auth = array(
            'site_id' => $args['om-site-id'],
            'api_key' => $args['om-api-key'],
        );

        // Make sure we can communicate with Customer.io
        try {
            $this->api = $this->customerio_api( null, $auth, 'auth' );
        } catch ( Exception $e ) {
            $message = $e->getMessage();
            return $this->error( 'auth-error',
                __( 'There was a problem authenticating with Customer.io.', 'optin-monster' )
            );
        }

        // Save the integration data.
        $providers                                        = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                           = uniqid();
        $providers[ $this->provider ][ $uniqid ]['api']   = trim( $args['om-api-key'] );
        $providers[ $this->provider ][ $uniqid ]['site']  = trim( $args['om-site-id'] );
        $providers[ $this->provider ][ $uniqid ]['label'] = trim( strip_tags( $args['om-account-label'] ) );
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
     * @return  string Output of the email lists
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        $output = '<div class="optin-monster-field-box optin-monster-provider-lists optin-monster-clear">';
            $output .= '<p class="optin-monster-field-wrap">';
                $output .= __( '<strong>This email provider does not use lists.</strong> Customer.io works by segmenting your customers based on data passed when the customer is created or updated. By default, any customer added to your Customer.io account through OptinMonster will be prefixed with "om-" and have the "optinmonster" attribute available for segmentation.', 'optin-monster' );
            $output .= '</p>';
        $output .= '</div>';

        return $output;

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
    protected function build_list_html( $lists, $list_id = '', $optin_id = '' ) {
        // TODO: Implement build_list_html() method.
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

        $auth = array(
            'site_id' => $account['site'],
            'api_key' => $account['api'],
        );


        try {
            // Setup the data to be passed
            $prefix      = apply_filters( 'optin_monster_provider_id_prefix_customerio', 'om-' );
            $customer_id = uniqid( $prefix );
            $data        = array(
                'email'        => $lead['lead_email'],
                'optinmonster' => true,
            );

            // Add name to the array if entered
            if ( isset( $lead['lead_name'] ) ) {
                $data['name'] = $lead['lead_name'];
            }

            $data = apply_filters( 'optin_monster_pre_optin_customerio', $data, $lead, $list_id, null );

            // Send the data to Customer.io
            $request = $this->customerio_api( $customer_id, $auth, 'identify', $data );
        } catch ( Exception $e ) {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to Customer.io. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        return true;

    }

    /**
     * Quick n dirty wrapper for Customer.io
     *
     * @param int    $customer_id The dynamically created customer identifier
     * @param array  $auth        Authorization details
     * @param string $action      API action. Can be 'identify', 'event', 'delete', or 'auth'
     * @param array  $data        Any data that should be passed to Customer.io
     *
     * @return stdClass The response from Customer.io
     * @throws Exception
     */
    protected function customerio_api( $customer_id, $auth = array(), $action = 'identify', $data = array() ) {

        $session = curl_init();

        // Set method and url based on the requested action
        switch ( $action ) {
            case 'identify' :
                $method         = 'PUT';
                $customerio_url = 'https://track.customer.io/api/v1/customers/' . $customer_id;
                break;
            case 'event' :
                $method         = 'POST';
                $customerio_url = 'https://track.customer.io/api/v1/customers/' . $customer_id . '/events';
                break;
            case 'delete' :
                $method         = 'DELETE';
                $customerio_url = 'https://track.customer.io/api/v1/customers/' . $customer_id;
                break;
            case 'auth' :
                $method         = 'GET';
                $customerio_url = 'https://track.customer.io/auth';
        }

        curl_setopt( $session, CURLOPT_URL, $customerio_url );
        curl_setopt( $session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );

        if ( 'identify' == $action ) {
            curl_setopt( $session, CURLOPT_HTTPGET, 1 );
        }

        curl_setopt( $session, CURLOPT_HEADER, false );
        curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $session, CURLOPT_CUSTOMREQUEST, $method );
        curl_setopt( $session, CURLOPT_VERBOSE, 1 );
        curl_setopt( $session, CURLOPT_POSTFIELDS, http_build_query( $data, '', '&' ) );

        curl_setopt( $session, CURLOPT_USERPWD, $auth['site_id'] . ":" . $auth['api_key'] );

        curl_setopt( $session, CURLOPT_SSL_VERIFYPEER, false );

        $res = curl_exec( $session );

        $code = curl_getinfo( $session, CURLINFO_HTTP_CODE );
        curl_close( $session );

        switch ( (int) $code ) {
            case 200 :
                return json_decode( $res );
                break;
            case 400 :
                throw new Exception( 'Missing required parameter' );
                break;
            case 401 :
                throw new Exception( 'Invalid credentials' );
                break;
            case 404 :
                throw new Exception( 'Requested item does not exist' );
                break;
            case 500 :
            case 502 :
            case 503 :
            case 504 :
                throw new Exception( 'There is a problem with Customer.io' );
                break;
        }

    }

}