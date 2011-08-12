<?php
class EnableCustomHeaderThemeOption extends Feature
{
	public function SetSettings()
  {
    $this->title              = __( 'Enable "custom header" theme option', 'developer-tools' );
		$this->description   			= __( 'Adds the "Header" theme option under the "Appearance" admin menu tab.', 'developer-tools' );
		$this->uploads				= array(
			'allowedFileTypes' => '*.jpg;*.jpeg;*.gif;*.png'
		);		
    $this->codeSample     = array(
      'code' => '&lt;?php if( <a href="http://codex.wordpress.org/Function_Reference/get_header_image" target="_blank">get_header_image</a>() ) : ?&gt;&lt;img src="&lt;?php <a href="http://codex.wordpress.org/Function_Reference/header_image" target="_blank">header_image</a>(); ?&gt;" height="&lt;?php echo HEADER_IMAGE_HEIGHT; ?&gt;" width="&lt;?php echo HEADER_IMAGE_WIDTH; ?&gt;" alt="" /&gt; &lt;?php endif; ?&gt;',
      'placement' => 'outside'
    );		
		$this->fields       			= array(
			array( 
				'fieldType' => 'TextInput',
				'label' => __( 'Width', 'developer-tools' ),
				'name' => 'width',
        'required' => true,				
				'characterSet' => 'numeric',
        'cssClass' => 'small_int',
        'afterLabel' => __( 'Pixel interger', 'developer-tools' )
			),
			array( 
				'fieldType' => 'TextInput',
				'label' => __( 'Height', 'developer-tools' ),
				'name' => 'height',
        'required' => true,				
				'characterSet' => 'numeric',
        'cssClass' => 'small_int',
        'afterLabel' => __( 'Pixel interger', 'developer-tools' )
			)
    );
	}
	
  public function Enabled($value)
  {
    $this->value = $value;
    add_action( 'after_setup_theme', array( &$this, 'AfterThemeSetup' ) );
  }	
	
	public function AfterThemeSetup()
	{
		add_custom_image_header('', '');
		define('NO_HEADER_TEXT', true );
		define('HEADER_IMAGE_WIDTH', $this->value['width']);
		define('HEADER_IMAGE_HEIGHT', $this->value['height']);
	}	
}
