<?php
/**
 * Whiteboard canvas theme class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @subpackage Optin_Monster_Canvas
 * @author  Thomas Griffin
 */
class Optin_Monster_Canvas_Theme_Whiteboard extends Optin_Monster_Theme {

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
    public $theme = 'whiteboard';

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
            width: 700px;
            height: 350px;
        }
        html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-wrap {
            position: relative;
            height: 100%;
            padding: 20px;
        }
        html div#om-' . $this->optin->post_name . ' .om-close {
            position: absolute;
            top: -19px;
            right: -19px;
            text-decoration: none !important;
            display: block;
            width: 35px;
            height: 35px;
            background: transparent url(' . plugins_url( 'assets/css/images/close.png', $this->base->file ) . ') no-repeat scroll 0 0 !important;
            z-index: 321;
        }
        @media (max-width: 700px) {
            html div#om-' . $this->optin->post_name . '[style] {
                position: absolute !important;
            }
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin[style] {
                width: 100%;
                position: relative;
                top: 30px !important;
            }
            html div#om-' . $this->optin->post_name . ' #om-' . $this->type . '-' . $this->theme . '-optin-wrap {
                padding: 10px;
            }
        }
        @media only screen and (-webkit-min-device-pixel-ratio: 2),only screen and (min--moz-device-pixel-ratio: 2),only screen and (-o-min-device-pixel-ratio: 2/1),only screen and (min-device-pixel-ratio: 2),only screen and (min-resolution: 192dpi),only screen and (min-resolution: 2dppx) {
            html div#om-' . $this->optin->post_name . ' .om-close {
                background-image: url("' . plugins_url( 'assets/css/images/close@2x.png', $this->base->file ) . '") !important;
                background-size: 35px 35px !important;
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

        $html = '<div id="om-' . $this->type . '-' . $this->theme . '-optin" class="om-' . $this->type . '-' . $this->theme . ' om-clearfix om-theme-' . $this->theme . '" style="width: ' . $this->get_setting( 'dimensions', 'width', '700' ) . 'px; height: ' . $this->get_setting( 'dimensions', 'height', '350' ) . 'px;" >';
            $html .= '<div id="om-' . $this->type . '-' . $this->theme . '-optin-wrap" class="om-clearfix">';
                $content = html_entity_decode( $this->get_setting( 'custom_canvas_html', '', '' ), ENT_QUOTES );
                $content = str_replace( array( 'ajax="true"', 'ajax=true' ), '', $content );
                $html .= '<div id="om-canvas-' . $this->theme . '-optin-content"><div class="optin_custom_html_applied">' . do_shortcode( $content ) . '</div></div>';
                $html .= '<a href="#" class="om-close" title="' . esc_attr__( 'Close', 'optin-monster-canvas' ) . '"></a>';
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

        add_filter( 'optin_monster_panel_design_fields', array( $this, 'design_fields' ), 10, 2 );
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

        $fields['width_field'] = $instance->get_text_field(
            'width_field',
            $this->get_setting( 'dimensions', 'width', '700' ),
            __( 'Canvas Width', 'optin-monster-canvas' ),
            __( 'The width of the lightbox (in pixels).', 'optin-monster-canvas' ),
            false,
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin',
                'method' => 'css',
                'attr'   => 'width'
            ),
            array( 'om-live-preview' )
        );
        $fields['height_field'] = $instance->get_text_field(
            'height_field',
            $this->get_setting( 'dimensions', 'height', '350' ),
            __( 'Canvas Height', 'optin-monster-canvas' ),
            __( 'The height of the lightbox (in pixels).', 'optin-monster-canvas' ),
            false,
            array(
                'target' => '#om-' . $this->type . '-' . $this->theme . '-optin',
                'method' => 'css',
                'attr'   => 'height'
            ),
            array( 'om-live-preview' )
        );
        $fields['custom_html'] = $instance->get_textarea_field(
            'custom_html',
            $instance->get_setting( 'custom_canvas_html', '', '' ),
            __( 'Custom HTML', 'optin-monster-canvas' ),
            '',
            false,
            array(
                'target' => '#om-canvas-' . $this->theme . '-optin-content.optin_custom_html_applied',
                'theme' => $this->meta['theme']
            ),
            array( 'om-custom-html-editor' )
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

        $meta['dimensions']['width']  = isset( $fields['width_field'] ) ? esc_attr( $fields['width_field'] ) : '';
        $meta['dimensions']['height'] = isset( $fields['height_field'] ) ? esc_attr( $fields['height_field'] ) : '';

        $html                       = isset( $fields['custom_html'] ) ? esc_attr( $fields['custom_html'] ) : '';
        $meta['custom_canvas_html'] = str_replace( array( 'ajax="true"', 'ajax=true' ), '', $html );
        return $meta;

    }

}