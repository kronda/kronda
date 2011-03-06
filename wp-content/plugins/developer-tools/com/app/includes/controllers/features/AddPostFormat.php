<?php
class AddPostFormat extends Feature
{ 
  public function SetSettings()
  {
    $this->minWpVersion     = "3.1";    
    $this->title          = __( 'Add post formats', 'developer-tools' );
    $this->information = __( 'A Post Format is a piece of meta information that can be used by a theme to customize its presentation of a post. The Post Formats feature provides a standardized list of formats that are available to all themes that support the feature. Themes are not required to support every format on the list. New formats cannot be introduced by themes nor even plugins. The standardization of this list provides both compatibility between numerous themes and an avenue for external blogging tools to access to this feature in a consistent fashion.', 'developer-tools' );
    $this->codeSample     = array(
      'code' => '&lt;?php if ( <a href="http://codex.wordpress.org/Function_Reference/has_post_format" target="_blank">has_post_format</a>( \'video\' ) ) print \'has video post format\'; ?&gt;',
      'placement' => 'inside',
      'moreCodexLink' => 'http://codex.wordpress.org/Post_Formats'
    );
    $this->fields       = array(
      array( 
        'fieldType' => 'MultipleCheckboxes',
        'name' => 'post_formats',
        'fieldDataMethod' => 'fieldDataMethod1'
      )      
    );  
  }
  
  public function Enabled($value)
  {
    add_theme_support( 'post-formats', $value['post_formats'] );
  }
  
  public function fieldDataMethod1()
  {
    $this->data =
      'aside' . "|" . __( 'Aside', 'developer-tools' ) . "\n" .
      'gallery' . "|" . __( 'Gallery', 'developer-tools' ) . "\n" .
      'link' . "|" . __( 'Link', 'developer-tools' ) . "\n" .
      'image' . "|" . __( 'Image', 'developer-tools' ) . "\n" .
      'quote' . "|" . __( 'Quote', 'developer-tools' ) . "\n" .
      'status' . "|" . __( 'Status', 'developer-tools' ) . "\n" .
      'video' . "|" . __( 'Video', 'developer-tools' ) . "\n" .  
      'audio' . "|" . __( 'Audio', 'developer-tools' ) . "\n" .         
      'chat' . "|" . __( 'Chat', 'developer-tools' ) . "\n"
    ;    
  }
}