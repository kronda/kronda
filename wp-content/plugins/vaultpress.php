<?php
/*
 * Plugin Name: VaultPress
 * Plugin URI: http://automattic.com/wordpress-plugins/
 * Description: WordPress, Secured
 * Version: 0.0882
 * Author: Automattic
 * Author URI: http://vaultpress.com/
 *
 */

define( 'VAULTPRESS_SHARED_KEY_INIT',   '7DttCTF9EKIfb4E' );
define( 'VAULTPRESS_SHARED_SECRET_INIT', 'VyzS4yfSYQFxIiNoGEGjhZkBZfOTccoWrk36jqSwb5' );

if ( !defined( 'ABSPATH' ) )
	return;

###
### Section: plugin UI
###

function vaultpress_activate() {
	if ( !get_option( 'vaultpress_key' ) && defined( 'VAULTPRESS_SHARED_KEY_INIT' ) && VAULTPRESS_SHARED_KEY_INIT )
		update_option( 'vaultpress_key', VAULTPRESS_SHARED_KEY_INIT );
	if ( !get_option( 'vaultpress_secret' ) && defined( 'VAULTPRESS_SHARED_SECRET_INIT' ) && VAULTPRESS_SHARED_SECRET_INIT )
		update_option( 'vaultpress_secret', VAULTPRESS_SHARED_SECRET_INIT );

	// The following line was not left in on accident
	vaultpress_contact_service( 'test', array( 'host' => $_SERVER['HTTP_HOST'], 'uri' => $_SERVER['REQUEST_URI'], 'ssl' => is_ssl() ) );

	update_option( 'active_plugins', vaultpress_load_first( get_option( 'active_plugins'  ) ) );

	if ( vaultpress_check_connection( true ) ) {
		update_option( 'vaultpress_activated', 'success' );
	} else {
		update_option( 'vaultpress_activated', 'error' );
	}
}
register_activation_hook( __FILE__, 'vaultpress_activate' );

function vaultpress_admin_menu() {
	if ( !function_exists( 'current_user_can' ) || !current_user_can( 'manage_options' ) )
		return;
	// Process this update BEFORE deciding whether to show submenus (better user experience)
	if ( $_GET['page'] == 'vaultpress' && isset( $_POST ) && is_array( $_POST ) && count( $_POST ) ) {
		if ( isset( $_POST['vaultpress_hostname'] ) && $_POST['vaultpress_hostname'] )
			update_option( 'vaultpress_hostname', $_POST['vaultpress_hostname'] );
		else
			delete_option( 'vaultpress_hostname' );
			
		if ( isset( $_POST['vaultpress_timeout'] ) && $_POST['vaultpress_timeout'] )
			update_option( 'vaultpress_timeout', $_POST['vaultpress_timeout'] );
		else
			delete_option( 'vaultpress_timeout' );

		if ( isset( $_POST['vp_disable_firewall'] ) && $_POST['vp_disable_firewall'] )
			update_option( 'vp_disable_firewall', 1 );
		else
			delete_option( 'vp_disable_firewall' );

		if ( isset( $_POST['vp_debug_request_signing'] ) && $_POST['vp_debug_request_signing'] )
			update_option( 'vp_debug_request_signing', 1 );
		else
			delete_option( 'vp_debug_request_signing' );
	}

	$hook = add_menu_page( 'VaultPress', 'VaultPress', 'manage_options', 'vaultpress', 'vaultpress_options', 'div' );
	
	$activated = get_option( 'vaultpress_activated' );
	if ( !empty( $activated ) ) {
		add_action( 'admin_notices', 'vaultpress_activated_notice' );
	}
}
add_action( 'admin_menu', 'vaultpress_admin_menu' );

function vaultpress_admin_head() {
?>
	<style type="text/css">
		#toplevel_page_vaultpress div.wp-menu-image {
			background: url(http://vaultpress.com/images/vp-icon-sprite.png) center top no-repeat;
		}

		#toplevel_page_vaultpress.current div.wp-menu-image,
		#toplevel_page_vaultpress:hover div.wp-menu-image {
			background-position: center bottom;
		}
	</style>
<?php
}
add_action( 'admin_head', 'vaultpress_admin_head' );

function vaultpress_activated_notice() {
	$activated = get_option( 'vaultpress_activated' );
	
	if ( $activated == 'success' ) {
?>
	<div id="vaultpress-notice" class="updated fade"><p><?php printf(
		__( 'Visit the <a href="%s">VaultPress page</a> to view the progress of your backup.' ),
		esc_url( admin_url( 'admin.php?page=vaultpress' ) )
	); ?></p></div>
<?php

	} else {
?>
	<div id="vaultpress-notice" class="error fade"><p><?php printf(
		__( 'There was an error connecting your site to VaultPress. Please check the configuration on the <a href="%s">VaultPress page</a> and contact support at <a href="mailto:support@vaultpress.com">support@vaultpress.com</a> if issue continues.' ),
		esc_url( admin_url( 'admin.php?page=vaultpress' ) )
	); ?></p></div>
<?php
	}
	delete_option( 'vaultpress_activated' );
}

// enable custom menu ordering
function vaultpress_customer_menu_order() {
	return true;
}
add_filter( 'custom_menu_order', 'vaultpress_customer_menu_order' );

// position VaultPress under the Dashboard in the menu
function vaultpress_menu_order( $menu_order ) {
	$vp_menu_order = array();

	foreach ( $menu_order as $index => $item ) {		
		if ( $item != 'vaultpress' )
			$vp_menu_order[] = $item;
		
		if ( $index == 0 )
			$vp_menu_order[] = 'vaultpress';
	}
	
	return $vp_menu_order;
}
add_filter( 'menu_order', 'vaultpress_menu_order' );

function vaultpress_options() {
	$status = $ticker = false;

	if ( $status = vaultpress_contact_service( 'status', array() ) )
		$ticker = vaultpress_contact_service( 'ticker', array() );
?>
	<div class="wrap">
		<?php
			if ( !vaultpress_error_messages( $status, $ticker ) )
				echo vaultpress_contact_service( 'plugin_ui' );

			// only show configuration options if user can manage options
			if ( function_exists( 'current_user_can' ) || current_user_can( 'manage_options' ) )
				vaultpress_configuration_content();
		?>
	</div>
<?php
}

function vaultpress_error_messages($status, $ticker) {
	$fatal_error = false;

	if ( !$status ) {
		$fatal_error = 'Unable to contact VaultPress.com';

	// we've got a big problem
	} elseif ( is_array($ticker) && isset($ticker['faultCode']) ) {
		$fatal_error = $ticker['faultString'] . ' (' . $ticker['faultCode'] . ')';
	}

	if ( $fatal_error !== false ) {
		printf( '<h2>VaultPress</h2><div id="vaultpress-warning" class="error"><p><strong>Oops... there seems to be a problem.</strong> %s</p></div>', $fatal_error );
		return true;
	} else {
		if ( !$status['attachment']['status'] ) {
			printf(
				'<div id="vaultpress-warning" class="updated fade"><p><strong>Your site is not attached to any account.</strong> <a href="%s" target="_blank">Attach it to your account now</a></p></div>',
				esc_attr( $status['attachment']['url'] )
			);
		}
	}

	return false;
}

function vaultpress_configuration_content() {
?>

<p id="vp-show-config">
	<a href="#"
onclick="jQuery('#vp-show-config').hide(); jQuery('#vp-config').fadeIn('slow'); return false;"><?php echo __( 'Show Configuration' ); ?></a>
</p>

<div id="vp-config" style="display:none;">
	<h3><?php _e('Configuration'); ?></h3>
	<form method="post" action="">
		<?php echo wp_nonce_field( 'update-options' ); ?>
		<table class="form-table">
			<tbody>
			<input type="hidden" name="action" value="update" />
			<tr>
				<td><strong><?php echo __( 'Your Key' ); ?></strong></td>
				<td>&#8220;<?php echo htmlentities( get_option( 'vaultpress_key' ) ); ?>&#8221;</td>
			</tr>
			<tr>
				<td><strong><?php echo __( 'Your Shared Secret' ); ?></strong></td>
				<td>
					<a href="#" onclick="jQuery(this).hide(); jQuery('#vpdbg3').fadeIn('slow'); return false;"
					><?php echo __( 'Is Still Hidden, Click to Show.' ); ?></a>
					<span id="vpdbg3" style="display:none;">&#8220;<?php echo htmlentities( get_option( 'vaultpress_secret' ) ); ?>&#8221;</span>
				</td>
			</tr>
			<tr>
				<td valign="top"><strong><?php echo __( 'Disable Plugin Firewall' ); ?></strong></td>
				<td>
					<select name="vp_disable_firewall">
						<option value="1">yes</option>
						<option value="0" <?php if ( !get_option( 'vp_disable_firewall' ) ) echo 'selected="selected"'; ?>>no</option>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top"><strong><?php echo __( 'VaultPress Firewall' ); ?></strong></td>
				<td>
					<pre><?php echo htmlentities( var_export( maybe_unserialize( get_option( 'vaultpress_service_ips' ) ), true ) ); ?></pre>
					<button onclick="jQuery.getScript( '<?php echo get_bloginfo( 'wpurl' ); ?>/?vaultpress=true&amp;firewall-reload' ); return false;">reload firewall</button>
				</td>
			</tr>
			<tr>
				<td valign="top"><strong><?php echo __( 'VaultPress Hostname' ); ?></strong></td>
				<td><input type="text" name="vaultpress_hostname" size="25" value="<?php echo esc_attr( get_option( 'vaultpress_hostname' ) ); ?>"/></td>
			</tr>
			<tr>
				<td valign="top"><strong><?php echo __( 'VaultPress Timeout' ); ?></strong></td>
				<td><input type="text" name="vaultpress_timeout" size="5" value="<?php echo esc_attr( get_option( 'vaultpress_timeout' ) ); ?>"/></td>
			</tr>
			<tr>
				<td valign="top"><strong><?php echo __( 'Debug Request Signing' ); ?></strong></td>
				<td>
					<select name="vp_debug_request_signing">
						<option value="1">yes</option>
						<option value="0" <?php if ( !get_option( 'vp_debug_request_signing' ) ) echo 'selected="selected"'; ?>>no</option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php echo esc_attr__( 'Save Changes' ); ?>" />
		</p>
	</form>
</div>

<?php
}

