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
 * Plugin Name:  OptinMonster Footer Bar
 * Plugin URI:   http://optinmonster.com/
 * Description:  Adds a new optin type - Footer - to the available optins.
 * Version:      1.0.4
 * Author:       Thomas Griffin
 * Author URI:   http://thomasgriffinmedia.com/
 * Text Domain:  optin-monster-footer
 * Contributors: griffinjt
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:  /lang
 */

add_action( 'init', 'om_footer_automatic_upgrades', 20 );
function om_footer_automatic_upgrades() {

    global $optin_monster_license;

    // Load the plugin updater.
    if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) :
        if ( ! empty( $optin_monster_license['key'] ) ) {
			$args = array(
				'remote_url' 	=> 'http://optinmonster.com/',
				'version' 		=> '1.0.4',
				'plugin_name'	=> 'OptinMonster Footer Bar',
				'plugin_slug' 	=> 'optin-monster-footer',
				'plugin_path' 	=> plugin_basename( __FILE__ ),
				'plugin_url' 	=> WP_PLUGIN_URL . '/optin-monster-footer',
				'time' 			=> 43200,
				'key' 			=> $optin_monster_license['key']
			);

			// Load the updater class.
			$optin_monster_footer_updater = new optin_monster_updater( $args );
		}
    endif;

}

add_action( 'optin_monster_optin_types', 'om_footer_optin_type' );
function om_footer_optin_type() {

    echo '<div class="optin-item one-fourth first" data-optin-type="footer">';
		echo '<h4>Footer Bar</h4>';
		echo '<img src="' . plugins_url( 'images/footerbaricon.png', __FILE__ ) . '" />';
	echo '</div>';

}

