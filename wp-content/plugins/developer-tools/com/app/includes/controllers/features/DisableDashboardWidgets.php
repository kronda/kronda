<?php
class DisableDashboardWidgets extends Feature
{ 
  public function SetSettings()
  {
    $this->title        = __( 'Disable dashboard widgets', 'developer-tools' );
    $this->fields       = array(
      array( 
        'fieldType' => 'MultipleCheckboxes',
        'label' => __( 'Left column dashboard widgets', 'developer-tools' ),				
        'name' => 'normal',
        'fieldDataMethod' => 'fieldDataMethod1'
      ),
      array( 
        'fieldType' => 'MultipleCheckboxes',
        'label' => __( 'Right column dashboard widgets', 'developer-tools' ),   				
        'name' => 'side',
        'fieldDataMethod' => 'fieldDataMethod2'
      ),			    
    );
  }
	
  public function Enabled($value)
  {
    $this->value = $value;
    add_action('wp_dashboard_setup', array(&$this, 'removeDashboardWidgets' ) );
  }
	
  public function fieldDataMethod1()
  {
    $this->data  = 
			'dashboard_right_now' . "|" . __( 'Right Now', 'developer-tools' ) . "\n" . 
			'dashboard_recent_comments' . "|" . __( 'Recent Comments', 'developer-tools' ) . "\n" . 
			'dashboard_incoming_links' . "|" . __( 'Incoming Links', 'developer-tools' ) . "\n" .
			'dashboard_plugins' . "|" . __( 'Plugins', 'developer-tools' ) . "\n"
		;
  }	

  public function fieldDataMethod2()
  {
    $this->data  = 
			'dashboard_quick_press' . "|" . __( 'QuickPress', 'developer-tools' ) . "\n" . 
			'dashboard_recent_drafts' . "|" . __( 'Recent Drafts', 'developer-tools' ) . "\n" . 
			'dashboard_primary' . "|" . __( 'WordPress Blog', 'developer-tools' ) . "\n" .
			'dashboard_secondary' . "|" . __( 'Other WordPress News', 'developer-tools' ) . "\n"
		;
	}
	
	public function removeDashboardWidgets()
	{
      global $wp_meta_boxes;
			if( $this->value['normal'] ) 
			foreach( $this->value['normal'] as $widget ) 
			 unset($wp_meta_boxes['dashboard']['normal']['core'][$widget]);
			 
			if( $this->value['side'] ) 
			 foreach( $this->value['side'] as $widget ) 
			   unset($wp_meta_boxes['dashboard']['side']['core'][$widget]);	
	}
}