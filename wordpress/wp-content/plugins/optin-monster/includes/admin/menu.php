<?php
/**
 * Menu admin class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Menu_Admin {

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
     * Holds the admin menu slug.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $hook;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Set the page UI for the screen (and possibly define an iframe request if using admin previews).
        $this->view = isset( $_GET['om_view'] ) ? $_GET['om_view'] : 'overview';
        if ( $this->is_admin_preview() && ! defined( 'IFRAME_REQUEST' ) ) {
            define( 'IFRAME_REQUEST', true );
        }

        // Build the custom admin page for managing optins.
        add_action( 'admin_menu', array( $this, 'menu' ) );

        // Add the settings menu item to the Plugins table.
        add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'optin-monster.php' ), array( $this, 'settings_link' ) );

    }

    /**
     * Registers the admin menu for managing the OptinMonster optins.
     *
     * @since 2.0.0
     */
    public function menu() {

        $this->hook = add_menu_page(
            __( 'OptinMonster Overview', 'optin-monster' ),
            __( 'OptinMonster', 'optin-monster' ),
            apply_filters( 'optin_monster_menu_cap', 'manage_options' ),
            'optin-monster-settings',
            array( $this, 'menu_ui' ),
            plugins_url( 'assets/css/images/menu-icon@2x.png', $this->base->file ),
            279
        );

        // Load global assets if the hook is successful.
        if ( $this->hook ) {
            add_action( 'load-' . $this->hook, array( $this, 'optin_assets' ) );
            add_action( 'admin_head', array( $this, 'optin_menu_icon' ) );

            // Enqueue custom pointer styles and scripts.
            add_action( 'admin_enqueue_scripts', array( $this, 'pointer_assets' ) );
        }

        // If the hook is successful and we are on the overview screen, possibly refresh our addons.
        if ( $this->hook && 'overview' == $this->view ) {
            add_action( 'load-' . $this->hook, array( $this, 'maybe_refresh_addons' ) );
        }

        // If the hook is succesful and we are on the preview screen, prepare our preview frame.
        if ( $this->hook && 'preview' == $this->view ) {
            Optin_Monster_Preview::get_instance()->prepare_preview_frame();
        }

    }

    /**
     * Loads assets for the settings page.
     *
     * @since 2.0.0
     */
    public function optin_assets() {

        add_action( 'admin_enqueue_scripts', array( $this, 'optin_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'optin_admin_scripts' ) );

    }

    /**
     * Register and enqueue settings page specific CSS.
     *
     * @since 2.0.0
     */
    public function optin_admin_styles() {

        // Register global styles.
        wp_register_style( $this->base->plugin_slug . '-font-awesome', plugins_url( 'assets/css/font-awesome.min.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-font-awesome' );

        if ( 'overview' == $this->view ) {
            wp_register_style( $this->base->plugin_slug . '-overview-style', plugins_url( 'assets/css/overview.css', $this->base->file ), array(), $this->base->version );
            wp_enqueue_style( $this->base->plugin_slug . '-overview-style' );
        }

        if ( 'edit' == $this->view ) {
            wp_register_style( $this->base->plugin_slug . '-color-style', plugins_url( 'assets/css/color.css', $this->base->file ), array(), $this->base->version );
            wp_enqueue_style( $this->base->plugin_slug . '-color-style' );
            wp_register_style( $this->base->plugin_slug . '-chosen-style', plugins_url( 'assets/chosen/chosen.min.css', $this->base->file ), array(), $this->base->version );
            wp_enqueue_style( $this->base->plugin_slug . '-chosen-style' );
            wp_register_style( $this->base->plugin_slug . '-edit-style', plugins_url( 'assets/css/edit.css', $this->base->file ), array(), $this->base->version );
            wp_enqueue_style( $this->base->plugin_slug . '-edit-style' );
        }

        if ( 'new' == $this->view ) {
            wp_register_style( $this->base->plugin_slug . '-new-style', plugins_url( 'assets/css/new.css', $this->base->file ), array(), $this->base->version );
            wp_enqueue_style( $this->base->plugin_slug . '-new-style' );
        }

        if ( 'split' == $this->view ) {
            wp_register_style( $this->base->plugin_slug . '-split-style', plugins_url( 'assets/css/split.css', $this->base->file ), array(), $this->base->version );
            wp_enqueue_style( $this->base->plugin_slug . '-split-style' );
        }

        if ( $this->is_admin_preview() ) {
            Optin_Monster_Preview::get_instance()->preview_styles();
        }

        // Run a hook to load in custom styles.
        do_action( 'optin_monster_admin_styles', $this->view );

    }

    /**
     * Register and enqueue settings page specific JS.
     *
     * @since 2.0.0
     */
    public function optin_admin_scripts() {

        if ( 'overview' == $this->view ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_register_script( $this->base->plugin_slug . '-overview-script', plugins_url( 'assets/js/overview.js', $this->base->file ), array( 'jquery', 'jquery-ui-sortable' ), $this->base->version, true );
            wp_enqueue_script( $this->base->plugin_slug . '-overview-script' );
            wp_localize_script(
                $this->base->plugin_slug . '-overview-script',
                'optin_monster_overview',
                array(
                    'active'           => __( 'Status: Active', 'optin-monster' ),
                    'activate'         => __( 'Activate', 'optin-monster' ),
                    'activate_nonce'   => wp_create_nonce( 'optin-monster-activate' ),
                    'activating'       => __( 'Activating...', 'optin-monster' ),
                    'ajax'             => admin_url( 'admin-ajax.php' ),
                    'confirm'          => __( 'Are you sure you want to perform this action?', 'optin-monster' ),
                    'deactivate'       => __( 'Deactivate', 'optin-monster' ),
                    'deactivate_nonce' => wp_create_nonce( 'optin-monster-deactivate' ),
                    'deactivating'     => __( 'Deactivating...', 'optin-monster' ),
                    'inactive'         => __( 'Status: Inactive', 'optin-monster' ),
                    'install'          => __( 'Install Addon', 'optin-monster' ),
                    'install_nonce'    => wp_create_nonce( 'optin-monster-install' ),
                    'installing'       => __( 'Installing...', 'optin-monster' ),
                    'int_delete'       => __( 'Are you sure you want to delete this integration?', 'optin-monster' ),
                    'int_error'        => __( 'There was an error deleting the integration.', 'optin-monster' ),
                    'proceed'          => __( 'Proceed', 'optin-monster' ),
                    'redirect'         => add_query_arg( array( 'post_type' => 'optin-monster', 'optin-monster-upgraded' => true ), admin_url( 'edit.php' ) ),
                    'upgrade_nonce'    => wp_create_nonce( 'optin-monster-upgrade' ),
                    'saving'           => __( 'Saving...', 'optin-monster' )
                )
            );
        }

        if ( 'edit' == $this->view ) {
            // Continue enqueueing media.
            wp_enqueue_media( array( 'post' => ( isset( $_GET['om_optin_id'] ) ? $_GET['om_optin_id'] : 0 ) ) );
            wp_enqueue_script( $this->base->plugin_slug . '-google-fonts', '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js' );
            wp_register_script( $this->base->plugin_slug . '-color-script', plugins_url( 'assets/js/color.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
            wp_enqueue_script( $this->base->plugin_slug . '-color-script' );
            wp_register_script( $this->base->plugin_slug . '-jquery-color-script', plugins_url( 'assets/js/jquery.color.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
            wp_enqueue_script( $this->base->plugin_slug . '-jquery-color-script' );
            wp_register_script( $this->base->plugin_slug . '-chosen-script', plugins_url( 'assets/chosen/chosen.jquery.min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
            wp_enqueue_script( $this->base->plugin_slug . '-chosen-script' );
            wp_register_script( $this->base->plugin_slug . '-postmessage-script', plugins_url( 'assets/js/postmessage.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
            wp_enqueue_script( $this->base->plugin_slug . '-postmessage-script' );
            wp_register_script( $this->base->plugin_slug . '-edit-script', plugins_url( 'assets/js/edit.js', $this->base->file ), array( 'jquery', $this->base->plugin_slug . '-color-script', $this->base->plugin_slug . '-postmessage-script' ), $this->base->version, true );
            wp_enqueue_script( $this->base->plugin_slug . '-edit-script' );
            wp_localize_script(
                $this->base->plugin_slug . '-edit-script',
                'optin_monster_edit',
                array(
                    'ajax'        => admin_url( 'admin-ajax.php' ),
                    'confirm'     => __( 'Are you sure you want to exit? Changes to your optin will not be saved.', 'optin-monster' ),
                    'id'          => isset( $_GET['om_optin_id'] ) ? absint( $_GET['om_optin_id'] ) : 0,
                    'ie'		  => sprintf( __( 'Internet Explorer does not support the protocol required to authenticate with this provider. Please save your changes and use another browser (such as Google Chrome or Mozilla Firefox) to authenticate with this provider. <a href="%s" title="Click here to learn more about this issue." target="_blank">Click here to learn more about this issue.</a>', 'optin-monster' ), 'http://optinmonster.com/docs/issues-with-internet-explorer-and-oauth/' ),
                    'iframe'      => $this->get_preview_url(),
                    'insert'      => __( 'Insert Image into Optin', 'optin-monster' ),
                    'fields'      => __( 'Please fill out all email provider fields.', 'optin-monster' ),
                    'fonts'       => urlencode( implode( '|', Optin_Monster_Output::get_instance()->get_supported_fonts( true ) ) ),
                    'save_nonce'  => wp_create_nonce( 'optin-monster-save' ),
                    'saving'      => __( 'Saving...', 'optin-monster' ),
                    'split'       => isset( $_GET['om_split'] ) ? 1 : 0,
                    'title'       => __( 'Select or Upload an Image', 'optin-monster' ),
                    'theme'       => __( 'Active: ', 'optin-monster' ),
                    'theme_nonce' => wp_create_nonce( 'optin-monster-theme' )
                )
            );
        }

        if ( 'new' == $this->view ) {
            wp_register_script( $this->base->plugin_slug . '-new-script', plugins_url( 'assets/js/new.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
            wp_enqueue_script( $this->base->plugin_slug . '-new-script' );
            wp_localize_script(
                $this->base->plugin_slug . '-new-script',
                'optin_monster_new',
                array(
                    'ajax'         => admin_url( 'admin-ajax.php' ),
                    'campaign'     => __( 'Please enter an optin campaign title before selecting your theme.', 'optin-monster' ),
                    'create_nonce' => wp_create_nonce( 'optin-monster-create' ),
                    'type_nonce'   => wp_create_nonce( 'optin-monster-type' )
                )
            );
        }

        if ( 'split' == $this->view ) {
            wp_register_script( $this->base->plugin_slug . '-split-script', plugins_url( 'assets/js/split.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
            wp_enqueue_script( $this->base->plugin_slug . '-split-script' );
            wp_localize_script(
                $this->base->plugin_slug . '-split-script',
                'optin_monster_split',
                array(
                    'ajax'    => admin_url( 'admin-ajax.php' ),
                    'confirm' => __( 'Are you sure you want to perform this action?', 'optin-monster' ),
                    'id'      => isset( $_GET['om_optin_id'] ) ? $_GET['om_optin_id'] : 0,
                )
            );
        }

        if ( $this->is_admin_preview() ) {
            Optin_Monster_Preview::get_instance()->preview_scripts();
        }

        // Run a hook to load in custom scripts.
        do_action( 'optin_monster_admin_scripts', $this->view );

    }

    /**
     * Style the menu icon for Retina displays.
     *
     * @since 2.0.0
     */
    public function optin_menu_icon() {

        ?>
        <style type="text/css">#adminmenu .toplevel_page_optin-monster-settings img { width: 25px !important; height: 19px !important; padding-top: 8px; }</style>
        <?php

    }

    /**
     * Creates a custom pointer to turn on Trends.
     *
     * @since 2.1.0
     */
    public function pointer_assets() {

        // If the user has already closed out the pointer or Trends is enabled, return early.
        $pointer_check = get_user_meta( get_current_user_id(), '_om_pointer_check', true );
        $option        = get_option( 'optin_monster' );
        if ( $pointer_check || isset( $option['allow_reporting'] ) && $option['allow_reporting'] ) {
            return;
        }

        // Load the custom pointer assets and content.
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        add_action( 'admin_print_footer_scripts', array( $this, 'pointer_output' ) );

    }

    /**
     * Outputs the custom pointer on the screen.
     *
     * @since 2.1.0
     */
    public function pointer_output() {

        $content  = '<h3>' . __( 'Help Us Improve OptinMonster!', 'optin-monster' ) . '</h3>';
        $content .= '<p>' . __( 'Please help us improve OptinMonster by allowing us to gather plugin usage data so that we can continue to build a better, more robust OptinMonster experience. <strong>No sensitive data will be gathered.</strong>', 'optin-monster' ) . '</p>';
        $content .= '<div class="wp-pointer-buttons om-pointer-buttons">';
            $content .= '<a href="#" class="button button-secondary" data-action="disallow" title="' . esc_attr__( 'Help us improve!', 'optin-monster' ) . '">' . __( 'You Bet!', 'optin-monster' ) . '</a>';
            $content .= '<a href="#" class="button button-primary" data-action="allow" title="' . esc_attr__( 'Not interested..', 'optin-monster' ) . '" style="margin-right:5px">' . __( 'Ignore This', 'optin-monster' ) . '</a>';
        $content .= '</div>';
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('#toplevel_page_optin-monster-settings').pointer({
                    content: '<?php echo $content; ?>',
                    position: {
                        edge: 'top',
                        align: 'center'
                    },
                    buttons: function(){}
                }).pointer('open');

                $(document).on('click.dismissOmPointer', '.om-pointer-buttons a', function(e){
                    var $this  = $(this),
                        action = $this.data('action'),
                        data   = {
                            action: 'optin_monster_pointer',
                            type:   action,
                            nonce:  '<?php echo wp_create_nonce( 'optin-monster-pointer' ); ?>'
                        };
                    $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(){}, 'json');
                    $('#toplevel_page_optin-monster-settings').pointer('close');
                });
            });
        </script>
        <?php

    }

    /**
     * Maybe refreshes the addons page.
     *
     * @since 2.0.0
     *
     * @return null Return early if not refreshing the addons.
     */
    public function maybe_refresh_addons() {

        if ( ! $this->is_refreshing_addons() ) {
            return;
        }

        if ( ! $this->refresh_addons_action() ) {
            return;
        }

        if ( ! $this->base->get_license_key() ) {
            return;
        }

        $this->get_addons_data( $this->base->get_license_key() );

    }

    /**
     * Outputs the main UI for handling and managing optins.
     *
     * @since 2.0.0
     */
    public function menu_ui() {

        // Build out the necessary HTML structure.
        echo '<div id="optin-monster-ui" class="wrap">';

        // Serve the UI based on the page being visited.
        if ( $this->load_view() ) {
	        switch ( $this->view ) {
	            case 'overview' :
	                require plugin_dir_path( $this->base->file ) . 'includes/admin/views/overview.php';
	                Optin_Monster_Views_Overview::get_instance()->view();
	                break;
	            case 'new' :
	                require plugin_dir_path( $this->base->file ) . 'includes/admin/views/new.php';
	                Optin_Monster_Views_New::get_instance()->view();
	                break;
	            case 'edit' :
	                require plugin_dir_path( $this->base->file ) . 'includes/admin/views/edit.php';
	                Optin_Monster_Views_Edit::get_instance()->view();
	                break;
	            case 'split' :
	                require plugin_dir_path( $this->base->file ) . 'includes/admin/views/split.php';
	                Optin_Monster_Views_Split::get_instance()->view();
	                break;
	            case 'preview' :
	                if ( $this->is_admin_preview() ) {
	                    require plugin_dir_path( $this->base->file ) . 'includes/admin/views/preview.php';
	                    Optin_Monster_Views_Preview::get_instance()->view();
	                } else {
	                    require plugin_dir_path( $this->base->file ) . 'includes/admin/views/overview.php';
	                    Optin_Monster_Views_Overview::get_instance()->view();
	                }
	                break;
	            default :
	                do_action( 'optin_monster_admin_view', $this->view );
	                break;
	        }
	    }

        // Wrap up the main UI.
        echo '</div>';

    }

    /**
     * Retrieves addons from the stored transient or remote server.
     *
     * @since 2.0.0
     *
     * @return bool|array False if no key or failure, array of addon data otherwise.
     */
    public function get_addons() {

        $key = $this->base->get_license_key();
        if ( ! $key ) {
            return false;
        }

        if ( false === ( $addons = get_transient( '_om_addons' ) ) ) {
            $addons = $this->get_addons_data( $key );
        } else {
            return $addons;
        }

    }

    /**
     * Pings the remote server for addons data.
     *
     * @since 2.0.0
     *
     * @param string $key The user license key.
     * @return bool|array False if no key or failure, array of addon data otherwise.
     */
    public function get_addons_data( $key ) {

        $addons = Optin_Monster_License::get_instance()->perform_remote_request( 'get-addons-data', array( 'tgm-updater-key' => $key ) );

        // If there was an API error, set transient for only 10 minutes.
        if ( ! $addons ) {
            set_transient( '_om_addons', false, 10 * MINUTE_IN_SECONDS );
            return false;
        }

        // If there was an error retrieving the addons, set the error.
        if ( isset( $addons->error ) ) {
            set_transient( '_om_addons', false, 10 * MINUTE_IN_SECONDS );
            return false;
        }

        // Otherwise, our request worked. Save the data and return it.
        set_transient( '_om_addons', $addons, DAY_IN_SECONDS );
        return $addons;

    }

    /**
     * Flag to determine if addons are being refreshed.
     *
     * @since 2.0.0
     *
     * @return bool True if being refreshed, false otherwise.
     */
    public function is_refreshing_addons() {

        return isset( $_POST['optin-monster-refresh-addons-submit'] );

    }

    /**
     * Verifies nonces that allow addon refreshing.
     *
     * @since 2.0.0
     *
     * @return bool True if nonces check out, false otherwise.
     */
    public function refresh_addons_action() {

        return isset( $_POST['optin-monster-refresh-addons-submit'] ) && wp_verify_nonce( $_POST['optin-monster-refresh-addons'], 'optin-monster-refresh-addons' );

    }

    /**
     * Retrieve the plugin basename from the plugin slug.
     *
     * @since 2.0.0
     *
     * @param string $slug The plugin slug.
     * @return string      The plugin basename if found, else the plugin slug.
     */
    public function get_plugin_basename_from_slug( $slug ) {

        $keys = array_keys( get_plugins() );

        foreach ( $keys as $key ) {
            if ( preg_match( '|^' . $slug . '|', $key ) ) {
                return $key;
            }
        }

        return $slug;

    }

    /**
     * Add Settings page to plugin action links in the Plugins table.
     *
     * @since 2.0.0
     *
     * @param array $links  Default plugin action links.
     * @return array $links Amended plugin action links.
     */
    public function settings_link( $links ) {

        $settings_link = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => 'optin-monster-settings' ), admin_url( 'admin.php' ) ), __( 'Settings', 'optin-monster' ) );
        array_unshift( $links, $settings_link );

        return $links;

    }

    /**
     * Returns a trusted preview URL for the OptinMonster Preview area.
     * This URL will test against cross-domain and SSL hosts to ensure that
     * a proper URL is loaded when domains don't match or that the frontend
     * URL does not support the SSL protocol.
     *
     * @since 2.0.2
     *
     * @return string $url The appropriate preview frame URL.
     */
    public function get_preview_url() {

        // Check for our admin preview page first.
        $option = get_option( 'optin_monster' );
        if ( isset( $option['admin_preview'] ) && $option['admin_preview'] ) {
            $optin = isset( $_GET['om_optin_id'] ) ? $this->base->get_optin( absint( $_GET['om_optin_id'] ) ) : false;
            if ( ! $optin ) {
                return add_query_arg( array( 'page' => 'optin-monster-settings', 'om_view' => 'preview' ), admin_url( 'admin.php' ) );
            } else {
                return add_query_arg( array( 'page' => 'optin-monster-settings', 'om_view' => 'preview', 'om_preview_optin' => $optin->ID ), admin_url( 'admin.php' ) );
            }
        }

    	// Grab our preview page.
    	$preview = get_post( get_option( 'optin_monster_preview_page' ) );

        // Attempt to grab our optin. If we can't find the optin, default to the preview (if it exists) or home page.
        $optin = isset( $_GET['om_optin_id'] ) ? $this->base->get_optin( absint( $_GET['om_optin_id'] ) ) : false;
        if ( ! $optin ) {
        	if ( $preview ) {
	        	return get_permalink( $preview->ID );
        	} else {
            	return home_url( '/' );
            }
        }

        // Determine what type of URL to retrieve.
        $url  = '';
        $meta = get_post_meta( $optin->ID, '_om_meta', true );
        if ( $preview ) {
        	if ( isset( $meta['type'] ) && ( 'post' == $meta['type'] || 'sidebar' == $meta['type'] ) ) {
	            $url = add_query_arg( array( 'om_logged_out' => true, 'om_preview_frame' => true, 'om_preview_optin' => $optin->ID ), get_permalink( $preview->ID ) ) . '#om-' . $optin->post_name;
	        } else {
	            $url = add_query_arg( array( 'om_logged_out' => true, 'om_preview_frame' => true, 'om_preview_optin' => $optin->ID ), get_permalink( $preview->ID ) );
	        }
        } else {
	        if ( isset( $meta['type'] ) && ( 'post' == $meta['type'] || 'sidebar' == $meta['type'] ) ) {
	            $latest_post = get_posts( array( 'posts_per_page' => 1 ) );
	            $latest_post = ! empty( $latest_post ) ? $latest_post[0] : false;
	            if ( $latest_post ) {
	                $url = add_query_arg( array( 'om_logged_out' => true, 'om_preview_frame' => true, 'om_preview_optin' => $optin->ID ), get_permalink( $latest_post->ID ) ) . '#om-' . $optin->post_name;
	            } else {
	                $url = add_query_arg( array( 'om_logged_out' => true, 'om_preview_frame' => true, 'om_preview_optin' => $optin->ID ), home_url( '/' ) );
	            }
	        } else {
	            $url = add_query_arg( array( 'om_logged_out' => true, 'om_preview_frame' => true, 'om_preview_optin' => $optin->ID ), home_url( '/' ) );
	        }
        }

        // Now we need to check against SSL and cross-domain origins to set the proper URL.
        $admin_origin = parse_url( admin_url() );
        $home_origin  = parse_url( home_url() );
        $cross_domain = ( strtolower( $admin_origin[ 'host' ] ) != strtolower( $home_origin[ 'host' ] ) );

        // If the page we are currently on is SSL and the home URL is cross domain, we need to fallback to the root URL for the admin site to try and run the preview.
        if ( $this->is_ssl() && $cross_domain ) {
            $url = add_query_arg( array( 'om_logged_out' => true, 'om_preview_frame' => true, 'om_preview_optin' => $optin->ID ), $admin_origin['scheme'] . '://' . $admin_origin['host'] );
        }

        // Prep the URL for SSL support.
        $url = $this->is_ssl() ? str_replace( 'http://', 'https://', $url ) : $url;

        // Allow the URL to be filtered.
        return apply_filters( 'optin_monster_preview_url', $url, $optin );

    }

    /**
     * A more comprehensive check for SSL support on a page.
     *
     * @since 2.0.0
     *
     * @return bool True if SSL, false otherwise.
     */
    public function is_ssl() {

        // Use the base is_ssl check first.
        if ( is_ssl() ) {
            return true;
        } else if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
            // Also catch proxies and load balancers.
            return true;
        } else if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
            return true;
        }

        // Otherwise, return false.
        return false;

    }

    /**
     * Checks to see if we are running an admin (instead of live) preview instance.
     *
     * @since 2.0.2.1
     *
     * @return bool True if admin preview, false otherwise.
     */
    public function is_admin_preview() {

        $option = get_option( 'optin_monster' );
        return 'preview' == $this->view && isset( $option['admin_preview'] ) && $option['admin_preview'];

    }

    /**
     * Loads the Rewards handler.
     *
     * @since 2.1.0
     *
     * @return bool True if admin preview, false otherwise.
     */
    public function load_view() {

        require plugin_dir_path( $this->base->file ) . 'includes/admin/ui/rewards.php';
        return Optin_Monster_UI_Rewards::get_instance()->view();

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Menu_Admin object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Menu_Admin ) ) {
            self::$instance = new Optin_Monster_Menu_Admin();
        }

        return self::$instance;

    }

}

// Load the menu admin class.
$optin_monster_menu_admin = Optin_Monster_Menu_Admin::get_instance();