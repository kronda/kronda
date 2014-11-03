<?php
/**
 * Shortcode class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Shortcode {

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
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Load hooks and filters.
        add_shortcode( 'optin-monster', array( $this, 'shortcode' ) );
        add_shortcode( 'optin-monster-shortcode', array( $this, 'shortcode_v1' ) );
        add_filter( 'widget_text', 'do_shortcode' );

    }

    /**
     * Creates the shortcode for the plugin.
     *
     * @since 2.0.0
     *
     * @global object $post The current post object.
     *
     * @param array $atts Array of shortcode attributes.
     * @return string     The optin output.
     */
    public function shortcode( $atts ) {

        global $post;

        $optin_id = false;
        if ( isset( $atts['id'] ) ) {
            $optin_id = (int) $atts['id'];
        } else if ( isset( $atts['slug'] ) ) {
            $optin = get_page_by_path( $atts['slug'], OBJECT, 'optin' );
            if ( $optin ) {
                $optin_id = $optin->ID;
            }
        } else {
            // A custom attribute must have been passed. Allow it to be filtered to grab the optin ID from a custom source.
            $optin_id = apply_filters( 'optin_monster_custom_optin_id', false, $atts, $post );
        }

        // Allow the optin ID to be filtered before it is stored and used to create the optin output.
        $optin_id = apply_filters( 'optin_monster_pre_optin_id', $optin_id, $atts, $post );

        // If there is no optin, do nothing.
        if ( ! $optin_id ) {
            return false;
        }

        // If we are in a preview state, the optin needs to match the one requested, otherwise return false.
    	if ( Optin_Monster_Output::get_instance()->is_preview() ) {
    		if ( Optin_Monster_Output::get_instance()->optin_id && Optin_Monster_Output::get_instance()->optin_id !== $optin_id || ! empty( Optin_Monster_Output::get_instance()->data[$optin_id] ) ) {
    			return false;
    		}
    	}
    	
    	// Track the optin.
        Optin_Monster_Output::get_instance()->track_manual( $optin_id );

		// Return the output.
        return Optin_Monster_Output::get_instance()->get_optin_monster( $optin_id );

    }

    /**
     * Backwards compat shortcode for v1.
     *
     * @since 2.0.0
     *
     * @global object $post The current post object.
     *
     * @param array $atts Array of shortcode attributes.
     * @return string     The optin output.
     */
    public function shortcode_v1( $atts ) {

        // Run the v2 implementation.
        $atts['slug'] = $atts['id'];
        unset( $atts['id'] );
        return $this->shortcode( $atts );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Shortcode ) ) {
            self::$instance = new Optin_Monster_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$optin_monster_shortcode = Optin_Monster_Shortcode::get_instance();