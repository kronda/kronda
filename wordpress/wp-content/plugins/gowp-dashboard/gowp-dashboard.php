<?php

/*
Plugin Name: Support Dashboard
Description: View details of automatic services associated with your support subscription (core/plugin updates, backups, security scan results, etc)
Version: 2.1.5
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'GOWP_PLUGIN_BASE', __FILE__ );

/* BRANDING */

	// Define constants

		$plugin_data = get_file_data( GOWP_PLUGIN_BASE, array( 'Plugin Name' => 'Plugin Name', 'Description' => 'Description', 'Version' => 'Version' ) );

		// Menu item
		if ( ! defined( 'GOWP_PLUGIN_MENU' ) ) {
			if ( get_option( 'gowp_enable_whitelabel' ) ) {
				if ( $gowp_whitelabel_menu = get_option( 'gowp_whitelabel_menu' ) ) {
					define( 'GOWP_PLUGIN_MENU', $gowp_whitelabel_menu );
				} else {
					define( 'GOWP_PLUGIN_MENU', "Support" );
				}
			} else {
				define( 'GOWP_PLUGIN_MENU', "GoWP" );
			}
		}

		// Page title
		if ( ! defined( 'GOWP_PLUGIN_TITLE' ) ) {
			if ( get_option( 'gowp_enable_whitelabel' ) ) {
				if ( $gowp_whitelabel_title = get_option( 'gowp_whitelabel_title' ) ) {
					define( 'GOWP_PLUGIN_TITLE', $gowp_whitelabel_title );
				} else {
					define( 'GOWP_PLUGIN_TITLE', "Support Dashboard" );
				}
			} else {
				define( 'GOWP_PLUGIN_TITLE', "GoWP Dashboard" );
			}
		}

		// Plugin name
		if ( ! defined( 'GOWP_PLUGIN_NAME' ) ) {
			if ( get_option( 'gowp_enable_whitelabel' ) ) {
				if ( $gowp_whitelabel_plugin = get_option( 'gowp_whitelabel_plugin' ) ) {
					define( 'GOWP_PLUGIN_NAME', $gowp_whitelabel_plugin );
				} else {
					define( 'GOWP_PLUGIN_NAME', "Support Dashboard" );
				}
			} else {
				define( 'GOWP_PLUGIN_NAME', "GoWP Dashboard" );
			}
		}

		// Plugin author
		if ( ! defined( 'GOWP_PLUGIN_AUTHOR' ) ) {
			if ( get_option( 'gowp_enable_whitelabel' ) ) {
				if ( $gowp_whitelabel_author = get_option( 'gowp_whitelabel_author' ) ) {
					define( 'GOWP_PLUGIN_AUTHOR', $gowp_whitelabel_author );
				} else {
					define( 'GOWP_PLUGIN_AUTHOR', '' );
				}
			} else {
				define( 'GOWP_PLUGIN_AUTHOR', "GoWP" );
			}
		}

		// Plugin description
		if ( ! defined( 'GOWP_PLUGIN_DESCRIPTION' ) ) {
			if ( get_option( 'gowp_enable_whitelabel' ) ) {
				if  ( $gowp_whitelabel_description = get_option( 'gowp_whitelabel_description' ) ) {
					define( 'GOWP_PLUGIN_DESCRIPTION', $gowp_whitelabel_description );
				} else {
					define( 'GOWP_PLUGIN_DESCRIPTION', $plugin_data['Description'] );
				}
			} else {
				define( 'GOWP_PLUGIN_DESCRIPTION', "View details of automatic services associated with your GoWP support subscription (core/plugin updates, backups, security scan results, etc)" );
			}
		}

		// Plugin URL
		if ( ! defined( 'GOWP_PLUGIN_URL' ) ) {
			if ( get_option( 'gowp_enable_whitelabel' ) ) {
				define( 'GOWP_PLUGIN_URL', '' );
			} else {
				define( 'GOWP_PLUGIN_URL', 'https://www.gowp.com' );
			}
		}

		// Menu icon
		if ( ! defined( 'GOWP_ICON' ) ) {
			if ( get_option( 'gowp_enable_whitelabel' ) ) {
				if ( $gowp_whitelabel_icon = get_option( 'gowp_whitelabel_icon' ) ) {
					define( 'GOWP_ICON', $gowp_whitelabel_icon );
				} else {
					define( 'GOWP_ICON', 'dashicons-sos' );
				}
			} else {
				define( 'GOWP_ICON',
					'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbW
					FnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2
					VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUC
					BDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPS
					JodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9Ii
					IgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS
					94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIH
					htcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6Qj
					I4QkU3RjJGQUZDMTFFM0FGOEI4ODc3NDhBMEE5QzkiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjI4QkU3RjNGQUZDMTFFM0
					FGOEI4ODc3NDhBMEE5QzkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCMjhCRTdGMEZBRk
					MxMUUzQUY4Qjg4Nzc0OEEwQTlDOSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCMjhCRTdGMUZBRkMxMUUzQUY4Qjg4Nzc0OE
					EwQTlDOSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PkW+9y
					YAAAKBSURBVHjarJTbaxNBFId/s9ncaltjTUKspMR4w0ttoaYISgpS+qZQwRsGH3wRKoJPFfsHiOCD1guiLyJSoYIg9kWItIogYl
					sJiuCtlrRe0qQlJs2tye6OZ2osqSRpLA587Ozs2Y+zZ+Ys45xDjIG3HpQYB4hbhLVUwL7G4YW5hKXHZ+IGKhyVCMPEs/8lPA7wR5
					yrfprfJTzLFdolJo9nlNk7mjLhMfIw4plxHwd/xSBdX4aQPUnmoi6HqRpe1yWsru/DRpsPWSUCknZRwPl/EXZyqI1mlsI2Zy86vn
					gxMOtCq+sy1lmPITkXFDEnSyVTbPGgqmWwva4R/SEDRkZv4vl0ABcmYtD0TajSzR+zOqK9UqGF6odwKoQWqwPM3Y6hdwE8nAzBaZ
					agaNqfuFX5q7yU8IXEjBhLTsGWvIhA6wZc3XsUD3bEMPajF1xnFzEzhJvoJ04vqn6RTqkiQmC6mngmiC21LjRUuxGYGUYkp6DW4I
					DGc6JzrhFviNfUKS3lMkwRQ+Aqao1OBNMxPJ16iSQ3o0ZvEzIR00005ePbCl+WiwjFkdhPUOqcydIKCDC/sFA/sdUrCR+RKFfD9c
					Q5Ik0oJY5ahLhPeIm+vx8WZmgizhKTRAPxnthcRHiGuFfJwd6U79WR/P1gkXixg/5yrVeY4S7CSTRrYMzEcl0MvC3L9VuZaDh6YG
					a5EzJTIxysIuFu8asi2Wg1m2uXmObPcXlnvRw9QrK1HNLj74plJKEaIDOtImGP2Dk9VM2iS4W6pw/jY9aRbjYGb0uUX4ob8CG7Bn
					HNDD1TF0kOlRB+E9jlOK787MBgfA9VOHHqa9bZ+bt0iIJlesDUTyjzyb8EGACNmtxpyRn8JwAAAABJRU5ErkJggg==' );
			}
		}

		// Plugin logo
		if ( ! defined( 'GOWP_LOGO' ) ) {
			if ( get_option( 'gowp_enable_whitelabel' ) ) {
				if ( $gowp_whitelabel_logo = get_option( 'gowp_whitelabel_logo' ) ) {
					define( 'GOWP_LOGO', $gowp_whitelabel_logo );
				}
			} else {
				define( 'GOWP_LOGO',
					'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOcAAABoCAYAAAAKEuctAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbW
					FnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2
					VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUC
					BDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPS
					JodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9Ii
					IgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS
					94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIH
					htcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoTWFjaW50b3NoKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZD
					pGRjY5QTY2OEUzREIxMUUzQjBBQ0Q1MTM0RTdGMzNFRCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpGRjY5QTY2OUUzREIxMU
					UzQjBBQ0Q1MTM0RTdGMzNFRCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkZGNjlBNjY2RT
					NEQjExRTNCMEFDRDUxMzRFN0YzM0VEIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkZGNjlBNjY3RTNEQjExRTNCMEFDRDUxMz
					RFN0YzM0VEIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+Lj
					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');
			}
		}

	// Brand the plugin

		if ( ! function_exists( 'gowp_plugin_branding' ) ) {
			add_filter( 'all_plugins', 'gowp_plugin_branding' );
			function gowp_plugin_branding( $all_plugins ) {
				if ( isset( $all_plugins['gowp-dashboard/gowp-dashboard.php'] ) ) {
					$original = $all_plugins['gowp-dashboard/gowp-dashboard.php'];
					$replace = array(
						'Name' => GOWP_PLUGIN_NAME,
						'Title' => GOWP_PLUGIN_NAME,
						'Description' => GOWP_PLUGIN_DESCRIPTION,
						'Author' => GOWP_PLUGIN_AUTHOR,
						'AuthorName' => GOWP_PLUGIN_AUTHOR,
						'AuthorURI' => GOWP_PLUGIN_URL,
						'PluginURI' => '',
						'hide' => FALSE
					);
					if ( $replace['hide'] ) {
						unset( $all_plugins['gowp-dashboard/gowp-dashboard.php'] );
					} else {
						$all_plugins['gowp-dashboard/gowp-dashboard.php'] = array_merge( $original, $replace );
					}
				}
				uasort( $all_plugins, 'gowp_sort_plugins_by_name' );
				return $all_plugins;
			}
		}

/* ADMIN PAGE */

	if ( ! function_exists( 'gowp_add_admin_page' ) ) {
		add_action( 'admin_menu', 'gowp_add_admin_page' );
		function gowp_add_admin_page() {
			add_menu_page( GOWP_PLUGIN_TITLE, GOWP_PLUGIN_MENU, 'manage_options', 'support', 'gowp_display_admin_page', GOWP_ICON, '3.0001' );
			add_submenu_page( 'support', 'Dashboard', 'Dashboard', 'manage_options', 'support' );
		}
	}

	if ( ! function_exists( 'gowp_display_admin_page' ) ) {
		function gowp_display_admin_page() {
			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'updates'; // set default tab
			?>
			<div class="wrap">
				<?php if ( defined( 'GOWP_LOGO' ) ) : ?>
					<img id="support-logo" src="<?php echo GOWP_LOGO; ?>" alt="">
				<?php endif; ?>
				<h2><?php echo GOWP_PLUGIN_TITLE; ?></h2>
				<h2 class="nav-tab-wrapper">
					<a href="?page=support&amp;tab=updates" class="nav-tab <?php echo $active_tab == 'updates' ? 'nav-tab-active' : ''; ?>"><span class="dashicons dashicons-update"></span> Updates</a>
					<a href="?page=support&amp;tab=backups" class="nav-tab <?php echo $active_tab == 'backups' ? 'nav-tab-active' : ''; ?>"><span class="dashicons dashicons-cloud"></span> Backups</a>
					<a href="?page=support&amp;tab=security" class="nav-tab <?php echo $active_tab == 'security' ? 'nav-tab-active' : ''; ?>"><span class="dashicons dashicons-lock"></span> Security</a>
					<a href="?page=support&amp;tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><span class="dashicons dashicons-admin-generic"></span> Settings</a>
					<?php if ( 'agency' == $active_tab ) : ?>
						<a href="?page=support&amp;tab=agency" class="nav-tab nav-tab-active"><span class="dashicons dashicons-admin-tools"></span> Agencies</a>
					<?php endif; ?>
					<?php if ( ! get_option( 'gowp_enable_whitelabel' ) ) : ?>
						<a href="http://www.gowp.com/need-help/" class="nav-tab" target="_blank"><span class="dashicons dashicons-sos"></span> Need Help?</a>
					<?php endif; ?>
				</h2>
				<?php if ( 'updates' == $active_tab ) gowp_display_updates_tab(); ?>
				<?php if ( 'backups' == $active_tab ) gowp_display_backups_tab(); ?>
				<?php if ( 'security' == $active_tab ) gowp_display_security_tab(); ?>
				<?php if ( 'settings' == $active_tab ) gowp_display_settings_tab(); ?>
				<?php if ( 'agency' == $active_tab ) gowp_display_agency_tab(); ?>
				<?php if ( 'about' == $active_tab ) gowp_display_about_tab(); ?>
				<style type="text/css">
					#support-logo {
						float: right;
						height: auto;
						max-width: 100%;
						width: 150px;
					}
					h2 .nav-tab {
						height: 20px;
						line-height: 1;
					}
					.gowp-logfile-toggle {
						font-family: monospace;
						font-size: small;
						text-decoration: none;
						vertical-align: middle;
					}
					.gowp-logfile-details {
						background-color: white;
						font-size: small;
						line-height: 1.5em;
						overflow-y: scroll;
						padding: 1em;
					}
					.gowp-logfile-details-group {
						background-color: #f3f3f3;
						margin: 5px;
						padding: 5px;
					}
					.payload { white-space: pre-line; }
					.gowp-pass { color: green; }
					.gowp-fail, .gowp-error { color: red; }
					.gowp-info {
						background-color: #fff;
						border-left: 4px solid #7ad03a;
						box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
						margin: 5px 0 15px;
						padding: 1px 12px;
					}
					.gowp-notice {
						background-color: #fff;
						border-left: 4px solid orange;
						box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
						margin: 5px 0 15px;
						padding: 1px 12px;
					}
					.gowp-change-log {
						background-color: white;
						font-size: small;
						line-height: 1.5em;
						overflow-y: scroll;
						padding: 1em;
					}
					.gowp-change-log > li {
						font-weight: bold;
					}
					.gowp-change-log ul {
						font-weight: normal;
						list-style: circle inside none;
						margin: 0;
						padding-left: 10px;
					}
					.gowp-change-log ul li {
						margin-bottom: 0;
					}
				</style>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						$('.logfile-name').prepend('<a class="gowp-logfile-toggle" href="#">[-]</a> ');
						$('.gowp-logfile-toggle').click(function(){
							var logFileDetails = $(this).parent().next();
							logFileDetails.toggle();
							if ( logFileDetails.is(":visible") ) {
								$(this).html("[-]");
							} else {
								$(this).html("[+]");
							}
							return false;
						});
						$('.gowp-logfile-toggle').slice(1).click();
						$('.payload').before(' (<a class="payload-toggle" href="#">payload</a>)<br>');
						$('.payload').hide();
						$('.payload-toggle').click(function(){
							var payload = $(this).next('br').next('.payload');
							payload.toggle();
						});
					});
				</script>
			</div>
			<?php
		}
	}

