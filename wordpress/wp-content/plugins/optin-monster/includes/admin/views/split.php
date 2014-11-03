<?php
/**
 * Views split class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Views_Split {

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
     * Holds the optin ID .
     *
     * @since 2.0.0
     *
     * @var bool|int
     */
    public $optin_id = false;

    /**
     * Holds the optin object.
     *
     * @since 2.0.0
     *
     * @var bool|object
     */
    public $optin = false;

    /**
     * Holds the optin meta.
     *
     * @since 2.0.0
     *
     * @var bool|array
     */
    public $meta = false;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Set the optin ID, object and meta properties.
        $this->optin_id = isset( $_GET['om_optin_id'] ) ? $_GET['om_optin_id'] : $_POST['id'];
        $this->optin    = get_post( $this->optin_id );
        $this->meta     = get_post_meta( $this->optin_id, '_om_meta', true );

        // The optins overview screen needs WP_List_Table. If it is not in scope, bring it into scope now.
        if ( ! class_exists( 'WP_List_Table' ) ) {
            require ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }

        // Load in the list table extension class.
        require plugin_dir_path( $this->base->file ) . 'includes/admin/ui/split-table.php';


    }

    /**
     * Outputs the optin view.
     *
     * @since 2.0.0
     */
    public function view() {

        // Prepare the list table.
        $split_table = Optin_Monster_UI_Split_Table::get_instance();
        $split_table->prepare_items();

        ?>
        <h2><?php printf( __( 'Manage Split Tests for %s', 'optin-monster' ), ( ! empty( $this->optin->post_title ) ? $this->optin->post_title : $this->optin->post_name ) ); ?> <a class="add-new-h2 om-add-split" href="#" title="<?php esc_attr_e( 'Add new split test for this optin', 'optin-monster' ); ?>"><?php _e( 'Add New Split Test', 'optin-monster' ); ?></a></h2>
        <div class="optin-monster optin-monster-split optin-monster-clear">
            <p class="om-overview-button"><a class="button button-secondary" href="<?php echo add_query_arg( array( 'om_view' => 'overview' ), admin_url( 'admin.php?page=optin-monster-settings' ) ); ?>" title="<?php esc_attr_e( 'Back to optin overview', 'optin-monster' ); ?>"><?php _e( 'Back to Overview', 'optin-monster' ); ?></a>
            <form id="optin-monster-optins-table" method="get" action="<?php echo add_query_arg( 'page', 'optin-monster-settings', admin_url( 'admin.php' ) ); ?>">
                <input type="hidden" name="post_type" value="optin" />
                <input type="hidden" name="page" value="optin-monster-settings" />
                <?php $split_table->display(); ?>
            </form>
            <div id="optin-monster-add-split">
                <div class="om-split-header">
                    <h2><?php _e( 'Add New Split Test', 'optin-monster' ); ?> <a href="#" class="om-close-split">&#215;</a></h2>
                </div>
                <div class="om-split-content">
                    <form method="post" action="<?php echo add_query_arg( array( 'om_view' => 'split', 'om_action' => 'split', 'om_optin_id' => $this->optin_id ), admin_url( 'admin.php?page=optin-monster-settings' ) ); ?>">
                        <p>
                            <label for="om-split-title"><?php _e( 'Split Test Name', 'optin-monster' ); ?></label>
                            <input type="text" id="om-split-title" name="om-split-title" value="" placeholder="<?php esc_attr_e( 'Enter your split test name here...', 'optin-monster' ); ?>" tabindex="456" />
                        </p>
                        <p>
                            <label for="om-split-notes"><?php _e( 'Notes for Split Test', 'optin-monster' ); ?></label>
                            <textarea id="om-split-notes" name="om-split-notes" placeholder="<?php esc_attr_e( 'Enter your split test notes here (e.g. changed title and colors)...', 'optin-monster' ); ?>" tabindex="457" rows="7"></textarea><br>
                            <span class="description"><?php _e( 'Notes are useful for keeping track of the changes between each split test you create.', 'optin-monster' ); ?></span>
                        </p>
                        <p>
                            <input type="submit" class="button button-primary" id="om-split-submit" value="<?php esc_attr_e( 'Create Split Test', 'optin-monster' ); ?>" tabindex="458" />
                        </p>
                    </form>
                    <div class="om-split-overlay"></div>
                </div>
            </div>
        </div>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Views_Split object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Views_Split ) ) {
            self::$instance = new Optin_Monster_Views_Split();
        }

        return self::$instance;

    }

}

// Load the views split class.
$optin_monster_views_split = Optin_Monster_Views_Split::get_instance();