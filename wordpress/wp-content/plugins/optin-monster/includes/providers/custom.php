<?php
/**
 * Custom HTML provider class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Provider_Custom extends Optin_Monster_Provider {

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
    public $provider = 'custom';

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

        // Store the account reference in the optin data.
        $uniqid = uniqid();

        $meta                      = get_post_meta( $optin_id, '_om_meta', true );
        $meta['email']['provider'] = $this->provider;
        $meta['email']['account']  = $uniqid;
        $meta['custom_html']       = $args['content'];
        update_post_meta( $optin_id, '_om_meta', $meta );

        // Flush any optin caches.
        Optin_Monster_Common::get_instance()->flush_optin_caches( $optin_id );

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

        return '';

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
            $output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-custom-html">' . __( 'Custom HTML Optin Form', 'optin-monster' ) . '</label><br />';
                $output .= '<textarea id="optin-monster-custom-html" class="om-custom-html-editor" rows="5"></textarea>';
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

        return true;

    }

}