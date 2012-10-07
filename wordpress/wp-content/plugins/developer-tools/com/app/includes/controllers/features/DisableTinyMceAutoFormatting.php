<?php
class DisableTinyMceAutoFormatting extends Feature
{	
	public function SetSettings()
	{
		$this->title				= __( 'Disable TinyMCE Auto-formatting', 'developer-tools' );
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
		remove_filter('the_content', 'wpautop');
		remove_filter('the_content', 'wptexturize');		
	}
}