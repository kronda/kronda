<?php
class PostTypesModel
{
	public static $data;
	
  private function __construct(){}
  
  public static function getInstance() 
  {
      if (!isset(self::$data)) {
          $c = __CLASS__;
          self::$data = new $c;
      }

      return self::$data;
  }

  public function GetData()
  {
  	$checkboxes = 'page|' . __( 'Page', 'developer-tools' )."\n";
  	$checkboxes .= 'post|' . __( 'Post', 'developer-tools' )."\n";
  	$post_types = get_post_types(array( 'public' => true, '_builtin' => false ), 'objects');
  	foreach( $post_types as $postTypeID => $postTypeObject )
  		$checkboxes .= $postTypeID.'|'.$postTypeObject->label."\n";
  	$this->data = $checkboxes;
  }
  
  public function __clone(){}    
}