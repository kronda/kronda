<?php 
class DtMaxUploadSize extends Feature
{
	public function SetSettings()
	{
		$this->pluginSetting 		= true;
		$this->title				= __( 'Maximum file upload size', 'developer-tools' );
		$this->information			= __( 'This is the maximum upload file size for the Developer Tools features. Default: 5MB', 'developer-tools' );
		$this->fields				= array(
			array( 
				'fieldType' => 'TextInput',
				'name' => 'mb',
				'cssClass' => 'small_int',
				'afterLabel' => __( 'integer in MegaBytes', 'developer-tools' ),
				'characterSet' => 'numeric'
			)
		);
	}
	
	public function Enabled($value)
	{
		$value = (int)$value * 1024;
		define( "DEVELOPER_TOOLS_MAX_UPLOAD_SIZE", "$value" );
	}				
}