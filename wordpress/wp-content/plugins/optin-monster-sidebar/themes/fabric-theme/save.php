<?php
/**
 * Saves the post optin "Fabric Theme".
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Save an optin theme.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_save_sidebar_fabric_theme {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $type, $theme, $optin, $meta, $data;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $type, $theme, $optin, $data ) {

		// Set class properties.
		global $optin_monster_account;
		$this->type    = $type;
		$this->theme   = $theme;
		$this->optin   = $optin;
		$this->data    = $data;
		$this->meta    = get_post_meta( $this->optin, '_om_meta', true );
		$this->account = $optin_monster_account;

	}

	/**
	 * Builds out the proper optin theme and styling.
	 *
	 * @since 1.0.0
	 */
	public function save_optin() {

		// Save the meta for the optin.
		$this->meta['type']  = $this->type;
		$this->meta['theme'] = $this->theme;

		if ( empty( $this->meta['background']['header'] ) )
			$this->meta['background']['header'] = isset( $this->data['optin_header_bg'] ) ? esc_attr( $this->data['optin_header_bg'] ) : '';
		else
			$this->meta['background']['header'] = isset( $this->data['optin_header_bg'] ) ? esc_attr( $this->data['optin_header_bg'] ) : $this->meta['background']['header'];
		
		if ( empty( $this->meta['background']['content'] ) )
		    $this->meta['background']['content'] = isset( $this->data['optin_content_bg'] ) ? esc_attr( $this->data['optin_content_bg'] ) : '';
		else
		    $this->meta['background']['content'] = isset( $this->data['optin_content_bg'] ) ? esc_attr( $this->data['optin_content_bg'] ) : $this->meta['background']['content'];

		// Save the optin title.
		if ( empty( $this->meta['title']['text'] ) )
		    $this->meta['title']['text'] = isset( $this->data['optin_title'] ) ? wp_kses_post( $this->data['optin_title'] ) : '';
		else
		    $this->meta['title']['text'] = isset( $this->data['optin_title'] ) ? wp_kses_post( $this->data['optin_title'] ) : $this->meta['title']['text'];

		if ( empty( $this->meta['title']['color'] ) )
		    $this->meta['title']['color'] = isset( $this->data['optin_title_color'] ) ? esc_attr( $this->data['optin_title_color'] ) : '';
		else
		    $this->meta['title']['color'] = isset( $this->data['optin_title_color'] ) ? esc_attr( $this->data['optin_title_color'] ) : $this->meta['title']['color'];

		if ( empty( $this->meta['title']['font'] ) ) {
		    $this->meta['title']['font'] = isset( $this->data['optin_title_font'] ) && 'Select your font...' !== $this->data['optin_title_font'] ? esc_attr( $this->data['optin_title_font'] ) : '';
		} else {
		    $this->meta['title']['font'] = isset( $this->data['optin_title_font'] ) && 'Select your font...' !== $this->data['optin_title_font'] ? esc_attr( $this->data['optin_title_font'] ) : $this->meta['title']['font'];
		}

		if ( empty( $this->meta['title']['size'] ) )
		    $this->meta['title']['size'] = isset( $this->data['optin_title_size'] ) ? esc_attr( $this->data['optin_title_size'] ) : '';
		else
		    $this->meta['title']['size'] = isset( $this->data['optin_title_size'] ) ? esc_attr( $this->data['optin_title_size'] ) : $this->meta['title']['size'];

		// Save all meta related items.
		$this->save_meta( 'title' );

		// Save the optin content.
		if ( empty( $this->meta['content']['text'] ) )
		    $this->meta['content']['text'] = isset( $this->data['optin_content'] ) ? wp_kses_post( $this->data['optin_content'] ) : '';
		else
		    $this->meta['content']['text'] = isset( $this->data['optin_content'] ) ? wp_kses_post( $this->data['optin_content'] ) : $this->meta['content']['text'];

		if ( empty( $this->meta['content']['color'] ) )
		    $this->meta['content']['color'] = isset( $this->data['optin_content_color'] ) ? esc_attr( $this->data['optin_content_color'] ) : '';
		else
		    $this->meta['content']['color'] = isset( $this->data['optin_content_color'] ) ? esc_attr( $this->data['optin_content_color'] ) : $this->meta['content']['color'];

		if ( empty( $this->meta['content']['font'] ) ) {
		    $this->meta['content']['font'] = isset( $this->data['optin_content_font'] ) && 'Select your font...' !== $this->data['optin_content_font'] ? esc_attr( $this->data['optin_content_font'] ) : '';
		} else {
		    $this->meta['content']['font'] = isset( $this->data['optin_content_font'] ) && 'Select your font...' !== $this->data['optin_content_font'] ? esc_attr( $this->data['optin_content_font'] ) : $this->meta['content']['font'];
		}

		if ( empty( $this->meta['content']['size'] ) )
		    $this->meta['content']['size'] = isset( $this->data['optin_content_size'] ) ? esc_attr( $this->data['optin_content_size'] ) : '';
		else
		    $this->meta['content']['size'] = isset( $this->data['optin_content_size'] ) ? esc_attr( $this->data['optin_content_size'] ) : $this->meta['content']['size'];

        // Save all meta related items.
		$this->save_meta( 'content' );

		// Save the optin name input field.
		if ( isset( $this->data['optin_name_show'] ) )
			$this->meta['name']['show'] = true;
		else
			$this->meta['name']['show'] = false;

		if ( empty( $this->meta['name']['placeholder'] ) )
		    $this->meta['name']['placeholder'] = isset( $this->data['optin_name_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_name_placeholder'] ) ) : '';
		else
		    $this->meta['name']['placeholder'] = isset( $this->data['optin_name_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_name_placeholder'] ) ) : $this->meta['name']['placeholder'];

		if ( empty( $this->meta['name']['color'] ) )
		    $this->meta['name']['color'] = isset( $this->data['optin_name_color'] ) ? esc_attr( $this->data['optin_name_color'] ) : '';
		else
		    $this->meta['name']['color'] = isset( $this->data['optin_name_color'] ) ? esc_attr( $this->data['optin_name_color'] ) : $this->meta['name']['color'];

		if ( empty( $this->meta['name']['font'] ) ) {
		    $this->meta['name']['font'] = isset( $this->data['optin_name_font'] ) && 'Select your font...' !== $this->data['optin_name_font'] ? esc_attr( $this->data['optin_name_font'] ) : '';
		} else {
		    $this->meta['name']['font'] = isset( $this->data['optin_name_font'] ) && 'Select your font...' !== $this->data['optin_name_font'] ? esc_attr( $this->data['optin_name_font'] ) : $this->meta['name']['font'];
		}

		// Save all meta related items.
		$this->save_meta( 'name' );

		// Save the optin email input field.
		if ( empty( $this->meta['email']['placeholder'] ) )
		    $this->meta['email']['placeholder'] = isset( $this->data['optin_email_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_email_placeholder'] ) ) : '';
		else
		    $this->meta['email']['placeholder'] = isset( $this->data['optin_email_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_email_placeholder'] ) ) : $this->meta['email']['placeholder'];

		if ( empty( $this->meta['email']['color'] ) )
		    $this->meta['email']['color'] = isset( $this->data['optin_email_color'] ) ? esc_attr( $this->data['optin_email_color'] ) : '';
		else
		    $this->meta['email']['color'] = isset( $this->data['optin_email_color'] ) ? esc_attr( $this->data['optin_email_color'] ) : $this->meta['email']['color'];

		if ( empty( $this->meta['email']['font'] ) ) {
		    $this->meta['email']['font'] = isset( $this->data['optin_email_font'] ) && 'Select your font...' !== $this->data['optin_email_font'] ? esc_attr( $this->data['optin_email_font'] ) : '';
		} else {
		    $this->meta['email']['font'] = isset( $this->data['optin_email_font'] ) && 'Select your font...' !== $this->data['optin_email_font'] ? esc_attr( $this->data['optin_email_font'] ) : $this->meta['email']['font'];
		}

		// Save all meta related items.
		$this->save_meta( 'email' );

		// Save the optin submit field.
		if ( empty( $this->meta['submit']['placeholder'] ) )
		    $this->meta['submit']['placeholder'] = isset( $this->data['optin_submit_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_submit_placeholder'] ) ) : '';
		else
		    $this->meta['submit']['placeholder'] = isset( $this->data['optin_submit_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_submit_placeholder'] ) ) : $this->meta['submit']['placeholder'];

		if ( empty( $this->meta['submit']['field_color'] ) )
		    $this->meta['submit']['field_color'] = isset( $this->data['optin_submit_field_color'] ) ? esc_attr( $this->data['optin_submit_field_color'] ) : '';
		else
		    $this->meta['submit']['field_color'] = isset( $this->data['optin_submit_field_color'] ) ? esc_attr( $this->data['optin_submit_field_color'] ) : $this->meta['submit']['field_color'];

		if ( empty( $this->meta['submit']['bg_color'] ) )
		    $this->meta['submit']['bg_color'] = isset( $this->data['optin_submit_bg_color'] ) ? esc_attr( $this->data['optin_submit_bg_color'] ) : '';
		else
		    $this->meta['submit']['bg_color'] = isset( $this->data['optin_submit_bg_color'] ) ? esc_attr( $this->data['optin_submit_bg_color'] ) : $this->meta['submit']['bg_color'];

		if ( empty( $this->meta['submit']['border_color'] ) )
			$this->meta['submit']['border_color'] = isset( $this->data['optin_submit_border_color'] ) ? esc_attr( $this->data['optin_submit_border_color'] ) : '';
		else
			$this->meta['submit']['border_color'] = isset( $this->data['optin_submit_border_color'] ) ? esc_attr( $this->data['optin_submit_border_color'] ) : $this->meta['submit']['border_color'];

		if ( empty( $this->meta['submit']['font'] ) ) {
		    $this->meta['submit']['font'] = isset( $this->data['optin_submit_font'] ) && 'Select your font...' !== $this->data['optin_submit_font'] ? esc_attr( $this->data['optin_submit_font'] ) : '';
		} else {
		    $this->meta['submit']['font'] = isset( $this->data['optin_submit_font'] ) && 'Select your font...' !== $this->data['optin_submit_font'] ? esc_attr( $this->data['optin_submit_font'] ) : $this->meta['submit']['font'];
		}

		// Save all meta related items.
		$this->save_meta( 'submit' );

		// Build all of the fonts together.
		$this->meta['fonts'] = array( $this->meta['title']['font'], $this->meta['name']['font'], $this->meta['email']['font'], $this->meta['submit']['font'] );
		$this->meta['fonts'] = array_filter( $this->meta['fonts'] );
		$this->meta['fonts'] = array_unique( $this->meta['fonts'] );

		// Remove any non-Google related fonts.
		foreach ( $this->meta['fonts'] as $i => $font )
			if ( ! in_array( $font, $this->account->get_available_fonts( false ) ) )
				unset( $this->meta['fonts'][$i] );

		// If there is any custom CSS, save that now.
        if ( isset( $this->data['optin_custom_css'] ) )
            $this->meta['custom_css'] = trim( esc_html( $this->data['optin_custom_css'] ) );

		// Finally update the post meta.
		update_post_meta( $this->optin, '_om_meta', $this->meta );

	}

	public function get_field( $field, $subfield = '' ) {

		if ( ! empty( $subfield ) )
			return isset( $this->meta[$field][$subfield] ) ? $this->meta[$field][$subfield] : '';
		else
			return isset( $this->meta[$field] ) ? $this->meta[$field] : '';

	}

	public function save_meta( $field ) {

		if ( 'email' == $field || 'name' == $field || 'submit' == $field )
			$meta_field = $field . '_placeholder';
		else
			$meta_field = $field;

		if ( ! isset( $this->meta[$field]['meta']['font_weight'] ) )
		    $this->meta[$field]['meta']['font_weight'] = isset( $this->data['optin_' . $meta_field . '_font-weight'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_font-weight'] ) : 'normal';
		else
		    $this->meta[$field]['meta']['font_weight'] = isset( $this->data['optin_' . $meta_field . '_font-weight'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_font-weight'] ) : $this->meta[$field]['meta']['font_weight'];

		if ( ! isset( $this->meta[$field]['meta']['font_style'] ) )
		    $this->meta[$field]['meta']['font_style'] = isset( $this->data['optin_' . $meta_field . '_font-style'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_font-style'] ) : 'normal';
		else
		    $this->meta[$field]['meta']['font_style'] = isset( $this->data['optin_' . $meta_field . '_font-style'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_font-style'] ) : $this->meta[$field]['meta']['font_style'];

		if ( ! isset( $this->meta[$field]['meta']['text_decoration'] ) )
		    $this->meta[$field]['meta']['text_decoration'] = isset( $this->data['optin_' . $meta_field . '_text-decoration'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_text-decoration'] ) : 'none';
		else
		    $this->meta[$field]['meta']['text_decoration'] = isset( $this->data['optin_' . $meta_field . '_text-decoration'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_text-decoration'] ) : $this->meta[$field]['meta']['text_decoration'];

		if ( ! isset( $this->meta[$field]['meta']['text_align'] ) )
		    $this->meta[$field]['meta']['text_align'] = isset( $this->data['optin_' . $meta_field . '_text-align'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_text-align'] ) : (( 'submit' == $field ) ? 'center' : 'left' );
		else
		    $this->meta[$field]['meta']['text_align'] = isset( $this->data['optin_' . $meta_field . '_text-align'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_text-align'] ) : $this->meta[$field]['meta']['text_align'];

	}

}