/* UPDATES */

	// Plugin update settings

		add_action( 'admin_init', 'gowp_plugin_settings_init', 200 );
		function gowp_plugin_settings_init() {
			add_settings_section(
				'gowp_settings_updates',
				'Plugin Updates',
				'gowp_settings_updates_description',
				'support&tab=settings'
			);
			function gowp_settings_updates_description() {
				echo '';
			}
				add_settings_field(
					'gowp_blocked_plugin_updates',
					'Blocked Plugins',
					'gowp_block_plugin_updates_render',
					'support&tab=settings',
					'gowp_settings_updates'
				);
				register_setting( 'gowp_settings', 'gowp_blocked_plugin_updates' );
				function gowp_block_plugin_updates_render() {
					$gowp_blocked_plugin_updates = (array) get_option( 'gowp_blocked_plugin_updates' );
					$internal = array( 'gowp-dashboard/gowp-dashboard.php', 'iwp-client/init.php' );
					$plugins = get_plugins();
					echo "<p>Plugins selected below will not be updated by our automatic process. You will still be able to manually update these plugins if/as needed.</p>";
					foreach ( $plugins as $file => $plugin ) {
						if ( ! in_array( $file, $internal ) ) {
							$checked = ( in_array( $file, $gowp_blocked_plugin_updates ) ) ? "checked='checked'" : "";
							echo "<label><input type='checkbox' name='gowp_blocked_plugin_updates[]' value='{$file}' {$checked}> {$plugin['Name']} (Version: {$plugin['Version']})</label><br>";
						}
					}
				}
			}

	// Display update log data in admin dashboard

		if ( ! function_exists( 'gowp_display_updates_tab' ) ) {
			function gowp_display_updates_tab() {
				?>
					<h3>Remote Updates</h3>
					<p>Available WordPress core and plugin updates are applied automatically each week day. If needed, you can optionally exclude one or more plugins from this process via the Settings tab.</p>
				<?php
				if ( $logs = gowp_get_iwp_update_log() ) {
					$logcontents = gowp_remote_updates_html( $logs );
					$logfile = "Updates Log";
					$html = "<h4 class='logfile-name'>{$logfile}</h4>";
					$html .= "<div id='{$logfile}' class='gowp-logfile-details'>{$logcontents}</div>";
					echo "<div class='logfile-container'>{$html}</div>";
				}
				?>
					<h3>Local Updates</h3>
					<p>Below you will find a log of recent core/plugin updates, including those not made as part of our remote maintenance. This log is provided as a convenience for information or troubleshooting.</p>
				<?php
				if ( $logs = get_option( 'gowp_updates_log' ) ) {
					$logcontents = "";
					foreach ( $logs as $log ) {
						$date = date( 'Y/m/d H:i:s', $log['date'] );
						if ( 'core' == $log['type'] ) {
							$logcontents .= "[{$date}] <b>WordPress Core</b> updated to version <i>{$log['version']}</i><br>";
						} else {
							if ( isset( $log['plugins'] ) ) {
								foreach ( $log['plugins'] as $plugin ) {
									if ( ! empty( $plugin['name'] ) && ! empty( $plugin['version'] ) ) {
										$logcontents .= "[{$date}] <b>{$plugin['name']}</b> updated to version <i>{$plugin['version']}</i><br>";
									}
								}
							}
						}
					}
					$logfile = "Updates Log";
					$html = "<h4 class='logfile-name'>{$logfile}</h4>";
					$html .= "<div id='{$logfile}' class='gowp-logfile-details'>{$logcontents}</div>";
					echo "<div class='logfile-container'>{$html}</div>";
				}
			}
		}

	// Return HTML-formatted version of remote updates log

		function gowp_remote_updates_html( $logs ) {
			$logcontents = "";
			foreach ( $logs as $log ) {
				$date = date( 'Y/m/d H:i:s', $log['end'] );
				$logcontents .= "[{$date}] <b>{$log['name']}</b> updated to version <i>{$log['to']}</i><br>";
			}
			return $logcontents;
		}

	// Reset locally stored remote updates log whenever a remote update is performed

		add_action( 'upgrader_process_complete', 'gowp_reset_local_updates_log' );
		function gowp_reset_local_updates_log() {
			if ( defined( 'IWP_AUTHORISED_CALL' ) ) {
				delete_transient( 'gowp_updates_log' );
			}
		}

	// Update the local log when updates performed

		if ( ! function_exists( 'gowp_update_updates_log' ) ) {
			add_action( 'upgrader_process_complete', 'gowp_update_updates_log', 10, 2 );
			function gowp_update_updates_log( $this, $array ) {
				$log = get_option( 'gowp_updates_log', array() );
				if ( 'update' == $array['action'] ) {
					$array['date'] = time();
					$type = $array['type'];
					if ( 'core' == $type ) {
						include( get_home_path() . WPINC . "/version.php" );
						$array['version'] = $wp_version;
					}
					if ( 'plugin' == $type ) {
						if ( isset( $array['plugin'] ) ) {
							$files[] = $array['plugin'];
							unset( $array['plugin'] );
						} else {
							$files = $array['plugins'];
						}
						foreach ( $files as $file ) {
							if ( $data = get_file_data( WP_PLUGIN_DIR . "/$file", array( 'name' => 'Plugin Name', 'version' => 'Version' ) ) ) {
								$plugins[] = $data;
							}
						}
						$array['plugins'] = $plugins;
					}
					array_unshift( $log, $array );
					update_option( 'gowp_updates_log', $log );
				}
			}
		}

	// Trim the local updates log each time it is updated

		if ( ! function_exists( 'gowp_trim_updates_log' ) ) {
			add_filter( 'pre_update_option_gowp_updates_log', 'gowp_trim_updates_log', 10, 2 );
			function gowp_trim_updates_log( $new, $old ) {
				return array_slice( $new, 0, 50 );
			}
		}

	// Prevent us from performing client-blocked plugin updates

		add_filter( 'site_transient_update_plugins', 'gowp_block_plugin_updates', 999 );
		add_filter( 'transient_update_plugins', 'gowp_block_plugin_updates', 999 );
		function gowp_block_plugin_updates( $value ) {
			$blocked = (array) get_option( 'gowp_blocked_plugin_updates' );
			if ( is_object( $value ) && defined( 'IWP_AUTHORISED_CALL' ) ) {
				$updates = $value->response;
				foreach ( $updates as $name => $plugin ) {
					if ( in_array( $name, $blocked ) ) {
						unset( $updates[$name] );
					}
					$value->response = $updates;
				}
			}
			return $value;
		}

	// Get update log details from IWP

		function gowp_get_iwp_update_log() {
			if ( false === ( $gowp_updates_log = get_transient( 'gowp_updates_log' ) ) ) {
				$domain = str_replace( "www.", "", parse_url( home_url(), PHP_URL_HOST ) );
				$response = wp_remote_get( "https://iwp.gowp.com/gowp.php?action=getUpdates&site={$domain}" );
				if ( is_array( $response ) ) {
					$gowp_updates_log = array_shift( unserialize( $response['body'] ) );
					set_transient( 'gowp_updates_log', $gowp_updates_log, 6 * HOUR_IN_SECONDS );
				}
			}
			return $gowp_updates_log;
		}

