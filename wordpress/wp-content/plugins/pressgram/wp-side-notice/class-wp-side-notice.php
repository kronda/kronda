<?php
if ( ! class_exists( 'WP_Side_Notice' ) ) {
	/**
	 * WP Side Notice Class
	 *
	 * Adds side notices to the WP admin
	 *
	 * @package WP Side Notice
	 * @author  UaMV
	 */
	class WP_Side_Notice {

		/*---------------------------------------------------------------------------------*
		 * Attributes
		 *---------------------------------------------------------------------------------*/

		/**
		 * Notices.
		 *
		 * @since    1.0
		 *
		 * @var      array
		 */
		public $notices;

		/**
		 * Version
		 *
		 * @since    1.0
		 *
		 * @var      array
		 */
		public $version;

		/**
		 * Notices.
		 *
		 * @since    1.0
		 *
		 * @var      array
		 */
		public $prefix;

		/**
		 * Height of notice div.
		 *
		 * @since    1.0
		 *
		 * @var      array
		 */
		public $height;


		/*---------------------------------------------------------------------------------*
		 * Consturctor
		 *---------------------------------------------------------------------------------*/

		/**
		 * Initialize the plugin by setting localization, filters, and administration functions.
		 *
		 * @since     1.0
		 */
		public function __construct( $prefix, $height = '200' ) {

			$this->prefix = $prefix;

			$this->height = $height;

			$this->notices = get_option( $this->prefix . '_side_notices', array() );

			// Load the administrative Stylesheets and JavaScript
			add_action( 'admin_enqueue_scripts', WP_Side_Notice::add_stylesheets_and_javascript() );

			// Process responses to the notices
			add_action( 'admin_init', WP_Side_Notice::process_response() );

		} // end constructor

		/*---------------------------------------------------------------------------------*
		 * Public Functions
		 *---------------------------------------------------------------------------------*/

		/**
		 * Registers the plugin's administrative stylesheets and JavaScript
		 *
		 * @since    2.1.3
		 */
		public function add_stylesheets_and_javascript() {
			wp_enqueue_style( 'wp-side-notice-style', plugin_dir_url( __FILE__ ) . 'wpsn.css', array(), $this->version, 'screen' );
			wp_enqueue_script( 'wp-side-notice-js', plugin_dir_url( __FILE__ ) . 'wpsn.js' , array(), $this->version, FALSE );
		} // end add_stylesheets_and_javascript

		/**
		 * Add a notice to the array during plugin activation.
		 *
		 * @since    1.0
		 */
		public function add( $args ) {

			$this->notices[ $args['name'] ] = $args;
			update_option( $this->prefix . '_side_notices', $this->notices );

		}

		/**
		 * Remove notices from the array.
		 *
		 * @since    1.0
		 */
		public function remove( $args = 'all' ) {

			if ( 'all' == $args ) {
				foreach ( $this->notices as $name => $notice ) {
					unset( $this->notices[ $name ] );
				}
			} else {
				foreach ( $args as $name ) {
					unset( $this->notices[ $name ] );
				}
			}

			update_option( $this->prefix . '_side_notices', $this->notices );

		}

		/**
		 * Returns the active plugin notices for display on the settings page summary.
		 *
		 * @since    1.0
		 */
		public function display() {

			global $pagenow;
			$current_user = wp_get_current_user();

			// Get the notice options from the user
			$user_notices = get_user_meta( $current_user->ID, $this->prefix . '_user_side_notices', TRUE );

			// If not yet set, then set the usermeta as an array
			! is_array( $user_notices ) ? add_user_meta( $current_user->ID, $this->prefix . '_user_side_notices', array() ) : FALSE;

			// Create specific notices if they do not exist, otherwise set to current notice state
			foreach ( $this->notices as $name => $notice ) {
				if ( ! isset( $user_notices[ $name ] ) ) {
					$user_notices[ $name ] = array(
						'trigger' => TRUE,
						'time'    => $notice['time'],
						);
				}

			}

			// Update the users meta
			update_user_meta( $current_user->ID, $this->prefix . '_user_side_notices', $user_notices );

			// Set the variable that will hold the html
			$html = '<div class="wpsn-outer-container" style="height:' . $this->height . '">';
			
			// Loop though the notices
			foreach ( $this->notices as $name => $notice ) {

				// Check that the notice is supposed to be displayed on this page and that it is active for the user
				if ( in_array( $pagenow, $notice['location'] ) && $user_notices[ $name ]['trigger'] && $user_notices[ $name ]['time'] < time() ) {
					$html .= '<div id="wpsn-' . $name . '-container" style="height:' . esc_attr( $notice['style']['height'] ) . ';">';
						$html .= ( (float) $GLOBALS['wp_version'] < 3.8 ) ? '' : '<style type="text/css">#wpsn-' . $name . '-container div.wpsn-notice:before{content:\'\\' . $notice['style']['icon'] . '\';}</style>';
						$html .= '<div class="wpsn-notice update-nag" style="border-left: 4px solid ' . esc_attr( $notice['style']['color'] ) . ';">';
							$html .= '<p>';
								$html .= apply_filters( $name . '_side_notice_content', $notice['content'], $notice, $current_user );
								$html .= $this->get_dismissals( $name );
							$html .= '</p>';
						$html .= '</div>';
					$html .= '</div>';

				}

			}

			$html .= '</div>';

			echo $html;

		} // end display_notices


		/**
		 * Get any assigned dismissal notices
		 *
		 * @since    2.1.0
		 */
		public function get_dismissals( $name ) {

			global $pagenow;

			$dismissals = explode( ',', $this->notices[ $name ]['dismiss'] );

			$html = '<span style="float:right">';

			$j = 1;

			foreach ( $dismissals as $dismissal ) {
				$j > 1 ? $html .= '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;' : FALSE;
				if ( 'week' == $dismissal ) {
					$html .= __( '<a href="' . wp_nonce_url( add_query_arg( array( $this->prefix . '-wpsn-action' => 'dismiss', 'notice' => $name, 'duration' => 'week' ) ), 'wpsn-notice' ) . '">Hide For One Week</a>', 'wpsn-locale' );
					$j ++;
				} elseif ( 'month' == $dismissal ) {
					$html .= __( '<a href="' . wp_nonce_url( add_query_arg( array( $this->prefix . '-wpsn-action' => 'dismiss', 'notice' => $name, 'duration' => 'month' ) ), 'wpsn-notice' ) . '">Hide For One Month</a>', 'wpsn-locale' );
					$j ++;
				} elseif ( 'forever' == $dismissal ) {
					$html .= __( '<a href="' . wp_nonce_url( add_query_arg( array( $this->prefix . '-wpsn-action' => 'dismiss', 'notice' => $name, 'duration' => 'forever' ) ), 'wpsn-notice' ) . '">Dismiss</a>', 'wpsn-locale' );
					$j ++;
				} elseif ( 'undismiss' == $dismissal ) {
					$html .= __( '<a href="' . wp_nonce_url( add_query_arg( array( $this->prefix . '-wpsn-action' => 'dismiss', 'notice' => $name, 'duration' => 'undismiss' ) ), 'wpsn-notice' ) . '">Reactivate Notices</a></span>', 'wpsn-locale' );
					$j ++;
				}
			}

			$html .= '</span>';

			return $html;

		}

		/**
		 * Process any responses to the displayed notices.
		 *
		 * @since    2.1.0
		 */
		public function process_response() {
			
			// Check if user has responded to notice
			if ( isset( $_GET[ $this->prefix . '-wpsn-action' ] ) ) {
				
				$current_user = wp_get_current_user();

				// Get the notice options from the user
				$user_notices = get_user_meta( $current_user->ID, $this->prefix . '_user_side_notices', TRUE );

				// If they've postponed the review and duration is set
				if ( 'dismiss' == $_GET[ $this->prefix . '-wpsn-action' ] && isset( $_GET['duration'] ) && isset( $_GET['notice'] ) && check_admin_referer( 'wpsn-notice' ) ) {
					
					switch ( $_GET['duration'] ) {
						case 'week':
							$user_notices[ $_GET['notice'] ]['time'] = time() + 604800;
							break;
						case 'month':
							$user_notices[ $_GET['notice'] ]['time'] = time() + 2592000;
							break;
						case 'forever':
							$user_notices[ $_GET['notice'] ]['trigger'] = FALSE;
							break;
						case 'undismiss':
							foreach ( $user_notices as $name => $notice ) {
								$user_notices[ $name ]['trigger'] = TRUE;
								$user_notices[ $name ]['time'] = time() - 5;
							}
							break;
						default:
							break;
					}

					// Update the option
					update_user_meta( $current_user->ID, $this->prefix . '_user_side_notices', $user_notices );
				}

			}

		} // end process_notice_response
	}
}