<?php
class AddImageSize extends Feature
{
	public function SetSettings()
	{
		$this->title				= __( 'Add image size', 'developer-tools' );
		$this->multiple				= true;
		$this->codeSample			= array(
		  'code' => '&lt;?php if ( <a href="http://codex.wordpress.org/Function_Reference/has_post_thumbnail" target="_blank">has_post_thumbnail</a>() ) <a href="http://codex.wordpress.org/Function_Reference/the_post_thumbnail" target="_blank">the_post_thumbnail</a>( \'<span class="replace-1"></span>\' ); ?&gt;',
			'placement' => 'inside'
		);		
		$this->information			= __( 'Registers a new image size. This means that WordPress will create a copy of the post thumbnail with the specified dimensions when you upload a new thumbnail.', 'developer-tools' );
		$this->fields				= array(
			array( 
				'fieldType' => 'TextInput', 
				'label' => __( 'Unique identifier', 'developer-tools' ),
				'name' => 'id',
				'required' => true,
				'characterSet' => 'alphaNumericHyphenUnderscore',
				'codeReplaceClass' => 'replace-1' 
			),		
			array( 
				'fieldType' => 'TextInput',
				'label' => __( 'Set thumbnail width', 'developer-tools' ),
				'name' => 'width',
        'required' => true,				
				'characterSet' => 'numeric',
        'cssClass' => 'small_int',
        'afterLabel' => __( 'Pixel interger', 'developer-tools' )
			),
			array( 
				'fieldType' => 'TextInput',
				'label' => __( 'Set thumbnail height', 'developer-tools' ),
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
	
	public function Enabled($value)
	{
		if ( function_exists( 'add_image_size' ) )
			foreach( $value as $id => $settings )
				add_image_size( 
					$settings['id'], 
					( $settings['width'] ? $settings['width'] : 0),
					( $settings['height'] ? $settings['height'] : 0), 
					( $settings['crop'] ? true : false ) 
				);
	}
}