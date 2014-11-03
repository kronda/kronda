<?php
/**
 * Views preview class.
 *
 * @since 2.0.2.1
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Views_Preview {

    /**
     * Holds the class object.
     *
     * @since 2.0.2.1
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 2.0.2.1
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 2.0.2.1
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 2.0.2.1
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

    }

    /**
     * Prepares the admin preview hooks/filters.
     *
     * @since 2.0.2.1
     */
    public function view() {

        $meta = get_post_meta( Optin_Monster_Output::get_instance()->optin_id, '_om_meta', true );
        $type = isset( $meta['type'] ) ? $meta['type'] : '';

        // Load in different areas based on the type of optin.
        if ( 'sidebar' == $type || 'post' == $type ) {
            $this->do_preview();
        } else {
            add_action( 'admin_footer', array( $this, 'do_preview' ), 9999 );
        }

    }

    /**
     * Outputs the optin view.
     *
     * @since 2.0.2.1
     */
    public function do_preview() {

        // Filter the data for preview mode.
        add_filter( 'optin_monster_data', array( Optin_Monster_Output::get_instance(), 'preview_data' ), 9999 );
        $optin = Optin_Monster_Output::get_instance()->get_optin_monster( Optin_Monster_Output::get_instance()->optin_id );
        remove_filter( 'optin_monster_data', array( Optin_Monster_Output::get_instance(), 'preview_data' ), 9999 );

        // Output the optin.
        if ( $optin ) {
            echo $optin;
        }

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.2.1
     *
     * @return object The Optin_Monster_Views_Preview object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Views_Preview ) ) {
            self::$instance = new Optin_Monster_Views_Preview();
        }

        return self::$instance;

    }

}

// Load the views preview class.
$optin_monster_views_preview = Optin_Monster_Views_Preview::get_instance();