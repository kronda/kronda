<?php
/**
 * Plugin Name: OptinMonster - Sidebar Addon
 * Plugin URI:  http://optinmonster.com
 * Description: Adds a new optin type - Sidebar - to the available optins.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     2.0.0
 * Text Domain: optin-monster-sidebar
 * Domain Path: languages
 *
 * OptinMonster is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OptinMonster is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OptinMonster. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define necessary addon constants.
define( 'OPTIN_MONSTER_SIDEBAR_PLUGIN_NAME', 'OptinMonster - Sidebar Addon' );
define( 'OPTIN_MONSTER_SIDEBAR_PLUGIN_VERSION', '2.0.0' );
define( 'OPTIN_MONSTER_SIDEBAR_PLUGIN_SLUG', 'optin-monster-sidebar' );

add_action( 'plugins_loaded', 'optin_monster_sidebar_plugins_loaded' );
/**
 * Ensures the full OptinMonster plugin is active before proceeding.
 *
 * @since 2.0.0
 *
 * @return null Return early if OptinMonster is not active.
 */
function optin_monster_sidebar_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Optin_Monster' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'optin_monster_init', 'optin_monster_sidebar_plugin_init' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 2.0.0
 */
function optin_monster_sidebar_plugin_init() {

    // Add necessary image sizes for the optin themes.
    add_image_size( 'optin-monster-sidebar-theme-banner', 100, 150, true );
    add_image_size( 'optin-monster-sidebar-theme-fabric', 100, 150, true );
    add_image_size( 'optin-monster-sidebar-theme-postal', 100, 150, true );

    add_action( 'optin_monster_updater', 'optin_monster_sidebar_updater' );
    add_filter( 'optin_monster_theme_types', 'optin_monster_sidebar_filter_optin_type' );
    add_filter( 'optin_monster_themes', 'optin_monster_sidebar_filter_optin_themes', 10, 2 );
    add_filter( 'optin_monster_theme_api', 'optin_monster_sidebar_theme_api', 10, 4 );
    add_filter( 'optin_monster_panel_configuration_fields', 'optin_monster_sidebar_config_fields', 10, 2 );
    add_filter( 'optin_monster_panel_output_fields', 'optin_monster_sidebar_output_fields', 10, 2 );
    add_filter( 'optin_monster_data', 'optin_monster_sidebar_data', 10, 3 );

}

/**
 * Initializes the addon updater.
 *
 * @since 2.0.0
 *
 * @param string $key The user license key.
 */
function optin_monster_sidebar_updater( $key ) {

    $args = array(
        'plugin_name' => OPTIN_MONSTER_SIDEBAR_PLUGIN_NAME,
        'plugin_slug' => OPTIN_MONSTER_SIDEBAR_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . OPTIN_MONSTER_SIDEBAR_PLUGIN_SLUG,
        'remote_url'  => 'http://optinmonster.com/',
        'version'     => OPTIN_MONSTER_SIDEBAR_PLUGIN_VERSION,
        'key'         => $key
    );
    $optin_monster_sidebar_updater = new Optin_Monster_Updater( $args );

}

/**
 * Filters the optin types
 *
 * Use filter 'optin_monster_theme_types' in constructor and only
 * add new keys to the $types array.
 *
 * @since 2.0.0
 *
 * @param array $types
 *
 * @return array
 */
function optin_monster_sidebar_filter_optin_type( $types ) {

    $types['sidebar'] = __( 'Sidebar', 'optin-monster-sidebar' );

    return $types;

}

/**
 * Filters the optin themes
 *
 * Use filter 'optin_monster_themes', 10, 2 in constructor
 *
 * @since 2.0.0
 *
 * @param array  $themes
 * @param string $type
 *
 * @return array
 */
