<?php
class AddTaxonomy extends Feature
{	
	public function SetSettings()
	{
		$this->title				  = __( 'Add taxonomy', 'developer-tools' );
		$this->multiple				= true;
		$this->codeSample			= array(
		  'code' => array(
			 __( 'Comma separated list', 'developer-tools' ) => '&lt;?php print <a href="http://codex.wordpress.org/Function_Reference/get_the_term_list" target="_blank">get_the_term_list</a>( $post-&gt;ID, \'<span class="replace-1"></span>\', null, \', \', null ); ?&gt;',
			 __ ( 'Object', 'developer-tools' ) => '&lt;?php $termsObject = <a href="http://codex.wordpress.org/Function_Reference/get_the_terms" target="_blank">get_the_terms</a>( $post-&gt;ID, \'<span class="replace-1"></span>\' ); ?&gt;'
			),
			'placement' => 'inside',
      'moreCodexLink' => 'http://codex.wordpress.org/Taxonomies'
		);
		$this->fields				= array(
			array( 
				'fieldType' => 'TextInput', 
        'label' => __( 'Unique identifier', 'developer-tools' ),
				'name' => 'id',
				'required' => true,
				'characterSet' => 'alphaNumericHyphenUnderscore',
				'unmodifiableAfterSave' => true,
				'codeReplaceClass' => 'replace-1' 
			),
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Singular name', 'developer-tools' ),
        'name' => 'name',
        'characterSet' => 'alphaNumericSpace'
      ),
      array( 
        'fieldType' => 'TextInput',
        'advanced' => true,
        'label' => __( 'Plural name', 'developer-tools' ),
        'name' => 'plural_name',
        'characterSet' => 'alphaNumericSpace'
      ),
      array( 
        'fieldType' => 'Checkbox',
        'name' => 'hierarchical',
		    'advanced' => true,
        'label' => __( 'Hierarchical', 'developer-tools' ),
        'afterLabel' => __( 'Checked: will act like categories. Unchecked: will act like tags.', 'developer-tools' )
      ),
      array( 
        'fieldType' => 'MultipleCheckboxes',
        'label' => __( 'Assign to post types', 'developer-tools' ),
        'name' => 'post_types',
        'fieldDataModel' => 'PostTypesModel'
      )      
		);	
	}
	
	public function Enabled($value)
	{
		$this->value = $value;
		add_action( 'init', array(&$this,'CreateTaxonomy'));
	}
	
	public function CreateTaxonomy()
	{
		foreach( $this->value as $taxonomy )
		{
		  if( !$taxonomy['id'] ) continue;
      $singularName = ( $taxonomy['name'] ? $taxonomy['name'] : $taxonomy['id'] );
      $pluralName = ( $taxonomy['plural_name'] ? $taxonomy['plural_name'] : $singularName.'s' ); 
      $postTypes = ( $taxonomy['post_types'] ? $taxonomy['post_types'] : '' );
			register_taxonomy($taxonomy['id'], $postTypes, array(
				'hierarchical' => ( $taxonomy['hierarchical'] ? true : false ),
				'label' => $pluralName,
				'singular_label' => $singularName,
				'labels' => array(
					'name' => _x( $pluralName, 'taxonomy general name' ),
					'singular_name' => _x( $singularName, 'taxonomy singular name' ),
					'search_items' =>  __( 'Search '.$pluralName ),
					'popular_items' => __( 'Popular '.$pluralName ),
					'all_items' => __( 'All '.$pluralName ),
					'parent_item' => __( 'Parent '.$singularName ),
					'parent_item_colon' => __( 'Parent '.$singularName.':' ),
					'edit_item' => __( 'Edit '.$singularName ), 
					'update_item' => __( 'Update '.$singularName ),
					'add_new_item' => __( 'Add New '.$singularName ),
					'new_item_name' => __( 'New '.$singularName.' Name' ),
					'separate_items_with_commas' => __( 'Separate '.$pluralName.' with commas' ),
					'add_or_remove_items' => __( 'Add or remove '.$pluralName ),
					'choose_from_most_used' => __( 'Choose from the most used '.$pluralName )
				), 
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => $taxonomy['id'] ),
			));	
		}
	}
}