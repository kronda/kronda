<?php
class CustomLoginImage extends Feature
{	
	public function SetSettings()
	{
		$this->title				= __( 'Custom admin login image', 'developer-tools' );
		$this->uploads				= array(
			'allowedFileTypes' => '*.jpg;*.jpeg;*.gif;*.png',
			'uploadDescription' => __( 'Should be 310px wide by 70px tall.', 'developer-tools' )
		);
		$this->fields				= array(
			array( 
				'fieldType' => 'SingleImageUploader',
				'uploader' => true,
				'label' => __( 'Choose image to upload', 'developer-tools' ),
				'description' => __( 'Should be 310px wide by 70px tall.', 'developer-tools' ),
				'name' => 'image'
			)
		);
	}
								
	public function Enabled($value)
	{
		$this->value = $value;
		add_action('login_head', array(&$this,'LoginHead'));
	}
	
	public function LoginHead()
	{ ?>
		<style style='text/css'>
			h1 a{
				background: url('<?php print DEVELOPER_TOOLS_UPLOADS_URL ?>CustomLoginImage/<?php print $this->value['image'] ?>') no-repeat top left;
			}
		</style>
	<?php }
}