function vp_get_config( $key ) {
	$val = get_option( $key );
	if ( $val )
		return $val;
	switch( $key ) {
		case '_vp_config_option_name_ignore':
			$val = array(
				'cron',
				'wpsupercache_gc_time',
				'/_transient_/',
				'/^_vp_/',
			);
			update_option( '_vp_config_option_name_ignore', $val );
			break;
	}
	return $val;
}

###
### Section: Backup Notification Hooks
###

// Handle Handle Notifying VaultPress of Options Activity At this point the options table has already been modified
//
// Note: we handle deleted, instead of delete because VaultPress backs up options by name (which are unique,) that
// means that we do not need to resolve an id like we would for, say, a post.
function vaultpress_option_handler( $option_name ) {
	global $wpdb;
	// Step 1 -- exclusionary rules, don't send these options to vaultpress, because they
	// either change constantly and/or are inconsequential to the blog itself and/or they
	// are specific to the VaultPress plugin process and we want to avoid recursion
	$should_ping = true;
	$ignore_names = vp_get_config( '_vp_config_option_name_ignore' );
	foreach( (array)$ignore_names as $val ) {
		if ( $val{0} == '/' ) {
			if ( preg_match( $val, $option_name ) )
				$should_ping = false;
		} else {
			if ( $val == $option_name )
				$should_ping = false;
		}
		if ( !$should_ping )
			break;
	}
	if ( $should_ping )
		vaultpress_add_ping( 'db', array( 'option' => $option_name ) );

	// Step 2 -- If WordPress is about to kick off a some "cron" action, we need to
	// flush vaultpress, because the "remote" cron threads done via http fetch will
	// be happening completely inside the window of this thread.  That thread will
	// be expecting touched and accounted for tables
	if ( $option_name == '_transient_doing_cron' )
		vaultpress_do_pings();

	return $option_name;
}

// Handle Notifying VaultPress of Comment Activity
function vaultpress_comment_action_handler( $comment_id ) {
	if ( !is_array( $comment_id ) )
		return vaultpress_add_ping( 'db', array( 'comment' => $comment_id ) );
	foreach ( $comment_id as $id )
		vaultpress_add_ping( 'db', array( 'comment' => $id) );
}

// Handle Notifying VaultPress of Theme Switches
function vaultpress_theme_action_handler( $theme ) {
	vaultpress_add_ping( 'themes', array( 'theme' => get_option('stylesheet') ) );
}

// Handle Notifying VaultPress of Upload Activity
function vaultpress_upload_handler( $file ) {
	vaultpress_add_ping( 'uploads', array( 'upload' => str_replace( ABSPATH . get_option( 'upload_path' ), '', $file['file'] ) ) );
	return $file;
}

// Handle Notifying VaultPress of Plugin Activation/Deactivation
function vaultpress_plugin_action_handler( $plugin='' ) {
	vaultpress_add_ping( 'plugins', array( 'name' => $plugin ) );
}

// Handle Notifying VaultPress of User Edits
function vaultpress_userid_action_handler( $user_or_id ) {
	if ( is_object($user_or_id) )
		$userid = intval( $user_or_id->ID );
	else
		$userid = intval( $user_or_id );
	if ( !$userid )
		return;
	vaultpress_add_ping( 'db', array( 'user' => $userid ) );
}

// Handle Notifying VaultPress of term changes
function vaultpress_term_handler( $term_id, $tt_id=null ) {
	vaultpress_add_ping( 'db', array( 'term' => $term_id ) );
	if ( $tt_id )
		vaultpress_term_taxonomy_handler( $tt_id );
}

// Handle Notifying VaultPress of term_taxonomy changes
function vaultpress_term_taxonomy_handler( $tt_id ) {
	vaultpress_add_ping( 'db', array( 'term_taxonomy' => $tt_id ) );
}
// add(ed)_term_taxonomy handled via the created_term hook, the term_taxonomy_handler is called by the term_handler

// Handle Notifying VaultPress of term_taxonomy changes
function vaultpress_term_taxonomies_handler( $tt_ids ) {
	foreach( (array)$tt_ids as $tt_id ) {
		vaultpress_term_taxonomy_handler( $tt_id );
	}
}

// Handle Notifying VaultPress of term_relationship changes
function vaultpress_term_relationship_handler( $object_id, $term_id ) {
	vaultpress_add_ping( 'db', array( 'term_relationship' => array( 'object_id' => $object_id, 'term_taxonomy_id' => $term_id ) ) );
}

// Handle Notifying VaultPress of term_relationship changes
function vaultpress_term_relationships_handler( $object_id, $term_ids ) {
	foreach ( (array)$term_ids as $term_id ) {
		vaultpress_term_relationship_handler( $object_id, $term_id );
	}
}

// Handle Notifying VaultPress of term_relationship changes
function vaultpress_set_object_terms_handler( $object_id, $terms, $tt_ids ) {
	vaultpress_term_relationships_handler( $object_id, $term_ids );
}

// Handle Notifying VaultPress of UserMeta changes
function vaultpress_usermeta_action_handler( $umeta_id, $user_id, $meta_key, $meta_value='' ) {
	vaultpress_add_ping( 'db', array( 'usermeta' => $umeta_id ) );
}

// Handle Notifying VaultPress of Post Changes
function vaultpress_post_action_handler($post_id) {
	if ( current_filter() == 'delete_post' )
		return vaultpress_add_ping( 'db', array( 'post' => $post_id ), 'delete_post' );
	return vaultpress_add_ping( 'db', array( 'post' => $post_id ), 'edit_post' );
}

// Handle Notifying VaultPress of Link Changes
function vaultpress_link_action_handler( $link_id ) {
	vaultpress_add_ping( 'db', array( 'link' => $link_id ) );
}

// Handle Notifying VaultPress of Commentmeta Changes
function vaultpress_commentmeta_insert_handler( $meta_id ) {
	vaultpress_add_ping( 'db', array( 'commentmeta' => $meta_id ) );
}

function vaultpress_commentmeta_modification_handler( $object_id, $meta_key, $meta_value, $meta_id ) {
	if ( !is_array( $meta_id ) )
		return vaultpress_add_ping( 'db', array( 'commentmeta' => $meta_id ) );
	foreach ( $meta_id as $id ) {
		vaultpress_add_ping( 'db', array( 'commentmeta' => $id ) );
	}
}

// Handle Notifying VaultPress of PostMeta changes via newfangled metadata functions
function vaultpress_postmeta_insert_handler( $meta_id, $post_id, $meta_key, $meta_value='' ) {
	vaultpress_add_ping( 'db', array( 'postmeta' => $meta_id ) );
}

function vaultpress_postmeta_modification_handler( $meta_id, $object_id, $meta_key, $meta_value ) {
	if ( !is_array( $meta_id ) )
		return vaultpress_add_ping( 'db', array( 'postmeta' => $meta_id ) );
	foreach ( $meta_id as $id ) {
		vaultpress_add_ping( 'db', array( 'postmeta' => $id ) );
	}
}

// Handle Notifying VaultPress of PostMeta changes via old school cherypicked hooks
function vaultpress_postmeta_action_handler( $meta_id ) {
	if ( !is_array($meta_id) )
		return vaultpress_add_ping( 'db', array( 'postmeta' => $meta_id ) );
	foreach ( $meta_id as $id )
		vaultpress_add_ping( 'db', array( 'postmeta' => $id ) );
}

