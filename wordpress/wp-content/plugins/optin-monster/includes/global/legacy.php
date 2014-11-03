<?php
/**
 * Legacy class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Legacy {

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

        // Bring the old v1 global license key variable into scope.
        global $optin_monster_license;
        $optin_monster_license = get_option( 'optin_monster_license' );

        // Load legacy and upgrade features.
        add_action( 'init', array( $this, 'legacy' ), 999 );

    }

    /**
     * Handles any legacy upgrades and features.
     *
     * @since 2.0.0
     */
    public function legacy() {

        // Run checks for different types of upgrades based on DB version.
        if ( ! get_option( 'optin_monster_upgrade_2.0' ) ) {
            $this->legacy_20();
        }

    }

    /**
     * Legacy data upgrades from anything prior to version 2.0 of OptinMonster.
     *
     * @since 2.0.0
     */
    public function legacy_20() {

        // Grab optins (but not any in cache).
        $optins = get_posts( array( 'post_type' => 'optin', 'posts_per_page' => -1 ) );
        if ( ! $optins ) {
            return;
        }

        // Loop through each optin and update meta structures.
        foreach ( (array) $optins as $optin ) {
            $meta = get_post_meta( $optin->ID, '_om_meta', true );

            // If the optin is a clone, create its own post meta field.
            if ( isset( $meta['is_clone'] ) && $meta['is_clone'] ) {
                update_post_meta( $optin->ID, '_om_is_clone', $meta['is_clone'] );
            }

            // If the optin has a clone, create its own post meta field as an array to allow unlimited split tests.
            if ( isset( $meta['has_clone'] ) && $meta['has_clone'] ) {
                $clones   = array();
                $clones[] = $meta['has_clone'];
                update_post_meta( $optin->ID, '_om_has_clone', $clones );
            }

            // Convert each theme name to the proper name for v2.
            if ( ! empty( $meta['theme'] ) ) {
	            switch ( $meta['theme'] ) {
		            case 'balance-theme' :
		            	$meta['theme'] = 'balance';
		            	break;
		            case 'bullseye-theme' :
		            	$meta['theme'] = 'bullseye';
						break;
					case 'case-study-theme' :
						$meta['theme'] = 'case-study';
						break;
					case 'chalkboard-theme' :
						$meta['theme'] = 'chalkboard';
						break;
					case 'clean-slate-theme' :
						$meta['theme'] = 'clean-slate';
						break;
					case 'postal-theme' :
						$meta['theme'] = 'postal';
						break;
					case 'target-theme' :
						$meta['theme'] = 'target';
						break;
					case 'transparent-theme' :
						$meta['theme'] = 'transparent';
						break;
					case 'whiteboard-theme' :
						$meta['theme'] = 'whiteboard';
						break;
					case 'sleek-theme' :
						$meta['theme'] = 'sleek';
						break;
					case 'tiles-theme' :
						$meta['theme'] = 'tiles';
						break;
					case 'action-theme' :
						$meta['theme'] = 'action';
						break;
					case 'banner-theme' :
						$meta['theme'] = 'banner';
						break;
					case 'fabric-theme' :
						$meta['theme'] = 'fabric';
						break;
					case 'valley-theme' :
						$meta['theme'] = 'valley';
						break;
					case 'converse-theme' :
						$meta['theme'] = 'converse';
						break;
	            }

	            // Update the meta so that the theme name is updated.
	            update_post_meta( $optin->ID, '_om_meta', $meta );
            }
        }

        // Grab options from the old v1 setting and add it to the new setting.
        $v1_option = get_option( 'optin_monster_license' );
        $v2_option = get_option( 'optin_monster' );
        if ( ! $v2_option || empty( $v2_option ) ) {
            $v2_option = Optin_Monster::default_options();
        }

        if ( ! empty( $v1_option['key'] ) ) {
            $v2_option['key'] = $v1_option['key'];
        }

        if ( ! empty( $v1_option['global_cookie'] ) ) {
            $v2_option['cookie'] = $v1_option['global_cookie'];
        }

        if ( ! empty( $v1_option['aff_link'] ) ) {
            $v2_option['affiliate_link'] = $v1_option['aff_link'];
        }

        if ( ! empty( $v1_option['aff_link_pos'] ) ) {
            $v2_option['affiliate_link_position'] = $v1_option['aff_link_pos'];
        }

        // Update the new option.
        update_option( 'optin_monster', $v2_option );

        // Update the option to be sure this does not run again.
        update_option( 'optin_monster_upgrade_2.0', true );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Legacy object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Legacy ) ) {
            self::$instance = new Optin_Monster_Legacy();
        }

        return self::$instance;

    }

}

// Load the legacy class.
$optin_monster_legacy = Optin_Monster_Legacy::get_instance();