<?php
/**
 * Plugin Name: OptinMonster - Canvas Addon
 * Plugin URI:  http://optinmonster.com
 * Description: Adds a new optin type - Footer Bar - to the available optins.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     2.0.1
 * Text Domain: optin-monster-canvas
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
define( 'OPTIN_MONSTER_CANVAS_PLUGIN_NAME', 'OptinMonster - Canvas Addon' );
define( 'OPTIN_MONSTER_CANVAS_PLUGIN_VERSION', '2.0.1' );
define( 'OPTIN_MONSTER_CANVAS_PLUGIN_SLUG', 'optin-monster-canvas' );

add_action( 'plugins_loaded', 'optin_monster_canvas_plugins_loaded' );
/**
 * Ensures the full OptinMonster plugin is active before proceeding.
 *
 * @since 2.0.0
 *
 * @return null Return early if OptinMonster is not active.
 */
function optin_monster_canvas_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Optin_Monster' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'optin_monster_init', 'optin_monster_canvas_plugin_init' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 2.0.0
 */
function optin_monster_canvas_plugin_init() {

	// Possibly count conversions with Canvas.
	add_action( 'init', 'optin_monster_canvas_do_conversions' );
    add_action( 'optin_monster_updater', 'optin_monster_canvas_updater' );
    add_filter( 'optin_monster_theme_types', 'optin_monster_canvas_filter_optin_type' );
    add_filter( 'optin_monster_themes', 'optin_monster_canvas_filter_optin_themes', 10, 2 );
    add_filter( 'optin_monster_theme_api', 'optin_monster_canvas_theme_api', 10, 4 );
    add_filter( 'optin_monster_positioning', 'optin_monster_canvas_positioning', 10, 5 );

}

/**
 * Handles conversion tracking for the Canvas addon.
 *
 * @since 2.0.1
 */
function optin_monster_canvas_do_conversions() {
	
	// If the conversion parameter is empty, return early.
	if ( empty( $_GET['omcanvas'] ) ) {
		return;
	}
	
	// Grab the optin.
	$optin = Optin_Monster::get_instance()->get_optin_by_slug( stripslashes( $_GET['omcanvas'] ) );
	if ( ! $optin ) {
		return;
	}
	
	// Process the conversion.
	if ( ! class_exists( 'Optin_Monster_Track_Datastore' ) ) {
		require plugin_dir_path( Optin_Monster::get_instance()->file ) . 'includes/global/track-datastore.php';
	}
	$track = new Optin_Monster_Track_Datastore( $optin->ID );
	$track->save( 'conversion' );
	
	// Add a hook for custom functionality.
	do_action( 'optin_monster_canvas_conversion', $optin );
	
}

/**
 * Initializes the addon updater.
 *
 * @since 2.0.0
 *
 * @param string $key The user license key.
 */
function optin_monster_canvas_updater( $key ) {

    $args = array(
        'plugin_name' => OPTIN_MONSTER_CANVAS_PLUGIN_NAME,
        'plugin_slug' => OPTIN_MONSTER_CANVAS_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . OPTIN_MONSTER_CANVAS_PLUGIN_SLUG,
        'remote_url'  => 'http://optinmonster.com/',
        'version'     => OPTIN_MONSTER_CANVAS_PLUGIN_VERSION,
        'key'         => $key
    );
    $optin_monster_effects_updater = new Optin_Monster_Updater( $args );

}

/**
 * Filters the optin types.
 *
 * @since 2.0.0
 *
 * @param array $types  Array of optin types.
 * @return array $types Amended array of optin types.
 */
function optin_monster_canvas_filter_optin_type( $types ) {

    $types['canvas'] = __( 'Canvas', 'optin-monster-canvas' );
    return $types;

}

/**
 * Filters the optin themes
 *
 * @since 2.0.0
 *
 * @param array  $themes
 * @param string $type
 *
 * @return array
 */
function optin_monster_canvas_filter_optin_themes( $themes, $type ) {

    if ( 'canvas' != $type ) {
        return $themes;
    }
    
    $themes = array(
        'whiteboard' => array(
            'name'   => __( 'Whiteboard Theme', 'optin-monster-canvas' ),
            'image'  => plugins_url( 'includes/themes/whiteboard/images/icon.jpg', __FILE__ ),
            'file'   => plugin_dir_path( 'includes/themes/whiteboard/whiteboard.php', __FILE__ )
        ),
    );

    return apply_filters( 'optin_monster_canvas_themes', $themes );

}

/**
 * Filters the current theme object.
 *
 * @since 2.0.0
 *
 * @param object $api      The theme object to filter.
 * @param string $theme    The currently selected theme slug.
 * @param int    $optin_id The current optin ID.
 * @param string $type     The current optin type.
 *
 * @return mixed           The correct theme object.
 */
function optin_monster_canvas_theme_api( $api, $theme, $optin_id, $type ) {

    // Return early if this isn't a footer optin.
    if ( 'canvas' != $type ) {
        return $api;
    }

    switch ( $theme ) {
        case 'whiteboard' :
            if ( ! class_exists( 'Optin_Monster_Canvas_Theme_Whiteboard' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/whiteboard/whiteboard.php';
            }
            $api = new Optin_Monster_Canvas_Theme_Whiteboard( $optin_id );
            break;
    }

    return $api;

}

/**
 * Filters the script used to position the optin on the front-end and in preview.
 *
 * @param string $script     The existing javascript
 * @param string $optin_name The optin hash
 * @param string $type       The optin type
 * @param string $theme      The current optin theme
 * @param bool   $preview    Are we in the optin preview?
 *
 * @since 2.0.0
 *
 * @return string The new positioning javascript
 */
function optin_monster_canvas_positioning( $script, $optin_name, $type, $theme, $preview ) {

    if ( 'canvas' != $type ) {
        return $script;
    }

    ob_start();
    ?>
    $('.om-theme-<?php echo $theme; ?>, .optin-monster-success-overlay').css({
    <?php if ( $preview ) : ?>
        top: (($(window).height() - $('.om-theme-<?php echo $theme; ?>').height()) / 2) - 39,
    <?php else : ?>
        top: ($(window).height() - $('.om-theme-<?php echo $theme; ?>').height()) / 2,
    <?php endif; ?>
        left: ($(window).width() - $('.om-theme-<?php echo $theme; ?>').width()) / 2
    });
    $(window).resize(function(){
        $('.om-theme-<?php echo $theme; ?>, .optin-monster-success-overlay').css({
        <?php if ( $preview ) : ?>
            top: (($(window).height() - $('.om-theme-<?php echo $theme; ?>').height()) / 2) - 39,
        <?php else : ?>
            top: ($(window).height() - $('.om-theme-<?php echo $theme; ?>').height()) / 2,
        <?php endif; ?>
        left: ($(window).width() - $('.om-theme-<?php echo $theme; ?>').width()) / 2
        });
    });
    <?php
    return ob_get_clean();

}
