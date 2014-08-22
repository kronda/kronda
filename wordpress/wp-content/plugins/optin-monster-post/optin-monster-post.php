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
 * Plugin Name:  OptinMonster After Post
 * Plugin URI:   http://optinmonster.com/
 * Description:  Adds a new optin type - After Post - to the available optins.
 * Version:      1.0.4
 * Author:       Thomas Griffin
 * Author URI:   http://thomasgriffinmedia.com/
 * Text Domain:  optin-monster-post
 * Contributors: griffinjt
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:  /lang
 */

add_action( 'init', 'om_post_automatic_upgrades', 20 );
function om_post_automatic_upgrades() {

    global $optin_monster_license;

    // Load the plugin updater.
    if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) :
        if ( ! empty( $optin_monster_license['key'] ) ) {
			$args = array(
				'remote_url' 	=> 'http://optinmonster.com/',
				'version' 		=> '1.0.4',
				'plugin_name'	=> 'OptinMonster After Post',
				'plugin_slug' 	=> 'optin-monster-post',
				'plugin_path' 	=> plugin_basename( __FILE__ ),
				'plugin_url' 	=> WP_PLUGIN_URL . '/optin-monster-post',
				'time' 			=> 43200,
				'key' 			=> $optin_monster_license['key']
			);

			// Load the updater class.
			$optin_monster_post_updater = new optin_monster_updater( $args );
		}
    endif;

}

add_action( 'optin_monster_optin_types', 'om_post_optin_type' );
function om_post_optin_type() {

    echo '<div class="optin-item one-fourth first" data-optin-type="post">';
		echo '<h4>After Post</h4>';
		echo '<img src="' . plugins_url( 'images/posticon.png', __FILE__ ) . '" />';
	echo '</div>';

}

add_action( 'optin_monster_config', 'om_post_hide_options', 0 );
function om_post_hide_options() {

    // Return early if not a post optin.
    if ( empty( $_GET['type'] ) || isset( $_GET['type'] ) && 'post' !== $_GET['type'] )
        return;

    echo '<style type="text/css">.optin-config-box:nth-of-type(2),.optin-config-box:nth-of-type(3),.optin-config-box:nth-of-type(5){display:none}</style>';

}

add_action( 'optin_monster_code', 'om_post_hide_output_options', 0 );
function om_post_hide_output_options() {

    // Return early if not a post optin.
    if ( empty( $_GET['type'] ) || isset( $_GET['type'] ) && 'post' !== $_GET['type'] )
        return;

    echo '<style type="text/css">.optin-config-box:nth-of-type(2),.optin-config-box:nth-of-type(3),.optin-config-box:nth-of-type(4),.optin-config-box:nth-of-type(5){display:none}</style>';

}

add_action( 'optin_monster_code_top', 'om_post_code_message', 0 );
function om_post_code_message() {

    // Return early if not a post optin.
    if ( empty( $_GET['type'] ) || isset( $_GET['type'] ) && 'post' !== $_GET['type'] )
        return;

    echo '<p><strong>This optin will automatically be added after each post on your website when it is activated on the site.</strong></p>';

}

add_action( 'optin_monster_code_bottom', 'om_post_code_auto', 0 );
function om_post_code_auto() {

    // Return early if not a post optin.
    if ( empty( $_GET['type'] ) || isset( $_GET['type'] ) && 'post' !== $_GET['type'] )
        return;

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    echo '<div class="optin-config-box">';
		echo '<h4><label for="om-automatic">' . __( 'Automatically add after post?', 'optin-monster' ) . '</label></h4>';
        echo '<input id="om-automatic" name="om-automatic" type="checkbox" tabindex="57" value="' . $tab->get_optin_setting( 'automatic' ) . '"' . checked( $tab->get_optin_setting( 'automatic' ), 1, false ) . ' />';
        echo '<label class="description" for="om-automatic" style="font-weight:400;display:inline;margin-left:5px">Automatically adds the optin after each post. You can turn this off and add it manually to your posts by <a href="http://optinmonster.com/docs/manually-add-after-post-optin/" target="_blank">clicking here and viewing the tutorial.</a></label>';
	echo '</div>';

}

