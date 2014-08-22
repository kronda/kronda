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
 * Plugin Name:  OptinMonster Slide In
 * Plugin URI:   http://optinmonster.com/
 * Description:  Adds a new optin type - Slide In - to the available optins.
 * Version:      1.0.4
 * Author:       Thomas Griffin
 * Author URI:   http://thomasgriffinmedia.com/
 * Text Domain:  optin-monster-footer
 * Contributors: griffinjt
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:  /lang
 */

add_action( 'init', 'om_slide_automatic_upgrades', 20 );
function om_slide_automatic_upgrades() {

    global $optin_monster_license;

    // Load the plugin updater.
    if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) :
        if ( ! empty( $optin_monster_license['key'] ) ) {
			$args = array(
				'remote_url' 	=> 'http://optinmonster.com/',
				'version' 		=> '1.0.4',
				'plugin_name'	=> 'OptinMonster Slide In',
				'plugin_slug' 	=> 'optin-monster-slide',
				'plugin_path' 	=> plugin_basename( __FILE__ ),
				'plugin_url' 	=> WP_PLUGIN_URL . '/optin-monster-slide',
				'time' 			=> 43200,
				'key' 			=> $optin_monster_license['key']
			);

			// Load the updater class.
			$optin_monster_slide_updater = new optin_monster_updater( $args );
		}
    endif;

}

add_action( 'optin_monster_optin_types', 'om_slide_optin_type' );
function om_slide_optin_type() {

    echo '<div class="optin-item one-fourth first" data-optin-type="slide">';
		echo '<h4>Slide In</h4>';
		echo '<img src="' . plugins_url( 'images/slideinicon.png', __FILE__ ) . '" />';
	echo '</div>';

}

