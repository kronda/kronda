<?php /*

**************************************************************************

Plugin Name:  Dashboard: Scheduled Posts
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/dashboard-scheduled-posts/
Description:  Displays scheduled posts on your WordPress 2.7+ dashboard.
Version:      2.0.0
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************/

class DashboardScheduledPosts {

	// Class initialization
	function DashboardScheduledPosts() {
		if ( !current_user_can( 'edit_posts' ) )
			return;

		// Load up the localization file if we're using WordPress in a different language
		// Place it in this plugin's folder and name it "dashboard-scheduled-posts-[value in wp-config].mo"
		load_plugin_textdomain( 'dashboard-scheduled-posts', FALSE, '/dashboard-scheduled-posts' );

		// Hooks!
		add_action( 'admin_head', array(&$this, 'css') );
		add_action( 'wp_dashboard_setup', array(&$this, 'register_widget') );
		add_filter( 'wp_dashboard_widgets', array(&$this, 'add_widget') );
	}


	// Register this widget -- we use a hook/function to make the widget a dashboard-only widget
	function register_widget() {
		wp_register_sidebar_widget( 'dashboard_scheduled_posts', __( 'Scheduled Posts', 'dashboard-scheduled-posts' ), array(&$this, 'widget'), array( 'all_link' => 'edit.php?post_status=future' ) );
	}


	// Some content styling for the widget
	function css() { ?>
<style type="text/css">
	#dashboard_scheduled_posts ul {
		margin: 0;
		padding: 0;
		list-style: none;
	}
	#dashboard_scheduled_posts ul li {
		margin-bottom: 0.6em;
	}
	#dashboard_scheduled_posts h4 {
		font-weight: normal;
	}
	#dashboard_scheduled_posts h4 abbr {
		font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;
		font-size: 11px;
		color: #999;
		margin-left: 3px;
	}
	#dashboard_scheduled_posts p {
		margin: 0;
		padding: 0;
	}
</style>
<?php
	}


	// Modifies the array of dashboard widgets and adds this plugin's
	function add_widget( $widgets ) {
		global $wp_registered_widgets;

		if ( !isset($wp_registered_widgets['dashboard_scheduled_posts']) )
			return $widgets;

		$widgets[] = 'dashboard_scheduled_posts';

		return $widgets;
	}


	// Output the widget contents
	function widget() {
		$futures_query = new WP_Query( array(
			'post_type' => 'post',
			'what_to_show' => 'posts',
			'post_status' => 'future',
			'posts_per_page' => 5,
			'orderby' => 'date',
			'order' => 'ASC'
		) );
		$futures =& $futures_query->posts;

		if ( $futures && is_array( $futures ) ) {
			$list = array();
			foreach ( $futures as $future ) {
				$url = get_edit_post_link( $future->ID );
				$title = _draft_or_post_title( $future->ID );
				$item = "<h4><a href='$url' title='" . sprintf( __( 'Edit "%s"' ), attribute_escape( $title ) ) . "'>$title</a> <abbr title='" . get_the_time(__('Y/m/d g:i:s A'), $future) . "'>" . get_the_time( get_option( 'date_format' ), $future ) . '</abbr></h4>';
				if ( $the_content = preg_split( '#\s#', strip_tags( $future->post_content ), 11, PREG_SPLIT_NO_EMPTY ) )
					$item .= '<p>' . join( ' ', array_slice( $the_content, 0, 10 ) ) . ( 10 < count( $the_content ) ? '&hellip;' : '' ) . '</p>';
				$list[] = $item;
			}
?>
	<ul>
		<li><?php echo join( "</li>\n<li>", $list ); ?></li>
	</ul>
	<p class="textright"><a href="edit.php?post_status=future" class="button"><?php _e('View all'); ?></a></p>
<?php
		} else {
			_e( 'There are no scheduled posts at the moment', 'dashboard-scheduled-posts' );
		}
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $DashboardScheduledPosts; $DashboardScheduledPosts = new DashboardScheduledPosts();' ) );

?>