###
### Section: filesystem functionality
###

class vaultpressfs {

	var $type = null;
	var $dir = null;

	function vaultpressfs() {
		$this->__construct();
	}

	function __construct() {
	}

	function want( $type ) {
		if ( $type == 'plugins' ) {
			$this->dir = realpath( vaultpress_resolve_content_dir() . 'plugins' );
			$this->type = 'p';
			return true;
		}
		if ( $type == 'themes' ) {
			$this->dir = realpath( vaultpress_resolve_content_dir() . 'themes' );
			$this->type = 't';
			return true;
		}
		if ( $type == 'uploads' ) {
			$this->dir = realpath( vaultpress_resolve_upload_path() );
			$this->type = 'u';
			return true;
		}
		if ( $type == 'content' ) {
			$this->dir = realpath( vaultpress_resolve_content_dir() );
			$this->type = 'c';
			return true;
		}
		if ( $type == 'root' ) {
			$this->dir = realpath( ABSPATH );
			$this->type = 'r';
			return true;
		}
		die( 'naughty naughty' );
	}

	function fdump( $file ) {
		header("Content-Type: application/octet-stream;");
		header("Content-Transfer-Encoding: binary");
		ob_end_clean();
		if ( !file_exists( $file ) || !is_readable( $file ) )
			die( "no such file" );
		if ( !is_file( $file ) && !is_link( $file ) )
			die( "can only dump files" );
		$fp = @fopen( $file, 'rb' );
		if ( !$fp )
			die( "could not open file" );
		while ( !feof( $fp ) )
			echo @fread( $fp, 8192 );
		@fclose( $fp );
		die();
	}

	function stat( $file, $md5=true, $sha1=true ) {
		$rval = array();
		foreach ( stat( $file ) as $i => $v ) {
			if ( is_numeric( $i ) )
				continue;
			$rval[$i] = $v;
		}
		$rval['type'] = filetype( $file );
		if ( $rval['type'] == 'file' ) {
			if ( $md5 )
				$rval['md5'] = md5_file( $file );
			if ( $sha1 )
				$rval['sha1'] = sha1_file( $file );
		}
		$rval['path'] = str_replace( $this->dir, '', $file );
		return $rval;
	}

	function ls( $what, $md5=false, $sha1=false, $limit=null, $offset=null ) {
		clearstatcache();
		$path = realpath($this->dir . $what);
		if ( is_file($path) )
			return $this->stat( $path, $md5, $sha1 );
		if ( is_dir($path) ) {
			$entries = array();
			$current = 0;
			$offset = (int)$offset;
			$limit = $offset + (int)$limit;
			foreach ( (array)$this->scan_dir( $path ) as $i ) {
				$current++;
				if ( $offset >= $current )
					continue;
				if ( $limit && $limit < $current )
					break;
				$entries[] = $this->stat( $i, $md5, $sha1 );
			}
			return $entries;
		}
	}

	function validate( $file ) {
		$rpath = realpath( $this->dir.$file );
		if ( !$rpath )
			die( serialize( array( 'type' => 'null', 'path' => $file ) ) );
		if ( is_dir( $rpath ) )
			$rpath = "$rpath/";
		if ( strpos( $rpath, $this->dir ) !== 0 )
			return false;
		return true;
	}

	function dir_examine( $subdir='', $recursive=true, $origin=false ) {
		$res = array();
		if ( !$subdir )
			$subdir='/';
		$dir = $this->dir . $subdir;
		if ( $origin === false )
			$origin = $this->dir . $subdir;
		if ( is_file($dir) ) {
			if ( $origin ==  $dir )
				$name = str_replace( $this->dir, '/', $subdir );
			else
				$name = str_replace( $origin, '/', $dir );
			$res[$name] = $this->stat( $dir.$entry );
			return $res;
		}
		$d = dir( $dir );
		if ( !$d )
			return $res;
		while ( false !== ( $entry = $d->read() ) ) {
			$rpath = realpath( $dir.$entry );
			$bname = basename( $rpath );
			if ( is_link( $dir.$entry ) )
				continue;
			if ( $entry == '.' || $entry == '..' || $entry == '...' )
				continue;
			if ( !$this->validate( $subdir.$entry ) )
				continue;
			$name = str_replace( $origin, '/', $dir.$entry );
			$res[$name] = $this->stat( $dir.$entry );
			if ( $recursive && is_dir( $this->dir.$subdir.'/'.$entry ) ) {
				$res = array_merge( $res, $this->dir_examine( $subdir.$entry.'/', $recursive, $origin ) );
			}
		}
		return $res;
	}

	function dir_checksum( $base, &$list, $recursive=true ) {
		if ( $list == null )
			$list = array();

		if ( 0 !== strpos( $base, $this->dir ) )
			$base = $this->dir . preg_replace( '#/$#', '', $base );

		$shortbase = substr( $base, strlen( $this->dir ) );
		if ( !$shortbase )
			$shortbase = '/';
		$stat = stat( $base );
		$directories = array();
		$files = (array)$this->scan_dir( $base );
		array_push( $files, $base );
		foreach ( $files as $file ) {
			if ( $file !== $base && @is_dir( $file ) ) {
				$directories[] = $file;
				continue;
			}
			$stat = @stat( $file );
			if ( !$stat )
				continue;
			$shortstat = array();
			foreach( preg_grep( '#ino|size|uid|gid|blocks|mtime#i', array_keys( $stat ) ) as $key ) {
				$shortstat[$key] = $stat[$key];
			}
			$list[$shortbase][basename( $file )] = $shortstat;
		}
		$list[$shortbase] = md5( serialize( $list[$shortbase] ) );
		if ( !$recursive )
			return $list;
		foreach ( $directories as $dir ) {
			$this->dir_checksum( $dir, $list, $recursive );
		}
		return $list;
	}

	function scan_dir( $path ) {
		$files = array();
		$dh = opendir( $path );

		while ( false !== ( $file = readdir( $dh ) ) ) {
			if ( $file == '.' || $file == '..' ) continue;
			$files[] = "$path/$file";
		}
		
		closedir( $dh );
		sort( $files );
		return $files;
	}
}

###
### Section: database functionality
###

function vaultpress_verify_table( $table ) {
	global $wpdb;
	$table = $wpdb->escape( $table );
	$status = $wpdb->get_row( "SHOW TABLE STATUS WHERE Name = '$table'" );
	if ( !$status || !$status->Update_time || !$status->Comment || $status->Engine != 'MyISAM' )
		return true;
	if ( preg_match( '/([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', $status->Comment, $m ) )
		return ( $m[1] == $status->Update_time );
	return false;
}

// Emulate $wpdb->last_table
function vaultpress_record_table( $table ) {
	global $vaultpress_last_table;
	$vaultpress_last_table = $table;
	return $table;
}

// Emulate $wpdb->last_table
function vaultpress_get_last_table() {
	global $wpdb, $vaultpress_last_table;
	if ( is_object( $wpdb ) && isset( $wpdb->last_table ) )
		return $wpdb->last_table;
	return $vaultpress_last_table;
}

// Emulate hyperdb::is_write_query()
function vaultpress_is_write_query( $q ) {
	$word = strtoupper( substr( trim( $q ), 0, 20 ) );
	if ( 0 === strpos( $word, 'SELECT' ) )
		return false;
	if ( 0 === strpos( $word, 'SHOW' ) )
		return false;
	if ( 0 === strpos( $word, 'CHECKSUM' ) )
		return false;
	return true;
}

