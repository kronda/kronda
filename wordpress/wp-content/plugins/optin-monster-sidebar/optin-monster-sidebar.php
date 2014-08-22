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
 * Plugin Name:  OptinMonster Sidebar
 * Plugin URI:   http://optinmonster.com/
 * Description:  Adds a new optin type - Sidebar - to the available optins.
 * Version:      1.0.2
 * Author:       Thomas Griffin
 * Author URI:   http://thomasgriffinmedia.com/
 * Text Domain:  optin-monster-sidebar
 * Contributors: griffinjt
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:  /lang
 */

add_action( 'init', 'om_sidebar_automatic_upgrades', 20 );
function om_sidebar_automatic_upgrades() {

    global $optin_monster_license;

    // Load the plugin updater.
    if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) :
        if ( ! empty( $optin_monster_license['key'] ) ) {
			$args = array(
				'remote_url' 	=> 'http://optinmonster.com/',
				'version' 		=> '1.0.2',
				'plugin_name'	=> 'OptinMonster Sidebar',
				'plugin_slug' 	=> 'optin-monster-sidebar',
				'plugin_path' 	=> plugin_basename( __FILE__ ),
				'plugin_url' 	=> WP_PLUGIN_URL . '/optin-monster-sidebar',
				'time' 			=> 43200,
				'key' 			=> $optin_monster_license['key']
			);

			// Load the updater class.
			$optin_monster_sidebar_updater = new optin_monster_updater( $args );
		}
    endif;

}

add_action( 'optin_monster_optin_types', 'om_sidebar_optin_type' );
function om_sidebar_optin_type() {

    echo '<div class="optin-item one-fourth first" data-optin-type="sidebar">';
		echo '<h4>Sidebar</h4>';
		echo '<img src="' . plugins_url( 'images/sidebaricon.png', __FILE__ ) . '" />';
	echo '</div>';

}

add_action( 'optin_monster_config', 'om_sidebar_hide_options', 0 );
function om_sidebar_hide_options() {

    // Return early if not a sidebar optin.
    if ( empty( $_GET['type'] ) || isset( $_GET['type'] ) && 'sidebar' !== $_GET['type'] )
        return;

    echo '<style type="text/css">.optin-config-box:nth-of-type(2),.optin-config-box:nth-of-type(3),.optin-config-box:nth-of-type(5){display:none}</style>';

}

add_action( 'optin_monster_code', 'om_sidebar_hide_output_options', 0 );
function om_sidebar_hide_output_options() {

    // Return early if not a sidebar optin.
    if ( empty( $_GET['type'] ) || isset( $_GET['type'] ) && 'sidebar' !== $_GET['type'] )
        return;

    echo '<style type="text/css">.optin-config-box:nth-of-type(2),.optin-config-box:nth-of-type(3),.optin-config-box:nth-of-type(4),.optin-config-box:nth-of-type(5){display:none}</style>';

}

add_action( 'optin_monster_code_top', 'om_sidebar_code_message', 0 );
function om_sidebar_code_message() {

    // Return early if not a sidebar optin.
    if ( empty( $_GET['type'] ) || isset( $_GET['type'] ) && 'sidebar' !== $_GET['type'] )
        return;

    echo '<p><strong>Since this optin type is used in widgets, there are no output settings available other than enabling/disabling the optin. You can manage the display of this optin via a widget area in your theme.</strong></p>';

}

