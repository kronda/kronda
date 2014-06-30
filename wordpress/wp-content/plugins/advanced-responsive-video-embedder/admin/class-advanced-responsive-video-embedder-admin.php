<?php
/**
 * Plugin Name.
 *
 * @package   Advanced_Responsive_Video_Embedder_Admin
 * @author    Nicolas Jonas
 * @license   GPL-3.0+
 * @link      http://example.com
 * @copyright 2013 Nicolas Jonas
 */

/*****************************************************************************

Copyright (c) 2013 Nicolas Jonas
Copyright (C) 2013 Tom Mc Farlin and WP Plugin Boilerplate Contributors

This file is part of Advanced Responsive Video Embedder.

Advanced Responsive Video Embedder is free software: you can redistribute it
and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Advanced Responsive Video Embedder is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
Public License for more details.

You should have received a copy of the GNU General Public License along with
Advanced Responsive Video Embedder.  If not, see
<http://www.gnu.org/licenses/>.

_  _ ____ _  _ ___ ____ ____ _  _ ___ _  _ ____ _  _ ____ ____  ____ ____ _  _ 
|\ | |___  \/   |  | __ |___ |\ |  |  |__| |___ |\/| |___ [__   |    |  | |\/| 
| \| |___ _/\_  |  |__] |___ | \|  |  |  | |___ |  | |___ ___] .|___ |__| |  | 

*******************************************************************************/

