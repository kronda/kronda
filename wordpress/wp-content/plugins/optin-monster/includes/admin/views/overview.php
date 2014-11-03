<?php
/**
 * Views overview class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Views_Overview {

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

        // Load actions for this view.
        add_action( 'optin_monster_tab_optins', array( $this, 'optins_tab' ) );
        add_action( 'optin_monster_tab_addons', array( $this, 'addons_tab' ) );
        add_action( 'optin_monster_tab_integrations', array( $this, 'integrations_tab' ) );
        add_action( 'optin_monster_tab_settings', array( $this, 'settings_tab' ) );

    }

    /**
     * Outputs the optin view.
     *
     * @since 2.0.0
     */
    public function view() {

        ?>
        <h2><?php echo esc_html( get_admin_page_title() ); ?> <a class="add-new-h2" href="<?php echo add_query_arg( 'om_view', 'new', admin_url( 'admin.php?page=optin-monster-settings' ) ); ?>" title="<?php esc_attr_e( 'Add new optin', 'optin-monster' ); ?>"><?php _e( 'Add New', 'optin-monster' ); ?></a></h2>
        <div class="optin-monster optin-monster-overview optin-monster-clear">
            <div id="optin-monster-tabs" class="optin-monster-clear">
                <h2 id="optin-monster-tabs-nav" class="optin-monster-clear nav-tab-wrapper">
                <?php $i = 0; foreach ( (array) $this->get_optin_monster_tab_nav() as $id => $title ) : $class = 0 === $i ? 'optin-monster-active nav-tab-active' : ''; ?>
                    <a class="nav-tab <?php echo $class; ?>" href="#optin-monster-tab-<?php echo $id; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
                <?php $i++; endforeach; ?>
                	<span class="om-version"><?php printf( __( 'v%s', 'optin-monster' ), $this->base->version ); ?></span>
                </h2>
                <?php $i = 0; foreach ( (array) $this->get_optin_monster_tab_nav() as $id => $title ) : $class = 0 === $i ? 'optin-monster-active' : ''; ?>
                <div id="optin-monster-tab-<?php echo $id; ?>" class="optin-monster-tab optin-monster-clear <?php echo $class; ?>">
                    <?php do_action( 'optin_monster_tab_' . $id ); ?>
                </div>
                <?php $i++; endforeach; ?>
            </div>
        </div>
        <?php

    }

    /**
     * Callback for getting all of the settings tabs for OptinMonster.
     *
     * @since 2.0.0
     *
     * @return array Array of tab information.
     */
    public function get_optin_monster_tab_nav() {

        $tabs = array(
            'optins'       => __( 'Optins', 'optin-monster' ), // This tab is required. DO NOT REMOVE VIA FILTERING.
            'addons'       => __( 'Addons', 'optin-monster' ),
            'integrations' => __( 'Integrations', 'optin-monster' ),
            'settings'     => __( 'Settings', 'optin-monster' ),
        );
        $tabs = apply_filters( 'optin_monster_settings_tab_nav', $tabs );

        return $tabs;

    }

    /**
     * Callback for displaying the UI for the optins tab.
     *
     * @since 2.0.0
     */
    public function optins_tab() {

        require plugin_dir_path( $this->base->file ) . 'includes/admin/ui/optins.php';

    }

    /**
     * Callback for displaying the UI for the addons tab.
     *
     * @since 2.0.0
     */
    public function addons_tab() {

        require plugin_dir_path( $this->base->file ) . 'includes/admin/ui/addons.php';

    }

    /**
     * Callback for displaying the UI for the integrations tab.
     *
     * @since 2.0.0
     */
    public function integrations_tab() {

        require plugin_dir_path( $this->base->file ) . 'includes/admin/ui/integrations.php';

    }

    /**
     * Callback for displaying the UI for the settings tab.
     *
     * @since 2.0.0
     */
    public function settings_tab() {

        require plugin_dir_path( $this->base->file ) . 'includes/admin/ui/settings.php';

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Views_Overview object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Views_Overview ) ) {
            self::$instance = new Optin_Monster_Views_Overview();
        }

        return self::$instance;

    }

}

// Load the views overview class.
$optin_monster_views_overview = Optin_Monster_Views_Overview::get_instance();