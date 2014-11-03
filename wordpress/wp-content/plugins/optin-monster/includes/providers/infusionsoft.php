<?php
/**
 * Class Optin_Monster_Provider_Infusionsoft
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Provider_Infusionsoft extends Optin_Monster_Provider {

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
    public $provider = 'infusionsoft';

    /**
     * Holds the InfusionSoft API instance.
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

        // Load the InfusionSoft API.
        if ( ! class_exists( 'iSDK' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/infusionsoft/isdk.php';
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
    public function authenticate( $args = array(), $optin_id ) {

        // Attempt to authenticate with Infusionsoft
        try {
            $this->api = new iSDK();
            $this->api->cfgCon( $args['om-subdomain'], $args['om-api-key'], 'throw' );
        } catch ( iSDKException $e ) {
            return $this->error( 'auth-error',
                sprintf(
                    __( 'There was an error authenticating with Infusionsoft. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        $providers                                        = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                           = uniqid();
        $providers[ $this->provider ][ $uniqid ]['app']   = trim( $args['om-subdomain'] );
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
     * @return string
     */
    public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

        // Authenticate with Infusionsoft if needed.
        if ( ! $this->api ) {
            $this->api = new iSDK();
            $this->api->cfgCon( $args['app'], $args['api'], 'throw' );
        }

        // Query Infusionsoft for available tags.
        $page    = 0;
        $all_res = array();
        while ( true ) {
            $res = $this->api->dsQuery(
                'ContactGroup',
                1000,
                $page,
                array( 'Id' => '%' ),
                array( 'Id', 'GroupName' )
            );
            $all_res = array_merge( $all_res, $res );
            if ( count( $res ) < 1000 ) {
                break;
            }

            $page ++;
        }

        return $this->build_list_html( $all_res, $list_id, $optin_id );

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
                        $output .= '<option value="' . $list['Id'] . '"' . selected( $list_id, $list['Id'], false ) . '>' . $list['GroupName'] . '</option>';
                    }
                $output .= '</select>';
            $output .= '</p>';
        $output .= '</div>';

        // Get the available sequences.
        $selected_list = empty( $list_id ) ? $lists[0]['Id'] : $list_id;
        $output .= $this->get_segments( array(), $selected_list, $optin_id );

        return $output;
    }

    /**
     * Retrieval method for getting list segments.
     *
     * @since 2.0.0
     *
     * @param array  $args    Args to be passed for list retrieval.
     * @param string $list_id The list ID to check for selection.
     * @param string $uniqid  The account ID to target.
     *
     * @return  string|WP_Error Output of the email segments or WP_Error.
     */
    public function get_segments( $args = array(), $list_id = '', $uniqid = '' ) {

        // Authenticate with Infusionsoft if needed.
        if ( ! $this->api ) {
            $this->api = new iSDK();
            $this->api->cfgCon( $args['app'], $args['api'], 'throw' );
        }

        // Attempt to grab sequences from InfusionSoft.
        $page      = 0;
        $sequences = array();
        while ( true ) {
            $res       = $this->api->dsQuery( 'Campaign', 1000, $page, array( 'Id' => '%' ), array( 'Id', 'Name' ) );
            $sequences = array_merge( $sequences, $res );
            if ( count( $res ) < 1000 ) {
                break;
            }
            $page ++;
        }

        // Get any previously selected sequences.
        $meta     = get_post_meta( (int) $uniqid, '_om_meta', true );
        $selected = isset( $meta['email']['segments'] ) ? (array) $meta['email']['segments'] : array();

        $output = '';
        $n      = 0;
        if ( $sequences ) {
            $output .= '<div class="optin-monster-field-box optin-monster-provider-segments optin-monster-clear">';
                $output .= '<p class="optin-monster-field-wrap">' . __( 'We also noticed that you have some follow-up sequences available. You can select them for your optin below.', 'optin-monster' ) . '</p><br />';
                foreach ( $sequences as $seq ) {
                    $checked = ( in_array( $seq['Id'], $selected ) ) ? 'checked="checked"' : '';
                    $output .= '<input id="' . sanitize_title_with_dashes( strtolower( $seq['Id'] ) ) . '" type="checkbox"  value="' . $seq['Id'] . '" name="optin_monster[email_segments][]" ' . $checked . ' /> <label for="' . sanitize_title_with_dashes( strtolower( $seq['Id'] ) ) . '">' . $seq['Name'] . '</label><br />';
                }
            $output .= '</div>';
        }

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
     * @return bool|WP_Error  True on successful optin.
     */
    public function optin( $account = array(), $list_id, $lead ) {

        // Authenticate with Infusionsoft.
        try {
            $this->api = new iSDK();
            $this->api->cfgCon( $account['app'], $account['api'], 'throw' );
        } catch ( iSDKException $e ) {
            return $this->error( 'auth-error',
                sprintf(
                    __( 'There was an error authenticating with Infusionsoft. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        $data = array();

        if ( $lead['lead_name'] && 'false' !== $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) && isset( $names[1] ) ) {
                $first = $names[0];
                $data  = array( 'FirstName' => $names[0], 'LastName' => $names[1], 'Email' => $lead['lead_email'] );
            } else {
                $data = array( 'FirstName' => $lead['lead_name'], 'Email' => $lead['lead_email'] );
            }
        } else {
            $data = array( 'Email' => $lead['lead_email'] );
        }

        // Allow the list ID to be filtered.
        $data['list_id'] = $list_id;

        $data = apply_filters( 'optin_monster_pre_optin_infusionsoft', $data, $lead, $list_id, $this->api );

        // Extract list ID from the filtered data.
        $list_id = $data['list_id'];
        unset( $data['list_id'] );

        // Add the new contact to Infusionsoft, first checking to see if they already exist.
        try {
            $bool = $this->api->findByEmail( $lead['lead_email'], array( 'Id' ) );
            if ( isset( $bool[0] ) && ! empty( $bool[0]['Id'] ) ) {
                $contact_id = $bool[0]['Id'];
                $this->api->updateCon( $contact_id, $data );
                $group_add = $this->api->grpAssign( $bool[0]['Id'], $list_id );
            } else {
                $contact_id = $this->api->addCon( $data );
                $group_add  = $this->api->grpAssign( $contact_id, $list_id );
            }
        } catch ( iSDKException $e ) {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to Infusionsoft. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        // Get any selected sequences.
        $sequences = isset( $account['segments'] ) ? (array) $account['segments'] : array();
        $sequences = apply_filters( 'optin_monster_infusionsoft_sequences', $sequences, $lead, $list_id, $this->api );

        // Return early if no sequences were selected.
        if ( empty( $sequences ) ) {
            return true;
        }

        // Assign the contact to each selected sequence.
        foreach ( $sequences as $seq_id ) {
            try {
                $campaign_added = $this->api->campAssign( $contact_id, $seq_id );
            } catch ( iSDKException $e ) {
                return $this->error( 'optin-error',
                    sprintf(
                        __( 'There was an error saving the data to Infusionsoft. %s', 'optin-monster' ),
                        $e->getMessage()
                    )
                );
            }
        }

        return true;

    }

}