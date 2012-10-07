<?php 
class GetFilesInDirectory
{
	private $_directory;

	public $files;
	
	public function __construct($fullPath)
	{
		$this->_directory = $fullPath;
		$this->_init();
	}
	
	private function _init()
	{
		if( file_exists( DEVELOPER_TOOLS_UPLOADS_DIR.$this->_directory ) )
		{
			$d = dir(DEVELOPER_TOOLS_UPLOADS_DIR.$this->_directory);
			while( false !== ($entry = $d->read()) )
				if($entry != '.' && $entry != '..' && !preg_match("/^\./", $entry ) )
					$this->files[] = $entry;
			$d->close();
		}
	}
}