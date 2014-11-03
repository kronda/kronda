<?php
/**
 * OptinMonster vendor class.
 *
 * @since 2.1.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Vendor {

    /**
     * Holds the class object.
     *
     * @since 2.1.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 2.1.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 2.1.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 2.1.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

    }

    /**
     * Retrieves data from the base class.
     *
     * @since 2.1.0
     *
     * @return mixed Data from the base class.
     */
    public function get_data() {

        return $this->base->get_license_key();

    }

    /**
     * Retrieves data errors from the base class.
     *
     * @since 2.1.0
     *
     * @return mixed Data errors from the base class.
     */
    public function get_data_errors() {

        return $this->base->get_license_key_errors();

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.1.0
     *
     * @return object The Optin_Monster_Vendor object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Vendor ) ) {
            self::$instance = new Optin_Monster_Vendor();
        }

        return self::$instance;

    }

}

// Load the vendor class.
$optin_monster_vendor = Optin_Monster_Vendor::get_instance();