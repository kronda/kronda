<?php
/**
 * Class Optin_Monster_Provider_SendinBlue
 *
 * Handles the interaction between SendinBlue and OptinMonster
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_SendinBlue extends Optin_Monster_Provider {

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
    public $provider = 'sendinblue';

    /**
     * Holds the SendinBlue API instance.
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

        // Load the SendinBlue API.
        if ( ! class_exists( 'Mailin' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/sendinblue/mailin.php';
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
     * @return  string|object   Output of the email lists or WP_Error
     */
    public function authenticate( $args = array(), $optin_id ) {

        // Instantiate the API
        $this->api = new Mailin( 'https://api.sendinblue.com/v1.0', $args['om-api-key'], $args['om-secret-key'] );

        // Make sure we can communicate with SendinBlue
        $test_call = $this->api->get_account();

        if ( ! $test_call ) {
            return $this->error( 'auth-error', __( 'There was a problem with your credentials.', 'optin-monster' ) );
        }

        $providers                                             = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                                = uniqid();
        $providers[ $this->provider ][ $uniqid ]['access_key'] = trim( $args['om-api-key'] );
        $providers[ $this->provider ][ $uniqid ]['secret_key'] = trim( $args['om-secret-key'] );
        $providers[ $this->provider ][ $uniqid ]['label']      = trim( strip_tags( $args['om-account-label'] ) );
        update_option( 'optin_monster_providers', $providers );

        // Store the account reference in the optin data.
        $this->save_account( $optin_id, $this->provider, $uniqid );

        // Return all of the list output from SendinBlue.
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
     * @return  string|object   Output of the email lists or WP_Error
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        // Instantiate the API if needed
        if ( ! $this->api ) {
            $this->api = new Mailin( 'https://api.sendinblue.com/v1.0', $args['access_key'], $args['secret_key'] );
        }

        // Get lists from SendinBlue
        $lists = $this->api->get_lists();

        if ( null == $lists ) {
            return $this->error( 'list-error', __( 'There was a problem retrieving your lists. Please check your API credentials.', 'optin-monster' ) );
        } else {
            return $this->build_list_html( $lists['data'], $list_id );
        }

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
                        $output .= '<option value="' . $list['id'] . '"' . selected( $list_id, $list['id'], false ) . '>' . $list['name'] . '</option>';
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

        // Instantiate the API
        $this->api = new Mailin( 'https://api.sendinblue.com/v1.0', $account['access_key'], $account['secret_key'] );

        // Setup the data to be sent
        $data = array(
            'email' => $lead['lead_email'],
        );

        // Set name if provided
        if ( $lead['lead_name'] && 'false' !== $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $data['NAME'] = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $data['SURNAME'] = $names[1];
            }
        }

        $data = apply_filters( 'optin_monster_pre_optin_sendinblue', $data, $lead, $list_id, $this->api );

        // Done with 'email'
        $lead['lead_email'] = $data['email'];
        unset( $data['email'] );

        // Set a couple more attributes for the request
        $blacklisted   = 0;
        $listid_unlink = array();

        // Send the lead to SendinBlue
        $response = $this->api->create_update_user( $lead['lead_email'], $data, $blacklisted, (array) $list_id, $listid_unlink );

        if ( 'success' == $response['code'] ) {
            return true;
        } else {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to SendinBlue. %s', 'optin-monster' ),
                    $response['message']
                )
            );
        }

    }

}