/* BACKUPS */

	// Display backup log data in admin dashboard

		if ( ! function_exists( 'gowp_display_backups_tab' ) ) {
			function gowp_display_backups_tab() {
				$hosts = array(
					'wpengine' => array(
						'name' => 'WP Engine',
						'constant' => 'WPE_PLUGIN_BASE',
						'panel' => 'https://my.wpengine.com/',
						'remote' => false
					),
					'flywheel' => array(
						'name' => 'Flywheel',
						'constant' => 'FLYWHEEL_PLUGIN_DIR',
						'panel' => 'https://app.getflywheel.com/',
						'remote' => true
					),
				);
				foreach ( $hosts as $host ) {
					if ( defined( "{$host['constant']}" ) ) {
						$host_notice = "<p>{$host['name']} provides backup service this site; these backups can be viewed here: <a href='{$host['panel']}' target='_blank'>{$host['panel']}</a></p>";
						$host_remote = $host['remote'];
					}
				}
				if ( isset( $host_notice ) ) {
					echo '<h3>Host Backups</h3>';
					echo $host_notice;
				}
				if ( ! isset( $host_notice ) || $host_remote ) {
					echo '<h3>Remote Backups</h3>';
					echo '<p>Your entire website is automatically backed up every day, stored safely offsite for thirty days and available for download via the links below.</p>';
					echo '<form method="post">
							<p>
								<input name="backup_name" type="text" placeholder="Manual Backup">
								<input type="submit" name="manual_backup" id="submit" class="button button-primary" value="Backup Now">
							</p>
						</form>';
					if ( isset( $_POST['manual_backup'] ) ) {
						$backupName = ( ! empty( $_POST['backup_name'] ) ) ? urlencode( $_POST['backup_name'] ) : urlencode( "Manual Backup" );
						$url = "https://iwp.gowp.com/gowp.php?action=manualBackup&backupName={$backupName}&url=" . get_site_url();
						$result = wp_remote_get( $url, array('blocking' => false) );
						echo '<p class="gowp-pass">Your manual backup request has been initiated and should appear in the list below shortly.</p>';
					}
					if ( $logs = get_option( 'gowp_backup_log' ) ) {
						$host = str_replace( array( 'https://', 'http://', 'www.' ), '', home_url() );
						$logcontents = "";
						foreach ( $logs as $log ) {
							$date = date( 'Y/m/d H:i:s', $log['time'] );
							$link = ( array_key_exists( 'amazons3', $log ) ) ? "<a href='https://iwp.gowp.com/gowp.php?action=getBackup&domain={$host}&resource={$log['amazons3']}'>download</a>" : "";
							$logcontents .= "[{$date}] <b>{$log['backup_name']}</b> ({$log['size']}) $link<br>";
						}
						$logfile = "Backups Log";
						$html = "<h4 class='logfile-name'>{$logfile}</h4>";
						$html .= "<div id='{$logfile}' class='gowp-logfile-details'>{$logcontents}</div>";
					} else {
						$html = "<div class='gowp-logfile-details'>No recent backup activity found.</div>";
					}
					echo "<div class='logfile-container'>{$html}</div>";
				}
			}
		}

	// Update the local backup log when backups perfomed

		if ( ! function_exists( 'gowp_update_backups_log' ) ) {
			add_action( 'update_option_iwp_client_backup_tasks', 'gowp_update_backups_log', 10, 2 );
			add_action( 'update_option_gowp_client_backup_tasks', 'gowp_update_backups_log', 10, 2 );
			function gowp_update_backups_log( $old, $new ) {
				$log = get_option( 'gowp_backup_log', array() );
				if ( isset( $new['task_results'] ) ) {
					$results = array_pop( $new['task_results'] );
					array_unshift( $log, $results );
					update_option( 'gowp_backup_log', $log );
				}
			}
		}

	// Trim the local backup log each time it is updated

		if ( ! function_exists( 'gowp_trim_backups_log' ) ) {
			add_filter( 'pre_update_option_gowp_backup_log', 'gowp_trim_backups_log', 10, 2 );
			function gowp_trim_backups_log( $new, $old ) {
				return array_slice( $new, 0, 20 );
			}
		}

