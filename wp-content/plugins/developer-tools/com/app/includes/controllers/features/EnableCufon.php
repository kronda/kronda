<?php
class EnableCufon extends Feature
{
	public function SetSettings()
	{
		$this->title				  = '<a href="http://cufon.shoqolate.com/generate/" target="_blank">Cuf&oacute;n</a>';
		$this->multiple				= true;
    $this->information    = 'Cuf&oacute;n ' . __( 'allows you to use custom fonts on the web. This will automatically load jQuery.', 'developer-tools' ) . ' <a href="https://github.com/sorccu/cufon/wiki/API" target="_blank">' . __( 'API details', 'developer-tools' ) . '</a>. v1.09i';
		$this->uploads				= array(
			'allowedFileTypes' => '*.js',
			'uploadDescription' => '<p>' . __( 'Use the', 'developer-tools' ) . ' <a href="http://cufon.shoqolate.com/generate/" target="_blank">' . sprintf( __( 'font generator at the %s website', 'developer-tools' ), 'cuf&oacute;n' ) . '</a>.</p>'
		);
		$this->fields				= array(
			array( 
				'fieldType' => 'SelectListUploader',
				'label' => __( 'Choose File', 'developer-tools' ),
				'name' => 'file',
				'required' => true
			),
      array( 
        'fieldType' => 'TextArea',
        'label' => __( 'Enter CSS selector(s)', 'developer-tools' ),
        'name' => 'selectors',
        'required' => true,
        'characterSet' => 'cssSelectors',
        'afterLabel' => '^' . __( 'Separate with commas for multiple', 'developer-tools' )
      ),			
      array( 
        'fieldType' => 'TextArea',
        'label' => __( 'Hover ( As object parameters )', 'developer-tools' ),
        'name' => 'hover',
        'advanced' => true,
        'afterLabel' => __( 'Do NOT include the { }\'s.', 'developer-tools' ) . ' <a href="https://github.com/sorccu/cufon/wiki/Styling" target="_blank">'. sprintf( __( '%s styling', 'developer-tools' ), 'Cuf&oacute;n') . '</a>'
      )			
		);
	}
	
	public function Enabled($value)
	{
		$this->value = $value;
		if( !IS_WP_ADMIN )
		  add_action('init', array(&$this, 'Init'));	
		add_action('wp_head', array(&$this,'HeadInclude'));	
		add_action('wp_footer', array(&$this,'FooterInclude'));
	}
  
	public function Init()
	{
    wp_register_script('cufon', DEVELOPER_TOOLS_URL.'libs/cufon/cufon-yui.js', array('jquery'), '1.09i');
		wp_enqueue_script('cufon');	
	}
	
	public function HeadInclude()
	{
    
	  foreach($this->value as $key => $value) :
      if( !$value['file'] || !$value['selectors'] ) continue;
			$file = DEVELOPER_TOOLS_UPLOADS_URL.'EnableCufon/'.$value['file'];
			$fileContents = file_get_contents( $file );
			// NEED TO USE A BETTER REGEX HERE
			// $fileExplode = explode('"font-family":"', $fileContents);
			// $fontFamily = explode('","', $fileExplode[1]);
			$fontFamily = $this->_GetFontFamily( '"font-family":', $fileContents );
		?>
			<script type='text/javascript' src='<?php print $file ?>'></script>
			<script type='text/javascript'>
				if( typeof Cufon == 'function' )
				  Cufon.replace("<?php print $value['selectors'] ?>", { fontFamily: "<?php print $fontFamily ?>"<?php if ( $value['hover'] ) print ', hover : { ' . str_replace( array( "\r\n","{", "}", " " ), null, stripslashes( $value['hover'] ) ) . ' } '; ?> });
			</script>
		<?php 
		endforeach;
	}
	
	public function FooterInclude()
	{ 
    ?>
		  <!--[if lte IE 8]><script type='text/javascript'>if( typeof Cufon == 'function' ) Cufon.now();</script><![endif]-->
	  <?php 
  }
  
  private function _GetFontFamily( $attrib, $fileContents ){
      $re = '/' . preg_quote( $attrib ) . '([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';
      if ( preg_match( $re, $fileContents, $match ) )
         return urldecode($match[2]);
      return false;
   }
}