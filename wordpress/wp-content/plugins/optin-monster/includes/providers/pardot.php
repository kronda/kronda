<?php
/**
 * Class Optin_Monster_Provider_Pardot
 *
 * Handles the interaction between Pardot and OptinMonster
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_Pardot extends Optin_Monster_Provider {

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
    public $provider = 'pardot';

    /**
     * Holds the Pardot API instance.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $api = false;

    /**
     * Stores the API key returned from Pardot
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $api_key = false;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Construct via the parent object.
        parent::__construct();

        // Load the Pardot API Wrapper.
        if ( ! class_exists( 'Pardot_OM_API' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/pardot/pardot.php';
        }

    }

    /**
     * Authentication method for providers.
     *
     * @since 2.0.0
     *
     * @param   array $args     Args to be passed for authentication.
     * @param   int   $optin_id The optin ID to target.
     *
     * @return  string|object       Output of email lists or WP_Error
     */
    public function authenticate( $args = array(), $optin_id ) {

        $api_data = array(
            'email'    => $args['om-email-address'],
            'password' => $args['om-password'],
            'user_key' => $args['om-user-key'],
        );

        // Instantiate the API.
        $this->api = new Pardot_OM_API();

        // Authenticate with Pardot.
        $this->api_key = $this->api->authenticate( $api_data );

        if ( $this->api->error ) {
            return $this->error( 'auth-error',
                sprintf( __( 'Sorry, but Pardot was unable to grant access to your account. Pardot gave this response: <em>%s</em>. Please check your login information.', 'optin-monster' ),
                    $this->api->error )
            );
        }

        $providers                                           = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                              = uniqid();
        $providers[ $this->provider ][ $uniqid ]['email']    = trim( $args['om-email-address'] );
        $providers[ $this->provider ][ $uniqid ]['password'] = trim( $args['om-password'] );
        $providers[ $this->provider ][ $uniqid ]['user_key'] = trim( $args['om-user-key'] );
        $providers[ $this->provider ][ $uniqid ]['api_key']  = trim( $this->api_key );
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
     * @param   array  $args     Args to be passed for list retrieval.
     * @param   string $list_id  The list ID to check for selection.
     * @param string   $uniqid   The account ID to target.
     * @param string   $optin_id The current optin ID
     *
     * @return  string|object   Output of the email lists or WP_Error
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        // Instantiate and authenticate with the API if needed.
        if ( ! $this->api ) {
            $api_data = array(
                'email'    => $args['email'],
                'password' => $args['password'],
                'user_key' => $args['user_key'],
            );

            $this->api = new Pardot_OM_API();

            if ( $this->api->error ) {
                return $this->error( 'auth-error',
                    sprintf( __( 'Sorry, but Pardot was unable to grant access to your account. Pardot gave this response: <em>%s</em>. Please check your login information.', 'optin-monster' ),
                        $this->api->error )
                );
            }

            $this->api_key = $this->api->authenticate( $api_data );
        }

        // Get lists (campaigns) from Pardot.
        $lists = $this->api->get_campaigns();

        if ( null == $lists ) {
            return $this->error( 'list-error', __( 'There was a problem retrieving your lists. Please check your API credentials.', 'optin-monster' ) );
        } else {
            return $this->build_list_html( $lists, $list_id );
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
                        $output .= '<option value="' . $list->id . '"' . selected( $list_id, $list->id, false ) . '>' . $list->name . '</option>';
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

        $api_data = array(
            'email'    => $account['email'],
            'password' => $account['password'],
            'user_key' => $account['user_key'],
        );

        // Authenticate with Pardot.
        $this->api = new Pardot_OM_API();
        $api_key   = $this->api->authenticate( $api_data );

        // Setup data to be passed.
        $data = array(
            'email' => $lead['lead_email'],
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

        $data = apply_filters( 'optin_monster_pre_optin_pardot', $data, $lead, $list_id, $this->api );

        $url = 'https://pi.pardot.com/api/prospect/version/3/do/upsert/email/' . $data['email'];

        // All done with 'email'.
        unset( $data['email'] );

        // Setup the POST args.
        $data['campaign_id'] = $list_id;
        $data['api_key']     = $api_key;
        $data['user_key']    = $account['user_key'];

        $args = array(
            'body' => $data,
        );

        // Send the lead to Pardot.
        $contact  = wp_remote_post( $url, $args );
        $xml_resp = new SimpleXMLElement( wp_remote_retrieve_body( $contact ) );
        $response = json_decode( json_encode( $xml_resp ) );

        if ( isset( $response->err ) ) {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to Pardot. %s', 'optin-monster' ),
                    $response->err
                )
            );
        } else {
            return true;
        }

    }
}