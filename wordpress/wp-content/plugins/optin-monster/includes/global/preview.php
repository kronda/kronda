<?php
/**
 * Preview class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Preview {

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
     * Holds the preview optin ID.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $optin_id;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Possibly prepare the preview customizer frame.
        add_action( current_filter(), array( $this, 'maybe_prepare_preview' ) );

    }

    /**
     * Prepares the iframe preview for customizing an optin.
     *
     * @since 2.0.0
     */
    public function maybe_prepare_preview() {

        if ( isset( $_GET['om_logged_out'] ) && $_GET['om_logged_out'] ) {
            $this->prepare_preview_user();
        }

        if ( isset( $_GET['om_preview_frame'] ) && $_GET['om_preview_frame'] ) {
            $this->prepare_preview_frame();
            $this->do_preview();
        }

    }

    /**
     * Nullifies the current user in the preview frame.
     *
     * @since 2.0.0
     */
    public function prepare_preview_user() {

        $current_user = wp_get_current_user();

        if ( ! empty( $current_user ) && $current_user->ID > 0 ) {
            wp_set_current_user( 0 );
        }

        // Fire a hook to allow 3rd parties to modify other user data if necessary.
        do_action( 'optin_monster_preview_user' );

    }

    /**
     * Sets all the properties for a preview frame instance.
     *
     * @since 2.0.0
     */
    public function prepare_preview_frame() {

        // Set the optin preview ID.
        $this->optin_id = absint( $_GET['om_preview_optin'] );
        $this->optin    = get_post( $this->optin_id );
        $this->meta     = get_post_meta( $this->optin_id, '_om_meta', true );
        $this->theme    = Optin_Monster_Output::get_instance()->get_optin_monster_theme( $this->meta['theme'], $this->optin_id, true );

    }

    /**
     * Allows customizations to be done within a preview frame instance.
     *
     * @since 2.0.2.1
     */
    public function do_preview() {

        // Possibly filter the query and page headers to allow our private, internal preview page to show.
        add_action( 'send_headers', array( $this, 'preview_headers' ) );
        add_filter( 'the_posts', array( $this, 'preview_query' ), 10, 2 );
        add_action( 'wp_head', array( $this, 'preview_meta' ) );

        // Enqueue styles and scripts to make live previewing and editing happen.
        add_action( 'wp_enqueue_scripts', array( $this, 'preview_styles' ), 0 );
        add_action( 'wp_enqueue_scripts', array( $this, 'preview_scripts' ), 0 );

        // Optionally add styles to force hash jumps to have padding.
        if ( isset( $this->meta['type'] ) && ( 'post' == $this->meta['type'] || 'sidebar' == $this->meta['type'] ) ) {
            add_action( 'wp_head', array( $this, 'preview_hash_fix' ) );
        }

        // Fire a hook to allow 3rd parties to add their own preview data.
        do_action( 'optin_monster_preview_frame' );

    }

    /**
     * Adds the proper Headers for our preview frame.
     *
     * @since 2.0.2
     *
     * @param object $query The query object.
     */
    public function preview_headers( $query ) {

	    // If our queried request does not match our preview page, return early.
	    $preview_id = absint( get_option( 'optin_monster_preview_page' ) );
	    if ( isset( $query->request ) && 'optin-monster-preview-page' !== $query->request || isset( $query->query_vars['page_id'] ) && $preview_id != $query->query_vars['page_id'] ) {
		    return;
	    }

	    // Set the nofollow, noindex robots header.
	    header( "X-Robots-Tag: noindex, nofollow", true );

    }

    /**
     * Filters the query to allow the preview page to be displayed.
     *
     * @since 2.0.2
     *
     * @param array $posts  Posts that have been selected.
     * @param object $query The query object.
     */
    public function preview_query( $posts, $query ) {

	    // Return early if not the main query.
	    if ( ! $query->is_main_query() ) {
		    return $posts;
	    }

	    // If our queried object ID does not match the preview page ID, return early.
	    $preview_id = absint( get_option( 'optin_monster_preview_page' ) );
	    $queried    = $query->get_queried_object_id();
	    if ( $queried && $queried != $preview_id && isset( $query->query_vars['page_id'] ) && $preview_id != $query->query_vars['page_id'] ) {
		    return $posts;
	    }

	    // If we have reached this point, we know we are on our preview page. Backfill the query with our post object.
	    $preview 			  = get_post( absint( $preview_id ) );
	    $preview->post_status = 'publish';
	    $posts   			  = (array) $posts;
	    $posts[] 			  = $preview;

	    // Return our posts.
	    return $posts;

    }

    /**
     * Adds the proper meta tags for our preview frame.
     *
     * @since 2.0.2
     */
    public function preview_meta() {

	    echo '<meta name="robots" content="noindex, nofollow">' . "\n";

    }

    /**
     * Adds proper styles to the iframe to ensure it is fully visible when previewing.
     *
     * @since 2.0.0
     */
    public function preview_styles() {

        wp_enqueue_style( 'buttons' );
        wp_enqueue_style( $this->base->plugin_slug . '-preview', plugins_url( 'assets/css/preview.css', $this->base->file ), array(), $this->base->version );

    }

    /**
     * Enqueues scripts necessary for live preview and edit functionality.
     *
     * @since 2.0.0
     */
    public function preview_scripts() {

        wp_enqueue_script( $this->base->plugin_slug . '-google-fonts', '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js' );
        wp_enqueue_script( $this->base->plugin_slug . '-api', plugins_url( 'assets/js/api.js', $this->base->file ), array( 'jquery' ), $this->base->version );
        wp_enqueue_script( $this->base->plugin_slug . '-postmessage', plugins_url( 'assets/js/postmessage.js', $this->base->file ), array( 'jquery' ), $this->base->version );
        wp_enqueue_script( $this->base->plugin_slug . '-ckeditor', plugins_url( 'assets/ckeditor/ckeditor.js', $this->base->file ), array( 'jquery', $this->base->plugin_slug . '-postmessage' ), $this->base->version );
        wp_enqueue_script( $this->base->plugin_slug . '-color', plugins_url( 'assets/js/color.js', $this->base->file ), array( 'jquery', $this->base->plugin_slug . '-postmessage', $this->base->plugin_slug . '-ckeditor' ), $this->base->version );
        wp_enqueue_script( $this->base->plugin_slug . '-preview', plugins_url( 'assets/js/preview.js', $this->base->file ), array( 'jquery', $this->base->plugin_slug . '-postmessage', $this->base->plugin_slug . '-ckeditor', $this->base->plugin_slug . '-color' ), $this->base->version );
        wp_localize_script(
            $this->base->plugin_slug . '-preview',
            'optin_monster_preview',
            array(
                'ajax'    => admin_url( 'admin-ajax.php' ),
                'ckfonts' => implode( ';', Optin_Monster_Output::get_instance()->get_supported_fonts() ),
                'ckpath'  => plugins_url( 'assets/ckeditor/', $this->base->file ),
                'config'  => plugins_url( 'assets/js/ckeditor.js', $this->base->file ),
                'fonts'   => urlencode( implode( '|', Optin_Monster_Output::get_instance()->get_supported_fonts( true ) ) ),
                'google'  => set_url_scheme( '//fonts.googleapis.com/css?family=' ),
                'id'      => isset( $_GET['om_preview_optin'] ) ? absint( $_GET['om_preview_optin'] ) : 0,
                'remove'  => __( 'Are you sure you want to remove this image from the optin?', 'optin-monster' ),
                'optin'   => $this->optin->post_name,
                'optinjs' => str_replace( '-', '_', $this->optin->post_name )
            )
        );

    }

    /**
     * Fixes hash jumping with things like fixed headers.
     *
     * @since 2.0.0
     */
    public function preview_hash_fix() {

        ?>
        <style type="text/css">#om-<?php echo $this->optin->post_name; ?>:before { display: block; content: ' '; margin-top: -100px; height: 100px; visibility: hidden; }</style>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Preview object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Preview ) ) {
            self::$instance = new Optin_Monster_Preview();
        }

        return self::$instance;

    }

}

// Load the preview class.
$optin_monster_preview = Optin_Monster_Preview::get_instance();