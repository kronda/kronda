<?php
/**
 * Builds out the post fabric theme.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Base non-logged in class that kick starts the theme.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_build_post_fabric_theme {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $type, $theme, $hash, $optin, $meta, $env, $ssl;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $type, $theme, $hash, $optin, $env = '', $ssl = false, $base ) {

		// Set class properties.
		$this->type    = $type;
		$this->theme   = $theme;
		$this->hash    = $hash;
		$this->optin   = $optin;
		$this->meta    = get_post_meta( $this->optin, '_om_meta', true );
		$this->env     = $env;
		$this->ssl     = $ssl;
		$this->base    = $base;

	}

	/**
	 * Builds out a post optin using the "sleek Theme".
	 *
	 * @since 1.0.0
	 */
	public function build() {

		// Build out the CSS styling for the optin.
		$theme = '<style type="text/css">';
		$css = '
		html div#om-' . $this->hash . ',
		html div#om-' . $this->hash . ' * {
			background:none;
			border:0;
			border-radius:0;
			-webkit-border-radius:0;
			-moz-border-radius:0;
			float:none;
			font:normal 100%/normal helvetica,arial,sans-serif;
			-webkit-font-smoothing:antialiased;
			height:auto;
			letter-spacing:normal;
			outline:none;
			position:static;
			text-decoration:none;
			text-indent:0;
			text-shadow:none;
			text-transform:none;
			width:auto;
			visibility:visible;
			overflow:visible;
			margin:0;
			padding:0;
			line-height:1;
			box-sizing:border-box;
			-webkit-box-sizing:border-box;
			-moz-box-sizing:border-box;
			-webkit-box-shadow:none;
			-moz-box-shadow:none;
			-ms-box-shadow:none;
			-o-box-shadow:none;
			box-shadow:none;
			-webkit-appearance:none;
		}
		html div#om-' . $this->hash . ' strong {
			font-weight: bold;
		}
		html div#om-' . $this->hash . ' em {
			font-style: italic;
		}
		html div#om-' . $this->hash . ' u {
			text-decoration: underline;
		}
		html div#om-' . $this->hash . ' .om-clearfix {
			clear: both;
		}
		html div#om-' . $this->hash . ' .om-clearfix:after {
			clear: both;
			content: ".";
			display: block;
			height: 0;
			line-height: 0;
			overflow: auto;
			visibility: hidden;
			zoom: 1;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin {
			background: url(' . plugins_url( 'images/background.png', __FILE__ ) . ');
			background-color: #fbfdf3;
			border: 1px solid #dcdcd9;
			-webkit-box-shadow: 0px 6px 11px -7px #000000;
			-moz-box-shadow: 0px 6px 11px -7px #000000;
			box-shadow: 0px 6px 11px -7px #000000;
			position: relative;
			text-align: center;
			margin: 0 auto;
			max-width: 100%;
			width: 100%;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-title {
			font-size: 26px;
			color: #fff;
			background-color: #f16a10;
			-webkit-text-shadow: #888 -0 0 1px;
			-moz-text-shadow: #888 -0 0 1px;
			text-shadow: #888 -0 0 1px;
			width: 100%;
			padding: 25px 40px;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-title span,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-title strong,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-title em,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-title u {
			font-family: inherit;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-content-clear {
			float: left;
			display: table;
			vertical-align: middle;
			width: 100%;
		}
        html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-content {
            display: table-cell;
			font-size: 16px;
			line-height: 24px;
			color: #858585;
			width: 100%;
			margin-bottom: 15px;
			padding-left: 0;
			vertical-align: middle;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-content span,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-content strong,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-content em,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-content u {
			font-family: inherit;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-left {
			display: table-cell;
			vertical-align: middle;
			width: 50%;
			padding: 50px 30px;
			position: relative;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-left.om-has-image #om-post-' . $this->theme . '-optin-content {
			width: 75%;
			padding-left: 15px;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-image-container {
			display: table-cell;
			vertical-align: middle;
			position: relative;
			width: 0%;
			height: auto;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-left.om-has-image #om-post-' . $this->theme . '-optin-image-container {
			width: 25%;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-image-container img {
			display: block;
			margin: 0 auto;
			text-align: center;
		}
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-right {
			display: table-cell;
			vertical-align: middle;
			width: 50%;
			padding: 50px 30px 50px 0;
			position: relative;
		}
		html div#om-' . $this->hash . ' input,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-name,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-email {
			background-color: #fff;
			width: 100%;
			height: 45px;
			border: 1px solid #c7c9bd;
			border-radius: 3px;
			font-size: 16px;
			line-height: 24px;
			padding: 6px;
			overflow: hidden;
			outline: none;
			margin: 0 0 10px;
			vertical-align: middle;
			display: inline;
			color: #222;
		}
		html div#om-' . $this->hash . ' input[type=submit],
		html div#om-' . $this->hash . ' button,
		html div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin-submit {
			background: #f16a10;
			border: 1px solid #cc611b;
			-webkit-box-shadow: 0 1px 1px -1px #fff inset;
			-moz-box-shadow: 0 1px 1px -1px #fff inset;
			box-shadow: 0 1px 1px -1px #fff inset;
			-webkit-text-shadow: #888 -0 0 1px;
			-moz-text-shadow: #888 -0 0 1px;
			text-shadow: #888 -0 0 1px;
			color: #fff;
			font-size: 16px;
			padding: 6px;
			line-height: 24px;
			text-align: center;
			vertical-align: middle;
			cursor: pointer;
			display: inline;
			margin: 0;
			margin-top: 10px;
			width: 100%;
		}
		html div#om-' . $this->hash . ' input[type=checkbox],
		html div#om-' . $this->hash . ' input[type=radio] {
		    -webkit-appearance: checkbox;
		    width: auto;
		    outline: invert none medium;
		    padding: 0;
		    margin: 0;
		}
		';

		// Minify CSS a bit.
		$theme .= str_replace( array( "\n", "\t", "\r" ), '', $css );

		// If there is any custom CSS, append it now.
		if ( ! empty( $this->meta['custom_css'] ) )
		    $theme .= str_replace( array( "\n", "\t", "\r" ), '', $this->meta['custom_css'] );

        // Close out the styles.
        $theme .= '</style>';

		// Build out the HTML structure for the optin.
		$class = $this->get_field( 'name', 'show' ) ? 'om-has-name-email' : 'om-has-email';
		$body_bg_color = $this->get_field( 'background', 'content' );
		$theme .= '<div id="om-' . $this->hash . '"><div id="om-post-' . $this->theme . '-optin" class="om-post-' . $this->theme . ' om-clearfix ' . $class . ' ' . ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ? 'om-custom-html-form' : '' ) . '" style="' .  ( ! empty( $body_bg_color ) ? 'background-color: ' . $body_bg_color : '' ) . '">';
		    $title = $this->get_field( 'title' );
			$header_bg_color = $this->get_field( 'background', 'header' );
		    $style = '';
		    if ( ! empty( $title['color'] ) )
		        $style .= 'color:' . $title['color'] . ';';
			if ( ! empty( $header_bg_color ) )
				$style .= 'background-color:' . $header_bg_color . ';';
		    if ( ! empty( $title['font'] ) )
		        $style .= 'font-family:\'' . $title['font'] . '\', sans-serif;';
		    if ( ! empty( $title['size'] ) )
		        $style .= 'font-size:' . $title['size'] . 'px;';
		    if ( ! empty( $title['meta'] ) )
		        foreach ( (array) $title['meta'] as $prop => $val )
		            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
			$theme .= '<div id="om-post-' . $this->theme . '-optin-title-container">';
			$theme .= '<h2 id="om-post-' . $this->theme . '-optin-title" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $title['text'] ) ? $title['text'] : '' ) . '</h2>';
			$theme .= '</div>';

			$theme .= '<div id="om-post-' . $this->theme . '-content" class="om-clearfix">';
				$theme .= '<div id="om-post-' . $this->theme . '-content-clear">';
					$image = get_the_post_thumbnail( $this->optin, 'full' ) ? ' om-has-image' : '';
					$theme .= '<div id="om-post-' . $this->theme . '-left" class="' . $image . '">';
						$content = $this->get_field( 'content' );
						$style = '';
						if ( ! empty( $content['color'] ) )
							$style .= 'color:' . $content['color'] . ';';
						if ( ! empty( $content['font'] ) )
							$style .= 'font-family:\'' . $content['font'] . '\', sans-serif;';
						if ( ! empty( $content['size'] ) )
							$style .= 'font-size:' . $content['size'] . 'px;';
						if ( ! empty( $content['meta'] ) )
							foreach ( (array) $content['meta'] as $prop => $val )
								$style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
						$theme .= '<div id="om-post-' . $this->theme . '-optin-image-container">';
							$url = get_the_post_thumbnail( $this->optin, 'full' );
							$theme .= $url;
						$theme .= '</div>';
						$theme .= '<h2 id="om-post-' . $this->theme . '-optin-content" class="' . $image . '" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $content['text'] ) ? $content['text'] : '' ) . '</h2>';
					$theme .= '</div>';
					$theme .= '<div id="om-post-' . $this->theme . '-right">';
						if ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ) :
							$theme .= html_entity_decode( $this->meta['custom_html'] );
						else :
							$disabled = 'customizer' == $this->env ? ' disabled="disabled"' : '';
							$show_name = $this->get_field( 'name', 'show' );
							if ( $show_name ) {
								$name = $this->get_field( 'name' );
								$style = '';
								if ( ! empty( $name['color'] ) )
									$style .= 'color:' . $name['color'] . ';';
								if ( ! empty( $name['font'] ) )
									$style .= 'font-family:\'' . $name['font'] . '\', sans-serif;';
								if ( ! empty( $name['meta'] ) )
									foreach ( (array) $name['meta'] as $prop => $val )
										$style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
								$theme .= '<input' . $disabled . ' id="om-post-' . $this->theme . '-optin-name" type="text" value="" placeholder="' . ( ! empty( $name['placeholder'] ) ? $name['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
							}

							$email = $this->get_field( 'email' );
							$style = '';
							if ( ! empty( $email['color'] ) )
								$style .= 'color:' . $email['color'] . ';';
							if ( ! empty( $email['font'] ) )
								$style .= 'font-family:\'' . $email['font'] . '\', sans-serif;';
							if ( ! empty( $email['meta'] ) )
								foreach ( (array) $email['meta'] as $prop => $val )
									$style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
							$theme .= '<input' . $disabled . ' id="om-post-' . $this->theme . '-optin-email" type="email" value="" placeholder="' . ( ! empty( $email['placeholder'] ) ? $email['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';

							$submit = $this->get_field( 'submit' );
							$style = '';
							if ( ! empty( $submit['field_color'] ) )
								$style .= 'color:' . $submit['field_color'] . ';';
							if ( ! empty( $submit['bg_color'] ) )
								$style .= 'background-color:' . $submit['bg_color'] . ';border-color:' . $submit['bg_color'] . ';';
							if ( ! empty( $submit['font'] ) )
								$style .= 'font-family:\'' . $submit['font'] . '\', sans-serif;';
							if ( ! empty( $submit['meta'] ) )
								foreach ( (array) $submit['meta'] as $prop => $val )
									$style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
							$theme .= '<input' . $disabled . ' id="om-post-' . $this->theme . '-optin-submit" type="submit" value="' . ( ! empty( $submit['placeholder'] ) ? $submit['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
						endif;
					$theme .= '</div>';
				$theme .= '</div>';
			$theme .= '</div>';

		$theme .= '</div></div>';

		// Build out any necessary JS elements.
		$theme .= '<script type="text/javascript">';
			$theme .= 'function om_js_' . str_replace( '-', '_', $this->hash ) . '(){';
				$theme .= 'this.init = function($){this.resize_element($, "div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin");},';
				$theme .= 'this.resize_element = function($, el){';
					if ( 'customizer' == $this->env ) :
					    $theme .= '$("#om-post-' . $this->theme . '-optin").css({ "margin-top":"50px"});';
						$theme .= '$("div#om-' . $this->hash . ' input[data-om-render=label]").each(function(){var new_el = $(this).changeElementType(\'label\');});';
						$theme .= '$("div#om-' . $this->hash . ' label[data-om-render=label]").each(function(){var new_el = $(this).text($(this).attr(\'value\')).removeAttr(\'type\');});';
						$theme .= '$("div#om-' . $this->hash . '").find("input").each(function(){$(this).attr("disabled", "disabled");});';
						$theme .= '$("div#om-' . $this->hash . ' #om-post-' . $this->theme . '-optin").fadeIn(300);';
			    	endif;
				$theme .= '}';
			$theme .= '}';
		$theme .= '</script>';

		// Return the theme output and design.
		return $theme;

	}

	public function get_field( $field, $subfield = '' ) {

		if ( ! empty( $subfield ) )
			return isset( $this->meta[$field][$subfield] ) ? $this->meta[$field][$subfield] : '';
		else
			return isset( $this->meta[$field] ) ? $this->meta[$field] : '';

	}

	public function hex2rgb( $hex ) {
	    $hex = str_replace("#", "", $hex);

	    if ( strlen( $hex ) == 3 ) {
	       $r = hexdec(substr($hex,0,1).substr($hex,0,1));
			   $g = hexdec(substr($hex,1,1).substr($hex,1,1));
	       $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	    } else {
	       $r = hexdec(substr($hex,0,2));
	       $g = hexdec(substr($hex,2,2));
	       $b = hexdec(substr($hex,4,2));
	    }
	    $rgb = array($r, $g, $b);

	    return implode( ',', $rgb ); // returns an array with the rgb values
	}

}