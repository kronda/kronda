<?php
/**
 * Admin UI settings class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_UI_Settings {

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
    public $tab = 'settings';

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

        $option = get_option( 'optin_monster' );
        ?>
        <div id="optin-monster-settings">
            <table class="form-table">
                <tbody>
                    <tr id="optin-monster-settings-key-box">
                        <th scope="row">
                            <label for="optin-monster-settings-key"><?php _e( 'OptinMonster License Key', 'optin-monster' ); ?></label>
                        </th>
                        <td>
                            <form id="optin-monster-settings-verify-key" method="post">
                                <input type="password" name="optin-monster-license-key" id="optin-monster-settings-key" value="<?php echo ( $this->base->get_license_key() ? $this->base->get_license_key() : '' ); ?>" />
                                <?php wp_nonce_field( 'optin-monster-key-nonce', 'optin-monster-key-nonce' ); ?>
                                <?php submit_button( __( 'Verify Key', 'optin-monster' ), 'primary', 'optin-monster-verify-submit', false ); ?>
                                <?php submit_button( __( 'Deactivate Key', 'optin-monster' ), 'secondary', 'optin-monster-deactivate-submit', false ); ?>
                                <p class="description"><?php _e( 'License key to enable automatic updates for OptinMonster.', 'optin-monster' ); ?></p>
                            </form>
                        </td>
                    </tr>
                    <?php $type = $this->base->get_license_key_type(); if ( ! empty( $type ) ) : ?>
                    <tr id="optin-monster-settings-key-type-box">
                        <th scope="row">
                            <label for="optin-monster-settings-key-type"><?php _e( 'OptinMonster License Type', 'optin-monster' ); ?></label>
                        </th>
                        <td>
                            <form id="optin-monster-settings-key-type" method="post">
                                <span class="optin-monster-license-type"><?php printf( __( 'Your license key type for this site is <strong>%s.</strong>', 'optin-monster' ), $this->base->get_license_key_type() ); ?>
                                <input type="hidden" name="optin-monster-license-key" value="<?php echo $this->base->get_license_key(); ?>" />
                                <?php wp_nonce_field( 'optin-monster-key-nonce', 'optin-monster-key-nonce' ); ?>
                                <?php submit_button( __( 'Refresh Key', 'optin-monster' ), 'primary', 'optin-monster-refresh-submit', false ); ?>
                                <p class="description"><?php _e( 'Your license key type (handles updates and Addons). Click refresh if your license has been upgraded or the type is incorrect.', 'optin-monster' ); ?></p>
                            </form>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr id="optin-monster-settings-cookie-box">
                        <th scope="row">
                            <label for="optin-monster-settings-cookie"><?php _e( 'OptinMonster Global Cookie', 'optin-monster' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="optin-monster-settings-cookie" name="cookie" value="<?php echo ( isset( $option['cookie'] ) ? absint( $option['cookie'] ) : 0 ); ?>" />
                            <p class="description"><?php _e( 'Entering a number (e.g. 30) will set a global cookie once any optin has resulted in a successful conversion. This global cookie will prevent any other optins from loading on your site for that visitor until the cookie expires. Defaults to 0 (no global cookie).', 'optin-monster' ); ?></p>
                        </td>
                    </tr>
                    <tr id="optin-monster-settings-affiliate-link-box">
                        <th scope="row">
                            <label for="optin-monster-settings-affiliate-link"><?php _e( 'OptinMonster Affiliate Link', 'optin-monster' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="optin-monster-settings-affiliate-link" name="affiliate-link" value="<?php echo ( isset( $option['affiliate_link'] ) ? esc_url( $option['affiliate_link'] ) : '' ); ?>" />
                            <p class="description"><?php printf( __( 'You can earn money by promoting OptinMonster! <a href="%s" target="_blank">Join our affiliate program</a>, and once you have joined, you can paste your OptinMonster affiliate link here. Once entered, it will replace the default OptinMonster "Powered by" link on your optins.', 'optin-monster' ), 'http://optinmonster.com/affiliates/' ); ?></p>
                        </td>
                    </tr>
                    <tr id="optin-monster-settings-affiliate-link-position-box">
                        <th scope="row">
                            <label for="optin-monster-settings-affiliate-link-position"><?php _e( 'OptinMonster Affiliate Link Position', 'optin-monster' ); ?></label>
                        </th>
                        <td>
                            <select id="optin-monster-settings-affiliate-link-position" name="affiliate-link-position">
                                <option value="under" <?php selected( 'under', ( isset( $option['affiliate_link_position'] ) ? $option['affiliate_link_position'] : 'under' ) ); ?>><?php _e( 'Under', 'optin-monster' ); ?></option>
                                <option value="bottom" <?php selected( 'bottom', ( isset( $option['affiliate_link_position'] ) ? $option['affiliate_link_position'] : 'under' ) ); ?>><?php _e( 'Bottom', 'optin-monster' ); ?></option>
                            </select>
                            <p class="description"><?php _e( 'Sets the position of the affiliate link relative to the optin (underneath or at the bottom left corner of the screen).', 'optin-monster' ); ?></p>
                        </td>
                    </tr>
                    <tr id="optin-monster-settings-leads-box">
                        <th scope="row">
                            <label for="optin-monster-settings-leads"><?php _e( 'Store Leads Locally?', 'optin-monster' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="optin-monster-settings-leads" name="leads" value="<?php echo ( isset( $option['leads'] ) && $option['leads'] ? 1 : 0 ); ?>"<?php checked( ( isset( $option['leads'] ) && $option['leads'] ? 1 : 0 ), 1 ); ?> />
                            <span class="description"><?php _e( 'If checked, successful optin leads will be stored locally in addition to your email service provider.', 'optin-monster' ); ?></span>
                        </td>
                    </tr>
                    <tr id="optin-monster-settings-admin-preview-box">
                        <th scope="row">
                            <label for="optin-monster-settings-admin-preview"><?php _e( 'Use Admin Preview?', 'optin-monster' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="optin-monster-settings-admin-preview" name="admin_preview" value="<?php echo ( isset( $option['admin_preview'] ) && $option['admin_preview'] ? 1 : 0 ); ?>"<?php checked( ( isset( $option['admin_preview'] ) && $option['admin_preview'] ? 1 : 0 ), 1 ); ?> />
                            <span class="description"><?php _e( 'If checked, optin previews will be loaded inside of a blank admin page instead of a page on your live site. This setting is helpful if you are having trouble with the live preview because of SSL or cross-domain requirements. <strong>You may experience some design inconsistencies with this setting checked since it is not a true live preview.</strong>', 'optin-monster' ); ?></span>
                        </td>
                    </tr>
                    <tr id="optin-monster-settings-reporting-box">
	                    <th scope="row">
		                    <label for="optin-monster-settings-reporting"><?php _e( 'Send Stats to OptinMonster?', 'optin-monster' ); ?></label>
	                    </th>
	                    <td>
		                    <input type="checkbox" id="optin-monster-settings-reporting" name="allow_reporting" value="<?php echo ( isset( $option['allow_reporting'] ) && $option['allow_reporting'] ? 1 : 0 ); ?>"<?php checked( ( isset( $option['allow_reporting'] ) && $option['allow_reporting'] ? 1 : 0 ), 1 ); ?> />
		                    <span class="description"><?php _e( 'If checked, data about how you use the plugin will be sent securely to OptinMonster. This data will help guide future development.', 'optin-monster' ); ?></span>
	                    </td>
                    </tr>
                    <?php do_action( 'optin_monster_settings_box', $option, $this ); ?>
                </tbody>
            </table>
            <p><a href="#" class="button button-primary om-save-settings" title="<?php esc_attr_e( 'Save Settings', 'optin-monster' ); ?>"><?php _e( 'Save Settings', 'optin-monster' ); ?></a></p>
        </div>
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

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_UI_Settings ) ) {
            self::$instance = new Optin_Monster_UI_Settings();
        }

        return self::$instance;

    }

}

// Load the admin UI settings class.
$optin_monster_ui_settings = Optin_Monster_UI_Settings::get_instance();