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
 * Plugin Name:  OptinMonster Canvas
 * Plugin URI:   http://optinmonster.com/
 * Description:  Adds a new optin type - Canvas - to the available optins.
 * Version:      1.0.1
 * Author:       Thomas Griffin
 * Author URI:   http://thomasgriffinmedia.com/
 * Text Domain:  optin-monster-canvas
 * Contributors: griffinjt
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:  /lang
 */

add_action( 'init', 'om_canvas_automatic_upgrades', 20 );
function om_canvas_automatic_upgrades() {

    global $optin_monster_license;

    // Load the plugin updater.
    if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) :
        if ( ! empty( $optin_monster_license['key'] ) ) {
			$args = array(
				'remote_url' 	=> 'http://optinmonster.com/',
				'version' 		=> '1.0.1',
				'plugin_name'	=> 'OptinMonster Canvas',
				'plugin_slug' 	=> 'optin-monster-canvas',
				'plugin_path' 	=> plugin_basename( __FILE__ ),
				'plugin_url' 	=> WP_PLUGIN_URL . '/optin-monster-canvas',
				'time' 			=> 43200,
				'key' 			=> $optin_monster_license['key']
			);

			// Load the updater class.
			$optin_monster_canvas_updater = new optin_monster_updater( $args );
		}
    endif;

    // Check for Canvas conversions.
    if ( ! empty( $_GET['omcanvas'] ) ) {
        om_canvas_do_conversion();
    }

}

function om_canvas_do_conversion() {

    $optins = get_posts( array( 'post_type' => 'optin', 'posts_per_page' => 1, 'post_status' => 'publish', 'name' => stripslashes( $_GET['omcanvas'] ) ) );
    if ( ! empty( $optins ) ) {
        $optin = $optins[0];
        $conversions = get_post_meta( $optin->ID, 'om_conversions', true );
        update_post_meta( $optin->ID, 'om_conversions', (int) $conversions + 1 );
    }

}

add_action( 'optin_monster_optin_types', 'om_canvas_optin_type' );
function om_canvas_optin_type() {

    echo '<div class="optin-item one-fourth first" data-optin-type="canvas">';
		echo '<h4>Canvas</h4>';
		echo '<img src="' . plugins_url( 'images/canvasicon.png', __FILE__ ) . '" />';
	echo '</div>';

}

add_action( 'optin_monster_design_canvas', 'om_canvas_design_output' );
function om_canvas_design_output() {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    echo '<div class="optin-select-wrap clearfix">';
		echo '<div class="optin-item one-fourth first ' . ( isset( $tab->meta['theme'] ) && 'whiteboard-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Whiteboard Theme">';
			echo '<h4>Whiteboard Theme</h4>';
			echo '<img src="' . plugins_url( 'images/canvasicon.png', __FILE__ ) . '" />';
			echo '<form id="whiteboard-theme" data-optin-theme="whiteboard-theme">';
			    echo om_canvas_get_whiteboard_theme( 'whiteboard-theme' );
            echo '</form>';
		echo '</div>';
	echo '</div>';

}

add_filter( 'optin_monster_template_canvas', 'om_canvas_template_optin_canvas', 10, 7 );
function om_canvas_template_optin_canvas( $html, $theme, $base_class, $hash, $optin, $env, $ssl ) {

    // Load template based on theme.
    switch ( $theme ) {
        case 'whiteboard-theme' :
            $template = 'canvas-' . $theme;
            require_once plugin_dir_path( __FILE__ ) . $template . '.php';
            $class = 'optin_monster_build_' . str_replace( '-', '_', $template );
    		$build = new $class( 'canvas', $theme, $hash, $optin, $env, $ssl, $base_class );
    		$html  = $build->build();
        break;
    }

    // Return the HTML of the optin type and theme.
    return $html;

}

add_action( 'optin_monster_save_canvas', 'om_canvas_save_optin_canvas', 10, 4 );
function om_canvas_save_optin_canvas( $type, $theme, $optin, $data ) {

    require_once plugin_dir_path( __FILE__ ) . 'save-' . $type . '-' . $theme . '.php';
	$class = 'optin_monster_save_' . $type . '_' . str_replace( '-', '_', $theme );
	$save  = new $class( $type, $theme, $optin, $data );
	$save->save_optin();

}

function om_canvas_get_whiteboard_theme( $theme_type ) {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    ob_start();
    	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
        	echo '<div class="design-sidebar">';
        		echo '<div class="controls-area om-clearfix">';
        			echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
        			echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
        		echo '</div>';
        		echo '<div class="title-area om-clearfix">';
        			echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
        			echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
        		echo '</div>';
        		echo '<div class="accordion-area om-clearfix">';
        			echo '<h3>'.__('Dimensions','optin-monster').'</h3>';
        			echo '<div class="colors-area">';
        				echo '<p>';
        					echo '<label for="om-canvas-' . $theme_type . '-optin-width">'.__('Lightbox Width (px)','optin-monster').'</label>';
        					echo '<input type="text" id="om-canvas-' . $theme_type . '-optin-width" class="optin_dimensions" name="optin_canvas_width" value="' . $tab->get_field( 'dimensions', 'width', '700' ) . '" data-target="om-canvas-' . $theme_type . '-optin" data-attr="width" />';
        				echo '</p>';
        				echo '<p>';
        					echo '<label for="om-canvas-' . $theme_type . '-optin-height">'.__('Lightbox Height (px)','optin-monster').'</label>';
        					echo '<input type="text" id="om-canvas-' . $theme_type . '-optin-height" name="optin_canvas_height" class="optin_dimensions" data-attr="height" value="' . $tab->get_field( 'dimensions', 'height', '350' ) . '" data-target="om-canvas-' . $theme_type . '-optin" />';
        				echo '</p>';
        			echo '</div>';

        			echo '<h3>'.__('HTML','optin-monster').'</h3>';
        			echo '<div class="content-area">';
        			    echo '<div class="custom-css-area">';
            				echo '<p><small>' . __( 'The textarea below is for adding your own custom HTML markup into the lightbox canvas provided.', 'optin-monster' ) . '</small></p>';
            				echo '<textarea id="om-canvas-' . $theme_type . '-custom-html" name="optin_custom_canvas_html" class="om-custom-css">' . $tab->get_field( 'custom_canvas_html' ) . '</textarea>';
            				echo '<p><small>' . __( 'Unique Optin Slug: ', 'optin-monster' ) . ' <strong><code>' . $tab->optin->post_name . '</code></strong></small></p>';
            				echo '<p><small><a href="http://optinmonster.com/docs/how-to-track-conversions-with-the-canvas-addon/" title="Tracking Conversions with Canvas" target="_blank"><em>' . __( 'This is needed for tracking conversions with the Canvas addon.', 'optin-monster' ) . '</em></a></small></p>';
            			echo '</div>';
        			echo '</div>';

        			echo '<h3>'.__('CSS','optin-monster').'</h3>';
        			echo '<div class="content-area">';
            			echo '<div class="custom-css-area">';
            				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
            				echo '<p><strong><code>html div#om-' . $tab->optin->post_name . '</code></strong></p>';
            				echo '<textarea id="om-canvas-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css', '', '' ) . '</textarea>';
            				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
            			echo '</div>';
        			echo '</div>';
        		echo '</div>';
        	echo '</div>';
        	echo '<div class="design-content">';
        	echo '</div>';
        echo '</div>';

        return ob_get_clean();

}