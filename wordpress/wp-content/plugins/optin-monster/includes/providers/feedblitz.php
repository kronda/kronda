<?php
/**
 * Feedblitz provider class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Provider_Feedblitz extends Optin_Monster_Provider {

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
    public $provider = 'feedblitz';

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
     * @param array $data     Data submitted by the user to be passed for authentication.
     * @param int   $optin_id The optin ID being used.
     *
     * @return string The list dropdown HTML
     */
    public function authenticate( $data = array(), $optin_id ) {

        // Attempt to conenct to the Feedblitz API.
        libxml_use_internal_errors( true );
        $api  = wp_remote_get( 'https://www.feedblitz.com/f.api/syndications?key=' . $data['om-api-key'] . '&summary=1', array(
                'headers' => array(
                    'Content-Type' => 'application/xml',
                    'User-Agent'   => 'OptinMonster API'
                )
            ) );
        $body = wp_remote_retrieve_body( $api );
        $body = str_replace( '&amp;', 'OMamp;', $body );
        $body = str_replace( '&', '&amp;', $body );
        $body = str_replace( 'OMamp;', '&amp;', $body );
        $res  = json_decode( json_encode( simplexml_load_string( $body ) ) );
        if ( ! empty( $res->rsp->err->{'@attributes'}->msg ) ) {
            return $this->error( 'api-error', $res->rsp->err->{'@attributes'}->msg . '.' );
        } else if ( ! $res ) {
            return $this->error( 'api-error', __( 'There was an error connecting to the Feedblitz API. Please try again.', 'optin-monster' ) );
        }

        // Save the account data for future reference.
        $providers                                        = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                           = uniqid();
        $providers[ $this->provider ][ $uniqid ]['api']   = trim( $data['om-api-key'] );
        $providers[ $this->provider ][ $uniqid ]['label'] = trim( strip_tags( $data['om-account-label'] ) );
        update_option( 'optin_monster_providers', $providers );

        // Store the account reference in the optin data.
        $this->save_account( $optin_id, $this->provider, $uniqid );

        // The $lists variable will already have our list data, so just return the lists variable.
        return $this->build_list_html( $res->syndications->syndication );

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

        // The API key will be part of the args passed.
        libxml_use_internal_errors( true );
        $api  = wp_remote_get( 'https://www.feedblitz.com/f.api/syndications?key=' . $args['api'] . '&summary=1', array(
                'headers' => array(
                    'Content-Type' => 'application/xml',
                    'User-Agent'   => 'OptinMonster API'
                )
            ) );
        $body = wp_remote_retrieve_body( $api );
        $body = str_replace( '&amp;', 'OMamp;', $body );
        $body = str_replace( '&', '&amp;', $body );
        $body = str_replace( 'OMamp;', '&amp;', $body );
        $res  = json_decode( json_encode( simplexml_load_string( $body ) ) );
        if ( ! empty( $res->rsp->err->{'@attributes'}->msg ) ) {
            return $this->error( 'api-error', $res->rsp->err->{'@attributes'}->msg . '.' );
        } else if ( ! $res ) {
            return $this->error( 'api-error', __( 'There was an error connecting to the Feedblitz API. Please try again.', 'optin-monster' ) );
        } else {
            return $this->build_list_html( $res->syndications->syndication, $list_id );
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

        libxml_use_internal_errors( true );
        $headers  = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent'   => 'OptinMonster API',
            'Referer'      => wp_get_referer()
        );
        $user     = wp_remote_get( 'https://www.feedblitz.com/f.api/user?key=' . $account['api'], array( 'headers' => $headers ) );
        $user_obj = json_decode( json_encode( simplexml_load_string( wp_remote_retrieve_body( $user ) ) ) );
        $data     = array(
            'EMAIL'         => $lead['lead_email'],
            'EMAIL_'        => '',
            'EMAIL_ADDRESS' => '',
            'FEEDID'        => $list_id,
            'CIDS'          => 1,
            'PUBLISHER'     => $user_obj->user->id
        );

        $data = apply_filters( 'optin_monster_pre_optin_hubspot', $data, $lead, $list_id, null );

        $data                      = http_build_query( $data, '', '&' );
        $headers['Content-Length'] = strlen( $data );
        $this->api                 = wp_remote_post( 'http://www.feedblitz.com/f/f.fbz?AddNewUserDirect', array(
                'headers' => $headers,
                'body'    => $data
            ) );

        return true;

    }

}