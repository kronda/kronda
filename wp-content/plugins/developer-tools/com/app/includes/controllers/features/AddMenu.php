<?php 
class AddMenu extends Feature
{ 
  public function SetSettings()
  {
    $this->title          = __( 'Add menu', 'developer-tools' );
    $this->multiple       = true;    
    $this->information    = __( 'Once enabled, menus appear under the "Appearance" -> "Menus" admin tab.  From there you create a new menu, and assign that menu under the "Theme Locations" panel.', 'developer-tools' );
    $this->codeSample     = array(
      'code' => '&lt;?php <a href="http://codex.wordpress.org/Function_Reference/wp_nav_menu" target="_blank">wp_nav_menu</a>( array( "theme_location" =&gt; "<span class="replace-1"></span>", "container_class" =&gt; "<span class="replace-2">menu</span>" ) ); ?&gt;',
      'placement' => 'outside'
    );
    $this->fields       = array(
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Unique identifier', 'developer-tools' ),
        'name' => 'id',
        'required' => true,
        'characterSet' => 'alphaNumericHyphenUnderscore',
        'codeReplaceClass' => 'replace-1'
      ),
      array( 
        'fieldType' => 'TextInput',
        'label' => __( 'Name', 'developer-tools' ),
        'name' => 'name',
        'characterSet' => 'alphaNumericSpace'       
      ),
      array( 
        'fieldType' => 'TextInput',
        'label' => __( 'CSS container class', 'developer-tools' ),
        'name' => 'class',
        'characterSet' => 'alphaNumericSpaceHyphenUnderscore',
        'codeReplaceClass' => 'replace-2',
        'advanced' => true        
      )      
    );
  }
  
  public function Enabled($value)
  {
    $this->value = $value;
    add_action( 'init', array( &$this, 'RegisterMenus' ) );
  }
  
  public function RegisterMenus()
  {
    if ( function_exists( 'register_nav_menus' ) ) {
      $registerMenusArray = array();
      foreach( $this->value as $menuItem )
      {
        if( !$menuItem['id'] ) continue;
        $registerMenusArray[$menuItem['id']] = ( $menuItem['name'] ? $menuItem['name'] : $menuItem['id'] );
      }
      register_nav_menus( $registerMenusArray );
    }    
  }
}