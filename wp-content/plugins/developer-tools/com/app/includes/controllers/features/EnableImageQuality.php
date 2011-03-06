<?php
class EnableImageQuality extends Feature
{
	public function SetSettings()
	{
		$this->title				= __( 'Image upload quality', 'developer-tools' );
		$this->information	= __( 'This setting is for resized images. Default is 75 percent.', 'developer-tools' );
		$this->fields				= array(
			array( 
				'fieldType' => 'TextInput',
				'label' => __( 'Percentage', 'developer-tools' ),
				'name' => 'quality',
				'characterSet' => 'numeric',
				'afterLabel' => __( 'Example: 100 for 100 percent', 'developer-tools' ),
				'cssClass' => 'small_int'
			)								
		);
	}	
	
	public function Enabled($value){
		$this->value = $value['quality'];
		if( (int)$value['quality'] > 0 && (int)$value['quality'] <= 100 )
			add_filter( 'jpeg_quality', array(&$this, 'jpeg_full_quality' ));
	}
	
	public function jpeg_full_quality( $quality ) { return $this->value; }
}