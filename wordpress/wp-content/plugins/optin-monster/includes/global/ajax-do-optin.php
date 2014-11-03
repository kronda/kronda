<?php
/**
 * Class Optin_Monster_Ajax_Do_Optin
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Ajax_Do_Optin implements Optin_Monster_Ajax_Interface {

    /**
     * The current provider object.
     *
     * @since 2.0.0
     *
     * @var Optin_Monster_Provider
     */
    protected $provider;

    /**
     * The current lead datastore object.
     *
     * @since 2.0.0
     *
     * @var Optin_Monster_Lead_Datastore
     */
    protected $lead_store;

    /**
     * The current tracking datastore object.
     *
     * @since 2.0.0
     *
     * @var Optin_Monster_Track_Datastore
     */
    protected $track_store;

    /**
     * The current optin meta data.
     *
     * @since 2.0.0
     *
     * @var mixed
     */
    protected $optin_meta;

    /**
     * The response from the provider optin.
     *
     * @since 2.0.0
     *
     * @var bool
     */
    protected $response;

    /**
     * The class constructor.
     *
     * @since 2.0.0
     *
     * @param                               $data        The user data from the browser
     * @param Optin_Monster_Provider        $provider    The provider object
     * @param Optin_Monster_Lead_Datastore  $lead_store  The lead datastore object
     * @param Optin_Monster_Track_Datastore $track_store The tracking datastore object
     */
    public function __construct( $data, Optin_Monster_Provider $provider, Optin_Monster_Lead_Datastore $lead_store, Optin_Monster_Track_Datastore $track_store ) {

        // Set class properties.
        $this->provider    = $provider;
        $this->lead_store  = $lead_store;
        $this->track_store = $track_store;

        // Process the data from the browser.
        $lead['optin_id']      = isset( $data['optin_id'] ) ? absint( $data['optin_id'] ) : 0;
        $lead['referrer']      = isset( $data['referrer'] ) ? stripslashes( esc_url( $data['referrer'] ) ) : '';
        $lead['user_agent']    = isset( $data['user_agent'] ) ? stripslashes( strip_tags( $data['user_agent'] ) ) : '';
        $lead['lead_name']     = isset( $data['name'] ) ? stripslashes( $data['name'] ) : '';
        $lead['lead_email']    = isset( $data['email'] ) ? stripslashes( $data['email'] ) : '';
        $lead['referred_from'] = isset( $data['previous'] ) ? stripslashes( esc_url( $data['previous'] ) ) : '';
        $lead['post_id']       = isset( $data['post_id'] ) ? absint( $data['post_id'] ) : 0;
        $lead['lead_type']     = 'conversion';

        // Get the optin meta data.
        $this->optin_meta = get_post_meta( $lead['optin_id'], '_om_meta', true );

        // Provide a hook to save custom tracking data.
        do_action( 'optin_monster_track_optin', $lead['optin_id'], 'conversion' );

        // Increase the conversion counter.
        if ( apply_filters( 'optin_monster_tracking', true, $lead['optin_id'] ) ) {
            try {
                $track = $this->track_store->save( 'conversion' );
            } catch ( Exception $e ) {
                $this->response = $e->getMessage();
            }
        }

        // Save the lead in the database.
        $option = get_option( 'optin_monster' );
        if ( isset( $option['leads'] ) && $option['leads'] ) {
            try {
                $save = $this->lead_store->save( $lead );
            } catch ( Exception $e ) {
                $this->response = $e->getMessage();
            }
        }

        // Save the lead to the email provider.
        $providers = Optin_Monster_Common::get_instance()->get_email_providers( true );
        $provider  = $this->optin_meta['email']['provider'];
        $account   = $this->optin_meta['email']['account'];
        $list_id   = $this->optin_meta['email']['list_id'];

        // Prepare the provider account.
        $provider_account               = $providers[ $provider ][ $account ];
        $provider_account['account_id'] = $account;

        // Get segments if set.
        if ( ! empty( $this->optin_meta['email']['segments'] ) ) {
            $provider_account['segments'] = $this->optin_meta['email']['segments'];
        }

        // Get client if set.
        if ( ! empty( $this->optin_meta['email']['client_id'] ) ) {
            $provider_account['client'] = $this->optin_meta['email']['client_id'];
        }

        // Set the response.
        $this->response = $this->provider->optin( $provider_account, $list_id, $lead );

        // Provide a hook to interact with the lead.
        do_action( 'optin_monster_after_lead_stored', $lead, $this );

        // Allow the response to be filtered (as long as it is not an error) so that lead data can be passed back to redirect URLs.
        if ( ! is_wp_error( $this->response ) ) {
        	$this->response = apply_filters( 'optin_monster_lead_response', $this->response, $lead, $this );
        }

    }

    /**
     * Returns the response.
     *
     * @since 2.0.0
     *
     * @return bool
     */
    public function get_response() {

        if ( $this->response ) {
            return $this->response;
        } else {
            return new WP_Error( 'optin-error', __( 'An unknown error occurred. Please try again.', 'optin-monster' ) );
        }

    }

}