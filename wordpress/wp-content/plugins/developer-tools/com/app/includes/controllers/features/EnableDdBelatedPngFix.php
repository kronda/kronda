<?php
class EnableDdBelatedPngFix extends Feature
{	
	public function SetSettings()
	{
		$this->title				= '<a href="http://www.dillerdesign.com/experiment/DD_belatedPNG/" target="_blank" title="Goto site">DD_belatedPNG</a>';
    $this->information  = __( 'This is a JavaScript library that sandwiches PNG image support into IE6 without much fuss.', 'developer-tools' ) . ' v0.0.8a';
		$this->fields				= array(
			array( 
				'fieldType' => 'TextArea',
				'label' => __( 'Enter CSS selector(s)', 'developer-tools' ),
				'name' => 'classes',
				'characterSet' => 'cssSelectors',
        'afterLabel' => '^' . __( 'Separate with commas for multiple', 'developer-tools' ),				
			)
		);
	}
								
	public function Enabled( $value )
	{
		$this->value = $value;
		if( !IS_WP_ADMIN )
		  add_action('init', array(&$this, 'Init'));
		add_action('wp_footer', array(&$this, 'FooterInclude'));
	}
	
	public function Init()
	{
		wp_register_script('DD_belatedPNG', DEVELOPER_TOOLS_URL.'libs/DD_belatedPNG/DD_belatedPNG_0.0.8a-min.js', array(), '0.0.8a');
		wp_enqueue_script('DD_belatedPNG');
	}
	
	public function FooterInclude()
	{ ?>
		<!--[if lt IE 7]>
			<script type="text/javascript">
				DD_belatedPNG.fix("<?php print $this->value['classes'] ?>");
			</script>
		<![endif]-->
	<?php }
}