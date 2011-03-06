<?php
class DisablePluginUpdates extends Feature
{
	public function SetSettings()
	{
		$this->title				= __( 'Disable all plugin updates', 'developer-tools' );
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
		# 2.8 to 3.0:
		remove_action( 'load-plugins.php', 'wp_update_plugins' );
		remove_action( 'load-update.php', 'wp_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'wp_update_plugins', 'wp_update_plugins' );
		add_filter( 'pre_transient_update_plugins', create_function( '$a', "return null;" ) );
		
		# 3.0:
		remove_action( 'load-update-core.php', 'wp_update_plugins' );
		add_filter( 'pre_site_transient_update_plugins', create_function( '$a', "return null;" ) );
	}
}