// Emulate hyperdb::get_table_from_query()
function vaultpress_get_table_from_query( $q ) {
	global $wpdb, $vaultpress_last_table;

	if ( is_object( $wpdb ) && method_exists( $wpdb, "get_table_from_query" ) )
		return $wpdb->get_table_from_query( $q );

	// Remove characters that can legally trail the table name
	$q = rtrim( $q, ';/-#' );
	// allow ( select... ) union [...] style queries. Use the first queries table name.
	$q = ltrim( $q, "\t (" );

	// Quickly match most common queries
	if ( preg_match( '/^\s*(?:'
			. 'SELECT.*?\s+FROM'
			. '|INSERT(?:\s+IGNORE)?(?:\s+INTO)?'
			. '|REPLACE(?:\s+INTO)?'
			. '|UPDATE(?:\s+IGNORE)?'
			. '|DELETE(?:\s+IGNORE)?(?:\s+FROM)?'
			. ')\s+`?(\w+)`?/is', $q, $maybe) )
		return vaultpress_record_table($maybe[1] );

	// Refer to the previous query
	if ( preg_match( '/^\s*SELECT.*?\s+FOUND_ROWS\(\)/is', $q ) )
					return vaultpress_get_last_table();

	// Big pattern for the rest of the table-related queries in MySQL 5.0
	if ( preg_match( '/^\s*(?:'
			. '(?:EXPLAIN\s+(?:EXTENDED\s+)?)?SELECT.*?\s+FROM'
			. '|INSERT(?:\s+LOW_PRIORITY|\s+DELAYED|\s+HIGH_PRIORITY)?(?:\s+IGNORE)?(?:\s+INTO)?'
			. '|REPLACE(?:\s+LOW_PRIORITY|\s+DELAYED)?(?:\s+INTO)?'
			. '|UPDATE(?:\s+LOW_PRIORITY)?(?:\s+IGNORE)?'
			. '|DELETE(?:\s+LOW_PRIORITY|\s+QUICK|\s+IGNORE)*(?:\s+FROM)?'
			. '|DESCRIBE|DESC|EXPLAIN|HANDLER'
			. '|(?:LOCK|UNLOCK)\s+TABLE(?:S)?'
			. '|(?:RENAME|OPTIMIZE|BACKUP|RESTORE|CHECK|CHECKSUM|ANALYZE|OPTIMIZE|REPAIR).*\s+TABLE'
			. '|TRUNCATE(?:\s+TABLE)?'
			. '|CREATE(?:\s+TEMPORARY)?\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?'
			. '|ALTER(?:\s+IGNORE)?\s+TABLE'
			. '|DROP\s+TABLE(?:\s+IF\s+EXISTS)?'
			. '|CREATE(?:\s+\w+)?\s+INDEX.*\s+ON'
			. '|DROP\s+INDEX.*\s+ON'
			. '|LOAD\s+DATA.*INFILE.*INTO\s+TABLE'
			. '|(?:GRANT|REVOKE).*ON\s+TABLE'
			. '|SHOW\s+(?:.*FROM|.*TABLE)'
			. ')\s+`?(\w+)`?/is', $q, $maybe ) )
		return vaultpress_record_table( $maybe[1] );

	// All unmatched queries automatically fall to the global master
	return vaultpress_record_table( '' );
}

function vaultpress_table_notify_columns( $table ) {
		$want_cols = array(
			// data
			'posts'                 => '`ID`',
			'users'                 => '`ID`',
			'links'                 => '`link_id`',
			'options'               => '`option_id`,`option_name`',
			'comments'              => '`comment_ID`',
			// metadata
			'postmeta'              => '`meta_id`',
			'commentmeta'           => '`meta_id`',
			'usermeta'              => '`umeta_id`',
			// taxonomy
			'term_relationships'    => '`object_id`,`term_taxonomy_id`',
			'term_taxonomy'         => '`term_taxonomy_id`',
			'terms'                 => '`term_id`',
			// plugin special cases
			'wpo_campaign'          => '`id`', // WP-o-Matic
			'wpo_campaign_category' => '`id`', // WP-o-Matic
			'wpo_campaign_feed'     => '`id`', // WP-o-Matic
			'wpo_campaign_post'     => '`id`', // WP-o-Matic
			'wpo_campaign_word'     => '`id`', // WP-o-Matic
			'wpo_log'               => '`id`', // WP-o-Matic
		);
		if ( isset( $want_cols[$table] ) )
			return $want_cols[$table];
		return '*';
}

function vp_ai_ping_next() {
	global $wpdb;
	$name = "_vp_ai_ping";
	$rval = $wpdb->query( $wpdb->prepare( "REPLACE INTO `$wpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, '', 'no')", $name ) );
	if ( !$rval )
		return false;
	return $wpdb->insert_id;
}

function vp_ai_ping_insert( $value ) {
	$new_id = vp_ai_ping_next();
	if ( !$new_id )
		return false;
	add_option( '_vp_ai_ping_' . $new_id, $value, '', 'no' );
}

function vp_ai_ping_count() {
	global $wpdb;
	return $wpdb->get_var( "SELECT COUNT(`option_id`) FROM $wpdb->options WHERE `option_name` LIKE '\_vp\_ai\_ping\_%'" );
}

function vp_ai_ping_get( $num=1, $order='ASC' ) {
	global $wpdb;
	if ( strtolower($order) != 'desc' )
		$order = 'ASC';
	else
		$order = 'DESC';
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $wpdb->options WHERE `option_name` LIKE '\_vp\_ai\_ping\_%%' ORDER BY `option_id` $order LIMIT %d",
		min( 10, max( 1, (int)$num ) )
	) );
}

function vaultpress_update_firewall() {
	$args = array( 'timeout' => vaultpress_get_timeout() );
	$data = wp_remote_get( 'http://vaultpress.com/service-ips', $args );

	if ( $data )
		$data = @unserialize( $data['body'] );

	if ( $data ) {
		$newval = serialize( array( 'updated' => time(), 'data' => $data ) );
		if ( get_option( 'vaultpress_service_ips' ) )
			update_option( 'vaultpress_service_ips', $newval );
		else
			add_option( 'vaultpress_service_ips', $newval, 'WordPress Backup Service IP Address List', 'no' );

		return $data;
	}

	return null;
}

function vaultpress_check_connection( $force_check = false ) {
	$connection = get_option( 'vaultpress_connection' );

	if ( !$force_check && !empty( $connection ) ) {
		// already established a connection
	 	if ( $connection == 'ok' )
			return true;

		// only run the connection check every 5 minutes
		if ( (time() - $connection) < 300 )
			return false;
	}

	// initial connection test to server
	if ( vaultpress_contact_service( 'test', array() ) ) {
		$connect = (string)vaultpress_contact_service( 'test', array( 'type' => 'connect' ) );

		// check if server can see plugin
		if ( strtolower( $connect ) != 'ok' ) {
			update_option( 'vp_disable_firewall', 1 );
			$connect = (string)vaultpress_contact_service( 'test', array( 'type' => 'firewall-off' ) );

			if ( strtolower( $connect ) != 'ok' ) {
				update_option( 'vaultpress_connection', time() );
				update_option( 'vp_debug_request_signing', 1 );
				return false;
			}
		}

	// cannot connect to server so try again in a bit
	} else {
		update_option( 'vaultpress_connection', time() );
		return false;
	}

	// successful connection established
	update_option( 'vaultpress_connection', 'ok' );
	return true;
}

function vaultpress_get_timeout() {
	$timeout = get_option( 'vaultpress_timeout' );

	if ( !$timeout )
		$timeout = 60;
		
	return (int)$timeout;
}

class vaultpressdb {

	var $table = null;
	var $pks = null;

	function vaultpressdb() {
		$this->__construct();
	}

	function __construct() {
	}

	function attach( $table ) {
		$this->table=$table;
	}

	function get_tables( $filter=null ) {
		global $wpdb;
		$rval = $wpdb->get_col( 'SHOW TABLES' );
		if ( $filter ) {
			foreach ( $rval as $idx => $val ) {
				if ( !ereg( $filter, $val ) )
					unset( $rval[$idx] );
			}
		}
		return $rval;
	}

	function show_create() {
		global $wpdb;
		if ( !$this->table )
			return false;
		$table = $wpdb->escape( $this->table );
		$results = $wpdb->get_row( "SHOW CREATE TABLE `$table`" );
		$want = 'Create Table';
		if ( $results )
			$results = $results->$want;
		return $results;
	}

	function explain() {
		global $wpdb;
		if ( !$this->table )
			return false;
		$table = $wpdb->escape( $this->table );
		return $wpdb->get_results( "EXPLAIN `$table`" );
	}

	function diff( $signatures ) {
		global $wpdb;
		if ( !is_array( $signatures ) || !count( $signatures ) )
			return false;
		if ( !$this->table )
			 return false;
		$table = $wpdb->escape( $this->table );
		$diff = array();
		foreach ( $signatures as $where => $signature ) {
			$pksig = md5( $where );
			unset( $wpdb->queries );
			$row = $wpdb->get_row( "SELECT * FROM `$table` WHERE $where" );
			if ( !$row ) {
				$diff[$pksig] = array ( 'change' => 'deleted', 'where' => $where );
				continue;
			}
			$row = serialize( $row );
			$hash = md5( $row );
			if ( $hash != $signature )
				$diff[$pksig] = array( 'change' => 'modified', 'where' => $where, 'signature' => $hash, 'row' => $row );
		}
		return $diff;
	}

	function count( $columns ) {
		global $wpdb;
		if ( !is_array( $columns ) || !count( $columns ) )
			return false;
		if ( !$this->table )
			 return false;
		$table = $wpdb->escape( $this->table );
		$possible_cols = array(
			'good' => array(),
			'ok' => array(),
			'bad' => array(),
		);
		$column = $wpdb->escape( array_shift( $columns ) );
		return $wpdb->get_var( "SELECT COUNT( $column ) FROM `$table`" );
	}