/* SECURITY */

	if ( ! function_exists( 'gowp_security_admin_notice' ) ) {
		add_action( 'admin_init', 'gowp_security_admin_notice' );
		function gowp_security_admin_notice() {
			$results = gowp_get_security_results();
			if ( isset( $results['MALWARE']['WARN'] ) || isset( $results['BLACKLIST']['WARN'] ) )
				add_action( 'admin_notices', 'gowp_display_security_admin_notice' );
		}
	}

	if ( ! function_exists( 'gowp_display_security_admin_notice' ) ) {
		function gowp_display_security_admin_notice() {
			?>
				<div class="error">
					<p>A recent security scan contained warnings. <a href="<?php echo admin_url('admin.php?page=support&tab=security'); ?>">View details.</a></p>
				</div>
			<?php
		}
	}

	if ( ! function_exists( 'gowp_get_security_results' ) ) {
		function gowp_get_security_results() {
			// Check for locally stored results
			if ( $results = get_transient( 'gowp_sucuri_results' ) ) {
				return $results;
			}
			// Check Sucuri API for results
			$host = str_replace( 'www.', '', home_url() );
			$domain = parse_url( $host, PHP_URL_HOST );
			$logcontents = @file_get_contents( "https://monitor16.sucuri.net/api.php?k=ffc84965edac5f38ef874044fa5325b31e6cb386366506cc96&a=query&format=serialized&host={$host}" );
			if ( strpos( $logcontents, $domain ) !== false ) {
				$results = unserialize( $logcontents );
			// Check IWP for results
			} else {
				$logcontents = @file_get_contents( "https://iwp.gowp.com/gowp.php?action=getScanResults&url=" . esc_url( home_url( '/' ) ) );
				if ( strpos( $logcontents, $domain ) !== false ) {
					$results = unserialize( $logcontents );
				}
			}
			// Store results locally
			if ( $results ) {
				set_transient( 'gowp_sucuri_results', $results, 24 * HOUR_IN_SECONDS );
				return $results;
			}
		}
	}

	if ( ! function_exists( 'gowp_display_security_tab' ) ) {
		function gowp_display_security_tab() {
			?>

				<?php $domain = str_replace( 'www.', '', home_url() ); ?>
				<?php if ( strpos( @file_get_contents( "https://monitor16.sucuri.net/api.php?k=ffc84965edac5f38ef874044fa5325b31e6cb386366506cc96&a=list" ), $domain ) !== false) : ?>
					<p>Your current subscription includes both <i>remote</i> and <i>server-side</i> scans. Remote scans are run every 6 hours and server-side scans every 24 hours.</p>
				<?php else : ?>
					<p>Your current subscription includes <i>remote</i> scans. Remote scans are run once weekly. To upgrade to a plan that includes server-side scans and unlimited cleanup, please contact us.</p>
				<?php endif; ?>
				<p><b>Remote scans</b> have the ability to detect the following infection types: Obfuscated JavaScript injections, Website Defacements, Hidden &amp; Malicious iFrames, Phishing Attempts, Malicious Redirects, Backdoors (e.g., C99, R57, Webshells), Anomalies, Drive-by-Downloads, SEO Blackhat Spam, Pharma Hacks, Conditional Redirects, and Mobile Redirects. The results of the latest remote scan can be found below.</p>
				<p><b>Server-side scans</b> are designed to detect these infection types: Phishing Pages, Backdoors (e.g., C99, R57, Webshells in various languages), Code Anomalies, Obfuscated Injections, and PHP Mailers. The results of server-side scans are not currently available here.</p>
				<p>In the event of a negative scan result, we are automatically notified and will contact you with details and updates on any cleanup required.</p>

				<?php $results = gowp_get_security_results(); ?>
				<?php if ( $results ) : ?>
					<div class="logfile-container">
						<h3 class="logfile-name">Remote Scan Summary</h3>
						<div class="gowp-logfile-details">
							<b>Last scan date:</b> <?php echo date( get_option( 'date_format' ), $results['SCAN']['DATE'] ); ?><br>
							<b>Scan site:</b> <?php echo $results['SCAN']['SITE'][0]; ?><br>
							<b>Hostname:</b> <?php echo $results['SCAN']['DOMAIN'][0]; ?><br>
							<b>IP address:</b> <?php echo $results['SCAN']['IP'][0]; ?><br>
							<b>Malware:</b>
								<?php if ( isset( $results['MALWARE']['WARN'] ) ) :?>
									<span class="gowp-fail">Potential malware detected.</span>
								<?php else : ?>
									<span class="gowp-pass">No malware detected.</span>
								<?php endif; ?>
								<br>
							<b>Blacklisting:</b>
								<?php if ( isset( $results['BLACKLIST']['WARN'] ) ) :?>
									<span class="gowp-fail">Potential blacklisting detected.</span>
								<?php else : ?>
									<span class="gowp-pass">No blacklisting detected.</span>
								<?php endif; ?>
								<br>
						</div>
					</div>

					<?php if ( ( isset( $results['MALWARE']['WARN'] ) ) && ( $warnings = $results['MALWARE']['WARN'] ) ) : ?>
						<div class="logfile-container">
							<h3 class="logfile-name">Malware Details</h3>
							<div>
								<div class="gowp-logfile-details">
									<?php
										foreach( $warnings as $warning ) {
											echo "<b class='gowp-fail'>{$warning[0]}</b><br>";
											$details = explode( "\n", $warning[1] );
											$summary = preg_replace( '/Details: (http.*?sucuri.*?malware.*)/', "(<a href='$1' target='_blank'>details</a>)", array_shift($details) );
											$payload = implode( "\n", $details );
											echo "$summary";
											echo "<pre class='payload gowp-logfile-details-group'>$payload</pre>";
										}
									?>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<div class="logfile-container">
						<h3 class="logfile-name">Blacklist Monitoring</h3>
						<div>
							<p>There are a number of blacklisting authorities that monitor for malware, SPAM, and phishing attempts. We leverages the APIs for these authorities to monitor your site's status.</p>
							<?php if ( $results ) : ?>
								<div class="gowp-logfile-details">
									<?php
										if ( ( isset( $results['BLACKLIST']['WARN'] ) ) && ( $warnings = $results['BLACKLIST']['WARN'] ) ) {
											foreach( $warnings as $warning ) {
												echo "<b class='gowp-fail'>{$warning[0]}</b> (<a href='{$warning[1]}' target='_blank'>reference</a>)<br>";
											}
										}
										if ( $infos = $results['BLACKLIST']['INFO'] ) {
											foreach( $infos as $info ) {
												echo "<b class='gowp-pass'>{$info[0]}</b> (<a href='{$info[1]}' target='_blank'>reference</a>)<br>";
											}
										}
									?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="logfile-container">
						<h3 class="logfile-name">Website Details</h3>
						<div class="gowp-logfile-details">
							<?php if ( $results['SYSTEM'] ) : ?>
								<b>System Details:</b><br>
								<div class="gowp-logfile-details-group">
									<?php if ( isset( $results['SYSTEM']['NOTICE'] ) ) echo implode( "<br>", $results['SYSTEM']['NOTICE'] ) . "<br>"; ?>
									<?php if ( isset( $results['SYSTEM']['INFO'] ) ) echo implode( "<br>", $results['SYSTEM']['INFO'] ) . "<br>"; ?>
									<?php
										if ( isset( $results['SYSTEM']['ERROR'] ) ) {
											echo "<b class='gowp-error'>Warnings:</b><br>";
											echo implode( "<br>", $results['SYSTEM']['ERROR'] );
										}
									?>
								</div>
							<?php endif; ?>
							<?php if ( $results['WEBAPP'] ) : ?>
								<b>Web application details:</b><br>
								<div class="gowp-logfile-details-group">
									<?php
										foreach ( $results['WEBAPP']['INFO'] as $webappinfo ) {
											if ( is_array( $webappinfo) ) {
												echo implode( " ", $webappinfo );
											} else {
												echo $webappinfo;
											}
											echo "<br>";
										}
									?>
									<?php echo implode( "<br>", $results['WEBAPP']['VERSION'] ); ?><br>
									<?php echo implode( "<br>", $results['WEBAPP']['NOTICE'] ); ?><br>
									<?php
										if ( isset( $results['WEBAPP']['WARN'] ) ) {
											echo "<b class='gowp-error'>Warnings:</b><br>";
											echo implode( "<br>", $results['WEBAPP']['WARN'] );
										}
									?>
								</div>
							<?php endif; ?>
							<?php if ( $results['LINKS']['URL'] ) : ?>
								<b>List of links found:</b><br>
								<div class="gowp-logfile-details-group"><?php echo implode( "<br>", $results['LINKS']['URL'] ); ?></div>
							<?php endif; ?>
							<?php if ( $results['LINKS']['JSLOCAL'] || $results['LINKS']['JSEXTERNAL'] ) : ?>
								<b>List of javascripts included:</b><br>
								<div class="gowp-logfile-details-group">
									<?php if ( isset( $results['LINKS']['JSLOCAL'] ) ) echo implode( "<br>", $results['LINKS']['JSLOCAL'] ); ?>
									<?php if ( isset( $results['LINKS']['JSEXTERNAL'] ) ) echo implode( "<br>", $results['LINKS']['JSEXTERNAL'] ); ?>
								</div>
							<?php endif; ?>
							<?php if ( isset( $results['LINKS']['IFRAME'] ) ) : ?>
								<b>List of iframes included:</b><br>
								<div class="gowp-logfile-details-group"><?php echo implode( "<br>", $results['LINKS']['IFRAME'] ); ?></div>
							<?php endif; ?>
							<?php if ( ( isset( $results['RECOMMENDATIONS'] ) ) && ( $recommendations = $results['RECOMMENDATIONS'] ) ) : ?>
								<b>Recommendations:</b><br>
								<div class="gowp-logfile-details-group">
									<?php
										foreach ( $recommendations as $recommendation ) {
											echo "<i>{$recommendation[0]}</i><br> {$recommendation[1]} (<a href='{$recommendation[2]}' target='_blank'>details</a>)<br>";
										}
									?>
								</div>
							<?php endif; ?>
						</div>
					</div>

				<?php endif; ?>
			<?php
		}
	}

