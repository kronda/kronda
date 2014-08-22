<?php
/**
 * OptinMonster is the #1 lead generation and email list building tool.
 *
 * @package   OptinMonster
 * @author    Thomas Griffin
 * @license   GPL-2.0+
 * @link      http://optinmonster.com/
 * @copyright 2013 Retyp, LLC. All rights reserved.
 *
 * @wordpress-plugin
 * Plugin Name:  OptinMonster Exit Intent
 * Plugin URI:   http://optinmonster.com/
 * Description:  Adds "Exit Intent" functionality to OptinMonster optins.
 * Version:      1.0.1
 * Author:       Thomas Griffin
 * Author URI:   http://thomasgriffinmedia.com/
 * Text Domain:  optin-monster-exit
 * Contributors: griffinjt
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:  /lang
 */

add_action( 'init', 'om_exit_automatic_upgrades', 20 );
function om_exit_automatic_upgrades() {

    global $optin_monster_license;

    // Load the plugin updater.
    if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) :
        if ( ! empty( $optin_monster_license['key'] ) ) {
			$args = array(
				'remote_url' 	=> 'http://optinmonster.com/',
				'version' 		=> '1.0.1',
				'plugin_name'	=> 'OptinMonster Exit Intent',
				'plugin_slug' 	=> 'optin-monster-exit',
				'plugin_path' 	=> plugin_basename( __FILE__ ),
				'plugin_url' 	=> WP_PLUGIN_URL . '/optin-monster-exit',
				'time' 			=> 43200,
				'key' 			=> $optin_monster_license['key']
			);

			// Load the updater class.
			$optin_monster_exit_updater = new optin_monster_updater( $args );
		}
    endif;

}

add_action( 'optin_monster_config_settings', 'om_exit_config', 10, 2 );
function om_exit_config( $optin, $type ) {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    echo '<div class="optin-config-box">';
		echo '<h4><label for="optin-exit">Use Exit Intent?</label></h4>';
        echo '<input id="optin-exit" type="checkbox" name="optin_exit" value="' . $tab->get_field( 'exit' ) . '"' . checked( $tab->get_field( 'exit' ), 1, false ) . ' />';
        echo '<label class="description" for="optin-exit" style="font-weight:400;display:inline;margin-left:5px">Show the optin when a user navigates their mouse outside of the website window <strong>(ignores loading delay setting).</strong></label>';
	echo '</div>';

}

add_action( 'optin_monster_save_config', 'om_exit_save_config', 10, 4 );
function om_exit_save_config( $meta, $data, $id, $type ) {

    $meta['exit'] = isset( $data['optin_exit'] ) ? 1 : 0;
    update_post_meta( $id, '_om_meta', $meta );

}