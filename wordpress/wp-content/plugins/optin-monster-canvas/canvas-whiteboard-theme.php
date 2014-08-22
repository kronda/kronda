<?php
/**
 * Builds out the canvas whiteboard theme.
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
class optin_monster_build_canvas_whiteboard_theme {

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
	 * Builds out a canvas optin using the "Balance Theme".
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
            outline:none;
            box-sizing:border-box;
            -webkit-box-sizing:border-box;
            -moz-box-sizing:border-box;
		}
		html div#om-' . $this->hash . ' {
			background: rgb(0, 0, 0);
			background: rgba(0, 0, 0, .7);
			width: 100%;
			height: 100%;
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
		html div#om-' . $this->hash . ' #om-canvas-' . $this->theme . '-optin {
			background: #fff;
			display: none;
			position: absolute;
			top: 50%;
			left: 50%;
			width: 700px;
			height: 350px;
			z-index: ' . ( 'customizer' == $this->env ? 1 : 734626274 ) . ';
		}
		html div#om-' . $this->hash . ' #om-canvas-' . $this->theme . '-optin-wrap {
			position: relative;
			height: 100%;
			padding: 20px;
		}
		html div#om-' . $this->hash . ' #om-close {
			position: absolute;
			top: -19px;
			right: -19px;
			text-decoration: none !important;
			display: block;
			width: 35px;
			height: 35px;
			background: transparent url(' . plugins_url( 'inc/css/images/case-study-close.png', $this->base->file ) . ') no-repeat scroll 0 0 !important;
			z-index: 321;
		}
		@media (max-width: 700px) {
		    html div#om-' . $this->hash . '[style] {
		        position: absolute !important;
		    }
		    html div#om-' . $this->hash . ' #om-canvas-' . $this->theme . '-optin[style] {
		        width: 100%;
		        position: relative;
		        top: 30px !important;
		    }
		    html div#om-' . $this->hash . ' #om-canvas-' . $this->theme . '-optin-wrap {
		        padding: 10px;
		    }
		}
		';

		// Minify CSS a bit.
		$theme .= str_replace( array( "\n", "\t", "\r" ), '', $css );

		// If there is any custom CSS, append it now.
		if ( ! empty( $this->meta['custom_css'] ) )
		    $theme .= str_replace( array( "\n", "\t", "\r" ), '', html_entity_decode( $this->meta['custom_css'], ENT_QUOTES ) );

        // Close out the styles.
        $theme .= '</style>';

		// Build out the HTML structure for the optin.
		$width  = $this->get_field( 'dimensions', 'width' );
        $height = $this->get_field( 'dimensions', 'height' );
		$theme .= '<div id="om-canvas-' . $this->theme . '-optin" class="om-canvas-' . $this->theme . ' om-clearfix" style="' . ( ! empty( $width ) ? 'width: ' . $width . 'px;' : '' ) . '' . ( ! empty( $height ) ? 'height: ' . $height . 'px;' : '' ) . '">';
			$theme .= '<div id="om-canvas-' . $this->theme . '-optin-wrap" class="om-clearfix">';
			    if ( ! empty( $this->meta['custom_canvas_html'] ) ) {
					$content = html_entity_decode( $this->meta['custom_canvas_html'], ENT_QUOTES );
					$content = str_replace( array( 'ajax="true"', 'ajax=true' ), '', $content );
					$content = do_shortcode( $content );
					$theme .= '<div id="om-canvas-' . $this->theme . '-optin-content"><div class="optin_custom_html_applied">' . $content . '</div></div>';
			    }
				$theme .= '<a href="#" id="om-close" title="Close"></a>';

			// If we have a powered by link, output it now.
    		if ( isset( $this->meta['powered_by'] ) && $this->meta['powered_by'] ) {
    		    global $optin_monster_account;
    		    $theme .= $optin_monster_account->get_powered_by_link();
    		}

    		$theme .= '</div>';
		$theme .= '</div>';

		// Build out any necessary JS elements.
		$theme .= '<script type="text/javascript">';
			$theme .= 'function om_js_' . str_replace( '-', '_', $this->hash ) . '(){';
				$theme .= 'this.init = function($){this.resize_element($, "div#om-' . $this->hash . ' #om-canvas-' . $this->theme . '-optin");},';
				$theme .= 'this.resize_element = function($, el){';
					if ( 'customizer' == $this->env ) :
						$theme .= '$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).height()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).width()) / 2 });';
						$theme .= '$(window).resize(function(){$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).height()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).width()) / 2 });});';
						$theme .= '$(el).resize(function(){$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).height()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).width()) / 2 });});';
						$theme .= '$("div#om-' . $this->hash . '").find("input").each(function(){$(this).attr("disabled", "disabled");});';
						$theme .= '$("div#om-' . $this->hash . ' #om-canvas-' . $this->theme . '-optin").fadeIn(300);';
					else :
			    		$theme .= '$(el).css({ top: ($(window).height() - $(el).height()) / 2, left: ($(window).width() - $(el).width()) / 2 });';
			    		$theme .= '$(window).resize(function(){$(el).css({ top: ($(window).height() - $(el).height()) / 2, left: ($(window).width() - $(el).width()) / 2 });});';
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

	public function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
           $r = hexdec(substr($hex,0,1).substr($hex,0,1));
           $g = hexdec(substr($hex,1,1).substr($hex,1,1));
           $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
           $r = hexdec(substr($hex,0,2));
           $g = hexdec(substr($hex,2,2));
           $b = hexdec(substr($hex,4,2));
        }
        $rgb = array($r, $g, $b);
        return implode(",", $rgb); // returns the rgb values separated by commas
    }

}