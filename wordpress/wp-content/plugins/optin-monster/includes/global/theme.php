<?php
/**
 * Theme class (abstract).
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
abstract class Optin_Monster_Theme {

    /**
     * Path to the file.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Holds the theme state (preview or not).
     *
     * @since 2.0.0
     *
     * @var bool
     */
    public $preview;

    /**
     * Holds the optin ID.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $optin_id;

    /**
     * Holds the optin object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $optin;

    /**
     * Holds the optin meta.
     *
     * @since 2.0.0
     *
     * @var array
     */
    public $meta;

    /**
     * Flag for loading the Google Fonts script.
     *
     * @since 2.0.0
     *
     * @var bool
     */
    public $google_loaded = false;

    /**
     * Holds fonts to be loaded via Google Fonts.
     *
     * @since 2.0.0
     *
     * @var array
     */
    public $fonts = array();

    /**
     * Simple check to see if the optin has an assigned image
     *
     * @since 2.0.0
     *
     * @var bool
     */
    public $has_image = false;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     *
     * @param int $optin_id The optin ID to target.
     */
    public function __construct( $optin_id ) {

        // Load the base class object and other class properties.
        $this->base     = Optin_Monster::get_instance();
        $this->preview  = Optin_Monster_Output::get_instance()->is_preview();
        $this->optin_id = $optin_id;
        $this->optin    = Optin_Monster::get_instance()->get_optin( $this->optin_id );
        $this->meta     = get_post_meta( $this->optin_id, '_om_meta', true );

    }

    /**
     * Retrieval method for getting the styles for a theme.
     *
     * @since 2.0.0
     */
    abstract public function get_styles();

    /**
     * Retrieval method for getting the HTML output for a theme.
     *
     * @since 2.0.0
     */
    abstract public function get_html();

    /**
     * Retrieval method for getting any custom JS for a theme.
     *
     * @since 2.0.0
     */
    abstract public function get_js();

    /**
     * Method for housing filters to allow for design and field controls.
     *
     * @since 2.0.0
     */
    abstract public function controls();

    /**
     * Because themes are not built inside of the global scope (only in Preview and live),
     * we add a method here to apply anything that should be accessed via ajax.
     * This is the preffered internal area to add filters and hooks for theme related activities that need
     * to be accessed from ajax requests, such as saving custom optin data.
     *
     * @since 2.0.0
     */
    public function ajax() {

        add_action( 'optin_monster_save_optin_data', array( $this, 'notes_panel_save' ), 10, 3 );

    }

    /**
     * Saves the Notes panel content.
     *
     * @since 2.0.0
     *
     * @param int $optin_id    The current optin ID.
     * @param array $fields    Array fo $_POST data fields with "optin-monster" key.
     * @param array $post_data All fields in the $_POST variable.
     */
    public function notes_panel_save( $optin_id, $fields, $post_data ) {

        if ( isset( $fields['notes'] ) ) {
            update_post_meta( $optin_id, '_om_split_notes', strip_tags( trim( $fields['notes'] ) ) );
        }

    }

    /**
     * Retrieves the styling for an element inside an optin.
     *
     * @since 2.0.0
     *
     * @param string $field  The meta field to retrieve.
     * @param array $styles  Array of key/value pairs for the style to retrieve and default if not set.
     * @return string $style The HTML style markup for the element.
     */
    public function get_element_style( $field, $styles = array() ) {

        $settings = $this->get_setting( $field );
        $fields   = array( 'color', 'field_color', 'bg_color', 'border', 'font', 'size', 'meta' );
        $style    = '';

        foreach ( $fields as $field ) {
            switch ( $field ) {
                case 'color' :
                case 'field_color' :
                    if ( ! empty( $settings[$field] ) ) {
                        $style .= 'color:' . $settings[$field] . ';';
                    } else if ( isset( $styles[$field] ) ) {
                        $style .= 'color:' . $styles[$field] . ';';
                    }
                    break;
                case 'bg_color' :
                    if ( ! empty( $settings[$field] ) ) {
                        $style .= 'background-color:' . $settings[$field] . ';';
                    } else if ( isset( $styles[$field] ) ) {
                        $style .= 'background-color:' . $styles[$field] . ';';
                    }
                    break;
                case 'border' :
                    if ( ! empty( $settings[$field] ) ) {
                        $style .= 'border-color:' . $settings[$field] . ';';
                    } else if ( isset( $styles[$field] ) ) {
                        $style .= 'border-color:' . $styles[$field] . ';';
                    } else {
                        if ( ! empty( $settings['bg_color'] ) ) {
                            $style .= 'border-color:' . $settings['bg_color'] . ';';
                        } else if ( isset( $styles['bg_color'] ) ) {
                            $style .= 'border-color:' . $styles['bg_color'] . ';';
                        }
                    }
                    break;
                case 'font' :
                    if ( ! empty( $settings[$field] ) ) {
                        if ( in_array( $settings[$field], $this->get_supported_fonts( true ) ) ) {
                            $this->load_font( $settings[$field] );
                        }
                        $style .= 'font-family:' . $settings[$field] . ';';
                    } else if ( isset( $styles[$field] ) ) {
                        if ( in_array( $styles[$field], $this->get_supported_fonts( true ) ) ) {
                            $this->load_font( $styles[$field] );
                        }
                        $style .= 'font-family:' . $styles[$field] . ';';
                    }
                    break;
                case 'size' :
                    if ( ! empty( $settings[$field] ) ) {
                        $style .= 'font-size:' . $settings[$field] . ';';
                    } else if ( isset( $styles[$field] ) ) {
                        $style .= 'font-size:' . $styles[$field] . ';';
                    }
                    break;
                case 'meta' :
                    if ( ! empty( $settings[$field] ) ) {
                        foreach ( (array) $settings[$field] as $prop => $val ) {
                            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
                        }
                    } else if ( isset( $styles[$field] ) ) {
                        foreach ( (array) $styles[$field] as $prop => $val ) {
                            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
                        }
                    }
                    break;
            }
        }

        return apply_filters( 'optin_monster_element_style', $style, $field, $styles, $this );

    }

    /**
     * Retrieves an optin meta setting.
     *
     * @since 2.0.0
     *
     * @param string $field   The meta field to retrieve.
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_setting( $field, $setting = '', $default = '' ) {

        if ( empty( $setting ) ) {
            return isset( $this->meta[$field] ) ? $this->meta[$field] : $default;
        } else {
            return isset( $this->meta[$field][$setting] ) ? $this->meta[$field][$setting] : $default;
        }

    }

    /**
     * Retrieves an optin meta setting for the display field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_display_setting( $setting, $default = '' ) {

        return isset( $this->meta['display'][$setting] ) ? $this->meta['display'][$setting] : $default;

    }

    /**
     * Retrieves an optin meta setting for the email field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_email_setting( $setting, $default = '' ) {

        return isset( $this->meta['email'][$setting] ) ? $this->meta['email'][$setting] : $default;

    }

    /**
     * Retrieves an optin meta setting for the name field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_name_setting( $setting, $default = '' ) {

        return isset( $this->meta['name'][$setting] ) ? $this->meta['name'][$setting] : $default;

    }

    /**
     * Retrieves an optin meta setting for the background field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_background_setting( $setting, $default = '' ) {

        return isset( $this->meta['background'][$setting] ) ? $this->meta['background'][$setting] : $default;

    }

    /**
     * Retrieves an optin meta setting for the title field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_title_setting( $setting, $default = '' ) {

        return isset( $this->meta['title'][$setting] ) ? $this->meta['title'][$setting] : $default;

    }

    /**
     * Retrieves an optin meta setting for the tagline field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_tagline_setting( $setting, $default = '' ) {

        return isset( $this->meta['tagline'][$setting] ) ? $this->meta['tagline'][$setting] : $default;

    }

    /**
     * Retrieves an optin meta setting for the bullet field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_bullet_setting( $setting, $default = '' ) {

        return isset( $this->meta['bullet'][$setting] ) ? $this->meta['bullet'][$setting] : $default;

    }

    /**
     * Retrieves an optin meta setting for the submit field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_submit_setting( $setting, $default = '' ) {

        return isset( $this->meta['submit'][$setting] ) ? $this->meta['submit'][$setting] : $default;

    }

    /**
     * Method for minifying styles.
     *
     * @since 2.0.0
     *
     * @param string $string The string to minify.
     * @param string $type   The type of data to minify.
     * @return string        Minified string.
     */
    public function minify( $string, $type = 'css' ) {

        $clean = 'css' == $type ? preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string ) : preg_replace( '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $string );
        $clean = str_replace( array( "\r\n", "\r", "\t", "\n", '  ', '    ', '     ' ), '', $clean );
        return $clean;

    }

    /**
     * Outputs the JS scaffolding required for optin themes.
     *
     * @since 2.0.0
     *
     * @param string $js The JS for the theme.
     * @return string    Prepended position data to the custom JS for the theme.
     */
    public function scaffold( $js ) {

        ob_start();
        ?>
        jQuery(document).ready(function($){
            // Load custom fonts if necessary.
            <?php if ( ! empty( $this->fonts ) ) : $fonts = implode( '|', array_unique( apply_filters( 'optin_monster_font_output', $this->fonts, $this ) ) ); $fonts = str_replace( "'", '', $fonts ); ?>
            WebFont.load({
                google: {
                    families: ['<?php echo urlencode( $fonts ); ?>']
                }
            });
            <?php endif; ?>

            // Append custom JS to the document.ready callback.
            <?php echo $js; ?>
        });
        <?php
        return $this->minify( ob_get_clean(), 'js' );

    }

    /**
     * Retrieves global styles applicable to any optin.
     *
     * @since 2.0.0
     *
     * @return string A string of CSS styles for global optin usage.
     */
    public function get_global_styles() {

        $styles = '
        .optin-monster-success-message {
            font-size: 21px;
            font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            color: #282828;
            font-weight: 300;
            text-align: center;
            margin: 0 auto;
        }
        .optin-monster-success-overlay .om-success-close {
        	font-size: 32px !important;
            font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif !important;
            color: #282828 !important;
            font-weight: 300 !important;
            position: absolute !important;
            top: 0px !important;
            right: 10px !important;
            background: none !important;
            text-decoration: none !important;
            width: auto !important;
            height: auto !important;
            display: block !important;
            line-height: 32px !important;
            padding: 0 !important;
        }
        ';
        return apply_filters( 'optin_monster_global_styles', $styles, $this );

    }

    /**
     * Returns the powered by link for an optin.
     *
     * @since 2.0.0
     *
     * @param bool $preview Ability to force preview styling.
     * @return string       The powered by link for the optin.
     */
    public function get_powered_by_link( $preview = false ) {

        if ( ! $this->get_setting( 'powered_by', '', true ) ) {
            return '';
        }

        $option  = get_option( 'optin_monster' );
        $link    = ! empty( $option['affiliate_link'] ) ? esc_url( $option['affiliate_link'] ) : 'http://optinmonster.com/?utm_source=plugin&utm_medium=link&utm_campaign=powered-by-link';
        $pos     = ! empty( $option['affiliate_link_position'] ) ? $option['affiliate_link_position'] : 'under';
        $output  = '';
        if ( 'under' == $pos ) {
            $output  .= '<p class="optin-monster-powered-by" style="width:100%;position:absolute;text-align:center;bottom:-35px;left:0;color:#fff;font-size:15px;line-height:15px;font-weight:700;margin:10px 0 0;">Powered by <a href="' . $link . '" title="OptinMonster" style="color:#fff;font-weight:700;text-decoration:underline;" target="_blank">OptinMonster</a></p>';
        } else {
            $bottom   = $this->preview || $preview ? '98px' : '20px';
            $output  .= '<p class="optin-monster-powered-by" style="position:fixed;text-align:center;bottom:' . $bottom . ';left:20px;color:#fff;font-size:15px;line-height:15px;font-weight:700;margin:10px 0 0;">Powered by <a href="' . $link . '" title="OptinMonster" style="color:#fff;font-weight:700;text-decoration:underline;" target="_blank">OptinMonster</a></p>';
        }

        return $output;

    }

    /**
     * Returns the Google webfont loader.
     *
     * @since 2.0.0
     *
     * @return string HTML for loading the webfont loader.
     */
    public function get_fonts() {

        if ( $this->preview || $this->google_loaded ) {
            return '';
        }

        $this->google_loaded = true;

        return '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js"></script>';

    }

    /**
     * Returns the supported theme fonts.
     *
     * @since 2.0.0
     *
     * @return array $fonts Array of supported font families.
     */
    public function get_supported_fonts( $google_only = false ) {

        return Optin_Monster_Output::get_instance()->get_supported_fonts( $google_only );

    }

    /**
     * Outputs the font loader to the page.
     *
     * @since 2.0.0
     *
     * @param string $font The font to load.
     */
    public function load_font( $font ) {

		// Remove quotes from font.
		$font = str_replace( '\'', '', $font );

        if ( in_array( $font, $this->fonts ) ) {
            return;
        }

        if ( ! in_array( $font, $this->get_supported_fonts( true ) ) ) {
            return;
        }

        $this->fonts[] = $font;

    }

    /**
     * Outputs the interface for adding images to optins.
     *
     * @since 2.0.0
     *
     * @return string The image placeholder interface.
     */
    public function get_image() {

        $html = '';
        if ( has_post_thumbnail( $this->optin_id ) ) {
            $this->has_image = true;
            if ( $this->preview ) {
                $html .= $this->get_image_with_controls();
            } else {
                $html .= $this->get_optin_image( $this->optin_id, $this->base->plugin_slug . '-' . $this->type . '-theme-' . $this->theme );
            }
        } else if ( $this->preview ) {
            $this->has_image = false;
            $html .= $this->get_image_placeholder();
        }

        return $html;

    }

    /**
     * Gets the HTML output for the optin image.
     *
     * @since 2.0.0
     *
     * @param int $optin_id The optin ID.
     * @param string $size  The optin image size registered with WordPress.
     * @return string 		The image placeholder interface.
     */
    public function get_optin_image( $optin_id, $size ) {

	    return optin_monster_ajax_get_optin_image( $optin_id, $size, $this->type, $this->theme );

    }

    /**
     * Gets the image with editing controls for the preview frame.
     *
     * @since 2.0.0
     *
     * @return string The image HTML with editing controls.
     */
    public function get_image_with_controls() {

        return optin_monster_ajax_get_image_thumbnail( $this->optin_id, $this->type, $this->theme );

    }

    /**
     * Gets the image placeholder.
     *
     * @since 2.0.0
     *
     * @return string The image placeholder.
     */
    public function get_image_placeholder() {

        return optin_monster_ajax_get_image_placeholder( $this->optin_id, $this->type, $this->theme, $this->img_width, $this->img_height );

    }

    /**
     * Gets the default bullet list for an optin.
     *
     * @since 2.0.0
     *
     * @return string The bullet list HTML.
     */
    public function get_default_bullets() {

        $html  = '<ul>';
            $html .= '<li>' . __( 'Grow your email list <span style="font-style:italic">exponentially</span>', 'optin-monster' ) . '</li>';
            $html .= '<li>' . __( 'Dramatically <span style="background-color:#ffff00">increase</span> your conversion rates', 'optin-monster' ) . '</li>';
            $html .= '<li>' . __( 'Engage more with your audience', 'optin-monster' ) . '</li>';
            $html .= '<li>' . __( '<span style="font-weight:bold">Boost your current and future profits</span>', 'optin-monster' ) . '</li>';
        $html .= '</ul>';

        return $html;

    }

    /**
     * Converts a hex color into RGB format.
     *
     * @since 2.0.0
     *
     * @param string $hex   The hex code to convert.
     * @return string|array RGB format for the color (string or array format).
     */
    public function hex2rgb( $hex, $array = false ) {

        $hex = str_replace( '#', '', $hex );

        if ( 3 == strlen( $hex ) ) {
           $r = hexdec( substr( $hex, 0, 1 ).substr( $hex, 0, 1 ) );
           $g = hexdec( substr( $hex, 1, 1 ).substr( $hex, 1, 1 ) );
           $b = hexdec( substr( $hex, 2, 1 ).substr( $hex, 2, 1 ) );
        } else {
           $r = hexdec( substr( $hex, 0, 2 ) );
           $g = hexdec( substr( $hex, 2, 2 ) );
           $b = hexdec( substr( $hex, 4, 2 ) );
        }

        $rgb = array ($r, $g, $b );
        return $array ? $rgb : implode(',', $rgb );

    }

    /**
     * Method for setting API errors for the themes.
     *
     * @since 2.0.0
     *
     * @param string $id      The ID of the error.
     * @param string $message The error message.
     * @return object         A new WP_Error object.
     */
    public function error( $id, $message ) {

        return new WP_Error( $id, $message );

    }

}