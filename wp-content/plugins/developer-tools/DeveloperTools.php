<?php
/*
Plugin Name: Developer Tools
Plugin URI: http://developertools.kjmeath.com
Description: WordPress developer tools. This plugin requires PHP5 or greater and wordpress 3.x.
Version: 1.1.1
Author: KJ Meath
Min WP Version: 3.0
Max WP Version: 3.1
*/

define( "DEVELOPER_TOOLS_VERSION", "1.1.1" );

if (version_compare(PHP_VERSION, '5.0.0', '<'))
{
  add_action( 'admin_notices', 'developer_tools_php_fail', 1 );
}
else
{
  add_action( 'plugins_loaded', 'developer_tools_init'); 
}

function developer_tools_init()
{
  add_action('admin_init', 'developer_tools_menu_item_script');
  include_once WP_PLUGIN_DIR . '/developer-tools/com/app/MainApplication.php';
}

function developer_tools_menu_item_script()
{
  wp_register_script( 'developer_tools-menu_item', WP_PLUGIN_URL.'/developer-tools/js/developer-tools-menu-item.js', array('jquery'));
  wp_enqueue_script( 'developer_tools-menu_item' );
}

function developer_tools_php_fail()
{
  $current_user = wp_get_current_user();
  if( $current_user->data->wp_capabilities['administrator'] )
  {  
    print '<div class="message error"><p>' . sprintf( __( 'The Developer Tools plugin requires PHP5 or greater. The version of PHP installed is %s.', 'developer-tools' ), PHP_VERSION ) . '</p></div>';
  }
}

if ( function_exists( 'register_uninstall_hook' ) )
{
  register_uninstall_hook(__FILE__, 'developer_tools_deinstall'); 
}
 
function developer_tools_deinstall()
{
  delete_option( 'developer-tools-values' );
  delete_option( 'developer-tools-uploads' );
  delete_option('developer-tools-server-configuration');
}