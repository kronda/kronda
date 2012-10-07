<?php
class AddMetaBoxes extends Feature
{
  public function SetSettings()
  {
    $this->title        = __( 'Add meta box', 'developer-tools' );
    $this->multiple       = true;
    $this->codeSample     = array(
      'code' => '&lt;?php \'<span class="replace-1"></span>\' ); ?&gt;',
      'placement' => 'inside'
    );    
    $this->information      = __( 'Registers a new, custom meta box (custom field).', 'developer-tools' );
    $this->fields       = array(
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Unique identifier', 'developer-tools' ),
        'name' => 'id',
        'required' => true,
        'characterSet' => 'alphaNumericHyphenUnderscore',
        'codeReplaceClass' => 'replace-1' 
      ),
      // going to use select list for field type, seen to be sure that field is working
    );
  } 
  
  public function Enabled($value)
  {
    
  }
}

/*
class AddMetaBoxes extends Feature
{	
	public function Enabled($value)
	{
		$this->value = $value;
		add_action('admin_menu', array(&$this, 'AdminMenu'));
		add_action('save_post', array(&$this, 'SavePost'));
	}
	
	public function AdminMenu()
	{
		foreach($this->value as $singleMetaBoxTemplate)
			if($singleMetaBoxTemplate['post_types'] && $singleMetaBoxTemplate['field_type'] && $singleMetaBoxTemplate['context'] && $singleMetaBoxTemplate['priority'])
				foreach($singleMetaBoxTemplate['post_types'] as $metaBoxpostType)
					add_meta_box( str_replace(" ", "_", $singleMetaBoxTemplate['name']).'-'.$metaBoxpostType, $singleMetaBoxTemplate['name'], array(&$this, 'RenderMetaBoxes'), $metaBoxpostType, $singleMetaBoxTemplate['context'], $singleMetaBoxTemplate['priority'], array('field_type' => $singleMetaBoxTemplate['field_type'], 'description' =>  $singleMetaBoxTemplate['description']) );		
	}
	
	public function RenderMetaBoxes($post,$args)
	{
		$this->MetaBoxField($post->ID, $args['args']['field_type'], str_replace(" ", "_", $args['title']) );
		$content = '<p>'.$args['args']['description'].'</p>';
		print $content;
	}
	
	public function MetaBoxField($post_ID, $field_type, $name)
	{
		$content = '<input type="hidden" name="'.$name.'_noncename" id="'.$name.'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
		$meta_box_value = get_post_meta($post_ID, $name, true);
		switch($field_type){
			case 'text_input':
				$content .= '<input name="'.$name.'" type="text" size="55" style="max-width:99%" value="'.$meta_box_value.'" />';
			break;
			case 'textarea':
				$content .= '<textarea name="'.$name.'" cols="45" rows="1" style="max-width:99%">'.$meta_box_value.'</textarea>';
			break;			
		}
		print $content;	
	}
	
	public function SavePost($post_id)
	{
		global $post;
		foreach($this->value as $singleMetaBoxTemplate) {
			// Verify
			if($singleMetaBoxTemplate['post_types'] && $singleMetaBoxTemplate['field_type'] && $singleMetaBoxTemplate['context'] && $singleMetaBoxTemplate['priority']){
				foreach($singleMetaBoxTemplate['post_types'] as $metaBoxpostType){			
					if ( !wp_verify_nonce( $_POST[str_replace(" ", "_", $singleMetaBoxTemplate['name']).'_noncename'], plugin_basename(__FILE__) )) {
						return $post_id;
					}

					if ( 'page' == $_POST['post_type'] ) {
						if ( !current_user_can( 'edit_page', $post_id ))
							return $post_id;
					} else {
						if ( !current_user_can( 'edit_post', $post_id ))
							return $post_id;
					}
					
					$data = $_POST[str_replace(" ", "_", $singleMetaBoxTemplate['name'])];
					if(get_post_meta($post_id, str_replace(" ", "_", $singleMetaBoxTemplate['name'])) == "")
						add_post_meta($post_id, str_replace(" ", "_", $singleMetaBoxTemplate['name']), $data, true);
					elseif($data != get_post_meta($post_id, str_replace(" ", "_", $singleMetaBoxTemplate['name']), true))
						update_post_meta($post_id, str_replace(" ", "_", $singleMetaBoxTemplate['name']), $data);
					elseif($data == "")
						delete_post_meta($post_id, str_replace(" ", "_", $singleMetaBoxTemplate['name']), get_post_meta($post_id, str_replace(" ", "_", $singleMetaBoxTemplate['name']), true));
				}
			}
		}		
	}
}
 */