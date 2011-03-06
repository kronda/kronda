<?php
class AddSidebar extends Feature
{	
	public function SetSettings()
	{	
		$this->title				= __( 'Add sidebar', 'developer-tools' );
		$this->multiple				= true;
		$this->codeSample			= array(
		  'code' => '&lt;?php <a href="http://codex.wordpress.org/Function_Reference/dynamic_sidebar" target="_blank">dynamic_sidebar</a>( \'<span class="replace-1"></span>\' ); ?&gt;',
			'placement' => 'outside'
		);
		$this->fields				= array(
			array( 
				'fieldType' => 'TextInput', 
        'label' => __( 'Unique name', 'developer-tools' ),
				'name' => 'name',
				'required' => true,
				'characterSet' => 'alphaNumericSpace',
				'codeReplaceClass' => 'replace-1' 
			)
		);
	}
								
	public function Enabled($value)
	{
		if( function_exists( 'register_sidebar_widget' ) )
			foreach( $value as $sidebar )
      {
        if( !$sidebar['name'] ) continue;
		  	register_sidebar( array( 'name'=> $sidebar['name'] ) );
      }		
	}
}