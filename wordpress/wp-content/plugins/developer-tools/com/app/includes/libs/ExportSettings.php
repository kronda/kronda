<?php
class ExportSettings
{
	private $_exportData;
  public function __construct( $data )
  {
		$this->_exportData = $data;
		$this->_init();
  }
	
	private function _init()
	{
  	add_action('admin_head', array(&$this, 'AdminHeadBuffer') );   
    add_action('admin_footer', array(&$this, 'AdminFooterBuffer') ); 
    $filename = "developer_tools_export_".date('Y.m.d-g.ia');
    $exportData['developer_tools_import_file'] = true;
		$exportData['version'] = DEVELOPER_TOOLS_VERSION;
    $exportData['dt'] = $this->_exportData['dt'];
    $exportData['show'] = $this->_exportData['show'];
    header("Accept-Ranges: none");
    header("Content-Disposition: attachment; filename=$filename");
    header('Content-Type: application/octet-stream');
    echo maybe_serialize( $exportData );
    exit();		
	}
	
  public function AddEnctype( $buffer )
  {
    return $buffer;
  }
  
  public function AdminHeadBuffer()
  {   
    ob_start( array( &$this, 'AddEnctype' ) );   
  } 
  
  public function AdminFooterBuffer()
  {   
    ob_end_flush();   
  } 	
	
}