add_action( 'optin_monster_design_post', 'om_post_design_output' );
function om_post_design_output() {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    echo '<div class="optin-select-wrap clearfix">';
		echo '<div class="optin-item one-fourth first ' . ( isset( $tab->meta['theme'] ) && 'action-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Action Theme">';
			echo '<h4>Action Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/action-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="action-theme" data-optin-theme="action-theme">';
			    echo om_post_get_action_theme( 'action-theme' );
            echo '</form>';
		echo '</div>';
		echo '<div class="optin-item one-fourth ' . ( isset( $tab->meta['theme'] ) && 'fabric-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Fabric Theme">';
			echo '<h4>Fabric Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/fabric-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="fabric-theme" data-optin-theme="fabric-theme">';
				echo om_post_get_fabric_theme( 'fabric-theme' );
			echo '</form>';
		echo '</div>';
		echo '<div class="optin-item one-fourth ' . ( isset( $tab->meta['theme'] ) && 'postal-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Postal Theme">';
			echo '<h4>Postal Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/postal-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="postal-theme" data-optin-theme="postal-theme">';
				echo om_post_get_postal_theme( 'postal-theme' );
			echo '</form>';
		echo '</div>';
		echo '<div class="optin-item one-fourth last ' . ( isset( $tab->meta['theme'] ) && 'banner-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Banner Theme">';
			echo '<h4>Banner Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/banner-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="banner-theme" data-optin-theme="banner-theme">';
				echo om_post_get_banner_theme( 'banner-theme' );
			echo '</form>';
		echo '</div>';
	echo '</div>';

}

add_filter( 'optin_monster_template_post', 'om_post_template_optin_post', 10, 7 );
function om_post_template_optin_post( $html, $theme, $base_class, $hash, $optin, $env, $ssl ) {

    // Load template based on theme.
    switch ( $theme ) {
        case 'action-theme' :
	        $html = om_post_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
			break;
	    case 'fabric-theme' :
		    $html = om_post_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
		    break;
	    case 'postal-theme' :
		    $html = om_post_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
		    break;
	    case 'banner-theme' :
		    $html = om_post_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
		    break;
    }

    // Return the HTML of the optin type and theme.
    return $html;

}

function om_post_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class ) {

	$template = 'post-' . $theme;
	require_once plugin_dir_path( __FILE__ ) . 'themes/' . $theme . '/' . 'template.php';
	$class = 'optin_monster_build_' . str_replace( '-', '_', $template );
	$build = new $class( 'footer', $theme, $hash, $optin, $env, $ssl, $base_class );
	return $build->build();

}

add_action( 'optin_monster_save_post', 'om_post_save_optin_post', 10, 4 );
function om_post_save_optin_post( $type, $theme, $optin, $data ) {

	require_once plugin_dir_path( __FILE__ ) . 'themes/' . $theme . '/save.php';
	$class = 'optin_monster_save_' . $type . '_' . str_replace( '-', '_', $theme );
	$save  = new $class( $type, $theme, $optin, $data );
	$save->save_optin();

}

function om_post_get_action_theme( $theme_type ) {

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
    					echo '<label for="om-post-' . $theme_type . '-content-bg">Content Background Color</label>';
    					echo '<input type="text" id="om-post-' . $theme_type . '-content-bg" class="om-bgcolor-picker" name="optin_content_bg" value="' . $tab->get_field( 'background', 'content' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-optin" />';
    				echo '</p>';
    			echo '</div>';

				echo '<h3>Title and Tagline</h3>';
				echo '<div class="title-tag-area">';
					echo '<p>';
						echo '<label for="om-post-' . $theme_type . '-headline">Optin Title</label>';
						echo '<input id="om-post-' . $theme_type . '-headline" class="main-field" data-target="om-post-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'title' );
							foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-headline-color">Optin Title Color</label>';
							echo '<input type="text" id="om-post-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color' ) . '" data-default-color="#ffffff" data-target="om-post-' . $theme_type . '-optin-title" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-headline-font">Optin Title Font</label>';
							echo '<select id="om-post-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
							echo '<input id="om-post-' . $theme_type . '-headline-size" data-target="om-post-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
    					echo '<label for="om-post-' . $theme_type . '-tagline">Optin Tagline</label>';
    					echo '<textarea id="om-post-' . $theme_type . '-tagline" class="main-field" data-target="om-post-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="e.g. OptinMonster explodes your email list!" rows="4">' . htmlentities( $tab->get_field( 'tagline', 'text' ) ) . '</textarea>';
    					echo '<span class="input-controls">';
    						echo $tab->get_meta_controls( 'tagline' );
    						foreach ( (array) $tab->get_field( 'tagline', 'meta' ) as $prop => $style )
    							echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
    					echo '</span>';
    				echo '</p>';
    				echo '<div class="optin-input-meta last">';
    					echo '<p>';
    						echo '<label for="om-post-' . $theme_type . '-tagline-color">Optin Tagline Color</label>';
    						echo '<input type="text" id="om-post-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $tab->get_field( 'tagline', 'color' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-tagline" />';
    					echo '</p>';
    					echo '<p>';
    						echo '<label for="om-post-' . $theme_type . '-tagline-font">Optin Tagline Font</label>';
    						echo '<select id="om-post-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
    						foreach ( $tab->account->get_available_fonts() as $font ) :
    							$selected = $tab->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
    							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
    						endforeach;
    						echo '</select>';
    					echo '</p>';
    					echo '<p>';
    						echo '<label for="om-post-' . $theme_type . '-tagline-size">Optin Tagline Font Size</label>';
    						echo '<input id="om-post-' . $theme_type . '-headline-size" data-target="om-post-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $tab->get_field( 'tagline', 'size' ) . '" placeholder="e.g. 36" />';
    					echo '</p>';
    				echo '</div>';
				echo '</div>';

                if ( ! $tab->meta['custom_html'] ) :
				echo '<h3>Fields and Buttons</h3>';
				echo '<div class="fields-area">';
					echo '<p>';
						echo '<label for="om-post-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-post-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
						echo '<input id="om-post-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-name-color">Optin Name Field Color</label>';
							echo '<input type="text" id="om-post-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-name" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-name-font">Optin Name Field Font</label>';
							echo '<select id="om-post-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-name-size">Optin Name Field Font Size</label>';
							echo '<input id="om-post-' . $theme_type . '-name-size" data-target="om-post-' . $theme_type . '-optin-name" name="optin_name_size" class="optin-size" type="text" value="' . $tab->get_field( 'name', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-post-' . $theme_type . '-email">Optin Email Field</label>';
						echo '<input id="om-post-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-email-color">Optin Email Field Color</label>';
							echo '<input type="text" id="om-post-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-email" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-email-font">Optin Email Field Font</label>';
							echo '<select id="om-post-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-email-size">Optin Email Field Font Size</label>';
							echo '<input id="om-post-' . $theme_type . '-email-size" data-target="om-post-' . $theme_type . '-optin-email" name="optin_email_size" class="optin-size" type="text" value="' . $tab->get_field( 'email', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-post-' . $theme_type . '-submit">Optin Submit Field</label>';
						echo '<input id="om-post-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'submit' );
							foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta last">';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
							echo '<input type="text" id="om-post-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-optin-submit" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
							echo '<input type="text" id="om-post-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color' ) . '" data-default-color="#484848" data-target="om-post-' . $theme_type . '-optin-submit" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
							echo '<select id="om-post-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-post-' . $theme_type . '-submit-size">Optin Submit Field Font Size</label>';
							echo '<input id="om-post-' . $theme_type . '-submit-size" data-target="om-post-' . $theme_type . '-optin-submit" name="optin_submit_size" class="optin-size" type="text" value="' . $tab->get_field( 'submit', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
				echo '</div>';
				endif;

				echo '<h3>Custom Optin CSS</h3>';
    			echo '<div class="custom-css-area">';
    				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
    				echo '<p><strong><code>html div#om-' . $tab->optin->post_name . '</code></strong></p>';
    				echo '<textarea id="om-post-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
    				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
    			echo '</div>';
			echo '</div>';
		echo '</div>';
		echo '<div class="design-content">';
		echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

function om_post_get_fabric_theme( $theme_type ) {

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
		echo '<label for="om-post-' . $theme_type . '-header-bg">Header Background Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-header-bg" class="om-bgcolor-picker" name="optin_header_bg" value="' . $tab->get_field( 'background', 'header', '#f16a10' ) . '" data-default-color="#f16a10" data-target="om-post-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-content-bg">Content Background Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-content-bg" class="om-bgcolor-picker" name="optin_content_bg" value="' . $tab->get_field( 'background', 'content', '#fbfdf3' ) . '" data-default-color="#fbfdf3" data-target="om-post-' . $theme_type . '-optin" />';
	echo '</p>';
	echo '</div>';

	echo '<h3>Title</h3>';
	echo '<div class="title-tag-area">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-headline">Optin Title</label>';
		echo '<input id="om-post-' . $theme_type . '-headline" class="main-field" data-target="om-post-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'title' );
		foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-headline-color">Optin Title Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color', '#fff' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-optin-title" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-headline-font">Optin Title Font</label>';
		echo '<select id="om-post-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
		echo '<input id="om-post-' . $theme_type . '-headline-size" data-target="om-post-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size', '26' ) . '" placeholder="e.g. 26" />';
		echo '</p>';
		echo '</div>';
	echo '</div>';
	echo '<h3>Content</h3>';
	echo '<div class="content-area">';
		echo '<p>';
			echo '<label for="om-post-' . $theme_type . '-content-image">Optin Image</label>';
			echo '<small>'.__('Click the button below to upload an image for this optin. It should be 88x134 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
			echo '<input type="hidden" name="optin_image" value="' . $tab->get_field( 'image' ) . '" />';
			echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
				echo '<div id="browse-button-' . $tab->optin->post_name . '"><a id="plupload-browse-button-' . $tab->optin->post_name . '" class="bullet-button" data-container="om-post-' . $theme_type . '-optin-image-container" href="#">Upload Image</a><a href="#" class="bullet-button remove-optin-image" data-container="om-post-' . $theme_type . '-optin-image-container">Remove Image</a></div>';
			echo '</div>';
		echo '</p>';
		echo '<p>';
			echo '<label for="om-post-' . $theme_type . '-content">Optin Content</label>';
			echo '<textarea id="om-post-' . $theme_type . '-content" class="main-field" data-target="om-post-' . $theme_type . '-optin-content" type="text" name="optin_content" placeholder="e.g. Enter your email and we’ll send you updates and exciting news!" rows="4">' . htmlentities( $tab->get_field( 'content', 'text' ) ) . '</textarea>';
			echo '<span class="input-controls">';
				echo $tab->get_meta_controls( 'content' );
				foreach ( (array) $tab->get_field( 'content', 'meta' ) as $prop => $style )
					echo '<input type="hidden" name="optin_content_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
			echo '</span>';
		echo '</p>';
		echo '<p>';
			echo '<label for="om-post-' . $theme_type . '-content-color">Optin Content Color</label>';
			echo '<input type="text" id="om-post-' . $theme_type . '-content-color" class="om-color-picker" name="optin_content_color" value="' . $tab->get_field( 'content', 'color', '#858585' ) . '" data-default-color="#858585" data-target="om-post-' . $theme_type . '-optin-content" />';
		echo '</p>';
		echo '<p>';
			echo '<label for="om-post-' . $theme_type . '-content-font">Optin Content Font</label>';
			echo '<select id="om-post-' . $theme_type . '-content-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-content" data-property="font-family" name="optin_content_font">';
				foreach ( $tab->account->get_available_fonts() as $font ) :
					$selected = $tab->get_field( 'content', 'font' ) == $font ? ' selected="selected"' : '';
					echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
				endforeach;
			echo '</select>';
		echo '</p>';
		echo '<p>';
			echo '<label for="om-post-' . $theme_type . '-content-size">Optin Content Font Size</label>';
			echo '<input id="om-post-' . $theme_type . '-headline-size" data-target="om-post-' . $theme_type . '-optin-content" name="optin_content_size" class="optin-size" type="text" value="' . $tab->get_field( 'content', 'size', '16' ) . '" placeholder="e.g. 16" />';
		echo '</p>';
	echo '</div>';
	if ( ! $tab->meta['custom_html'] ) :
		echo '<h3>Fields and Buttons</h3>';
		echo '<div class="fields-area">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-post-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
		echo '<input id="om-post-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name-color">Optin Name Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-name" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name-font">Optin Name Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email">Optin Email Field</label>';
		echo '<input id="om-post-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email-color">Optin Email Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-email" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email-font">Optin Email Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit">Optin Submit Field</label>';
		echo '<input id="om-post-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'submit' );
		foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color', '#f16a10' ) . '" data-default-color="#f16a10" data-target="om-post-' . $theme_type . '-optin-submit" />';
		echo '<input type="hidden" name="optin_submit_border_color" value="' . $tab->get_field( 'submit', 'border_color', '#cc611b' ) . '" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
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
	echo '<p><strong><code>html div#om-' . $tab->optin->post_name . '</code></strong></p>';
	echo '<textarea id="om-post-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
	echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="design-content">';
	echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

function om_post_get_postal_theme( $theme_type ) {

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
	echo '<label for="om-post-' . $theme_type . '-header-bg">Header Background Color</label>';
	echo '<input type="text" id="om-post-' . $theme_type . '-header-bg" class="om-bgcolor-picker" name="optin_header_bg" value="' . $tab->get_field( 'background', 'header', '#50bbe8' ) . '" data-default-color="#50bbe8" data-target="om-post-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '</div>';

	echo '<h3>Title</h3>';
	echo '<div class="title-tag-area">';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-headline">Optin Title</label>';
	echo '<input id="om-post-' . $theme_type . '-headline" class="main-field" data-target="om-post-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'title' );
	foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<div class="optin-input-meta">';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-headline-color">Optin Title Color</label>';
	echo '<input type="text" id="om-post-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color', '#fff' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-headline-font">Optin Title Font</label>';
	echo '<select id="om-post-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
	echo '<input id="om-post-' . $theme_type . '-headline-size" data-target="om-post-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size', '26' ) . '" placeholder="e.g. 26" />';
	echo '</p>';
	echo '</div>';
	echo '</div>';
	echo '<h3>Content</h3>';
	echo '<div class="content-area">';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content-image">Optin Image</label>';
	echo '<small>'.__('Click the button below to upload an image for this optin. It should be 88x134 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
	echo '<input type="hidden" name="optin_image" value="' . $tab->get_field( 'image' ) . '" />';
	echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
	echo '<div id="browse-button-' . $tab->optin->post_name . '"><a id="plupload-browse-button-' . $tab->optin->post_name . '" class="bullet-button" data-container="om-post-' . $theme_type . '-optin-image-container" href="#">Upload Image</a><a href="#" class="bullet-button remove-optin-image" data-container="om-post-' . $theme_type . '-optin-image-container">Remove Image</a></div>';
	echo '</div>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content">Optin Content</label>';
	echo '<textarea id="om-post-' . $theme_type . '-content" class="main-field" data-target="om-post-' . $theme_type . '-optin-content" type="text" name="optin_content" placeholder="e.g. Enter your email and we’ll send you updates and exciting news!" rows="4">' . htmlentities( $tab->get_field( 'content', 'text' ) ) . '</textarea>';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'content' );
	foreach ( (array) $tab->get_field( 'content', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_content_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content-color">Optin Content Color</label>';
	echo '<input type="text" id="om-post-' . $theme_type . '-content-color" class="om-color-picker" name="optin_content_color" value="' . $tab->get_field( 'content', 'color', '#858585' ) . '" data-default-color="#858585" data-target="om-post-' . $theme_type . '-optin-content" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content-font">Optin Content Font</label>';
	echo '<select id="om-post-' . $theme_type . '-content-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-content" data-property="font-family" name="optin_content_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'content', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content-size">Optin Content Font Size</label>';
	echo '<input id="om-post-' . $theme_type . '-headline-size" data-target="om-post-' . $theme_type . '-optin-content" name="optin_content_size" class="optin-size" type="text" value="' . $tab->get_field( 'content', 'size', '16' ) . '" placeholder="e.g. 16" />';
	echo '</p>';
	echo '</div>';
	if ( ! $tab->meta['custom_html'] ) :
		echo '<h3>Fields and Buttons</h3>';
		echo '<div class="fields-area">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-post-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
		echo '<input id="om-post-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name-color">Optin Name Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-name" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name-font">Optin Name Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email">Optin Email Field</label>';
		echo '<input id="om-post-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email-color">Optin Email Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-email" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email-font">Optin Email Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit">Optin Submit Field</label>';
		echo '<input id="om-post-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'submit' );
		foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color', '#50bbe8' ) . '" data-default-color="#50bbe8" data-target="om-post-' . $theme_type . '-optin-submit" />';
		echo '<input type="hidden" name="optin_submit_border_color" value="' . $tab->get_field( 'submit', 'border_color', '#429bc0' ) . '" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
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
	echo '<p><strong><code>html div#om-' . $tab->optin->post_name . '</code></strong></p>';
	echo '<textarea id="om-post-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
	echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="design-content">';
	echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

function om_post_get_banner_theme( $theme_type ) {

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
	echo '<label for="om-post-' . $theme_type . '-header-bg">Header Background Color</label>';
	echo '<input type="text" id="om-post-' . $theme_type . '-header-bg" class="om-bgcolor-picker" name="optin_header_bg" value="' . $tab->get_field( 'background', 'header', '#50aae9' ) . '" data-default-color="#50aae9" data-target="om-post-' . $theme_type . '-optin-title" />';
	echo '<input type="hidden" name="optin_header_border_color" value="' . $tab->get_field( 'header', 'border_color', '#3880bd' ) . '" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content-bg">Content Background Color</label>';
	echo '<input type="text" id="om-post-' . $theme_type . '-content-bg" class="om-bgcolor-picker" name="optin_content_bg" value="' . $tab->get_field( 'background', 'content', '#fff' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-content-clear" />';
	echo '</p>';
	echo '</div>';

	echo '<h3>Title</h3>';
	echo '<div class="title-tag-area">';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-headline">Optin Title</label>';
	echo '<input id="om-post-' . $theme_type . '-headline" class="main-field" data-target="om-post-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'title' );
	foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<div class="optin-input-meta">';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-headline-color">Optin Title Color</label>';
	echo '<input type="text" id="om-post-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color', '#fff' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-headline-font">Optin Title Font</label>';
	echo '<select id="om-post-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
	echo '<input id="om-post-' . $theme_type . '-headline-size" data-target="om-post-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size', '24' ) . '" placeholder="e.g. 24" />';
	echo '</p>';
	echo '</div>';
	echo '</div>';
	echo '<h3>Content</h3>';
	echo '<div class="content-area">';
	echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-content-image">'.__('Optin Image','optin-monster').'</label>';
		echo '<small>'.__('Click the button below to upload an image for this optin. It should be 280x245 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
		echo '<input type="hidden" name="optin_image" value="' . $tab->get_field( 'image' ) . '" />';
		echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
			echo '<div id="browse-button-' . $tab->optin->post_name . '"><a href="#" class="bullet-button remove-optin-image" data-container="om-post-' . $theme_type . '-optin-image-container">'.__('Remove Image','optin-monster').'</a></div>';
		echo '</div>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content">Optin Content</label>';
	echo '<textarea id="om-post-' . $theme_type . '-content" class="main-field" data-target="om-post-' . $theme_type . '-optin-content" type="text" name="optin_content" placeholder="e.g. Enter your email and we’ll send you updates and exciting news!" rows="4">' . htmlentities( $tab->get_field( 'content', 'text' ) ) . '</textarea>';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'content' );
	foreach ( (array) $tab->get_field( 'content', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_content_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content-color">Optin Content Color</label>';
	echo '<input type="text" id="om-post-' . $theme_type . '-content-color" class="om-color-picker" name="optin_content_color" value="' . $tab->get_field( 'content', 'color', '#858585' ) . '" data-default-color="#858585" data-target="om-post-' . $theme_type . '-optin-content" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content-font">Optin Content Font</label>';
	echo '<select id="om-post-' . $theme_type . '-content-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-content" data-property="font-family" name="optin_content_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'content', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-post-' . $theme_type . '-content-size">Optin Content Font Size</label>';
	echo '<input id="om-post-' . $theme_type . '-headline-size" data-target="om-post-' . $theme_type . '-optin-content" name="optin_content_size" class="optin-size" type="text" value="' . $tab->get_field( 'content', 'size', '15' ) . '" placeholder="e.g. 15" />';
	echo '</p>';
	echo '</div>';
	if ( ! $tab->meta['custom_html'] ) :
		echo '<h3>Fields and Buttons</h3>';
		echo '<div class="fields-area">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-post-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
		echo '<input id="om-post-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name-color">Optin Name Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-name" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-name-font">Optin Name Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email">Optin Email Field</label>';
		echo '<input id="om-post-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email-color">Optin Email Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-post-' . $theme_type . '-optin-email" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-email-font">Optin Email Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit">Optin Submit Field</label>';
		echo '<input id="om-post-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-post-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'submit' );
		foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-post-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
		echo '<input type="text" id="om-post-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color', '#50aae9' ) . '" data-default-color="#50aae9" data-target="om-post-' . $theme_type . '-optin-submit" />';
		echo '<input type="hidden" name="optin_submit_border_color" value="' . $tab->get_field( 'submit', 'border_color', '#3880bd' ) . '" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-post-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
		echo '<select id="om-post-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-post-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
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
	echo '<p><strong><code>html div#om-' . $tab->optin->post_name . '</code></strong></p>';
	echo '<textarea id="om-post-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
	echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="design-content">';
	echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

add_action( 'pre_get_posts', 'om_post_maybe_do_optin' );
function om_post_maybe_do_optin( $query ) {

    // If we are in the admin or not on the main query, do nothing.
    if ( is_admin() || ! $query->is_main_query() )
    	return;

    // Filter the content and excerpt with our social bar.
    $priority = apply_filters( 'optin_monster_post_priority', 999 );
    add_filter( 'the_content', 'om_post_do_optin', $priority );
    add_filter( 'the_excerpt', 'om_post_do_optin', $priority );

}

function om_post_do_optin( $content ) {

	global $post, $wp_current_filter;

	// If we are not on a single post, the global $post is not set or the post status is not published, return early.
    if ( ! is_singular( 'post' ) || empty( $post ) || isset( $post->ID ) && 'publish' !== get_post_status( $post->ID ) )
        return $content;

    // Don't do anything for excerpts.
    if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) )
    	return $content;

    // Get all after post optins.
    $optin_items = get_posts(
        array(
            'post_type' => 'optin',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'no_found_rows' => true,
            'cache_results' => false,
            'nopaging' => true
        )
    );
    $optins = array();
    foreach ( (array) $optin_items as $optin ) {
        $meta = get_post_meta( $optin->ID, '_om_meta', true );

        // Skip over clones. They will be checked with parents.
        if ( isset( $meta['is_clone'] ) ) {
            continue;
        }

        // If the optin has a clone, overwrite the main data with the clone data.
        if ( isset( $meta['has_clone'] ) ) {
            // Get the clone and prepare to select at random the optin to use.
            $clone      = get_post( $meta['has_clone'] );
            $clone_meta = get_post_meta( $clone->ID, '_om_meta', true );

            // If the clone is not active, set back to the main optin.
            if ( ! empty( $clone_meta['display']['enabled'] ) || $clone_meta['display']['enabled'] ) {
                $optin_array   = array();
                $optin_array[] = $optin;
                $optin_array[] = $clone;

                // Select randomly the optin to use.
                $optin = $optin_array[rand()%2];
                $meta  = get_post_meta( $optin->ID, '_om_meta', true );
            }
        }

        if ( isset( $meta['type'] ) && 'post' !== $meta['type'] ) {
            continue;
        }

        if ( isset( $meta['display']['automatic'] ) && ! $meta['display']['automatic'] ) {
            continue;
        }

        $optins[] = $optin;
    }

    // Loop through the optins and output them in the content.
    $optin_output = '';
    foreach ( (array) $optins as $optin ) {
        $optin_output .= optin_monster_tag( $optin->post_name, true );
    }

    return $content . $optin_output;

}