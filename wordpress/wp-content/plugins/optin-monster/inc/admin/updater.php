<?php
/**
 * Updater class for OptinMonster.
 *
 * @since 1.0.0
 *
 * @package	OptinMonster
 * @author	Thomas Griffin
 */
class optin_monster_updater {

    /**
     * Instance.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Plugin name.
     *
     * @since 1.0.0
     *
     * @var bool|string
     */
    public $plugin_name = false;

    /**
     * Plugin slug.
     *
     * @since 1.0.0
     *
     * @var bool|string
     */
    public $plugin_slug = false;

    /**
     * Plugin path.
     *
     * @since 1.0.0
     *
     * @var bool|string
     */
    public $plugin_path = false;

    /**
     * URL of the plugin.
     *
     * @since 1.0.0
     *
     * @var bool|string
     */
    public $plugin_url = false;

    /**
     * Remote URL for getting plugin updates.
     *
     * @since 1.0.0
     *
     * @var bool|string
     */
    public $remote_url = false;

    /**
     * Version number of the plugin.
     *
     * @since 1.0.0
     *
     * @var bool|int
     */
    public $version = false;

    /**
     * License key for the plugin.
     *
     * @since 1.0.0
     *
     * @var bool|string
     */
    public $key = false;

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
     * Primary class constructor.
     *
     * @since 1.0.0
     *
     * @param array $config Array of updater config args.
     */
    public function __construct( array $config ) {

        // Set the instance.
        self::$instance = $this;

        // Set class properties.
        $accepted_args = array(
            'plugin_name',
            'plugin_slug',
            'plugin_path',
            'plugin_url',
            'remote_url',
            'version',
            'key'
        );
        foreach ( $accepted_args as $arg ) {
            $this->$arg = $config[$arg];
        }

        // If the user cannot update plugins, stop processing here.
        if ( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        global $pagenow;

        // Only run every 12 hours.
        $timestamp = get_option( 'optin_monster_updates_' . $this->plugin_slug );
        if ( ! $timestamp ) {
            $timestamp = strtotime( '+12 hours' );
            update_option( 'optin_monster_updates_' . $this->plugin_slug, $timestamp );
        } else {
            $current_timestamp = time();
            if ( $current_timestamp < $timestamp && 'update-core.php' !== $pagenow && 'update.php' !== $pagenow && 'plugins.php' !== $pagenow && 'plugin-install.php' !== $pagenow ) {
                return;
            } else {
                update_option( 'optin_monster_updates_' . $this->plugin_slug, strtotime( '+12 hours' ) );
            }
        }

        // Load the updater hooks and filters.
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_plugins_filter' ) );
        add_filter( 'http_request_args', array( $this, 'http_request_args' ), 10, 2 );
        add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );

    }

    /**
     * Infuse plugin update details when WordPress runs its update checker.
     *
     * @since 1.0.0
     *
     * @param object $value  The WordPress update object.
     * @return object $value Amended WordPress update object on success, default if object is empty.
     */
    public function update_plugins_filter( $value ) {

        // If no update object exists, return early.
        if ( empty( $value ) ) {
            return $value;
        }

        // Run update check by pinging the external API. If it fails, return the default update object.
        $plugin_update = $this->perform_remote_request( 'get-plugin-update', array( 'tgm-updater-plugin' => $this->plugin_slug ) );
        if ( ! $plugin_update || ! empty( $plugin_update->error ) ) {
            return $value;
        }

        // Infuse the update object with our data if the version from the remote API is newer.
        if ( isset( $plugin_update->new_version ) && version_compare( $this->version, $plugin_update->new_version, '<' ) ) {
            // The $plugin_update object contains new_version, package, slug and last_update keys.
            $value->response[$this->plugin_path] = $plugin_update;
        }

        // Return the update object.
        return $value;

    }