/* AGENCIES */

	// Register/add agency settings

		add_action( 'admin_init', 'gowp_agency_settings_init' );
		if ( ! function_exists( 'gowp_agency_settings_init' ) ) {
			function gowp_agency_settings_init() {
				add_settings_section(
					'gowp_settings_agency',
					'Agencies',
					'gowp_settings_agency_description',
					'support&tab=agency'
				);
				function gowp_settings_agency_description() {
					echo '';
				}
					add_settings_field(
						'gowp_enable_whitelabel',
						'Enable White Labelling',
						'gowp_enable_whitelabel_render',
						'support&tab=agency',
						'gowp_settings_agency'
					);
					register_setting( 'gowp_agency_settings', 'gowp_enable_whitelabel' );
					function gowp_enable_whitelabel_render() {
						echo '<label><input name="gowp_enable_whitelabel" id="gowp_enable_whitelabel" type="checkbox" value="1" ' . checked( 1, get_option( 'gowp_enable_whitelabel' ), false ) . ' /> Checking this box will enable white labelling for this plugin.</label>';
					}
					add_settings_field(
						'gowp_whitelabel_menu',
						'Plugin Menu Name',
						'gowp_whitelabel_menu_render',
						'support&tab=agency',
						'gowp_settings_agency'
					);
					register_setting( 'gowp_agency_settings' , 'gowp_whitelabel_menu' );
					function gowp_whitelabel_menu_render() {
						$gowp_whitelabel_menu = get_option( 'gowp_whitelabel_menu' );
						echo "<input name='gowp_whitelabel_menu' id='gowp_whitelabel_menu' type='text' value='{$gowp_whitelabel_menu}'>";
					}
					add_settings_field(
						'gowp_whitelabel_title',
						'Plugin Page Title',
						'gowp_whitelabel_title_render',
						'support&tab=agency',
						'gowp_settings_agency'
					);
					register_setting( 'gowp_agency_settings' , 'gowp_whitelabel_title' );
					function gowp_whitelabel_title_render() {
						$gowp_whitelabel_title = get_option( 'gowp_whitelabel_title' );
						echo "<input name='gowp_whitelabel_title' id='gowp_whitelabel_title' type='text' value='{$gowp_whitelabel_title}'>";
					}
					add_settings_field(
						'gowp_whitelabel_author',
						'Plugin Author',
						'gowp_whitelabel_author_render',
						'support&tab=agency',
						'gowp_settings_agency'
					);
					register_setting( 'gowp_agency_settings' , 'gowp_whitelabel_author' );
					function gowp_whitelabel_author_render() {
						$gowp_whitelabel_author = get_option( 'gowp_whitelabel_author' );
						echo "<input name='gowp_whitelabel_author' id='gowp_whitelabel_author' type='text' value='{$gowp_whitelabel_author}'>";
					}
					add_settings_field(
						'gowp_whitelabel_plugin',
						'Plugin Name',
						'gowp_whitelabel_plugin_render',
						'support&tab=agency',
						'gowp_settings_agency'
					);
					register_setting( 'gowp_agency_settings' , 'gowp_whitelabel_plugin' );
					function gowp_whitelabel_plugin_render() {
						$gowp_whitelabel_plugin = get_option( 'gowp_whitelabel_plugin' );
						echo "<input name='gowp_whitelabel_plugin' id='gowp_whitelabel_plugin' type='text' value='{$gowp_whitelabel_plugin}'>";
					}
					add_settings_field(
						'gowp_whitelabel_logo',
						'Plugin Logo',
						'gowp_whitelabel_logo_render',
						'support&tab=agency',
						'gowp_settings_agency'
					);
					register_setting( 'gowp_agency_settings' , 'gowp_whitelabel_logo' );
					function gowp_whitelabel_logo_render() {
						$gowp_whitelabel_logo = get_option( 'gowp_whitelabel_logo' );
						echo "<input name='gowp_whitelabel_logo' id='gowp_whitelabel_logo' type='text' value='{$gowp_whitelabel_logo}'>
							<br>URL or <a href='http://en.wikipedia.org/wiki/Data_URI_scheme' target='_blank'>Data URI</a>
							<br>Leave blank to disable";
					}
					add_settings_field(
						'gowp_whitelabel_icon',
						'Plugin Icon',
						'gowp_whitelabel_icon_render',
						'support&tab=agency',
						'gowp_settings_agency'
					);
					register_setting( 'gowp_agency_settings' , 'gowp_whitelabel_icon' );
					function gowp_whitelabel_icon_render() {
						$gowp_whitelabel_icon = get_option( 'gowp_whitelabel_icon' );
						echo "<input name='gowp_whitelabel_icon' id='gowp_whitelabel_icon' type='text' value='{$gowp_whitelabel_icon}'>
							<br>URL, <a href='https://developer.wordpress.org/resource/dashicons/' target='_blank'>Dashicon</a>, or <a href='http://en.wikipedia.org/wiki/Data_URI_scheme' target='_blank'>Data URI</a>
							<br>Leave blank to disable";
					}
					add_settings_field(
						'gowp_whitelabel_description',
						'Plugin Description',
						'gowp_whitelabel_description_render',
						'support&tab=agency',
						'gowp_settings_agency'
					);
					register_setting( 'gowp_agency_settings' , 'gowp_whitelabel_description' );
					function gowp_whitelabel_description_render() {
						$gowp_whitelabel_description = get_option( 'gowp_whitelabel_description' );
						echo "<textarea name='gowp_whitelabel_description' id='gowp_whitelabel_description' rows='5' cols='30'>{$gowp_whitelabel_description}</textarea><br>Leave blank to disable.";
					}
			}
		}

	// Display settings tab

		if ( ! function_exists( 'gowp_display_agency_tab' ) ) {
			function gowp_display_agency_tab() {
				if ( isset( $_GET['action'] ) && 'export' == $_GET['action'] ) {
					echo '<h3>Agencies - Export Settings</h3>';
					global $wpdb;
					$results = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'gowp_whitelabel%'" );
					foreach ( $results as $result ) {
						$options[ $result->option_name ] = $result->option_value;
					}
					$export = serialize( $options );
					echo '<textarea rows="20" cols="100" disabled style="width: 100%;">' . print_r( $export, true ) . '</textarea>';
					echo '<p><a href="' . admin_url( 'admin.php?page=support&tab=agency' ) . '" class="button button-primary">Return</a></p>';
				} else if ( isset( $_GET['action'] ) && 'import' == $_GET['action'] ) {
					echo '<h3>Agencies - Import Settings</h3>';
					if ( $_POST && isset( $_POST['gowp_whitelabel_import'] ) && check_admin_referer( 'gowp-whitelabel-import' ) ) {
						$options = unserialize( stripslashes( $_POST['gowp_whitelabel_import'] ) );
						foreach ( $options as $option => $value ) {
							update_option( $option, $value );
						}
						echo '<p>Settings imported.</p>';
						echo '<p><a href="' . admin_url( 'admin.php?page=support&tab=agency' ) . '" class="button button-primary">Return</a></p>';
					} else {
						echo '<form method="POST">';
						echo '<textarea name="gowp_whitelabel_import" rows="20" cols="100" style="width: 100%;"></textarea>';
						wp_nonce_field( 'gowp-whitelabel-import' );
						submit_button( "Import" );
						echo '</form>';
					}
				} else {
					echo '<form method="POST" action="options.php">';
					settings_fields( 'gowp_agency_settings' );
					do_settings_sections( 'support&tab=agency' );
					submit_button();
					echo '<p>
							<a href="' . admin_url( 'admin.php?page=support&tab=agency&action=import' ) . '" class="button button-small button-primary">Import</a>
							<a href="' . admin_url( 'admin.php?page=support&tab=agency&action=export' ) . '" class="button button-small button-secondary">Export</a>
						</p>';
					echo '</form>';
				}
			}
		}

