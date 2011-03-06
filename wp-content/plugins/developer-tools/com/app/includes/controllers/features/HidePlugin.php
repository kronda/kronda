<?php
class HidePlugin extends Feature
{
	public function SetSettings()
	{	
		$this->uid1accessOnly		= true;	
		$this->title				    = __( 'Hide Developer Tools plugin', 'developer-tools' );
		$this->description			= __( 'Only allow user account:', 'developer-tools' ) . ' <strong>'.UID1_USERNAME.'</strong> ' . __('to access to the Developer Tools plugin.', 'developer-tools' );
		$this->fields			    	= array(
			array( 
				'fieldType' => 'Checkbox',
        'name' => 'enabled',
        'label' => __( 'Enable', 'developer-tools' )
			)
		);
	}
	
	public function Enabled($value){}
}