add_action( 'optin_monster_design_slide', 'om_slide_design_output' );
function om_slide_design_output() {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    echo '<div class="optin-select-wrap clearfix">';
		echo '<div class="optin-item one-fourth first ' . ( isset( $tab->meta['theme'] ) && 'converse-theme' == $tab->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Converse Theme">';
			echo '<h4>Converse Theme</h4>';
			echo '<img src="' . plugins_url( 'images/slideinicon.png', __FILE__ ) . '" />';
			echo '<form id="converse-theme" data-optin-theme="converse-theme">';
			    echo om_slide_get_converse_theme( 'converse-theme' );
            echo '</form>';
		echo '</div>';
	echo '</div>';

}

add_filter( 'optin_monster_template_slide', 'om_slide_template_optin_slide', 10, 7 );
function om_slide_template_optin_slide( $html, $theme, $base_class, $hash, $optin, $env, $ssl ) {

    // Load template based on theme.
    switch ( $theme ) {
        case 'converse-theme' :
            $template = 'slide-' . $theme;
            require_once plugin_dir_path( __FILE__ ) . $template . '.php';
            $class = 'optin_monster_build_' . str_replace( '-', '_', $template );
    		$build = new $class( 'slide', $theme, $hash, $optin, $env, $ssl, $base_class );
    		$html  = $build->build();
        break;
    }

    // Return the HTML of the optin type and theme.
    return $html;

}

add_action( 'optin_monster_save_slide', 'om_slide_save_optin_slide', 10, 4 );
function om_slide_save_optin_slide( $type, $theme, $optin, $data ) {

    require_once plugin_dir_path( __FILE__ ) . 'save-' . $type . '-' . $theme . '.php';
	$class = 'optin_monster_save_' . $type . '_' . str_replace( '-', '_', $theme );
	$save  = new $class( $type, $theme, $optin, $data );
	$save->save_optin();

}

function om_slide_get_converse_theme( $theme_type ) {

    global $optin_monster_tab_optins;
    $tab = $optin_monster_tab_optins;

    ob_start();
    echo '<div class="design-customizer-ui" data-optin-theme="converse-theme">';
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
						echo '<label for="om-slide-' . $theme_type . '-header-bg">Header Background Color</label>';
						echo '<input type="text" id="om-slide-' . $theme_type . '-header-bg" class="om-bgcolor-picker" name="optin_header_bg" value="' . $tab->get_field( 'background', 'header' ) . '" data-default-color="#000" data-target="om-slide-' . $theme_type . '-optin .om-slide-open-holder" />';
					echo '</p>';
					echo '<p>';
						echo '<label for="om-slide-' . $theme_type . '-content-bg">Content Background Color</label>';
						echo '<input type="text" id="om-slide-' . $theme_type . '-content-bg" class="om-bgcolor-picker" name="optin_content_bg" value="' . $tab->get_field( 'background', 'content' ) . '" data-default-color="#000" data-target="om-slide-' . $theme_type . '-content" />';
					echo '</p>';
				echo '</div>';

				echo '<h3>Title and Tagline</h3>';
				echo '<div class="title-tag-area">';
					echo '<p>';
						echo '<label for="om-slide-' . $theme_type . '-headline-closed">Optin Title Closed</label>';
						echo '<input id="om-slide-' . $theme_type . '-headline-closed" class="main-field" data-target="om-slide-' . $theme_type . '-optin-title-closed" name="optin_title_closed" type="text" value="' . $tab->get_field( 'title_closed', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'title_closed' );
							foreach ( (array) $tab->get_field( 'title_closed', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_title_closed_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-headline-color-closed">Optin Title Closed Color</label>';
							echo '<input type="text" id="om-slide-' . $theme_type . '-headline-color-closed" class="om-color-picker" name="optin_title_closed_color" value="' . $tab->get_field( 'title_closed', 'color' ) . '" data-default-color="#fff" data-target="om-slide-' . $theme_type . '-optin-title-closed" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-headline-font-closed">Optin Title Closed Font</label>';
							echo '<select id="om-slide-' . $theme_type . '-headline-font-closed" class="main-field optin-font" data-target="om-slide-' . $theme_type . '-optin-title-closed" data-property="font-family" name="optin_title_closed_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'title_closed', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-headline-size-closed">Optin Title Closed Font Size</label>';
							echo '<input id="om-slide-' . $theme_type . '-headline-size-closed" data-target="om-slide-' . $theme_type . '-optin-title-closed" name="optin_title_closed_size" class="optin-size" type="text" value="' . $tab->get_field( 'title_closed', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-slide-' . $theme_type . '-headline-open">Optin Title Open</label>';
						echo '<input id="om-slide-' . $theme_type . '-headline-open" class="main-field" data-target="om-slide-' . $theme_type . '-optin-title-open" name="optin_title_open" type="text" value="' . $tab->get_field( 'title_open', 'text' ) . '" placeholder="e.g. OptinMonster Rules!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'title_open' );
							foreach ( (array) $tab->get_field( 'title_open', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_title_open_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-headline-color-open">Optin Title Open Color</label>';
							echo '<input type="text" id="om-slide-' . $theme_type . '-headline-color-open" class="om-color-picker" name="optin_title_open_color" value="' . $tab->get_field( 'title_open', 'color' ) . '" data-default-color="#fff" data-target="om-slide-' . $theme_type . '-optin-title-open" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-headline-font-open">Optin Title Open Font</label>';
							echo '<select id="om-slide-' . $theme_type . '-headline-font-open" class="main-field optin-font" data-target="om-slide-' . $theme_type . '-optin-title-open" data-property="font-family" name="optin_title_open_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'title_open', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-headline-size-open">Optin Title Open Font Size</label>';
							echo '<input id="om-slide-' . $theme_type . '-headline-size-open" data-target="om-slide-' . $theme_type . '-optin-title-open" name="optin_title_open_size" class="optin-size" type="text" value="' . $tab->get_field( 'title_open', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-slide-' . $theme_type . '-tagline">Optin Tagline</label>';
						echo '<textarea id="om-slide-' . $theme_type . '-tagline" class="main-field" data-target="om-slide-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="e.g. OptinMonster explodes your email list!" rows="4">' . $tab->get_field( 'tagline', 'text' ) . '</textarea>';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'tagline' );
							foreach ( (array) $tab->get_field( 'tagline', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta last">';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-tagline-color">Optin Tagline Color</label>';
							echo '<input type="text" id="om-slide-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $tab->get_field( 'tagline', 'color' ) . '" data-default-color="#fff" data-target="om-slide-' . $theme_type . '-optin-tagline" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-tagline-font">Optin Title Font</label>';
							echo '<select id="om-slide-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-slide-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-tagline-size">Optin Tagline Font Size</label>';
							echo '<input id="om-slide-' . $theme_type . '-headline-size" data-target="om-slide-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $tab->get_field( 'tagline', 'size' ) . '" placeholder="e.g. 36" />';
						echo '</p>';
					echo '</div>';
				echo '</div>';

                if ( ! $tab->meta['custom_html'] ) :
				echo '<h3>Fields and Buttons</h3>';
				echo '<div class="fields-area">';
					echo '<p>';
						echo '<label for="om-slide-' . $theme_type . '-name"><input style="display:inline;width:auto;margin-right:3px;" type="checkbox" id="om-slide-' . $theme_type . '-name" name="optin_name_show" value="' . $tab->get_field( 'name', 'show' ) . '"' . checked( $tab->get_field( 'name', 'show' ), 1, false ) . ' /> Show Optin Name Field?</label>';
						echo '<input id="om-slide-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-slide-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $tab->get_field( 'name', 'placeholder' ) . '" placeholder="e.g. Your Name" />';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-name-color">Optin Name Field Color</label>';
							echo '<input type="text" id="om-slide-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $tab->get_field( 'name', 'color' ) . '" data-default-color="#282828" data-target="om-slide-' . $theme_type . '-optin-name" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-name-font">Optin Name Field Font</label>';
							echo '<select id="om-slide-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-slide-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-slide-' . $theme_type . '-email">Optin Email Field</label>';
						echo '<input id="om-slide-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-slide-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $tab->get_field( 'email', 'placeholder' ) . '" placeholder="e.g. Your Email" />';
					echo '</p>';
					echo '<div class="optin-input-meta">';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-email-color">Optin Email Field Color</label>';
							echo '<input type="text" id="om-slide-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $tab->get_field( 'email', 'color' ) . '" data-default-color="#282828" data-target="om-slide-' . $theme_type . '-optin-email" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-email-font">Optin Email Field Font</label>';
							echo '<select id="om-slide-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-slide-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
							foreach ( $tab->account->get_available_fonts() as $font ) :
								$selected = $tab->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
								echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
							endforeach;
							echo '</select>';
						echo '</p>';
					echo '</div>';
					echo '<p>';
						echo '<label for="om-slide-' . $theme_type . '-submit">Optin Submit Field</label>';
						echo '<input id="om-slide-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-slide-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $tab->get_field( 'submit', 'placeholder' ) . '" placeholder="e.g. Sign Me Up!" />';
						echo '<span class="input-controls">';
							echo $tab->get_meta_controls( 'submit' );
							foreach ( (array) $tab->get_field( 'submit', 'meta' ) as $prop => $style )
								echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
						echo '</span>';
					echo '</p>';
					echo '<div class="optin-input-meta last">';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-submit-field-color">Optin Submit Field Color</label>';
							echo '<input type="text" id="om-slide-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $tab->get_field( 'submit', 'field_color' ) . '" data-default-color="#fff" data-target="om-slide-' . $theme_type . '-optin-submit" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-submit-bg-color">Optin Submit Background Color</label>';
							echo '<input type="text" id="om-slide-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $tab->get_field( 'submit', 'bg_color' ) . '" data-default-color="#484848" data-target="om-slide-' . $theme_type . '-optin-submit" />';
						echo '</p>';
						echo '<p>';
							echo '<label for="om-slide-' . $theme_type . '-submit-font">Optin Submit Field Font</label>';
							echo '<select id="om-slide-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-slide-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
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