/* REPORTING */

	// Add settings

		add_action( 'admin_init', 'gowp_reporting_settings_init', 100 );
		function gowp_reporting_settings_init() {
			if ( ! get_option( 'gowp_enable_whitelabel' ) ) { // Do nothing if this is a white-labelled site
				add_settings_section(
					'gowp_settings_reporting',
					'Reporting Options',
					'gowp_settings_reporting_description',
					'support&tab=settings'
				);
				function gowp_settings_reporting_description() {
					echo '';
				}
				add_settings_field(
					'gowp_reporting_addresses',
					'Email Address(es)',
					'gowp_reporting_addresses_render',
					'support&tab=settings',
					'gowp_settings_reporting'
				);
				register_setting( 'gowp_settings', 'gowp_reporting_addresses' );
				function gowp_reporting_addresses_render() {
					$gowp_reporting_addresses = implode( "\n", (array) get_option( 'gowp_reporting_addresses' ) );
					echo "<p>Add one or more email addresses to receive monthly reports.</p>";
					echo "<textarea name='gowp_reporting_addresses' rows='5' cols='30'>{$gowp_reporting_addresses}</textarea>";
				}
				add_filter( 'sanitize_option_gowp_reporting_addresses', 'gowp_reporting_addresses_sanitize' );
				function gowp_reporting_addresses_sanitize( $value ) {
					$search = array( ",", ";", " ", "\t", "\r\n", "\n\n" );
					$replace = "\n";
					$lines = explode( "\n", str_replace( $search, $replace, $value ) );
					foreach ( $lines as $line ) {
						if ( is_email( $line ) ) {
							$emails[] = $line;
						}
					}
					return array_unique( $emails );
				}
			}
		}

	// Remote interaction

		add_action( 'init', 'gowp_reporting_addresses_remote' );
		function gowp_reporting_addresses_remote() {
			if ( isset( $_REQUEST['gowp_reporting'] ) ) {
				$return = "";
				// Remove an email address from the list
				if ( 'unsubscribe' == $_REQUEST['gowp_reporting'] && isset( $_REQUEST['address'] ) && is_email( $_REQUEST['address'] ) && ( $address = $_REQUEST['address'] ) ) {
					$gowp_reporting_addresses = (array) get_option( 'gowp_reporting_addresses' );
					$key = array_search( $address, $gowp_reporting_addresses, true );
					if ( $key !== false ) {
						unset( $gowp_reporting_addresses[$key] );
						update_option( 'gowp_reporting_addresses', $gowp_reporting_addresses );
					}
					$return = "<p>The email address '{$address}' will no longer receive monthly reports for this site.</p>";
				}
				// Add an email address to the list
				if ( 'subscribe' == $_REQUEST['gowp_reporting'] && isset( $_REQUEST['address'] ) && is_email( $_REQUEST['address'] ) && ( $address = $_REQUEST['address'] ) ) {
					$gowp_reporting_addresses = (array) get_option( 'gowp_reporting_addresses' );
					$gowp_reporting_addresses[] = $address;
					update_option( 'gowp_reporting_addresses', $gowp_reporting_addresses );
					$return = "<p>The email address '{$address}' will now receive monthly reports for this site.</p>";
				}
				// Get report details
				if ( 'get' == $_REQUEST['gowp_reporting'] ) {
					$report['recipients'] = (array) get_option( 'gowp_reporting_addresses' );
					$logs = gowp_get_iwp_update_log();
					$report['updates'] = gowp_remote_updates_html( $logs );
					$return = base64_encode( serialize( $report ) );
				}
				// Prime recipient list
				if ( 'prime' == $_REQUEST['gowp_reporting'] ) {
					$exclude = array(
						'support@gowp.com',
						'support@southpointmedia.com',
						'support@getbasepoint.com'
					);
					$addresses = ( isset( $_REQUEST['addresses'] ) ) ? explode( ",", $_REQUEST['addresses'] ) : array();
					$args = array( 'role' => 'administrator', 'fields' => array( 'user_email' ) );
					$users = get_users( $args );
					foreach ( $users as $user ) {
						if ( ! in_array( $user->user_email, $exclude ) ) {
							$addresses[] = $user->user_email;
						}
					}
					update_option( 'gowp_reporting_addresses', $addresses );
				}
				/*if ( 'get' == $_REQUEST['gowp_reporting'] && isset( $_REQUEST['type'] ) ) {
					$return = "<!-- GoWP Reporting -->\n";
					switch ( $_REQUEST['type'] ) {
						case 'recipients':
							$gowp_reporting_addresses = (array) get_option( 'gowp_reporting_addresses' );
							$return .= implode( "\n", $gowp_reporting_addresses );
							break;
						case 'updates':
							$logs = gowp_get_iwp_update_log();
							$return .= gowp_remote_updates_html( $logs );
							break;
					}
				}*/
				echo $return;
				die();
			}
		}

