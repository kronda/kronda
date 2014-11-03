<?php
/**
 * Views edit class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Views_New {

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
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

    }

    /**
     * Outputs the optin view.
     *
     * @since 2.0.0
     */
    public function view() {

        ?>
        <h2><?php _e( 'Add New Optin', 'optin-monster' ); ?> <a class="add-new-h2" href="<?php echo add_query_arg( 'om_view', 'overview', admin_url( 'admin.php?page=optin-monster-settings' ) ); ?>" title="<?php esc_attr_e( 'Back to optin overview', 'optin-monster' ); ?>"><?php _e( 'Back to Overview', 'optin-monster' ); ?></a></h2>
        <div class="optin-monster optin-monster-new optin-monster-clear">
            <div class="optin-monster-new-campaign optin-monster-clear">
                <h4><?php _e( 'Optin Campaign Title', 'optin-monster' ); ?> <input type="text" name="optin-campaign-title" id="optin-campaign-title" placeholder="<?php esc_attr_e( 'Enter your optin campaign title here...', 'optin-monster' ); ?>" /></h4>
            </div>
            <div class="optin-monster-new-toolbar optin-monster-clear">
                <h4><?php _e( 'Select Your Design', 'optin-monster' ); ?> <i class="fa fa-spinner fa-spin"></i></h4>
                <ul class="optin-monster-design-options">
                    <?php $types = $this->get_optin_types(); foreach ( (array) $types as $type => $name ) : $class = 'lightbox' == $type ? 'om-optin-type om-optin-type-active' : 'om-optin-type'; ?>
                    <li><a href="#" class="<?php echo $class; ?>" title="<?php esc_attr_e( $name ); ?>" data-om-optin-type="<?php echo $type; ?>"><?php echo $name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="optin-monster-themes optin-monster-clear">
                <?php echo $this->get_theme_ui( 'lightbox' ); ?>
            </div>
        </div>
        <?php

    }

    /**
     * Retrieves the optin theme selection UI.
     *
     * @since 2.0.0
     *
     * @param string $type The optin type to use for retrieving themes.
     * @return string      HTML string of the theme selection UI.
     */
    public function get_theme_ui( $type ) {

        $themes = $this->get_optin_themes( $type );
        ob_start();
        foreach ( (array) $themes as $id => $data ) {
            ?>
            <div class="optin-monster-theme optin-monster-theme-<?php echo $id; ?>" data-om-optin-theme="<?php echo $id; ?>" data-om-optin-type="<?php echo $type; ?>">
                <div class="optin-monster-theme-screenshot">
                    <img src="<?php echo esc_url( $data['image'] ); ?>" alt="<?php esc_attr( $data['name'] ); ?>" />
                </div>
                <h3 class="optin-monster-theme-name"><?php echo $data['name']; ?></h3>
                <div class="optin-monster-theme-actions">
                    <a class="button button-primary om-theme-select" data-om-optin-theme="<?php echo $id; ?>" data-om-optin-type="<?php echo $type; ?>" title="<?php esc_attr_e( 'Select this theme', 'optin-monster' ); ?>"><?php _e( 'Select Theme', 'optin-monster' ); ?></a>
                </div>
            </div>
            <?php
        }

        return ob_get_clean();

    }

    /**
     * Retrieves all of the available OptinMonster optin types.
     *
     * @since 2.0.0
     */
    public function get_optin_types() {

        $types = array(
            'lightbox' => __( 'Lightbox', 'optin-monster' )
        );

        return apply_filters( 'optin_monster_theme_types', $types );

    }

    /**
     * Retrieves all of the available OptinMonster optin themes for a specific optin type.
     *
     * @since 2.0.0
     *
     * @param string $type The optin type to use for retrieving themes.
     * @return array       An array of themes and their associated data.
     */
    public function get_optin_themes( $type ) {

        switch ( $type ) {
            case 'lightbox' :
                $themes = array(
                    'balance' => array(
                        'name'  => __( 'Balance Theme', 'optin-monster' ),
                        'image' => plugins_url( 'includes/themes/balance/images/icon.png', $this->base->file ),
                        'file'  => $this->base->file
                    ),
                    'case-study' => array(
                        'name'  => __( 'Case Study Theme', 'optin-monster' ),
                        'image' => plugins_url( 'includes/themes/case-study/images/icon.png', $this->base->file ),
                        'file'  => $this->base->file
                    ),
                    'chalkboard' => array(
                        'name'  => __( 'Chalkboard Theme', 'optin-monster' ),
                        'image' => plugins_url( 'includes/themes/chalkboard/images/icon.jpg', $this->base->file ),
                        'file'  => $this->base->file
                    ),
                    'clean-slate' => array(
                        'name'  => __( 'Clean Slate Theme', 'optin-monster' ),
                        'image' => plugins_url( 'includes/themes/clean-slate/images/icon.png', $this->base->file ),
                        'file'  => $this->base->file
                    ),
                    'bullseye' => array(
                        'name'  => __( 'Bullseye Theme', 'optin-monster' ),
                        'image' => plugins_url( 'includes/themes/bullseye/images/icon.png', $this->base->file ),
                        'file'  => $this->base->file
                    ),
                    'postal' => array(
                        'name'  => __( 'Postal Theme', 'optin-monster' ),
                        'image' => plugins_url( 'includes/themes/postal/images/icon.jpg', $this->base->file ),
                        'file'  => $this->base->file
                    ),
                    'simple' => array(
	                    'name'  => __( 'Simple Theme', 'optin-monster' ),
	                    'image' => plugins_url( 'includes/themes/simple/images/icon.jpg', $this->base->file ),
	                    'file'  => $this->base->file
                    ),
                    'target' => array(
                        'name'  => __( 'Target Theme', 'optin-monster' ),
                        'image' => plugins_url( 'includes/themes/target/images/icon.jpg', $this->base->file ),
                        'file'  => $this->base->file
                    ),
                    'transparent' => array(
                        'name'  => __( 'Transparent Theme', 'optin-monster' ),
                        'image' => plugins_url( 'includes/themes/transparent/images/icon.png', $this->base->file ),
                        'file'  => $this->base->file
                    )
                );
                break;
            default :
                $themes = array();
                break;
        }

        // Allow the themes to be filtered, and then sort them.
        $themes = apply_filters( 'optin_monster_themes', $themes, $type );
        $keys   = array_keys( $themes );
        $sorted = array();
        sort( $keys );
        foreach ( $keys as $key ) {
            $sorted[$key] = $themes[$key];
        }

        // Returned the sorted themes.
        return $sorted;

    }

    /**
     * Retrieves a specific optin theme for a specific optin type.
     *
     * @since 2.0.0
     *
     * @param string $type  The optin type to use for retrieving themes.
     * @param string $theme The theme slug to retrieve.
     * @return array        An array of data about the theme.
     */
    public function get_optin_theme( $type, $theme ) {

        $themes = $this->get_optin_themes( $type );
        return isset( $themes[$theme] ) ? apply_filters( 'optin_monster_theme', $themes[$theme], $type, $theme ) : false;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Views_New object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Views_New ) ) {
            self::$instance = new Optin_Monster_Views_New();
        }

        return self::$instance;

    }

}

// Load the views new class.
$optin_monster_views_new = Optin_Monster_Views_New::get_instance();