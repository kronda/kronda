<?php
class SetUploadsDirectory
{
  public $errors = false;
  
  public function __construct()
  {
    $this->_init();
  }
  
  private function _init()
  {
    
    $uploadsPath = get_option( 'developer-tools-uploads' );    
    if( !$uploadsPath['dir'] && !$uploadsPath['url'] )
    {
      $this->_setUploadsDir();
    }
    else
    {
      define( 'DEVELOPER_TOOLS_UPLOADS_DIR', $uploadsPath['dir'] );
      define( 'DEVELOPER_TOOLS_UPLOADS_URL', $uploadsPath['url'] );  
    }
  }

  private function _setUploadsDir()
  {
    if( !is_writable( WP_CONTENT_DIR ) )
    {
      $this->errors = '<strong>' . __( 'Developer Tools plugin error:', 'developer-tools' ) . ' </strong> wp-content' . __(' is not writable.', 'developer-tools' );
      return;
    }
    
    $wpUploadDir = wp_upload_dir();
    if( $wpUploadDir['error'] )
    {
      $this->errors[] = '<strong>' . __( 'Developer Tools plugin error:', 'developer-tools' ) . ' </strong>' . $wpUploadDir['error'];
      $this->errors[] = '<strong>' . __( 'Developer Tools plugin error:', 'developer-tools' ) . ' </strong> ' . __('The uploads directory is not writable.', 'developer-tools' );
      return;      
    }
    
    if( !is_writable( $wpUploadDir['basedir'] ) )
    {
      $this->errors[] =  '<strong>' . __( 'Developer Tools plugin error:', 'developer-tools' ) . ' </strong>' . $wpUploadDir['basedir'] . __(' is not writable.', 'developer-tools' );
      return;      
    }

    $uploadDirString = $wpUploadDir['basedir'].'/developer_tools/';
    $uploadUrlString = $wpUploadDir['baseurl'].'/developer_tools/';
    
    if( !is_dir( $uploadDirString ) )
    { 
      $createUploadsDirectory = new CreateDirectory( $uploadDirString );
      if( $createUploadsDirectory->errors )
      {
        $this->errors[] = $createUploadsDirectory->errors;
        return;        
      }
    }

    update_option( 'developer-tools-uploads', array( 'url' => $uploadUrlString, 'dir' => $uploadDirString ) );
    define( 'DEVELOPER_TOOLS_UPLOADS_DIR', $uploadDirString );
    define( 'DEVELOPER_TOOLS_UPLOADS_URL', $uploadUrlString );
  }
}