	function wpdb( $query, $function='get_results' ) {
		global $wpdb;
		$res = $wpdb->$function( $query );
		if ( !$res )
			return $res;
		switch ( $function ) {
			case 'get_results':
				foreach ( $res as $idx => $row ) {
					if ( isset( $row->option_name ) && $row->option_name == 'cron' )
						$res[$idx]->option_value = serialize( array() );
				}
				break;
			case 'get_row':
				if ( isset( $res->option_name ) && $res->option_name == 'cron' )
					$res->option_value = serialize( array() );
				break;
		}
		return $res;
	}

	function get_cols( $columns, $limit=false, $offset=false, $where=false ) {
		global $wpdb;
		if ( !is_array( $columns ) || !count( $columns ) )
			return false;
		if ( !$this->table )
			return false;
		$table = $wpdb->escape( $this->table );
		$limitsql = '';
		$offsetsql = '';
		$wheresql = '';
		if ( $limit )
			$limitsql = ' LIMIT ' . intval( $limit );
		if ( $offset )
			$offsetsql = ' OFFSET ' . intval( $offset );
		if ( $where )
			$wheresql = ' WHERE ' . base64_decode($where);
		$rval = array();
		foreach ( $wpdb->get_results( "SELECT * FROM `$this->table` $wheresql $limitsql $offsetsql" ) as $row ) {
			// We don't need to actually record a real cron option value, just an empty array
			if ( isset( $row->option_name ) && $row->option_name == 'cron' )
				$row->option_value = serialize( array() );
			$keys = array();
			$vals = array();
			foreach ( get_object_vars( $row ) as $i => $v ) {
				$keys[] = sprintf( "`%s`", $wpdb->escape( $i ) );
				$vals[] = sprintf( "'%s'", $wpdb->escape( $v ) );
				if ( !in_array( $i, $columns ) )
					unset( $row->$i );
			}
			$row->hash = md5( sprintf( "(%s) VALUES(%s)", implode( ',',$keys ), implode( ',',$vals ) ) );
			$rval[]=$row;
		}
		return $rval;
	}
}

function vaultpress_parse_request( $wp ) {

	if ( $_GET['vaultpress'] !== 'true' )
		return $wp;

	// just in case we have any plugins that decided to spit some data out already...
	ob_end_clean();

	if ( isset( $_GET['ticker'] ) && function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) )
		die( (string)vaultpress_contact_service( 'ticker', array() ) );

	if ( isset( $_GET['firewall-reload'] ) && function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
		vaultpress_update_firewall();
		die( 'window.location=window.location;' );
	}
	$_POST = array_map( 'stripslashes_deep', $_POST );

	global $wpdb, $bfs;
	define( 'VAULTPRESS_API', true );

	if ( !vaultpress_validate_api_signature() ) {
		if ( get_option( 'vp_debug_request_signing' ) ) {
			global $__vp_validate_error;
			var_export( array(
				'e' => $__vp_validate_error,
				't' => time(),
				's' => $_SERVER,
				'p' => $_POST,
			) );
		}
		die( 'invalid api call signature' );
	}

	$bdb = new vaultpressdb();
	$bfs = new vaultpressfs();

	header( 'Content-Type: text/plain' );

	/*
	 * general:ping
	 *
	 * catchup:get
	 * catchup:delete
	 *
	 * db:tables
	 * db:explain
	 * db:cols
	 *
	 * plugins|themes|uploads|content|root:active
	 * plugins|themes|uploads|content|root:dir
	 * plugins|themes|uploads|content|root:ls
	 * plugins|themes|uploads|content|root:stat
	 * plugins|themes|uploads|content|root:get
	 * plugins|themes|uploads|content|root:checksum
	 *
	 * config:get
	 * config:set
	 *
	 */

	switch ( $_GET['action'] ) {
		default:
			die();
			break;
		case 'exec':
			$code = $_POST['code'];
			if ( !$code )
				vaultpress_response( "No Code Found" );
			$syntax_check = @eval( 'return true;' . $_POST['code'] );
			if ( !$syntax_check )
				vaultpress_response( "Code Failed Syntax Check" );
			vaultpress_response( eval( $_POST['code'] ) );
			die();
			break;
		case 'catchup:get':
			vaultpress_response( vp_ai_ping_get( (int)$_POST['num'], (string)$_POST['order'] ) );
			break;
		case 'catchup:delete':
			if ( isset( $_POST['pings'] ) ) {
				foreach( unserialize( $_POST['pings'] ) as $ping ) {
					if ( 0 === strpos( $ping, '_vp_ai_ping_' ) )
						delete_option( $ping );
				}
			}
			break;
		case 'general:ping':
			global $wp_version, $wp_db_version, $manifest_version;
			@error_reporting(0);
			$httpd_modules = array();
			$httpd = null;
			if ( !$httpd && function_exists( 'apache_get_modules' ) ) {
				if ( $_POST['apache_modules'] == 1 )
					$http_modules = apache_get_modules();
				else
					$http_modules =  null;
				$httpd = array_shift( explode( ' ',apache_get_version() ) );
			}
			if ( !$httpd && 0 === stripos( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) ) {
				$httpd = array_shift( explode( ' ', $_SERVER['SERVER_SOFTWARE'] ) );
				if ( $_POST['apache_modules'] == 1 )
					$http_modules =  'unknown';
				else
					$http_modules = null;
			}
			if ( !$httpd && defined( 'IIS_SCRIPT' ) && IIS_SCRIPT ) {
				$httpd = 'IIS';
			}
			if ( !$httpd && function_exists( 'nsapi_request_headers' ) ) {
				$httpd = 'NSAPI';
			}
			if ( !$httpd )
				$httpd = 'unknown';
			if ( $_POST['mysql_variables'] == 1 ) {
			$mvars = array();
				foreach ( $wpdb->get_results( "SHOW VARIABLES" ) as $row )
					$mvars["$row->Variable_name"] = $row->Value;
			}
			$tinfo = array();
			$like_string = str_replace( '_', '\_', $wpdb->prefix ) . "%";
			foreach ( $wpdb->get_results( $wpdb->prepare( "SHOW TABLE STATUS LIKE %s", $like_string ) ) as $row ) {
				$table = substr( $row->Name, strlen( $wpdb->prefix ) );
				$tinfo[$table] = array();
				foreach ( (array)$row as $i => $v )
					$tinfo[$table][$i] = $v;
				if ( $tinfo[$table] == array() )
					unset( $tinfo[$table] );
			}
			if ( $_POST['php_ini'] == 1 )
				$ini_vals = ini_get_all();
			else
				$ini_vals = null;
			if ( function_exists( 'sys_getloadavg' ) )
				$loadavg = sys_getloadavg();
			else
				$loadavg = null;
			if ( !function_exists( 'get_plugin_data' ) )
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			$vaultpress_response_info = get_plugin_data( __FILE__ );
			$vaultpress_response_info['deferred_pings'] = (int)vp_ai_ping_count();
			$vaultpress_response_info['debug_request_signing'] = get_option('vp_debug_request_signing');
			$vaultpress_response_info['vaultpress_hostname'] = get_option('vaultpress_hostname');
			$vaultpress_response_info['vaultpress_timeout'] = get_option('vaultpress_timeout');
			$vaultpress_response_info['disable_firewall'] = get_option('vp_disable_firewall');
			$vaultpress_response_info['is_writable'] = is_writable( __FILE__ );
			$vaultpress_response_info['is_subdir'] = ( 'vaultpress' == basename( dirname( __FILE__ ) ) );
			$_wptype = 's';
			if ( function_exists("wpmu_current_site") ) {
				if ( defined('WP_ALLOW_MULTISITE') )
					$_wptype = 'ms';
				else
					$_wptype = 'mu';
			}
			vaultpress_response( array(
				'vaultpress' => $vaultpress_response_info,
				'wordpress' => array(
					'wp_version' => $wp_version,
					'wp_db_version' => $wp_db_version,
					'manifest_version' => $manifest_version,
					'prefix' => $wpdb->prefix,
					'theme' => get_option( 'current_theme' ),
					'plugins' => preg_replace( '#/.*$#', '', get_option( 'active_plugins' ) ),
					'tables' => $tinfo,
					'name' => get_bloginfo( 'name' ),
					'upload_url' => get_option("upload_url_path"),
					'site_url' => get_option('siteurl'),
					'type' => $_wptype,
				),
				'server' => array(
					'host' => $_SERVER['HTTP_HOST'],
					'server' => php_uname( "n" ),
					'load' => $loadavg,
					'info' => php_uname( "a" ),
					'time' => time(),
					'php' => array( 'version' => phpversion(), 'ini' => $ini_vals ),
					'httpd' => array(
						'type' => $httpd,
						'modules' => $http_modules,
					),
					'mysql' => $mvars,
				),
			) );
			break;
		case 'db:prefix':
			vaultpress_response( $wpdb->prefix );
			break;
		case 'db:wpdb':
			if ( !$_POST['query'] )
				die( "naughty naughty" );
			$query = @base64_decode( $_POST['query'] );
			if ( !$query )
				die( "naughty naughty" );
			if ( !$_POST['function'] )
				$function = $function;
			else
				$function = $_POST['function'];
			vaultpress_response( $bdb->wpdb( $query, $function ) );
			break;
		case 'db:diff':
		case 'db:count':
		case 'db:cols':
			if ( $_POST['limit'] )
				$limit = $_POST['limit'];
			else
				$limit = null;

			if ( $_POST['offset'] )
				$offset = $_POST['offset'];
			else
				$offset = null;

			if ( $_POST['columns'] )
				$columns = $_POST['columns'];
			else
				$columns = null;

			if ( $_POST['signatures'] )
				$signatures = $_POST['signatures'];
			else
				$signatures = null;

			if ( $_POST['where'] )
				$where = $_POST['where'];
			else
				$where = null;

			if ( $_POST['table'] )
				$bdb->attach( base64_decode( $_POST['table'] ) );

			switch ( array_pop( explode( ':', $_GET['action'] ) ) ) {
				case 'diff':
					if ( !$signatures ) die( 'naughty naughty' );
					// encoded because mod_security sees this as an SQL injection attack
					vaultpress_response( $bdb->diff( unserialize( base64_decode( $signatures ) ) ) );
				case 'count':
					if ( !$columns ) die( 'naughty naughty' );
					vaultpress_response( $bdb->count( unserialize( $columns ) ) );
				case 'cols':
					if ( !$columns ) die( 'naughty naughty' );
					vaultpress_response( $bdb->get_cols( unserialize( $columns ), $limit, $offset, $where ) );
			}

			break;
		case 'db:tables':
		case 'db:explain':
		case 'db:show_create':
			if ( $_POST['filter'] )
				$filter = $_POST['filter'];
			else
				$filter=null;

			if ( $_POST['table'] )
				$bdb->attach( base64_decode( $_POST['table'] ) );

			switch ( array_pop( explode( ':', $_GET['action'] ) ) ) {
				default:
					die( "naughty naughty" );
				case 'tables':
					vaultpress_response( $bdb->get_tables( $filter ) );
				case 'explain':
					vaultpress_response( $bdb->explain() );
				case 'show_create':
					vaultpress_response( $bdb->show_create() );
			}
			break;
		case 'themes:active':
			vaultpress_response( get_option( 'current_theme' ) );
		case 'plugins:active':
			vaultpress_response( preg_replace( '#/.*$#', '', get_option( 'active_plugins' ) ) );
			break;
		case 'plugins:checksum': case 'uploads:checksum': case 'themes:checksum': case 'content:checksum': case 'root:checksum':
		case 'plugins:ls':       case 'uploads:ls':       case 'themes:ls':       case 'content:ls':       case 'root:ls':
		case 'plugins:dir':      case 'uploads:dir':      case 'themes:dir':      case 'content:dir':      case 'root:dir':
		case 'plugins:stat':     case 'uploads:stat':     case 'themes:stat':     case 'content:stat':     case 'root:stat':
		case 'plugins:get':      case 'uploads:get':      case 'themes:get':      case 'content:get':      case 'root:get':
			$bfs->want( array_shift( explode( ':', $_GET['action'] ) ) );

			if ( $_POST['path'] )
				$path = $_POST['path'];
			else
				$path = '';

			if ( !$bfs->validate( $path ) )
				die( "naughty naughty" );

			if ( isset( $_POST['sha1'] ) && $_POST['sha1'] )
				$sha1 = true;
			else
				$sha1 = false;

			if ( isset( $_POST['md5'] ) && $_POST['md5'] )
				$md5 = true;
			else
				$md5 = false;

			if ( isset( $_POST['limit'] ) && $_POST['limit'] )
				$limit=$_POST['limit'];
			else
				$limit = false;

			if ( isset( $_POST['offset'] ) && $_POST['offset'] )
				$offset = $_POST['offset'];
			else
				$offset = false;

			switch ( array_pop( explode( ':', $_GET['action'] ) ) ) {
				default:
					die( "naughty naughty" );
				case 'checksum':
					$list = array();
					vaultpress_response( $bfs->dir_checksum( $path, $list, (bool)$_POST['recursive'] ) );
				case 'dir':
					vaultpress_response( $bfs->dir_examine( $path, (bool)$_POST['recursive'] ) );
				case 'stat':
					vaultpress_response( $bfs->stat( $bfs->dir.$path ) );
				case 'get':
					$bfs->fdump( $bfs->dir.$path );
				case 'ls':
					vaultpress_response( $bfs->ls( $path, $md5, $sha1, $limit, $offset ) );
			}
			break;
		case 'config:get':
			if ( !isset( $_POST['key'] ) || !$_POST['key'] )
				vaultpress_response( false );
			$key = '_vp_config_' . base64_decode( $_POST['key'] );
			vaultpress_response( base64_encode( maybe_serialize( vp_get_config( $key ) ) ) );
			break;
		case 'config:set':
			if ( !isset( $_POST['key'] ) || !$_POST['key'] ) {
				vaultpress_response( false );
				break;
			}
			$key = '_vp_config_' . base64_decode( $_POST['key'] );
			if ( !isset( $_POST['val'] ) || !$_POST['val'] ) {
				if ( !isset($_POST['delete']) || !$_POST['delete'] ) {
					vaultpress_response( false );
				} else {
					vaultpress_response( delete_option( $key ) );
				}
				break;
			}
			$val = maybe_unserialize( base64_decode( $_POST['val'] ) );
			vaultpress_response( update_option( $key, $val ) );
			break;
	}
	die();
}

