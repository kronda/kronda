<?php
/**
 * Class Optin_Monster_Provider_TotalSend
 *
 * Handles the interaction between TotalSend and OptinMonster
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_TotalSend extends Optin_Monster_Provider {

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
    public $provider = 'totalsend';

    /**
     * Holds the TotalSend API instance.
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

        // Load the TotalSend API.
        if ( ! class_exists( 'TotalSend' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/totalsend/totalsend.php';
        }

    }

    /**
     * Authentication method for providers.
     *
     * @since 2.0.0
     *
     * @param array  $args     Args to be passed for authentication.
     * @param int    $optin_id The optin ID to target.
     * @param string $uniqid   The account ID to target.
     *
     * @return  string|object Output of the email lists or WP_Error
     */
    public function authenticate( $args = array(), $optin_id ) {

        // Instantiate the API.
        $this->api = new TotalSend( $args['om-email-address'], $args['om-password'] );

        // Make sure we can communicate with TotalSend.
        $test_connection = $this->api->getConnection();

        if ( false == $test_connection['Success'] ) {
            return $this->error( 'api-error',
                sprintf( __( 'Cannot authenticate. TotalSend returned the following error: <em>%s</em>.', 'optin-monster' ),
                    $test_connection['ErrorText'][0] )
            );
        }

        $providers                                           = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                              = uniqid();
        $providers[ $this->provider ][ $uniqid ]['email']    = trim( $args['om-email-address'] );
        $providers[ $this->provider ][ $uniqid ]['password'] = trim( $args['om-password'] );
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
     * @return  string|object   Output of the email lists or WP_Error
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        // Instantiate the API if needed
        if ( ! $this->api ) {
            $this->api = new TotalSend( $args['email'], $args['password'] );
        }

        // Get lists from TotalSend
        $lists = $this->api->getSubscriberLists();

        if ( false == $lists['Success'] ) {
            return $this->error( 'list-error',
                sprintf( __( 'Could not retrieve your lists. TotalSend returned the following error: <em>%s</em>.', 'optin-monster' ),
                    $lists['ErrorText'][0] )
            );
        }

        return $this->build_list_html( $lists['Lists'], $list_id );

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
                        $output .= '<option value="' . $list['ListID'] . '"' . selected( $list_id, $list['ListID'], false ) . '>' . $list['Name'] . '</option>';
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
        $this->api = new TotalSend( $account['email'], $account['password'] );

        // Set up the name fields
        $field_names = array(
            'first_name' => 'First Name',
            'last_name'  => 'Last Name',
        );
        $field_names = apply_filters( 'optin_monster_provider_totalsend_custom_fields', $field_names );

        // Get the custom fields from TotalSend
        $fields = $this->api->getSubscriberListFields( $list_id );

        // Save the field IDs
        foreach ( $fields as $field ) {
            switch ( $field['FieldName'] ) {
                case $field_names['first_name'] :
                    $fname_key = $field['CustomFieldID'];
                    break;
                case $field_names['last_name'] :
                    $lname_key = $field['CustomFieldID'];
                    break;
            }
        }

        $first_name = '';
        $last_name  = '';
        if ( isset( $lead['lead_name'] ) && 'false' != $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $first_name = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $last_name = $names[1];
            }
        }

        // Setup data to be passed
        $data = array(
            'email' => $lead['lead_email'],
        );

        // Set first name if it exists
        if ( isset( $first_name ) ) {
            $data[ $fname_key ] = $first_name;
        }

        // Set last name if it exists
        if ( isset( $last_name ) ) {
            $data[ $lname_key ] = $last_name;
        }

        $data = apply_filters( 'optin_monster_pre_optin_totalsend', $data, $lead, $list_id, $this->api );

        $this->api->setEmail( $data['email'] );
        unset( $data['email'] ); // Done with 'email'

        $this->api->setCustomFields( $data );

        ob_start();
        $this->api->subscribe();
        $subscribe = ob_get_clean();

        if ( $subscribe['success'] == true ) {
            return true;
        } else {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to TotalSend. %s', 'optin-monster' ),
                    $subscribe['msg']
                )
            );
        }

    }
}