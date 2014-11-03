<?php
/**
 * Admin UI optins class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_UI_Optins {

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
     * Holds the tab slug.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $tab = 'optins';

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // The optins overview screen needs WP_List_Table. If it is not in scope, bring it into scope now.
        if ( ! class_exists( 'WP_List_Table' ) ) {
            require ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }

        // Load in the list table extension class.
        require plugin_dir_path( $this->base->file ) . 'includes/admin/ui/optins-table.php';

        // Display the UI.
        $this->display();

    }

    /**
     * Displays the UI view.
     *
     * @since 2.0.0
     */
    public function display() {

        // Prepare the list table.
        $optins_table = Optin_Monster_UI_Optins_Table::get_instance();
        $optins_table->prepare_items();

        // Output the list table.
        ?>
        <form id="optin-monster-optins-table" method="get" action="<?php echo add_query_arg( 'page', 'optin-monster-settings', admin_url( 'admin.php' ) ); ?>">
            <input type="hidden" name="post_type" value="optin" />
            <input type="hidden" name="page" value="optin-monster-settings" />
            <?php $optins_table->display(); ?>
        </form>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Posttype object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_UI_Optins ) ) {
            self::$instance = new Optin_Monster_UI_Optins();
        }

        return self::$instance;

    }

}

// Load the admin UI settings class.
$optin_monster_ui_settings = Optin_Monster_UI_Optins::get_instance();