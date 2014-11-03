<?php
/**
 * Class Optin_Monster_Ajax_Get_Optin
 *
 * @package Optin_Monster
 * @author  Thomas Griffin <thomas@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Ajax_Get_Optin implements Optin_Monster_Ajax_Interface {

    /**
     * The current optin slug.
     *
     * @since 2.0.0
     *
     * @var string
     */
    protected $slug;
    
    /**
     * The optin object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    protected $optin;

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
     * @param string $slug The optin slug.
     */
    public function __construct( $slug ) {

        // Set class properties.
        $this->slug  = $slug;
        $this->optin = Optin_Monster::get_instance()->get_optin_by_slug( $this->slug );

        // Set the response.
        $this->response = $this->get_optin_data( $this->optin );

    }
    
    /**
     * Returns the optin data.
     *
     * @since 2.0.0
     *
     * @return array $data An array of optin data.
     */
    public function get_optin_data( $optin ) {
	    
	    // Prepare some needed variables.
	    $meta  = get_post_meta( $optin->ID, '_om_meta', true );
	    $theme = isset( $meta['theme'] ) ? $meta['theme'] : false;
	    
	    // If no theme exists, return false.
	    if ( ! $theme ) {
		    return false;
	    }
	    
	    // Load the optin data and theme and tailor it to the manual click experience.
	    $data 		    = Optin_Monster_Output::get_instance()->get_optin_monster_data( $optin->ID );
		$data['delay']  = 0;
		$data['exit']   = 0;
		$data['manual'] = 0;
	    $data['html']   = Optin_Monster_Output::get_instance()->get_optin_monster( $optin->ID, true );
	    
	    // Return the data.
	    return $data;
	    
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