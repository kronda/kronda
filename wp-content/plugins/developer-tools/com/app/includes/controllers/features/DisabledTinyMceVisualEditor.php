<?php
class DisableTinyMceVisualEditor extends Feature
{ 
  public function SetSettings()
  {
    $this->title        = __( 'Disable TinyMCE visual editor', 'developer-tools' );
    $this->information  = __( 'This will completely remove the visual editor for all users.', 'developer-tools' );
    $this->fields       = array(
      array( 
        'fieldType' => 'Checkbox',
        'name' => 'enabled',
        'label' => 'Enable'
      )
    );    
  }
                
  public function Enabled($value)
  {
    add_action('profile_update', array( &$this, 'DisableEditor' ) );
    add_action('user_register', array( &$this, 'DisableEditor' ) );
  }
  
  public function DisableEditor($user_id){
    global $wpdb;
    $query = $wpdb->query("
      UPDATE $wpdb->usermeta SET meta_value = 'false'
      WHERE meta_key = 'rich_editing'"    
    );
  }
  
}