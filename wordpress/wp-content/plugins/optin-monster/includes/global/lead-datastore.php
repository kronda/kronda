<?php
/**
 * Class Optin_Monster_Lead_Datastore
 *
 * CRUD interface for optin leads.
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
class Optin_Monster_Lead_Datastore implements Optin_Monster_Datastore_Interface {

    /**
     * Instance of $wpdb.
     *
     * @since 2.0.0
     *
     * @var wpdb
     */
    protected $db;

    /**
     * Name of the associated table.
     *
     * @since 2.0.0
     *
     * @var string
     */
    protected $table;

    /**
     * Array of all available fields.
     *
     * @since 2.0.0
     *
     * @var array
     */
    protected $available_fields = array(
        'lead_id',
        'optin_id',
        'lead_name',
        'lead_email',
        'lead_type',
        'user_agent',
        'referrer',
        'referred_from',
        'post_id',
    );

    /**
     * Array of required fields.
     *
     * @since 2.0.0
     *
     * @var array
     */
    protected $required_fields = array(
        'optin_id',
        'lead_email',
        'lead_type',
        'user_agent',
        'referrer',
    );

    /**
     * Class constructor.
     *
     * @param wpdb $db
     *
     * @since 2.0.0
     */
    public function __construct( wpdb $db ) {

        $this->db = $db;

        // Save the table name
        $this->table = $this->db->prefix . 'om_leads';

    }

    /**
     * Saves data into the database.
     *
     * @since 2.0.0
     *
     * @param array $data The data to insert into database.
     *
     * @return bool
     * @throws Exception
     */
    public function save( $data ) {

        $time = current_time( 'mysql' );

        // Only save leads if the user has the option checked.
        $option = get_option( 'optin_monster' );
        if ( ! isset( $option['leads'] ) || isset( $option['leads'] ) && ! $option['leads'] ) {
            throw new Exception( __( 'The user has chosen not to store leads locally.', 'optin-monster' ) );
        }

        // Make sure the required fieds exist in the passed data array.
        foreach ( $this->required_fields as $key => $value ) {
            if ( ! array_key_exists( $value, $data ) ) {
                throw new Exception( sprintf( __( 'A value for %s must be passed in the $data array.', 'optin-monster' ), $value ) );
            }
        }

        // Ensure that the lead does not already exist in the DB.
        $exists = $this->find_where( 'lead_email', $data['lead_email'] );
        if ( ! empty( $exists ) ) {
            throw new Exception( __( 'The lead already exists in the database.', 'optin-monster' ) );
        }

        // Add the date modified to the data array
        $data['date_modified'] = $time;

        // If an 'id' is passed, we're going to update the record, else insert.
        if ( isset( $data['id'] ) ) {
            $id = $data['id'];
            unset( $data['id'] );

            if ( false == $this->db->update( $this->table, $data, array( 'lead_id', $id ) ) ) {
                throw new Exception( __( 'There was an error updating the lead.', 'optin-monster' ) );
            } else {
                return true;
            }
        } else {
            // This is a new record, so we'll insert the date added.
            $data['date_added'] = $time;

            if ( false == $this->db->insert( $this->table, $data ) ) {
                throw new Exception( __( 'There was an error inserting the lead.', 'optin-monster' ) );
            } else {
                return true;
            }
        }

    }

    /**
     * Deletes the record of the passed ID
     *
     * @since 2.0.0
     *
     * @param int $id ID of the record to delete
     *
     * @return bool
     * @throws Exception
     */
    public function remove( $id ) {

        // Make sure the ID passed is an integer.
        if ( ! is_int( $id ) ) {
            throw new Exception( __( 'A numerical ID must be passed.', 'optin-monster' ) );
        }

        // Setup the WHERE clause.
        $where = array(
            'lead_id' => $id,
        );

        // Delete the record.
        if ( false == $this->db->delete( $this->table, $where ) ) {
            throw new Exception( __( 'There was an error deleting the lead.', 'optin-monster' ) );
        } else {
            return true;
        }

    }

    /**
     * Returns query based on the passed ID(s).
     *
     * @since 2.0.0
     *
     * @param string|array $ids The ID(s) to search
     *
     * @return mixed
     */
    public function find( $ids ) {

        // Make array of IDs into a string that MySQL can use for IN().
        if ( is_array( $ids ) ) {
            $search = implode( ', ', $ids );
        } else {
            $search = $ids;
        }

        // Setup the query.
        $query = "SELECT * FROM {$this->table} WHERE lead_id IN %s;";

        // Run the query and return the results.
        return $this->db->get_results( $this->db->prepare( $query, $search ) );

    }

    /**
     * Returns all records from the table.
     *
     * @since 2.0.0
     *
     * @return mixed
     */
    public function find_all() {

        // Setup the query.
        $query = "SELECT * FROM {$this->table};";

        // Run the query and return the results.
        return $this->db->get_results( $query );

    }

    /**
     * Returns query where the key equals the value.
     *
     * @since 2.0.0
     *
     * @param string $key
     * @param string $value
     * @param bool   $strict
     *
     * @return mixed
     * @throws Exception
     */
    public function find_where( $key, $value, $strict = true ) {

        // Make sure the specified field exists.
        if ( ! in_array( $key, $this->available_fields ) ) {
            throw new Exception( __( 'The key passed is not a valid column in the leads table.', 'optin-monster' ) );
        }

        // Set wildcards around value if $strict is false.
        if ( ! $strict ) {
            $value = "%%$value%%";
        }

        // Setup the query.
        $query = "SELECT * FROM {$this->table} WHERE $key LIKE %s;";

        // Run the query and return the results.
        return $this->db->get_results( $this->db->prepare( $query, $value ) );

    }

    /**
     * Creates the table
     *
     * @since 2.0.0
     *
     * @return void
     */
    public function create_table() {

        global $charset_collate;

        if ( ! empty( $this->db->charset ) ) {
            $charset         = $this->db->charset;
            $charset_collate = "DEFAULT CHARACTER SET $charset";
        }
        if ( ! empty( $this->db->collate ) ) {
            $collate = $this->db->collate;
            $charset_collate .= " COLLATE $collate";
        }

        $query = "CREATE TABLE {$this->table} (
                  lead_id bigint(20) unsigned NOT NULL auto_increment,
                  optin_id int(8) unsigned NOT NULL default '0',
                  lead_name varchar(128) NOT NULL default '',
                  lead_email varchar(128) NOT NULL default '',
                  lead_type varchar(128) NOT NULL default '',
                  user_agent varchar(128) NOT NULL default '',
                  referrer varchar(256) NOT NULL default '',
                  referred_from varchar(256) NOT NULL default '',
                  post_id int(8) unsigned NOT NULL default '0',
                  date_added datetime NOT NULL default '0000-00-00 00:00:00',
                  date_modified datetime NOT NULL default '0000-00-00 00:00:00',
                  PRIMARY KEY  (lead_id),
                  KEY (optin_id),
                  UNIQUE KEY (lead_email)
             ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $query );

    }

    /**
     * Drops the associated table
     *
     * @since 2.0.0
     *
     * @return void
     */
    public function remove_table() {

        $query = "DROP TABLE IF EXISTS {$this->table}";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $query );

    }

    /**
     * Returns the table name
     *
     * @since 2.0.0
     *
     * @return string
     */
    public function get_table_name() {

        return $this->table;

    }

    /**
     * Returns the available fields
     *
     * @since 2.0.0
     *
     * @return array
     */
    public function get_available_fields() {

        return $this->available_fields;

    }

    /**
     * Returns the required fields
     *
     * @since 2.0.0
     *
     * @return array
     */
    public function get_required_fields() {

        return $this->required_fields;

    }

}