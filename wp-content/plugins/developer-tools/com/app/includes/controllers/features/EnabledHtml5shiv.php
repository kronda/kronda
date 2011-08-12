<?php
class EnabledHtml5shiv extends Feature
{
  public function SetSettings()
  {
    $this->title        = '<a href="http://code.google.com/p/html5shiv/" target="_blank" title="Goto site">HTML5 shiv</a>';
    $this->information  = __( 'This is a JavaScript library that forces older versions of Internet Explorer to acknowledge HTML5 tags.', 'developer-tools' );
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
    add_action('wp_head', array(&$this, 'HeaderInclude'));
  }
  
  public function HeaderInclude()
  { ?>
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  <?php }
}
