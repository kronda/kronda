<?php
class EnableBackgroundThemeOption extends Feature
{
	public function SetSettings()
  {
    $this->title              = __( 'Enable "background image" theme option', 'developer-tools' );
		$this->description   			= __( 'Adds the "Background" theme option under the "Appearance" admin menu tab.', 'developer-tools' );
		$this->fields       			= array(
      array( 
        'fieldType' => 'Checkbox',
        'name' => 'background',
        'label' => __( 'Enable', 'developer-tools' )
      )     
    );
	}
	
  public function Enabled($value)
  {
    add_action( 'after_setup_theme', array( &$this, 'AfterThemeSetup' ) );
  }	
	
	public function AfterThemeSetup()
	{
		add_custom_background();
	}	
}