add_action( 'optin_monster_design_footer', 'om_footer_design_output' );
function om_footer_design_output() {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    echo '<div class="optin-select-wrap clearfix">';
		echo '<div class="optin-item one-fourth first ' . ( isset( $tab->meta['theme'] ) && 'sleek-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Sleek Theme">';
			echo '<h4>Sleek Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/sleek-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="sleek-theme" data-optin-theme="sleek-theme">';
			    echo om_footer_get_sleek_theme( 'sleek-theme' );
            echo '</form>';
		echo '</div>';
		echo '<div class="optin-item one-fourth ' . ( isset( $tab->meta['theme'] ) && 'tiles-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Tiles Theme">';
			echo '<h4>Tiles Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/tiles-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="tiles-theme" data-optin-theme="tiles-theme">';
				echo om_footer_get_tiles_theme( 'tiles-theme' );
			echo '</form>';
		echo '</div>';
		echo '<div class="optin-item one-fourth ' . ( isset( $tab->meta['theme'] ) && 'postal-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Postal Theme">';
			echo '<h4>Postal Theme</h4>';
			echo '<img src="' . plugins_url( 'themes/postal-theme/images/icon.png', __FILE__ ) . '" />';
			echo '<form id="postal-theme" data-optin-theme="postal-theme">';
				echo om_footer_get_postal_theme( 'postal-theme' );
			echo '</form>';
		echo '</div>';
	echo '</div>';

}

add_filter( 'optin_monster_template_footer', 'om_footer_template_optin_footer', 10, 7 );
function om_footer_template_optin_footer( $html, $theme, $base_class, $hash, $optin, $env, $ssl ) {

    // Load template based on theme.
    switch ( $theme ) {
        case 'sleek-theme' :
			$html = om_footer_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
			break;
	    case 'tiles-theme' :
		    $html = om_footer_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
		    break;
	    case 'postal-theme' :
		    $html = om_footer_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class );
		    break;
    }

    // Return the HTML of the optin type and theme.
    return $html;

}

function om_footer_load_theme( $theme, $hash, $optin, $env, $ssl, $base_class ) {

	$template = 'footer-' . $theme;
	require_once plugin_dir_path( __FILE__ ) . 'themes/' . $theme . '/' . 'template.php';
	$class = 'optin_monster_build_' . str_replace( '-', '_', $template );
	$build = new $class( 'footer', $theme, $hash, $optin, $env, $ssl, $base_class );
	return $build->build();

}

add_action( 'optin_monster_save_footer', 'om_footer_save_optin_footer', 10, 4 );
function om_footer_save_optin_footer( $type, $theme, $optin, $data ) {

    require_once plugin_dir_path( __FILE__ ) . 'themes/' . $theme . '/save.php';
	$class = 'optin_monster_save_' . $type . '_' . str_replace( '-', '_', $theme );
	$save  = new $class( $type, $theme, $optin, $data );
	$save->save_optin();

}

function om_footer_get_sleek_theme( $theme_type ) {

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
				echo '<h3>Title</h3>';
				echo '<div class="title-tag-area">';
					echo '<p>';
						echo '<label for="om-footer-' . $theme_type . '-headline">Optin Title</label>';
						echo '<input id="om-footer-' . $theme_type . '-headline" class="main-field" data-target="om-footer-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'title' );
							foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-headline-color">Optin Title Color</label>';
							echo '<input type="text" id="om-footer-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color' ) . '" data-default-color="#ffffff" data-target="om-footer-' . $theme_type . '-optin-title" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-headline-font">Optin Title Font</label>';
							echo '<select id="om-footer-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
							echo '<input id="om-footer-' . $theme_type . '-headline-size" data-target="om-footer-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
				echo '</div>';

                if ( ! $tab->meta['custom_html'] ) :
				echo '<h3>Fields and Buttons</h3>';
				echo '<div class="fields-area">';
					echo '<p>';
						echo '<label for="om-footer-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-footer-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
						echo '<input id="om-footer-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-name-color">Optin Name Field Color</label>';
							echo '<input type="text" id="om-footer-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color' ) . '" data-default-color="#282828" data-target="om-footer-' . $theme_type . '-optin-name" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-name-font">Optin Name Field Font</label>';
							echo '<select id="om-footer-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-name-size">Optin Name Field Font Size</label>';
							echo '<input id="om-footer-' . $theme_type . '-name-size" data-target="om-footer-' . $theme_type . '-optin-name" name="optin_name_size" class="optin-size" type="text" value="' . $tab->get_field( 'name', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-footer-' . $theme_type . '-email">Optin Email Field</label>';
						echo '<input id="om-footer-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-email-color">Optin Email Field Color</label>';
							echo '<input type="text" id="om-footer-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color' ) . '" data-default-color="#282828" data-target="om-footer-' . $theme_type . '-optin-email" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-email-font">Optin Email Field Font</label>';
							echo '<select id="om-footer-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-email-size">Optin Email Field Font Size</label>';
							echo '<input id="om-footer-' . $theme_type . '-email-size" data-target="om-footer-' . $theme_type . '-optin-email" name="optin_email_size" class="optin-size" type="text" value="' . $tab->get_field( 'email', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-footer-' . $theme_type . '-submit">Optin Submit Field</label>';
						echo '<input id="om-footer-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'submit' );
							foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta last">';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
							echo '<input type="text" id="om-footer-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color' ) . '" data-default-color="#fff" data-target="om-footer-' . $theme_type . '-optin-submit" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
							echo '<input type="text" id="om-footer-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color' ) . '" data-default-color="#484848" data-target="om-footer-' . $theme_type . '-optin-submit" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
							echo '<select id="om-footer-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-footer-' . $theme_type . '-submit-size">Optin Submit Field Font Size</label>';
							echo '<input id="om-footer-' . $theme_type . '-submit-size" data-target="om-footer-' . $theme_type . '-optin-submit" name="optin_submit_size" class="optin-size" type="text" value="' . $tab->get_field( 'submit', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
				echo '</div>';
				endif;

				echo '<h3>Custom Optin CSS</h3>';
    			echo '<div class="custom-css-area">';
    				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
    				echo '<p><strong><code>html div#om-' . $tab->optin->post_name . '</code></strong></p>';
    				echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
    				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
    			echo '</div>';
			echo '</div>';
		echo '</div>';
		echo '<div class="design-content">';
		echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

function om_footer_get_tiles_theme( $theme_type ) {

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
	echo '<h3>Title</h3>';
	echo '<div class="title-tag-area">';
	echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-headline">Optin Title</label>';
		echo '<input id="om-footer-' . $theme_type . '-headline" class="main-field" data-target="om-footer-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
		echo '<span class="input-controls">';
			echo $tab->get_meta_controls( 'title', true, array( 'font_weight' => 'bold' ) );
			foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
				echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
	echo '</p>';
	echo '<div class="optin-input-meta">';
		echo '<p>';
			echo '<label for="om-footer-' . $theme_type . '-headline-color">Optin Title Color</label>';
			echo '<input type="text" id="om-footer-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color', '#fff' ) . '" data-default-color="#fff" data-target="om-footer-' . $theme_type . '-optin-title" />';
		echo '</p>';
		echo '<p>';
			echo '<label for="om-footer-' . $theme_type . '-headline-font">Optin Title Font</label>';
			echo '<select id="om-footer-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
			foreach ( $tab->account->get_available_fonts() as $font ) :
				$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
		echo '</p>';
		echo '<p>';
			echo '<label for="om-footer-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
			echo '<input id="om-footer-' . $theme_type . '-headline-size" data-target="om-footer-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size', '22' ) . '" placeholder="e.g. 22" />';
		echo '</p>';
	echo '</div>';
	echo '</div>';

	if ( ! $tab->meta['custom_html'] ) :
		echo '<h3>Fields and Buttons</h3>';
		echo '<div class="fields-area">';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-footer-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
		echo '<input id="om-footer-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-name-color">Optin Name Field Color</label>';
		echo '<input type="text" id="om-footer-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color', '#fff' ) . '" data-default-color="#fff" data-target="om-footer-' . $theme_type . '-optin-name" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-name-font">Optin Name Field Font</label>';
		echo '<select id="om-footer-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-email">Optin Email Field</label>';
		echo '<input id="om-footer-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-email-color">Optin Email Field Color</label>';
		echo '<input type="text" id="om-footer-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color', '#fff' ) . '" data-default-color="#fff" data-target="om-footer-' . $theme_type . '-optin-email" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-email-font">Optin Email Field Font</label>';
		echo '<select id="om-footer-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-submit">Optin Submit Field</label>';
		echo '<input id="om-footer-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'submit' );
		foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
		echo '<input type="text" id="om-footer-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'color', '#fff' ) . '" data-default-color="#fff" data-target="om-footer-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
		echo '<input type="text" id="om-footer-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color', '#3ea2dc' ) . '" data-default-color="#3ea2dc" data-target="om-footer-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
		echo '<select id="om-footer-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
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
	echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
	echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="design-content">';
	echo '</div>';
	echo '</div>';

	return ob_get_clean();

}

