<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package optin_monster
 * @author  Thomas Griffin
 */
class optin_monster_shortcode {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Holds the optin data.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $data;

    /**
     * Holds optin IDs for init firing checks.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $done = array();

    /**
     * Iterator for optins on the page.
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $counter = 1;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = optin_monster::get_instance();

        // Load hooks and filters.
        add_shortcode( 'optin-monster-shortcode', array( $this, 'shortcode' ) );

    }

    /**
     * Creates the shortcode for the plugin.
     *
     * @since 1.0.0
     *
     * @global object $optin_monster_ajax The OptinMonster ajax object.
     *
     * @param array $atts Array of shortcode attributes.
     * @return string     The optin output.
     */
    public function shortcode( $atts ) {

        // If no attributes have been passed, return early.
        $optin_id = $data = false;
        if ( empty( $atts ) ) {
            return;
        } else if ( isset( $atts['id'] ) ) {
            $optin = get_posts( array( 'post_type' => 'optin', 'name' => $atts['id'], 'posts_per_page' => 1 ) );
            if ( ! $optin ) {
                return;
            } else {
                $optin = $optin[0];
                $slug  = $atts['id'];
                $meta  = get_post_meta( $optin->ID, '_om_meta', true );
                $data  = $this->base->get_optin_monster( $optin, $slug, $meta, true );
            }
        }

        // If there is no data to output or the gallery is inactive, do nothing.
        if ( ! $data || empty( $data ) ) {
            return;
        }

        // Allow the data to be filtered before it is stored and used to create the gallery output.
        $data = apply_filters( 'optin_monster_pre_data', $data, $optin_id );

        // If the optin is not enabled, pass over to the next optin.
        if ( empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] ) {
            return;
        }

        // Prepare variables.
        $this->data[] = $data;

        // Enqueue main optin script.
        if ( ! wp_script_is( 'om-api-script', 'registered' ) ) {
            wp_register_script( 'om-api-script', OPTINMONSTER_APIURL, array( 'jquery' ), $this->base->version, true );
            wp_enqueue_script( 'om-api-script' );
            wp_localize_script( 'om-api-script', 'om_api_object', array( 'ajaxurl' => add_query_arg( 'optin-monster-ajax-route', true, trailingslashit( get_home_url() ) ) ) );
        }

        // Remove this optin from the init items.
        add_filter( 'optinmonster_output', array( $this, 'remove_optin' ) );

        // Count the impression.
        $counter = get_post_meta( $optin->ID, 'om_counter', true );
        update_post_meta( $optin->ID, 'om_counter', (int) $counter + 1 );

        // Load gallery init code in the footer.
        add_action( 'wp_footer', array( $this, 'optin_shortcode_init' ), 1000 );

        // Return the optin HTML.
        require_once plugin_dir_path( $this->base->file ) . 'inc/templates/template.php';
        $ssl   = ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443;
		$theme = new optin_monster_template( $meta['type'], $meta['theme'], $optin->post_name, $optin->ID, 'live', $ssl );
        return apply_filters( 'optin_monster_shortcode_output', $theme->build_optin(), $data );

    }

    /**
     * Removes the optin from the output possibilities in the footer.
     *
     * @since 1.0.0
     *
     * @param array $optins Optins to be loaded on the page.
     */
    public function remove_optin( $optins ) {

        foreach ( $this->data as $array => $data ) {
            if ( isset( $optins[$data['hash']] ) ) {
                unset( $optins[$data['hash']] );
            }
        }

        return $optins;

    }

    /**
     * Outputs the gallery init script in the footer.
     *
     * @since 1.0.0
     */
    public function optin_shortcode_init() {

        ?>
        <script type="text/javascript">jQuery(document).ready(function($){<?php ob_start();
            do_action( 'optin_monster_api_start_global' );
            foreach ( $this->data as $data ) :
                // Prevent multiple init scripts for the same gallery ID.
                if ( in_array( $data['hash'], $this->done ) ) {
                    continue;
                }
                $this->done[] = $data['hash'];
                $data['html_manual'] = true;

                do_action( 'optin_monster_api_start', $data ); ?>

                var custom_om_output = new OptinMonster();
                custom_om_output.manualInit(<?php echo json_encode( $data ); ?>);

            <?php do_action( 'optin_monster_api_end', $data );
            endforeach;

            // Minify before outputting to improve page load time.
            do_action( 'optin_monster_api_end_global' );
            $clean = str_replace( array( "\r\n", "\r", "\t", "\n", '  ', '    ', '     ' ), '', ob_get_clean() );
            echo $clean; ?>});</script>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The optin_monster_shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof optin_monster_shortcode ) ) {
            self::$instance = new optin_monster_shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
global $optin_monster_shortcode;
$optin_monster_shortcode = optin_monster_shortcode::get_instance();