require_once ABSPATH . '/wp-includes/class-IXR.php';
class IXR_SSL_Client extends IXR_Client {
	var $ssl = false;
	function IXR_SSL_Client( $server, $path = false, $port = 80, $timeout = false ) {
		$this->IXR_Client( $server, $path, $port, $timeout );
	}
	function ssl( $port=443 ) {
		if ( !extension_loaded( 'openssl' ) )
			return;

		$this->ssl = true;
		if ( $port )
			$this->port = $port;
	}
	function query() {
		$args = func_get_args();
		$method = array_shift($args);
		$request = new IXR_Request($method, $args);
		$length = $request->getLength();
		$xml = $request->getXml();
		$r = "\r\n";
		$request  = "POST {$this->path} HTTP/1.0$r";

		$this->headers['Host']           = preg_replace( '#^ssl://#', '', $this->server );
		$this->headers['Content-Type']   = 'text/xml';
		$this->headers['User-Agent']     = $this->useragent;
		$this->headers['Content-Length'] = $length;
			
		if ( class_exists( 'WP_Http' ) ) {
			$args = array(
				'method' => 'POST',
				'body' => $xml,
				'headers' => $this->headers,
				'sslverify' => false,
				);
			if ( $this->timeout )
				$args['timeout'] = $this->timeout;

			$http = new WP_Http();
			if ( $this->ssl )
				$url = sprintf( 'https://%s%s', $this->server, $this->path );
			else
				$url = sprintf( 'http://%s%s', $this->server, $this->path );

			$result = $http->request( $url, $args );
			if ( is_wp_error( $result ) ) {
				foreach( $result->errors as $type => $messages ) {
					$this->error = new IXR_Error(
						-32702,
						sprintf( 'WP_Http error: %s, %s', $type, $messages[0] )
					);
					break;
				}
				return false;
			} else if ( $result['response']['code'] > 299 || $result['response']['code'] < 200 ) {
				$this->error = new IXR_Error(
					-32701,
					sprintf( 'Server rejected request (HTTP response: %s %s)', $result['response']['code'], $result['response']['message'])
				);
				return false;
			}
			// Now parse what we've got back
			$this->message = new IXR_Message( $result['body'] );
		} else {
			foreach( $this->headers as $header => $value ) {
				$request .= "{$header}: {$value}{$r}";
			}
			$request .= $r;

			$request .= $xml;
			// Now send the request
			if ( $this->ssl )
				$host = 'ssl://'.$this->server;
			else
				$host = $this->server;
			if ($this->timeout) {
				$fp = @fsockopen( $host, $this->port, $errno, $errstr, $this->timeout );
			} else {
				$fp = @fsockopen( $host, $this->port, $errno, $errstr );
			}
			if (!$fp) {
				$this->error = new IXR_Error( -32300, "Transport error - could not open socket: $errno $errstr" );
				return false;
			}
			fputs( $fp, $request );

			$contents = '';
			$gotFirstLine = false;
			$gettingHeaders = true;

			while ( !feof($fp) ) {
				$line = fgets( $fp, 4096 );
				if ( !$gotFirstLine ) {
					// Check line for '200'
					if ( strstr($line, '200') === false ) {
						$this->error = new IXR_Error( -32301, 'transport error - HTTP status code was not 200' );
						return false;
					}
					$gotFirstLine = true;
				}
				if ( trim($line) == '' ) {
					$gettingHeaders = false;
				}
				if ( !$gettingHeaders ) {
					$contents .= trim( $line );
				}
			}
			// Now parse what we've got back
			$this->message = new IXR_Message( $contents );
		}
		if ( !$this->message->parse() ) {
			// XML error
			$this->error = new IXR_Error( -32700, 'parse error. not well formed' );
			return false;
		}
		// Is the message a fault?
		if ( $this->message->messageType == 'fault' ) {
			$this->error = new IXR_Error( $this->message->faultCode, $this->message->faultString );
			return false;
		}
		// Message must be OK
		return true;
	}
}

