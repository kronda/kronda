<?php
/**
 * ActiveCampaign provider class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @author  Garry Gonzales
 */
class Optin_Monster_Provider_ActiveCampaign extends Optin_Monster_Provider {

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
    public $provider = 'activecampaign';

    /**
     * Holds the MailChimp API instance.
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

        // Load the ActiveCampaign API.
        if ( ! class_exists( 'ActiveCampaign' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/activecampaign/activecampaign.php';
        }

    }

    /**
     * Authentication method for providers.
     *
     * @since 2.0.0
     *
     * @param array $data     Data submitted by the user to be passed for authentication.
     * @param int   $optin_id The optin ID being used.
     *
     * @return string The list dropdown HTML
     */
    public function authenticate( $data = array(), $optin_id ) {

        // Call to ActiveCampaign.
        $this->api = new ActiveCampaign( $data['om-api-url'], $data['om-api-key'] );

        try {
            $this->api->api( 'user/verify' );
        } catch ( Exception $e ) {
            return $this->error( 'api-error', $e->getMessage() );
        }

        // Save the account data for future reference.
        $providers                                        = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                           = uniqid();
        $providers[ $this->provider ][ $uniqid ]['url']   = trim( $data['om-api-url'] );
        $providers[ $this->provider ][ $uniqid ]['api']   = trim( $data['om-api-key'] );
        $providers[ $this->provider ][ $uniqid ]['label'] = trim( strip_tags( $data['om-account-label'] ) );
        update_option( 'optin_monster_providers', $providers );

        // Store the account reference in the optin data.
        $this->save_account( $optin_id, $this->provider, $uniqid );

        return $this->get_lists( array( 'url' => $data['om-api-url'], 'api' => $data['om-api-key'] ) );

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
            // Call ActiveCampaign.
            $this->api = new ActiveCampaign( $args['url'], $args['api'] );
        }

        try {
            $lists = $this->api->api( 'list/list' );
        } catch ( Exception $e ) {
            return $this->error( 'list-error', $e->getMessage() );
        }

        return $this->build_list_html( $lists, $list_id, $optin_id );

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
                    foreach ( (array) $lists as $i => $list ) {
                        if ( ! empty( $list['id'] ) ) {
                            $output .= '<option value="' . $list['id'] . '"' . selected( $list_id, $list['id'], false ) . '>' . $list['name'] . '</option>';
                        }
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

        // Call to ActiveCampaign.
        $this->api = new ActiveCampaign( $account['url'], $account['api'] );

        // Setup data to be passed.
        $data = array(
            'email'             => $lead['lead_email'],
            'p'                 => array( $list_id ),
            'status'            => 1,
            'instantresponders' => array( 1 )
        );

        // Setup name if set.
        if ( isset( $lead['lead_name'] ) && 'false' != $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $data['first_name'] = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $data['last_name'] = $names[1];
            }
        }

        try {
            $response = $this->api->api( 'contact/add', $data );
        } catch( Exception $e ) {
             return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to ActiveCampaign. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        return true;

    }

}