function om_footer_get_postal_theme( $theme_type ) {

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
	echo '<h3>Title</h3>';
	echo '<div class="title-tag-area">';
	echo '<p>';
	echo '<label for="om-footer-' . $theme_type . '-headline">Optin Title</label>';
	echo '<input id="om-footer-' . $theme_type . '-headline" class="main-field" data-target="om-footer-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . $tab->get_field( 'title', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
	echo '<span class="input-controls">';
	echo $tab->get_meta_controls( 'title' );
	foreach ( (array) $tab->get_field( 'title', 'meta' ) as $prop => $style )
		echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
	echo '</span>';
	echo '</p>';
	echo '<div class="optin-input-meta">';
	echo '<p>';
	echo '<label for="om-footer-' . $theme_type . '-headline-color">Optin Title Color</label>';
	echo '<input type="text" id="om-footer-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $tab->get_field( 'title', 'color', '#59626d' ) . '" data-default-color="#59626d" data-target="om-footer-' . $theme_type . '-optin-title" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-footer-' . $theme_type . '-headline-font">Optin Title Font</label>';
	echo '<select id="om-footer-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
	foreach ( $tab->account->get_available_fonts() as $font ) :
		$selected = $tab->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
		echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
	endforeach;
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo '<label for="om-footer-' . $theme_type . '-headline-size">Optin Title Font Size</label>';
	echo '<input id="om-footer-' . $theme_type . '-headline-size" data-target="om-footer-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $tab->get_field( 'title', 'size', '24' ) . '" placeholder="e.g. 24" />';
	echo '</p>';
	echo '</div>';
	echo '</div>';

	if ( ! $tab->meta['custom_html'] ) :
		echo '<h3>Fields and Buttons</h3>';
		echo '<div class="fields-area">';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-footer-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
		echo '<input id="om-footer-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-name-color">Optin Name Field Color</label>';
		echo '<input type="text" id="om-footer-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color', '#59626d' ) . '" data-default-color="#59626d" data-target="om-footer-' . $theme_type . '-optin-name" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-name-font">Optin Name Field Font</label>';
		echo '<select id="om-footer-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-email">Optin Email Field</label>';
		echo '<input id="om-footer-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-email-color">Optin Email Field Color</label>';
		echo '<input type="text" id="om-footer-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color', '#59626d' ) . '" data-default-color="#59626d" data-target="om-footer-' . $theme_type . '-optin-email" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-email-font">Optin Email Field Font</label>';
		echo '<select id="om-footer-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
		foreach ( $tab->account->get_available_fonts() as $font ) :
			$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-submit">Optin Submit Field</label>';
		echo '<input id="om-footer-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-footer-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
		echo '<span class="input-controls">';
		echo $tab->get_meta_controls( 'submit' );
		foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
		echo '<input type="text" id="om-footer-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-footer-' . $theme_type . '-optin-submit" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
		echo '<input type="text" id="om-footer-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color', '#65d759' ) . '" data-default-color="#65d759" data-target="om-footer-' . $theme_type . '-optin-submit" />';
		echo '<input type="hidden" name="optin_submit_border_color" value="' . $tab->get_field( 'submit', 'border_color', '#429bc0' ) . '" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-footer-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
		echo '<select id="om-footer-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-footer-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
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
	echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $tab->optin->post_name . ' input[type=submit], html div#' . $tab->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $tab->get_field( 'custom_css' ) . '</textarea>';
	echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>Click here for help on using custom CSS with OptinMonster.</em></a></small>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="design-content">';
	echo '</div>';
	echo '</div>';

	return ob_get_clean();

}
