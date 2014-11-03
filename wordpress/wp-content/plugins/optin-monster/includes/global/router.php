<?php
/**
 * Class Optin_Monster_Router
 *
 * @package optin-monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Router {

    /**
     * The action requested.
     *
     * @since 2.0.0
     *
     * @var string
     */
    protected $action;

    /**
     * The $_POST array for later use.
     *
     * @since 2.0.0
     *
     * @var array
     */
    protected $data;

    /**
     * The result of the requested action.
     *
     * @since 2.0.0
     *
     * @var mixed
     */
    protected $route;

    /**
     * Class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Do nothing if our header is not set.
        if ( empty( $_POST['optin_monster_ajax_action'] ) ) {
            return;
        }

        // Load the ajax interface.
        require plugin_dir_path( __FILE__ ) . 'ajax-interface.php';

        // Set the header and fire the route.
        header( "X-Robots-Tag: noindex, nofollow", true );
        $this->action = $_POST['optin_monster_ajax_action'];
        unset( $_POST['optin_monster_ajax_action'] );
        $this->data   = $_POST;

        // Fire the route.
        add_action( 'init', array( $this, 'route' ) );

    }

    /**
     * Routes the request to the correct controller.
     *
     * @since 2.0.0
     *
     * @return void
     */
    public function route() {

        switch ( $this->action ) {
            case 'do_optinmonster' :
                $this->do_optinmonster();
                break;
            case 'track_optinmonster' :
                $this->track_optinmonster();
                break;
            case 'get_optinmonster' :
                $this->get_optinmonster();
                break;
        }

        // Add hook to attach to ajax requests to our route.
        do_action( 'optin_monster_ajax_action', $this->action, $this->data );

        // Send the response back to the browser.
        die( json_encode( $this->get_route_response() ) );

    }

    /**
     * Collects the data for and opts-in a visitor.
     *
     * @since 2.0.0
     *
     * @global object $wpdb The WordPress DB object.
     * @return void
     */
    protected function do_optinmonster() {

        // Make sure an optin ID was set.
        if ( ! isset( $this->data['optin_id'] ) ) {
            die( json_encode( array( 'error' => __( 'No optin ID could be found. Please try again.', 'optin-monster' ) ) ) );
        }

        // Collect and prepare the data.
        $meta     = get_post_meta( $this->data['optin_id'], '_om_meta', true );
        $provider = isset( $meta['email']['provider'] ) ? $meta['email']['provider'] : false;

        // Make sure a provider has been set.
        if ( ! $provider || 'none' == $provider ) {
            die( json_encode( array( 'error' => __( 'No email provider has been set for this optin. Please try again.', 'optin-monster' ) ) ) );
        }

        // Grab the provider object.
        $provider = optin_monster_ajax_get_email_provider( $provider );

        // Load the data interfaces.
        require plugin_dir_path( __FILE__ ) . 'ajax-do-optin.php';
        require plugin_dir_path( __FILE__ ) . 'track-datastore.php';

        // Create the datastore interface objects.
        global $wpdb;
        $lead_store  = new Optin_Monster_Lead_Datastore( $wpdb );
        $track_store = new Optin_Monster_Track_Datastore( $this->data['optin_id'] );

        // Save the optin object.
        add_filter( 'optin_monster_lead_response', array( $this, 'pass_lead' ), 10, 2 );
        $this->route = new Optin_Monster_Ajax_Do_Optin( $this->data, $provider, $lead_store, $track_store );
        remove_filter( 'optin_monster_lead_response', array( $this, 'pass_lead' ), 10, 2 );

    }

    /**
     * Tracks impressions for optins.
     *
     * @since 2.0.0
     *
     * @return void
     */
    protected function track_optinmonster() {

        // Make sure an optin ID was sent
        if ( ! isset( $this->data['optin_id'] ) ) {
            die( json_encode( array( 'error' => __( 'An optin ID was not set.', 'optin-monster' ) ) ) );
        }

        // Load the data interfaces.
        require plugin_dir_path( __FILE__ ) . 'ajax-track-optin.php';
        require plugin_dir_path( __FILE__ ) . 'track-datastore.php';

        // Create the tracking datastore.
        $track_store = new Optin_Monster_Track_Datastore( $this->data['optin_id'] );

        // Save the tracker.
        $this->route = new Optin_Monster_Ajax_Track_Optin( $track_store );

    }

    /**
     * Retrieves an optin requested via AJAX.
     *
     * @since 2.0.0
     *
     * @return void
     */
    protected function get_optinmonster() {

        // Make sure an optin ID was set.
        if ( ! isset( $this->data['slug'] ) ) {
            die( json_encode( array( 'error' => __( 'No optin slug could be found. Please try again.', 'optin-monster' ) ) ) );
        }

        // Load the data interfaces.
        require plugin_dir_path( __FILE__ ) . 'ajax-get-optin.php';

        // Retrieve the optin data.
        $this->route = new Optin_Monster_Ajax_Get_Optin( $this->data['slug'] );

    }

    /**
     * Retrieves the proper route response in a JSON format that the
     * ajax callback handler can understand and parse.
     *
     * @since 2.0.0
     *
     * @return mixed
     */
    protected function get_route_response() {

        // If there are any errors, send back an array with an error key for a response.
        if ( ! $this->route && has_action( 'optin_monster_ajax_action' ) ) {
	        $response = array( 'success' => true );
        } else if ( is_wp_error( $this->route->get_response() ) ) {
            $response = array( 'error' => $this->route->get_response()->get_error_message() );
        } else {
            $response = array( 'success' => $this->route->get_response() );
        }

        return apply_filters( 'optin_monster_route_response', $response, $this );

    }

    /**
     * Possibly modifies the lead response to append lead data to the redirect URL.
     *
     * @since 2.1.0
     *
     * @param bool $response The response from the optin method (will be true).
     * @param array $lead    The lead data passed from the optin conversion.
     * @return array $res    The array of response data to be appended to the redirect URL.
     */
    public function pass_lead( $response, $lead ) {

    	// Get the optin and check to make sure we should pass leads to the redirect URL.
    	$optin = get_post( absint( $lead['optin_id'] ) );
    	if ( ! $optin ) {
	    	return $response;
    	}

    	$meta = get_post_meta( $optin->ID, '_om_meta', true );
    	if ( ! isset( $meta['redirect_pass'] ) || isset( $meta['redirect_pass'] ) && ! (bool) $meta['redirect_pass'] ) {
	    	return $response;
    	}

	    // Set a custom response with our lead data.
		$email_arg = apply_filters( 'optin_monster_redirect_email_arg', 'om_email', $lead );
		$name_arg  = apply_filters( 'optin_monster_redirect_name_arg', 'om_name', $lead );
		$res       = array();

		// If we have an email address, add it.
		if ( ! empty( $lead['lead_email'] ) && $email_arg ) {
			$res[ $email_arg ] = urlencode( $lead['lead_email'] );
		}

		// If we have a name, add it.
		if ( ! empty( $lead['lead_name'] ) && $name_arg ) {
			$res[ $name_arg ]  = urlencode( $lead['lead_name'] );
		}

		// Return the filtered response.
		return $res;

    }

}