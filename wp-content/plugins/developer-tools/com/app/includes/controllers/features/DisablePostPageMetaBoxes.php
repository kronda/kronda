<?php
class DisablePostPageMetaBoxes extends Feature
{	

	public function SetSettings()
	{
		$this->title				= __( 'Disable meta boxes', 'developer-tools' );
    $this->information = __( 'Disable meta boxes on the post and page post-types.', 'developer-tools' );
		$this->featureDataMethod	= true;
		$this->fields				= array(
			array( 
				'fieldType' => 'MultipleCheckboxes',
				'label' => __( 'Blog post meta boxes', 'developer-tools' ),
				'name' => 'post',
				'fieldDataMethod' => 'fieldDataMethod1'
			),
			array( 
				'fieldType' => 'MultipleCheckboxes',
				'label' => __( 'Page meta boxes', 'developer-tools' ),
				'name' => 'page',
				'fieldDataMethod' => 'fieldDataMethod2'
			)			
		);

	}
	
	public function Enabled($value)
	{
		$this->value = $value;
		add_action( 'admin_menu', array(&$this,'AdminMenu'));
	}
	
	public function AdminMenu()
	{
		foreach($this->value as $area => $values)
			foreach($values as $metaBox)
				if($metaBox != '')
					remove_meta_box($metaBox,$area,'core');		
	}	
	
	public function fieldDataMethod1()
	{
		$this->data =
			'postexcerpt' . "|" . __( 'Excerpt', 'developer-tools' ) . "\n" .
			'trackbacksdiv' . "|" . __( 'Trackbacks', 'developer-tools' ) . "\n" .
			'postcustom' . "|" . __( 'Custom Fields', 'developer-tools' ) . "\n" .
			'commentsdiv' . "|" . __( 'Comments', 'developer-tools' ) . "\n" .
			'revisionsdiv' . "|" . __( 'Post Revisions', 'developer-tools' ) . "\n" .
			'commentstatusdiv' . "|" . __( 'Discussion', 'developer-tools' ) . "\n" .
			'slugdiv' . "|" . __( 'Slug', 'developer-tools' ) . "\n" .	
			'authordiv' . "|" . __( 'Post Author', 'developer-tools' ) . "\n" .					
			'categorydiv' . "|" . __( 'Categories', 'developer-tools' ) . "\n" .		
			'tagsdiv-post_tag' . "|" . __( 'Tags', 'developer-tools' ) . "\n"
		;
	}
	
	public function fieldDataMethod2()
	{
		$this->data =	
			'postcustom' . "|" . __( 'Custom Fields', 'developer-tools' ) . "\n" .
			'commentsdiv' . "|" . __( 'Comments', 'developer-tools' ) . "\n" .
			'revisionsdiv' . "|" . __( 'Page Revisions', 'developer-tools' ) . "\n" .
			'commentstatusdiv' . "|" . __( 'Discussion', 'developer-tools' ) . "\n" .
			'slugdiv' . "|" . __( 'Slug', 'developer-tools' ) . "\n" .
			'authordiv' . "|" . __( 'Page Author', 'developer-tools' ) . "\n" .		
			'pageparentdiv' . "|" . __( 'Page Attributes', 'developer-tools' ) . "\n"				
		;
	}	
}