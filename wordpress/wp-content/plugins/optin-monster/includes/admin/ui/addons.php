<?php
/**
 * Admin UI settings class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_UI_Addons {

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
    public $tab = 'addons';

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Display the UI.
        $this->display();

    }

    /**
     * Displays the UI view.
     *
     * @since 2.0.0
     */
    public function display() {

        // Go ahead and grab the type of license. It will be necessary for displaying Addons.
        $type = $this->base->get_license_key_type();

        // Only display the Addons information if no license key errors are present.
        if ( ! $this->base->get_license_key_errors() ) :
        ?>
        <div id="optin-monster-settings-<?php echo $this->tab; ?>">
            <?php if ( empty( $type ) ) : ?>
                <div class="error below-h2"><p><?php _e( 'In order to get access to Addons, you need to verify your license key for OptinMonster.', 'optin-monster' ); ?></p></div>
            <?php else : ?>
                <?php $addons = $this->get_addons(); if ( $addons ) : ?>
                    <form id="optin-monster-settings-refresh-addons-form" method="post">
                        <p><?php _e( 'Missing addons that you think you should be able to see? Try clicking the button below to refresh the addon data.', 'optin-monster' ); ?></p>
                        <?php wp_nonce_field( 'optin-monster-refresh-addons', 'optin-monster-refresh-addons' ); ?>
                        <?php submit_button( __( 'Refresh Addons', 'optin-monster' ), 'primary', 'optin-monster-refresh-addons-submit', false ); ?>
                    </form>
                    <div id="optin-monster-addons-area" class="optin-monster-clear">
                        <?php
                        // Let's begin outputting the addons.
                        $i = 0;
                        foreach ( (array) $addons as $i => $addon ) {
                            // Attempt to get the plugin basename if it is installed or active.
                            $plugin_basename   = $this->get_plugin_basename_from_slug( $addon->slug );
                            $installed_plugins = get_plugins();
                            $last              = ( 0 == $i%3 ) ? 'last' : '';
                            $url               = Optin_Monster_Menu_Admin::get_instance()->is_ssl() ? str_replace( 'http://', 'https://', $addon->image ) : $addon->image;

                            echo '<div class="optin-monster-addon ' . $last . '">';
                                echo '<img class="optin-monster-addon-thumb" src="' . esc_url( $url ) . '" width="300px" height="250px" alt="' . esc_attr( $addon->title ) . '" />';
                                echo '<h3 class="optin-monster-addon-title">' . esc_html( $addon->title ) . '</h3>';

                                // If the plugin is active, display an active message and deactivate button.
                                if ( is_plugin_active( $plugin_basename ) ) {
                                    echo '<div class="optin-monster-addon-active optin-monster-addon-message">';
                                        echo '<span class="addon-status">' . __( 'Status: Active', 'optin-monster' ) . '</span>';
                                        echo '<div class="optin-monster-addon-action">';
                                            echo '<a class="button button-primary optin-monster-addon-action-button optin-monster-deactivate-addon" href="#" rel="' . esc_attr( $plugin_basename ) . '">' . __( 'Deactivate', 'optin-monster' ) . '</a><span class="spinner optin-monster-spinner"></span>';
                                        echo '</div>';
                                    echo '</div>';
                                }

                                // If the plugin is not installed, display an install message and install button.
                                if ( ! isset( $installed_plugins[$plugin_basename] ) ) {
                                    echo '<div class="optin-monster-addon-not-installed optin-monster-addon-message">';
                                        echo '<span class="addon-status">' . __( 'Status: Not Installed', 'optin-monster' ) . '</span>';
                                        echo '<div class="optin-monster-addon-action">';
                                            echo '<a class="button button-primary optin-monster-addon-action-button optin-monster-install-addon" href="#" rel="' . esc_url( $addon->url ) . '">' . __( 'Install Addon', 'optin-monster' ) . '</a><span class="spinner optin-monster-spinner"></span>';
                                        echo '</div>';
                                    echo '</div>';
                                }
                                // If the plugin is installed but not active, display an activate message and activate button.
                                elseif ( is_plugin_inactive( $plugin_basename ) ) {
                                    echo '<div class="optin-monster-addon-inactive optin-monster-addon-message">';
                                        echo '<span class="addon-status">' . __( 'Status: Inactive', 'optin-monster' ) . '</span>';
                                        echo '<div class="optin-monster-addon-action">';
                                            echo '<a class="button button-primary optin-monster-addon-action-button optin-monster-activate-addon" href="#" rel="' . esc_attr( $plugin_basename ) . '">' . __( 'Activate', 'optin-monster' ) . '</a><span class="spinner optin-monster-spinner"></span>';
                                        echo '</div>';
                                    echo '</div>';
                                }

                                echo '<p class="optin-monster-addon-excerpt">' . esc_html( $addon->excerpt ) . '</p>';
                            echo '</div>';
                            $i++;
                        }
                        ?>
                    </div>
                <?php elseif ( 'basic' == $type ) : ?>
                    <form id="optin-monster-settings-refresh-<?php echo $this->tab; ?>-form" method="post">
                        <p><?php _e( 'The basic OptinMonster license does not include addons. In order to get access to addons, you need to upgrade your license to a higher plan. You can do this by visiting your OptinMonster account and clicking on the "Upgrade" tab to see which plan is right for you.', 'optin-monster' ); ?></p>
                    </form>
                <?php else : ?>
                    <form id="optin-monster-settings-refresh-<?php echo $this->tab; ?>-form" method="post">
                        <p><?php _e( 'There was an issue retrieving the addons for this site. Please click on the button below the refresh the addons data.', 'optin-monster' ); ?></p>
                        <?php wp_nonce_field( 'optin-monster-refresh-addons', 'optin-monster-refresh-addons' ); ?>
                        <?php submit_button( __( 'Refresh Addons', 'optin-monster' ), 'primary', 'optin-monster-refresh-addons-submit', false ); ?>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php else : ?>
            <div class="error below-h2"><p><?php _e( 'In order to get access to Addons, you need to resolve your license key errors.', 'optin-monster' ); ?></p></div>
        <?php
        endif;

    }

    /**
     * Retrieves addons from the stored transient or remote server.
     *
     * @since 2.0.0
     *
     * @return bool|array False if no key or failure, array of addon data otherwise.
     */
    public function get_addons() {

        return Optin_Monster_Menu_Admin::get_instance()->get_addons();

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

        return Optin_Monster_Menu_Admin::get_instance()->get_addons_data( $key );

    }

    /**
     * Flag to determine if addons are being refreshed.
     *
     * @since 2.0.0
     *
     * @return bool True if being refreshed, false otherwise.
     */
    public function is_refreshing_addons() {

        return Optin_Monster_Menu_Admin::get_instance()->is_refreshing_addons();

    }

    /**
     * Verifies nonces that allow addon refreshing.
     *
     * @since 2.0.0
     *
     * @return bool True if nonces check out, false otherwise.
     */
    public function refresh_addons_action() {

        return Optin_Monster_Menu_Admin::get_instance()->refresh_addons_action();

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

        return Optin_Monster_Menu_Admin::get_instance()->get_plugin_basename_from_slug( $slug );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Posttype object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_UI_Addons ) ) {
            self::$instance = new Optin_Monster_UI_Addons();
        }

        return self::$instance;

    }

}

// Load the admin UI settings class.
$optin_monster_ui_settings = Optin_Monster_UI_Addons::get_instance();