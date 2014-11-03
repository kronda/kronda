<?php
/**
 * Class Optin_Monster_Provider_iContact
 *
 * Handles the interaction between iContact and OptinMonster
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_iContact extends Optin_Monster_Provider {

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
    public $provider = 'icontact';

    /**
     * Holds the iContact API instance.
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

        // Load the iContact API.
        if ( ! class_exists( 'iContactApi' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/icontact/iContactApi.php';
        }

    }

    /**
     * Authentication method for providers.
     *
     * @since 2.0.0
     *
     * @param array $args     Args to be passed for authentication.
     * @param int   $optin_id The optin ID to target.
     */
    public function authenticate( $args = array(), $optin_id ) {

        $auth = array(
            'appId'       => $args['om-app-id'],
            'apiPassword' => $args['om-app-password'],
            'apiUsername' => $args['om-username'],
        );

        // Instantiate and authenticate with iContact.
        try {
            iContactApi::getInstance()->setConfig( $auth );
            $this->api = iContactApi::getInstance();
        } catch ( Exception $e ) {
            $errors = $this->api->getErrors();

            return $this->error( 'auth-error',
                sprintf( __( 'There was an error authenticating with iContact. The following error was returned: <em>%s</em>',
                    $errors[0]
                ) )
            );
        }

        $providers                                           = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                              = uniqid();
        $providers[ $this->provider ][ $uniqid ]['app_id']   = trim( $args['om-app-id'] );
        $providers[ $this->provider ][ $uniqid ]['app_pass'] = trim( $args['om-app-password'] );
        $providers[ $this->provider ][ $uniqid ]['username'] = trim( $args['om-username'] );
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

        // Instantiate the API if needed.
        if ( ! $this->api ) {
            $auth = array(
                'appId'       => $args['app_id'],
                'apiPassword' => $args['app_pass'],
                'apiUsername' => $args['username'],
            );

            iContactApi::getInstance()->setConfig( $auth );
            $this->api = iContactApi::getInstance();
        }

        // Get lists from iContact.
        try {
            $lists = $this->api->getLists();
        } catch ( Exception $e ) {
            $errors = $this->api->getErrors();
            return $this->error( 'list-error', sprintf( __( 'There was an error retrieving your lists. The following error was returned: <em>%s</em>', 'optin-monster' ), $errors[0] ) );
        }

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
    protected function build_list_html( $lists, $list_id = '', $optin_id = '' ) {

        $output = '<div class="optin-monster-field-box optin-monster-provider-lists optin-monster-clear">';
            $output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-provider-list">' . __( 'Email provider list', 'optin-monster' ) . '</label><br />';
                $output .= '<select id="optin-monster-provider-list" name="optin_monster[provider_list]">';
                    foreach ( $lists as $id => $data ) {
                        $output .= '<option value="' . $data->listId . '"' . selected( $list_id, $data->listId, false ) . '>' . $data->name . '</option>';
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
        iContactApi::getInstance()->setConfig( array(
            'appId'       => $account['app_id'],
            'apiPassword' => $account['app_pass'],
            'apiUsername' => $account['username']
        ) );
        $this->api = iContactApi::getInstance();

        // Setup the data to be passed.
        $data = array(
            'email' => $lead['lead_email'],
        );

        // Set name if provided.
        if ( $lead['lead_name'] && 'false' !== $lead['lead_name'] ) {
            $data['first_name'] = null;
            $data['last_name']  = null;
            $names              = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $data['first_name'] = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $data['last_name'] = $names[1];
            }
            $data = apply_filters( 'optin_monster_pre_optin_icontact', $data, $lead, $list_id, $this->api );
            $res  = $this->api->addContact( $data['email'], 'normal', null, $data['first_name'], $data['last_name'] );
        } else { // No name provided.
            $data = apply_filters( 'optin_monster_pre_optin_icontact', $data, $lead, $list_id, $this->api );
            $res  = $this->api->addContact( $data['email'] );
        }

        // Subscribe the contact to the list.
        $sub = $this->api->subscribeContactToList( $res->contactId, $list_id );

        return true;

    }

}