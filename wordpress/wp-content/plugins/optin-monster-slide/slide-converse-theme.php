<?php
/**
 * Builds out the slide converse theme.
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
class optin_monster_build_slide_converse_theme {

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
	 * Builds out a slide optin using the "converse Theme".
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
			background: transparent;
			-webkit-font-smoothing: antialiased;
			line-height: 1;
			width: 300px;
			position: fixed;
			right: 20px;
			bottom: 0;
			z-index: ' . ( 'customizer' == $this->env ? 1 : 734626274 ) . ';
			min-height: 20px;
			height: auto;
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
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin {
			background: #000;
			display: none;
			position: relative;
			min-height: 20px;
		}
		html div#om-' . $this->hash . ' .om-slide-open-holder,
		html div#om-' . $this->hash . ' .om-slide-close-holder {
			color: #fff;
			display: block;
			font-family: Arial, sans-serif;
			position relative;
			padding: 10px;
		}
		html div#om-' . $this->hash . ' .om-slide-close-holder {
			padding: 10px 0 0;
		}
		html div#om-' . $this->hash . ' .om-slide-open-holder span,
		html div#om-' . $this->hash . ' .om-slide-close-holder span {
			color: #fff;
			display: inline;
			float: right;
			font-size: 21px;
			line-height: 14px;
			font-weight: 100;
			text-decoration: none !important;
			font-family: Arial, sans-serif !important;
			margin: 0 0 0 10px;
			vertical-align: middle;
		}
		html div#om-' . $this->hash . ' .om-slide-close-holder span {
			font-size: 16px;
			font-weight: bold;
		}
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-closed,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-open,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-tagline {
			display: block;
			font-size: 14px;
			color: #fff;
			width: 100%;
		}
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-closed span,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-closed strong,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-closed em,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-closed u,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-open span,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-open strong,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-open em,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-title-open u,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-tagline span,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-tagline strong,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-tagline em,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-tagline u {
			font-family: inherit;
		}
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-tagline {
			line-height: 1.2;
		}
		html div#om-' . $this->hash . ' .om-slide-open .om-slide-open-holder,
		html div#om-' . $this->hash . ' .om-slide-closed #om-slide-' . $this->theme . '-header,
		html div#om-' . $this->hash . ' .om-slide-closed #om-slide-' . $this->theme . '-content,
		html div#om-' . $this->hash . ' .om-slide-closed #om-slide-' . $this->theme . '-footer {
			display: none;
		}
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-header,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-content,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-footer {
			padding: 0 10px 10px;
		}
		html div#om-' . $this->hash . ' input,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-name,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-email {
			background-color: #fff;
			display: block;
			border: 1px solid #fff;
			font-size: 14px;
			line-height: 24px;
			padding: 4px 6px;
			overflow: hidden;
			outline: none;
			margin: 0 0 10px;
			vertical-align: middle;
			width: 280px;
		}
		html div#om-' . $this->hash . ' input[type=submit],
		html div#om-' . $this->hash . ' button,
		html div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin-submit {
			background: #ff370f;
			border: 1px solid #ff370f;
			width: 280px;
			color: #fff;
			font-size: 14px;
			padding: 4px 6px;
			line-height: 24px;
			height: 34px;
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
		';

		// Minify CSS a bit.
		$theme .= str_replace( array( "\n", "\t", "\r" ), '', $css );

		// If there is any custom CSS, append it now.
		if ( ! empty( $this->meta['custom_css'] ) )
		    $theme .= str_replace( array( "\n", "\t", "\r" ), '', $this->meta['custom_css'] );

        // Close out the styles.
        $theme .= '</style>';

		// Build out the HTML structure for the optin.
		$bg = $this->get_field( 'background', 'header' );
		$theme .= '<div id="om-slide-' . $this->theme . '-optin" class="om-slide-' . $this->theme . ' om-clearfix om-slide-closed ' . ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ? 'om-custom-html-form' : '' ) . '">';
			$theme .= '<div id="om-slide-' . $this->theme . '-optin-wrap" class="om-clearfix">';
				$theme .= '<a href="#" class="om-slide-open-holder" title="Open" style="' . ( ! empty( $bg ) ? 'background-color: ' . $bg : '' ) . '">';

				$title = $this->get_field( 'title_closed' );
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
				$theme .= '<h1 id="om-slide-' . $this->theme . '-optin-title-closed" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $title['text'] ) ? $title['text'] : '' ) . '<span class="om-slide-open-content">&#43;</span></h1>';
				$theme .= '</a>';

				// Header area.
				$theme .= '<div id="om-slide-' . $this->theme . '-header" class="om-clearfix" style="' . ( ! empty( $bg ) ? 'background-color: ' . $bg : '' ) . '">';
					$theme .= '<a href="#" class="om-slide-close-holder" title="Close">';
				    $title = $this->get_field( 'title_open' );
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
					$theme .= '<h2 id="om-slide-' . $this->theme . '-optin-title-open" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $title['text'] ) ? $title['text'] : '' ) . '<span class="om-slide-close-content">&#120;</span></h2>';
					$theme .= '</a>';
				$theme .= '</div>';

				// Content area.
				$content_bg = $this->get_field( 'background', 'content' );
				$theme .= '<div id="om-slide-' . $this->theme . '-content" class="om-clearfix" style="' . ( ! empty( $content_bg ) ? 'background-color: ' . $content_bg . ';' : '' ) . ( ! empty( $bg ) && ! empty( $content_bg ) && ($bg !== $content_bg) ? 'padding-top:10px;' : '' ) . '">';
					$theme .= '<div id="om-slide-' . $this->theme . '-content-clear">';
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
							$theme .= '<h2 id="om-slide-' . $this->theme . '-optin-tagline" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $tagline['text'] ) ? $tagline['text'] : '' ) . '</h2>';
					$theme .= '</div>';
				$theme .= '</div>';

				// slide area.
                $class = $this->get_field( 'name', 'show' ) ? ' om-has-name-email' : ' om-has-email';
				$theme .= '<div id="om-slide-' . $this->theme . '-footer" class="om-clearfix' . $class . '" style="' . ( ! empty( $content_bg ) ? 'background-color: ' . $content_bg : '' ) . '">';
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
        				    if ( ! empty( $name['size'] ) )
        				        $style .= 'font-size:' . $name['size'] . 'px;';
        				    if ( ! empty( $name['meta'] ) )
        				        foreach ( (array) $name['meta'] as $prop => $val )
        				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
    					    $theme .= '<input' . $disabled . ' id="om-slide-' . $this->theme . '-optin-name" type="text" value="" placeholder="' . ( ! empty( $name['placeholder'] ) ? $name['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
    					}

    				    $email = $this->get_field( 'email' );
    				    $style = '';
    				    if ( ! empty( $email['color'] ) )
    				        $style .= 'color:' . $email['color'] . ';';
    				    if ( ! empty( $email['font'] ) )
    				        $style .= 'font-family:\'' . $email['font'] . '\', sans-serif;';
    				    if ( ! empty( $email['size'] ) )
    				        $style .= 'font-size:' . $email['size'] . 'px;';
    				    if ( ! empty( $email['meta'] ) )
    				        foreach ( (array) $email['meta'] as $prop => $val )
    				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
    					$theme .= '<input' . $disabled . ' id="om-slide-' . $this->theme . '-optin-email" type="email" value="" placeholder="' . ( ! empty( $email['placeholder'] ) ? $email['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';

                        $submit = $this->get_field( 'submit' );
    				    $style = '';
    				    if ( ! empty( $submit['field_color'] ) )
    				        $style .= 'color:' . $submit['field_color'] . ';';
    				    if ( ! empty( $submit['bg_color'] ) )
    				        $style .= 'background-color:' . $submit['bg_color'] . ';border-color:' . $submit['bg_color'] . ';';
    				    if ( ! empty( $submit['font'] ) )
    				        $style .= 'font-family:\'' . $submit['font'] . '\', sans-serif;';
    				    if ( ! empty( $submit['size'] ) )
    				        $style .= 'font-size:' . $submit['size'] . 'px;';
    				    if ( ! empty( $submit['meta'] ) )
    				        foreach ( (array) $submit['meta'] as $prop => $val )
    				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
    					$theme .= '<input' . $disabled . ' id="om-slide-' . $this->theme . '-optin-submit" type="submit" value="' . ( ! empty( $submit['placeholder'] ) ? $submit['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
    				endif;
				$theme .= '</div>';
			$theme .= '</div>';
		$theme .= '</div>';

		// Build out any necessary JS elements.
		$theme .= '<script type="text/javascript">';
			$theme .= 'function om_js_' . str_replace( '-', '_', $this->hash ) . '(){';
				$theme .= 'this.init = function($){this.resize_element($, "div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin");},';
				$theme .= 'this.resize_element = function($, el){';
					if ( 'customizer' == $this->env ) :
						$theme .= '$("#om-slide-' . $this->theme . '-optin-title-open").resize(function(){$("#om-slide-' . $this->theme . '-optin .om-slide-close-holder span").css({ "line-height": $("#om-slide-' . $this->theme . '-optin-title-open").outerHeight() + "px" });});';
						$theme .= '$("#om-slide-' . $this->theme . '-optin-title-closed").resize(function(){$("#om-slide-' . $this->theme . '-optin .om-slide-open-holder span").css({ "line-height": $("#om-slide-' . $this->theme . '-optin-title-closed").outerHeight() + "px" });});';
						$theme .= '$("div#om-' . $this->hash . ' input[data-om-render=label]").each(function(){var new_el = $(this).changeElementType(\'label\');});';
						$theme .= '$("div#om-' . $this->hash . ' label[data-om-render=label]").each(function(){var new_el = $(this).text($(this).attr(\'value\')).removeAttr(\'type\');});';
						$theme .= '$("div#om-' . $this->hash . '").find("input").each(function(){$(this).attr("disabled", "disabled");});';
						$theme .= '$("div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin").fadeIn(300);';
						$theme .= '$("div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin .om-slide-open-holder").on("click", function(e){e.preventDefault();$("div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin").removeClass("om-slide-closed").addClass("om-slide-open");});';
						$theme .= '$("div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin .om-slide-close-holder").on("click", function(e){e.preventDefault();$("div#om-' . $this->hash . ' #om-slide-' . $this->theme . '-optin").removeClass("om-slide-open").addClass("om-slide-closed");});';
					else :
						$theme .= '$("#om-slide-' . $this->theme . '-optin .om-slide-open-holder span").css({ "line-height": $("#om-slide-' . $this->theme . '-optin-title-closed").outerHeight() + "px" });';
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

}