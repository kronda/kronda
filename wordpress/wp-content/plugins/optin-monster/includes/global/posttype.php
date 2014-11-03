<?php
/**
 * Posttype class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Posttype {

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

        // Build out the post type arguments.
        $args = apply_filters( 'optin_monster_post_type_args',
            array(
                'labels'              => array(),
                'public'              => false,
                'exclude_from_search' => false,
                'show_ui'             => false,
                'show_in_admin_bar'   => false,
                'rewrite'             => false,
                'query_var'           => false,
                'menu_position'       => apply_filters( 'optin_monster_post_type_menu_position', 279 ),
                'menu_icon'           => plugins_url( 'assets/css/images/menu-icon@2x.png', $this->base->file ),
                'supports'            => array( 'title' )
            )
        );

        // Register the post type with WordPress.
        register_post_type( 'optin', $args );

        // Register any image sizes that will be needed for optin themes.
        $this->register_image_sizes();

    }

    /**
     * Register necessary image sizes for optin themes.
     *
     * @since 2.0.0
     */
    public function register_image_sizes() {

        // Register our lightbox image sizes since they are included with the plugin.
        add_image_size( $this->base->plugin_slug . '-lightbox-theme-balance', 225, 175, true );
        add_image_size( $this->base->plugin_slug . '-lightbox-theme-bullseye', 700, 350, true );
        add_image_size( $this->base->plugin_slug . '-lightbox-theme-case-study', 280, 245, true );
        add_image_size( $this->base->plugin_slug . '-lightbox-theme-clean-slate', 230, 195, true );
        add_image_size( $this->base->plugin_slug . '-lightbox-theme-transparent', 700, 450, true );

        // Add hook to register image sizes for other optin types.
        do_action( 'optin_monster_register_image_sizes', $this );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Posttype object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Posttype ) ) {
            self::$instance = new Optin_Monster_Posttype();
        }

        return self::$instance;

    }

}

// Load the posttype class.
$optin_monster_posttype = Optin_Monster_Posttype::get_instance();