/* SETTINGS */

	if ( ! function_exists( 'gowp_display_settings_tab' ) ) {
		function gowp_display_settings_tab() {
			?>
			<form method="POST" action="options.php">
				<?php settings_fields( 'gowp_settings' ); ?>
				<?php do_settings_sections( 'support&tab=settings' ); ?>
				<?php submit_button(); ?>
			</form>
			<?php
		}
	}

/* IWP PLUGIN */

	// Branding

		if ( ! function_exists( 'gowp_iwp_client_branding' ) && ! get_option( 'gowp_enable_whitelabel' ) ) {
			add_filter( 'all_plugins', 'gowp_iwp_client_branding' );
			function gowp_iwp_client_branding( $all_plugins ) {
				if ( isset( $all_plugins['iwp-client/init.php'] ) ) {
					$original = $all_plugins['iwp-client/init.php'];
					$replace = array(
						'Name' => 'GoWP Remote',
						'Title' => 'GoWP Remote',
						'Description' => 'This plugin allows the GoWP support team to keep your WordPress installation and plugins updated as well as perform regular off-site backups of your entire website.',
						'Author' => 'GoWP',
						'AuthorName' => 'GoWP',
						'AuthorURI' => 'http://www.gowp.com',
						'PluginURI' => 'http://www.gowp.com',
						'hide' => FALSE
					);
					if ( $replace['hide'] ) {
						unset( $all_plugins['iwp-client/init.php'] );
					} else {
						$all_plugins['iwp-client/init.php'] = array_merge( $original, $replace );
					}
				}
				uasort( $all_plugins, 'gowp_sort_plugins_by_name' );
				return $all_plugins;
			}
		}

	// Auto-activation

		if( ! function_exists( 'gowp_iwp_admin_notice' ) ) {
			add_action( 'admin_notices', 'gowp_iwp_admin_notice');
			function gowp_iwp_admin_notice() {
				?>
				<script type="text/javascript">jQuery(document).ready(function($){ $('.updated:contains("IWP")').hide(); });</script>
				<?php
			}
		}

		if ( ! function_exists( 'gowp_iwp_auto_activate' ) ) {
			add_action('init', 'gowp_iwp_auto_activate');
			function gowp_iwp_auto_activate() {
				if ( ( $activationKey = get_option( 'iwp_client_activate_key', NULL ) ) && !get_option('iwp_client_public_key', NULL) ) {
					$manage = 'https://iwp.gowp.com/gowp.php';
					$action = 'addSite';
					$url = urlencode( home_url() );
					if ( is_user_logged_in() ) {
						$user = wp_get_current_user();
						$username = urlencode( $user->user_login );
					} else {
						$admins = get_users( array( 'role' => 'administrator' ) );
						$username = ( username_exists( 'gowp' ) ) ? 'gowp' : $admins[0]->user_login;
					}
					$ch_url = "{$manage}?action={$action}&url={$url}&username={$username}&activationKey={$activationKey}";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $ch_url);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_exec($ch);
					curl_close($ch);
				}
				register_deactivation_hook( 'iwp-client/init.php', 'gowp_iwp_auto_deactivate' );
			}
		}

		if ( ! function_exists( 'gowp_iwp_auto_deactivate' ) ) {
			function gowp_iwp_auto_deactivate() {
				$manage = 'https://iwp.gowp.com/gowp.php';
				$action = 'removeSite';
				$url = urlencode( home_url() );
				$ch_url = "{$manage}?action={$action}&url={$url}";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $ch_url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_exec($ch);
				curl_close($ch);
			}
		}

/* VUM PLUGIN */

	if ( ! get_option( 'gowp_enable_whitelabel' ) ) { // Do nothing if this is a white-labelled site

		// Set VUM plugin options

			if ( ! function_exists( 'gowp_set_vum_options' ) ) {
				add_action( 'init', 'gowp_set_vum_options' );
				function gowp_set_vum_options() {
					$wpm_o_user_id = get_option( 'wpm_o_user_id', NULL );
					if ( class_exists('Vum') && ! $wpm_o_user_id ) {
						delete_option( 'wpm_o_just_installed' );
						$option_updates = array(
							'wpm_o_user_id' => 'm_f09fe707537833d5b130bb709accf0fb',
							'wpm_o_change_popup_url' => '1',
							'wpm_o_show_dashboard' => '1',
							'wpm_o_show_editor' => '1',
							'wpm_o_show_images' => '1',
							'wpm_o_show_pages' => '1',
							'wpm_o_show_media' => '1',
							'wpm_o_show_posts' => '1',
							'wpm_o_show_comments' => '1',
							'wpm_o_show_profile' => '1',
							'wpm_o_show_widgets' => '1',
							'wpm_o_show_menus' => '1',
							'wpm_o_show_seo' => '1',
							'wpm_o_show_woocommerce' => '1',
							'wpm_o_show_woocommerce_products' => '1',
							'wpm_o_show_google_analytics_setup' => '1',
							'wpm_o_show_google_analytics_reports' => '1',
							'wpm_o_show_gravity_forms' => '1',
						);
						foreach ( $option_updates as $key => $val ) {
							update_option( $key, $val );
						}
					}
				}
			}

		// Move VUM menu options to GoWP

			if ( ! function_exists( 'gowp_add_vum_menu_items' ) ) {
				add_action( 'admin_menu', 'gowp_add_vum_menu_items' );
				function gowp_add_vum_menu_items() {
					if ( class_exists('Vum') ) {
						$vum = new Vum();
						$video_title = 'WP Videos';
						update_option( 'wpm_o_plugin_heading_video', $video_title );
						add_submenu_page( 'support', $video_title, $video_title, 'edit_posts', 'gowp-videos', array( $vum, 'display') );
						$manual_title = 'WP Manual';
						update_option( 'wpm_o_plugin_heading_user', $manual_title );
						add_submenu_page( 'support', $manual_title, $manual_title, 'edit_posts', 'gowp-manual', array( $vum, 'ebook') );
						add_action( 'admin_init', 'gowp_remove_vum_menu' );
					}
				}
			}

			if ( ! function_exists( 'gowp_remove_vum_menu' ) ) {
				function gowp_remove_vum_menu() {
					remove_menu_page( 'video-user-manuals/plugin.php' );
					remove_menu_page( 'vum-options' );
				}
			}

	}

