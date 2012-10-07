<?php
class EnableFeaturedImageThumbnails extends Feature
{
  public function SetSettings()
  {
    $this->title          = __( 'Enable featured thumbnail images', 'developer-tools' );
    $this->description    = __( 'If a new post type was created, you must save first before it becomes available below.', 'developer-tools' );
    $this->information    = __( 'For all custom post types that have been created above, you must enable the "Feature Image Thumbnail" meta box for that post type, as well as enable it below.', 'developer-tools' );
    $this->codeSample     = array(
      'code' => array(
       __( 'Simple', 'developer-tools' ) => '&lt;?php if ( <a href="http://codex.wordpress.org/Function_Reference/has_post_thumbnail" target="_blank">has_post_thumbnail</a>() ) <a href="http://codex.wordpress.org/Function_Reference/the_post_thumbnail" target="_blank">the_post_thumbnail</a>(); ?&gt;',
       __( 'Advanced ( Define height, width and css class. Do not add "px" to the end of height or width. )', 'developer-tools' ) => '&lt;?php if ( <a href="http://codex.wordpress.org/Function_Reference/has_post_thumbnail" target="_blank">has_post_thumbnail</a>() ) <a href="http://codex.wordpress.org/Function_Reference/the_post_thumbnail" target="_blank">the_post_thumbnail</a>( array( <span class="none_related">' . __( 'Width', 'developer-tools' ) . '</span>, <span class="none_related">' . __( 'Height', 'developer-tools' ) . '</span> ), array( "class" =&gt; "<span class="none_related">' . __( 'Add CSS class name here', 'developer-tools' ) . '</span>" ) ); ?&gt;',
       __( 'Other featured image thumbnail functions', 'developer-tools' ) => '&lt;?php <a href="http://codex.wordpress.org/Function_Reference/get_post_thumbnail_id" target="_blank">get_post_thumbnail_id</a>(); ?&gt;<br />&lt;?php <a href="http://codex.wordpress.org/Function_Reference/get_the_post_thumbnail" target="_blank">get_the_post_thumbnail</a>(); ?&gt;'
      ),
      'placement' => 'inside',
      'moreCodexLink' => 'http://codex.wordpress.org/Post_Thumbnails'
    );
    $this->fields       = array(
      array( 
        'fieldType' => 'MultipleCheckboxes',
        'label' => __( 'Select post types to enable featured image thumbnails', 'developer-tools' ),
        'name' => 'post_types',
        'fieldDataModel' => 'PostTypesModel'
      ),
      array( 
        'fieldType' => 'TextInput',
        'label' => __( 'Thumbnail width', 'developer-tools' ),
        'name' => 'width',
        'required' => true,
        'characterSet' => 'numeric',
        'cssClass' => 'small_int',
        'afterLabel' => __( 'Pixel interger', 'developer-tools' )
      ),
      array( 
        'fieldType' => 'TextInput',
        'label' => __( 'Thumbnail height', 'developer-tools' ),
        'name' => 'height',
        'required' => true,
        'characterSet' => 'numeric',
        'cssClass' => 'small_int',
        'afterLabel' => __( 'Pixel interger', 'developer-tools' )
      ),
      array( 
        'fieldType' => 'Checkbox',
        'label' => __( 'Crop image to exact dimensions', 'developer-tools' ),
        'name' => 'crop',
        'advanced' => true
      )
    );
  } 
  
  public function Enabled( $value )
  {
    $this->value = $value;
    add_action( 'after_setup_theme', array( &$this, 'AfterSetupTheme' ) );
  }
  
  public function AfterSetupTheme()
  {
    if( $this->value['post_types'] )
      add_theme_support( 'post-thumbnails', $this->value['post_types'] );
    if( $this->value['post_types'] && $this->value['width'] && $this->value['height'] )
      set_post_thumbnail_size( $this->value['width'], $this->value['height'], ( $this->value['crop'] ? true : false ) );    
  }
}