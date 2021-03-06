<?php
/**
 * Builds out the ligthbox balance theme.
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
class optin_monster_build_lightbox_balance_theme {

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
	 * Builds out a lightbox optin using the "Balance Theme".
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
		html div#om-' . $this->hash . ' {
			background: rgb(0, 0, 0);
			background: rgba(0, 0, 0, .7);
			-webkit-font-smoothing: antialiased;
			line-height: 1;
			width: 100%;
			height: 100%;
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
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin {
			background: #fff;
			display: none;
			position: absolute;
			top: 50%;
			left: 50%;
			min-height: 175px;
			width: 700px;
			z-index: ' . ( 'customizer' == $this->env ? 1 : 734626274 ) . ';
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-wrap {
			position: relative;
			height: 100%;
		}
		html div#om-' . $this->hash . ' #om-close {
			color: #fff;
			position: absolute;
			top: -21px;
			right: 0;
			font-size: 21px;
			line-height: 21px;
			text-decoration: none !important;
			font-family: Arial, sans-serif;
		}
		html div#om-' . $this->hash . ' #om-close:hover {
			text-shadow: 0px 0px 2px #fff;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-header {
			min-height: 30px;
			padding: 15px;
			width: 100%;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title {
			font-size: 24px;
			color: #484848;
			width: 100%;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title span,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title strong,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title em,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title u {
			font-family: inherit;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-content {
			padding: 15px;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-content-clear {
			min-height: 175px;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-left {
			float: left;
			width: 415px;
			position: relative;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline {
			font-size: 16px;
			line-height: 1.25;
			color: #484848;
			width: 100%;
			margin-bottom: 20px;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline span,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline strong,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline em,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline u {
			font-family: inherit;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-bullet-list {
			padding: 0;
			margin: 0;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-bullet-list li {
			margin-left: 18px;
			line-height: 1.25;
			list-style-type: disc;
			position: relative;
			font-size: 16px;
			color: #484848;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-bullet-list li span,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-bullet-list li strong,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-bullet-list li em,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-bullet-list li u {
			font-family: inherit;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-right {
			float: right;
			width: 225px;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-image-container {
			position: relative;
			width: 225px;
			height: 175px;
			margin: 0 auto;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-image-container img {
			display: block;
			margin: 0 auto;
			text-align: center;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-footer {
			padding: 15px;
		}
		html div#om-' . $this->hash . ' label {
		    color: #333;
		}
		html div#om-' . $this->hash . ' input,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-name,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-email {
			background-color: #fff;
			display: inline;
			width: 230px;
			height: 36px;
			border: 1px solid #ddd;
			font-size: 16px;
			line-height: 24px;
			padding: 4px 6px;
			overflow: hidden;
			outline: none;
			margin: 0 10px 0 0;
			vertical-align: middle;
		}
		html div#om-' . $this->hash . ' .om-has-email #om-lightbox-' . $this->theme . '-optin-email {
			width: 470px;
		}
		html div#om-' . $this->hash . ' input[type=submit],
		html div#om-' . $this->hash . ' button,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-submit {
			background: #484848;
			display: inline;
			border: 1px solid #484848;
			width: 190px;
			color: #fff;
			font-size: 16px;
			padding: 4px 6px;
			line-height: 24px;
			text-align: center;
			vertical-align: middle;
			cursor: pointer;
			margin: 0;
		}
		html div#om-' . $this->hash . ' input[type=checkbox],
		html div#om-' . $this->hash . ' input[type=radio] {
		    -webkit-appearance: checkbox;
		    width: auto;
		    outline: invert none medium;
		    padding: 0;
		    margin: 0;
		}
		@media (max-width: 700px) {
		    html div#om-' . $this->hash . '[style] {
		        position: absolute !important;
		    }
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin[style] {
		        width: 100%;
		        position: relative;
		        top: 30px !important;
		    }
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-wrap {
		        padding: 10px;
		    }
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-left,
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-right,
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-name,
            html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-email,
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-submit,
		    html div#om-' . $this->hash . ' .om-has-email #om-lightbox-' . $this->theme . '-optin-email {
		        float: none;
		        width: 100%;
		    }
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-left {
		        margin-bottom: 15px;
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
		$theme .= '<div id="om-lightbox-' . $this->theme . '-optin" class="om-lightbox-' . $this->theme . ' om-clearfix ' . ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ? 'om-custom-html-form' : '' ) . '">';
			$theme .= '<div id="om-lightbox-' . $this->theme . '-optin-wrap" class="om-clearfix">';
				$theme .= '<a href="#" id="om-close" title="Close">x</a>';

				// Header area.
				$bg = $this->get_field( 'background', 'header', '#fff' );
				$theme .= '<div id="om-lightbox-' . $this->theme . '-header" class="om-clearfix" style="' . ( ! empty( $bg ) ? 'background-color: ' . $bg : '' ) . '">';
				    $title = $this->get_field( 'title' );
				    $style = '';
				    if ( ! empty( $title['color'] ) )
				        $style .= 'color:' . $title['color'] . ';';
				    if ( ! empty( $title['font'] ) )
				        $style .= 'font-family:\'' . $title['font'] . '\', sans-serif;';
				    if ( ! empty( $title['size'] ) )
				        $style .= 'font-size:' . $title['size'] . 'px;';
				    if ( ! empty( $title['meta'] ) )
				        foreach ( (array) $title['meta'] as $prop => $val )
				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
					$theme .= '<h1 id="om-lightbox-' . $this->theme . '-optin-title" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $title['text'] ) ? $title['text'] : '' ) . '</h1>';
				$theme .= '</div>';

				// Content area.
				$bg = $this->get_field( 'background', 'content', '#fff' );
				$theme .= '<div id="om-lightbox-' . $this->theme . '-content" class="om-clearfix" style="' . ( ! empty( $bg ) ? 'background-color: ' . $bg : '' ) . '">';
					$theme .= '<div id="om-lightbox-' . $this->theme . '-content-clear">';
						$theme .= '<div id="om-lightbox-' . $this->theme . '-left">';
						    $tagline = $this->get_field( 'tagline' );
        				    $style = '';
        				    if ( ! empty( $tagline['color'] ) )
        				        $style .= 'color:' . $tagline['color'] . ';';
        				    if ( ! empty( $tagline['font'] ) )
        				        $style .= 'font-family:\'' . $tagline['font'] . '\', sans-serif;';
        				    if ( ! empty( $tagline['size'] ) )
        				        $style .= 'font-size:' . $tagline['size'] . 'px;';
        				    if ( ! empty( $tagline['meta'] ) )
        				        foreach ( (array) $tagline['meta'] as $prop => $val )
        				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
							$theme .= '<h2 id="om-lightbox-' . $this->theme . '-optin-tagline" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $tagline['text'] ) ? $tagline['text'] : '' ) . '</h2>';

                            $theme .= '<div id="om-lightbox-' . $this->theme . '-optin-bullets">';
                                $theme .= '<ul id="om-lightbox-' . $this->theme . '-optin-bullet-list">';
        							$bullets = $this->get_field( 'bullet' );
        							if ( ! empty( $bullets['text'] ) ) :
        							    $style = '';
                    				    if ( ! empty( $bullets['color'] ) )
                    				        $style .= 'color:' . $bullets['color'] . ';';
                    				    if ( ! empty( $bullets['font'] ) )
                    				        $style .= 'font-family:\'' . $bullets['font'] . '\', sans-serif;';
                    				    if ( ! empty( $bullets['size'] ) )
                    				        $style .= 'font-size:' . $bullets['size'] . 'px;';
                    				    if ( ! empty( $bullets['meta'] ) ) {
        	        				        foreach ( (array) $bullets['meta'] as $prop => $val )
        	        				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';

        								    foreach ( $bullets['text'] as $i => $bullet ) {
        								    	if ( empty( $bullet ) ) continue;
            								    $theme .= '<li data-number="' . $i . '" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . $bullet . '</li>';
        								    }
                                        }
                                    endif;
                                $theme .= '</ul>';
                            $theme .= '</div>';
						$theme .= '</div>';
						$theme .= '<div id="om-lightbox-' . $this->theme . '-right">';
                            $theme .= '<div id="om-lightbox-' . $this->theme . '-optin-image-container">';
							     $url = get_the_post_thumbnail( $this->optin, 'full' );
							     $theme .= $url;
                            $theme .= '</div>';
						$theme .= '</div>';
					$theme .= '</div>';
				$theme .= '</div>';

				// Footer area.
                $bg = $this->get_field( 'background', 'footer', '#fff' );
                $class = $this->get_field( 'name', 'show' ) ? ' om-has-name-email' : ' om-has-email';
				$theme .= '<div id="om-lightbox-' . $this->theme . '-footer" class="om-clearfix' . $class . '" style="' . ( ! empty( $bg ) ? 'background-color: ' . $bg : '' ) . '">';
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
						    $theme .= '<input' . $disabled . ' id="om-lightbox-' . $this->theme . '-optin-name" type="text" value="" placeholder="' . ( ! empty( $name['placeholder'] ) ? $name['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
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
						$theme .= '<input' . $disabled . ' id="om-lightbox-' . $this->theme . '-optin-email" type="email" value="" placeholder="' . ( ! empty( $email['placeholder'] ) ? $email['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';

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
						$theme .= '<input' . $disabled . ' id="om-lightbox-' . $this->theme . '-optin-submit" type="submit" value="' . ( ! empty( $submit['placeholder'] ) ? $submit['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
                    endif;
				$theme .= '</div>';
			$theme .= '</div>';

			// If we have a powered by link, output it now.
    		if ( isset( $this->meta['powered_by'] ) && $this->meta['powered_by'] ) {
    		    global $optin_monster_account;
    		    $theme .= $optin_monster_account->get_powered_by_link();
    		}

		$theme .= '</div>';

		// Build out any necessary JS elements.
		$theme .= '<script type="text/javascript">';
			$theme .= 'function om_js_' . str_replace( '-', '_', $this->hash ) . '(){';
				$theme .= 'this.init = function($){this.resize_element($, "div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin");},';
				$theme .= 'this.resize_element = function($, el){';
					if ( 'customizer' == $this->env ) :
						$theme .= '$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).height()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).width()) / 2 });';
						$theme .= '$(window).resize(function(){$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).height()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).width()) / 2 });});';
						$theme .= '$(el).resize(function(){$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).height()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).width()) / 2 });});';
						$theme .= '$("div#om-' . $this->hash . ' input[data-om-render=label]").each(function(){var new_el = $(this).changeElementType(\'label\');});';
						$theme .= '$("div#om-' . $this->hash . ' label[data-om-render=label]").each(function(){var new_el = $(this).text($(this).attr(\'value\')).removeAttr(\'type\');});';
						$theme .= '$("div#om-' . $this->hash . '").find("input").each(function(){$(this).attr("disabled", "disabled");});';
						$theme .= '$("div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin").fadeIn(300);';
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

	public function get_field( $field, $subfield = '', $default = '' ) {

		if ( ! $this->optin ) {
			return '';
        }

		if ( ! empty( $subfield ) ){
			return isset( $this->meta[$field][$subfield] ) ? $this->meta[$field][$subfield] : $default;
        }
		else{
			return isset( $this->meta[$field] ) ? $this->meta[$field] : $default;
        }

	}

}