<?php
/**
 * Plugin Name: OptinMonster - Footer Bar Addon
 * Plugin URI:  http://optinmonster.com
 * Description: Adds a new optin type - Footer Bar - to the available optins.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     2.0.0
 * Text Domain: optin-monster-footer
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
define( 'OPTIN_MONSTER_FOOTER_PLUGIN_NAME', 'OptinMonster - Footer Bar Addon' );
define( 'OPTIN_MONSTER_FOOTER_PLUGIN_VERSION', '2.0.0' );
define( 'OPTIN_MONSTER_FOOTER_PLUGIN_SLUG', 'optin-monster-footer' );

add_action( 'plugins_loaded', 'optin_monster_footer_plugins_loaded' );
/**
 * Ensures the full OptinMonster plugin is active before proceeding.
 *
 * @since 2.0.0
 *
 * @return null Return early if OptinMonster is not active.
 */
function optin_monster_footer_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Optin_Monster' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'optin_monster_init', 'optin_monster_footer_plugin_init' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 2.0.0
 */
function optin_monster_footer_plugin_init() {

    add_action( 'optin_monster_updater', 'optin_monster_footer_updater' );
    add_filter( 'optin_monster_theme_types', 'optin_monster_footer_filter_optin_type' );
    add_filter( 'optin_monster_themes', 'optin_monster_footer_filter_optin_themes', 10, 2 );
    add_filter( 'optin_monster_theme_api', 'optin_monster_footer_theme_api', 10, 4 );
    add_filter( 'optin_monster_positioning', 'optin_monster_footer_positioning', 10, 5 );

}

/**
 * Initializes the addon updater.
 *
 * @since 2.0.0
 *
 * @param string $key The user license key.
 */
function optin_monster_footer_updater( $key ) {

    $args = array(
        'plugin_name' => OPTIN_MONSTER_FOOTER_PLUGIN_NAME,
        'plugin_slug' => OPTIN_MONSTER_FOOTER_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . OPTIN_MONSTER_FOOTER_PLUGIN_SLUG,
        'remote_url'  => 'http://optinmonster.com/',
        'version'     => OPTIN_MONSTER_FOOTER_PLUGIN_VERSION,
        'key'         => $key
    );
    $optin_monster_slide_updater = new Optin_Monster_Updater( $args );

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
function optin_monster_footer_filter_optin_type( $types ) {

    $types['footer'] = __( 'Footer Bar', 'optin-monster-footer' );
    return $types;

}

/**
 * Filters the optin themes
 *
 * Use filter 'optin_monster_themes', 10, 2 in constructor
 *
 * @param array  $themes Themes of the currently selected type
 * @param string $type   The selected type
 *
 * @since 2.0.0
 *
 * @return array The footer themes
 */
function optin_monster_footer_filter_optin_themes( $themes, $type ) {

    if ( 'footer' !== $type ) {
        return $themes;
    }

    $themes = array(
        'sleek'  => array(
            'name'  => __( 'Sleek Theme', 'optin-monster-footer' ),
            'image' => plugins_url( 'includes/themes/sleek/images/icon.jpg', __FILE__ ),
            'file'  => __FILE__
        ),
        'postal' => array(
            'name'  => __( 'Postal Theme', 'optin-monster-footer' ),
            'image' => plugins_url( 'includes/themes/postal/images/icon.jpg', __FILE__ ),
            'file'  => __FILE__
        ),
        'tiles'  => array(
            'name'  => __( 'Tiles Theme', 'optin-monster-footer' ),
            'image' => plugins_url( 'includes/themes/tiles/images/icon.jpg', __FILE__ ),
            'file'  => __FILE__
        ),
    );

    return apply_filters( 'optin_monster_footer_themes', $themes );

}

/**
 * Filters the current theme object
 *
 * @since 2.0.0
 *
 * @param object $api      The theme object to filter
 * @param string $theme    The currently selected theme slug
 * @param int    $optin_id The current optin ID
 * @param string $type     The current optin type
 *
 * @return mixed The correct theme object
 */
function optin_monster_footer_theme_api( $api, $theme, $optin_id, $type ) {

    // Return early if this isn't a footer optin.
    if ( 'footer' != $type ) {
        return $api;
    }

    switch ( $theme ) {
        case 'sleek' :
            if ( ! class_exists( 'Optin_Monster_Footer_Theme_Sleek' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/sleek/sleek.php';
            }
            $api = new Optin_Monster_Footer_Theme_Sleek( $optin_id );
            break;
        case 'postal' :
            if ( ! class_exists( 'Optin_Monster_Footer_Theme_Postal' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/postal/postal.php';
            }
            $api = new Optin_Monster_Footer_Theme_Postal( $optin_id );
            break;
        case 'tiles' :
            if ( ! class_exists( 'Optin_Monster_Footer_Theme_Tiles' ) ) {
                require plugin_dir_path( __FILE__ ) . 'includes/themes/tiles/tiles.php';
            }
            $api = new Optin_Monster_Footer_Theme_Tiles( $optin_id );
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
function optin_monster_footer_positioning( $script, $optin_name, $type, $theme, $preview ) {

    if ( 'footer' != $type ) {
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