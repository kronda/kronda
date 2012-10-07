<?php
class AddPostType extends Feature
{
	public function SetSettings()
	{
		$this->title				  = __( 'Add post type', 'developer-tools' );
		$this->multiple				= true;
		$this->codeSample			= array(
		  'code' => '&lt;?php <a href="http://codex.wordpress.org/Function_Reference/query_posts" target="_blank">query_posts</a>( \'post_type=<span class="replace-1"></span>&post_status=publish\' ); ?&gt;',
			'placement' => 'right_before',
			'moreCodexLink' => 'http://codex.wordpress.org/Post_Types'
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
        'fieldType' => 'TextInput',
        'label' => __( 'Menu position', 'developer-tools' ),
        'name' => 'menu_position',
        'advanced' => true,
        'characterSet' => 'numeric',
        'afterLabel' => __( '5 - below Posts (Default), 10 - below Media, 20 - below Pages, 60 - below first separator, 100 - below second separator', 'developer-tools' ),
        'cssClass' => 'small_int'
      ),
      array( 
        'fieldType' => 'Checkbox',
        'name' => 'hierarchical',
        'advanced' => true,        
        'label' => __( 'Hierarchical', 'developer-tools' ),
        'afterLabel' => __( 'Whether the post type is hierarchical. Allows Parent to be specified.', 'developer-tools' ),
      ),
      array( 
        'fieldType' => 'Checkbox',
        'name' => 'private',
        'advanced' => true,        
        'label' => __( 'Private', 'developer-tools' ),
        'afterLabel' => __( 'Do not display a user-interface for this post type, queries can not be performed from the front end, exclude posts with this post type from search results and hide for selection in navigation menus.', 'developer-tools' ),
      ),      
			array( 
				'fieldType' => 'MultipleCheckboxes',
				'label' => __( 'Meta boxes', 'developer-tools' ),
				'name' => 'supports',
				'fieldDataMethod' => 'SupportsDataMethod'
			),
			array( 
				'fieldType' => 'MultipleCheckboxes',
				'label' => __( 'Taxonomies', 'developer-tools' ),
				'name' => 'taxonomies',
				'fieldDataModel' => 'TaxonomiesModel'
			)									
		);
	}
	
	public function SupportsDataMethod()
	{
		$this->data = 
			'title|' . __( 'Title (Default)', 'developer-tools' ) . "\n" .
			'editor|' . __( 'Body (Default, TinyMCE Editor)', 'developer-tools' ) . "\n" .
			'author|' . __( 'Author', 'developer-tools' ) . "\n" .
			'sticky|' . __( 'Sticky', 'developer-tools' ) . "\n" .
			'excerpt|' . __( 'Excerpt', 'developer-tools' ) . "\n" .
			'trackbacks|' . __( 'Trackbacks', 'developer-tools' ) . "\n" .
			'custom-fields|' . __( 'Custom Fields', 'developer-tools' ) . "\n" .
			'comments|' . __( 'Comments', 'developer-tools' ) . "\n" .
			'revisions|' . __( 'Revision', 'developer-tools' ) . "\n" .
			'page-attributes|' . __( 'Page Attributes (post-type order)', 'developer-tools' ) . "\n" .
			'thumbnail|' . __( 'Featured Image Thumbnail (Must also be enabled below)', 'developer-tools' ) . "\n"
		;
	}
	
	public function Enabled($value)
	{
		$this->value = $value;
		add_action('init', array(&$this,'CreatePostTypesInit'));
	}
	
	public function CreatePostTypesInit()
	{
		foreach($this->value as $key => $postType)
		{
      if( !$postType['id'] ) continue;
			$supports = ( count($postType['supports']) > 0  ? $postType['supports'] : '' );
			$taxonomies = ( $postType['taxonomies'] ? $postType['taxonomies'] : array());
			$singularName = ( $postType['name'] ? $postType['name'] : $postType['id'] );
			$pluralName = ( $postType['plural_name'] ? $postType['plural_name'] : $singularName.'s' ); 
			register_post_type( $postType['id'],
				array(
					'label' => $pluralName,
					'labels' => array(
					    'name' => _x($pluralName, 'post type general name'),
					    'singular_name' => _x($singularName, 'post type singular name'),
					    'add_new' => _x('Add New', $singularName),
					    'add_new_item' => __('Add New '.$singularName),
					    'edit_item' => __('Edit '.$singularName),
					    'new_item' => __('New '.$singularName),
					    'view_item' => __('View '.$singularName),
					    'search_items' => __('Search '.$singularName),
					    'not_found' =>  __('No '.$pluralName.' found'),
					    'not_found_in_trash' => __('No '.$pluralName.' found in Trash'), 
					    'parent_item_colon' => ''
					),
          'public' => ( $postType['private'] ? false : true ),
					'hierarchical' => ( $postType['hierarchical'] ? true : false ),
					'supports' => $supports,
					'taxonomies' => $taxonomies,
					'menu_position' => ( $postType['menu_position'] ? $postType['menu_position'] : 5 ),
					'capability_type' => 'page'
				)
		    );
		}		
	}
}