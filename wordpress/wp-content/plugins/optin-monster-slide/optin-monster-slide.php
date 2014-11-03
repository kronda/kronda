<?php
/**
 * Plugin Name: OptinMonster - Slide-In Addon
 * Plugin URI:  http://optinmonster.com
 * Description: Adds a new optin type - Slide-In - to the available optins.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     2.0.0
 * Text Domain: optin-monster-slide
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
define( 'OPTIN_MONSTER_SLIDE_PLUGIN_NAME', 'OptinMonster - Slide-In Addon' );
define( 'OPTIN_MONSTER_SLIDE_PLUGIN_VERSION', '2.0.0' );
define( 'OPTIN_MONSTER_SLIDE_PLUGIN_SLUG', 'optin-monster-slide' );

add_action( 'plugins_loaded', 'optin_monster_slide_plugins_loaded' );
/**
 * Ensures the full OptinMonster plugin is active before proceeding.
 *
 * @since 2.0.0
 *
 * @return null Return early if OptinMonster is not active.
 */
function optin_monster_slide_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Optin_Monster' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'optin_monster_init', 'optin_monster_slide_plugin_init' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 2.0.0
 */
function optin_monster_slide_plugin_init() {

    add_action( 'optin_monster_updater', 'optin_monster_slide_updater' );
    add_filter( 'optin_monster_theme_types', 'optin_monster_slide_filter_optin_type' );
    add_filter( 'optin_monster_themes', 'optin_monster_slide_filter_optin_themes', 10, 2 );
    add_filter( 'optin_monster_theme_api', 'optin_monster_slide_theme_api', 10, 3 );
    add_filter( 'optin_monster_positioning', 'optin_monster_slide_positioning', 10, 5 );

}

/**
 * Initializes the addon updater.
 *
 * @since 2.0.0
 *
 * @param string $key The user license key.
 */
function optin_monster_slide_updater( $key ) {

    $args                             = array(
        'plugin_name' => OPTIN_MONSTER_SLIDE_PLUGIN_NAME,
        'plugin_slug' => OPTIN_MONSTER_SLIDE_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . OPTIN_MONSTER_SLIDE_PLUGIN_SLUG,
        'remote_url'  => 'http://optinmonster.com/',
        'version'     => OPTIN_MONSTER_SLIDE_PLUGIN_VERSION,
        'key'         => $key
    );
    $optin_monster_slide_updater = new Optin_Monster_Updater( $args );

}
/**
 * Filters the optin types.
 *
 * @since 2.0.0
 *
 * @param array $types  Array of optin types to choose from.
 * @return array $types Amended array of optin types.
 */
function optin_monster_slide_filter_optin_type( $types ) {

    $types['slide'] = __( 'Slide-In', 'optin-monster-slide' );
    return $types;

}

/**
 * Filters the optin themes.
 *
 * @since 2.0.0
 *
 * @param array  $themes The available themes for the Slide-In optin type.
 * @param string $type The type of theme to filter.
 * @return array $themes Amended array of available themes for the optin type.
 */
function optin_monster_slide_filter_optin_themes( $themes, $type ) {

    // Return early if not the proper type.
    if ( 'slide' !== $type ) {
        return $themes;
    }

    $themes = array(
        'converse' => array(
            'name'  => __( 'Converse Theme', 'optin-monster-slide' ),
            'image' => plugins_url( 'includes/themes/converse/images/icon.jpg', __FILE__ ),
            'file'  => plugin_dir_path( 'includes/themes/converse/converse.php', __FILE__ )
        ),
    );

    return apply_filters( 'optin_monster_slide_themes', $themes );

}

/**
 * Filters the current theme object.
 *
 * @param $api      The current theme object
 * @param $theme    The current theme name
 * @param $optin_id The current optin id
 *
 * @since 2.0.0
 *
 * @return mixed The correct theme object.
 */
function optin_monster_slide_theme_api( $api, $theme, $optin_id ) {

    switch ( $theme ) {
        case 'converse' :
            if ( ! class_exists( 'Optin_Monster_Slide_Theme_Converse' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/converse/converse.php';
            }
            $api = new Optin_Monster_Slide_Theme_Converse( $optin_id );
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
function optin_monster_slide_positioning( $script, $optin_name, $type, $theme, $preview ) {

    if ( 'slide' != $type ) {
        return $script;
    }

    ob_start();
    ?>
    $('#om-<?php echo $optin_name; ?>').css({
        <?php if ( $preview ) : ?>
        bottom: 78
        <?php else : ?>
        bottom: 0
        <?php endif; ?>
    });
    <?php
    return ob_get_clean();

}
