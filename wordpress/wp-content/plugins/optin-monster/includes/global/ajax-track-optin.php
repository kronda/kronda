<?php
/**
 * Class Optin_Monster_Ajax_Track_Optin
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Ajax_Track_Optin implements Optin_Monster_Ajax_Interface {

    /**
     * The current tracking datastore object
     *
     * @since 2.0.0
     *
     * @var Optin_Monster_Track_Datastore
     */
    protected $track_store;

    /**
     * The response from storing the impression
     *
     * @since 2.0.0
     *
     * @var mixed
     */
    protected $response;

    /**
     * Class constructor
     *
     * @since 2.0.0
     *
     * @param Optin_Monster_Track_Datastore $track_store The tracking datastore object
     */
    public function __construct( Optin_Monster_Track_Datastore $track_store ) {

        $this->track_store = $track_store;

        // Provide a hook to save custom tracking data.
        do_action( 'optin_monster_track_optin', $track_store->get_optin_id(), 'impression' );

        // Save the impression to the database.
        if ( apply_filters( 'optin_monster_tracking', true, $track_store->get_optin_id() ) ) {
	        try {
	            $this->response = $this->track_store->save( 'impression' );
	        } catch ( Exception $e ) {
	            $this->response = $e->getMessage();
	        }
	    } else {
		    $this->response = true;
	    }

    }

    /**
     * Returns the response from the database
     *
     * @since 2.0.0
     *
     * @return mixed
     */
    public function get_response() {

        if ( $this->response ) {
            return $this->response;
        } else {
            return new WP_Error( 'optin-error', __( 'An unknown error occurred. Please try again.', 'optin-monster' ) );
        }

    }

}