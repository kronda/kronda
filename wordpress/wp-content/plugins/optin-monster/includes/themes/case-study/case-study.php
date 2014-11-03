<?php
/**
 * Case Study theme class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Theme_Case_Study extends Optin_Monster_Theme {

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
    public $theme = 'case-study';

    /**
     * The width of the image for the optin.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $img_width = 280;

    /**
     * The height of the image for the optin.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $img_height = 245;

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
     *
     * @return string A string of concatenated CSS for the theme.
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
            -moz-osx-font-smoothing: grayscale;
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
            background: rgb(0, 0, 0);
            background: rgba(0, 0, 0, .7);
            font-family: helvetica,arial,sans-serif;
            -moz-osx-font-smoothing: grayscale;
            -webkit-font-smoothing: antialiased;
            line-height: 1;
            width: 100%;
            height: 100%;
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
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin {
            background: #fff;
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            min-height: 175px;
            max-width: 700px;
            width: 100%;
            z-index: 734626274;
            -moz-box-shadow: inset 3px 4px 250px #d4d4d4;
            -webkit-box-shadow: inset 3px 4px 250px #d4d4d4;
            box-shadow: inset 3px 4px 250px #d4d4d4;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-wrap {
            position: relative;
            height: 100%;
        }
        html div#om-' . $this->optin->post_name . ' .om-close {
            position: absolute;
            top: -12px;
            right: -12px;
            text-decoration: none !important;
            display: block;
            width: 35px;
            height: 35px;
            background: url("' . plugins_url( 'assets/css/images/close.png', $this->base->file ) . '") no-repeat scroll 0 0;
            z-index: 1500;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-bar {
            margin-bottom: 20px;
            clear: both;
        }
        html div#om-' . $this->optin->post_name . ' .om-bar {
            float: left;
            width: 25%;
            height: 11px;
            display: block;
        }
        html div#om-' . $this->optin->post_name . ' .om-red-bar {
            background-color: #ef3e36;
        }
        html div#om-' . $this->optin->post_name . ' .om-green-bar {
            background-color: #abb92e;
        }
        html div#om-' . $this->optin->post_name . ' .om-orange-bar {
            background-color: #f57826;
        }
        html div#om-' . $this->optin->post_name . ' .om-blue-bar {
            background-color: #17b4e9;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-header {
            min-height: 30px;
            padding: 15px;
            width: 100%;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-title {
            font-size: 24px;
            color: #484848;
            width: 100%;
            margin-bottom: 15px;
            text-shadow: 0 1px #fff;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-tagline {
            font-size: 16px;
            line-height: 1.25;
            color: #484848;
            width: 100%;
            margin-bottom: 30px;
            text-shadow: 0 1px #fff;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-content {
            padding: 15px;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-left {
            float: left;
            max-width: 280px;
            width: 100%;
            position: relative;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-image-container {
            position: relative;
            max-width: 280px;
            max-height: 245px;
            margin: 0 auto;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-image-container img {
            display: block;
            margin: 0 auto;
            text-align: center;
            height: auto;
            max-width: 100%;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-right {
            float: right;
            max-width: 370px;
            width: 100%;
            background-color: #dadada;
            margin-right: -15px;
            padding: 20px;
            position: relative;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-arrow {
            position: absolute;
            width: 49px;
            height: 56px;
            left: -40px;
            top: -40px;
        }
        html div#om-' . $this->optin->post_name . ' label {
            color: #333;
        }
        html div#om-' . $this->optin->post_name . ' input,
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-name,
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-email {
            background-color: #fff;
            width: 100%;
            border: 1px solid #ccc;
            font-size: 16px;
            line-height: 24px;
            padding: 10px 6px;
            overflow: hidden;
            outline: none;
            margin: 0 0 15px;
            vertical-align: middle;
            height: 46px;
        }
        html div#om-' . $this->optin->post_name . ' input[type=submit],
        html div#om-' . $this->optin->post_name . ' button,
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-submit {
            background: #484848;
            border: 1px solid #484848;
            width: 100%;
            color: #fff;
            font-size: 20px;
            padding: 12px 6px;
            line-height: 28px;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            margin: 0;
            height: 54px;
        }
        html div#om-' . $this->optin->post_name . ' input[type=checkbox],
        html div#om-' . $this->optin->post_name . ' input[type=radio] {
            -webkit-appearance: checkbox;
            width: auto;
            outline: invert none medium;
            padding: 0;
            margin: 0;
        }
        @media (max-width: 700px) {
            html div#om-' . $this->optin->post_name . '[style] {
                position: absolute !important;
                height: 100%;
            }
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin[style] {
                width: 100%;
                position: relative;
            }
            html div#om-' . $this->optin->post_name . ' .om-close {
                right: 9px;
            }
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-left,
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-right,
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-name,
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-email,
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-submit,
            html div#om-' . $this->optin->post_name . ' .om-has-email #om-' . $this->type . '-' . $this->theme . '-optin-email {
                float: none;
                width: 100%;
                max-width: 100%;
            }
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-left,
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-name,
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-email {
                margin-bottom: 15px;
            }
        }
        @media only screen and (-webkit-min-device-pixel-ratio: 2),only screen and (min--moz-device-pixel-ratio: 2),only screen and (-o-min-device-pixel-ratio: 2/1),only screen and (min-device-pixel-ratio: 2),only screen and (min-resolution: 192dpi),only screen and (min-resolution: 2dppx) {
            html div#om-' . $this->optin->post_name . ' .om-close {
                background-image: url("' . plugins_url( 'assets/css/images/close@2x.png', $this->base->file ) . '");
                background-size: 35px 35px;
            }
        }
        ';
        return $css;

    }

    /**
     * Retrieval method for getting the HTML output for a theme.
     *
     * @since 2.0.0
     *
     * @return string A string of HTML for the theme.
     */
    public function get_html() {

        $provider = $this->get_email_setting( 'provider', '', false );
        $html = '<div id="om-' . $this->type . '-' . $this->theme . '-optin" class="om-' . $this->type . '-' . $this->theme . ' om-clearfix om-theme-' . $this->theme . ' ' . ( $provider && 'custom' == $provider ? 'om-custom-html-form' : '' ) . '">';
            $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-wrap" class="om-clearfix">';
                $html .= '<a href="#" class="om-close" title="' . esc_attr__( 'Close', 'optin-monster' ) . '"></a>';

                // Colored bar on top of theme.
                $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-bar" class="om-clearfix">';
                    $html .= '<span class="om-bar om-red-bar"></span>';
                    $html .= '<span class="om-bar om-green-bar"></span>';
                    $html .= '<span class="om-bar om-orange-bar"></span>';
                    $html .= '<span class="om-bar om-blue-bar"></span>';
                $html .= '</div>';

                // Header area.
                $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-header" class="om-clearfix">';
                    $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-title" data-om-action="editable" data-om-field="title" style="' . $this->get_element_style( 'title', array( 'color' => '#484848', 'font' => 'Bree Serif', 'size' => '32px', 'meta' => array( 'text-align' => 'center' ) ) ) . '">' . $this->get_title_setting( 'text', __( 'FREE Case Study Download', 'optin-monster' ) ) . '</div>';
                    $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-tagline" data-om-action="editable" data-om-field="tagline" style="' . $this->get_element_style( 'tagline', array( 'color' => '#484848', 'font' => 'Open Sans', 'size' => '24px', 'meta' => array( 'text-align' => 'center' ) ) ) . '">' . $this->get_tagline_setting( 'text', __( 'Learn how to market to visitors effectively.', 'optin-monster' ) ) . '</div>';
                $html .= '</div>';

                // Content area.
                $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-content" class="om-clearfix">';

                    // Left content area.
                    $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-left">';
                        $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-image-container" class="om-image-container om-clearfix">';
                             $html .= $this->get_image();
                        $html .= '</div>';
                    $html .= '</div>';

                    // Right content area.
                    $show_name = $this->get_name_setting( 'show' );
                    $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-right">';
                        $html .= '<img id="om-' . $this->type . '-' . $this->theme . '-arrow" src="' . plugins_url( 'images/arrow.png', $this->file ) . '" alt="' . esc_attr__( 'Arrow', 'optin-monster' ) . '" />';

                        // Show either the custom HTML form entered by the user or the custom fields in the Fields pane.
                        if ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ) {
                            $html .= do_shortcode( html_entity_decode( $this->meta['custom_html'] ) );
                        } else {
                            // Possibly show the name field.
                            if ( $show_name ) {
                                $html .= '<input id="om-' . $this->type . '-' . $this->theme . '-optin-name" type="text" value="" data-om-action="selectable" data-om-target="#optin-monster-field-name_field" placeholder="' . $this->get_name_setting( 'placeholder', __( 'Enter your name here...', 'optin-monster' ) ) . '" style="' . $this->get_element_style( 'name', array( 'font' => 'Open Sans' ) ) . '" />';
                            }

                            $html .= '<input id="om-' . $this->type . '-' . $this->theme . '-optin-email" type="email" value="" data-om-action="selectable" data-om-target="#optin-monster-field-email_field" placeholder="' . $this->get_email_setting( 'placeholder', __( 'Enter your email address here...', 'optin-monster' ) ) . '" style="' . $this->get_element_style( 'email', array( 'font' => 'Open Sans' ) ) . '" />';
                            $html .= '<input id="om-' . $this->type . '-' . $this->theme . '-optin-submit" type="submit" data-om-action="selectable" data-om-target="#optin-monster-field-submit_field" value="' . $this->get_submit_setting( 'placeholder', __( 'Sign Up', 'optin-monster' ) ) . '" style="' . $this->get_element_style( 'submit', array( 'font' => 'Bree Serif' ) ) . '" />';
                        }
                    $html .= '</div>';

                $html .= '</div>';

            $html .= '</div>';

            // If we have a powered by link, output it now.
            $html .= $this->get_powered_by_link();
        $html .= '</div>';
        return $html;

    }

    /**
     * Retrieval method for getting any custom JS for a theme.
     * This is done via output buffering so that it is easier
     * to read as JS during development.
     *
     * @since 2.0.0
     *
     * @return string A string of JS for the theme.
     */
    public function get_js() {



    }

    /**
     * Outputs the filters needed to register controls for the OptinMonster
     * preview customizer and save the fields registered.
     *
     * @since 2.0.0
     */
    public function controls() {

        add_filter( 'optin_monster_panel_input_fields', array( $this, 'input_fields' ), 10, 2 );
        add_filter( 'optin_monster_save_optin', array( $this, 'save_controls' ), 10, 4 );

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
        $fields['name_header'] = $instance->get_field_header( __( 'Name Field', 'optin-monster' ), 'name', true );
        $fields['name_show'] = $instance->get_checkbox_field(
            'name_show',
            $instance->get_name_setting( 'show' ),
            __( 'Show optin name field?', 'optin-monster' ),
            __( 'Displays or hides the name field in the optin.', 'optin-monster' ),
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-right',
                'input'  => esc_attr( '<input id="om-' . $this->type . '-' . $this->theme . '-optin-name" type="text" value="" data-om-action="selectable" data-om-target="#optin-monster-field-name_field" placeholder="' . $this->get_name_setting( 'placeholder', __( 'Enter your name here...', 'optin-monster' ) ) . '" style="' . $this->get_element_style( 'name', array( 'font' => 'Open Sans' ) ) . '" />' ),
                'name'   => '#om-' . $this->type . '-' . $this->theme . '-optin-name'
            )
        );
        $fields['name_field'] = $instance->get_text_field(
            'name_field',
            $instance->get_name_setting( 'placeholder', __( 'Enter your name here...', 'optin-monster' ) ),
            __( 'Name Placeholder', 'optin-monster' ),
            __( 'The placeholder text for the email field.', 'optin-monster' ),
            false,
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-name',
                'method' => 'attr',
                'attr'   => 'placeholder'
            ),
            array( 'om-live-preview' )
        );
        $fields['name_color'] = $instance->get_color_field(
            'name_color',
            $instance->get_name_setting( 'color', '#484848' ),
            __( 'Name Color', 'optin-monster' ),
            __( 'The text color for the name field.', 'optin-monster' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-name',
                'props'  => 'color'
            )
        );
        $fields['name_font'] = $instance->get_font_field(
            'name_font',
            $instance->get_name_setting( 'font', 'Open Sans' ),
            Optin_Monster_Output::get_instance()->get_supported_fonts(),
            __( 'Name Font', 'optin-monster' ),
            __( 'The font family for the name field.', 'optin-monster' ),
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-name',
                'attr'   => 'font-family',
                'method' => 'css'
            ),
            array( 'om-live-preview' )
        );

        // Build the email field.
        $fields['email_header'] = $instance->get_field_header( __( 'Email Field', 'optin-monster' ), 'email' );
        $fields['email_field'] = $instance->get_text_field(
            'email_field',
            $instance->get_email_setting( 'placeholder', __( 'Enter your email address here...', 'optin-monster' ) ),
            __( 'Email Placeholder', 'optin-monster' ),
            __( 'The placeholder text for the email field.', 'optin-monster' ),
            false,
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-email',
                'method' => 'attr',
                'attr'   => 'placeholder'
            ),
            array( 'om-live-preview' )
        );
        $fields['email_color'] = $instance->get_color_field(
            'email_color',
            $instance->get_email_setting( 'color', '#484848' ),
            __( 'Email Color', 'optin-monster' ),
            __( 'The text color for the email field.', 'optin-monster' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-email',
                'props'  => 'color'
            )
        );
        $fields['email_font'] = $instance->get_font_field(
            'email_font',
            $instance->get_email_setting( 'font', 'Open Sans' ),
            Optin_Monster_Output::get_instance()->get_supported_fonts(),
            __( 'Email Font', 'optin-monster' ),
            __( 'The font family for the email field.', 'optin-monster' ),
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-email',
                'attr'   => 'font-family',
                'method' => 'css'
            ),
            array( 'om-live-preview' )
        );

        // Build the submit field.
        $fields['submit_header'] = $instance->get_field_header( __( 'Submit Field', 'optin-monster' ), 'submit' );
        $fields['submit_field'] = $instance->get_text_field(
            'submit_field',
            $instance->get_submit_setting( 'placeholder', __( 'Sign Up', 'optin-monster' ) ),
            __( 'Submit Field', 'optin-monster' ),
            __( 'The value of the submit button field.', 'optin-monster' ),
            false,
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-submit',
                'method' => 'val'
            ),
            array( 'om-live-preview' )
        );
        $fields['submit_color'] = $instance->get_color_field(
            'submit_color',
            $instance->get_submit_setting( 'field_color', '#fff' ),
            __( 'Submit Button Color', 'optin-monster' ),
            __( 'The text color for the submit button field.', 'optin-monster' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-submit',
                'props'  => 'color'
            )
        );
        $fields['submit_bg'] = $instance->get_color_field(
            'submit_bg',
            $instance->get_submit_setting( 'bg_color', '#484848' ),
            __( 'Submit Button Background', 'optin-monster' ),
            __( 'The background color of the submit button.', 'optin-monster' ),
            '',
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin-submit',
                'props'  => 'background-color, border-color'
            )
        );
        $fields['submit_font'] = $instance->get_font_field(
            'submit_font',
            $instance->get_submit_setting( 'font', 'Bree Serif' ),
            Optin_Monster_Output::get_instance()->get_supported_fonts(),
            __( 'Submit Button Font', 'optin-monster' ),
            __( 'The font family for the submit button field.', 'optin-monster' ),
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