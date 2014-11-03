<?php
/**
 * Admin UI settings class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_UI_Integrations {

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
     * Holds the tab slug.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $tab = 'integrations';

    /**
     * Array of all available providers
     *
     * @since 2.0.0
     *
     * @var array
     */
    public $all_providers = array();

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Load available providers
        $common              = Optin_Monster_Common::get_instance();
        $this->all_providers = $this->parse_providers( $common->get_email_providers() );

        // Display the UI.
        $this->display();

    }

    /**
     * Displays the UI view.
     *
     * @since 2.0.0
     */
    public function display() {

        $registered = Optin_Monster_Common::get_instance()->get_email_providers( true );
        ?>
        <div id="optin-monster-settings-<?php echo $this->tab; ?>">
        <?php if ( empty( $registered ) ) : ?>
            <div class="error below-h2"><p><?php _e( 'You have not registered any email service provider integrations.', 'optin-monster' ); ?></p></div>
        <?php else : ?>
            <div class="optin-integrations om-clearfix">
                <?php $i = 1; foreach ( $registered as $provider => $array ) : $class = 0 == $i%3 ? ' last' : ''; ?>
                    <?php if ( 1 == $i || 1 == $i%3 ) : ?>
                        <div class="optin-integration-wrap om-clearfix">
                    <?php endif; ?>
                    <div class="optin-integration <?php echo $provider . $class; ?>">
                        <div class="logo"></div>
                        <ul class="integration om-clearfix">
                            <?php foreach ( $array as $hash => $data ) : ?>
                                <li><span class="name"><?php echo $data['label']; ?></span> <a class="button button-secondary button-small delete-integration" href="#" title="<?php esc_attr_e( 'Delete Integration', 'optin-monster' ); ?>" data-provider="<?php echo $provider; ?>" data-hash="<?php echo $hash; ?>"><?php _e( 'Delete', 'optin-monster' ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php if ( 0 == $i%3 || $i == count( $registered ) ) : ?>
                        </div>
                    <?php endif; $i++; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Posttype object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_UI_Integrations ) ) {
            self::$instance = new Optin_Monster_UI_Integrations();
        }

        return self::$instance;

    }

    /**
     * Parses the providers into a better format for display.
     *
     * @since 2.0.0
     *
     * @param array $providers Array of registered providers.
     * @return array $parsed Array of parsed providers.
     */
    protected function parse_providers( $providers ) {

        $parsed = array();

        foreach ( $providers as $provider ) {
            $parsed[$provider['value']] = $provider['name'];
        }

        return $parsed;

    }

}

// Load the admin UI settings class.
$optin_monster_ui_integrations = Optin_Monster_UI_Integrations::get_instance();