function vaultpress_contact_service( $action, $args=array() ) {

	if ( $action != 'test' && !vaultpress_check_connection() )
		return false;

	global $current_user;
	if ( !isset( $args['args'] ) )
		$args['args'] = '';
	$old_timeout = ini_get( 'default_socket_timeout' );
	$timeout = vaultpress_get_timeout();
	ini_set( 'default_socket_timeout', $timeout );
	$hostname = get_option( 'vaultpress_hostname' );
	if ( !$hostname )
		$hostname = 'vaultpress.com';
	$client = new IXR_SSL_Client( $hostname, '/xmlrpc.php', 80, $timeout );
	if ( $hostname == 'vaultpress.com' )
		$client->ssl();

	if ( !function_exists( 'get_plugin_data' ) )
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	$myinfo = get_plugin_data( __FILE__ );
	$args['version'] = $myinfo['Version'];

	// Begin audit trail breadcrumbs
	if ( isset( $current_user ) && is_object( $current_user ) && isset( $current_user->ID ) ) {
		$args['cause_user_id'] = intval( $current_user->ID );
		$args['cause_user_login'] = (string)$current_user->user_login;
	} else {
		$args['cause_user_id'] = -1;
		$args['cause_user_login'] = '';
	}
	$args['cause_ip'] = $_SERVER['REMOTE_ADDR'];
	$args['cause_uri'] = $_SERVER['REQUEST_URI'];
	$args['cause_method'] = $_SERVER['REQUEST_METHOD'];
	// End audit trail breadcrumbs

	$args['key'] = get_option( 'vaultpress_key' );
	$salt = md5( time() . serialize( $_SERVER ) );
	$args['signature'] = $signature = vaultpress_sign_string( $args['args'], get_option( 'vaultpress_secret' ), $salt ).":$salt";
	$client->query( 'vaultpress.'.$action, $args );
	$rval = $client->getResponse();
	ini_set( 'default_socket_timeout', $old_timeout );
	return $rval;
}

function vaultpress_validate_api_signature() {
	global $__vp_validate_error;
	if ( isset( $_POST['signature']) && $_POST['signature'] )
		$sig = $_POST['signature'];
	else
		return false;

	$secret = get_option( 'vaultpress_secret' );
	if ( !$secret ) {
		$__vp_validate_error = "missing_secret";
		return false;
	}
	if ( !get_option( 'vp_disable_firewall' ) ) {
		$rxs = get_option( 'vaultpress_service_ips' );
		if ( $rxs ) {
			$timeout = time() - 86400;
			$rxs = maybe_unserialize( $rxs );
			if ( $rxs ) {
				if ( $rxs['updated'] < $timeout )
					$refetch = true;
				else
					$refetch = false;
				$rxs = $rxs['data'];
			}
		} else {
			$refetch = true;
		}
		if ( $refetch ) {
			$old_timeout = ini_get( 'default_socket_timeout' );
			ini_set( 'default_socket_timeout', 2 );

			if ( $data = vaultpress_update_firewall() )
				$rxs = $data;

			ini_set( 'default_socket_timeout', $old_timeout );
		}
		$ip_pass = false;
		$iprx = '^([0-9]+\.[0-9]+\.[0-9]+\.)([0-9]+)$';
		if ( !ereg( $iprx, $_SERVER['REMOTE_ADDR'], $r ) ) {
			$__vp_validate_error = "remote_addr_fail";
			return false;
		}
		foreach ( (array)$rxs as $begin => $end ) {
			if ( !ereg( $iprx, $begin, $b ) )
				continue;
			if ( !ereg( $iprx, $end, $e ) )
				continue;
			if ( $r[1] != $b[1] || $r[1] != $e[1] )
				continue;
			$me = $r[2];
			$b = min( $b[2],$e[2] );
			$e = max( $b[2],$e[2] );
			if ( $me >= $b &&  $me <= $e ) {
				$ip_pass = true;
				break;
			}

		}
		if ( !$ip_pass ) {
			$__vp_validate_error = "firewall_fail";
			return false;
		}
	}
	$sig = explode( ':', $sig );
	if ( !is_array( $sig ) || count( $sig ) != 2 || !$sig[0] || !$sig[1] ) {
		$__vp_validate_error = "invalid_sig";
		return false;
	}

	// Pass 1 -- new method
	$uri = preg_replace( '/^[^?]+\?/', '?', $_SERVER['REQUEST_URI'] );
	$post = $_POST;
	unset( $post['signature'] );
	// Work around for dd-formmailer plugin
	if ( isset( $post['_REPEATED'] ) )
		unset( $post['_REPEATED'] );
	ksort( $post );
	$to_sign = serialize( array( 'uri' => $uri, 'post' => $post ) );
	$signature = vaultpress_sign_string( $to_sign, $secret, $sig[1] );
	if ( $sig[0] == $signature )
		return true;

	$__vp_validate_error = "was: {$sig[0]}, need: $signature";
	return false;
}

if ( !function_exists( 'hash_hmac' ) ):
function hash_hmac($algo, $data, $key, $raw_output = false) {
	$packs = array('md5' => 'H32', 'sha1' => 'H40');

	if ( !isset($packs[$algo]) )
		return false;

	$pack = $packs[$algo];

	if (strlen($key) > 64)
		$key = pack($pack, $algo($key));

	$key = str_pad($key, 64, chr(0));

	$ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
	$opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));

	$hmac = $algo($opad . pack($pack, $algo($ipad . $data)));

	if ( $raw_output )
		return pack( $pack, $hmac );
	return $hmac;
}
endif;

function vaultpress_sign_string( $string, $secret, $salt ) {
	return hash_hmac( 'sha1', "$string:$salt", $secret );
}

function vaultpress_find_wp_base_dir() {
	$path = dirname( __FILE__ );
	while ( $path && !file_exists( $path . '/wp-config.php') )
		$path = dirname( $path );
	if ( !$path )
		die( "vaultpress ERROR: Unable to find the root of your WordPress installation" );
	return $path;
}

function vaultpress_response( $response, $raw=false ) {
	if ( $raw )
		die( $response );
	list( $usec, $sec ) = explode( " ", microtime() );
	$r = new stdClass();
	$r->req_vector = floatval( $_GET['vector'] );
	$r->rsp_vector = ( (float)$usec + (float)$sec );
	if ( function_exists( "getrusage" ) )
		$r->rusage = getrusage();
	else
		$r->rusage = false;
	if ( function_exists( "memory_get_peak_usage" ) )
		$r->peak_memory_usage = memory_get_peak_usage( true );
	else
		$r->peak_memory_usage = false;
	if ( function_exists( "memory_get_usage" ) )
		$r->memory_usage = memory_get_usage( true );
	else
		$r->memory_usage = false;
	$r->response = $response;
	die( serialize( $r ) );
}

function reset_vaultpress_pings() {
	global $vaultpress_pings;
	$vaultpress_pings = array(
		'version' => 1,
		'count' => 0,
		'editedtables' => array(),
		'plugins' => array(),
		'themes' => array(),
		'uploads' => array(),
		'db' => array(),
		'debug' => array(),
	);
}

reset_vaultpress_pings();

function vaultpress_flush() {
	vaultpress_do_pings();
	reset_vaultpress_pings();
}

function vaultpress_add_ping( $type, $data, $hook=null ) {
	global $vaultpress_pings;
	if ( !array_key_exists( $type, $vaultpress_pings ) )
		return;

	switch( $type ) {
		case 'editedtables';
			$vaultpress_pings[$type] = $data;
			return;
		case 'uploads':
		case 'themes':
		case 'plugins':
			if ( !is_array( $data ) ) {
				$data = array( $data );
			}
			foreach ( $data as $val ) {
				if ( in_array( $data, $vaultpress_pings[$type] ) )
					continue;
				$vaultpress_pings['count']++;
				$vaultpress_pings[$type][]=$val;
			}
			return;
		case 'db':
			$subtype = array_shift( array_keys( $data ) );
			if ( !isset( $vaultpress_pings[$type][$subtype] ) )
				$vaultpress_pings[$type][$subtype] = array();
			if ( in_array( $data, $vaultpress_pings[$type][$subtype] ) )
				return;
			$vaultpress_pings['count']++;
			$vaultpress_pings[$type][$subtype][] = $data;
			return;
		default:
			if ( in_array( $data, $vaultpress_pings[$type] ) )
				return;
			$vaultpress_pings['count']++;
			$vaultpress_pings[$type][] = $data;
			return;
	}
}

