<?php
class EnableSwfObject extends Feature
{
  public function SetSettings()
  { 
    $this->title          = '<a href="http://code.google.com/p/swfobject/" target="_blank">SWFObject</a>';
    $this->multiple       = true;
    $this->information    = __( 'SWFObject is an open-source JavaScript library used to embed Adobe Flash content into web pages.', 'developer-tools' ) . ' <a href="http://code.google.com/p/swfobject/wiki/documentation" target="_blank">' . __( 'API details', 'developer-tools' ) . '</a>. v2.2';
    $this->uploads        = array(
      'allowedFileTypes' => '*.swf',
      'uploadDescription' => __( 'Upload the swf.', 'developer-tools' )
    );
    $this->fields       = array(
      array( 
        'fieldType' => 'SelectListUploader',
        'label' => __( 'Choose File', 'developer-tools' ),
        'name' => 'file',
        'required' => true
      ),
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'CSS container ID:', 'developer-tools' ) . ' <strong>#</strong>',
        'name' => 'id',
        'required' => true,
        'characterSet' => 'alphaNumericSpaceHyphenUnderscore',
        'afterLabel' => __( 'The DOM element to be replaced', 'developer-tools' )
      ),
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Swf width', 'developer-tools' ),
        'name' => 'width',
        'required' => true,
        'characterSet' => 'numeric',
        'cssClass' => 'small_int',
        'afterLabel' => __( 'Pixel interger', 'developer-tools' )
      ),      
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Swf height', 'developer-tools' ),
        'name' => 'height',
        'required' => true,
        'characterSet' => 'numeric',
        'cssClass' => 'small_int',
        'afterLabel' => __( 'Pixel interger', 'developer-tools' )
      ),
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Minimum flash version', 'developer-tools' ),
        'name' => 'min',
        'required' => true,
        'characterSet' => 'numericDecimal',
        'cssClass' => 'small_int',
        'afterLabel' => __( 'Example: 9.0.0', 'developer-tools' )
      ),                        
      array( 
        'fieldType' => 'TextArea',
        'label' => __( 'Alternate content', 'developer-tools' ),
        'name' => 'alternate',
        'advanced' => true,
        'afterLabel' => '^' . __( 'This text appears if the swf is unable to load.', 'developer-tools' )
      ),
      array( 
        'fieldType' => 'TextArea',
        'label' => __( 'Flashvars', 'developer-tools' ),
        'name' => 'flashvars',
        'advanced' => true,
        'afterLabel' => '^' . __('As comma-delimited object properties. Do NOT include the { }\'s.', 'developer-tools' )
      ),
      array( 
        'fieldType' => 'TextArea',
        'label' => __( 'Parameters', 'developer-tools' ),
        'name' => 'params',
        'advanced' => true,
        'afterLabel' => '^' . __( 'As comma-delimited object properties. Do NOT include the { }\'s.<br />For a transparent background use:', 'developer-tools' ) .' <em>wmode : \'transparent\'</em>'
      ),
      array( 
        'fieldType' => 'TextArea',
        'label' => __( 'Attributes', 'developer-tools' ),
        'name' => 'attributes',
        'advanced' => true,
        'afterLabel' => '^' . __( 'As comma-delimited object properties. Do NOT include the { }\'s.', 'developer-tools' )
      )                      
    );
  }
  
  public function Enabled( $value )
  {
    $this->value = $value;
		if( !IS_WP_ADMIN )
		  add_action('init', array(&$this, 'Init'));  
    add_action('wp_head', array(&$this, 'HeadInclude')); 
  }
  
  public function Init()
  {
    wp_enqueue_script( 'swfobject' );
    wp_enqueue_script( 'jquery' );
  }
  
  public function HeadInclude()
  {
    ?>
    <script type="text/javascript">
      if( typeof swfobject == 'object' )
      {
      <?php 
        $alternateContent = false;
        $loopCounter = 0;
        foreach( $this->value as $swfObject ) : if( !$swfObject['file'] || !$swfObject['id'] || !$swfObject['width'] || !$swfObject['height'] || !$swfObject['min'] ) continue;
          $loopCounter++;
          if( $swfObject['alternate'] ) $alternateContent[$swfObject['id']] = $swfObject['alternate'];
      ?>
        var flashvars<?php print $loopCounter ?> = {<?php if( $swfObject['flashvars'] ) print str_replace( array( "\r\n", " ","{", "}" ), null, stripslashes( $swfObject['flashvars'] ) ) ?>};
        var params<?php print $loopCounter ?> = {<?php if( $swfObject['params'] ) print str_replace( array( "\r\n", " ","{", "}" ), null, stripslashes( $swfObject['params'] ) ) ?>};
        var attributes<?php print $loopCounter ?> = {<?php if( $swfObject['attributes'] ) print str_replace( array( "\r\n", " ","{", "}" ), null, stripslashes( $swfObject['attributes'] ) ) ?>};
        swfobject.embedSWF("<?php print DEVELOPER_TOOLS_UPLOADS_URL.'EnableSwfObject/'.$swfObject['file'] ?>", "<?php print $swfObject['id'] ?>", "<?php print $swfObject['width'] ?>", "<?php print $swfObject['height'] ?>", "<?php print $swfObject['min'] ?>", true, flashvars<?php print $loopCounter ?>, params<?php print $loopCounter ?>, attributes<?php print $loopCounter ?>);
      <?php
        endforeach;
      ?>
      }
      <?php 
        if( $alternateContent ) : 
      ?>
      if( typeof jQuery == 'function' )
        jQuery(function($){
          <?php foreach( $alternateContent as $id => $content ) : ?>
            $('#<?php print $id ?>:not(object, embed)').html('<?php print $content ?>');
          <?php endforeach; ?>
        });
      <?php 
        endif;
      ?>
    </script>
    <?php
  }
}
