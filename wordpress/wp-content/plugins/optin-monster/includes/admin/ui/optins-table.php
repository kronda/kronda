<?php
/**
 * Admin UI optins list table class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_UI_Optins_Table extends WP_List_Table {

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
     * Number of results to show per page.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $per_page = 60;

    /**
     * URL of this page.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $base_url;

    /**
     * Total number of optins.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $total;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Bring globals into scope for parent.
        global $status, $page;

        // Utilize the parent constructor to build the main class properties.
        parent::__construct(
            array(
                'singular' => 'optin',
                'plural'   => 'optins',
                'ajax'     => false
            )
        );

        // Set some of the class properties.
        $this->total    = $this->base->get_optins() ? count( $this->base->get_optins() ) : 0;
        $this->base_url = add_query_arg( 'page', 'optin-monster-settings', admin_url( 'admin.php' ) );

        // Process any bulk actions.
        $this->process_bulk_actions();

        // Load the track datastore interface.
	    if ( ! class_exists( 'Optin_Monster_Track_Datastore' ) ) {
		    require plugin_dir_path( $this->base->file ) . 'includes/global/track-datastore.php';
	    }

    }

    /**
     * Retrieve the optin table columns.
     *
     * @since 2.0.0
     *
     * @return array $columns Array of all the list table columns.
     */
    public function get_columns() {

        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'name'        => __( 'Name', 'optin-monster' ),
            'slug'        => __( 'Slug', 'optin-monster' ),
            'impressions' => __( 'Impressions', 'optin-monster' ),
            'conversions' => __( 'Conversions', 'optin-monster' ),
            'percent'     => __( '% Conversions', 'optin-monster' ),
            'status'      => __( 'Status', 'optin-monster' ),
            'settings'    => __( 'Settings', 'optin-monster' )
        );

        return apply_filters( 'optin_monster_table_columns', $columns );

    }

    /**
     * Render the checkbox column.
     *
     * @since 2.0.0
     *
     * @param array $optin Contains all the data for the checkbox column.
     * @return string      Displays a checkbox for bulk actions.
     */
    public function column_cb( $optin ) {

        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            'optin',
            $optin->ID
        );

    }

    /**
     * Renders the rest of the columns in the list table.
     *
     * @since 2.0.0
     *
     * @param object $optin       The optin object.
     * @param string $column_name The name of the column.
     * @return string $value      The value of the column.
     */
    public function column_default( $optin, $column_name ) {

        // Load the track datastore interface.
        $track_store = new Optin_Monster_Track_Datastore( $optin->ID );

        switch ( $column_name ) {
            case 'name' :
                $value = ! empty( $optin->post_title ) ? $optin->post_title : $optin->post_name;
                break;
            case 'slug' :
                $test  = get_post_meta( $optin->ID, '_om_test_mode', true );
                $value = ! empty( $test ) ? '<span class="om-optin-slug">' . $optin->post_name . '</span><br><span class="om-test-mode-title">' . __( 'Test Mode', 'optin-monster' ) . '</span>' : '<span class="om-optin-slug">' . $optin->post_name . '</span>';
                break;
            case 'impressions' :
                $value = number_format( $track_store->get_impressions() );
                break;
            case 'conversions' :
                $value = number_format( $track_store->get_conversions() );
                break;
            case 'percent' :
                $imp   = $track_store->get_impressions();
                $conv  = $track_store->get_conversions();
                $value = ( 0 == $conv || 0 == $imp ) ? '0' : number_format( ($conv/$imp) * 100, 2 ) . '&#37;';
                break;
            case 'status' :
                $meta   = get_post_meta( $optin->ID, '_om_meta', true );
                $value  = Optin_Monster_Common::get_instance()->get_optin_status( $meta );
                $value .= '<div class="row-actions">';
                    $value .= Optin_Monster_Common::get_instance()->get_optin_status_link( $meta, $optin->ID );
                $value .= '</div>';
                break;
            case 'settings' :
                $link  = '<a href="#" class="om-settings-button" title="' . esc_attr__( 'Optin settings', 'optin-monster' ) . '"><i class="fa fa-cog"></i></a>';
                $link .= '<div class="om-settings-popover">';
                    $link .= $this->get_settings_actions( $optin );
                $link .= '</div>';
                $value = $link;
                break;
            default:
                $value = apply_filters( 'optin_monster_column_value', '', $optin, $column_name );
                break;

        }

        return apply_filters( 'optin_monster_table_column', $value, $optin, $column_name );

    }

    /**
     * Render the Name column with custom action links.
     *
     * @since 2.0.0
     *
     * @param array $optin The optin object.
     * @return string      Data shown in the column.
     */
    public function column_name( $optin ) {

        // Prepare variables.
        $name        = ! empty( $optin->post_title ) ? $optin->post_title : $optin->post_name;
        $name        = '<a class="row-title" href="' . add_query_arg( array( 'om_view' => 'edit', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Edit this optin', 'optin-monster' ) . '"><strong>' . $name . '</strong></a>';
        $row_actions = array();

        // Build all of the row action links.
        $row_actions['edit'] = '<a href="' . add_query_arg( array( 'om_view' => 'edit', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Edit this optin', 'optin-monster' ) . '">' . __( 'Edit', 'optin-monster' ) . '</a>';
        $row_actions['delete'] = '<a class="submitdelete" href="' . add_query_arg( array( 'om_action' => 'delete', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Delete this optin', 'optin-monster' ) . '">' . __( 'Delete', 'optin-monster' ) . '</a>';

        // Build the row action links and return the value.
        $value = $name . $this->row_actions( $row_actions );
        return apply_filters( 'optin_monster_table_column_name', $value, $optin );

    }

    /**
     * Generates content for a single row of the table.
     *
     * @since 2.0.0
     *
     * @param object $item The current item.
     */
    function single_row( $item ) {

        static $row_class = '';
        $row_class = empty( $row_class ) ? 'alternate' : '';

        // Check for test mode.
        $test_mode = get_post_meta( $item->ID, '_om_test_mode', true );
        $test_mode = ! empty( $test_mode ) ? ' om-test-mode' : '';

        echo '<tr id="' . $item->ID . '" class="' . trim( $row_class . $test_mode ) . '">';
            $this->single_row_columns( $item );
        echo '</tr>';

    }

    /**
     * Retrieve the bulk actions.
     *
     * @since 2.0.0
     *
     * @return array $actions Array of the bulk actions.
     */
    public function get_bulk_actions() {

        $actions = array(
            'delete'      => __( 'Delete', 'optin-monster' ),
            'reset-stats' => __( 'Reset Stats', 'optin-monster' ),
            'cookies'     => __( 'Clear Cookies', 'optin-monster' )
        );

        return apply_filters( 'optin_monster_table_bulk_actions', $actions );

    }

    /**
     * Processes any bulk optin actions.
     *
     * @since 2.0.0
     */
    public function process_bulk_actions() {

        // Prepare variables.
        $ids    = isset( $_GET['optin'] ) ? $_GET['optin'] : false;
        $action = $this->current_action();

        // If the $ids variable is not an array, cast it as such.
        if ( ! is_array( $ids ) ) {
            $ids = array( $ids );
        }

        // If there are no ids or the action is empty, return early.
        if ( empty( $ids ) || empty( $action ) ) {
            return;
        }

        // Loop through the IDs and process the action.
        foreach ( $ids as $id ) {
            switch ( $action ) {
                case 'delete' :
                    wp_delete_post( $id, true );

                    // Delete any split tests if they exist.
                    $clones = $this->base->get_split_tests( $id );
                    if ( $clones ) {
                        foreach ( $clones as $clone ) {
                            wp_delete_post( $clone->ID, true );
                        }
                    }
                    break;
                case 'reset-stats' :
                    update_post_meta( $id, 'om_counter', (int) 0 );
                    update_post_meta( $id, 'om_conversions', (int) 0 );
                    break;
            }

            // Provide a hook to do extra things in the bulk action.
            do_action( 'optin_monster_table_bulk_action', $action, $id );

            // Flush any optin caches.
            Optin_Monster_Common::get_instance()->flush_optin_caches( $id );
        }

    }

    /**
     * Grabs all the optin data necessary for the table UI.
     *
     * @since 2.0.0
     */
    public function optins_data() {

        return Optin_Monster::get_instance()->get_optins();

    }

    /**
     * Setup the final data for the optins table.
     *
     * @since 2.0.0
     */
    public function prepare_items() {

        // Reset the internal query vars.
        wp_reset_vars( array( 'action', 'optin', 'orderby', 'order', 's' ) );

        // Prepare variables.
        $columns  = $this->get_columns();
        $hidden   = array(); // No hidden columns.
        $sortable = array(); // No sortable columns.
        $data     = $this->optins_data();

        // Set parent class properties.
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items           = $data;

        // Set the pagination args for the table.
        $this->set_pagination_args(
            array(
                'total_items' => $this->total,
                'per_page'    => $this->per_page,
                'total_pages' => ceil( $this->total / $this->per_page )
            )
        );

    }

    /**
     * Returns the action links for each optin.
     *
     * @since 2.0.0
     *
     * @param object $optin The current optin object.
     * @return string       A string of action links for the optin.
     */
    public function get_settings_actions( $optin ) {

        $test  = get_post_meta( $optin->ID, '_om_test_mode', true );
        $links = array();

        // Handle the modify link.
        $links['modify'] = '<li><a href="' . add_query_arg( array( 'om_view' => 'edit', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Edit this optin', 'optin-monster' ) . '">' . __( 'Modify', 'optin-monster' ) . '</a></li>';

        // Handle the split test link.
        $links['split'] = '<li><a href="' . add_query_arg( array( 'om_view' => 'split', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Manage split tests for this optin', 'optin-monster' ) . '">' . __( 'Split Tests', 'optin-monster' ) . '</a></li>';

        // Handle the duplication link.
        $links['duplicate'] = '<li><a href="' . add_query_arg( array( 'om_action' => 'duplicate', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Duplicate this optin', 'optin-monster' ) . '">' . __( 'Duplicate', 'optin-monster' ) . '</a></li>';

        // Handle the reset stats link.
        $links['reset'] = '<li><a href="' . add_query_arg( array( 'om_action' => 'reset', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Reset the stats for this optin', 'optin-monster' ) . '">' . __( 'Reset Stats', 'optin-monster' ) . '</a></li>';

        // Handle the test mode link.
        if ( $test ) {
            $links['test'] = '<li><a href="' . add_query_arg( array( 'om_action' => 'test', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Disable test mode for this optin', 'optin-monster' ) . '">' . __( 'Disable Test Mode', 'optin-monster' ) . '</a></li>';
        } else {
            $links['test'] = '<li><a href="' . add_query_arg( array( 'om_action' => 'test', 'om_optin_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-settings' ) ) . '" title="' . esc_attr__( 'Enable test mode for this optin', 'optin-monster' ) . '">' . __( 'Enable Test Mode', 'optin-monster' ) . '</a></li>';
        }

        // Handle the custom optin slug output.
        $links['slug'] = '<li><span class="om-unique-slug"><span class="description">' . __( 'Unique Optin Slug', 'optin-monster' ) . '</span><br /><code>' . $optin->post_name . '</code></span></li>';

        // Allow the links to be filtered.
        $links = apply_filters( 'optin_monster_action_links', $links, $optin );

        // Return and allow the final output to be filtered.
        return apply_filters( 'optin_monster_action_links_output', '<ul>' . implode( "\n", (array) $links ) . '</ul>', $optin );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_UI_Optins_Table object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_UI_Optins_Table ) ) {
            self::$instance = new Optin_Monster_UI_Optins_Table();
        }

        return self::$instance;

    }

}