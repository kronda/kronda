<?php
/**
 * Provider class (abstract).
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
abstract class Optin_Monster_Provider {

    /**
     * Path to the file.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

    }

    /**
     * Authentication method for providers.
     *
     * @since 2.0.0
     *
     * @param array $args   Args to be passed for authentication.
     * @param int $optin_id The optin ID to target.
     */
    abstract public function authenticate( $args = array(), $optin_id );

    /**
     * Retrieval method for getting lists.
     *
     * @since 2.0.0
     *
     * @param array  $args    Args to be passed for list retrieval.
     * @param string $list_id The list ID to check for selection.
     * @param string $uniqid
     * @param string $optin_id
     *
     * @return
     */
    abstract public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' );

    /**
     * Method for building out the list selection HTML.
     *
     * @since 2.0.0
     *
     * @param array  $lists Lists for the email provider.
     * @param string $list_id
     * @param string $optin_id
     *
     * @return string $html HTML string for selecting lists.
     */
    abstract protected function build_list_html( $lists, $list_id = '', $optin_id = '' );

    /**
     * Method for opting into the email service provider.
     *
     * @since 2.0.0
     *
     * @param array $args Args to be passed when opting in.
     */
    abstract public function optin( $account = array(), $list_id, $lead );

    /**
     * Method for saving API account info to an optin.
     *
     * @since 2.0.0
     *
     * @param int $optin_id    The ID of the optin to target.
     * @param string $provider The email provider slug.
     * @param string $account  The unique account ID for the optin.
     */
    public function save_account( $optin_id, $provider, $account ) {

        $meta                      = get_post_meta( $optin_id, '_om_meta', true );
        $meta['email']['provider'] = $provider;
        $meta['email']['account']  = $account;
        update_post_meta( $optin_id, '_om_meta', $meta );

        // Flush any optin caches.
        Optin_Monster_Common::get_instance()->flush_optin_caches( $optin_id );

        // Now reset the meta property with our updated meta field.
        Optin_Monster_Output::get_instance()->meta = get_post_meta( $optin_id, '_om_meta', true );

    }

    /**
     * Method for setting API errors for email providers.
     *
     * @since 2.0.0
     *
     * @param string $id      The ID of the error.
     * @param string $message The error message.
     * @return object         A new WP_Error object.
     */
    public function error( $id, $message ) {

        return new WP_Error( $id, $message );

    }

}