<?php
class EnableJavascriptRequiredMessage extends Feature
{
	public function SetSettings()
	{
		$this->title				= __( '"JavaScript required" message', 'developer-tools' );
    $this->information  = __( 'For users who have JavaScript disabled.', 'developer-tools' ) . ' <a id="preview_default_javascript_required_link">Preview default</a><div id="PreviewJavaScriptRequired" class="no_javascript hidden"><h1>' . __( 'JavaScript must be enabled to use this site', 'developer-tools' ) . '</h1></div>';
		$this->fields				= array(
			array( 
				'fieldType' => 'Checkbox',
				'label' => __( 'Enable', 'developer-tools' ),
				'name' => 'enabled',
			),
      array( 
        'fieldType' => 'TextArea',
        'label' => __( 'Message', 'developer-tools' ),
        'name' => 'message',
        'advanced' => true,
        'afterLabel' => __( '^ Defaults to "JavaScript must be enabled to use this site"', 'developer-tools' )
      ),
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'CSS container ID:', 'developer-tools' ) . ' <strong>#</strong>',
        'name' => 'id',
        'advanced' => true,
        'characterSet' => 'alphaNumericSpaceHyphenUnderscore',
        'afterLabel' => __( 'This will negate all inline styles so you can define the styles in your theme\'s stylesheet', 'developer-tools' )
      )
		);	
	}
	
	public function Enabled($value)
	{
		$this->value = $value;		
		add_action('wp_footer', array(&$this, 'FooterInclude'));
	}
	
	public function FooterInclude()
	{
	  ?>
	  <noscript<?php print ( $this->value['id'] ? ' id="'.$this->value['id'].'"' : '' ); ?><?php if( !$this->value['id'] ) : ?> style="display: block; width: 500px; position: absolute; top: 20px; left: 50%; margin-left: -260px; text-align: center; background-color: red; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; box-shadow: 10px 10px 10px #666; -moz-box-shadow: 10px 10px 10px #666; -webkit-box-shadow: 10px 10px 10px #666; z-index: 9999;"<?php endif; ?>>
	   <h1<?php if( !$this->value['id'] ) : ?> style="margin: 10px; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; background-color: white; color: red !important; font: bold 20px/50px arial, sans-serif;"<?php endif; ?>><?php print ( $this->value['message'] ? $this->value['message'] : __( 'JavaScript must be enabled to use this site', 'developer-tools' ) ); ?></h1>
	  </noscript>
	  <?php
	}
}