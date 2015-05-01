<?php
/*
Plugin Name: Click To Tweet
Description: Add click to tweet boxes to your WordPress posts, easily.
Version: 1.3
Author: Todaymade
Author URI: http://coschedule.com/
Plugin URI: http://coschedule.com/click-to-tweet
*/

// Check for existing class
if ( ! class_exists( 'tm_clicktotweet' ) ) {
/**
	 * Main Class
	 */
	class tm_clicktotweet  {

		/**
		 * Class constructor: initializes class variables and adds actions and filters.
		 */
		public function __construct() {
			$this->tm_clicktotweet();
		}

		public function tm_clicktotweet() {
			register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
			register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivation' ) );

			// Register global hooks
			$this->register_global_hooks();

			// Register admin only hooks
			if(is_admin()) {
				$this->register_admin_hooks();
			}
		}

		/**
		 * Print the contents of an array
		 */
		public function debug($array) {
			echo '<pre>';
			print_r($array);
			echo '</pre>';
		}

		/**
		 * Handles activation tasks, such as registering the uninstall hook.
		 */
		public function activation() {
			register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
		}

		/**
		 * Handles deactivation tasks, such as deleting plugin options.
		 */
		public function deactivation() {

		}

		/**
		 * Handles uninstallation tasks, such as deleting plugin options.
		 */
		public function uninstall() {
			delete_option('twitter-handle');
		}

		/**
		 * Registers global hooks, these are added to both the admin and front-end.
		 */
		public function register_global_hooks() {
			add_action('wp_enqueue_scripts', array($this, 'add_css'));
			add_filter('the_content', array($this, 'replace_tags'), 1);
		}

		/**
		 * Registers admin only hooks.
		 */
		public function register_admin_hooks() {
			// Cache bust tinymce
			add_filter('tiny_mce_version', array($this, 'refresh_mce'));

			// Add Settings Link
			add_action('admin_menu', array($this, 'admin_menu'));

			// Add settings link to plugins listing page
			add_filter('plugin_action_links', array($this, 'plugin_settings_link'), 2, 2);

			// Add button plugin to TinyMCE
			add_action('init', array($this, 'tinymce_button'));
		}

		public function tinymce_button() {
			if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
				return;
			}

			if (get_user_option('rich_editing') == 'true') {
				add_filter('mce_external_plugins', array($this, 'tinymce_register_plugin'));
				add_filter('mce_buttons', array($this, 'tinymce_register_button'));
			}
		}

		public function tinymce_register_button($buttons) {
		   array_push($buttons, "|", "tmclicktotweet");
		   return $buttons;
		}

		public function tinymce_register_plugin($plugin_array) {
		   $plugin_array['tmclicktotweet'] = plugins_url( '/assets/js/tmclicktotweet_plugin.js', __FILE__);
		   return $plugin_array;
		}

		/**
		 * Admin: Add settings link to plugin management page
		 */
		public function plugin_settings_link($actions, $file) {
			if(false !== strpos($file, 'tm-click-to-tweet')) {
				$actions['settings'] = '<a href="options-general.php?page=tmclicktotweet">Settings</a>';
			}
			return $actions;
		}

		/**
		 * Admin: Add Link to sidebar admin menu
		 */
		public function admin_menu() {
			add_action('admin_init', array($this, 'register_settings'));
			add_options_page('Click To Tweet Options', 'Click To Tweet', 'manage_options', 'tmclicktotweet', array($this, 'settings_page'));
		}

		/**
		 * Admin: Settings page
		 */
		public function settings_page() {
			if ( !current_user_can( 'manage_options' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			} ?>

			<div class="wrap">

				<?php screen_icon(); ?>
				<h2>Click To Tweet</h2>

				<hr/>

				<div style="float:right; margin-left:20px;">
					<a href="http://coschedule.com/?utm_source=WordPress+Plugin&utm_medium=banner&utm_campaign=WordPress+Plugin" target="_blank">
						<img src="http://space.todaymade.com/wp-plugins/click-to-tweet-sidebar/coschedule-Sidebar.png" alt="The Better Editorial Calendar For WordPress" />
					</a>
				</div>

				<h2>Instructions</h2>
				<p>
					To use, simply include the Click to Tweet code in your post. Place your message within the parentheses. Tweet length will be automatically truncated to 120 characters.  <pre>[Tweet "This is a tweet. It is only a tweet."]</pre>
				</p>

				<h2>Settings</h2>

				<p>Enter your Twitter handle to add "via @yourhandle" to your tweets. Do not include the @ symbol.</p>
				<form method="post" action="options.php" style="display: inline-block;">
					<?php settings_fields( 'tmclicktotweet-options' ); ?>

					<table class="form-table">
		        		<tr valign="top">
		        			<th style="width: 200px;"><label>Your Twitter Handle</label></th>
							<td><input type="text" name="twitter-handle" value="<?php echo get_option('twitter-handle'); ?>" /></td>
						</tr>
						<tr>
							<td></td>
							<td><?php submit_button(); ?></td>
					</table>
			 	</form>

			 	<hr/>
			 	<em>A plugin by <a href="http://coschedule.com" target="_blank">CoSchedule</a> © 2014</em>
			</div>
			<?php
		}

		/**
		 * Admin: Whitelist the settings used on the settings page
		 */
		public function register_settings() {
			register_setting('tmclicktotweet-options', 'twitter-handle', array($this, 'validate_settings'));
		}

		/**
		 * Admin: Validate settings
		 */
		public function validate_settings($input) {
			return str_replace('@', '', strip_tags(stripslashes($input)));
		}

		/**
		 * Add CSS needed for styling the plugin
		 */
		public function add_css() {
		    wp_register_style('tm_clicktotweet', plugins_url('/assets/css/styles.css', __FILE__));
		    wp_enqueue_style('tm_clicktotweet');
		}

		/**
		 * Shorten text lenth to 100 characters.
		 */
		public function shorten($input, $length, $ellipses = true, $strip_html = true) {
		    if ($strip_html) {
		        $input = strip_tags($input);
		    }
		    if (strlen($input) <= $length) {
		        return $input;
		    }
		    $last_space = strrpos(substr($input, 0, $length), ' ');
		    $trimmed_text = substr($input, 0, $last_space);
		    if ($ellipses) {
		        $trimmed_text .= '...';
		    }
		    return $trimmed_text;
		}

		/**
		 * Replacement of Tweet tags with the correct HTML
		 */
		public function tweet($matches) {
		    $handle = get_option('twitter-handle');
		    if (!empty($handle)) {
		        $handle_code = "&via=".$handle."&related=".$handle;
		    }
		    $text = $matches[1];
		    $short = $this->shorten($text, 100);
		    return "<div class='tm-tweet-clear'></div><div class='tm-click-to-tweet'><div class='tm-ctt-text'><a href='https://twitter.com/share?text=".urlencode($short).$handle_code."&url=".get_permalink()."' target='_blank'>".$short."</a></div><a href='https://twitter.com/share?text=".urlencode($short)."".$handle_code."&url=".get_permalink()."' target='_blank' class='tm-ctt-btn'>Click To Tweet</a><div class='tm-ctt-tip'></div></div>";
		}

		/**
		 * Replacement of Tweet tags with the correct HTML for a rss feed
		 */
		public function tweet_feed($matches) {
		    $handle = get_option('twitter-handle');
		    if (!empty($handle)) {
		        $handle_code = "&via=".$handle."&related=".$handle;
		    }
		    $text = $matches[1];
		    $short = $this->shorten($text, 100);
		    return "<hr /><p><em>".$short."</em><br /><a href='https://twitter.com/share?text=".urlencode($short).$handle_code."&url=".get_permalink()."' target='_blank'>Click To Tweet</a></p><hr />";
		}

		/**
		 * Regular expression to locate tweet tags
		 */
		public function replace_tags($content) {
			if (!is_feed()) {
				$content = preg_replace_callback("/\[tweet \"(.*?)\"]/i", array($this, 'tweet'), $content);
			} else {
				$content = preg_replace_callback("/\[tweet \"(.*?)\"]/i", array($this, 'tweet_feed'), $content);
			}
			return $content;
		}

		/**
		 * Cache bust tinymce
		 */
		public function refresh_mce($ver) {
			$ver += 3;
			return $ver;
		}
	} // End tm_clicktotweet class

	// Init Class
	new tm_clicktotweet();
}

?>