add_action( 'optin_monster_design_sidebar', 'om_sidebar_design_output' );
function om_sidebar_design_output() {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    echo '<div class="optin-select-wrap clearfix">';
		echo '<div class="optin-item one-fourth first ' . ( isset( $tab->meta['theme'] ) && 'action-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Action Theme">';
			echo '<h4>Action Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/action-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="action-theme" data-optin-theme="action-theme">';
			    echo om_sidebar_get_action_theme( 'action-theme' );
            echo '</form>';
		echo '</div>';
		echo '<div class="optin-item one-fourth ' . ( isset( $tab->meta['theme'] ) && 'fabric-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Fabric Theme">';
			echo '<h4>Fabric Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/fabric-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="fabric-theme" data-optin-theme="fabric-theme">';
				echo om_sidebar_get_fabric_theme( 'fabric-theme' );
			echo '</form>';
		echo '</div>';
		echo '<div class="optin-item one-fourth ' . ( isset( $tab->meta['theme'] ) && 'postal-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="postal Theme">';
			echo '<h4>Postal Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/postal-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="postal-theme" data-optin-theme="postal-theme">';
			echo om_sidebar_get_postal_theme( 'postal-theme' );
			echo '</form>';
		echo '</div>';
		echo '<div class="optin-item one-fourth last ' . ( isset( $tab->meta['theme'] ) && 'valley-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="valley Theme">';
			echo '<h4>Valley Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/valley-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="valley-theme" data-optin-theme="valley-theme">';
				echo om_sidebar_get_valley_theme( 'valley-theme' );
			echo '</form>';
		echo '</div>';
	echo '</div>';

}

add_filter( 'optin_monster_template_sidebar', 'om_sidebar_template_optin_sidebar', 10, 7 );
function om_sidebar_template_optin_sidebar( $html, $theme, $base_class, $hash, $optin, $env, $ssl ) {

    // Load template based on theme.
    switch ( $theme ) {
        case 'action-theme' :
			$html = om_sidebar_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
			break;
	    case 'fabric-theme' :
		    $html = om_sidebar_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
		    break;
	    case 'postal-theme' :
		    $html = om_sidebar_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
		    break;
	    case 'valley-theme' :
		    $html = om_sidebar_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
		    break;
    }

    // Return the HTML of the optin type and theme.
    return $html;

}

function om_sidebar_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class ) {

	$template = 'sidebar-' . $theme;
	require_once plugin_dir_path( __FILE__ ) . 'themes/' . $theme . '/template.php';
	$class = 'optin_monster_build_' . str_replace( '-', '_', $template );
	$build = new $class( 'sidebar', $theme, $hash, $optin, $env, $ssl, $base_class );
	return $build->build();

}

add_action( 'optin_monster_save_sidebar', 'om_sidebar_save_optin_sidebar', 10, 4 );
function om_sidebar_save_optin_sidebar( $type, $theme, $optin, $data ) {

    require_once plugin_dir_path( __FILE__ ) . 'themes/' . $theme . '/save.php';
	$class = 'optin_monster_save_' . $type . '_' . str_replace( '-', '_', $theme );
	$save  = new $class( $type, $theme, $optin, $data );
	$save->save_optin();

}

function om_sidebar_get_action_theme( $theme_type ) {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    ob_start();
    echo '<div class="design-customizer-ui" data-optin-theme="action-theme">';
		echo '<div class="design-sidebar">';
			echo '<div class="controls-area clearfix">';
    			echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="Close Customizer">Close</a>';
    			echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="Save Changes">Save</a>';
    		echo '</div>';
    		echo '<div class="title-area clearfix">';
    			echo '<p class="no-margin">You are now previewing:</p>';
    			echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
    		echo '</div>';
			echo '<div class="accordion-area clearfix">';
			    echo '<h3>Background Colors</h3>';
    			echo '<div class="colors-area">';
    				echo '<p>';
    					echo '<label for="om-sidebar-' . $theme_type . '-content-bg">Content Background Color</label>';
    					echo '<input type="text" id="om-sidebar-' . $theme_type . '-content-bg" class="om-bgcolor-picker" name="optin_content_bg" value="' . $tab->get_field( 'background', 'content' ) . '" data-default-color="#fff" data-target="om-sidebar-' . $theme_type . '-optin" />';
    				echo '</p>';
    			echo '</div>';

				echo '<h3>Title and Tagline</h3>';
				echo '<div class="title-tag-area">';
					echo '<p>';
						echo '<label for="om-sidebar-' . $theme_type . '-headline">Optin Title</label>';
						echo '<input id="om-sidebar-' . $theme_type . '-headline" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'title' );
							foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-headline-color">Optin Title Color</label>';
							echo '<input type="text" id="om-sidebar-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color' ) . '" data-default-color="#ffffff" data-target="om-sidebar-' . $theme_type . '-optin-title" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-headline-font">Optin Title Font</label>';
							echo '<select id="om-sidebar-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
							echo '<input id="om-sidebar-' . $theme_type . '-headline-size" data-target="om-sidebar-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
    					echo '<label for="om-sidebar-' . $theme_type . '-tagline">Optin Tagline</label>';
    					echo '<textarea id="om-sidebar-' . $theme_type . '-tagline" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="e.g. OptinMonster explodes your email list!" rows="4">' . htmlentities( $tab->get_field( 'tagline', 'text' ) ) . '</textarea>';
    					echo '<span class="input-controls">';
    						echo $tab->get_meta_controls( 'tagline' );
    						foreach ( (array) $tab->get_field( 'tagline', 'meta' ) as $prop => $style )
    							echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
    					echo '</span>';
    				echo '</p>';
    				echo '<div class="optin-input-meta last">';
    					echo '<p>';
    						echo '<label for="om-sidebar-' . $theme_type . '-tagline-color">Optin Tagline Color</label>';
    						echo '<input type="text" id="om-sidebar-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $tab->get_field( 'tagline', 'color' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-tagline" />';
    					echo '</p>';
    					echo '<p>';
    						echo '<label for="om-sidebar-' . $theme_type . '-tagline-font">Optin Tagline Font</label>';
    						echo '<select id="om-sidebar-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
    						foreach ( $tab->account->get_available_fonts() as $font ) :
    							$selected = $tab->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
    							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
    						endforeach;
    						echo '</select>';
    					echo '</p>';
    					echo '<p>';
    						echo '<label for="om-sidebar-' . $theme_type . '-tagline-size">Optin Tagline Font Size</label>';
    						echo '<input id="om-sidebar-' . $theme_type . '-headline-size" data-target="om-sidebar-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $tab->get_field( 'tagline', 'size' ) . '" placeholder="e.g. 36" />';
    					echo '</p>';
    				echo '</div>';
				echo '</div>';

                if ( ! $tab->meta['custom_html'] ) :
				echo '<h3>Fields and Buttons</h3>';
				echo '<div class="fields-area">';
					echo '<p>';
						echo '<label for="om-sidebar-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-sidebar-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
						echo '<input id="om-sidebar-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-name-color">Optin Name Field Color</label>';
							echo '<input type="text" id="om-sidebar-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-name" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-name-font">Optin Name Field Font</label>';
							echo '<select id="om-sidebar-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-name-size">Optin Name Field Font Size</label>';
							echo '<input id="om-sidebar-' . $theme_type . '-name-size" data-target="om-sidebar-' . $theme_type . '-optin-name" name="optin_name_size" class="optin-size" type="text" value="' . $tab->get_field( 'name', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-sidebar-' . $theme_type . '-email">Optin Email Field</label>';
						echo '<input id="om-sidebar-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-email-color">Optin Email Field Color</label>';
							echo '<input type="text" id="om-sidebar-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-email" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-email-font">Optin Email Field Font</label>';
							echo '<select id="om-sidebar-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-email-size">Optin Email Field Font Size</label>';
							echo '<input id="om-sidebar-' . $theme_type . '-email-size" data-target="om-sidebar-' . $theme_type . '-optin-email" name="optin_email_size" class="optin-size" type="text" value="' . $tab->get_field( 'email', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-sidebar-' . $theme_type . '-submit">Optin Submit Field</label>';
						echo '<input id="om-sidebar-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'submit' );
							foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta last">';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
							echo '<input type="text" id="om-sidebar-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color' ) . '" data-default-color="#fff" data-target="om-sidebar-' . $theme_type . '-optin-submit" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
							echo '<input type="text" id="om-sidebar-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color' ) . '" data-default-color="#484848" data-target="om-sidebar-' . $theme_type . '-optin-submit" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
							echo '<select id="om-sidebar-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-sidebar-' . $theme_type . '-submit-size">Optin Submit Field Font Size</label>';
							echo '<input id="om-sidebar-' . $theme_type . '-submit-size" data-target="om-sidebar-' . $theme_type . '-optin-submit" name="optin_submit_size" class="optin-size" type="text" value="' . $tab->get_field( 'submit', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
				echo '</div>';
				endif;

				echo '<h3>Custom Optin CSS</h3>';
    			echo '<div class="custom-css-area">';
    				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
    				echo '<p><strong><code>html div#om-' . $tab->optin->post_name . '</code></strong></p>';
    				echo '<textarea id="om-sidebar-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
    				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
    			echo '</div>';
			echo '</div>';
		echo '</div>';
		echo '<div class="design-content">';
		echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

function om_sidebar_get_fabric_theme( $theme_type ) {

	global $optin_monster_tab_optins;
	$tab = $optin_monster_tab_optins;

	ob_start();
	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
	echo '<div class="design-sidebar">';
	echo '<div class="controls-area clearfix">';
	echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="Close Customizer">Close</a>';
	echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="Save Changes">Save</a>';
	echo '</div>';
	echo '<div class="title-area clearfix">';
	echo '<p class="no-margin">You are now previewing:</p>';
	echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
	echo '</div>';
	echo '<div class="accordion-area clearfix">';
	echo '<h3>Background Colors</h3>';
	echo '<div class="colors-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-header-bg">Header Background Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-header-bg" class="om-bgcolor-picker" name="optin_header_bg" value="' . $tab->get_field( 'background', 'header', '#f16a10' ) . '" data-default-color="#f16a10" data-target="om-sidebar-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-bg">Content Background Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-content-bg" class="om-bgcolor-picker" name="optin_content_bg" value="' . $tab->get_field( 'background', 'content', '#fbfdf3' ) . '" data-default-color="#fbfdf3" data-target="om-sidebar-' . $theme_type . '-optin" />';
	echo '</p>';
	echo '</div>';

	echo '<h3>Title</h3>';
	echo '<div class="title-tag-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline">Optin Title</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'title' );
	foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<div class="optin-input-meta">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-color">Optin Title Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color' ) . '" data-default-color="#ffffff" data-target="om-sidebar-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-font">Optin Title Font</label>';
	echo '<select id="om-sidebar-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline-size" data-target="om-sidebar-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size', '28' ) . '" placeholder="e.g. 28" />';
	echo '</p>';
	echo '</div>';
	echo '</div>';
	echo '<h3>Content</h3>';
	echo '<div class="content-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-image">Optin Image</label>';
	echo '<small>'.__('Click the button below to upload an image for this optin. It should be 88x134 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
	echo '<input type="hidden" name="optin_image" value="' . $tab->get_field( 'image' ) . '" />';
	echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
	echo '<div id="browse-button-' . $tab->optin->post_name . '"><a id="plupload-browse-button-' . $tab->optin->post_name . '" class="bullet-button" data-container="om-sidebar-' . $theme_type . '-optin-image-container" href="#">Upload Image</a><a href="#" class="bullet-button remove-optin-image" data-container="om-sidebar-' . $theme_type . '-optin-image-container">Remove Image</a></div>';
	echo '</div>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content">Optin Content</label>';
	echo '<textarea id="om-sidebar-' . $theme_type . '-content" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-content" type="text" name="optin_content" placeholder="e.g. Enter your email and we’ll send you updates and exciting news!" rows="4">' . htmlentities( $tab->get_field( 'content', 'text' ) ) . '</textarea>';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'content' );
	foreach ( (array) $tab->get_field( 'content', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_content_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-color">Optin Content Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-content-color" class="om-color-picker" name="optin_content_color" value="' . $tab->get_field( 'content', 'color', '#858585' ) . '" data-default-color="#858585" data-target="om-sidebar-' . $theme_type . '-optin-content" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-font">Optin Content Font</label>';
	echo '<select id="om-sidebar-' . $theme_type . '-content-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-content" data-property="font-family" name="optin_content_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'content', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-size">Optin Content Font Size</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline-size" data-target="om-sidebar-' . $theme_type . '-optin-content" name="optin_content_size" class="optin-size" type="text" value="' . $tab->get_field( 'content', 'size', '16' ) . '" placeholder="e.g. 16" />';
	echo '</p>';
	echo '</div>';
	if ( ! $tab->meta['custom_html'] ) :
		echo '<h3>Fields and Buttons</h3>';
		echo '<div class="fields-area">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-sidebar-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name-color">Optin Name Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-name" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name-font">Optin Name Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email">Optin Email Field</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email-color">Optin Email Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-email" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email-font">Optin Email Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit">Optin Submit Field</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'submit' );
		foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-sidebar-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color', '#f16a10' ) . '" data-default-color="#f16a10" data-target="om-sidebar-' . $theme_type . '-optin-submit" />';
		echo '<input type="hidden" name="optin_submit_border_color" value="' . $tab->get_field( 'submit', 'border_color', '#cc611b' ) . '" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '</div>';
	endif;

	echo '<h3>Custom Optin CSS</h3>';
	echo '<div class="custom-css-area">';
	echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
	echo '<p><strong><code>html div#om-' . $tab->optin->sidebar_name . '</code></strong></p>';
	echo '<textarea id="om-sidebar-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->sidebar_name . ' input[type=submit], html div#' . $tab->optin->sidebar_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
	echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="design-content">';
	echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

function om_sidebar_get_postal_theme( $theme_type ) {

	global $optin_monster_tab_optins;
	$tab = $optin_monster_tab_optins;

	ob_start();
	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
	echo '<div class="design-sidebar">';
	echo '<div class="controls-area clearfix">';
	echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="Close Customizer">Close</a>';
	echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="Save Changes">Save</a>';
	echo '</div>';
	echo '<div class="title-area clearfix">';
	echo '<p class="no-margin">You are now previewing:</p>';
	echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
	echo '</div>';
	echo '<div class="accordion-area clearfix">';
	echo '<h3>Background Color</h3>';
	echo '<div class="colors-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-header-bg">Header Background Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-header-bg" class="om-bgcolor-picker" name="optin_header_bg" value="' . $tab->get_field( 'background', 'header', '#50bbe8' ) . '" data-default-color="#50bbe8" data-target="om-sidebar-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '</div>';

	echo '<h3>Title</h3>';
	echo '<div class="title-tag-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline">Optin Title</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'title' );
	foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<div class="optin-input-meta">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-color">Optin Title Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color' ) . '" data-default-color="#ffffff" data-target="om-sidebar-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-font">Optin Title Font</label>';
	echo '<select id="om-sidebar-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline-size" data-target="om-sidebar-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size', '28' ) . '" placeholder="e.g. 28" />';
	echo '</p>';
	echo '</div>';
	echo '</div>';
	echo '<h3>Content</h3>';
	echo '<div class="content-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-image">Optin Image</label>';
	echo '<small>'.__('Click the button below to upload an image for this optin. It should be 88x134 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
	echo '<input type="hidden" name="optin_image" value="' . $tab->get_field( 'image' ) . '" />';
	echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
	echo '<div id="browse-button-' . $tab->optin->post_name . '"><a id="plupload-browse-button-' . $tab->optin->post_name . '" class="bullet-button" data-container="om-sidebar-' . $theme_type . '-optin-image-container" href="#">Upload Image</a><a href="#" class="bullet-button remove-optin-image" data-container="om-sidebar-' . $theme_type . '-optin-image-container">Remove Image</a></div>';
	echo '</div>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content">Optin Content</label>';
	echo '<textarea id="om-sidebar-' . $theme_type . '-content" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-content" type="text" name="optin_content" placeholder="e.g. Enter your email and we’ll send you updates and exciting news!" rows="4">' . htmlentities( $tab->get_field( 'content', 'text' ) ) . '</textarea>';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'content' );
	foreach ( (array) $tab->get_field( 'content', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_content_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-color">Optin Content Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-content-color" class="om-color-picker" name="optin_content_color" value="' . $tab->get_field( 'content', 'color', '#858585' ) . '" data-default-color="#858585" data-target="om-sidebar-' . $theme_type . '-optin-content" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-font">Optin Content Font</label>';
	echo '<select id="om-sidebar-' . $theme_type . '-content-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-content" data-property="font-family" name="optin_content_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'content', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-size">Optin Content Font Size</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline-size" data-target="om-sidebar-' . $theme_type . '-optin-content" name="optin_content_size" class="optin-size" type="text" value="' . $tab->get_field( 'content', 'size', '16' ) . '" placeholder="e.g. 16" />';
	echo '</p>';
	echo '</div>';
	if ( ! $tab->meta['custom_html'] ) :
		echo '<h3>Fields and Buttons</h3>';
		echo '<div class="fields-area">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-sidebar-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name-color">Optin Name Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-name" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name-font">Optin Name Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email">Optin Email Field</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email-color">Optin Email Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-email" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email-font">Optin Email Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit">Optin Submit Field</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'submit' );
		foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-sidebar-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color', '#50bbe8' ) . '" data-default-color="#50bbe8" data-target="om-sidebar-' . $theme_type . '-optin-submit" />';
		echo '<input type="hidden" name="optin_submit_border_color" value="' . $tab->get_field( 'submit', 'border_color', '#429bc0' ) . '" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '</div>';
	endif;

	echo '<h3>Custom Optin CSS</h3>';
	echo '<div class="custom-css-area">';
	echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
	echo '<p><strong><code>html div#om-' . $tab->optin->sidebar_name . '</code></strong></p>';
	echo '<textarea id="om-sidebar-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->sidebar_name . ' input[type=submit], html div#' . $tab->optin->sidebar_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
	echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="design-content">';
	echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

function om_sidebar_get_valley_theme( $theme_type ) {

	global $optin_monster_tab_optins;
	$tab = $optin_monster_tab_optins;

	ob_start();
	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
	echo '<div class="design-sidebar">';
	echo '<div class="controls-area clearfix">';
	echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="Close Customizer">Close</a>';
	echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="Save Changes">Save</a>';
	echo '</div>';
	echo '<div class="title-area clearfix">';
	echo '<p class="no-margin">You are now previewing:</p>';
	echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
	echo '</div>';
	echo '<div class="accordion-area clearfix">';
	echo '<h3>Background Colors</h3>';
	echo '<div class="colors-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-header-bg">Header Background Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-header-bg" class="om-bgcolor-picker" name="optin_header_bg" value="' . $tab->get_field( 'background', 'header', '#355368' ) . '" data-default-color="#355368" data-target="om-sidebar-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-bg">Content Background Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-content-bg" class="om-bgcolor-picker" name="optin_content_bg" value="' . $tab->get_field( 'background', 'content', '#f8fcff' ) . '" data-default-color="#f8fcff" data-target="om-sidebar-' . $theme_type . '-optin" />';
	echo '</p>';
	echo '</div>';

	echo '<h3>Title</h3>';
	echo '<div class="title-tag-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline">Optin Title</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'title', true, array( 'text_align' => 'center' ) );
	foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<div class="optin-input-meta">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-color">Optin Title Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color' ) . '" data-default-color="#ffffff" data-target="om-sidebar-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-font">Optin Title Font</label>';
	echo '<select id="om-sidebar-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline-size" data-target="om-sidebar-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size', '26' ) . '" placeholder="e.g. 26" />';
	echo '</p>';
	echo '</div>';
	echo '</div>';
	echo '<h3>Content</h3>';
	echo '<div class="content-area">';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-video-url">Optin Video</label>';
	echo '<p><small>' . __( 'Read the <a href="http://optinmonster.com/docs/embedding-a-video-in-compatible-themes/" title="Video embed how-to article" target="_blank">how-to article</a> on embedding videos.', 'optin-monster' ) . '</small></p>';
	echo '<input id="om-sidebar-' . $theme_type . '-video-url" class="main-field" data-target="om-sidebar-' . $theme_type . '-video" name="optin_video" type="text" value="' . $tab->get_field( 'content', 'video' ) . '" placeholder="Video URL" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content">Optin Content</label>';
	echo '<textarea id="om-sidebar-' . $theme_type . '-content" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-content" type="text" name="optin_content" placeholder="e.g. Enter your email and we’ll send you updates and exciting news!" rows="4">' . htmlentities( $tab->get_field( 'content', 'text' ) ) . '</textarea>';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'content' );
	foreach ( (array) $tab->get_field( 'content', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_content_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-color">Optin Content Color</label>';
	echo '<input type="text" id="om-sidebar-' . $theme_type . '-content-color" class="om-color-picker" name="optin_content_color" value="' . $tab->get_field( 'content', 'color', '#858585' ) . '" data-default-color="#858585" data-target="om-sidebar-' . $theme_type . '-optin-content" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-font">Optin Content Font</label>';
	echo '<select id="om-sidebar-' . $theme_type . '-content-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-content" data-property="font-family" name="optin_content_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'content', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-sidebar-' . $theme_type . '-content-size">Optin Content Font Size</label>';
	echo '<input id="om-sidebar-' . $theme_type . '-headline-size" data-target="om-sidebar-' . $theme_type . '-optin-content" name="optin_content_size" class="optin-size" type="text" value="' . $tab->get_field( 'content', 'size', '15' ) . '" placeholder="e.g. 15" />';
	echo '</p>';
	echo '</div>';
	if ( ! $tab->meta['custom_html'] ) :
		echo '<h3>Fields and Buttons</h3>';
		echo '<div class="fields-area">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-sidebar-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name-color">Optin Name Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-name" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-name-font">Optin Name Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email">Optin Email Field</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email-color">Optin Email Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-sidebar-' . $theme_type . '-optin-email" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-email-font">Optin Email Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit">Optin Submit Field</label>';
		echo '<input id="om-sidebar-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-sidebar-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'submit' );
		foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-sidebar-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
		echo '<input type="text" id="om-sidebar-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color', '#355368' ) . '" data-default-color="#355368" data-target="om-sidebar-' . $theme_type . '-optin-submit" />';
		echo '<input type="hidden" name="optin_submit_border_color" value="' . $tab->get_field( 'submit', 'border_color', '#213442' ) . '" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-sidebar-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
		echo '<select id="om-sidebar-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-sidebar-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '</div>';
	endif;

	echo '<h3>Custom Optin CSS</h3>';
	echo '<div class="custom-css-area">';
	echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
	echo '<p><strong><code>html div#om-' . $tab->optin->sidebar_name . '</code></strong></p>';
	echo '<textarea id="om-sidebar-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->sidebar_name . ' input[type=submit], html div#' . $tab->optin->sidebar_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
	echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="design-content">';
	echo '</div>';
	echo '</div>';

	return ob_get_clean();

}
