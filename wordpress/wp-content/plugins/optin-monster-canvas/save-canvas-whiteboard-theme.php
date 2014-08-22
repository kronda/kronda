<?php
/**
 * Saves the canvas optin "Whiteboard Theme".
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
class optin_monster_save_canvas_whiteboard_theme {

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

		global $optin_monster_account;

		// Set class properties.
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

		if ( isset( $this->data['optin_canvas_width'] ) ) {
    		$this->meta['dimensions']['width'] = absint( $this->data['optin_canvas_width'] );
		}

		if ( isset( $this->data['optin_canvas_height'] ) ) {
    		$this->meta['dimensions']['height'] = absint( $this->data['optin_canvas_height'] );
		}

        // If there is any custom CSS, save that now.
        if ( isset( $this->data['optin_custom_css'] ) )
            $this->meta['custom_css'] = trim( esc_html( $this->data['optin_custom_css'] ) );

        if ( isset( $this->data['optin_custom_canvas_html'] ) )
            $this->meta['custom_canvas_html'] = trim( esc_html( $this->data['optin_custom_canvas_html'] ) );

		// Finally update the post meta.
		update_post_meta( $this->optin, '_om_meta', $this->meta );

	}

	public function get_field( $field, $subfield = '' ) {

		if ( ! empty( $subfield ) )
			return isset( $this->meta[$field][$subfield] ) ? $this->meta[$field][$subfield] : '';
		else
			return isset( $this->meta[$field] ) ? $this->meta[$field] : '';

	}

}