<?php

class CreateDirectory
{
  public $errors;
  
  private $_directory;
  
  public function __construct( $directory )
  {
    $this->_directory = $directory;
    $this->_init();
  }
  
  private function _init()
  {
    $parentDirectory = join( array_slice( split( "/", dirname( $this->_directory ) ), 0, -1), "/" ) . '/';
    if( !@is_writable( $parentDirectory ) ){
      $this->errors[] = DEVELOPER_TOOLS_UPLOADS_DIR.$this->_directory . ' ' . __( 'is not writable', 'developer-tools' );
      return;
    }
    
    if( ini_get('safe_mode') )
    {
      $this->errors[] = __( 'PHP is running in safe mode. If possible, disable this configuration.', 'developer-tools' );
      $this->errors[] = __( 'You must create the directory', 'developer-tools' ) . ' ' . DEVELOPER_TOOLS_UPLOADS_DIR.$this->_directory;
      return;
    }
    else
    {
      if( !is_dir( $this->_directory ) ){
        if( !mkdir( $this->_directory, 0777, true ) ){
          $this->errors[] = __( 'Unable to make directory', 'developer-tools' ) . ' ' . DEVELOPER_TOOLS_UPLOADS_DIR.$this->_directory;
          return;
        }
        if( !chmod( $this->_directory, 0777 ) ){
          $this->errors[] = __( 'Unable to change permissions for directory', 'developer-tools' ) . ' ' . DEVELOPER_TOOLS_UPLOADS_DIR.$this->_directory;
          return;
        }
      }
    }
  }
}