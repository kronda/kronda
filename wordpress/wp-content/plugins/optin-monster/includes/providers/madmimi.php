<?php
/**
 * Class Optin_Monster_Provider_MadMimi
 *
 * Handles the interaction between MadMimi and OptinMonster
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_MadMimi extends Optin_Monster_Provider {

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
    public $provider = 'madmimi';

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

        // Load the Mad Mimi API.
        if ( ! class_exists( 'MadMimi' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/madmimi/madmimi.php';
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

        // Instantiate and save the API
        $this->api = new MadMimi( $args['om-email-address'], $args['om-api-key'] );

        libxml_use_internal_errors( true );

        // Make sure we can communicate with Mad Mimi.
        $test_call = simplexml_load_string( $this->api->Lists() );

        if ( ! $test_call ) {
            return $this->error( 'auth-error',
                __( 'Unable to authenticate with the Mad Mimi API. Please check your credentials.', 'optin-monster' )
            );
        }

        $providers                                           = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                              = uniqid();
        $providers[ $this->provider ][ $uniqid ]['username'] = trim( $args['om-email-address'] );
        $providers[ $this->provider ][ $uniqid ]['api']      = trim( $args['om-api-key'] );
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
     * @return  string|object Output of the email lists or WP_Error.
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        // Instantiate the API if needed
        if ( ! $this->api ) {
            $this->api = new MadMimi( $args['username'], $args['api'] );
        }

        libxml_use_internal_errors( true );
        // Get the account lists
        $response = simplexml_load_string( $this->api->Lists() );

        if ( ! $response ) {
            return $this->error( 'list-error', __( 'There was a problem retrieving your lists. Please check your API credentials.', 'optin-monster' ) );
        } else {
            return $this->build_list_html( $response, $list_id );
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
                    foreach ( $lists->list as $list ) {
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

        // Instantiate the API.
        $this->api = new MadMimi( $account['username'], $account['api'] );

        // Setup the data to be passed.
        $data = array(
            'email'    => $lead['lead_email'],
            'add_list' => $list_id
        );

        // Add name to the data if set.
        if ( $lead['lead_name'] && 'false' !== $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $data['firstName'] = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $data['lastName'] = $names[1];
            }
        }

        $data = apply_filters( 'optin_monster_pre_optin_madmimi', $data, $lead, $list_id, $this->api );

        // Send the lead to Mad Mimi.
        ob_start();
        $this->api->AddUser( $data, true );
        $request = ob_get_clean();

        return true;

    }

}