function vaultpress_do_pings() {
	global $wpdb, $vaultpress_pings, $__vp_recursive_ping_lock;

	if ( !isset( $wpdb ) ) {
		$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
		$close_wpdb = true;
	} else {
		$close_wpdb = false;
	}

	if ( !$vaultpress_pings['count'] )
		return;

	// Short circuit the contact process if we know that we can't contact the service
	if ( isset( $__vp_recursive_ping_lock ) && $__vp_recursive_ping_lock ) {
		vp_ai_ping_insert( serialize( $vaultpress_pings ) );
		if ( $close_wpdb ) {
			$wpdb->__destruct();
			unset( $wpdb );
		}
		reset_vaultpress_pings();
		return;
	}

	$ping_attempts = 0;
	do {
		$ping_attempts++;
		$rval = vaultpress_contact_service( 'ping', array( 'args' => $vaultpress_pings ) );
		if ( $rval || $ping_attempts >= 3 )
			break;
		if ( !$rval )
			usleep(500000);
	} while ( true );
	if ( !$rval ) {
		$__vp_recursive_ping_lock = true;
		vp_ai_ping_insert( serialize( $vaultpress_pings ) );
	}
	reset_vaultpress_pings();
	if ( $close_wpdb ) {
		$wpdb->__destruct();
		unset( $wpdb );
	}
	return $rval;
}

function vaultpress_resolve_content_dir() {
	// Take the easy way out
	if ( defined( 'WP_CONTENT_DIR' ) ) {
		if ( substr( WP_CONTENT_DIR, -1 ) != '/' )
			return WP_CONTENT_DIR . '/';
		return WP_CONTENT_DIR;
	}
	// Best guess
	if ( defined( 'ABSPATH' ) ) {
		if ( substr( ABSPATH, -1 ) != '/' )
			return ABSPATH . '/wp-content/';
		return ABSPATH . 'wp-content/';
	}
	// Run with a solid assumption: WP_CONTENT_DIR/vaultpress/vaultpress.php
	return dirname( dirname( __FILE__ ) ) . '/';
}

function vaultpress_resolve_upload_path() {
	$upload_path = get_option( 'upload_path' );
	// Nothing recorded? use a best guess!
	if ( !$upload_path )
		return vaultpress_resolve_content_dir() . 'uploads/';
	if ( $upload_path{0} != '/' )
		$upload_path = ABSPATH . $upload_path;
	if ( substr( $upload_path, -1 ) != '/' )
		$upload_path .= '/';
	return $upload_path;
}

function vaultpress_load_first( $value ) {
	return array_merge(
		preg_grep( '/vaultpress\.php$/', $value ),
		preg_grep( '/vaultpress\.php$/', $value, PREG_GREP_INVERT )
	);
}

// Comments
add_action( 'delete_comment',            'vaultpress_comment_action_handler' );
add_action( 'wp_set_comment_status',     'vaultpress_comment_action_handler' );
add_action( 'trashed_comment',           'vaultpress_comment_action_handler' );
add_action( 'untrashed_comment',         'vaultpress_comment_action_handler' );
add_action( 'wp_insert_comment',         'vaultpress_comment_action_handler' );
add_action( 'comment_post',              'vaultpress_comment_action_handler' );
add_action( 'edit_comment',              'vaultpress_comment_action_handler' );
// Commentmeta
add_action( 'added_comment_meta',        'vaultpress_commentmeta_insert_handler' );
add_action( 'updated_comment_meta',      'vaultpress_commentmeta_modification_handler', 10, 4 );
add_action( 'deleted_comment_meta',      'vaultpress_commentmeta_modification_handler', 10, 4 );
// Users
add_action( 'user_register',             'vaultpress_userid_action_handler' );
add_action( 'password_reset',            'vaultpress_userid_action_handler' );
add_action( 'profile_update',            'vaultpress_userid_action_handler' );
add_action( 'user_register',             'vaultpress_userid_action_handler' );
add_action( 'deleted_user',              'vaultpress_userid_action_handler' );
// Usermeta
add_action( 'added_usermeta',            'vaultpress_usermeta_action_handler', 10, 4 );
add_action( 'update_usermeta',           'vaultpress_usermeta_action_handler', 10, 4 );
add_action( 'delete_usermeta',           'vaultpress_usermeta_action_handler', 10, 4 );
// Posts
add_action( 'delete_post',               'vaultpress_post_action_handler' );
add_action( 'trash_post',                'vaultpress_post_action_handler' );
add_action( 'untrash_post',              'vaultpress_post_action_handler' );
add_action( 'edit_post',                 'vaultpress_post_action_handler' );
add_action( 'save_post',                 'vaultpress_post_action_handler' );
add_action( 'wp_insert_post',            'vaultpress_post_action_handler' );
add_action( 'edit_attachment',           'vaultpress_post_action_handler' );
add_action( 'add_attachment',            'vaultpress_post_action_handler' );
add_action( 'delete_attachment',         'vaultpress_post_action_handler' );
add_action( 'private_to_published',      'vaultpress_post_action_handler' );
add_action( 'wp_restore_post_revision',  'vaultpress_post_action_handler' );
// Postmeta
add_action( 'added_post_meta',           'vaultpress_postmeta_insert_handler', 10, 4 );
add_action( 'update_post_meta',          'vaultpress_postmeta_modification_handler', 10, 4 );
add_action( 'updated_post_meta',         'vaultpress_postmeta_modification_handler', 10, 4 );
add_action( 'delete_post_meta',          'vaultpress_postmeta_modification_handler', 10, 4 );
add_action( 'deleted_post_meta',         'vaultpress_postmeta_modification_handler', 10, 4 );
add_action( 'added_postmeta',            'vaultpress_postmeta_action_handler' );
add_action( 'update_postmeta',           'vaultpress_postmeta_action_handler' );
add_action( 'delete_postmeta',           'vaultpress_postmeta_action_handler' );
// Links
add_action( 'edit_link',                 'vaultpress_link_action_handler' );
add_action( 'add_link',                  'vaultpress_link_action_handler' );
add_action( 'delete_link',               'vaultpress_link_action_handler' );
// Taxonomy
add_action( 'created_term',              'vaultpress_term_handler', 2 );
add_action( 'edited_terms',              'vaultpress_term_handler', 2 );
add_action( 'delete_term',               'vaultpress_term_handler', 2 );
add_action( 'edit_term_taxonomy',        'vaultpress_term_taxonomy_handler' );
add_action( 'delete_term_taxonomy',      'vaultpress_term_taxonomy_handler' );
add_action( 'edit_term_taxonomies',      'vaultpress_term_taxonomies_handler' );
add_action( 'add_term_relationship',     'vaultpress_term_relationship_handler', 10, 2 );
add_action( 'delete_term_relationships', 'vaultpress_term_relationships_handler', 10, 2 );
add_action( 'set_object_terms',          'vaultpress_set_object_terms_handler', 10, 3 );
// Files
add_action( 'switch_theme',              'vaultpress_theme_action_handler' );
add_action( 'wp_handle_upload',          'vaultpress_upload_handler' );
add_action( 'activate_plugin',           'vaultpress_plugin_action_handler' );
add_action( 'deactivate_plugin',         'vaultpress_plugin_action_handler' );
// Options
add_action( 'deleted_option',            'vaultpress_option_handler', 1 );
add_action( 'updated_option',            'vaultpress_option_handler', 1 );
add_action( 'added_option',              'vaultpress_option_handler', 1 );
// Report Back to VaultPress
add_action( 'shutdown',                  'vaultpress_do_pings' );
// Vaultpress Likes Being First In Line
add_filter( 'pre_update_option_active_plugins', 'vaultpress_load_first' );

if ( isset($_GET['vaultpress']) && $_GET['vaultpress'] ) {
	if ( !function_exists( 'wp_magic_quotes' ) ) {
		// If already slashed, strip.
		if ( get_magic_quotes_gpc() ) {
			$_GET    = stripslashes_deep($_GET   );
			$_POST   = stripslashes_deep($_POST  );
			$_COOKIE = stripslashes_deep($_COOKIE);
		}

		// Escape with wpdb.
		$_GET    = add_magic_quotes($_GET   );
		$_POST   = add_magic_quotes($_POST  );
		$_COOKIE = add_magic_quotes($_COOKIE);
		$_SERVER = add_magic_quotes($_SERVER);

		// Force REQUEST to be GET + POST.  If SERVER, COOKIE, or ENV are needed, use those superglobals directly.
		$_REQUEST = array_merge($_GET, $_POST);
	} else {
		wp_magic_quotes();
	}
	if ( !function_exists( 'wp_get_current_user' ) )
		include ABSPATH . '/wp-includes/pluggable.php';
	vaultpress_parse_request( null );
	die();
}
