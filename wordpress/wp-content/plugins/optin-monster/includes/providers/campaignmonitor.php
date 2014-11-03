<?php
/**
 * Class Optin_Monster_Provider_CampaignMonitor.
 *
 * Handles the interaction between Campaign Monitor and OptinMonster.
 *
 * @package Optin_Monster
 * @author J. Aaron Eaton <aaron@awesomemotive.com>
 * @since 2.0.0
 */
class Optin_Monster_Provider_CampaignMonitor extends Optin_Monster_Provider {

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
    public $provider = 'campaign-monitor';

    /**
     * Holds the Campaign Monitor API instance.
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

        // Load the Campaign Monitor API.
        if ( ! class_exists( 'CS_REST_General' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/campaign-monitor/csrest_general.php';
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/campaign-monitor/csrest_clients.php';
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/campaign-monitor/csrest_lists.php';
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/campaign-monitor/csrest_subscribers.php';
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
     * @return string The client & list dropdown HTML
     */
    public function authenticate( $args = array(), $optin_id ) {

        $this->api['access']  = urldecode( $args['om-access-token'] );
        $this->api['refresh'] = urldecode( $args['om-refresh-token'] );
        $this->api['expires'] = time() + (int) $args['om-expires-in'];

        $providers                                          = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                             = uniqid();
        $providers[ $this->provider ][ $uniqid ]['access']  = trim( $this->api['access'] );
        $providers[ $this->provider ][ $uniqid ]['refresh'] = trim( $this->api['refresh'] );
        $providers[ $this->provider ][ $uniqid ]['expires'] = trim( $this->api['expires'] );
        $providers[ $this->provider ][ $uniqid ]['label']   = trim( strip_tags( $args['om-account-label'] ) );
        update_option( 'optin_monster_providers', $providers );

        // Store the account reference in the optin data.
        $this->save_account( $optin_id, $this->provider, $uniqid );

        return $this->get_clients();

    }

    /**
     * Retrieval method for getting clients.
     *
     * @since 2.0.0
     *
     * @param array  $args      Args to be passed for list retrieval.
     * @param string $client_id The client ID to check for selection.
     * @param string $list_id   The list ID to target.
     * @param string $uniqid    The account ID to target.
     * @param string $optin_id  The optin ID to target.
     *
     * @return  string|WP_Error Output of the clients or WP_Error.
     */
    public function get_clients( $args = array(), $client_id = '', $list_id = '', $uniqid = '', $optin_id = '' ) {

        if ( ! $this->api ) {
            if ( isset( $args['access'] ) && isset( $args['refresh'] ) && isset( $args['expires'] ) ) {
                $this->api['access']  = $args['access'];
                $this->api['refresh'] = $args['refresh'];
                $this->api['expires'] = $args['expires'];
            }
        }

        // If needed, get a new access token.
        if ( isset( $this->api['expires'] ) && isset( $this->api['refresh'] ) && time() > $this->api['expires'] ) {
            $this->api['access'] = $this->refresh_access_token( $uniqid, $this->api['refresh'] );
        }

        // Possibly get data via OAuth or API key for backwards compat.
        if ( isset( $this->api['access'] ) && isset( $this->api['refresh'] ) ) {
            $auth = array(
                'access_token'  => $this->api['access'],
                'refresh_token' => $this->api['refresh'],
            );
        } else if ( isset( $args['api'] ) ) {
            $auth = array(
                'api_key' => $args['api']
            );
        } else {
            $auth = array();
        }

        $api     = new CS_REST_General( $auth );
        $clients = $api->get_clients();

        $output  = '';
        $output .= '<div class="optin-monster-field-box optin-monster-provider-clients optin-monster-clear">';
            $output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-client-list">' . __( 'Campaign Monitor Client', 'optin-monster' ) . '</label><br />';
                $output .= '<select id="optin-monster-client-list" name="optin_monster[provider_client]">';
                    foreach ( $clients->response as $client ) {
                        $output .= '<option value="' . $client->ClientID . '"' . selected( $client_id, $client->ClientID, false ) . '>' . $client->Name . '</option>';
                    }
                $output .= '</select>';
            $output .= '</p>';
        $output .= '</div>';

        // Get the lists for the first client.
        $this->api['client'] = ! empty( $client_id ) ? $client_id : $clients->response[0]->ClientID;
        $output .= $this->get_lists( $args, $list_id, $uniqid, $optin_id );

        return $output;

    }

