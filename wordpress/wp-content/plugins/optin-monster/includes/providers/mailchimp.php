<?php
/**
 * Mailchimp provider class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Provider_Mailchimp extends Optin_Monster_Provider {

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
    public $provider = 'mailchimp';

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

        // Load the MailChimp API.
        if ( ! class_exists( 'Mailchimp' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/vendor/mailchimp/mailchimp.php';
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

        // Attempt to connect to the MailChimp API.
        $this->api = new Mailchimp( $data['om-api-key'] );

        try {
            $this->api->helper->ping();
        } catch ( Mailchimp_Invalid_ApiKey $e ) {
            return $this->error( 'api-error', $e->getMessage() );
        }

        // Save the account data for future reference.
        $providers                                        = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $uniqid                                           = uniqid();
        $providers[ $this->provider ][ $uniqid ]['api']   = trim( $data['om-api-key'] );
        $providers[ $this->provider ][ $uniqid ]['label'] = trim( strip_tags( $data['om-account-label'] ) );
        update_option( 'optin_monster_providers', $providers );

        // Store the account reference in the optin data.
        $this->save_account( $optin_id, $this->provider, $uniqid );

        // Return all of the list output from MailChimp.
        return $this->get_lists( array( 'api' => $data['om-api-key'] ) );

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
            $this->api = new Mailchimp( $args['api'] );
        }

        try {
            $lists = $this->api->lists->getList();
        } catch ( Mailchimp_Error $e ) {
            return $this->error( 'list-error', $e->getMessage() );
        }

        return $this->build_list_html( $lists['data'], $list_id, $optin_id );

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

        if ( empty( $list_id ) ) {
            return false;
        }

        if ( ! $this->api ) {
            $this->api = new Mailchimp( $args['api'] );
        }

        // Get any previously selected segments
        $meta     = get_post_meta( (int) $uniqid, '_om_meta', true );
        $selected = isset( $meta['email']['segments'] ) ? (array) $meta['email']['segments'] : array();

        // Attempt to grab segments for the list.
        try {
            $data = $this->api->lists->interestGroupings( $list_id );
        } catch ( Mailchimp_List_InvalidOption $e ) {
            return '';
        } catch ( Mailchimp_List_DoesNotExist $e ) {
            return '';
        }

        // Output the segments checkbox list
        $output = '';
        if ( $data ) {
            $output .= '<div class="optin-monster-field-box optin-monster-provider-segments optin-monster-clear">';
                $output .= '<p class="optin-monster-field-wrap">' . __( 'We also noticed that you have some segments in your list. You can select specific list segments for your optin below.', 'optin-monster' ) . '</p>';
                foreach ( $data as $group ) {
                    $output .= '<p class="optin-monster-field-wrap blue"><span>' . $group['name'] . '</span></p>';
                    foreach ( (array) $group['groups'] as $subgroup ) {
                        $checked = ( isset( $selected[$group['id'] ] ) && in_array( $subgroup['name'], $selected[$group['id']] ) ) ? 'checked="checked"' : '';
                        $output .= '<input id="' . sanitize_title_with_dashes( strtolower( $subgroup['name'] ) ) . '" type="checkbox" value="' . $subgroup['name'] . '" name="optin_monster[email_segments][' . $group['id'] . '][]" ' . $checked . '/> <label for="' . sanitize_title_with_dashes( strtolower( $subgroup['name'] ) ) . '">' . $subgroup['name'] . '</label><br />';
                    }
                }
            $output .= '</div>';
        }

        return empty( $output ) ? __( 'No segments are available for this list.', 'optin-monster' ) : $output;

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
                    foreach ( $lists as $list ) {
                        $output .= '<option value="' . $list['id'] . '"' . selected( $list_id, $list['id'], false ) . '>' . $list['name'] . '</option>';
                    }
                $output .= '</select>';
            $output .= '</p>';

            // We also want to check for group segments for the correct list. Let's check for them now.
            $selected_list = empty( $list_id ) ? $lists[0]['id'] : $list_id;
            $output .= $this->get_segments( array(), $selected_list, $optin_id );
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
        $this->api = new Mailchimp( $account['api'] );

        // Setup data to be passed
        $data = array(
            'email'     => $lead['lead_email'],
            'groupings' => array(),
        );

        // Setup name if set
        if ( isset( $lead['lead_name'] ) && 'false' != $lead['lead_name'] ) {
            $names = explode( ' ', $lead['lead_name'] );
            if ( isset( $names[0] ) ) {
                $data['FNAME'] = $names[0];
            }
            if ( isset( $names[1] ) ) {
                $data['LNAME'] = $names[1];
            }
        }

        // Setup segments if set
        if ( ! empty( $account['segments'] ) ) {
            $i = 0;
            foreach ( $account['segments'] as $group_id => $segments ) {
                $data['groupings'][ $i ]['id']     = $group_id;
                $data['groupings'][ $i ]['groups'] = $segments;
                $i++;
            }
        }

        $data    = apply_filters( 'optin_monster_pre_optin_mailchimp', $data, $lead, $list_id, $this->api );
        $double  = apply_filters( 'optin_monster_mailchimp_double', true );
        $welcome = apply_filters( 'optin_monster_mailchimp_welcome', true );

        // Massage the data a bit more
        $lead['lead_email'] = $data['email'];
        unset( $data['email'] );

        // Send data to Mailchimp
        try {
            $response = $this->api->lists->subscribe( $list_id, array( 'email' => $lead['lead_email'] ), $data, 'html', (bool) $double, true, false, (bool) $welcome );
        } catch( Mailchimp_List_AlreadySubscribed $e ) {
            return true;
        } catch ( Mailchimp_Error $e ) {
            return $this->error( 'optin-error',
                sprintf(
                    __( 'There was an error saving the data to MailChimp. %s', 'optin-monster' ),
                    $e->getMessage()
                )
            );
        }

        return true;

    }

}