function optin_monster_sidebar_filter_optin_themes( $themes, $type ) {

    if ( 'sidebar' != $type ) {
        return $themes;
    }

    $themes = array(
        'action' => array(
            'name'  => __( 'Action Theme', 'optin-monster-sidebar' ),
            'image' => plugins_url( 'includes/themes/action/images/icon.jpg', __FILE__ ),
            'file'  => __FILE__
        ),
        'fabric' => array(
            'name'  => __( 'Fabric Theme', 'optin-monster-sidebar' ),
            'image' => plugins_url( 'includes/themes/fabric/images/icon.jpg', __FILE__ ),
            'file'  => __FILE__
        ),
        'postal' => array(
            'name'  => __( 'Postal Theme', 'optin-monster-sidebar' ),
            'image' => plugins_url( 'includes/themes/postal/images/icon.jpg', __FILE__ ),
            'file'  => __FILE__
        ),
        'valley' => array(
            'name'  => __( 'Valley Theme', 'optin-monster-sidebar' ),
            'image' => plugins_url( 'includes/themes/valley/images/icon.jpg', __FILE__ ),
            'file'  => __FILE__
        ),
    );

    return apply_filters( 'optin_monster_sidebar_themes', $themes );

}

/**
 * Filters the theme API object for sidebar optin themes.
 *
 * @since 2.0.0
 *
 * @param array  $themes
 * @param string $type
 *
 * @return array
 */
function optin_monster_sidebar_theme_api( $api, $theme, $optin_id, $type ) {

    // Return early if this isn't a sidebar optin.
    if ( 'sidebar' != $type ) {
        return $api;
    }

    switch ( $theme ) {
        case 'action' :
            if ( ! class_exists( 'Optin_Monster_Sidebar_Theme_Action' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/action/action.php';
            }
            $api = new Optin_Monster_Sidebar_Theme_Action( $optin_id );
            break;
        case 'fabric' :
            if ( ! class_exists( 'Optin_Monster_Sidebar_Theme_Fabric' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/fabric/fabric.php';
            }
            $api = new Optin_Monster_Sidebar_Theme_Fabric( $optin_id );
            break;
        case 'postal' :
            if ( ! class_exists( 'Optin_Monster_Sidebar_Theme_Postal' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/postal/postal.php';
            }
            $api = new Optin_Monster_Sidebar_Theme_Postal( $optin_id );
            break;
        case 'valley' :
            if ( ! class_exists( 'Optin_Monster_Sidebar_Theme_Valley' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/valley/valley.php';
            }
            $api = new Optin_Monster_Sidebar_Theme_Valley( $optin_id );
            break;
    }

    return $api;

}

/**
 * Filters the Configuration panel fields
 *
 * @since 2.0.0
 *
 * @param array  $config The existing configuration fields
 * @param object $object The edit view instance
 *
 * @return array
 */
function optin_monster_sidebar_config_fields( $config, $object ) {

    // Return early if this isn't a sidebar optin.
    if ( 'sidebar' !== $object->meta['type'] ) {
        return $config;
    }

    // Unset the fields we don't need for this optin type.
    unset( $config['delay'] );
    unset( $config['cookie'] );
    unset( $config['second'] );
    unset( $config['logged_in'] );

    return $config;

}

/**
 * Filters the Output panel fields
 *
 * @since 2.0.0
 *
 * @param array  $html   The existing output panel fields
 * @param object $object The edit view instance
 *
 * @return array
 */
function optin_monster_sidebar_output_fields( $html, $object ) {

    // Return early if this isn't a sidebar optin.
    if ( 'sidebar' !== $object->meta['type'] ) {
        return $html;
    }

    // Unset the fields we don't need for this optin type.
    unset( $html['show'] );
    unset( $html['categories'] );
    unset( $html['global'] );
    unset( $html['never'] );
    unset( $html['exclusive'] );

    // Return the amended fields.
    return $html;

}

/**
 * Forces specific settings for sidebar optins.
 *
 * @since 2.0.0
 *
 * @param array $data   Array of optin data to be passed to the JS API.
 * @param int $optin_id The current optin ID.
 * @param array $meta   The current optin meta.
 * @return array $data  Amended array of optin data.
 */
function optin_monster_sidebar_data( $data, $optin_id, $meta ) {

	if ( 'sidebar' !== $meta['type'] ) {
        return $data;
    }
    
    $data['delay']         = 0;
    $data['cookie']        = 0;
    $data['exit']          = false;
    $data['second']        = false;
    $data['global_cookie'] = false;
    $data['mobile']        = false;
    
    return $data;
    
}