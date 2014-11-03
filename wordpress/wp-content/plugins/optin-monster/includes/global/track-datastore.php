<?php
/**
 * Class Optin_Monster_Track_Datastore
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Track_Datastore implements Optin_Monster_Datastore_Interface {

    /**
     * The current optin ID.
     *
     * @since 2.0.0
     *
     * @var int
     */
    protected $optin_id;

    /**
     * The meta key for optin impressions.
     *
     * @since 2.0.0
     *
     * @var string
     */
    protected $impression = 'om_counter';

    /**
     * The meta key for optin conversions.
     *
     * @since 2.0.0
     *
     * @var string
     */
    protected $conversion = 'om_conversions';

    /**
     * Class constructor.
     *
     * @since 2.0.0
     *
     * @param int $optin_id The current optin ID.
     */
    public function __construct( $optin_id ) {

        $this->optin_id = $optin_id;

    }

    /**
     * Saves the hit to the database.
     *
     * @since 2.0.0
     *
     * @param string $data Either 'impression' or 'conversion'.
     * @return bool
     */
    public function save( $data ) {

        // Set the correct meta key based on passed data.
        if ( 'impression' == $data ) {
            $key = $this->impression;
        } elseif ( 'conversion' == $data ) {
            $key = $this->conversion;
        }

        // Increase the counter by 1.
        $counter = get_post_meta( $this->optin_id, $key, true );
        update_post_meta( $this->optin_id, $key, (int) $counter + 1 );

        return true;

    }

    /**
     * Retrieves the impressions for a given optin.
     *
     * @since 2.0.0
     *
     * @return int The number of optin impressions.
     */
    public function get_impressions() {

        return (int) get_post_meta( $this->optin_id, $this->impression, true );

    }

    /**
     * Retrieves the conversions for a given optin.
     *
     * @since 2.0.0
     *
     * @return int The number of optin conversions.
     */
    public function get_conversions() {

        return (int) get_post_meta( $this->optin_id, $this->conversion, true );

    }

    public function remove( $id ) {
        // TODO: Implement remove() method.
    }

    public function find( $ids ) {
        // TODO: Implement find() method.
    }

    public function find_all() {
        // TODO: Implement find_all() method.
    }

    public function find_where( $key, $value, $strict = true ) {
        // TODO: Implement find_where() method.
    }

    public function create_table() {
        // TODO: Implement create_table() method.
    }

    public function remove_table() {
        // TODO: Implement remove_table() method.
    }

    public function get_table_name() {
        // TODO: Implement get_table_name() method.
    }

    public function get_available_fields() {
        // TODO: Implement get_available_fields() method.
    }

    public function get_required_fields() {
        // TODO: Implement get_required_fields() method.
    }

    /**
     * Returns the optin ID.
     *
     * @since 2.0.0
     *
     * @return int
     */
    public function get_optin_id() {

        return $this->optin_id;

    }
    
    /**
     * Magic method to retrieve protected class properties.
     *
     * @since 2.0.4
     *
     * @return mixed
     */
    public function __get( $property ) {
	    
	    return $this->{$property};
	    
    }

}