<?php
/**
 * Rewards UI class.
 *
 * @since 2.1.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_UI_Rewards {

    /**
     * Holds the class object.
     *
     * @since 2.1.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 2.1.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 2.1.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 2.1.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster_Vendor::get_instance();

    }

    /**
     * Outputs the optin view.
     *
     * @since 2.1.0
     *
     * @return bool $ret True if no issues, false otherwise.
     */
    public function view() {

		$data   = $this->base->get_data();
		$errors = $this->base->get_data_errors();
		$option = get_option( 'optin_monster' );
		$ret    = true;

		if ( ! $data || $errors ) :
			$ret = false;
			if ( ! $data && ! $errors ) :
	        ?>
	        <h2><?php _e( 'Uh Oh - Archie Found an Issue!', 'optin-monster' ); ?></h2>
	        <div class="optin-monster optin-monster-rewards optin-monster-clear">
				<div class="error">
					<p><?php _e( 'You must enter and verify your OptinMonster license key before you can use OptinMonster.', 'optin-monster' ); ?></p>
				</div>
				<p><?php printf( __( '<strong>It appears that you have not entered and verified your OptinMonster license key yet.</strong> Your license key can be found in your email purchase receipt or in your Account area. <a href="%s" target="_blank">Click here to learn how to verify your OptinMonster license key.</a>', 'optin-monster' ), 'http://optinmonster.com/docs/how-to-verify-your-optinmonster-license-key/' ); ?></p>
				<div id="optin-monster-settings">
		            <table class="form-table">
		                <tbody>
		                    <tr id="optin-monster-settings-key-box">
		                        <th scope="row">
		                            <label for="optin-monster-settings-key"><?php _e( 'OptinMonster License Key', 'optin-monster' ); ?></label>
		                        </th>
		                        <td>
		                            <form id="optin-monster-settings-verify-key" method="post">
		                                <input type="password" name="optin-monster-license-key" id="optin-monster-settings-key" value="" placeholder="<?php esc_attr_e( 'OptinMonster license key here...', 'optin-monster' ); ?>" />
		                                <?php wp_nonce_field( 'optin-monster-key-nonce', 'optin-monster-key-nonce' ); ?>
		                                <?php submit_button( __( 'Verify Key', 'optin-monster' ), 'primary', 'optin-monster-verify-submit', false ); ?>
		                            </form>
		                        </td>
		                    </tr>
		                </tbody>
		            </table>
				</div>
	        </div>
	        <?php
	        elseif ( $data && $errors ) :
				if ( isset( $option['is_expired'] ) && $option['is_expired'] ) :
		        ?>
		        <h2><?php _e( 'Uh Oh - Archie Found an Issue!', 'optin-monster' ); ?></h2>
		        <div class="optin-monster optin-monster-rewards optin-monster-clear">
					<div class="error">
						<p><?php _e( 'Your OptinMonster license key has expired. You need to renew your license key in order to continue using the plugin.', 'optin-monster' ); ?></p>
					</div>
					<?php if ( isset( $option['expired_id'] ) && $option['expired_id'] ) : ?>
					<p><?php printf( __( '<strong>It appears that your license key has expired.</strong> In order to continue using OptinMonster on your website, you need to renew your license key. <a href="%s" target="_blank">Click here to renew this site\'s license key</a> or <a href="%s" target="_blank">click here to login to your account and renew it there.</a>', 'optin-monster' ), 'https://optinmonster.com/checkout/?edd_license_key=' . $data . '&download_id=' . $option['expired_id'] . '&nocache=true', 'https://optinmonster.com/account/' ); ?></p>
					<?php else : ?>
					<p><?php printf( __( '<strong>It appears that your license key has expired.</strong> In order to continue using OptinMonster on your website, you need to renew your license key. <a href="%s" target="_blank">Click here to login to your account and renew it there.</a>', 'optin-monster' ), 'https://optinmonster.com/account/' ); ?></p>
					<?php endif; ?>
					<div id="optin-monster-settings">
			            <table class="form-table">
			                <tbody>
			                    <tr id="optin-monster-settings-key-box">
			                        <th scope="row">
			                            <label for="optin-monster-settings-key"><?php _e( 'OptinMonster License Key', 'optin-monster' ); ?></label>
			                        </th>
			                        <td>
			                            <form id="optin-monster-settings-verify-key" method="post">
			                                <input type="password" name="optin-monster-license-key" id="optin-monster-settings-key" value="" placeholder="<?php esc_attr_e( 'OptinMonster license key here...', 'optin-monster' ); ?>" />
			                                <?php wp_nonce_field( 'optin-monster-key-nonce', 'optin-monster-key-nonce' ); ?>
			                                <?php submit_button( __( 'Verify Key', 'optin-monster' ), 'primary', 'optin-monster-verify-submit', false ); ?>
			                            </form>
			                        </td>
			                    </tr>
			                </tbody>
			            </table>
					</div>
		        </div>
		        <?php
		        elseif ( isset( $option['is_disabled'] ) && $option['is_disabled'] ) :
		        ?>
		        <h2><?php _e( 'Uh Oh - Archie Found an Issue!', 'optin-monster' ); ?></h2>
		        <div class="optin-monster optin-monster-rewards optin-monster-clear">
					<div class="error">
						<p><?php _e( 'Your OptinMonster license key has been disabled. Your key needs to be re-enabled to continue using the plugin.', 'optin-monster' ); ?></p>
					</div>
					<p><?php printf( __( '<strong>It appears that your license key has been disabled.</strong> This likely means that your key is associated with a refunded purchase or has been manually revoked by one of our staff for violation of terms. You need to enter a different, valid license key to continue using OptinMonster. <a href="%s" target="_blank">Click here to login to your account for more information.</a>', 'optin-monster' ), 'https://optinmonster.com/account/' ); ?></p>
					<div id="optin-monster-settings">
			            <table class="form-table">
			                <tbody>
			                    <tr id="optin-monster-settings-key-box">
			                        <th scope="row">
			                            <label for="optin-monster-settings-key"><?php _e( 'OptinMonster License Key', 'optin-monster' ); ?></label>
			                        </th>
			                        <td>
			                            <form id="optin-monster-settings-verify-key" method="post">
			                                <input type="password" name="optin-monster-license-key" id="optin-monster-settings-key" value="" placeholder="<?php esc_attr_e( 'OptinMonster license key here...', 'optin-monster' ); ?>" />
			                                <?php wp_nonce_field( 'optin-monster-key-nonce', 'optin-monster-key-nonce' ); ?>
			                                <?php submit_button( __( 'Verify Key', 'optin-monster' ), 'primary', 'optin-monster-verify-submit', false ); ?>
			                            </form>
			                        </td>
			                    </tr>
			                </tbody>
			            </table>
					</div>
		        </div>
		        <?php
		        elseif ( isset( $option['is_invalid'] ) && $option['is_invalid'] ) :
		        ?>
		        <h2><?php _e( 'Uh Oh - Archie Found an Issue!', 'optin-monster' ); ?></h2>
		        <div class="optin-monster optin-monster-rewards optin-monster-clear">
					<div class="error">
						<p><?php _e( 'Your OptinMonster license key is invalid. The key no longer exists, the user associated with the key has been deleted or your key has reached its activation limit.', 'optin-monster' ); ?></p>
					</div>
					<p><?php printf( __( '<strong>It appears that your license key is invalid.</strong> This means that your key no longer exists, the user associated with the key has been deleted or your key has reached its site activation limit. You need to enter a different, valid license key to continue using OptinMonster. <a href="%s" target="_blank">Click here to login to your account for more information.</a>', 'optin-monster' ), 'https://optinmonster.com/account/' ); ?></p>
					<div id="optin-monster-settings">
			            <table class="form-table">
			                <tbody>
			                    <tr id="optin-monster-settings-key-box">
			                        <th scope="row">
			                            <label for="optin-monster-settings-key"><?php _e( 'OptinMonster License Key', 'optin-monster' ); ?></label>
			                        </th>
			                        <td>
			                            <form id="optin-monster-settings-verify-key" method="post">
			                                <input type="password" name="optin-monster-license-key" id="optin-monster-settings-key" value="" placeholder="<?php esc_attr_e( 'OptinMonster license key here...', 'optin-monster' ); ?>" />
			                                <?php wp_nonce_field( 'optin-monster-key-nonce', 'optin-monster-key-nonce' ); ?>
			                                <?php submit_button( __( 'Verify Key', 'optin-monster' ), 'primary', 'optin-monster-verify-submit', false ); ?>
			                            </form>
			                        </td>
			                    </tr>
			                </tbody>
			            </table>
					</div>
		        </div>
		        <?php
		        endif;
	        endif;
        endif;

        return $ret;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.1.0
     *
     * @return object The Optin_Monster_UI_Rewards object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_UI_Rewards ) ) {
            self::$instance = new Optin_Monster_UI_Rewards();
        }

        return self::$instance;

    }

}

// Load the rewards UI class.
$optin_monster_ui_rewards = Optin_Monster_UI_Rewards::get_instance();