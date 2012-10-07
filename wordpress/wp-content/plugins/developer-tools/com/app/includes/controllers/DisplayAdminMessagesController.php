<?php
class DisplayAdminMessagesController
{
	private $_updated = false;
	private $_errors = false;
  private $_uniqueMessages = array();
	
	public function __construct( $messages = false, $errors = false )
	{
		
		$this->_GetStrings( $errors, $messages );
		/*
		 * we only want to use admin_notices action when we need to render a message before we are in the body tag of the page 
		 */
		if( has_action( 'rendered_developer_tools_content' ) )
			$this->DisplayAdminMessages();
		else
			add_action( 'admin_notices', array( &$this, 'DisplayAdminMessages' ), 1 );
	}
	
	public function DisplayAdminMessages()
	{
		$updated = $this->_updated;
		$errors = $this->_errors;
		include DEVELOPER_TOOLS_VIEWS_DIR.'admin/ui/page/messages.php';
	}
	
	private function _GetStrings($error = false, $message = false)
	{
		if( $error )
		{
		  if( is_string( $error ) && $this->_UniqueMessage( $error ) ) $this->_errors[] = $error;
		  if( is_array( $error ) )  foreach( $error as $item ) $this->_GetStrings( $item, false );
		}
		if( $message )
		{
      if( is_string( $message ) && $this->_UniqueMessage( $message ) ) $this->_updated[] = $message;
      if( is_array( $message ) )  foreach( $message as $item ) $this->_GetStrings( false, $item );			
		}
	}
  
  private function _UniqueMessage( $message )
  {
    if( !in_array( $message, $this->_uniqueMessages ) ) 
    {
      $this->_uniqueMessages[] = $message;
      return true;
    }
  }
}