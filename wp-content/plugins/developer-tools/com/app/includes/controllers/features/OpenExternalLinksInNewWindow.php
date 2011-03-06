<?php
class OpenExternalLinksInNewWindow extends Feature
{
	public function SetSettings()
	{
		$this->title				= __( 'Open external links in a new window / tab', 'developer-tools' );
		$this->information	= __( 'Automatically open external links in a new window / tab. This will automatically load jQuery if it has not already been loaded.', 'developer-tools' );
		$this->fields				= array(
			array( 
				'fieldType' => 'Checkbox',
				'label' => __( 'Enable', 'developer-tools' ),
				'name' => 'enabled',
				'afterLabel' => __( 'This also adds the CSS class "external_link" to the anchor tags.', 'developer-tools' )
			)
		);
	}
								
	public function Enabled($value)
	{
    if( !IS_WP_ADMIN )
      add_action('init', array(&$this, 'Init'));
    add_action('wp_print_scripts', array(&$this, 'PrintScripts'));
	}
  
  public function Init() { wp_register_script('open-external-links-in-new-window', DEVELOPER_TOOLS_URL.'libs/open-external-links-in-new-window.js', array('jquery')); }  
	public function PrintScripts(){ wp_enqueue_script('open-external-links-in-new-window'); }	
}