    /**
     * Retrieval method for getting lists.
     *
     * @since 2.0.0
     *
     * @param array  $args    Args to be passed for list retrieval.
     * @param string $list_id The list ID to check for selection.
     * @param string $uniqid  The account ID to target.
     * @param string $optin_id The current optin ID
     *
     * @return  string|WP_Error Output of the email lists or WP_Error.
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        if ( ! $this->api ) {
            if ( isset( $args['access'] ) && isset( $args['refresh'] ) && isset( $args['expires'] ) ) {
                $this->api['access']  = $args['access'];
                $this->api['refresh'] = $args['refresh'];
                $this->api['expires'] = $args['expires'];
            }

            if ( isset( $args['client'] ) ) {
                $this->api['client'] = $args['client'];
            }
        }

        // If needed, get a new access token.
        if ( isset( $this->api['expires'] ) && isset( $this->api['refresh'] ) && time() > $this->api['expires'] ) {
            $this->api['access'] = $this->refresh_access_token( $uniqid, $this->api['refresh'] );
        }

        // Possibly get data via OAuth or API key for backwards compat.
        if ( isset( $this->api['access'] ) && isset( $this->api['refresh'] ) ) {
            $auth = array(
                'access_token'  => $this->api['access'],
                'refresh_token' => $this->api['refresh'],
            );
        } else if ( isset( $args['api'] ) ) {
            $auth = array(
                'api_key' => $args['api']
            );
        } else {
            $auth = array();
        }

        $api   = new CS_REST_Clients( $this->api['client'], $auth );
        $lists = $api->get_lists();

        return $this->build_list_html( $lists, $list_id, $optin_id );

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
                    foreach ( $lists->response as $list ) {
                        $output .= '<option value="' . $list->ListID . '"' . selected( $list_id, $list->ListID, false ) . '>' . $list->Name . '</option>';
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

        // If needed, get a new access token.
        if ( isset( $account['expires'] ) && isset( $account['refresh'] ) && time() > $account['expires'] ) {
            $account['access'] = $this->refresh_access_token( $account['account_id'], $account['refresh'] );
        }

        // Get connected to Campaign Monitor.
        if ( isset( $account['access'] ) && isset( $account['refresh'] ) ) {
            $auth = array(
                'access_token'  => $account['access'],
                'refresh_token' => $account['refresh'],
            );
        } else if ( isset( $account['api'] ) ) {
            $auth = array(
                'api_key' => $account['api']
            );
        } else {
            $auth = array();
        }
        $this->api = new CS_Rest_Subscribers( $list_id, $auth );

        // Setup the data to be passed.
        $data = array(
            'EmailAddress' => $lead['lead_email'],
            'Resubscribe'  => true,
            'CustomFields' => array(
                array(
                    'Key'   => 'OptinMonster',
                    'Value' => true,
                ),
            ),
        );

        // Add name to the array if entered.
        if ( $lead['lead_name'] && 'false' !== $lead['lead_name'] ) {
            $data['Name'] = $lead['lead_name'];
        }

        $data = apply_filters( 'optin_monster_pre_optin_campaign-monitor', $data, $lead, $list_id, $this->api );

        // Send the new lead to Campaign Monitor.
        $result = $this->api->add( $data );

        // Return true if successful, else return an error.
        if ( $result->was_successful() ) {
            return true;
        } else {
            return $this->error( 'optin-error',
                __( 'There was an error saving the data to Campaign Monitor', 'optin-monster' )
            );
        }

    }

    /**
     * Retrieves a new access token if needed
     *
     * @since 2.0.0
     *
     * @param string $uniqid        The integration ID
     * @param string $refresh_token The refresh token associated with the integration
     *
     * @return string|WP_Error
     */
    private function refresh_access_token( $uniqid, $refresh_token ) {

        // Setup the request.
        $base_url = 'https://api.createsend.com/oauth/token';
        $params   = array(
            'method' => 'POST',
            'body'   => array(
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refresh_token,
            )
        );

        // Perform the request.
        $result = wp_remote_post( $base_url, $params );

        // Return an error if there was a problem with the request.
        if ( is_wp_error( $result ) || ( $result && $result['response']['code'] == 401 ) ) {
            return $this->error( 'refresh-error', __( 'There was a problem accessing Campaign Monitor. Please contact the site owner.', 'optin-monster' ) );
        }

        $body = json_decode( $result['body'] );

        // Save the new account tokens.
        $providers                                          = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $providers[ $this->provider ][ $uniqid ]['access']  = $body->access_token;
        $providers[ $this->provider ][ $uniqid ]['refresh'] = $body->refresh_token;
        $providers[ $this->provider ][ $uniqid ]['expires'] = time() + $body->expires_in;

        update_option( 'optin_monster_providers', $providers );

        // Return the new access token for immediate use.
        return $body->access_token;

    }
}