class Advanced_Responsive_Video_Embedder_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    2.6.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 *
		 */
		$plugin = Advanced_Responsive_Video_Embedder::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
	
		//* Display a notice that can be dismissed
		add_action( 'admin_init',    array( $this, 'admin_notice_ignore') );
		add_action( 'admin_notices', array( $this, 'admin_notice') );

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'media_buttons', array( $this, 'add_media_button'), 11 );

		// Only loaded on admin pages with editor
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_footer', array( $this, 'print_dialog' ) );
	}

	/**
	 *
	 * @since 4.3.0
	 */
	public function print_dialog() {

		if ( $this->admin_page_has_post_editor() ) {

			include_once( 'views/admin-dialog.php' );
		}
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {

		include_once( 'views/admin.php' );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     2.6.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( $this->admin_page_has_post_editor() ) {

			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Advanced_Responsive_Video_Embedder::VERSION );
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( $this->admin_page_has_post_editor() ) {

			wp_enqueue_script(
				$this->plugin_slug . '-admin-script',
				plugins_url( 'assets/js/admin.js', __FILE__ ),
				array( 'jquery' ),
				Advanced_Responsive_Video_Embedder::VERSION,
				true
			);

			$plugin = Advanced_Responsive_Video_Embedder::get_instance();
			
			$regex_list = $plugin->get_regex_list();

			foreach ( $regex_list as $provider => $regex ) {

	            if ( $provider != 'ign' ) {

	            	$regex = str_replace( array( 'https?://(?:www\.)?', 'http://' ), '', $regex );
	            }

	            $regex_list[$provider] = $regex;

	        }

			wp_localize_script( 'jquery', 'arve_regex_list', $regex_list );
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Advanced Responsive Video Embedder Settings', $this->plugin_slug ),
			__( 'A.R. Video Embedder Settings', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		$extra_links = array(
			'settings'   => sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . $this->plugin_slug ),                        __( 'Settings', $this->plugin_slug ) ),
			'contribute' => sprintf( '<a href="%s">%s</a>', 'http://nextgenthemes.com/plugins/advanced-responsive-video-embedder/#contribute',    __( 'Contribute', $this->plugin_slug ) ),
			'donate'     => sprintf( '<a href="%s">%s</a>', 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UNDSCARF3ZPBC', __( 'Donate', $this->plugin_slug ) ),
		);

		return array_merge( $extra_links, $links );

	}

#<a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="content" title="Dateien hinzufügen">
#<span class="wp-media-buttons-icon"></span> Dateien hinzufügen</a>

	/**
	 * Action to add a custom button to the content editor
	 *
	 * @since 4.3.0
	 */
	public function add_media_button() {

		// The ID of the container I want to show in the popup
		$popup_id = 'arve-form';

		// Our popup's title
		$title = 'Advanced Responsive Video Embedder Shortcode Creator';

		// Append the icon
		printf(
			'<a href="%1$s" id="arve-btn" class="button add_media thickbox" title="%2$s"><span class="wp-media-buttons-icon arve-icon"></span> %3$s</a>',
			esc_url( '#TB_inline?width=640&height=400&inlineId=' . $popup_id ),
			esc_attr( $title ),
			esc_html__( 'Embed Video', $this->plugin_slug )
		);
	}

	/**
	 * 
	 *
	 * @since    2.6.0
	 */
	public function register_settings() {
		register_setting( 'arve_plugin_options', 'arve_options', array( $this, 'validate_options' ) );
	}

	/**
	 * 
	 *
	 * @since    2.6.0
	 */
	public function validate_options( $input ) {
		
		//* Reset options by deleting the options and returning nothing will cause the reset/defaults of all options at the init options function
		if( isset( $input['reset'] ) ) {
			return;
		}

		$output = array();

		$output['mode']               = wp_filter_nohtml_kses( (string) $input['mode'] );
		$output['custom_thumb_image'] = esc_url_raw( $input['custom_thumb_image'] );

		$output['fakethumb']      = isset( $input['fakethumb'] );
		$output['autoplay']       = isset( $input['autoplay'] );
		
		if( (int) $input['thumb_width'] > 50 ) {
			$output['thumb_width'] = (int) $input['thumb_width'];
		}

		if( (int) $input['align_width'] > 200 ) {
			$output['align_width'] = (int) $input['align_width'];
		}

		if( (int) $input['video_maxwidth'] > 50 ) {
			$output['video_maxwidth'] = (int) $input['video_maxwidth'];
		} else {
			$output['video_maxwidth'] = '';
		}

		if( (int) $input['transient_expire_time'] >= 1 ) {
			$output['transient_expire_time'] = (int) $input['transient_expire_time'];
		}

		foreach ( $input['shortcodes'] as $key => $var ) {
		
			$var = preg_replace('/[_]+/', '_', $var );	// remove multiple underscores
			$var = preg_replace('/[^A-Za-z0-9_]/', '', $var );	// strip away everything except a-z,0-9 and underscores
			
			if ( strlen($var) < 3 )
				continue;
			
			$output['shortcodes'][$key] = $var;
		}

		foreach ( $input['params'] as $key => $var ) {
		
			$plugin = Advanced_Responsive_Video_Embedder::get_instance();
			$var = $plugin->parse_parameters( $var );
			
			$output['params'][$key] = $var;
		}		
		
		return $output;
	}

	/**
	 * Display a notice that can be dismissed
	 *
	 * @since     3.0.0
	 */
	function admin_notice() {

		global $current_user ;
		$user_id = $current_user->ID;

		$current_date = current_time( 'timestamp' );
		$install_date = get_option( 'arve_install_date', $current_date );

		#delete_user_meta( $user_id, 'arve_ignore_admin_notice' );
		#$install_date = strtotime('-7 days', $current_date);
		
		if ( ! current_user_can( 'delete_plugins' ) || get_user_meta( $user_id, 'arve_ignore_admin_notice' ) || ( $current_date - $install_date ) < 604800 ) {
			return;
		}

		$message  = __( 'The Advanced Responsive Video Embedder Plugin was activated on this site for over a week now. I hope you like it.', $this->plugin_slug ) . '<br>';
		$message .= sprintf(
			__( 'It is always nice when people show their appreciation for a plugin by <a href="%s" target="_blank">contributing</a> or <a href="%s" target="_blank">donating</a>. Thank you!', $this->plugin_slug ),
			'http://nextgenthemes.com/plugins/advanced-responsive-video-embedder/#contribute',
			'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UNDSCARF3ZPBC'
		);

		$dismiss = sprintf( '<a class="alignright" href="?arve_nag_ignore=1">%s</a>', __( 'Dismiss', $this->plugin_slug ) );

		echo '<div class="updated"><p><big>' . $message . $dismiss . '</big><br class="clear"></p></div>';
	}

	/**
	 * Maybe dismiss admin Notice
	 *
	 * @since     3.0.0
	 */
	function admin_notice_ignore() {
		global $current_user;

		$user_id = $current_user->ID;
		//* If user clicks to ignore the notice, add that to their user meta
		if ( isset( $_GET['arve_nag_ignore'] ) && '1' == $_GET['arve_nag_ignore'] ) {
			add_user_meta( $user_id, 'arve_ignore_admin_notice', 'true', true );
		}
	}

	/**
	 * Maybe dismiss admin Notice
	 *
	 * @since     4.3.0
	 */
	function admin_page_has_post_editor() {

		global $pagenow;

	    if ( empty ( $pagenow ) ) {

	        return false;
	    }

	    if ( ! in_array( $pagenow, array ( 'post-new.php', 'post.php' ) ) ) {
	        
	        return false;
	    }

	    return post_type_supports( get_current_screen()->post_type, 'editor' );
	}	
}