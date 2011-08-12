<?php
class EnabledModernizr extends Feature
{
  public function SetSettings()
  {
    $this->title        = '<a href="http://modernizr.com/" target="_blank" title="Goto site">Modernizr</a>';
    $this->information  = __( 'Modernizr is a small and simple JavaScript library that helps you take advantage of emerging web technologies (CSS3, HTML 5) while still maintaining a fine level of control over older browsers that may not yet support these new technologies. v1.7', 'developer-tools' );
    $this->fields       = array(
      array( 
        'fieldType' => 'Checkbox',
        'name' => 'enabled',
        'label' => __( 'Enable', 'developer-tools' )
      )
    );
  }
                
  public function Enabled( $value )
  {
    if( !IS_WP_ADMIN )
      add_action('init', array(&$this, 'Init'));
  }
  
  public function Init()
  {
    wp_register_script('modernizr', DEVELOPER_TOOLS_URL.'libs/modernizr/modernizr-1.7.js', array(), '1.7');
    wp_enqueue_script('modernizr'); 
  }
}