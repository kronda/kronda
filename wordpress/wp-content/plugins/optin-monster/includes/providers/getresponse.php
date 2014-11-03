<?php
/**
 * Class Optin_Monster_Provider_GetResponse.
 *
 * Handles the interaction between GetResponse and OptinMonster.
 *
 * @package Optin_Monster
 * @author J. Aaron Eaton <aaron@awesomemotive.com>
 * @since 2.0.0
 */
class Optin_Monster_Provider_GetResponse extends Optin_Monster_Provider {

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
    public $provider = 'getresponse';

    /**
     * Holds the GetResponse API instance.
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

        // Load the GetResponse API.
        if ( ! class_exists( 'GetResponse' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/getresponse/getresponse.php';
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
        $this->api = new GetResponse( $args['om-api-key'] );

        // Make sure we can communicate with GetResponse
        $ping = $this->api->ping();

        if ( ! $ping ) {
            return $this->error( 'api-error', __( 'There was an error connecting to the GetResponse API. Please check your credentials.', 'optin-monster' ) );
        }

        $providers                                        = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                           = uniqid();
        $providers[ $this->provider ][ $uniqid ]['api']   = trim( $args['om-api-key'] );
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
     * @return  string|object Output of the email lists or WP_Error
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        // Instantaite the API if needed
        if ( ! $this->api ) {
            $this->api = new GetResponse( $args['api'] );
        }

        // Get the lists (campaigns) from GetResponse
        $lists = $this->api->getCampaigns();

        // Return an error if something went wrong with the request
        if ( ! $lists ) {
            return $this->error( 'list-error', __( 'There was an error retrieving your lists from the GetResponse API. Please check your credentials.', 'optin-monster' ) );
        }

        return $this->build_list_html( (array) $lists, $list_id );

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
                    foreach ( $lists as $id => $data ) {
                        $output .= '<option value="' . $id . '"' . selected( $list_id, $id, false ) . '>' . $data->name . '</option>';
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
        $this->api = new GetResponse( $account['api'] );

        // Setup data to be passed.
        $data = array(
            'email' => $lead['lead_email'],
            'customs' => array(
                array(
                    'name' => 'optinmonster',
                    'content' => 'true',
                )
            ),
        );

        // Set name if provided.
        if ( ! empty( $lead['lead_name'] ) ) {
            $data['name'] = $lead['lead_name'];
        }

        $data = apply_filters( 'optin_monster_pre_optin_getresponse', $data, $lead, $list_id, $this->api );

        // Add the campaign to the data array
        $data['campaign'] = $list_id;

        // Send data to GetResponse.
        try {
            $response = $this->api->addContact( $data );
        } catch ( Exception $e ) {
            return $this->error( 'lead-error',
                sprintf(
                    __( 'GetResponse responded with the error code %s.', 'optin-monster' ),
                    $e->getCode()
                )
            );
        }

        return true;

    }

}