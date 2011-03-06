<?php
class DisableWpCoreUpdates extends Feature
{	
	public function SetSettings()
	{
		$this->title				= __( 'Disable WordPress Core Updates', 'developer-tools' );
		$this->fields				= array(
			array( 
				'fieldType' => 'Checkbox',
        'name' => 'enabled',
        'label' => 'Enable'
			)
		);
	}				
	
	public function Enabled($value)
	{	
		//disable core update 2.8 to 3.0:
		remove_action( 'wp_version_check', 'wp_version_check' );
		remove_action( 'admin_init', '_maybe_update_core' );
		add_filter( 'pre_transient_update_core', create_function( '$a', "return null;" ) );
		
		//disable core update 3.0:
		add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );# 2.3 to 2.7:
		add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ), 2 );
		add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );
		
		if( IS_WP_ADMIN )
			add_action('admin_head', array( &$this, 'RemoveUpdatesPage' ));
	}
	
	public function RemoveUpdatesPage()
	{
	  global $submenu;
	  unset($submenu['index.php']);
	}
}