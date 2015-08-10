<?php
/*
Plugin Name: CoSchedule by Todaymade
Description: Schedule social media messages alongside your blog posts in WordPress, and then view them on a Google Calendar interface. <a href="http://app.coschedule.com" target="_blank">Account Settings</a>
Version: 2.4.3
Author: Todaymade
Author URI: http://todaymade.com/
Plugin URI: http://coschedule.com/
*/

// Check for existing class
if ( ! class_exists( 'tm_coschedule' ) ) {

    // Include Http Class
    if( ! class_exists( 'WP_Http' ) ) {
        include_once( ABSPATH . WPINC . '/class-http.php' );
    }

    /**
     * Main Class
     */
    class TM_CoSchedule  {
        private $api = "https://api.coschedule.com";
        private $app = "https://app.coschedule.com";
        private $app_metabox = "https://app.coschedule.com/metabox";
        private $assets = "https://d2lbmhk9kvi6z5.cloudfront.net";
        private $version = "2.4.3";
        private $build = 56;
        private $connected = false;
        private $token = false;
        private $blog_id = false;
        private $current_user_id = false;
        private $is_wp_vip = false;
        private $base64_decode_disabled;

        /**
         * Class constructor: initializes class variables and adds actions and filters.
         */
        public function __construct() {
            $this->TM_CoSchedule();
        }

        public function TM_CoSchedule() {
            register_activation_hook( __FILE__, array( $this, 'activation' ) );
            register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

            // Load variables
            $this->token = get_option( 'tm_coschedule_token' );
            $this->blog_id = get_option( 'tm_coschedule_id' );
            $this->synced_build = get_option( 'tm_coschedule_synced_build' );
            $this->is_wp_vip = ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV === true );
            $this->base64_decode_disabled = in_array('base64_decode', explode( ',', str_replace( ' ', '', ini_get( 'disable_functions' ) ) ) );

            // Check if connected to api
            if ( ! empty( $this->token ) && ! empty( $this->blog_id ) ) {
                $this->connected = true;
            }

            // Register global hooks
            $this->register_global_hooks();

            // Register admin only hooks
            if ( is_admin() ) {
                $this->register_admin_hooks();
            }

            // Sync build number
            if ( $this->synced_build === false || intval( $this->synced_build ) !==  intval( $this->build ) ) {
                $this->save_build_callback();
            }
        }

        /**
         * Handles activation tasks, such as registering the uninstall hook.
         */
        public function activation() {
            register_uninstall_hook( __FILE__, array( 'tm_coschedule', 'uninstall' ) );

            // Set redirection to true
            add_option( 'tm_coschedule_activation_redirect', true );
        }

        /**
         * Checks to see if the plugin was just activated to redirect them to settings
         */
        public function activation_redirect() {
            if ( get_option( 'tm_coschedule_activation_redirect', false ) ) {
                // Redirect to settings page
                if ( delete_option( 'tm_coschedule_activation_redirect' ) ) {
                    // NOTE: call to exit after wp_redirect is per WP Codex doc:
                    //       http://codex.wordpress.org/Function_Reference/wp_redirect#Usage
                    wp_redirect( 'admin.php?page=tm_coschedule_calendar' );
                    exit;
                }
            }
        }

        /**
         * Handles deactivation tasks, such as deleting plugin options.
         */
        public function deactivation() {
            delete_option( 'tm_coschedule_token' );
            delete_option( 'tm_coschedule_id' );
            delete_option( 'tm_coschedule_activation_redirect' );
            delete_option( 'tm_coschedule_custom_post_types_list' );
            delete_option( 'tm_coschedule_synced_build' );
        }

        /**
         * Handles uninstallation tasks, such as deleting plugin options.
         */
        public function uninstall() {
            delete_option( 'tm_coschedule_token' );
            delete_option( 'tm_coschedule_id' );
            delete_option( 'tm_coschedule_activation_redirect' );
            delete_option( 'tm_coschedule_custom_post_types_list' );
            delete_option( 'tm_coschedule_synced_build' );
        }

        /**
         * Registers global hooks, these are added to both the admin and front-end.
         */
        public function register_global_hooks() {
            add_action( 'init', array( $this, "set_current_user" ) );

            // Called whenever a post is created/updated/deleted
            add_action( 'load-post.php', array( $this, "edit_post_callback") );
            add_action( 'save_post', array( $this, "save_post_callback" ) );
            add_action( 'delete_post', array( $this, "delete_post_callback" ) );

            // Called whenever a post is created/updated/deleted
            add_action( 'create_category', array( $this, "save_category_callback" ) );
            add_action( 'edit_category', array( $this, "save_category_callback" ) );
            add_action( 'delete_category', array( $this, "delete_category_callback" ) );

            // Called whenever a user/author is created/updated/deleted
            add_action( 'user_register', array( $this, "save_user_callback" ) );
            add_action( 'profile_update', array( $this, "save_user_callback" ) );
            add_action( 'delete_user', array( $this, "delete_user_callback" ) );

            // Called whenever timezone is updated
            add_action( 'update_option_timezone_string', array( $this, "save_timezone_callback" ) );
            add_action( 'update_option_gmt_offset', array( $this, "save_timezone_callback" ) );

            // work around 'missed schedule draft' condition //
            add_action( 'wp_insert_post_data', array( $this, 'conditionally_update_post_date_on_publish' ), 1, 2);

            // Edit Flow Fix
            add_filter( 'wp_insert_post_data', array( $this, 'fix_custom_status_timestamp_before' ), 1 );
            add_filter( 'wp_insert_post_data', array( $this, 'fix_custom_status_timestamp_after' ), 20 );
        }

        /**
         * Registers admin only hooks.
         */
        public function register_admin_hooks() {
            // Add meta box setup actions to post edit screen
            add_action( 'load-post.php', array( $this, "meta_box_action" ) );
            add_action( 'load-post-new.php', array( $this, "meta_box_action" ) );

            if ( $this->is_wp_vip !== true ) {
                // Ajax: Trigger cron - only available in non-WP-VIP environments
                add_action( 'wp_ajax_tm_aj_trigger_cron', array( $this, 'tm_aj_trigger_cron' ) );
                add_action( 'wp_ajax_nopriv_tm_aj_trigger_cron', array( $this, 'tm_aj_trigger_cron' ) );
            }

            // Ajax: Get blog info
            add_action( 'wp_ajax_tm_aj_get_bloginfo', array( $this, 'tm_aj_get_bloginfo' ) );
            add_action( 'wp_ajax_nopriv_tm_aj_get_bloginfo', array( $this, 'tm_aj_get_bloginfo' ) );

            // Ajax: Get full post with permalink
            add_action( 'wp_ajax_tm_aj_get_full_post', array( $this, 'tm_aj_get_full_post' ) );
            add_action( 'wp_ajax_nopriv_tm_aj_get_full_post', array( $this, 'tm_aj_get_full_post' ) );

            // Ajax: Set token
            add_action( 'wp_ajax_tm_aj_set_token', array( $this, 'tm_aj_set_token' ) );

            // Ajax: Check token
            add_action( 'wp_ajax_tm_aj_check_token', array( $this, 'tm_aj_check_token' ) );
            add_action( 'wp_ajax_nopriv_tm_aj_check_token', array( $this, 'tm_aj_check_token' ) );

            // Ajax: Set custom post types
            add_action( 'wp_ajax_tm_aj_set_custom_post_types', array( $this, 'tm_aj_set_custom_post_types' ) );
            add_action( 'wp_ajax_nopriv_tm_aj_set_custom_post_types', array( $this, 'tm_aj_set_custom_post_types' ) );

            // Ajax: Get function
            add_action( 'wp_ajax_tm_aj_function', array( $this, 'tm_aj_function' ) );
            add_action( 'wp_ajax_nopriv_tm_aj_function', array( $this, 'tm_aj_function' ) );

            // Ajax: The main entry point (when plugin_build > 38)
            add_action( 'wp_ajax_tm_aj_action', array( $this, 'tm_aj_action' ) );
            add_action( 'wp_ajax_nopriv_tm_aj_action', array( $this, 'tm_aj_action' ) );

            // Ajax: Deactivation
            add_action( 'wp_ajax_tm_aj_deactivation', array( $this, 'tm_aj_deactivation' ) );
            add_action( 'wp_ajax_nopriv_tm_aj_deactivation', array( $this, 'tm_aj_deactivation' ) );

            // Add Sidebar Links
            add_action( 'admin_menu', array( $this, 'add_menu' ) );
            add_action( 'admin_menu', array( $this, 'add_submenu' ) );
            add_action( 'admin_menu', array( $this, 'admin_submenu_new_window_items' ) );
            add_action( 'admin_menu', array( $this, 'admin_submenu_new_window_items_jquery' ) );

            // Add settings link to plugins listing page
            add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 2, 2 );

            // Add check for activation redirection
            add_action( 'admin_init', array( $this, 'activation_redirect' ) );
        }

        /**
         * Add calendar and settings link to the admin menu
         */
        public function add_menu() {
            add_menu_page( 'CoSchedule Calendar', 'Calendar', 'edit_posts', 'tm_coschedule_calendar', array( $this, 'plugin_calendar_page' ), $this->assets . '/plugin/img/icon.png', '50.505' );
        }

        /**
         * Add calendar submenu links to admin menu.
         */
        public function add_submenu() {
            if ( true == $this->connected ) {
                add_submenu_page( 'tm_coschedule_calendar', 'My Activity', 'My Activity', 'edit_posts', 'tm_coschedule_activity', array( $this, 'plugin_activity_page' ) );
                add_submenu_page( 'tm_coschedule_calendar', 'Top Posts', 'Top Posts', 'edit_posts', 'tm_coschedule_top_posts', array( $this, 'plugin_top_posts_page' ) );
                add_submenu_page( 'tm_coschedule_calendar', 'My Team', 'My Team', 'edit_posts', 'tm_coschedule_team', array( $this, 'plugin_team_page' ) );
                add_submenu_page( 'tm_coschedule_calendar', 'Settings', 'Settings', 'edit_posts', 'tm_coschedule_settings', array( $this, 'plugin_settings_page' ) );
                add_submenu_page( 'tm_coschedule_calendar', 'Need Help?', 'Need Help?', 'edit_posts', 'tm_coschedule_help', array( $this, 'plugin_help_page' ) );
            }
        }

        /**
         * Add submenu item(s) that open in new window
         */
        public function admin_submenu_new_window_items() {
            global $submenu;

            if ( true == $this->connected ) {
                $url = $this->app . '/#/calendar/' . $this->blog_id . '/schedule';
                $submenu['tm_coschedule_calendar'][500] = array( '<span class="cos-submenu-new-window">Open In Web App</span>', 'edit_posts', esc_url( $url ) );
            }
        }

        /**
         * Enqueue script for opening submenu links in new window
         */
        public function admin_submenu_new_window_items_jquery() {
            $cache_bust = urlencode( $this->get_cache_bust() );
            $url = $this->assets . '/plugin/js/cos-plugin-new-window.js?cb=' . $cache_bust;
            wp_enqueue_script( 'cos_js_plugin_new_window', $url, false, null, true );
        }

        /**
         * Admin: Add settings link to plugin management page
         */
        public function plugin_settings_link( $actions, $file ) {
            if( false !== strpos( $file, 'tm-scheduler' ) ) {
                $url = "admin.php?page=tm_coschedule_calendar";
                $actions['settings'] = '<a href="' . esc_url( $url ) . '">Settings</a>';
            }
            return $actions;
        }

        /**
         * Settings page styles
         */
        public function plugin_iframe_styles() {
            $cache_bust = urlencode( $this->get_cache_bust() );
            $url = $this->assets . '/plugin/css/cos-iframe-fix.css?cb=' . $cache_bust;
            wp_enqueue_style( 'cos_css', $url );
        }

        /**
         * Settings page scripts
         */
        public function plugin_settings_scripts() {
            $cache_bust = urlencode( $this->get_cache_bust() );
            wp_enqueue_style( 'cos_css', $this->assets . '/plugin/css/cos-plugin-setup.css?cb=' . $cache_bust );
            wp_enqueue_script( 'cos_js_config', $this->assets . '/config.js?cb=' . $cache_bust, false, null, true );
            wp_enqueue_script( 'cos_js_plugin', $this->assets . '/plugin/js/cos-plugin-setup.js?cb=' . $cache_bust, false, null, true );
        }

        /**
         * Calendar page menu callback
         */
        public function plugin_calendar_page() {
            if( ! current_user_can( 'edit_posts' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            // Check if connected
            if ( true == $this->connected ) {
                $this->plugin_iframe_styles();
                include( sprintf( "%s/calendar.php", dirname( __FILE__ ) ) );
            } else {
                $this->plugin_settings_scripts();
                include( sprintf( "%s/plugin_setup.php", dirname( __FILE__ ) ) );
            }
        }

        /**
         * Team page menu callback
         */
        public function plugin_team_page() {
            if( ! current_user_can( 'edit_posts' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            // Setup styles
            if ( current_user_can( 'manage_options' ) ) {
                $this->plugin_iframe_styles();
            } else {
                $this->plugin_settings_scripts();
            }

            include( sprintf( "%s/team.php", dirname( __FILE__ ) ) );
        }

        /**
         * Activity page menu callback
         */
        public function plugin_activity_page() {
            if( ! current_user_can( 'edit_posts' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            // Setup styles
            $this->plugin_iframe_styles();

            include( sprintf( "%s/activity.php", dirname( __FILE__ ) ) );
        }

        /**
         * Top Posts page menu callback
         */
        public function plugin_top_posts_page() {
            if( ! current_user_can( 'edit_posts' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            // Setup styles
            $this->plugin_iframe_styles();

            include( sprintf( "%s/top_posts.php", dirname( __FILE__ ) ) );
        }

        /**
         * Settings page menu callback
         */
        public function plugin_settings_page() {
            if( ! current_user_can( 'edit_posts' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            // Setup styles
            if ( current_user_can( 'manage_options' ) ) {
                $this->plugin_iframe_styles();
            } else {
                $this->plugin_settings_scripts();
            }

            include( sprintf( "%s/settings.php", dirname( __FILE__ ) ) );
        }

        /**
         * Help page menu callback
         */
        public function plugin_help_page() {
            if( ! current_user_can( 'edit_posts' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            // Setup styles
            $this->plugin_iframe_styles();

            include( sprintf( "%s/help.php", dirname( __FILE__ ) ) );
        }

        /**
         * Checks if the meta box should be included on the page based on post type
         */
        public function meta_box_enabled() {
            $post_type = $this->get_current_post_type();
            return $this->is_synchronizable_post_type( $post_type, true );
        }

        /**
         * Adds action to insert a meta box
         */
        public function meta_box_action() {
            add_action( 'add_meta_boxes', array( $this, "meta_box_setup" ) );
        }

        /**
         * Sets up the meta box to be inserted
         */
        public function meta_box_setup() {
            if ( true == $this->meta_box_enabled() && true == $this->connected ) {
                $this->metabox_iframe_styles();
                $this->metabox_iframe_scripts();

                $post_type = $this->get_current_post_type();
                add_meta_box(
                    'tm-scheduler',                         // Unique ID
                    'CoSchedule',                           // Title
                    array( &$this, 'meta_box_insert' ),       // Callback function
                    $post_type,                             // Admin page (or post type)
                    'normal',                               // Context
                    'default'                               // Priority
                );
            }
        }

        /**
         * Metabox iframe styles
         */
        public function metabox_iframe_styles() {
            $cache_bust = urlencode( $this->get_cache_bust() );
            $url = $this->assets . '/plugin/css/cos-metabox.css?cb=' . $cache_bust;
            wp_enqueue_style( 'cos_metabox_css', $url );
        }

        /**
         * Metabox iframe scripts
         */
        public function metabox_iframe_scripts() {
            $cache_bust = urlencode( $this->get_cache_bust() );
            $resizer_url = $this->assets . '/plugin/js/cos-iframe-resizer.js?cb=' . $cache_bust;
            $resizer_exec_url = $this->assets . '/plugin/js/cos-iframe-resizer-exec.js?cb=' . $cache_bust;
            wp_enqueue_script( 'cos_js_iframe_resizer', $resizer_url, false, null, true );
            wp_enqueue_script( 'cos_js_iframe_resizer_exec', $resizer_exec_url, false, null, true );
        }

        /**
         * Inserts the meta box
         */
        public function meta_box_insert( $post ) {
            $blog_id = get_option( 'tm_coschedule_id' );
            $query_params = array(
                "blogID" => urlencode( $blog_id ),
                "postID" => urlencode( $post->ID ),
                "build"  => urlencode( $this->build ),
                "userID" => urlencode( $this->current_user_id )
            );
            $url = untrailingslashit( $this->app_metabox ) . "/#/authenticate";
            // NOTE: calling add_query_arg(...) with empty string to avoid it relocating the hash location of above $url
            $url .= add_query_arg( $query_params, '' );
        ?>
            <iframe id="CoSmetabox" frameborder="0" border="0" scrolling="no" src="<?php echo esc_url( $url ); ?>" width="100%"></iframe>
        <?php
        }

        /**
         * Ajax: Secure using token
         */
        public function valid_token( $token = '' ) {
            $validate = "";
            if ( ! empty( $token ) ) {
                if ( true == $this->connected ) {
                    if ( $this->token === $token ) {
                        $validate = true;
                    } else {
                        $validate = "Invalid token";
                    }
                } else {
                    $validate = "Not connected to api";
                }
            } else {
                $validate = "Token required";
            }

            if ( true === $validate ) {
                return true;
            } else {
                $error = array(
                    'error' => $validate
                );
                $this->respond_json_and_die( $error );
            }
        }

        /**
         * Ajax: "Near Realtime Assist"
         * Triggers internal cron at the scheduled time of publication for a particular post
         */
        public function tm_aj_trigger_cron( $data_args ) {
            $response = array();
            try {
                if ( isset( $_GET['token'] ) ) {
                    $token = $_GET['token'];
                } else if ( isset( $data_args['token'] ) ) {
                    $token = $data_args['token'];
                }
                $this->sanitize_param( $token );

                // only proceed if valid token
                if ( true == $this->valid_token( $token ) ) {

                    if ( is_array( $_GET ) && array_key_exists( 'post_id', $_GET ) ) {
                        $post_id = $_GET['post_id'];
                    } else if ( is_array( $data_args ) && array_key_exists( 'post_id', $data_args ) ) {
                        $post_id = $data_args['post_id'];
                    }
                    $this->sanitize_param( $post_id );

                    // purge any post caches
                    $cache_flush_result = $this->cache_flush( $post_id );

                    if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
                        // wp_cron is disabled
                        $wp_cron_response = 'disabled';
                    } else {
                        // wp_cron can be executed, do it
                        $wp_cron_response = ( ( wp_cron() !== null ) ? true : false );
                    }

                    // if indication is wp_cron not run or disabled, force the issue //
                    if ( $wp_cron_response == false || $wp_cron_response == 'disabled' ) {
                        $publish_missed_schedule_posts_result = $this->publish_missed_schedule_posts( $post_id );
                    }

                    // report the findings
                    $response['wp_cron_was_run'] = $wp_cron_response;
                    $response['cache_flush_result'] = $cache_flush_result;
                    $response['publish_missed_schedule_posts_result'] = $publish_missed_schedule_posts_result;
                    $response['server_time'] = time();
                    $response['server_date'] = date( 'c' );
                    $response['gmt_offset'] = get_option( 'gmt_offset' );
                    $response['tz_abbrev'] = date( 'T' );
                }
            } catch ( Exception $e ) {
                $response['error'] = $e->getMessage();
            }

            $this->respond_json_and_die( $this->array_decode_entities( $response ) );
        }

        /**
         * Adapted from nice example found here: http://theme.fm/2011/10/how-to-upload-media-via-url-programmatically-in-wordpress-2657/
         */
        public function tm_aj_sideload_url( $data_args ) {
            try {

                if ( isset( $data_args['url'] ) ) {
                    $url = $data_args['url'];
                }
                if ( isset( $data_args['post_id'] ) ) {
                    $post_id = $data_args['post_id'];
                }

                // make $url safe //
                $this->sanitize_param( $url );
                $url = esc_url( $url );

                // make $post_id safe //
                $this->sanitize_param( $post_id );
                if ( ! is_numeric( $post_id ) ) {
                    $post_id = 0;
                }

                // validate required param //
                if ( ! isset( $url ) || empty( $url ) ) {
                    throw new Exception( 'Invalid API call. Missing argument(s).' );
                }

                // download it to temporary spot //
                $attachment_pointer = download_url( $url );
                $file_array = array(
                    'name' => basename( $url ),
                    'tmp_name' => $attachment_pointer
                );

                // track where in process //
                $stage = 'download';

                // check for download errors //
                if ( ! is_wp_error( $attachment_pointer ) ) {

                    // handle media, $post_id === 0 will not associate media with a post //
                    $attachment_pointer = media_handle_sideload( $file_array, $post_id );

                    // track where in process //
                    $stage = 'sideload';

                    // check for sideload error //
                    if ( ! is_wp_error( $attachment_pointer ) ) {

                        // extract url of attachment //
                        $response = array();
                        $response['url'] = $url;
                        $response['attachment_url'] = wp_get_attachment_url( $attachment_pointer );

                        // respond OK //
                        return $this->respond_json_and_die( $response );
                    }
                }

                // something went wrong, remove temporary file //
                @unlink( $file_array['tmp_name'] );

                // report error //
                if ( is_wp_error( $attachment_pointer ) ) {
                    throw new Exception( 'Sideload failed during ' . $stage . ' with WP Error: ' . $attachment_pointer->get_error_message() ) ;
                } else {
                    throw new Exception( 'Sideload failed during ' . $stage . ' for unknown reason.' ) ;
                }

            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Insert a new WordPress Post given a WP Post-like structure @ $data_args['post'], upon success returns JSON
         * form of get_full_post(...)
         */
        public function tm_aj_import_post( $data_args ) {
            try {

                // validate expected arg //
                if ( isset( $data_args['post'] ) ) {
                    $post = $data_args['post'];
                }

                // NOTE: all significant sanitization of $post content is left to wp_insert_post(...) //
                if ( ! isset( $post ) || empty( $post ) ) {
                    throw new Exception( 'Invalid API call. Missing argument(s).' );
                }

                // validate required $post attributes //
                $post_attributes = array( 'post_content', 'post_title' );
                foreach ( $post_attributes as $required_attribute ) {
                    if ( ! isset( $post[$required_attribute] ) || empty( $post[$required_attribute] ) ) {
                        throw new Exception( 'Invalid API call. Missing required post attribute(s).' );
                    }
                }

                // sanitize title per https://codex.wordpress.org/Function_Reference/wp_insert_post#Security //
                $post['post_title'] = wp_strip_all_tags( $post['post_title'] );

                // guarded default values //
                $post['post_status'] = $this->get_value_or_default( $post['post_status'], 'draft' );
                $post['post_type'] = $this->get_value_or_default( $post['post_type'], 'post' );

                // add filter to prevent CoSchedule's own API callback upon post creation //
                add_filter( 'tm_coschedule_save_post_callback_filter', array( $this, 'prevent_save_post_callback' ), 1, 2 );
                $post_id = wp_insert_post( $post, true );

                // respond //
                if ( ! is_wp_error( $post_id ) ) {
                    $this->respond_json_and_die( $this->get_full_post( $post_id ) );
                } else {
                    throw new Exception( 'Unable to insert post: ' . $post_id->get_error_message() );
                }
            } catch( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Filter target that, when registered, will prevent CoSchedule's own registered save_post callback from executing.
         */
        public function prevent_save_post_callback( $state, $post_id ) {
            return false;
        }

        /**
         * Utility that will return given value, given default or null.
         */
        public function get_value_or_default(&$var, $default=null) {
            return isset($var) ? $var : $default;
        }

        /**
         * Ajax: Return blog info
         */
        public function tm_aj_get_bloginfo( $data_args ) {
            try {
                $http_api_transports = apply_filters( 'http_api_transports', array( 'curl', 'streams' ), array(), $this->api );
                $http = new WP_Http;
                $vars = array(
                    "name"            =>  get_bloginfo( "name" ),
                    "description"     =>  get_bloginfo( "description" ),
                    "wpurl"           =>  get_bloginfo( "wpurl" ),
                    "url"             =>  get_bloginfo( "url" ),
                    "version"         =>  get_bloginfo( "version" ),
                    "language"        =>  get_bloginfo( "language" ),
                    "pingback_url"    =>  get_bloginfo( "pingback_url" ),
                    "rss2_url"        =>  get_bloginfo( "rss2_url" ),
                    "timezone_string" =>  get_option( "timezone_string" ),
                    "gmt_offset"      =>  get_option( "gmt_offset" ),
                    "plugin_version"  =>  $this->version,
                    "plugin_build"    =>  $this->build,
                    "is_wp_vip"       =>  $this->is_wp_vip,
                    "charset"         =>  get_bloginfo('charset'),
                    "first_transport" =>  $http->_get_first_available_transport( $this->api ),
                    "all_transports"  =>  implode( ',', $http_api_transports ),
                    "is_multisite"    =>  is_multisite(),
                    "base64_decode_disabled" => $this->base64_decode_disabled,
                    "php_disabled_fn"        => ini_get( 'disable_functions' ),
                    "php_disabled_cl"        => ini_get( 'disable_classes' )
                );

                if ( isset( $_GET['tm_debug'] ) || isset( $data_args['tm_debug'] ) ) {
                    $vars["debug"] = array();
                    $vars["debug"]["server_time"] = time();
                    $vars["debug"]["server_date"] = date( 'c' );
                    $vars["debug"]["site_url"] = get_option( 'siteurl' );
                    $vars["debug"]["php_version"] = phpversion();

                    $theme = wp_get_theme();
                    $vars["debug"]["theme"] = array();
                    $vars["debug"]["theme"]["Name"] = $theme->get( 'Name' );
                    $vars["debug"]["theme"]["ThemeURI"] = $theme->get( 'ThemeURI' );
                    $vars["debug"]["theme"]["Description"] = $theme->get( 'Description' );
                    $vars["debug"]["theme"]["Author"] = $theme->get( 'Author' );
                    $vars["debug"]["theme"]["AuthorURI"] = $theme->get( 'AuthorURI' );
                    $vars["debug"]["theme"]["Version"] = $theme->get( 'Version' );
                    $vars["debug"]["theme"]["Template"] = $theme->get( 'Template' );
                    $vars["debug"]["theme"]["Status"] = $theme->get( 'Status' );
                    $vars["debug"]["theme"]["Tags"] = $theme->get( 'Tags' );
                    $vars["debug"]["theme"]["TextDomain"] = $theme->get( 'TextDomain' );
                    $vars["debug"]["theme"]["DomainPath"] = $theme->get( 'DomainPath' );

                    $vars["debug"]["plugins"] = $this->get_installed_plugins();
                }
                $this->respond_json_and_die( $this->array_decode_entities( $vars ) );
            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Ajax: Return full post with permalink
         */
        public function tm_aj_get_full_post( $data_args ) {
            try {
                if ( isset( $_GET['post_id'] ) ) {
                    $id = $_GET['post_id'];
                } else if (isset( $data_args['post_id'] ) ) {
                    $id = $data_args['post_id'];
                } else {
                    throw new Exception( 'Invalid API call. Missing argument(s).' );
                }

                $this->sanitize_param( $id );

                $this->respond_json_and_die( $this->get_full_post( $id ) );
            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Ajax: Set token
         */
        public function tm_aj_set_token( $data_args ) {
            try {
                $params = array();

                // Sanitize $_POST or $_GET params
                if ( isset( $_POST['token'] ) && isset( $_POST['id'] ) ) {
                    $params['token'] = $_POST['token'];
                    $params['id'] = $_POST['id'];
                } elseif ( isset( $_GET['token'] ) && isset( $_GET['id'] ) ) {
                    $params['token'] = $_GET['token'];
                    $params['id'] = $_GET['id'];
                } elseif ( isset( $data_args['token'] ) && isset( $data_args['id'] ) ) {
                    $params['token'] = $data_args['token'];
                    $params['id'] = $data_args['id'];
                }

                $this->sanitize_array( $params );

                // Set options
                $response = '';
                if ( isset( $params['token'] ) && isset( $params['id'] ) ) {
                    update_option( 'tm_coschedule_token', $params['token'] );
                    update_option( 'tm_coschedule_id', $params['id'] );
                    $response = $params['token'];
                }
                $this->respond_text_and_die( $response );
            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Ajax: Check a token against the current token
         */
        public function tm_aj_check_token( $data_args ) {
            try {
                if ( isset( $_GET['token'] ) ) {
                    $token = $_GET['token'];
                } else {
                    $token = $data_args['token'];
                }

                $this->sanitize_param( $token );

                // Compare
                $response = ( ( true == $this->valid_token( $token ) ) ? 'Tokens match' : 'Tokens do not match' );
                $this->respond_text_and_die( $response );
            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Ajax: Set custom post types
         */
        public function tm_aj_set_custom_post_types( $data_args ) {
            try {
                if ( isset( $_GET['post_types_list'] ) ) {
                    $list = $_GET['post_types_list'];
                } else if ( isset( $data_args['post_types_list'] ) ) {
                    $list = $data_args['post_types_list'];
                } else {
                    throw new Exception( 'Invalid API call. Missing argument(s).' );
                }

                $this->sanitize_param( $list );

                if ( !is_string( $list ) ) {
                    throw new Exception( 'Invalid API call. Invalid argument(s).' );
                }

                update_option( 'tm_coschedule_custom_post_types_list', $list );
                $this->respond_text_and_die( $list );
            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Ajax: Get function
         */
        public function tm_aj_function() {
            try {
                // Save args
                $args = $_GET;
                $this->sanitize_array( $args );

                // Validate call
                $this->valid_token( $args['token'] );

                // Remove action name
                unset( $args['action'] );

                // Remove token
                unset( $args['token'] );

                // Save and remove function name
                $func = $args['call'];
                unset( $args['call'] );

                // Check if function is allowed
                $allowed = array(
                    'get_users',
                    'get_categories',
                    'get_posts_with_categories',
                    'get_post_types',
                    'wp_update_post',
                    'wp_insert_post'
                );

                if ( ! in_array( $func, $allowed ) ) {
                    throw new Exception( 'Invalid function called' );
                }

                // Fix: Prevent WP from stripping iframe tags when updating post
                if ( 'wp_update_post' === $func || 'wp_insert_post' === $func ) {
                    remove_filter( 'title_save_pre', 'wp_filter_kses' );
                    remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
                }

                // Call public or private Function
                if ( isset( $args['private'] ) ) {
                    unset( $args['private'] );
                    $out = call_user_func_array( array( $this, $func ), $args );
                } else {
                    $out = call_user_func_array( $func, $args );
                }

                if ( is_array( $out ) ) {
                    $out = array_values( $out );
                    $this->respond_json_and_die( $out );
                } else {
                    // Check for errors
                    if ( is_wp_error( $out ) ) {
                        $out = $out->get_error_message();
                    }
                    // ensure $out is not an object before responding //
                    $out = ( is_object( $out ) ? json_encode( $out ) : $out );
                    $this->respond_text_and_die( $out );
                }
            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * AJAX: main entry point (when plugin_build > 38)
         */
        public function tm_aj_action() {
            try {
                // favor POST values for compatibility  //
                if ( isset( $_POST['action'] ) ) { // plugin_build > 40 will prefer POST
                   $args = $_POST;
                } else { // fallback to GET params //
                   $args = $_GET;
                }
                // at this point $args expected to contain only 'action' and 'data' keys, all others ignored

                // Remove 'action' arg - the means by which this function was invoked
                unset( $args['action'] );

                // make $args safe
                $this->sanitize_array( $args );

                // Validate 'data' arg
                if ( ! isset( $args['data'] ) ) {
                    throw new Exception( 'Invalid API call. Missing data.' );
                }

                // Decode 'data' and re-define $args
                $args = json_decode( $this->adapt_base64_decode( $args['data'] ), true );

                // NOTE: After this point, $args elements should be individually sanitized before use!!!

                // Normalize 'method' arg: prefer 'method', accept 'call' or die with exception if neither provided
                if ( ! isset( $args['method'] ) ) {
                    if ( isset( $args['call'] ) ) {
                        $args['method'] = $args['call'];
                    } else if ( isset( $args['action'] ) ) {
                        $args['method'] = $args['action'];
                    }
                }

                if ( ! isset( $args['method'] ) ) {
                    throw new Exception( 'Invalid API call. Missing method.' );
                }

                // Sanitize what is trying to be called
                $this->sanitize_param( $args['method'] );
                $func = $args['method'];

                // Remove nested 'method', 'action' and 'call'
                unset( $args['method'] );
                unset( $args['action'] );
                unset( $args['call'] );

                // functions that handle token validation internally //
                $defer_token_check = array(
                    'tm_aj_deactivation',
                    'tm_aj_check_token',
                    'tm_aj_get_bloginfo',
                    'tm_aj_trigger_cron'
                );

                // Functions in the WP environment
                $wp_functions = array(
                    'get_users',
                    'get_categories',
                    'get_post_types',
                    'wp_update_post',
                    'wp_insert_post'
                );

                // Functions defined by plugin
                $private_functions = array(
                    'get_posts_with_categories',
                    'tm_aj_get_bloginfo',
                    'tm_aj_get_full_post',
                    'tm_aj_check_token',
                    'tm_aj_set_custom_post_types',
                    'tm_aj_deactivation',
                    'tm_aj_trigger_cron',
                    'tm_aj_sideload_url',
                    'tm_aj_import_post',
                );

                // do not allow some functions when in WP-VIP environments
                if ( $this->is_wp_vip === true ) {
                    unset( $defer_token_check[ array_search( 'tm_aj_trigger_cron', $defer_token_check ) ] );
                    unset( $private_functions[ array_search( 'tm_aj_trigger_cron', $private_functions ) ] );
                }

                // Allowed functions
                $allowed = array_merge( $wp_functions, $private_functions );

                // Validate allowed
                if ( ! in_array( $func, $allowed ) ) {
                    throw new Exception( 'Invalid API call. Method not allowed.' );
                }

                // Only invoke validation for those functions not having it internally
                if ( ! in_array( $func, $defer_token_check) ) {
                    // Validate 'token' arg
                    if ( ! isset( $args['token'] ) ) {
                        throw new Exception( 'Invalid API call. Token not found.' );
                    }
                    $this->sanitize_param( $args['token'] );
                    $this->valid_token( $args['token'] );
                }

                // Fix: Prevent WP from stripping iframe tags and Jetpack markdown when updating post
                if ( 'wp_update_post' === $func || 'wp_insert_post' === $func ) {
                    remove_filter( 'title_save_pre', 'wp_filter_kses' );
                    remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
                }

                // Fix: Prevent WP from stripping Jetpack markdown when updating post
                if ( 'wp_update_post' === $func ) {
                    $this->preserve_markdown();
                }

                // Is the target function private ?
                $is_private = in_array( $func, $private_functions ) ;

                // wrap model in order to preserve it through call_user_func_array invocation //
                if ( isset( $args['args'] ) ) {
                    $args = array( $args['args'] );
                } else {
                    $args = array( $args );
                }

                // Call $func with $args
                if ( $is_private ) {
                    $out = call_user_func_array( array( $this, $func ), $args );
                } else {
                    $out = call_user_func_array( $func, $args );
                }

                // Handle output
                if ( is_array( $out ) ) {
                    $out = array_values( $out );
                    $this->respond_json_and_die( $out );
                } else {
                    // Check for errors
                    if ( is_wp_error( $out ) ) {
                        $out = $out->get_error_message();
                    }
                    // ensure $out is not an object before responding //
                    $out = ( is_object( $out ) ? json_encode( $out ) : $out );
                    $this->respond_text_and_die( $out );
                }

            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Prevent WP from stripping Jetpack markdown
         */
        public function preserve_markdown () {
            if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'markdown' ) ) {
                require_once ABSPATH . 'wp-content/plugins/jetpack/modules/markdown/easy-markdown.php';

                if ( class_exists( 'WPCom_Markdown' ) ) {
                    WPCom_Markdown::get_instance()->unload_markdown_for_posts();
                }
            }
        }

        /**
         * AJAX: Handles deactivation task
         */
        public function tm_aj_deactivation( $data_args ) {
            try {
                // Validate call
                if ( isset( $_GET['token'] ) ) {
                    $token = $_GET['token'];
                } else {
                    $token = $data_args['token'];
                }

                $this->sanitize_param( $token );
                $this->valid_token( $token );

                delete_option( 'tm_coschedule_token' );
                delete_option( 'tm_coschedule_id' );
                $this->respond_empty_and_die();
            } catch ( Exception $e ) {
                $this->respond_exception_and_die( $e );
            }
        }

        /**
         * Get the post by id, with permalink and attachments
         */
        public function get_full_post( $post_id ) {
            $post = get_post( $post_id, "ARRAY_A" );
            $post['permalink'] = get_permalink( $post_id );

            // Media attachments (start with featured image)
            $post['attachments'] = array();
            $featured_image = $this->get_thumbnail( $post_id );

            if ( ! empty( $featured_image ) ) {
                array_push( $post['attachments'], $featured_image );
            }

            if ( isset( $post['post_content'] ) ) {
                // Add post attachments and remove duplicates
                $post['attachments'] = array_merge( $post['attachments'], $this->get_attachments( $post['post_content'] ) );
                $post['attachments'] = array_unique( $post['attachments'] );

                // Generate an excerpt if one isn't available
                if ( ! isset( $post['post_excerpt'] ) || ( isset( $post['post_excerpt'] ) && empty( $post['post_excerpt'] ) ) ) {
                    $post['post_excerpt'] = $this->get_post_excerpt( $post['post_content'] );
                }

                // Remove content
                unset( $post['post_content'] );
            }

            // Remove content filtered
            if ( isset( $post['post_content_filtered'] ) ) {
                unset( $post['post_content_filtered'] );
            }

            // Process category
            if ( isset($post['post_category']) && ! is_null( $post['post_category'] ) ) {
                $post['post_category'] = implode( $post['post_category'], ',' );
            } else {
                $post['post_category'] = "";
            }

            return $post;
        }

        /**
         * Generate an excerpt by taking the first words of the post
         */
        public function get_post_excerpt( $content ) {
            $the_excerpt = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
            $excerpt_length = 35; // Sets excerpt length by word count
            $the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) ); //Strips tags and images
            $words = explode( ' ', $the_excerpt, $excerpt_length + 1 );

            if( count( $words ) > $excerpt_length ) {
                array_pop( $words );
                array_push( $words, '…' );
                $the_excerpt = implode( ' ', $words );
            }

            // Remove undesirable whitespace and condense consecutive spaces
            $the_excerpt = preg_replace( '/\s+/', " ", $the_excerpt );

            return $the_excerpt;
        }

        /**
         * Get posts with permalinks, attachments, and categories
         */
        public function get_posts_with_categories( $args ) {
            // Load posts
            $out = call_user_func_array( 'get_posts', $args );

            $posts = array();
            foreach ( $out as $post ) {
                $post = $this->get_full_post( $post->ID );

                array_push( $posts, $post );
            }
            return $posts;
        }

        /**
         * Get the thumbnail url of the post
         */
        public function get_thumbnail( $post_id ) {
            $post_thumbnail_id = get_post_thumbnail_id( $post_id );
            $post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );
            $site_url = get_site_url();

            // remove trailing slash from site url
            // Codex Reference: http://codex.wordpress.org/Function_Reference/untrailingslashit
            $site_url = untrailingslashit( $site_url );

            // Only include valid URL
            if ( is_string( $post_thumbnail_url ) && strlen( $post_thumbnail_url ) > 0 ) {
                // Older versions of WordPress (<3.6) may exclude site URL from attachment URL
                if ( false === strpos( $post_thumbnail_url, 'http' ) ) {
                    $post_thumbnail_url = $site_url . $post_thumbnail_url;
                }
            } else {
                $post_thumbnail_url = null;
            }

            return $post_thumbnail_url;
        }

        /**
         * Get array of all attachments of the post
         */
        public function get_attachments( $content ) {
            $attachments = array();
            $site_url = get_site_url();

            // remove trailing slash from site url
            // Codex Reference: http://codex.wordpress.org/Function_Reference/untrailingslashit
            $site_url = untrailingslashit( $site_url );

            preg_match_all( '/<img[^>]+>/i', $content, $images );

            for ( $i = 0; $i < count( $images[0] ); $i++ ) {

                // Match the image source and remove 'src='
                // (accounts for single and double quotes)
                preg_match( '/src=[\'"]([^\'"]+)/i', $images[0][ $i ], $img );
                $url = str_ireplace( 'src="', '',  $img[0] );
                $url = str_ireplace( "src='", '',  $url );

                // Older versions of WordPress (<3.6) may exclude site URL from attachment URL
                if ( false === strpos( $url, 'http' ) ) {
                    $url = $site_url . $url;
                }

                $attachments[] = esc_url( $url );
            }

            return $attachments;
        }

        /**
         * Utility function to validate if given $post_type is in option 'tm_coschedule_custom_post_types_list' or
         * default of 'post'
         */
        public function is_synchronizable_post_type( $post_type, $sync_with_api ) {
            $sync_with_api = ( $sync_with_api === true ? true : false ) ;
            $custom_post_types_list = get_option( 'tm_coschedule_custom_post_types_list' );

            // Grab remote list if not set
            if ( $sync_with_api && empty( $custom_post_types_list ) && true == $this->connected ) {
                // Load remote blog information
                $resp = $this->api_get( '/wordpress_keys?_wordpress_key=' . $this->token );

                // be extra careful with resp as we don't want an exception to escape this function //
                if ( ! is_wp_error($resp) && isset( $resp['response'] ) && isset( $resp['response']['code'] ) && 200 === $resp['response']['code'] ) {
                    $json = json_decode( $resp['body'], true );

                    // Check for a good response
                    if ( isset( $json['result'] ) && isset( $json['result'][0] ) && ! empty( $json['result'][0]['custom_post_types'] ) ) {
                        $custom_post_types_list = $json['result'][0]['custom_post_types_list'];

                        // Save custom list
                        if ( ! empty( $custom_post_types_list ) ) {
                            update_option( 'tm_coschedule_custom_post_types_list', $custom_post_types_list );
                        }
                    }
                }
            }

            // Default
            if ( empty( $custom_post_types_list ) ) {
                $custom_post_types_list = 'post';
                update_option( 'tm_coschedule_custom_post_types_list', $custom_post_types_list );
            }

            // Convert to an array
            $custom_post_types_list_array = explode( ',', $custom_post_types_list );

            // Check if post type is supported
            return in_array( $post_type, $custom_post_types_list_array );
        }

        /**
         * Get currated array of all plugins installed in this blog
         */
        public function get_installed_plugins() {
            $plugins = array();
            $plugins['active'] = array();
            $plugins['inactive'] = array();

            foreach ( get_plugins() as $key => $plugin ) {
                $plugin['path'] = $key;
                $plugin['status'] = is_plugin_active( $key ) ? 'Active' : 'Inactive';

                if ( is_plugin_active( $key ) ) {
                    array_push( $plugins['active'], $plugin );
                } else {
                    array_push( $plugins['inactive'], $plugin );
                }
            }

            return $plugins;
        }

        /**
         * Initialize the currently logged in user to a local variable
         */
        public function set_current_user() {
            $this->current_user_id = get_current_user_id();
        }

        /**
         * Callback for when a post is opened for editing
         */
        public function edit_post_callback() {
             if ( isset( $_GET['post'] ) ) {
                $post_id = $_GET['post'];
                $this->sanitize_param( $post_id );
                $this->save_post_callback( $post_id );
             }
        }

        /**
         * Callback for when a post is created or updated
         */
        public function save_post_callback( $post_id ) {
            // allow external plugins to hook CoSchedule's post save hook in order to ignore certain post updates //
            // useful for plugins that do highly custom things with WordPress posts                               //
            // filter with caution as incorrect filtering could leave CoSchedule with stale data                  //
            $filter_result = apply_filters( 'tm_coschedule_save_post_callback_filter', true , $post_id );
            // Verify post is not a revision
            if ( true == $this->connected && ! wp_is_post_revision( $post_id ) && $filter_result ) {
                // Load post
                $post = $this->get_full_post( $post_id );
                $post_type = $this->get_value_or_default( $post['post_type'], 'post' );

                // poke API only for certain post_type //
                if ( $this->is_synchronizable_post_type( $post_type, false ) ) {
                    // Send to API
                    $this->api_post( '/hook/wordpress_posts/save?_wordpress_key=' . $this->token, $post );
                }
            }
        }

        /**
         * Callback for when a post is deleted
         */
        public function delete_post_callback( $post_id ) {
            // allow external plugins to hook CoSchedule's post delete hook in order to ignore certain post deletes //
            // useful for plugins that do highly custom things with WordPress posts                                 //
            // filter with caution as incorrect filtering could leave CoSchedule with stale data                    //
            $filter_result = apply_filters( 'tm_coschedule_delete_post_callback_filter', true , $post_id );
            // Verify post is not a revision
            if ( true == $this->connected && ! wp_is_post_revision( $post_id ) && $filter_result ){

                // Load post (NOTE: bypass $this->get_full_post(...) because we do not need added info) //
                $post = get_post( $post_id, "ARRAY_A" );
                $post_type = $this->get_value_or_default( $post['post_type'], 'post' );

                // poke API only for certain post_type //
                if ( $this->is_synchronizable_post_type( $post_type, false ) ) {
                    // Send to API
                    $this->api_post( '/hook/wordpress_posts/delete?_wordpress_key=' . $this->token, array( 'post_id' => $post_id ) );
                }
            }
        }

        /**
         * Callback for when a category is created or updated
         */
        public function save_category_callback( $category_id ) {
            if ( true == $this->connected ) {
                $category = get_category( $category_id, "ARRAY_A" );
                $this->api_post( '/hook/wordpress_categories/save?_wordpress_key=' . $this->token, $category );
            }
        }

        /**
         * Callback for when a category is deleted
         */
        public function delete_category_callback( $category_id ) {
            if ( true == $this->connected ) {
                $resp = $this->api_post( '/hook/wordpress_categories/delete?_wordpress_key=' . $this->token, array( 'cat_id' => $category_id ) );
            }
        }

        /**
         * Callback for when a user is created or updated
         */
        public function save_user_callback( $user_id ) {
            if ( true == $this->connected ) {
                $user = get_userdata( $user_id );

                if ( ! is_object( $user ) ) {
                    return false; // invalid user
                }

                if ( $user->has_cap( 'edit_posts' ) ) {
                    $this->api_post( '/hook/wordpress_authors/save?_wordpress_key=' . $this->token, (array) $user->data );
                } else {
                    $this->delete_user_callback( $user_id ); // Remove
                }
            }
        }

        /**
         * Callback for when a user is deleted
         */
        public function delete_user_callback( $user_id ) {
            if ( true == $this->connected ) {
                $this->api_post( '/hook/wordpress_authors/delete?_wordpress_key=' . $this->token , array( 'user_id' => $user_id ) );
            }
        }

        /**
         * Callback for when timezone_string or gmt_offset are changed
         */
        public function save_timezone_callback() {
            if ( true == $this->connected ) {
                $params = array();

                if ( $timezone_string = get_option( 'timezone_string' ) ) {
                    $params['timezone_string'] = $timezone_string;
                }
                if ( $gmt_offset = get_option( 'gmt_offset' ) ) {
                    $params['gmt_offset'] = $gmt_offset;
                }

                $this->api_post( '/hook/wordpress_keys/timezone/save?_wordpress_key=' . $this->token, $params );
            }
        }

        /**
         * Callback for when plugin build number is changed to notify the api
         */
        public function save_build_callback() {
            if ( true == $this->connected ) {
                // Update a tracking option in wordpress
                if ( true == update_option( 'tm_coschedule_synced_build', $this->build ) ) {

                    // Post new info to api
                    $params = array();
                    $params['build'] = $this->build;
                    $params['version'] = $this->version;
                    $this->api_post( '/hook/wordpress_keys/build/save?_wordpress_key=' . $this->token, $params );
                }
            }
        }

        /**
         * Post data to a url on the api
         * Returns: Result of call
         */
        public function api_post( $url, $body ) {
            $http = new WP_Http;
            $params = array(
                'method' => 'POST',
                'body'   => $this->array_decode_entities( $body )
            );
            return $http->request( $this->api . $url, $params );
        }

        /**
         * Get data from a url on the api
         * Returns: Result of call
         */
        public function api_get( $url ) {
            $http = new WP_Http;
            return $http->request( $this->api . $url );
        }

        /**
         * Get cache bust number from assets
         * Returns: Number from text file
         */
        public function get_cache_bust() {
            $location = $this->assets . '/plugin/cache_bust.txt';
            $response = '';
            $result = null;

            // Check if VIP functions exist, which will cache response
            // for fifteen minutes, with a timeout of three seconds
            if ( true == function_exists( 'wpcom_vip_file_get_contents' ) ) {
                $response = wpcom_vip_file_get_contents( $location );
            } else {
                $http = new WP_Http;
                $response = $http->request( $location );
            }

            // Validate response
            if ( true == is_string( $response ) ) {
                $result = $response;
            } else if ( true == is_array( $response ) && true == isset( $response['body'] ) ) {
                $result = $response['body'];
            } else {
                $result = '0';
            }

            return $result;
        }

        /**
         * Given an array it html_entity_decodes every element of the array that is a string.
         */
        public function array_decode_entities( $array ){
            $new_array = array();

            foreach ( $array as $key => $string ) {
                if( is_string( $string ) ) {
                    $new_array[ $key ] = html_entity_decode( $string, ENT_QUOTES );
                } else {
                    $new_array[ $key ] = $string;
                }
            }

            return $new_array;
        }

        /**
         * Edit Flow Fix: Runs before the edit flow function that modifies the post_date_gmt
         */
        public function fix_custom_status_timestamp_before( $data ) {
            // Save post_date_gmt for later
            global $cos_cached_post_date_gmt;
            if ( isset( $data['post_date_gmt'] ) && ! empty( $data['post_date_gmt'] ) ) {
                $cos_cached_post_date_gmt = $data['post_date_gmt'];
            }

            return $data;
        }

        /**
         * Edit Flow Fix: Runs after the edit flow function that modifies the post_date_gmt
         */
        public function fix_custom_status_timestamp_after( $data ) {
            global $cos_cached_post_date_gmt;
            if ( isset( $cos_cached_post_date_gmt ) && ! empty( $cos_cached_post_date_gmt ) ) {
                $data['post_date_gmt'] = $cos_cached_post_date_gmt;
            }
            return $data;
        }

        /**
         * Catch 'schedule missed draft' posts and if 'now' is within 24 hours of post_date, update post_date to now.
         */
        function conditionally_update_post_date_on_publish( $data, $postarr ) {
            try {
                if ( isset( $postarr ) && isset( $postarr['ID'] ) && isset( $postarr['post_status'] ) ) {
                    $previous_status = get_post_field( 'post_status', $postarr['ID'] );
                    $new_status = $postarr['post_status'];

                    if ( $previous_status != 'publish' && $new_status == 'publish' ) {

                        // post is transitioning to publish state //

                        if ( isset( $postarr['post_date'] ) && !empty( $postarr['post_date'] ) ) {

                            // found usable data for next test condition //

                            $now_value = strtotime( current_time( 'mysql' ) );
                            $post_date_value = strtotime( $postarr['post_date'] );
                            $the_interval = ( $now_value - $post_date_value );

                            // if 'now' is no more than 24 hours from the original post_date, force post_date to 'now' //

                            if ( $the_interval > 0 && $the_interval <= 86400 ) {

                                $new_post_date = current_time( 'mysql' );
                                $new_post_date_gmt = get_gmt_from_date( $new_post_date );

                                $data['post_date'] = $new_post_date;
                                $data['post_date_gmt'] = $new_post_date_gmt;
                            }
                        }
                    }
                }
            } catch( Exception $e ) {
                /* ignore */
            }

            // ensure $data is always returned //
            return $data;
        }

        /**
         * Get's the current post's post_type.
         */
        public function get_current_post_type() {
            global $post, $typenow, $current_screen;

            if ( ! empty( $post ) && ! empty( $post->post_type ) ) {
                //we have a post so we can just get the post type from that
                $type = $post->post_type;
            } elseif( ! empty( $typenow) ) {
                //check the global $typenow - set in admin.php
                $type = $typenow;
            } elseif( ! empty( $current_screen ) && ! empty( $current_screen->post_type ) ) {
                //check the global $current_screen object - set in sceen.php
                $type = $current_screen->post_type;
            } elseif( isset( $_REQUEST['post_type'] ) ) {
                //lastly check the post_type querystring
                $type = $_REQUEST['post_type'];
                sanitize_param( $type );
            } else {
                $type = null;
            }

            return $type;
        }

        /**
         * Helper function to sanitize elements in an array
         */
        public function sanitize_array( &$param = array() ) {
            if ( ! is_array( $param ) ) {
                $this->sanitize_param( $param );
                return;
            }

            foreach ( $param as &$p) {
                $this->sanitize_array($p);
            }
        }

        /**
         * Helper function to sanitize param
         */
        public function sanitize_param( &$param = '' ) {
            if ( is_string( $param ) ) {
                $param = esc_sql( $param );
                $param = esc_html( $param );
            }
        }

        public function cache_flush( $post_id ) {
            $cache_flush_response = array();

            try {
                // generic WP cache flush scoped to a post ID.
                // well behaved caching plugins listen for this action.
                // WPEngine (which caches outside of WP) also listens for this action.
                $cache_flush_response['clean_post_cache'] = null;
                if ( is_numeric( $post_id ) ) {
                    clean_post_cache( $post_id );
                    $cache_flush_response['clean_post_cache'] = true;
                }
            } catch ( Exception $e ) {
                $cache_flush_response['exception'] = $e->getMessage();
            }

            return $cache_flush_response;
        }

        /**
         * Function definition is based on core of https://wordpress.org/plugins/wp-missed-schedule/
         */
        public function publish_missed_schedule_posts( $post_id ) {
            global $wpdb;
            $publish_missed_schedule_posts_response = array();

            try {
                $post_date = current_time( 'mysql', 0 );
                $publish_missed_schedule_posts_response['post_date'] = $post_date;

                if ( is_numeric( $post_id ) ) {
                    $qry = "SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND ( ( post_date > 0 && post_date <= %s ) ) AND post_status = 'future' LIMIT 1";
                    $sql = $wpdb->prepare( $qry, $post_id, $post_date );
                } else {
                    $qry = "SELECT ID FROM {$wpdb->posts} WHERE ( ( post_date > 0 && post_date <= %s ) ) AND post_status = 'future' LIMIT 0,10";
                    $sql = $wpdb->prepare( $qry, $post_date );
                }
                //log('SQL: ' . $sql);
                $post_ids = $wpdb->get_col( $sql );

                $count_missed_schedule = count( $post_ids );
                $publish_missed_schedule_posts_response['count_missed_schedule'] = $count_missed_schedule;

                if ( $count_missed_schedule > 0 ) {
                    $publish_missed_schedule_posts_response['missed_schedule_post_ids'] = $post_ids;
                    foreach ( $post_ids as $post_id ) {
                        if ( !$post_id ) {
                            continue;
                        }
                        // !!! LET THE MAGIC HAPPEN !!! //
                        wp_publish_post( $post_id );
                    }
                }
            } catch ( Exception $e ) {
                $publish_missed_schedule_posts_response['exception'] = $e->getMessage();
            }

            return $publish_missed_schedule_posts_response;
        }

        // response handling functions start here //

        public function respond_empty_and_die() {
            $this->respond_text_and_die();
        }

        public function respond_json_and_die( $data ) {
            $this->respond_with_content_type_and_die( 'application/json', json_encode( $data ) );
        }

        public function respond_exception_and_die( $e ) {
            $this->respond_with_content_type_and_die( 'text/plain', 'Exception: ' . $e->getMessage() );
        }

        public function respond_text_and_die( $data ) {
            $this->respond_with_content_type_and_die( 'text/plain', $data );
        }

        public function respond_with_content_type_and_die( $content_type, $data ) {
            try {
                header( 'Pragma: no-cache' );
                header( 'Cache-Control: no-cache' );
                header( 'Expires: Thu, 01 Dec 1994 16:00:00 GMT' );
                header( 'Connection: close' );
                header( 'Content-Type: ' . $content_type );

                // response body is optional //
                if ( isset( $data ) ) {
                    echo $data;
                }
            } catch (Exception $e) {
                header( 'Content-Type: text/plain' );
                echo 'Exception in respond_with_content_type_and_die(...): ' . $e->getMessage();
            }

            die();
        }

        public function adapt_base64_decode( $encoded_value ) {
            if ( !$this->base64_decode_disabled ) {
                return base64_decode( $encoded_value );
            } else {
                return $this->cos_base64_decode( $encoded_value );
            }
        }

        /*
         * Based on example found here: http://stackoverflow.com/a/27025025
         */
        public function cos_base64_decode( $input ) {

            if ( !isset( $input ) || !is_string( $input ) ) {
                return $input;
            }

            $keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
            $chr1 = $chr2 = $chr3 = '';
            $enc1 = $enc2 = $enc3 = $enc4 = '';
            $i = 0;
            $output = '';

            // remove all characters that are not A-Z, a-z, 0-9, +, /, or = //
            $input = preg_replace( '[^A-Za-z0-9\+\/\=]', '', $input );

            do {
                $enc1 = strpos( $keyStr, substr( $input, $i++, 1 ) );
                $enc2 = strpos( $keyStr, substr( $input, $i++, 1 ) );
                $enc3 = strpos( $keyStr, substr( $input, $i++, 1 ) );
                $enc4 = strpos( $keyStr, substr( $input, $i++, 1 ) );

                $chr1 = ( $enc1 << 2 ) | ( $enc2 >> 4 );
                $chr2 = ( ( $enc2 & 15 ) << 4 ) | ( $enc3 >> 2 );
                $chr3 = ( ( $enc3 & 3 ) << 6 ) | $enc4;

                $output = $output . chr( (int) $chr1 );
                if ( $enc3 != 64 ) {
                    $output = $output . chr( (int) $chr2 );
                }
                if ( $enc4 != 64 ) {
                    $output = $output . chr( (int) $chr3 );
                }

                $chr1 = $chr2 = $chr3 = '';
                $enc1 = $enc2 = $enc3 = $enc4 = '';

            } while ( $i < strlen( $input ) );

            return urldecode( $output );
        }

        /*
         * Based on example found here: http://stackoverflow.com/a/27025025
         */
        public function cos_base64_encode( $data ) {

            if ( !isset( $data ) || !is_string( $data ) ) {
                return $data;
            }

            $b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
            $o1 = $o2 = $o3 = $h1 = $h2 = $h3 = $h4 = $bits = $i = 0;
            $ac = 0;
            $enc = '';
            $tmp_arr = array();

            if ( !$data ) {
                return data;
            }

            do {
                // pack three octets into four hexets
                $o1 = $this->charCodeAt( $data, $i++ );
                $o2 = $this->charCodeAt( $data, $i++ );
                $o3 = $this->charCodeAt( $data, $i++ );

                $bits = $o1 << 16 | $o2 << 8 | $o3;

                $h1 = $bits >> 18 & 0x3f;
                $h2 = $bits >> 12 & 0x3f;
                $h3 = $bits >> 6 & 0x3f;
                $h4 = $bits & 0x3f;

                // use hexets to index into b64, and append result to encoded string //
                $tmp_arr[$ac++] =
                    $this->charAt( $b64, $h1 )
                    .$this->charAt( $b64, $h2 )
                    .$this->charAt( $b64, $h3 )
                    .$this->charAt( $b64, $h4 );

            } while ( $i < strlen( $data ) );

            $enc = implode( $tmp_arr, '' );
            $r = ( strlen( $data ) % 3 );

            return ( $r ? substr( $enc, 0, ( $r - 3 ) ) : $enc ) . substr( '===', ( $r || 3 ) );
        }

        public function charCodeAt( $data, $char ) {
            return ord( substr( $data, $char, 1 ) );
        }

        public function charAt( $data, $char ) {
            return substr( $data, $char, 1 );
        }

    } // End TM_CoSchedule class

    global $wp_version;
    $coschedule_min_wp_version = '3.5';

    // Version guard to avoid blowing up in unsupported versions
    if ( version_compare( $wp_version, $coschedule_min_wp_version, '<' ) ) {
        if ( isset( $_REQUEST['action'] ) && ( $_REQUEST['action'] == 'error_scrape' ) ) {

            $plugin_data = get_plugin_data( __FILE__, false );

            $activation_error = '<div class="error">';
            $activation_error .= '<strong>' . esc_html( $plugin_data['Name'] ) . '</strong> requires <strong>WordPress ' . $coschedule_min_wp_version . '</strong> or higher, and has been deactivated!<br/><br/>'.
                                    'Please upgrade WordPress and try again.';
            $activation_error .= '</div>';

            die( $activation_error );  // die() to stop execution
        } else {
            trigger_error( $message, E_USER_ERROR ); // throw an error, execution flow returns
        }
        // note, no need for return here as error or die will return execution to caller
    }

    // Passed version check
    return new TM_CoSchedule();

}
