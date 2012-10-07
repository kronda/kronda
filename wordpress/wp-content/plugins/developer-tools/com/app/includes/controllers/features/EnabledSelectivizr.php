<?php
class EnabledSelectivizr extends Feature
{
  public function SetSettings()
  {
    $this->title        = '<a href="http://selectivizr.com/" target="_blank" title="Goto site">:select[ivizr]</a>';
    $this->information  = __( 'selectivizr is a JavaScript utility that emulates CSS3 pseudo-classes and attribute selectors in Internet Explorer 6-8. Simply include the script in your pages and selectivizr will do the rest. v1.02', 'developer-tools' );
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
    <!--[if (gte IE 6)&(lte IE 8)]>
      <script src="<?php print DEVELOPER_TOOLS_URL ?>libs/selectivizr/selectivizr-min.js"></script>
    <![endif]-->
  <?php }
}