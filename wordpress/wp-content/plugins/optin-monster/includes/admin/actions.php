<?php
/**
 * Actions class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Actions {

    /**
     * Holds the class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public static $instance;

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
     * Holds the action item.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $action;

    /**
     * Holds the optin ID.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $optin_id;

    /**
     * Holds the optin object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $optin;

    /**
     * Holds any notices for the actions.
     *
     * @since 2.0.0
     *
     * @var array
     */
    public $notices = array();

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Handle any optin actions in the admin.
        if ( empty( $_GET['om_action'] ) && empty( $_GET['optin'] ) ) {
            return;
        }

        // Set class properties.
        $this->action   = isset( $_GET['om_action'] ) ? $_GET['om_action'] : $_GET['action'];
        $this->optin_id = isset( $_GET['om_optin_id'] ) ? absint( $_GET['om_optin_id'] ) : 0;
        $this->split    = isset( $_GET['om_optin_split'] ) ? absint( $_GET['om_optin_split'] ) : false;
        if ( $this->split ) {
            $id             = $this->optin_id;
            $split          = $this->split;
            $this->optin_id = $split;
            $this->split    = $id;
        }
        $this->optin    = get_post( $this->optin_id );

        // Do our actions.
        add_action( 'init', array( $this, 'do_actions' ) );

    }

    /**
     * Handles varies OptinMonster actions in the admin.
     *
     * @since 2.0.0
     */
    public function do_actions() {

        switch ( $this->action ) {
            case 'reset-stats' :
                $this->notices['updated'] = __( 'You have reset the stats for the selected optins successfully!', 'optin-monster' );
                break;
            case 'split' :
                $this->split_test();
                $this->notices['updated'] = sprintf( __( 'You have created a split test for %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' );
                break;
            case 'duplicate' :
                $this->duplicate();
                $this->notices['updated'] = sprintf( __( 'You have duplicated %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' );
                break;
            case 'reset' :
                $this->reset_stats();
                $this->notices['updated'] = sprintf( __( 'You have reset the stats for %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' );
                break;
            case 'test' :
                $test = $this->test_mode();
                $this->notices['updated'] = $test ? sprintf( __( 'You have enabled test mode for %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' ) : sprintf( __( 'You have disabled test mode for %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' );
                break;
            case 'delete' :
                $this->delete();
                $this->notices['updated'] = isset( $_GET['om_action'] ) ? sprintf( __( 'You have deleted %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' ) : __( 'You have deleted the selected optins successfully!', 'optin-monster' );
                break;
            case 'disable' :
                $this->disable();
                $this->notices['updated'] = sprintf( __( 'You have disabled %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' );
                break;
            case 'live' :
                $this->live();
                $this->notices['updated'] = sprintf( __( 'You have gone live with %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' );
                break;
            case 'primary' :
                $this->make_primary();
                $this->notices['updated'] = sprintf( __( 'You have made %s the primary optin successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' );
                break;
            case 'cookies' :
                $this->clear_cookies();
                $this->notices['updated'] = isset( $_GET['om_action'] ) ? sprintf( __( 'You have cleared the local cookies for %s successfully!', 'optin-monster' ), '<strong>' . $this->get_optin_title() . '</strong>' ) : __( 'You have cleared the local cookies for the selected optins successfully!', 'optin-monster' );
                break;
        }

        do_action( 'optin_monster_admin_action', $this->action, $this->optin_id, $this->optin );

        // Flush any optin caches.
        Optin_Monster_Common::get_instance()->flush_optin_caches( $this->optin_id, ( is_object( $this->optin ) ? $this->optin->post_name : '' ) );
        if ( $this->split ) {
            Optin_Monster_Common::get_instance()->flush_optin_caches( $this->split );
        }

        // If there are any notices, output them now.
        if ( ! empty( $this->notices ) ) {
            add_action( 'admin_notices', array( $this, 'notices' ) );
        }

    }

    /**
     * Split tests an optin.
     *
     * @since 2.0.0
     */
    public function split_test() {

        // Grab the original meta from the optin that is to be split tested.
        $original_meta = get_post_meta( $this->optin->ID, '_om_meta', true );

        // Grab the title.
        $new_title = isset( $_POST['om-split-title'] ) ? stripslashes( trim( $_POST['om-split-title'] ) ) : '';
        if ( empty( $new_title ) ) {
            $new_title = ! empty( $this->optin->post_title ) ? $this->optin->post_title . ' ' . __( 'Clone', 'optin-monster' ) : $this->optin->post_name . ' ' . __( 'Clone', 'optin-monster' );
        }

        // Generate a new optin object.
        $new_slug = $this->generate_postname_hash() . '-' . $original_meta['type'];
        $new_post = array(
            'menu_order'     => $this->optin->menu_order,
            'comment_status' => $this->optin->comment_status,
            'ping_status'    => $this->optin->ping_status,
            'post_author'    => $this->optin->post_author,
            'post_content'   => $this->optin->post_content,
            'post_excerpt'   => $this->optin->post_excerpt,
            'post_mime_type' => $this->optin->post_mime_type,
            'post_parent'    => 0,
            'post_password'  => $this->optin->post_password,
            'post_status'    => 'publish',
            'post_title'     => $new_title,
            'post_type'      => $this->optin->post_type,
            'post_date'      => date( 'Y-m-d H:i:s', strtotime( '-5 seconds', strtotime( $this->optin->post_date ) ) ),
            'post_date_gmt'  => date( 'Y-m-d H:i:s', strtotime( '-5 seconds', strtotime( $this->optin->post_date_gmt ) ) ),
            'post_name'      => $new_slug
        );

        // Insert the duplicate into the database.
        $new_post_id = wp_insert_post( $new_post );

        // Update the original optin with a reference to the cloned instance.
        $clones = (array) get_post_meta( $this->optin->ID, '_om_has_clone', true );
        $clones = array_filter( $clones );
        array_unshift( $clones, $new_post_id );
        update_post_meta( $this->optin->ID, '_om_has_clone', $clones );

        // Update the original post meta to the duplicate.
        $custom = get_post_custom( $this->optin->ID );
        foreach ( $custom as $key => $value ) {
            if ( empty( $value[0] ) ) {
                continue;
            }

            $value = maybe_unserialize( $value[0] );
            update_post_meta( $new_post_id, $key, $value );
        }

        // Grab the updated meta and store the new clone instance and update the custom CSS slug.
        $new_meta = get_post_meta( $new_post_id, '_om_meta', true );
        if ( ! empty( $original_meta['custom_css'] ) ) {
            $new_meta['custom_css'] = str_replace( $this->optin->post_name, strtolower( $new_slug ), $original_meta['custom_css'] );
        }

        // Make sure the newly split tested optin does not appear live immediately.
        $new_meta['display']['enabled'] = 0;

        // Update the post meta (along with resetting stats).
        update_post_meta( $new_post_id, '_om_meta', $new_meta );
        update_post_meta( $new_post_id, '_om_is_clone', $this->optin->ID );
        update_post_meta( $new_post_id, 'om_counter', (int) 0 );
        update_post_meta( $new_post_id, 'om_conversions', (int) 0 );

        // If the original optin had an image, carry over to the new clone.
        if ( has_post_thumbnail( $this->optin->ID ) ) {
            set_post_thumbnail( $new_post_id, get_post_thumbnail_id( $this->optin->ID ) );
        }

        // If any notes have been added, save them.
        if ( isset( $_POST['om-split-notes'] ) ) {
            update_post_meta( $new_post_id, '_om_split_notes', strip_tags( trim( $_POST['om-split-notes'] ) ) );
        }

        // Split tests cannot be in test mode, so remove any reference to test mode.
        delete_post_meta( $new_post_id, '_om_test_mode' );

        // Provide an API to modify data.
        do_action( 'optin_monster_split_test_optin', $this->optin_id, $this->optin );

        // Flush cache for the optin being split tested.
        Optin_Monster_Common::get_instance()->flush_optin_caches( $this->optin->ID, $this->optin->post_name );
        Optin_Monster_Common::get_instance()->flush_optin_caches( $new_post_id );

    }

    /**
     * Duplicates an optin.
     *
     * @since 2.0.0
     */
    public function duplicate() {

        // Grab the original meta from the optin that is to be duplicated.
        $original_meta = get_post_meta( $this->optin->ID, '_om_meta', true );

        // Generate a new optin object.
        $new_slug = $this->generate_postname_hash() . '-' . $original_meta['type'];
        $new_post = array(
            'menu_order'     => $this->optin->menu_order,
            'comment_status' => $this->optin->comment_status,
            'ping_status'    => $this->optin->ping_status,
            'post_author'    => $this->optin->post_author,
            'post_content'   => $this->optin->post_content,
            'post_excerpt'   => $this->optin->post_excerpt,
            'post_mime_type' => $this->optin->post_mime_type,
            'post_parent'    => 0,
            'post_password'  => $this->optin->post_password,
            'post_status'    => 'publish',
            'post_title'     => ! empty( $this->optin->post_title ) ? $this->optin->post_title . ' ' . __( 'Duplicate', 'optin-monster' ) : $this->optin->post_name . ' ' . __( 'Duplicate', 'optin-monster' ),
            'post_type'      => $this->optin->post_type,
            'post_date'      => date( 'Y-m-d H:i:s', strtotime( '+5 seconds', strtotime( $this->optin->post_date ) ) ),
            'post_date_gmt'  => date( 'Y-m-d H:i:s', strtotime( '+5 seconds', strtotime( $this->optin->post_date_gmt ) ) ),
            'post_name'      => $new_slug
        );

        // Insert the duplicate into the database.
        $new_post_id = wp_insert_post( $new_post );

        // Update the original post meta to the duplicate.
        $custom = get_post_custom( $this->optin->ID );
        foreach ( $custom as $key => $value ) {
            if ( empty( $value[0] ) ) {
                continue;
            }

            $value = maybe_unserialize( $value[0] );
            update_post_meta( $new_post_id, $key, $value );
        }

        // Grab the updated meta and update the custom CSS slug.
        $new_meta = get_post_meta( $new_post_id, '_om_meta', true );
        if ( ! empty( $original_meta['custom_css'] ) ) {
            $new_meta['custom_css'] = str_replace( $this->optin->post_name, strtolower( $new_slug ), $original_meta['custom_css'] );
        }

        // Make sure the newly duplicated optin does not appear live immediately.
        $new_meta['display']['enabled'] = 0;

        // Update the post meta (along with resetting stats).
        update_post_meta( $new_post_id, '_om_meta', $new_meta );
        update_post_meta( $new_post_id, 'om_counter', (int) 0 );
        update_post_meta( $new_post_id, 'om_conversions', (int) 0 );

        // If the original optin had an image, carry over to the new clone.
        if ( has_post_thumbnail( $this->optin->ID ) ) {
            set_post_thumbnail( $new_post_id, get_post_thumbnail_id( $this->optin->ID ) );
        }

        // If this is a split test, make sure to add the new duplicate to the primary optin.
        if ( $this->split ) {
            $clones   = get_post_meta( $this->split, '_om_has_clone', true );
            $clones[] = $new_post_id;
            update_post_meta( $this->split, '_om_has_clone', $clones );
            update_post_meta( $new_post_id, '_om_is_clone', $this->split );
        }

        // Provide an API to modify data.
        do_action( 'optin_monster_duplicate_optin', $this->optin_id, $this->optin );

    }

    /**
     * Resets the stats for an optin.
     *
     * @since 2.0.0
     */
    public function reset_stats() {

        // Reset the stats for the optin.
        update_post_meta( $this->optin_id, 'om_counter', (int) 0 );
        update_post_meta( $this->optin_id, 'om_conversions', (int) 0 );

        // Provide an API to modify data.
        do_action( 'optin_monster_reset_stats', $this->optin_id, $this->optin );

    }

    /**
     * Enables or disables test mode for an optin.
     *
     * @since 2.0.0
     *
     * @return $bool $test True if in test mode, false otherwise.
     */
    public function test_mode() {

        $test = false;
        $mode = get_post_meta( $this->optin_id, '_om_test_mode', true );
        if ( ! empty( $mode ) ) {
            delete_post_meta( $this->optin_id, '_om_test_mode' );
        } else {
            update_post_meta( $this->optin_id, '_om_test_mode', true );
            $test = true;
            
            // Remove the cookie from the browser.
            if ( isset( $_COOKIE['om-' . $this->optin_id] ) ) {
                unset( $_COOKIE['om-' . $this->optin_id] );
                setcookie( 'om-' . $this->optin_id, '', -1, COOKIEPATH, COOKIE_DOMAIN, false );
            }
        }

        // Provide an API to modify data.
        do_action( 'optin_monster_test_mode', $this->optin_id, $this->optin );

        return $test;

    }

    /**
     * Deletes an optin.
     *
     * @since 2.0.0
     */
    public function delete() {

        // Don't do anything if this is a bulk action because both action names are the same for single and bulk.
        if ( isset( $_GET['action'] ) ) {
            return;
        }

        // Delete the requested optin.
        wp_delete_post( $this->optin_id, true );

        // If this is a split test, make sure to remove the split test from the main optin meta, otherwise delete the main optin and all associated split tests.
        if ( $this->split ) {
            $clones = get_post_meta( $this->split, '_om_has_clone', true );
            if ( ( $key = array_search( $this->optin_id, (array) $clones ) ) !== false ) {
                unset( $clones[$key] );
                $clones = array_filter( $clones );
                update_post_meta( $this->split, '_om_has_clone', $clones );
            }
        } else {
            $clones = get_post_meta( $this->optin_id, '_om_has_clone', true );
            foreach ( (array) $clones as $clone ) {
                wp_delete_post( $clone, true );
            }
        }

        // Provide an API to modify data.
        do_action( 'optin_monster_delete_optin', $this->optin_id, $this->optin );

    }

    /**
     * Disables an optin.
     *
     * @since 2.0.0
     */
    public function disable() {

        // Uncheck the enabled setting for the optin to disable it.
        $meta = get_post_meta( $this->optin_id, '_om_meta', true );
        $meta['display']['enabled'] = 0;
        update_post_meta( $this->optin_id, '_om_meta', $meta );

        // Provide an API to modify data.
        do_action( 'optin_monster_disable_optin', $this->optin_id, $this->optin );

    }

    /**
     * Pushes an optin live.
     *
     * @since 2.0.0
     */
    public function live() {

        // Check the enabled setting for the optin to make it go live.
        $meta = get_post_meta( $this->optin_id, '_om_meta', true );
        $meta['display']['enabled'] = 1;
        update_post_meta( $this->optin_id, '_om_meta', $meta );

        // Provide an API to modify data.
        do_action( 'optin_monster_live_optin', $this->optin_id, $this->optin );

    }

    /**
     * Makes a split test the primary optin.
     *
     * @since 2.0.0
     */
    public function make_primary() {

        // Prepare variables.
        $new_primary      = $this->optin->ID;
        $original_primary = $this->split;
        $original_clones  = get_post_meta( $original_primary, '_om_has_clone', true );

        // Remove the split that is about to be made primary from the clones array.
        if ( ( $key = array_search( $new_primary, (array) $original_clones ) ) !== false ) {
            unset( $original_clones[$key] );
            $original_clones = array_filter( $original_clones );
        }
        
        // Add the original primary as a clone of the new primary, set it as a split test and deactivate.
        $original_clones[]                   = $original_primary;
        $original_meta                       = get_post_meta( $original_primary, '_om_meta', true );
        $original_meta['display']['enabled'] = 0;
        update_post_meta( $original_primary, '_om_meta', $original_meta );
        update_post_meta( $original_primary, '_om_is_clone', $new_primary );
        delete_post_meta( $original_primary, '_om_test_mode' );

        // Remove the meta key for saying the current split is a split test, and add the clones to it.
        delete_post_meta( $new_primary, '_om_is_clone' );
        update_post_meta( $new_primary, '_om_has_clone', $original_clones );

        // Update each clone so that it references the new parent optin.
        foreach ( (array) $original_clones as $clone ) {
            update_post_meta( $clone, '_om_is_clone', $new_primary );
        }

        // Provide an API to modify data.
        do_action( 'optin_monster_primary_optin', $this->optin_id, $this->optin, $this->split );

        // Flush any optin caches now since we will have to do a redirect.
        Optin_Monster_Common::get_instance()->flush_optin_caches( $this->optin_id, ( is_object( $this->optin ) ? $this->optin->post_name : '' ) );
        if ( $this->split ) {
            Optin_Monster_Common::get_instance()->flush_optin_caches( $this->split );
        }

        // Now we need to do a redirect because the old primary optin no longer exists.
        wp_redirect( add_query_arg( array( 'om_primary' => true, 'om_title' => urlencode( $this->get_optin_title() ) ), admin_url( 'admin.php?page=optin-monster-settings' ) ) );
        exit;

    }
    
    /**
     * Clears local cookies set for a particular optin.
     *
     * @since 2.0.0
     */
    public function clear_cookies() {

        // Clear any cookies set for the optin (including split tests).
        $optins = isset( $_GET['optin'] ) ? (array) $_GET['optin'] : false;
        if ( ! $optins ) {
            return;
        }
        
        foreach ( $optins as $id ) {
            setcookie( 'om-' . $id, '', -1, COOKIEPATH, COOKIE_DOMAIN, false );
            $clones = get_post_meta( $id, '_om_has_clone', true );
            foreach ( (array) $clones as $clone ) {
                setcookie( 'om-' . $clone, '', -1, COOKIEPATH, COOKIE_DOMAIN, false );
            }
        }
        
        // Provide an API to modify data.
        do_action( 'optin_monster_clear_cookies', $optins );

    }

    /**
     * Outputs any admin notices for any actions that have occurred.
     *
     * @since 2.0.0
     */
    public function notices() {

        foreach ( $this->notices as $id => $notice ) {
            echo '<div class="' . sanitize_html_class( $id, 'updated' ) . '"><p>' . $notice . '</p></div>';
        }

    }

    /**
     * Retrieves the optin title or slug if the title does not exist.
     *
     * @since 2.0.0
     *
     * @return string The optin title or slug.
     */
    public function get_optin_title() {

        return ! empty( $this->optin->post_title ) ? $this->optin->post_title : $this->optin->post_name;

    }

    /**
     * Generates a postname hash for an optin.
     *
     * @since 2.0.0
     *
     * @return string The optin slug.
     */
    public function generate_postname_hash() {

        return optin_monster_ajax_generate_postname_hash();

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Actions object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Actions ) ) {
            self::$instance = new Optin_Monster_Actions();
        }

        return self::$instance;

    }

}

// Load the actions class.
$optin_monster_actions = Optin_Monster_Actions::get_instance();