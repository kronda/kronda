<?php
/**
 * Converse theme class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Slide_Theme_Converse extends Optin_Monster_Theme {

    /**
     * Path to the file.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Slug of the theme.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $theme = 'converse';

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     *
     * @param int $optin_id The optin ID to target.
     */
    public function __construct( $optin_id ) {

        // Construct via the parent object.
        parent::__construct( $optin_id );

        // Set the optin type.
        $this->type = $this->meta['type'];

    }
    /**
     * Retrieval method for getting the styles for a theme.
     *
     * @since 2.0.0
     */
    public function get_styles() {

        $css = '
        html div#om-' . $this->optin->post_name . ' * {
            box-sizing:border-box;
            -webkit-box-sizing:border-box;
            -moz-box-sizing:border-box;
        }
        html div#om-' . $this->optin->post_name . ' {
            background:none;
            border:0;
            border-radius:0;
            -webkit-border-radius:0;
            -moz-border-radius:0;
            float:none;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
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
        html div#om-' . $this->optin->post_name . ' {
            background: transparent;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1;
            max-width: 300px;
            width: 100%;
            position: fixed;
            right: 20px;
			bottom: 0;
			min-height: 20px;
			height: auto;
			z-index: 72652617263;
			color: #222;
        }
        html div#om-' . $this->optin->post_name . ' .om-clearfix {
            clear: both;
        }
        html div#om-' . $this->optin->post_name . ' .om-clearfix:after {
            clear: both;
            content: ".";
            display: block;
            height: 0;
            line-height: 0;
            overflow: auto;
            visibility: hidden;
            zoom: 1;
        }
        html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin {
			background: #000;
			display: none;
			position: relative;
			min-height: 20px;
		}
		html div#om-' . $this->optin->post_name . ' .om-slide-open-holder,
		html div#om-' . $this->optin->post_name . ' .om-slide-close-holder {
			color: #fff;
			display: block;
			position relative;
			padding: 10px;
		}
		html div#om-' . $this->optin->post_name . ' .om-slide-open-holder {
		    position: relative;
		}
		html div#om-' . $this->optin->post_name . ' .om-slide-close-holder {
			padding: 10px 0 0;
			position: relative;
		}
		html div#om-' . $this->optin->post_name . ' .om-slide-open-holder .om-slide-open-content,
		html div#om-' . $this->optin->post_name . ' .om-slide-close-holder .om-slide-close-content {
			color: #fff;
			display: block;
			float: right;
			font-size: 20px;
			line-height: 14px;
			font-weight: bold;
			text-decoration: none !important;
			font-family: Helvetica, Arial, sans-serif !important;
			vertical-align: middle;
			cursor: pointer;
			position: absolute;
			margin: auto;
			top: 9px;
			right: 10px;
		}
		html div#om-' . $this->optin->post_name . ' .om-slide-close-holder .om-slide-close-content {
		    top: 10px;
			right: -14px;
		}
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-title-closed,
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-title-open,
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-tagline {
			display: block;
			font-size: 14px;
			color: #fff;
			width: 100%;
		}
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-title-closed:before,
        html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-title-closed:after,
        html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-title-open:before,
        html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-title-open:after {
            display: inline;
        }
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-tagline {
			line-height: 1.2;
		}
		html div#om-' . $this->optin->post_name . ' .om-slide-open .om-slide-open-holder,
		html div#om-' . $this->optin->post_name . ' .om-slide-closed #om-slide-' . $this->theme . '-header,
		html div#om-' . $this->optin->post_name . ' .om-slide-closed #om-slide-' . $this->theme . '-content,
		html div#om-' . $this->optin->post_name . ' .om-slide-closed #om-slide-' . $this->theme . '-footer {
			display: none;
		}
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-header,
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-content,
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-footer {
			padding: 0 10px 10px;
		}
		html div#om-' . $this->optin->post_name . ' .om-slide-open-holder,
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-header {
		    padding-right: 22px;
		}
		html div#om-' . $this->optin->post_name . ' input,
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-name,
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-email {
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
			max-width: 280px;
			width: 100%;
			height: 34px;
		}
		html div#om-' . $this->optin->post_name . ' input[type=submit],
		html div#om-' . $this->optin->post_name . ' button,
		html div#om-' . $this->optin->post_name . ' #om-slide-' . $this->theme . '-optin-submit {
			background: #ff370f;
			border: 1px solid #ff370f;
			max-width: 280px;
			width: 100%;
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
		html div#om-' . $this->optin->post_name . ' input[type=checkbox],
		html div#om-' . $this->optin->post_name . ' input[type=radio] {
		    -webkit-appearance: checkbox;
		    width: auto;
		    outline: invert none medium;
		    padding: 0;
		    margin: 0;
		}
        ';

        return $css;

    }

    /**
     * Retrieval method for getting the HTML output for a theme.
     *
     * @since 2.0.0
     */
    public function get_html() {

        $provider = $this->get_email_setting( 'provider', '', false );

        $html = '<div id="om-' . $this->type . '-' . $this->theme . '-optin" class="om-' . $this->type . '-' . $this->theme . ' om-clearfix om-slide-closed om-theme-' . $this->theme . ' ' . ( $provider && 'custom' == $provider ? 'om-custom-html-form' : '' ) . '">';
            $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-wrap" class="om-clearfix">';

                // Closed title state.
                $html .= '<div class="om-slide-open-holder om-close" style="background-color:' . $this->get_background_setting( 'header', '#000' ) . '">';
                    $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-title-closed" data-om-action="editable" data-om-field="title_closed" style="' . $this->get_element_style( 'title_closed', array( 'font' => 'Open Sans', 'meta' => array( 'font-weight' => 'bold' ) ) ) . '">' . $this->get_setting( 'title_closed', 'text', __( 'Learn more about OptinMonster!', 'optin-monster-slide' ) ) . '</div>';
                    $html .= '<span class="om-slide-open-content" contenteditable="false">&#43;</span>';
                $html .= '</div>';

                // Open title state.
                $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-header" class="om-clearfix" style="background-color:' . $this->get_background_setting( 'header', '#000' ) . '">';
					$html .= '<span class="om-slide-close-holder">';
					    $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-title-open" data-om-action="editable" data-om-field="title_open" style="' . $this->get_element_style( 'title_open', array( 'font' => 'Open Sans', 'meta' => array( 'font-weight' => 'bold' ) ) ) . '">' . $this->get_setting( 'title_open', 'text', __( 'OptinMonster explodes your list!', 'optin-monster-slide' ) ) . '</div>';
					    $html .= '<span class="om-slide-close-content" contenteditable="false">&times;</span>';
					$html .= '</span>';
				$html .= '</div>';

                // Content area.
				$html .= '<div id="om-' . $this->type . '-' . $this->theme . '-content" class="om-clearfix" style="background-color:' . $this->get_background_setting( 'content', '#000' ) . ( $this->get_background_setting( 'header', '#000' ) !== $this->get_background_setting( 'content', '#000' ) ? ';padding-top:10px;' : '' ) . '">';
					$html .= '<div id="om-' . $this->type . '-' . $this->theme . '-content-clear">';
                        $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-tagline" data-om-action="editable" data-om-field="tagline" style="' . $this->get_element_style( 'tagline', array( 'font' => 'Open Sans' ) ) . '">' . $this->get_tagline_setting( 'text', __( 'OptinMonster works by targeting users with our powerful exit intent technology, giving you a higher rate of conversion that allow you to grow your business.', 'optin-monster-slide' ) ) . '</div>';
					$html .= '</div>';
				$html .= '</div>';

                // Footer area.
                $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-footer" class="om-clearfix" style="background-color: ' . $this->get_background_setting( 'content', '#000' ) . '">';
                    $show_name  = $this->get_name_setting( 'show' );
                    $class_name = $show_name ? 'om-has-name-email' : 'om-has-email';

                    // Show either the custom HTML form entered by the user or the custom fields in the Fields pane.
                    if ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ) {
                        $html .= do_shortcode( html_entity_decode( $this->meta['custom_html'] ) );
                    } else {
                        // Possibly show the name field.
                        if ( $show_name ) {
                            $html .= '<input id="om-' . $this->type . '-' . $this->theme . '-optin-name" type="text" value="" data-om-action="selectable" data-om-target="#optin-monster-field-name_field" placeholder="' . $this->get_name_setting( 'placeholder', __( 'Enter your name here...', 'optin-monster-slide' ) ) . '" style="' . $this->get_element_style( 'name', array( 'font' => 'Open Sans' ) ) . '" />';
                        }

                        $html .= '<input id="om-' . $this->type . '-' . $this->theme . '-optin-email" type="email" value="" data-om-action="selectable" data-om-target="#optin-monster-field-email_field" placeholder="' . $this->get_email_setting( 'placeholder', __( 'Enter your email address here...', 'optin-monster-slide' ) ) . '" style="' . $this->get_element_style( 'email', array( 'font' => 'Open Sans' ) ) . '" />';
                        $html .= '<input id="om-' . $this->type . '-' . $this->theme . '-optin-submit" type="submit" data-om-action="selectable" data-om-target="#optin-monster-field-submit_field" value="' . $this->get_submit_setting( 'placeholder', __( 'Sign Up', 'optin-monster-slide' ) ) . '" style="' . $this->get_element_style( 'submit', array( 'font' => 'Open Sans' ) ) . '" />';
                    }
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;

    }

    /**
     * Retrieval method for getting any custom JS for a theme.
     *
     * @since 2.0.0
     */
    public function get_js() {
        // TODO: Implement get_js() method.
    }

    /**
     * Method for housing filters to allow for design and field controls.
     *
     * @since 2.0.0
     */
    public function controls() {

        add_filter( 'optin_monster_panel_design_fields', array( $this, 'design_fields' ), 10, 2 );
        add_filter( 'optin_monster_panel_input_fields', array( $this, 'input_fields' ), 10, 2 );
        add_filter( 'optin_monster_save_optin', array( $this, 'save_controls' ), 10, 4 );

    }

    /**
     * Outputs the design controls for the theme.
     *
     * @since 2.0.0
     *
     * @param array $fields    Array of design fields.
     * @param object $instance The Edit UI instance.
     * @return array $fields   Amended array of design fields.
     */
    public function design_fields( $fields, $instance ) {

        $fields['header_bg'] = $instance->get_color_field(
            'header_bg',
            $instance->get_background_setting( 'header', '#000' ),
            __( 'Header Background', 'optin-monster-slide' ),
            __( 'The background color of the optin header.', 'optin-monster-slide' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-header, .om-slide-open-holder',
                'props'  => 'background-color'
            )
        );
        $fields['content_bg'] = $instance->get_color_field(
            'content_bg',
            $instance->get_background_setting( 'content', '#000' ),
            __( 'Content Background', 'optin-monster-slide' ),
            __( 'The background color of the optin content area.', 'optin-monster-slide' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-content, #om-' . $this->type . '-' . $this->theme . '-footer',
                'props'  => 'background-color'
            )
        );

        return $fields;

    }

    /**
     * Outputs the fields controls for the theme.
     *
     * @since 2.0.0
     *
     * @param array $fields    Array of input fields.
     * @param object $instance The Edit UI instance.
     * @return array $fields   Amended array of input fields.
     */
    public function input_fields( $fields, $instance ) {

        // Build the name field.
        $fields['name_header'] = $instance->get_field_header(
            __( 'Name Field', 'optin-monster-slide' ),
            'name',
            true
        );
        $fields['name_show']   = $instance->get_checkbox_field(
            'name_show',
            $instance->get_name_setting( 'show' ),
            __( 'Show optin name field?', 'optin-monster-slide' ),
            __( 'Displays or hides the name field in the optin.', 'optin-monster-slide' ),
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-footer',
                'input'  => esc_attr( '<input id="om-' . $this->type . '-' . $this->theme . '-optin-name" type="text" value="" data-om-action="selectable" data-om-target="#optin-monster-field-name_field" placeholder="' . $this->get_name_setting( 'placeholder', __( 'Enter your name here...', 'optin-monster-slide' ) ) . '" style="' . $this->get_element_style( 'name', array( 'font' => 'Open Sans' ) ) . '" />' ),
                'name'   => '#om-' . $this->type . '-' . $this->theme . '-optin-name'
            )
        );
        $fields['name_field']  = $instance->get_text_field(
            'name_field',
            $instance->get_name_setting( 'placeholder', __( 'Enter your name here...', 'optin-monster-slide' ) ),
            __( 'Name Placeholder', 'optin-monster-slide' ),
            __( 'The placeholder text for the email field.', 'optin-monster-slide' ),
            false,
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-name',
                'method' => 'attr',
                'attr'   => 'placeholder'
            ),
            array( 'om-live-preview' )
        );
        $fields['name_color']  = $instance->get_color_field(
            'name_color',
            $instance->get_name_setting( 'color', '#484848' ),
            __( 'Name Color', 'optin-monster-slide' ),
            __( 'The text color for the name field.', 'optin-monster-slide' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-name',
                'props'  => 'color'
            )
        );
        $fields['name_font']   = $instance->get_font_field(
            'name_font',
            $instance->get_name_setting( 'font', 'Open Sans' ),
            Optin_Monster_Output::get_instance()->get_supported_fonts(),
            __( 'Name Font', 'optin-monster-slide' ),
            __( 'The font family for the name field.', 'optin-monster-slide' ),
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-name',
                'attr'   => 'font-family',
                'method' => 'css'
            ),
            array( 'om-live-preview' )
        );

        // Build the email field.
        $fields['email_header'] = $instance->get_field_header(
            __( 'Email Field', 'optin-monster-slide' ),
            'email'
        );
        $fields['email_field']  = $instance->get_text_field(
            'email_field',
            $instance->get_email_setting( 'placeholder', __( 'Enter your email address here...', 'optin-monster-slide' ) ),
            __( 'Email Placeholder', 'optin-monster-slide' ),
            __( 'The placeholder text for the email field.', 'optin-monster-slide' ),
            false,
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-email',
                'method' => 'attr',
                'attr'   => 'placeholder'
            ),
            array( 'om-live-preview' )
        );
        $fields['email_color']  = $instance->get_color_field(
            'email_color',
            $instance->get_email_setting( 'color', '#484848' ),
            __( 'Email Color', 'optin-monster-slide' ),
            __( 'The text color for the email field.', 'optin-monster-slide' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-email',
                'props'  => 'color'
            )
        );
        $fields['email_font']   = $instance->get_font_field(
            'email_font',
            $instance->get_email_setting( 'font', 'Open Sans' ),
            Optin_Monster_Output::get_instance()->get_supported_fonts(),
            __( 'Email Font', 'optin-monster-slide' ),
            __( 'The font family for the email field.', 'optin-monster-slide' ),
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-email',
                'attr'   => 'font-family',
                'method' => 'css'
            ),
            array( 'om-live-preview' )
        );

        // Build the submit field.
        $fields['submit_header'] = $instance->get_field_header(
            __( 'Submit Field', 'optin-monster-slide' ),
            'submit'
        );
        $fields['submit_field']  = $instance->get_text_field(
            'submit_field',
            $instance->get_submit_setting( 'placeholder', __( 'Sign Up', 'optin-monster-slide' ) ),
            __( 'Submit Field', 'optin-monster-slide' ),
            __( 'The value of the submit button field.', 'optin-monster-slide' ),
            false,
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-submit', 'method' => 'val' ),
            array( 'om-live-preview' )
        );
        $fields['submit_color']  = $instance->get_color_field(
            'submit_color',
            $instance->get_submit_setting( 'field_color', '#fff' ),
            __( 'Submit Button Color', 'optin-monster-slide' ),
            __( 'The text color for the submit button field.', 'optin-monster-slide' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-submit',
                'props'  => 'color'
            )
        );
        $fields['submit_bg']     = $instance->get_color_field(
            'submit_bg',
            $instance->get_submit_setting( 'bg_color', '#ff370f' ),
            __( 'Submit Button Background', 'optin-monster-slide' ),
            __( 'The background color of the submit button.', 'optin-monster-slide' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-submit',
                'props'  => 'background-color,border-color'
            )
        );
        $fields['submit_font']   = $instance->get_font_field(
            'submit_font',
            $instance->get_submit_setting( 'font', 'Open Sans' ),
            Optin_Monster_Output::get_instance()->get_supported_fonts(),
            __( 'Submit Button Font', 'optin-monster-slide' ),
            __( 'The font family for the submit button field.', 'optin-monster-slide' ),
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-submit',
                'attr'   => 'font-family',
                'method' => 'css'
            ),
            array( 'om-live-preview' )
        );

        return $fields;

    }

    /**
     * Saves the meta fields for the optin controls.
     *
     * @since 2.0.0
     *
     * @param array $meta      The meta key "_om_meta" with all of its data.
     * @param int $optin_id    The optin ID to target.
     * @param array $fields    The post fields under the key "optin_monster".
     * @param array $post_data All of the $_POST contents generated when saving.
     * @return array $meta     Amended array of meta to be saved.
     */
    public function save_controls( $meta, $optin_id, $fields, $post_data ) {

        $meta['background']['header']  = isset( $fields['header_bg'] ) ? esc_attr( $fields['header_bg'] ) : '';
        $meta['background']['content'] = isset( $fields['content_bg'] ) ? esc_attr( $fields['content_bg'] ) : '';
        $meta['name']['show']          = isset( $fields['name_show'] ) ? 1 : 0;
        $meta['name']['placeholder']   = isset( $fields['name_field'] ) ? trim( strip_tags( $fields['name_field'] ) ) : '';
        $meta['name']['color']         = isset( $fields['name_color'] ) ? esc_attr( $fields['name_color'] ) : '';
        $meta['name']['font']          = isset( $fields['name_font'] ) ? trim( $fields['name_font'] ) : '';
        $meta['email']['placeholder']  = isset( $fields['email_field'] ) ? trim( strip_tags( $fields['email_field'] ) ) : '';
        $meta['email']['color']        = isset( $fields['email_color'] ) ? esc_attr( $fields['email_color'] ) : '';
        $meta['email']['font']         = isset( $fields['email_font'] ) ? trim( $fields['email_font'] ) : '';
        $meta['submit']['placeholder'] = isset( $fields['submit_field'] ) ? trim( strip_tags( $fields['submit_field'] ) ) : '';
        $meta['submit']['field_color'] = isset( $fields['submit_color'] ) ? esc_attr( $fields['submit_color'] ) : '';
        $meta['submit']['bg_color']    = isset( $fields['submit_bg'] ) ? esc_attr( $fields['submit_bg'] ) : '';
        $meta['submit']['font']        = isset( $fields['submit_font'] ) ? trim( $fields['submit_font'] ) : '';
        return $meta;

    }

}