/* CHAT WIDGET */

	// Live Chat settings

		add_action( 'admin_init', 'gowp_livechat_settings_init', 300 );
		function gowp_livechat_settings_init() {
			if ( ! get_option( 'gowp_enable_whitelabel' ) ) { // Do nothing if this is a white-labelled site
				add_settings_section(
					'gowp_settings_livechat',
					'Live Chat Support Widget',
					'gowp_settings_livechat_description',
					'support&tab=settings'
				);
				function gowp_settings_livechat_description() {
					echo '';
				}
					// Filter chat widget by role
					add_settings_field(
						'gowp_livechat_roles',
						'Required Roles',
						'gowp_livechat_roles_render',
						'support&tab=settings',
						'gowp_settings_livechat'
					);
					register_setting( 'gowp_settings', 'gowp_livechat_roles' );
					function gowp_livechat_roles_render() {
						$gowp_livechat_roles = (array) get_option( 'gowp_livechat_roles', 'administrator' );
						$roles = get_editable_roles();
						echo "<p>Choose which users should see the chat widget.</p>";
						foreach ( $roles as $id => $role ) {
							$name = $role['name'];
							$checked = ( in_array( $id, $gowp_livechat_roles ) ) ? "checked='checked'" : "";
							echo "<label><input type='checkbox' name='gowp_livechat_roles[]' value='{$id}' {$checked}> {$role['name']}</label><br>";
						}
					}
					// Disable chat widget entirely
					add_settings_field(
						'gowp_disable_livechat',
						'Disable Widget',
						'gowp_disable_livechat_render',
						'support&tab=settings',
						'gowp_settings_livechat'
					);
					register_setting( 'gowp_settings', 'gowp_disable_livechat' );
					function gowp_disable_livechat_render() {
						echo '<label><input name="gowp_disable_livechat" id="gowp_disable_livechat" type="checkbox" value="1"' . checked( 1, get_option( 'gowp_disable_livechat' ), false ) . ' /> Checking this box will disable the live chat support widget for all users.</label>';
					}
					// Placement of chat widget
					add_settings_field(
						'gowp_livechat_position',
						'Widget Position',
						'gowp_livechat_position_render',
						'support&tab=settings',
						'gowp_settings_livechat'
					);
					register_setting( 'gowp_settings', 'gowp_livechat_position' );
					function gowp_livechat_position_render() {
						$position = get_option( 'gowp_livechat_position', 'BR' );
						echo '<label><input type="radio" name="gowp_livechat_position" value="BL" ' . checked( "BL", $position, false ) . '> Left</label><br>';
						echo '<label><input type="radio" name="gowp_livechat_position" value="BR" ' . checked( "BR", $position, false ) . '> Right</label>';
					}
			}
		}

	// Add chat widget to admin pages

		if ( ! function_exists( 'gowp_display_chat_widget' ) && ! get_option( 'gowp_enable_whitelabel' ) ) {
			add_action( 'in_admin_footer', 'gowp_display_chat_widget' );
			function gowp_display_chat_widget() {
				global $current_user;
				get_currentuserinfo();
				$gowp_livechat_roles = (array) get_option( 'gowp_livechat_roles', 'administrator' );
				$user_roles = $current_user->roles;
				foreach ( $user_roles as $user_role ) {
					if ( in_array( $user_role, $gowp_livechat_roles ) ) {
						$user_can_livechat = TRUE;
					}
				}
				if ( ( isset( $user_can_livechat ) ) && ( ! get_option( 'gowp_disable_livechat', FALSE ) ) ) : ?>
					<!-- begin olark code -->
					<script data-cfasync="false" type='text/javascript'>
						/*<![CDATA[*/
						window.olark||(function(c){var f=window,d=document,l=f.location.protocol=="https:"?"https:":"http:",z=c.name,r="load";var nt=function(){
						f[z]=function(){
						(a.s=a.s||[]).push(arguments)};var a=f[z]._={
						},q=c.methods.length;while(q--){(function(n){f[z][n]=function(){
						f[z]("call",n,arguments)}})(c.methods[q])}a.l=c.loader;a.i=nt;a.p={
						0:+new Date};a.P=function(u){
						a.p[u]=new Date-a.p[0]};function s(){
						a.P(r);f[z](r)}f.addEventListener?f.addEventListener(r,s,false):f.attachEvent("on"+r,s);var ld=function(){function p(hd){
						hd="head";return["<",hd,"></",hd,"><",i,' onl' + 'oad="var d=',g,";d.getElementsByTagName('head')[0].",j,"(d.",h,"('script')).",k,"='",l,"//",a.l,"'",'"',"></",i,">"].join("")}var i="body",m=d[i];if(!m){
						return setTimeout(ld,100)}a.P(1);var j="appendChild",h="createElement",k="src",n=d[h]("div"),v=n[j](d[h](z)),b=d[h]("iframe"),g="document",e="domain",o;n.style.display="none";m.insertBefore(n,m.firstChild).id=z;b.frameBorder="0";b.id=z+"-loader";if(/MSIE[ ]+6/.test(navigator.userAgent)){
						b.src="javascript:false"}b.allowTransparency="true";v[j](b);try{
						b.contentWindow[g].open()}catch(w){
						c[e]=d[e];o="javascript:var d="+g+".open();d.domain='"+d.domain+"';";b[k]=o+"void(0);"}try{
						var t=b.contentWindow[g];t.write(p());t.close()}catch(x){
						b[k]=o+'d.write("'+p().replace(/"/g,String.fromCharCode(92)+'"')+'");d.close();'}a.P(2)};ld()};nt()})({
						loader: "static.olark.com/jsclient/loader0.js",name:"olark",methods:["configure","extend","declare","identify"]});
						/* custom configuration goes here (www.olark.com/documentation) */
						olark.identify('9350-634-10-3257');
						olark('api.visitor.updateFullName', {fullName: '<?php echo $current_user->display_name; ?>'});
						olark('api.visitor.updateEmailAddress', {emailAddress: '<?php echo $current_user->user_email; ?>'});
						olark.configure('box.corner_position', '<?php echo get_option( 'gowp_livechat_position', "br" ); ?>');
						/*]]>*/
					</script>
					<noscript>
						<a href="https://www.olark.com/site/9350-634-10-3257/contact" title="Contact us" target="_blank">Questions? Feedback?</a> powered by <a href="http://www.olark.com?welcome" title="Olark live chat software">Olark live chat software</a>
					</noscript>
					<!-- end olark code -->
				<?php endif;
			}
		}

/* CORE UPDATES */

	// Disable core update emails notifications

		add_filter( 'auto_core_update_send_email', '__return_false' );

/* COMMON FUNCTIONS */

	// Sort plugins by name

		function gowp_sort_plugins_by_name ( $a, $b ) {
			return strcasecmp( $a["Name"], $b["Name"] );
		}

/* HOTFIXES */