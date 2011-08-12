<?php
class LoadWpCoreJavascriptLibraries extends Feature
{               
  public function SetSettings()
  {
    $this->title              = __( 'Load WordPress core JavaScript libraries', 'developer-tools' );
    $this->description        = __( 'These are only loaded on the front-end for whichever theme is enabled.', 'developer-tools' );
    $this->information        =  __( 'Full details about all these scripts can be found' , 'developer-tools' ) . ' <a href="http://codex.wordpress.org/Function_Reference/wp_enqueue_script" target="_blank">' . __('here' , 'developer-tools' ) . '</a>.';
    $this->featureDataMethod  = true;
    $this->fields       = array(
      array( 
        'fieldType' => 'MultipleCheckboxes',
        'label' => __( 'Commonly used', 'developer-tools' ),
        'name' => 'normal',
        'fieldDataMethod' => 'fieldDataMethod1'
      ),
      array( 
        'fieldType' => 'MultipleCheckboxes',
        'label' => __( 'Less commonly used', 'developer-tools' ),
        'name' => 'advanced',
        'advanced' => true,       
        'fieldDataMethod' => 'fieldDataMethod2'
      )     
    );

  }
  
  public function Enabled($value)
  {
    $this->value = $value;
    if( !IS_WP_ADMIN )
      add_action('wp_print_scripts',array(&$this, 'PrintScripts')); 
  }
  
  public function PrintScripts()
  {
    if( $this->value['normal'] )
      foreach( $this->value['normal'] as $library )
        if( $library != '' ) wp_enqueue_script( $library );
        
    if( $this->value['advanced'] )
      foreach( $this->value['advanced'] as $library )
        if( $library != '' ) wp_enqueue_script( $library );       
  }
  
  public function fieldDataMethod1()
  {
    $this->data = 
      'jquery'."|".'jQuery'."\n".
      'jquery-ui-core'."|".'jQuery UI Core'."\n".
      'jquery-ui-sortable'."|".'jQuery UI Sortable'."\n".
      'jquery-ui-draggable'."|".'jQuery UI Draggable'."\n".
      'jquery-ui-droppable'."|".'jQuery UI Droppable'."\n".
      'jquery-ui-selectable'."|".'jQuery UI Selectable'."\n".
      'jquery-ui-resizable'."|".'jQuery UI Resizable'."\n".
      'jquery-ui-dialog'."|".'jQuery UI Dialog'."\n".
      'thickbox'."|".'ThickBox'."\n".
      'swfobject'."|".'SWFObject'."\n"      
    ;
  }
  
  public function fieldDataMethod2()
  {
    $this->data =
      'interface'."|".'jQuery Interface'."\n".
      'schedule'."|".'jQuery Schedule'."\n".
      'suggest'."|".'jQuery Suggest'."\n".
      'jquery-hotkeys'."|".'jQuery Hotkeys'."\n". 
      'jquery-ui-tabs'."|".'jQuery UI Tabs'."\n".
      'jquery-form'."|".'jQuery Form'."\n".
      'jquery-color'."|".'jQuery Color'."\n".             
      'scriptaculous-root'."|".'Scriptaculous Root'."\n".
      'scriptaculous-builder'."|".'Scriptaculous Builder'."\n".
      'scriptaculous-dragdrop'."|".'Scriptaculous Drag & Drop'."\n".
      'scriptaculous-effects'."|".'Scriptaculous Effects'."\n".
      'scriptaculous-slider'."|".'Scriptaculous Slider'."\n".
      'scriptaculous-sound'."|".'Scriptaculous Sound'."\n".
      'scriptaculous-controls'."|".'Scriptaculous Controls'."\n".
      'scriptaculous'."|".'Scriptaculous'."\n".
      'cropper'."|".'Image Cropper'."\n".
      'swfupload'."|".'SWFUpload'."\n".
      'swfupload-degrade'."|".'SWFUpload Degarade'."\n".
      'swfupload-queue'."|".'SWFUpload Queue'."\n".
      'swfupload-handlers'."|".'SWFUpload Handlers'."\n".
      'sack'."|".'Simple AJAX Code-Kit'."\n".
      'quicktags'."|".'QuickTags'."\n".
      'farbtastic'."|".'Farbtastic (color picker)'."\n".
      'tiny_mce'."|".'Tiny MCE'."\n".
      'prototype'."|".'Prototype Framework'."\n"
    ;
  }
}