<?php
class EnableJqueryImageReflection extends Feature
{
	public function SetSettings()
	{	
		$this->title				= '<a href="http://www.digitalia.be/software/reflectionjs-for-jquery" target="_blank">Image reflections</a>';
		$this->information	= __( 'Allows you to add instantaneous reflection effects to your images in modern browsers, in less than 2 KB. This will automatically load jQuery.', 'developer-tools' ) . ' v1.03';
		$this->fields				= array(
			array( 
				'fieldType' => 'TextArea',
				'label' => __( 'Enter CSS selector(s)', 'developer-tools' ),
				'afterLabel' => '^' . __( 'Separate with commas for multiple', 'developer-tools' ),
				'name' => 'classes',
				'characterSet' => 'cssSelectors'
			)
		);
	}
	
	public function Enabled($value)
	{	
		$this->value = $value;
		if( !IS_WP_ADMIN )
			add_action('init', array(&$this, 'Init'));
		add_action( 'wp_footer', array(&$this, 'FooterInclude'));
	}
	
	public function Init()
	{ 
	 wp_register_script('jquery_reflection', DEVELOPER_TOOLS_URL.'libs/imgReflection/jquery.reflection.js', array('jquery'), '1.03');
	 wp_enqueue_script('jquery_reflection'); 
	}
	
	public function FooterInclude()
	{ ?>
		<script type='text/javascript'>
			if(typeof jQuery == 'function')
				(function($){ 
					$(function(){
						$('<?php print $this->value['classes'] ?>').reflect();
					});
				})(jQuery);
		</script>
	<?php }
}