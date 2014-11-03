<?php
/**
 * Class Optin_Monster_Provider_Mailpoet
 *
 * Handles the interaction between iContact and OptinMonster
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_Mailpoet extends Optin_Monster_Provider {

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
    public $provider = 'mailpoet';

    /**
     * Holds the MailPoet API instance.
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
     */
    public function authenticate( $args = array(), $optin_id ) {

        $providers                                           = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                              = uniqid();
        $providers[ $this->provider ][ $uniqid ]['label']    = 'MailPoet';
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

        // Get lists from iContact
        try {
            $this->api = WYSIJA::get( 'list', 'model' );
            $lists = $this->api->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );
        } catch ( Exception $e ) {
            return $this->error( 'list-error', __( 'There was an error retrieveing your lists.', 'optin-monster' ) );
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
                    foreach ( $lists as $list ) {
                        $output .= '<option value="' . $list['list_id'] . '"' . selected( $list_id, $list['list_id'], false ) . '>' . $list['name'] . '</option>';
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

        $user = array(
            'email' => $lead['lead_email'],
        );

        // Setup name if set
        if ( isset( $lead['lead_name'] ) && 'false' != $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $user['firstname'] = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $user['lastname'] = $names[1];
            }
        }

        $data = array(
            'user' => $user,
            'user_list' => array( 'list_ids' => array( $list_id ) ),
        );
        $data = apply_filters( 'optin_monster_pre_optin_mailpoet', $data, $lead, $list_id, null );

        $userHelper = WYSIJA::get( 'user', 'helper' );
        $userHelper->addSubscriber( $data );

        return true;

    }

}