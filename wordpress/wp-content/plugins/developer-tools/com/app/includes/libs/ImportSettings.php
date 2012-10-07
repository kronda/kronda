<?php
class ImportSettings
{
	public $errors;
	public $messages;
  public function __construct()
  {
		$this->_init();
  }
	
	private function _init()
	{
	  if ( !is_uploaded_file( $_FILES['developer_tools_imported_file']['tmp_name'] ) )
	  {
	    $this->errors[] = __( 'What are you trying to do?  I do not like the way you are trying to upload this file.', 'developer-tools' );
	    return;
	  }
	  ob_start();
	  readfile( $_FILES['developer_tools_imported_file']['tmp_name'] );
	  $importedFile = ob_get_contents();
	  ob_end_clean();
	  $importedFile = maybe_unserialize( $importedFile );

    if( !$importedFile['developer_tools_import_file'] )
    {
      $this->errors[] = __( 'Invalid import file.', 'developer-tools' );
      return;
    }
		else
		{
    	if( $importedFile && update_option( 'developer-tools-values', $importedFile ) )
      	$this->messages[] = __( 'Options imported.', 'developer-tools' );
			else
				$this->errors[] = __( 'Import error.', 'developer-tools' );
		}		
	}
}
  	