<?php
/**
 * Views edit class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Views_Edit {

    /**
     * Holds the class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public static $instance;

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
     * Holds the optin ID .
     *
     * @since 2.0.0
     *
     * @var bool|int
     */
    public $optin_id = false;

    /**
     * Holds the optin object.
     *
     * @since 2.0.0
     *
     * @var bool|object
     */
    public $optin = false;

    /**
     * Holds the optin meta.
     *
     * @since 2.0.0
     *
     * @var bool|array
     */
    public $meta = false;

    /**
     * Holds the theme object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $theme;

    /**
     * Holds a tabindex counter for easy navigation through fields.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $tabindex = 129;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Set the optin ID, object and meta properties.
        $this->optin_id = isset( $_GET['om_optin_id'] ) ? $_GET['om_optin_id'] : $_POST['id'];
        $this->optin    = get_post( $this->optin_id );
        $this->meta     = get_post_meta( $this->optin_id, '_om_meta', true );
        $this->theme    = Optin_Monster_Output::get_instance()->get_optin_monster_theme( $this->meta['theme'], $this->optin_id, true );

        // Possibly add custom notes area if editing a split test.
        if ( isset( $_GET['om_split'] ) && $_GET['om_split'] ) {
            $this->split_test_notes();
        }

        // Load Google fonts to the page.
        add_action( 'admin_print_footer_scripts', array( $this, 'load_google_fonts' ), 999 );

        // Load the theme control filters.
        $this->theme->controls();

    }

    /**
     * Outputs the optin view.
     *
     * @since 2.0.0
     */
    public function view() {

        ?>
        <div class="optin-monster optin-monster-edit optin-monster-customizer optin-monster-clear">
            <div class="optin-monster-sidebar">
                <div class="optin-monster-logo"></div>
                <div class="optin-monster-campaign-title">
                    <h4><?php printf( __( 'Now Editing: %s', 'optin-monster' ), '<span id="optin-monster-campaign-title">' . ( ! empty( $this->optin->post_title ) ? $this->optin->post_title : $this->optin->post_name ) . '</span>' ); ?></h4>
                </div>
                <form id="optin-monster-settings-form" name="optin-monster-settings-form" method="post">
                    <dl class="optin-monster-panels">
                        <?php $panels = $this->get_panels(); foreach ( $panels as $id => $data ) : $class = 'design' == $id ? 'optin-monster-panel optin-monster-panel-' . $id . ' optin-monster-panel-active' : 'optin-monster-panel optin-monster-panel-' . $id; ?>
                        <dt id="optin-monster-panel-<?php echo sanitize_html_class( $id ); ?>" class="<?php echo $class; ?>">
                            <a href="#">
                                <?php $padding = ! empty( $data['padding'] ) ? 'style="padding-left:' . $data['padding'] . 'px;"' : ''; ?>
                                <i class="fa <?php echo $data['icon']; ?>"></i><span <?php echo $padding; ?>><?php echo $data['name']; ?></span>
                                <?php if ( 'integration' == $id ) : ?>
                                <i class="fa fa-spinner fa-spin"></i>
                                <?php endif; ?>
                            </a>
                        </dt>
                        <dd class="optin-monster-board optin-monster-board-<?php echo $id; ?>">
                            <?php echo $this->get_panel_content( $id ); ?>
                        </dd>
                        <?php endforeach; ?>
                    </dl>
                </form>
            </div>
            <div class="optin-monster-preview">
                <div class="optin-monster-toolbar">
                    <div class="optin-monster-toolbar-title">
                        <h3>
                            <?php $theme = ucwords( str_replace( '-', ' ', $this->meta['theme'] ) ) . ' Theme'; printf( __( 'Now Previewing: %s', 'optin-monster' ), $theme ); ?> <i class="fa fa-spinner fa-spin"></i>
                        </h3>
                    </div>
                    <div class="optin-monster-toolbar-buttons">
                        <a class="om-toolbar-button" href="#" title="<?php esc_attr_e( 'Save', 'optin-monster' ); ?>" data-om-action="save"><i class="fa fa-power-off"></i> <span><?php _e( 'Save', 'optin-monster' ); ?></span></a>
                        <a class="om-toolbar-button om-toolbar-button-dark" href="#" title="<?php esc_attr_e( 'Save and Exit', 'optin-monster' ); ?>" data-om-action="exit"><i class="fa fa-sign-out"></i> <span><?php _e( 'Save and Exit', 'optin-monster' ); ?></span></a>
                        <a class="om-toolbar-button om-toolbar-button-red" href="<?php echo esc_url( add_query_arg( array( 'page' => 'optin-monster-settings' ), admin_url( 'admin.php' ) ) ); ?>" title="<?php esc_attr_e( 'Exit', 'optin-monster' ); ?>" data-om-action="exit-none"><i class="fa fa-times"></i> <span><?php _e( 'Exit', 'optin-monster' ); ?></span></a>
                    </div>
                    <div class="optin-monster-preview-frame">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php

    }

    /**
     * Adds managing split test notes to the editing experience.
     *
     * @since 2.0.0
     */
    public function split_test_notes() {

        add_filter( 'optin_monster_panels', array( $this, 'notes_panel' ), 10, 2 );
        add_filter( 'optin_monster_panel_content', array( $this, 'notes_panel_content' ), 10, 3 );

    }

    /**
     * Builds the Notes panel.
     *
     * @since 2.0.0
     *
     * @param array $panels  Array of customizer panels.
     * @param int $optin_id  The current optin ID.
     * @return array $panels Amended array of panels.
     */
    public function notes_panel( $panels, $optin_id ) {

        $panels['notes'] = array(
            'name' => __( 'Notes', 'optin-monster' ),
            'icon' => 'fa-edit'
        );
        return $panels;

    }

    /**
     * Builds the Notes panel content.
     *
     * @since 2.0.0
     *
     * @param string $content  The panel content.
     * @param string $panel_id The panel ID to target.
     * @param int $optin_id    The current optin ID.
     * @return string $content The content for the panel.
     */
    public function notes_panel_content( $content, $panel_id, $optin_id ) {

        if ( 'notes' !== $panel_id ) {
            return $content;
        }

        $content = $this->get_textarea_field( 'notes', get_post_meta( $optin_id, '_om_split_notes', true ), __( 'Split Test Notes', 'optin-monster' ), __( 'Notes are useful for keeping track of the changes between each split test you create.', 'optin-monster' ) );
        return $content;

    }

    /**
     * Loads Google fonts pnto the page.
     *
     * @since 2.0.0
     */
    public function load_google_fonts() {

        ?>
        <script type="text/javascript">
            WebFont.load({
                google: {
                    families: [optin_monster_edit.fonts]
                }
            });
        </script>
        <?php

    }

    /**
     * Retrieves all of the design panels.
     *
     * @since 2.0.0
     */
    public function get_panels() {

        $panels = array(
            'design' => array(
                'name' => __( 'Design', 'optin-monster' ),
                'icon' => 'fa-picture-o'
            ),
            'theme' => array(
                'name'    => __( 'Theme', 'optin-monster' ),
                'icon'    => 'fa-bullhorn',
                'padding' => '13'
            ),
            'fields' => array(
                'name' => __( 'Fields', 'optin-monster' ),
                'icon' => 'fa-file-text-o'
            ),
            'configuration' => array(
                'name' => __( 'Configuration', 'optin-monster' ),
                'icon' => 'fa-cogs'
            ),
            'integration' => array(
                'name' => __( 'Integration', 'optin-monster' ),
                'icon' => 'fa-paper-plane-o'
            ),
            'output' => array(
                'name' => __( 'Output', 'optin-monster' ),
                'icon' => 'fa-desktop'
            )
        );

        return apply_filters( 'optin_monster_panels', $panels, $this->optin_id, $this );

    }

    /**
     * Retrieves a panel with its appropriate content.
     *
     * @since 2.0.0
     *
     * @param string $id The panel ID to retrieve content for.
     */
    public function get_panel_content( $id ) {

        $ret = '';
        switch ( $id ) {
            case 'design' :
                $ret = $this->get_design_panel_content();
                break;
            case 'theme' :
                $ret = $this->get_theme_panel_content();
                break;
            case 'fields' :
                $ret = $this->get_fields_panel_content();
                break;
            case 'configuration' :
                $ret = $this->get_configuration_panel_content();
                break;
            case 'integration' :
                $ret = $this->get_integration_panel_content();
                break;
            case 'output' :
                $ret = $this->get_output_panel_content();
                break;
            default :
                $ret = apply_filters( 'optin_monster_panel_content', '', $id, $this->optin_id, $this );
                break;
        }

        return apply_filters( 'optin_monster_panel', $ret, $id, $this );

    }

    /**
     * Retrieves the panel content for the Design panel.
     *
     * @since 2.0.0
     *
     * @param string HTML string of panel content.
     */
    public function get_design_panel_content() {

        // Allow each design field to be filtered.
        $design               = array();
        $design['help']       = $this->get_help_tips();
        $design['custom_css'] = $this->get_textarea_field( 'custom_css', $this->get_setting( 'custom_css', '', '' ), __( 'Custom Optin CSS', 'optin-monster' ), sprintf( __( 'Adds custom CSS to your optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration: %s <a href="%s" target="_blank">Click here for help on using custom CSS with OptinMonster.</a>', 'optin-monster' ), '<br><br><code class="om-custom-css">' . 'html div#om-' . $this->optin->post_name . '</code><br><br>', 'http://optinmonster.com/docs/custom-css/' ), false, array( 'target' => '#om-' . $this->optin->post_name, 'theme' => $this->meta['theme'] ), array( 'om-custom-css-editor' ) );
        $design               = apply_filters( 'optin_monster_panel_design_fields', $design, $this );

        // Move the custom CSS field to the last field if it still exists.
        if ( ! empty( $design['custom_css'] ) ) {
            $custom = $design['custom_css'];
            unset( $design['custom_css'] );
            $design['custom_css'] = $custom;
        }

        return apply_filters( 'optin_monster_panel_design', implode( "\n", $design ), $design, $this );

    }

    /**
     * Retrieves the panel content for the Theme panel.
     *
     * @since 2.0.0
     *
     * @param string HTML string of panel content.
     */
    public function get_theme_panel_content() {

        $theme          = array();
        $theme['help']  = '<p class="om-theme-selection-title">' . __( 'You can change your optin theme by selecting one of the available themes below. <strong>When you change a theme, changes to your current optin (except for the theme itself) will be saved automatically before the new theme is displayed in the preview frame.</strong>', 'optin-monster' ) . '</p>';
        $theme['theme'] = $this->get_theme_selection();
        $theme          = apply_filters( 'optin_monster_panel_theme_fields', $theme, $this );

        return apply_filters( 'optin_monster_panel_theme', implode( "\n", $theme ), $theme, $this );

    }

    /**
     * Retrieves the panel content for the Fields panel.
     *
     * @since 2.0.0
     *
     * @param string HTML string of panel content.
     */
    public function get_fields_panel_content() {

        // Allow each design field to be filtered.
        $fields = apply_filters( 'optin_monster_panel_input_fields', array(), $this );
        return apply_filters( 'optin_monster_panel_fields', implode( "\n", $fields ), $fields, $this );

    }

    /**
     * Retrieves the panel content for the Configuration panel.
     *
     * @since 2.0.0
     *
     * @param string HTML string of panel content.
     */
    public function get_configuration_panel_content() {

        $config                  = array();
        $config['title']         = $this->get_text_field( 'campaign_title', $this->optin->post_title, __( 'Optin Title', 'optin-monster' ), __( 'The internal campaign title for your optin.', 'optin-monster' ), __( 'Your optin campaign title...', 'optin-monster' ), array( 'target' => '#optin-monster-campaign-title', 'method' => 'text' ) );
        $config['delay']         = $this->get_text_field( 'delay', $this->get_setting( 'delay', '', 5000 ), __( 'Optin Loading Delay', 'optin-monster' ), __( 'The amount of time the page should wait before loading the optin (defaults to 5 seconds) <strong>in milliseconds</strong>.', 'optin-monster' ) );
        $config['cookie']        = $this->get_text_field( 'cookie', $this->get_setting( 'cookie', '', 30 ), __( 'Optin Cookie Duration', 'optin-monster' ), __( 'The length of time before the optin will display again to the user once they exit or opt in to your campaign (defaults to 30 days). <strong>Set to 0 to prevent cookies from being set.</strong>', 'optin-monster' ) );
        $config['success']       = $this->get_textarea_field( 'success', $this->get_setting( 'success', '', __( 'Thanks for subscribing! Please check your email for further instructions.', 'optin-monster' ) ), __( 'Optin Success Message', 'optin-monster' ), __( 'The message to display once a user has successfully opted in to this campaign <strong>(only displayed if there is no redirect on success link).</strong>', 'optin-monster' ) );
        $config['redirect']      = $this->get_text_field( 'redirect', $this->get_setting( 'redirect' ), __( 'Optin Redirect on Success', 'optin-monster' ), __( 'The URL to redirect a user to after they have successfully opted in to this campaign.', 'optin-monster' ) );
        $config['redirect_pass'] = $this->get_checkbox_field( 'redirect_pass', $this->get_checkbox_setting( 'redirect_pass', '', 0 ), __( 'Pass Lead Data to Redirect URL?', 'optin-monster' ), sprintf( __( 'Passes the lead email (and name if enabled) as query args to the redirect URL. <a href="%s" title="Click here for more information" target="_blank">Click here for more information about this feature.</a>', 'optin-monster' ), 'http://optinmonster.com/docs/passing-lead-data-to-redirect-urls-in-optinmonster/' ) );
        $config['second']        = $this->get_checkbox_field( 'second', $this->get_checkbox_setting( 'second', '', 0 ), __( 'Load on Second Pageview?', 'optin-monster' ), __( 'Forces the optin to load on the second pageview for the visitor, not the first.', 'optin-monster' ) );
        $config['logged_in']     = $this->get_checkbox_field( 'logged_in', $this->get_checkbox_setting( 'logged_in', '', 0 ), __( 'Hide for Logged-in Users?', 'optin-monster' ), __( 'Hides the optin for users logged into your website.', 'optin-monster' ) );

        // Cpnditionally add the powered by setting for lightbox/canvas optin types.
        $type = $this->get_setting( 'type' );
        if ( 'lightbox' == $type || 'canvas' == $type ) {
            $config['powered_by'] = $this->get_checkbox_field( 'powered_by', $this->get_checkbox_setting( 'powered_by', '', 1 ), __( 'Show Powered By Link?', 'optin-monster' ), sprintf( __( 'Shows a "powered by" link below your optin. If your affiliate link is set in the <a href="%s">Misc settings</a>, it will be used.', 'optin-monster' ), add_query_arg( array( 'page' => 'optin-monster-settings#!optin-monster-tab-settings' ), admin_url( 'admin.php' ) ) ), array( 'target' => '#om-' . $this->theme->type . '-' . $this->theme->theme . '-optin', 'html' => esc_attr( $this->theme->get_powered_by_link( true ) ) ) );
        }

        // Allow each configuration field to be filtered.
        $config = apply_filters( 'optin_monster_panel_configuration_fields', $config, $this );
        return apply_filters( 'optin_monster_panel_configuration', implode( "\n", $config ), $config, $this );

    }

    /**
     * Retrieves the panel content for the Integration panel.
     *
     * @since 2.0.0
     *
     * @param string HTML string of panel content.
     */
    public function get_integration_panel_content() {

        $html = $this->get_dropdown_field( 'provider', $this->get_email_setting( 'provider' ), Optin_Monster_Common::get_instance()->get_email_providers(), __( 'Email provider', 'optin-monster' ) );
        return $html;

    }

    /**
     * Retrieves the panel content for the Output panel.
     *
     * @since 2.0.0
     *
     * @param string HTML string of panel content.
     */
    public function get_output_panel_content() {

        $html              = array();
        $html['enabled']   = $this->get_checkbox_field( 'enabled', $this->get_checkbox_setting( 'display', 'enabled', 1 ), __( 'Enable optin on site?', 'optin-monster' ), __( 'The optin will not be displayed on this site unless this setting is checked.', 'optin-monster' ) );
        $html['global']    = $this->get_checkbox_field( 'global', $this->get_checkbox_setting( 'display', 'global', 0 ), __( 'Load optin globally?', 'optin-monster' ), __( 'If checked, the optin code will be loaded on all pages of your site.', 'optin-monster' ) );
        $html['never']     = $this->get_custom_field( 'never', $this->get_post_selection( 'never', (array) $this->get_display_setting( 'never' ) ), __( 'Never load optin on:', 'optin-monster' ), __( 'Never loads the optin on the selected posts.', 'optin-monster' ) );
        $html['exclusive'] = $this->get_custom_field( 'exclusive', $this->get_post_selection( 'exclusive', (array) $this->get_display_setting( 'exclusive' ) ), __( 'Load optin exclusively on:', 'optin-monster' ), __( 'Loads the optin only on the selected posts.', 'optin-monster' ) );

        // Possibly load the categories setting if they exist.
        $categories = get_categories();
        if ( $categories ) {
            ob_start();
            wp_category_checklist( 0, 0, (array) $this->get_display_setting( 'categories' ), false, null, false );
            $value = ob_get_clean();
            $html['categories'] = $this->get_custom_field( 'categories', $value, __( 'Load on post categories:', 'optin-monster' ), __( 'Loads the optin on posts that are in one of the selected categories.', 'optin-monster' ) );
        }

        $html['show'] = $this->get_custom_field( 'show', $this->get_optin_show_fields(), __( 'Load optin on:', 'optin-monster' ), __( 'Loads the optin on posts that match the selection criteria.', 'optin-monster' ) );

        // Allow fileds to be filtered.
        $html = apply_filters( 'optin_monster_panel_output_fields', $html, $this );
        return apply_filters( 'optin_monster_panel_output', implode( "\n", $html ), $html, $this );

    }

    /**
     * Retrieves the post selection data for choosing posts to display or
     * not display optins.
     *
     * @since 2.0.0
     *
     * @param string $field   The field to target.
     * @param array $selected The selected items to check.
     * @return string         The post selection interface.
     */
    public function get_post_selection( $field, $selected ) {

        $posts = get_posts(
            array(
                'posts_per_page' => apply_filters( 'optin_monster_post_selection_limit', 500 ),
                'post_status'    => 'publish',
                'post_type'      => get_post_types( array( 'public' => true ) )
            )
        );
        $prior  = '';
        $output = '<select id="optin-monster-field-' . $field . '" name="optin_monster[' . $field . '][]" tabindex="' . $this->tabindex . '" class="om-chosen-field" multiple="multiple" data-placeholder="' . esc_attr__( 'Select your post(s)...', 'optin-monster' ) . '">';
            foreach ( (array) $posts as $post ) {
                $type  = get_post_type_object( $post->post_type );
                $type  = $type->labels->name;
                if ( $type != $prior ) {
                    if ( '' != $prior ) {
                        $output .= '</optgroup>';
                    }

                    $output .= '<optgroup label="' . $type . '">';
                }

                if ( in_array( $post->ID, $selected ) ) {
                    $selection = 'selected';
                } else {
                    $selection = '';
                }

                $output .= '<option value="' . $post->ID . '" ' . $selection . ' >' . $post->post_title . '</option>';
                $prior = $type;
            }
        $output .= '</select>';

        return $output;

    }

    /**
     * Retrieves the UI output for the single posts show setting.
     *
     * @since 2.0.0
     *
     * @return string $html HTML representation of the data.
     */
    public function get_optin_show_fields() {

        // Increment the global tabindex counter.
        $this->tabindex++;

        $output  = '<input type="checkbox" id="optin-monster-field-show-index" name="optin_monster[show][]" value="index"' . checked( in_array( 'index', (array) $this->get_display_setting( 'show' ) ), 1, false ) . ' /> ';
        $output .= '<label for="optin-monster-field-show-index" class="optin-monster-custom-label">' . __( 'Front Page, Archive and Search Pages', 'optin-monster' ) . '</label><br />';
        $post_types = get_post_types( array( 'public' => true ) );
        foreach ( (array) $post_types as $show ) {
            $pt_object = get_post_type_object( $show );
            $label     = $pt_object->labels->name;
            $output   .= '<input type="checkbox" id="optin-monster-field-show-' . esc_html( strtolower( $label ) ) . '" name="optin_monster[show][]" tabindex="' . $this->tabindex . '" value="' . $show . '"' . checked( in_array( $show, (array) $this->get_display_setting( 'show' ) ), 1, false ) . ' /> ';
            $output   .= '<label for="optin-monster-field-show-' . esc_html( strtolower( $label ) ) . '" class="optin-monster-custom-label">' . esc_html( $label ) . '</label><br />';

            // Increment the global tabindex counter.
            $this->tabindex++;
        }

        return $output;

    }

    /**
     * Retrieves the UI output for a colorpicker field.
     *
     * @since 2.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param string $place   Placeholder text for the field.
     * @param array $attr     Array of data attributes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_color_field( $setting, $value, $label, $desc = false, $place = false, $attr = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // If there are attributes to add, build them now.
        $attr_string = '';
        if ( ! empty( $attr ) ) {
            foreach ( $attr as $key => $val ) {
                $attr_string .= ' data-' . $key . '="' . $val . '"';
            }
        }

        // Build the HTML.
        $field  = '<div class="optin-monster-field-box optin-monster-field-box-' . $setting . ' optin-monster-clear">';
            $field .= '<p class="optin-monster-field-wrap"><label for="optin-monster-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<input type="text" class="om-color-picker" id="optin-monster-field-' . $setting . '" name="optin_monster[' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . '' . $attr_string . ' />';
                if ( $desc ) {
                    $field .= '<br /><span class="optin-monster-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_text_field', $field, $setting, $value, $label, $this->optin_id );

    }

    /**
     * Retrieves the UI output for a plain text input field setting.
     *
     * @since 2.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param string $place   Placeholder text for the field.
     * @param array $attr     Array of data attributes to add to the field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_text_field( $setting, $value, $label, $desc = false, $place = false, $attr = array(), $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // If there are attributes to add, build them now.
        $attr_string = '';
        if ( ! empty( $attr ) ) {
            foreach ( $attr as $key => $val ) {
                $attr_string .= ' data-' . $key . '="' . $val . '"';
            }
        }

        // Build the HTML.
        $field  = '<div class="optin-monster-field-box optin-monster-field-box-' . $setting . ' optin-monster-clear">';
            $field .= '<p class="optin-monster-field-wrap"><label for="optin-monster-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<input type="text" id="optin-monster-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="optin_monster[' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . '' . $attr_string . ' />';
                if ( $desc ) {
                    $field .= '<br /><span class="optin-monster-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_text_field', $field, $setting, $value, $label, $this->optin_id );

    }

    /**
     * Retrieves the UI output for a hidden input field setting.
     *
     * @since 2.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param array $attr     Array of data attributes to add to the field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_hidden_field( $setting, $value, $attr = array(), $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // If there are attributes to add, build them now.
        $attr_string = '';
        if ( ! empty( $attr ) ) {
            foreach ( $attr as $key => $val ) {
                $attr_string .= ' data-' . $key . '="' . $val . '"';
            }
        }

        // Build the HTML.
        $field  = '<div class="optin-monster-field-box optin-monster-field-box-' . $setting . ' optin-monster-clear hidden">';
        $field .= '<input type="hidden" id="optin-monster-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="optin_monster[' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '" ' . $attr_string . ' />';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_hidden_field', $field, $setting, $value, $this->optin_id );

    }
    /**
     * Retrieves the UI output for a plain textarea field setting.
     *
     * @since 2.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param string $place   Placeholder text for the field.
     * @param array $attr     Array of data attributes to add to the field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_textarea_field( $setting, $value, $label, $desc = false, $place = false, $attr = array(), $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // If there are attributes to add, build them now.
        $attr_string = '';
        if ( ! empty( $attr ) ) {
            foreach ( $attr as $key => $val ) {
                $attr_string .= ' data-' . $key . '="' . $val . '"';
            }
        }

        // Build the HTML.
        $field  = '<div class="optin-monster-field-box optin-monster-field-box-' . $setting . ' optin-monster-clear">';
            $field .= '<p class="optin-monster-field-wrap"><label for="optin-monster-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<textarea id="optin-monster-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="optin_monster[' . $setting . ']" rows="5" tabindex="' . $this->tabindex . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . '' . $attr_string . '>' . $value . '</textarea>';
                if ( $desc ) {
                    $field .= '<br /><span class="optin-monster-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_textarea_field', $field, $setting, $value, $label, $this->optin_id );

    }

    /**
     * Retrieves the UI output for a checkbox setting.
     *
     * @since 2.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param array $attr     Array of data attributes to add to the field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_checkbox_field( $setting, $value, $label, $desc = false, $attr = array(), $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // If there are attributes to add, build them now.
        $attr_string = '';
        if ( ! empty( $attr ) ) {
            foreach ( $attr as $key => $val ) {
                $attr_string .= ' data-' . $key . '="' . $val . '"';
            }
        }

        // Build the HTML.
        $field  = '<div class="optin-monster-field-box optin-monster-field-box-' . $setting . ' optin-monster-clear">';
            $field .= '<p class="optin-monster-field-wrap"><label for="optin-monster-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<input type="checkbox" id="optin-monster-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="optin_monster[' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '"' . checked( $value, 1, false ) . '' . $attr_string . ' /> ';
                if ( $desc ) {
                    $field .= '<span class="optin-monster-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_checkbox_field', $field, $setting, $value, $label, $this->optin_id );

    }

    /**
     * Retrieves the UI output for a dropdown field setting.
     *
     * @since 2.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param array $data     The data to be used for option fields.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param array $attr     Array of data attributes to add to the field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_dropdown_field( $setting, $value, $data, $label, $desc = false, $attr = array(), $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // If there are attributes to add, build them now.
        $attr_string = '';
        if ( ! empty( $attr ) ) {
            foreach ( $attr as $key => $val ) {
                $attr_string .= ' data-' . $key . '="' . $val . '"';
            }
        }

        // Build the HTML.
        $field  = '<div class="optin-monster-field-box optin-monster-field-box-' . $setting . ' optin-monster-clear">';
            $field .= '<p class="optin-monster-field-wrap"><label for="optin-monster-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<select id="optin-monster-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="optin_monster[' . $setting . ']" tabindex="' . $this->tabindex . '"' . $attr_string . '>';
                foreach ( $data as $i => $info ) {
                    $field .= '<option value="' . $info['value'] . '"' . selected( $info['value'], $value, false ) . '>' . $info['name'] . '</option>';
                }
                $field .= '</select>';
                if ( $desc ) {
                    $field .= '<br /><span class="optin-monster-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_dropdown_field', $field, $setting, $value, $label, $this->optin_id );

    }

    /**
     * Retrieves the UI output for a font dropdown field setting.
     *
     * @since 2.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param array $fonts    The fonts to be used for option fields.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param array $attr     Array of data attributes to add to the field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_font_field( $setting, $value, $fonts, $label, $desc = false, $attr = array(), $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // If there are attributes to add, build them now.
        $attr_string = '';
        if ( ! empty( $attr ) ) {
            foreach ( $attr as $key => $val ) {
                $attr_string .= ' data-' . $key . '="' . $val . '"';
            }
        }

        // Merge in the custom font field class.
        $classes[] = 'om-font-field';

        // Build the HTML.
        $field  = '<div class="optin-monster-field-box optin-monster-field-box-' . $setting . ' optin-monster-clear">';
            $field .= '<p class="optin-monster-field-wrap"><label for="optin-monster-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<select id="optin-monster-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="optin_monster[' . $setting . ']" tabindex="' . $this->tabindex . '"' . $attr_string . '>';
                foreach ( $fonts as $font ) {
                    $field .= '<option value="' . $font . '"' . selected( $font, $value, false ) . '>' . $font . '</option>';
                }
                $field .= '</select>';
                if ( $desc ) {
                    $field .= '<br /><span class="optin-monster-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_font_field', $field, $setting, $value, $label, $this->optin_id );

    }

    /**
     * Retrieves the UI output for a field with a custom output.
     *
     * @since 2.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @return string $html   HTML representation of the data.
     */
    public function get_custom_field( $setting, $value, $label, $desc = false ) {

        // Build the HTML.
        $field = '<div class="optin-monster-field-box optin-monster-field-box-' . $setting . ' optin-monster-clear">';
            $field .= '<p class="optin-monster-field-wrap"><label for="optin-monster-field-' . $setting . '">' . $label . '</label></p>';
            $field .= $value;
            if ( $desc ) {
                $field .= '<br /><span class="optin-monster-field-desc">' . $desc . '</span>';
            }
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_custom_field', $field, $setting, $value, $label, $this->optin_id );

    }

    /**
     * Retrieves the UI output for a field header in the customizer panel.
     *
     * @since 2.0.0
     *
     * @param string $text  The text to display for the header.
     * @param string $field The field to target.
     * @param bool $first   Whether or not the field is the first field or not.
     * @return string $text HTML representation of the data.
     */
    public function get_field_header( $text, $field, $first = false ) {

        // Build the HTML.
        $class = $first ? ' optin-monster-header-first' : '';
        $html  = '<div class="optin-monster-field-header optin-monster-field-header-' . $field . $class . ' optin-monster-clear">';
            $html .= '<h4>' . $text . '</h4>';
        $html .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_field_header', $html, $text, $first );

    }

    /**
     * Retrieves helpful tips for the user when editing optins.
     *
     * @since 2.0.1.6
     *
     * @return string The random tip for the user.
     */
    public function get_help_tips() {

        // If the user has closed out the tips, don't display them.
        $user_id  = get_current_user_id();
        $has_tips = (bool) get_user_meta( $user_id, '_om_hide_tips', true );
        if ( $has_tips ) {
            return '';
        }

        // Build an array of helpful tips.
        $tip  = '';
        $tips = array(
            0 => __( 'To edit the content of an optin, locate it in the Preview Frame and then click on the text - it\'s that easy!', 'optin-monster' ),
            1 => __( 'You can hide optins from logged-in users by clicking on the Configuration tab and selecting the "Hide for Logged-in Users?" option.', 'optin-monster' ),
            2 => __( 'Some optins support images. On those optins, click on Archie (the monster!) to add your image from your Media Library.', 'optin-monster' ),
            3 => __( 'You can load popup optins with the click of a link or button! <a href="http://optinmonster.com/docs/how-to-manually-load-optinmonster-popup-with-click-of-a-button/" target="_blank">Click here to learn more.</a>', 'optin-monster' )
        );

        // If the user has never viewed this screen before, let them know about how to edit an optin first.
        $edit = get_user_meta( $user_id, '_om_seen_tips', true );
        if ( ! $edit ) {
            update_user_meta( $user_id, '_om_seen_tips', 1 );
            $tip = $tips[0];
        } else {
            $key = array_rand( $tips );
            $tip = $tips[ $key ];
        }

        // Build out the HTML for the tips.
        $output  = '<p class="optin-monster-field-wrap om-helpful-tips">';
            $output .= '<span class="om-helpful-tip">' . __( 'Helpful Tip', 'optin-monster' ) . '<a href="#" class="om-tip-close" title="' . esc_attr__( 'Click here to hide these tips.', 'optin-monster' ) . '">&times;</a></span>';
            $output .= $tip;
        $output .= '</p>';

        // Return the output for the tips.
        return $output;

    }

    /**
     * Retrieves the theme selection dialog for changing themes within optins.
     *
     * @since 2.0.3
     *
     * @return string HTML markup for the theme selection dialog.
     */
    public function get_theme_selection() {

        // Load the interface for grabbing optins.
        if ( ! class_exists( 'Optin_Monster_Views_New' ) ) {
            require plugin_dir_path( $this->base->file ) . 'includes/admin/views/new.php';
        }

        // Get all the themes for the optin type.
        $themes_object = Optin_Monster_Views_New::get_instance();
        $themes        = $themes_object->get_optin_themes( $this->meta['type'] );
        $active        = array();
        if ( isset( $themes[$this->meta['theme']] ) ) {
            $active[$this->meta['theme']] = $themes[$this->meta['theme']];
            unset( $themes[$this->meta['theme']] );
            $themes = array_merge( $active, $themes );
        }

        // Now create the interface for selecting optins.
        $html  = '<div class="optin-monster-themes optin-monster-clear">';
            foreach ( $themes as $id => $data ) {
                $class = $id == $this->meta['theme'] ? ' om-active-theme' : '';
                $html .= '<div class="optin-monster-theme optin-monster-theme-' . $id . $class . '" data-om-optin-theme="' . $id . '" data-om-optin-type="' . $this->meta['type'] . '">';
                    $html .= '<div class="optin-monster-theme-screenshot">';
                        $html .= '<img src="' . esc_url( $data['image'] ) . '" alt="' . esc_attr( $data['name'] ) . '" />';
                    $html .= '</div>';
                    $html .= '<h3 class="optin-monster-theme-name">' . ( $id == $this->meta['theme'] ? '<span>' . __( 'Active: ', 'optin-monster' ) . '</span>' : '' ) . $data['name'] . '</h3>';
                    $html .= '<div class="optin-monster-theme-actions"' . ( $id == $this->meta['theme'] ? ' style="display:none;"' : '' ) . '>';
                        $html .= '<a class="button button-primary om-theme-select" data-om-optin-theme="' . $id . '" data-om-optin-type="' . $this->meta['type'] . '" title="' . esc_attr__( 'Select this theme', 'optin-monster' ) . '">' . __( 'Select Theme', 'optin-monster' ) . '</a>';
                    $html .= '</div>';
                $html .= '</div>';
            }
        $html .= '</div>';

        // Return the output.
        return apply_filters( 'optin_monster_theme_selection_output', $html );

    }

    /**
     * Retrieves an optin checkbox setting.
     *
     * @since 2.0.0
     *
     * @param string $field   The meta field to retrieve.
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_checkbox_setting( $field, $setting = '', $default = 0 ) {

        return Optin_Monster_Output::get_instance()->get_checkbox_setting( $field, $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_setting( $field, $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_display_setting( $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_email_setting( $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_name_setting( $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_background_setting( $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_title_setting( $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_tagline_setting( $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_bullet_setting( $setting, $default );

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

        return Optin_Monster_Output::get_instance()->get_submit_setting( $setting, $default );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Views_Edit object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Views_Edit ) ) {
            self::$instance = new Optin_Monster_Views_Edit();
        }

        return self::$instance;

    }

}

// Load the views edit class.
$optin_monster_views_edit = Optin_Monster_Views_Edit::get_instance();