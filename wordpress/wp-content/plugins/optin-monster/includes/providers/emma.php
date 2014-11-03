<?php
/**
 * Class Optin_Monster_Provider_Emma.
 *
 * Handles the interaction between Emma and OptinMonster.
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_Emma extends Optin_Monster_Provider {

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
    public $provider = 'emma';

    /**
     * Holds the Emma API instance.
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

        // Load the Emma API.
        if ( ! class_exists( 'Emma' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/emma/Emma.php';
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

        $this->api = new Emma( $args['om-account-id'], $args['om-api-key'], $args['om-secret-key'] );

        // Make sure we can communicate to Emma
        try {
            $test = $this->api->myAccountSummary();
        } catch ( Exception $e ) {
            return $this->error( 'auth-error', __( 'There was an error authenticating with Emma. Please verify your API credentials.', 'optin-monster' ) );
        }

        // Save the integration info
        $providers                                          = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                             = uniqid();
        $providers[ $this->provider ][ $uniqid ]['api']     = trim( $args['om-api-key'] );
        $providers[ $this->provider ][ $uniqid ]['secret']  = trim( $args['om-secret-key'] );
        $providers[ $this->provider ][ $uniqid ]['account'] = trim( $args['om-account-id'] );
        $providers[ $this->provider ][ $uniqid ]['label']   = trim( strip_tags( $args['om-account-label'] ) );
        update_option( 'optin_monster_providers', $providers );

        // Store the account reference in the optin data.
        $this->save_account( $optin_id, $this->provider, $uniqid );

        // Return all of the list output from Emma.
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
     * @return  string|WP_Error Output of the clients or WP_Error.
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        // Create the API instance if needed
        if ( ! $this->api ) {
            $this->api = new Emma( $args['account'], $args['api'], $args['secret'] );
        }

        // Get the lists from Emma
        try {
            $lists = $this->api->myGroups( array( 'group_types' => 'all' ) );
        } catch ( Exception $e ) {
            return $this->error( 'list-error',
                sprintf(
                    __( 'There was an error retrieving your lists. Emma responded with the following error: <em>%s</em>', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        return $this->build_list_html( json_decode( $lists ) );

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

        $output = '<div class="optin-monster-field-box optin-monster-provider-lists optin-monster-clear">';
            $output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-provider-list">' . __( 'Email provider list', 'optin-monster' ) . '</label><br />';
                $output .= '<select id="optin-monster-provider-list" name="optin_monster[provider_list]">';
                    foreach ( $lists as $list ) {
                        $output .= '<option value="' . $list->member_group_id . '"' . selected( $list_id, $list->member_group_id, false ) . '>' . $list->group_name . '</option>';
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
        $this->api = new Emma( $account['account'], $account['api'], $account['secret'] );

        // Setup the data to be passed
        $data = array(
            'email'     => $lead['lead_email'],
            'group_ids' => array(
                $list_id,
            ),
            'fields'    => array(
                'optin_monster' => true,
            ),
        );

        // Add name to the array if entered
        if ( isset( $lead['lead_name'] ) && 'false' != $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $data['fields']['first_name'] = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $data['fields']['last_name'] = $names[1];
            }
        }

        $data = apply_filters( 'optin_monster_pre_optin_emma', $data, $lead, $list_id, $this->api );

        // Send the data to Emma
        try {
            $request = $this->api->membersAddSingle( $data );
        } catch ( Emma_Invalid_Response_Exception $e ) {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to Emma. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        return true;

    }

}