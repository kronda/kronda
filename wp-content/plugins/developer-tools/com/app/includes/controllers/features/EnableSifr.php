<?php
class EnableSifr extends Feature
{	
	public function SetSettings()
	{
		$this->title				  = '<a href="http://wiki.novemberborn.net/sifr3/" target="_blank">sIFR</a>';
    $this->information    = 'sIFR ' . __('is an open source JavaScript and Adobe Flash dynamic web fonts implementation, enabling the replacement of text elements on HTML web pages with Flash equivalents. If possible, use Cuf&oacute;n over sIFR, as it is seemingly no longer supported.', 'developer-tools' ) . ' <a href="http://wiki.novemberborn.net/sifr3/JavaScript+Configuration" target="_blank">' . __( 'API details', 'developer-tools' ) . '</a>. v3';
		$this->multiple				= true;
		$this->uploads				= array(
			'allowedFileTypes' => '*.swf',
			'uploadDescription' => '<p>Download and unzip <a href="'.DEVELOPER_TOOLS_URL.'libs/sifr3/flash/sifr.zip">sifr.zip</a>. Open sifr.fla with the Flash editor and double click in the "white area", you should see the following text: "Bold  Italic Normal". In order to minimize the required file-size, remove all text. Then, if you want to use a bold font, toggle bold on and type one character. The same goes for italic and normal text. Add all types you need (except for underline), or else the text won\'t render correctly. If you wish to display special characters such as accents, you need to embed these. Click the embed button and select the extra characters you want to use, or type them in the box.</p><p>Next, export the movie. Click on the File menu, then Export, Export Movie. Give the name and location for the new Flash file and click Save <strong>(The file-name should only container letters, hyphens and underscores)</strong>. A new menu opens. Here you need to do the following:</p><ul><li>Flash version is Flash 8.</li><li>Set Load order to Bottom up.</li><li>The ActionScript version is 2.0.</li><li>Protect the movie from import and compress it.</li><li>Omit trace actions and don\'t permit debugging. If you want to, you can generate a size report.</li></ul><p>This report will show you which font types have been exported. For instance, if you only wanted to use a bold font, and you see it has exported a normal font, you\'ll have to repeat the procedure to make sure the normal font isn\'t unnecessarily exported.</p><p>Finally, export the movie and upload.</p>'
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
        'label' => __( 'CSS styles', 'developer-tools' ),
        'name' => 'css',
        'advanced' => true,
        'afterLabel' => '^The <a href="http://wiki.novemberborn.net/sifr3/Styling" target="_blank">styling documentation</a> for this is poor, I think it\'s easier to view the source of the <a href="http://dev.novemberborn.net/sifr3/beta2/demo/" target="_blank">offical sIFR3 demo page</a>. This field sets the CSS property.'
      ),
      array( 
        'fieldType' => 'Checkbox',
        'name' => 'selectable',
        'label' => __( 'Selectable', 'developer-tools' ),
        'advanced' => true        
      )      
		);		
	}
	
	public function Enabled($value)
	{
		$this->value = $value;
    if( !IS_WP_ADMIN )
      add_action('init', array(&$this, 'Init'));
    add_action('wp_print_styles ', array(&$this, 'PrintStyles'));  // this doesnt work for some reason
    add_action('wp_head', array(&$this, 'HeadInclude'));		 
	}	
	
	public function Init()
	{
    wp_register_script('sifr-js', DEVELOPER_TOOLS_URL.'libs/sifr3/js/sifr.js', array('jquery'), '3-beta');
		wp_enqueue_script('sifr-js');	 	
	}
	
	public function PrintStyles()
	{
		wp_register_style( 'sifr-css', DEVELOPER_TOOLS_URL.'libs/sifr3/css/sifr.css' );
    wp_enqueue_style('sifr-css');		
	}
	
	public function HeadInclude()
	{ ?>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php print DEVELOPER_TOOLS_URL ?>libs/sifr3/css/sifr.css" />

		<script type='text/javascript'>
		  if (typeof sIFR == "object")
			{
				<?php foreach( $this->value as $key => $value ) : if( !$value['file'] || !$value['selectors'] ) continue; $fontName = str_replace( '.swf', null, $value['file'] ); ?>
					var <?php print $fontName ?> = { src: '<?php print DEVELOPER_TOOLS_UPLOADS_URL ?>EnableSifr/<?php print $value['file'] ?>' };
				<?php endforeach; ?>
				sIFR.activate(<?php
				$totalsIFRs = count($this->value);
				$loopCounter = 0;
				foreach($this->value as $key => $value) :
          if( !$value['file'] || !$value['selectors'] ) continue;
					$loopCounter++;
					$fontName = str_replace( '.swf', null, $value['file'] );
					print $fontName;
					if($loopCounter != $totalsIFRs) : ?>,<?php endif; 
				endforeach; 
				?>);
				
				<?php foreach( $this->value as $key => $value ) : if( !$value['file'] || !$value['selectors'] ) continue; str_replace( '.swf', null, $value['file'] ); ?>
					sIFR.replace(<?php print $fontName ?>, { transparent : true, selector: "<?php print $value['selectors'] ?>"<?php if( $value['css'] ) print ', css: ' . str_replace( array( " ","\r\n", "css:" ), null, stripslashes( $value['css'] ) ); ?>, selectable: <?php print ( $value['selectable'] ? 'true' : 'false'); ?> });
				<?php endforeach; ?>
		  }
		</script>
	<?php }
}