    /**
     * Disables SSL verification to prevent download package failures.
     *
     * @since 1.0.0
     *
     * @param array $args  Array of request args.
     * @param string $url  The URL to be pinged.
     * @return array $args Amended array of request args.
     */
    public function http_request_args( $args, $url ) {

        // If this is an SSL request and we are performing an upgrade routine, disable SSL verification.
        if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'tgm-updater-action=get-plugin-update' ) ) {
            $args['sslverify'] = false;
        }

        return $args;

    }

    /**
     * Filters the plugins_api function to get our own custom plugin information
     * from our private repo.
     *
     * @since 1.0.0
     *
     * @param object $api    The original plugins_api object.
     * @param string $action The action sent by plugins_api.
     * @param array $args    Additional args to send to plugins_api.
     * @return object $api   New stdClass with plugin information on success, default response on failure.
     */
    public function plugins_api( $api, $action = '', $args = null ) {

        $plugin = ( 'plugin_information' == $action ) && isset( $args->slug ) && ( $this->plugin_slug == $args->slug );

        // If our plugin matches the request, set our own plugin data, else return the default response.
        if ( $plugin ) {
            return $this->set_plugins_api( $api );
        } else {
            return $api;
        }

    }

    /**
     * Pings a remote API to retrieve plugin information for WordPress to display.
     *
     * @since 1.0.0
     *
     * @param object $default_api The default API object.
     * @return object $api        Return custom plugin information to plugins_api.
     */
    public function set_plugins_api( $default_api ) {

        // Perform the remote request to retrieve our plugin information. If it fails, return the default object.
        $plugin_info = $this->perform_remote_request( 'get-plugin-info', array( 'tgm-updater-plugin' => $this->plugin_slug ) );
        if ( ! $plugin_info || ! empty( $plugin_update->error ) ) {
            return $default_api;
        }

        // Create a new stdClass object and populate it with our plugin information.
        $api                        = new stdClass;
        $api->name                  = isset( $plugin_info->name )           ? $plugin_info->name           : '';
        $api->slug                  = isset( $plugin_info->slug )           ? $plugin_info->slug           : '';
        $api->version               = isset( $plugin_info->version )        ? $plugin_info->version        : '';
        $api->author                = isset( $plugin_info->author )         ? $plugin_info->author         : '';
        $api->author_profile        = isset( $plugin_info->author_profile ) ? $plugin_info->author_profile : '';
        $api->requires              = isset( $plugin_info->requires )       ? $plugin_info->requires       : '';
        $api->tested                = isset( $plugin_info->tested )         ? $plugin_info->tested         : '';
        $api->last_updated          = isset( $plugin_info->last_updated )   ? $plugin_info->last_updated   : '';
        $api->homepage              = isset( $plugin_info->homepage )       ? $plugin_info->homepage       : '';
        $api->sections['changelog'] = isset( $plugin_info->changelog )      ? $plugin_info->changelog      : '';
        $api->download_link         = isset( $plugin_info->download_link )  ? $plugin_info->download_link  : '';

        // Return the new API object with our custom data.
        return $api;

    }

    /**
     * Queries the remote URL via wp_remote_post and returns a json decoded response.
     *
     * @since 1.0.0
     *
     * @param string $action        The name of the $_POST action var.
     * @param array $body           The content to retrieve from the remote URL.
     * @param array $headers        The headers to send to the remote URL.
     * @param string $return_format The format for returning content from the remote URL.
     * @return string|bool          Json decoded response on success, false on failure.
     */
    public function perform_remote_request( $action, $body = array(), $headers = array(), $return_format = 'json' ) {

        // Build the body of the request.
        $body = wp_parse_args(
            $body,
            array(
                'tgm-updater-action'     => $action,
                'tgm-updater-key'        => $this->key,
                'tgm-updater-wp-version' => get_bloginfo( 'version' ),
                'tgm-updater-referer'    => site_url()
            )
        );
        $body = http_build_query( $body, '', '&' );

        // Build the headers of the request.
        $headers = wp_parse_args(
            $headers,
            array(
                'Content-Type'   => 'application/x-www-form-urlencoded',
                'Content-Length' => strlen( $body )
            )
        );

        // Setup variable for wp_remote_post.
        $post = array(
            'headers' => $headers,
            'body'    => $body
        );

        // Perform the query and retrieve the response.
        $response      = wp_remote_post( esc_url_raw( $this->remote_url ), $post );
        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        // Bail out early if there are any errors.
        if ( 200 != $response_code || is_wp_error( $response_body ) ) {
            return false;
        }

        // Return the json decoded content.
        return json_decode( $response_body );

    }

    /**
	 * Queries the remote URL via wp_remote_post and returns a json decoded response.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action        The name of the $_POST action var.
	 * @param array $body           The content to retrieve from the remote URL.
	 * @param array $headers        The headers to send to the remote URL.
	 * @param string $return_format The format for returning content from the remote URL.
	 * @return string|boolean       Json decoded response on success, false on failure.
	 */
	public static function perform_outside_remote_request( $action, $body = array(), $headers = array(), $return_format = 'json' ) {

        return self::$instance->perform_remote_request( $action, $body, $headers, $return_format );

	}

}