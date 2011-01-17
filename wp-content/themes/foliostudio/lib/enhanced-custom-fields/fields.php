<?php
class ECF_Field {
	var $default_value;
	var $value;
	
	var $post_id;
	
	var $html_id;
	
	function factory($type, $name, $label) {
		$class = "ECF_Field$type";
		
		if (!class_exists($class)) {
			ecf_conf_error("Cannot add meta field $type -- unknow type. ");
		}
		if (substr($name, 0, 1)!='_') {
			// add underscore to custom field name -- this will remove it from 
			// custom fields list in administration
			$name = "_$name";
		}
	    return new $class($name, $label);
	}
	function ECF_Field($name, $label) {
	    $this->name = $name;
	    $this->label = $label;
	    
	    $this->html_id = 'ecf-'. md5(mt_rand() . $this->name . $this->label);
	    
	    $this->init();
	    
	}
	function load() {
		if (empty($this->post_id)) {
			ecf_conf_error("Cannot load");
		}
		$value = get_post_meta($this->post_id, $this->name, 1);
	    $this->set_value($value);
	}
	// abstract method
	function init() {}
	
	function set_value($value) {
	    $this->value = $value;
	}
	function set_default_value($default_value) {
	    $this->default_value = $default_value;
	    return $this;
	}
	function set_description($description) {
		$this->description = $description;
		return $this;
	}
	function render_row($field_html) {
		$description = isset($this->description) ? '<p class="ecf-description">' . $this->description . '</p>' : '' ;
	    return '
<tr class="ecf-field-container">
	<td class="ecf-label"><label for="' . $this->html_id . '">' . $this->label . '</label></td>
	<td>' . $field_html . $description . '</td>
</tr>
';
	}
	function set_value_from_input() {
		if ( !isset($_POST[$this->name]) ) { return; }
		$value = $_POST[$this->name];
	    $this->set_value($value);
	}
	function save() {
	    update_post_meta($this->post_id, $this->name, $this->value);
	}
	// abstract method
	function render() {}
	
	function build_html_atts($tag_atts) {
	    $default = array(
	    	'class'=>'ecf-field ecf-' . strtolower(get_class($this)),
	    	'id'=>$this->html_id,
	    );
	    
	    if (isset($tag_atts['class'])) {
	    	$tag_atts['class'] .= ' ' . $default['class'];
	    }
	    return array_merge($default, $tag_atts);
	}
	// Builds HTML for tag. 
	// example usage:
	// echo $this->build_tag('strong', array('class'=>'red'), 'I'm bold and red');
	// ==> <strong class="red">I'm bold and red</strong>
	function build_tag($tag, $atts, $content=null) {
	    $atts_text = '';
	    foreach ($atts as $key=>$value) {
	    	$atts_text .= ' ' . $key . '="' . esc_attr($value) . '"';
	    }
	    
	    $return = '<' . $tag . $atts_text;
	    if (!is_null($content)) {
	    	$return .= '>' . $content . '</' . $tag . '>';
	    } else {
	    	$return .= ' />';
	    }
	    return $return;
	}
	
}
class ECF_FieldText extends ECF_Field {
	function render() {
		$input_atts = $this->build_html_atts(array(
			'type'=>'text',
			'name'=>$this->name,
			'value'=>$this->value,
		));
		$field_html = $this->build_tag('input', $input_atts);
		
	    return $this->render_row($field_html);
	}
}
class ECF_FieldTextarea extends ECF_Field {
	function render() {
		$textarea_atts = $this->build_html_atts(array(
			'name'=>$this->name,
		));
		$val = $this->value ? $this->value : '';
		$field_html = $this->build_tag('textarea', $textarea_atts, $val);
		
	    return $this->render_row($field_html);
	}
}
class ECF_FieldSelect extends ECF_Field {
	var $options = array();
	function add_options($options) {
	    $this->options = $options;
	    return $this;
	}
    function render() {
    	if (empty($this->options)) {
    		ecf_conf_error("Add some options to $this->name");
    		return;
    	}
		$options = '';
		foreach ($this->options as $key=>$value) {
			$options_atts = array('value'=>$key);
			if ($this->value==$key) {
				$options_atts['selected'] = "selected";
			}
			$options .= $this->build_tag('option', $options_atts, $value);
		}
		$select_atts = $this->build_html_atts(array(
			'name'=>$this->name,
		));
		$select_html = $this->build_tag('select', $select_atts, $options);
		
	    return $this->render_row($select_html);
	}
}
class ECF_FieldImage extends ECF_Field {
	var $width, $height;
	var $thumbnails = array(), $called = false;
	function init() {
		$theme_root = get_bloginfo('stylesheet_directory');
	    wp_enqueue_script(
	    	'ecf-enable-file-uploads',
	    	"$theme_root/lib/enhanced-custom-fields/tpls/enable-file-uploads.js"
	    );
	    $this->width = $this->height = null;
	    ECF_Field::init();
	}
	function enable_form_file_uploads() {
	    include 'js/make-admin_editable';
	}
	function set_size($width, $height) {
	    $this->width = intval($width);
	    $this->height = intval($height);
	}
	function add_thumbnail($thumb_name, $width, $height) {
		$this->thumbnails[$thumb_name] = array('name' => $thumb_name, 'width' => $width, 'height' => $height);
	}
	function render() {
	    $atts = $this->build_html_atts(array(
		    'type'=>'file',
		    'name'=>$this->name,
	    ));
	    $input_html = $this->build_tag('input', $atts);
	    if ( !empty($this->value) ) {
	    	$input_html .= '<img src="' . get_upload_url() . '/' . $this->value . '" alt="" height="100" class="ecf-view_image"/>';
	    }
	    
	    return $this->render_row($input_html);
	}
	function set_value_from_input() {
		if ($this->called) {
			// Due bug in wordpress this will be called twice. Since the file is moved the
			// first time seond attemp will fail and trigger error.
			return;
		}
		if ( empty($_FILES[$this->name]) || $_FILES[$this->name]['error'] != 0) {
			return;
		}
		$this->called = true;
		
		// Build destination path
		$upload_path = get_upload_dir();
		
		$file_ext = array_pop(explode('.', $_FILES[$this->name]['name']));
		
		// Build image name (+path)
		$image_id = time();
		$image_path = $this->name . '/' . $this->post_id . '-' . $image_id . '.' . $file_ext;
		
		$file_dest = $upload_path . DIRECTORY_SEPARATOR . $image_path;
		if ( !file_exists( dirname($file_dest) ) ) {
			mkdir( dirname($file_dest) );
		}
		
		// Remove old image
		if ( !empty($this->value) && $this->value != $image_path) {
			if ( file_exists($upload_path . DIRECTORY_SEPARATOR . $this->value) ) {
				unlink($upload_path . DIRECTORY_SEPARATOR . $this->value);
			}
			$old_image_parts = explode('.', $this->value);
			$old_image_ext = array_pop($old_image_parts);
			
			// Remove old thumbnails
			foreach ($this->thumbnails as $thumbnail) {
				$thumbnail_path = $upload_path . DIRECTORY_SEPARATOR . implode($old_image_parts) . '-' . $thumbnail['name'] . '.' . $old_image_ext;
				if ( $thumbnail_path && file_exists($thumbnail_path)) {
					unlink($thumbnail_path);
				}
			}
			
		}
		
		// Move file
		if ( move_uploaded_file($_FILES[$this->name]['tmp_name'], $file_dest) !== FALSE ) {
	    	$this->set_value($image_path);
	    	
			// Resize if width and height are set
			if ( !($this->width == null && $this->height == null)) {
				$resized = image_resize($file_dest , $this->width, $this->height, true, 'tmp');
				// Check if image was resized
				if ( is_string($resized) ) {
					if ( file_exists($file_dest)) {
						unlink($file_dest);
					}
					rename($resized, $file_dest);
				}
			}
			
			
			// Create thumbnails
			foreach ($this->thumbnails as $thumbnail) {
				$resized = image_resize($file_dest , $thumbnail['width'], $thumbnail['height'], true, $thumbnail['name']);
				// Check if image was resized
				update_post_meta($this->post_id, $this->name . '_' . $thumbnail['name'], $this->name . '/' . $this->post_id . '-' . $image_id . '-' . $thumbnail['name'] . '.' . $file_ext);
			}
		}
	}
}

class ECF_FieldFile extends ECF_Field {
	function init() {
		$theme_root = get_bloginfo('stylesheet_directory');
	    wp_enqueue_script(
	    	'ecf-enable-file-uploads',
	    	"$theme_root/lib/enhanced-custom-fields/tpls/enable-file-uploads.js"
	    );
	    ECF_Field::init();
	}
	function enable_form_file_uploads() {
	    include 'js/make-admin_editable';
	}
	function render() {
	    $atts = $this->build_html_atts(array(
		    'type'=>'file',
		    'name'=>$this->name,
	    ));
	    $input_html = $this->build_tag('input', $atts);
	    if ( !empty($this->value) ) {
	    	$input_html .= '<a href="' . get_option('home') . '/wp-content/uploads/' . $this->value . '" alt="" class="ecf-view_file">View File</a>';
	    }
	    
	    return $this->render_row($input_html);
	}
	function set_value_from_input() {
		static $run = false;
		if ( $run || empty($_FILES[$this->name]) || $_FILES[$this->name]['error'] != 0) {
			return;
		}
		$run = true;
		
		// Build destination path
		$upload_path = get_option( 'upload_path' );
		$upload_path = trim($upload_path);
		if ( empty($upload_path) || realpath($upload_path) == false ) {
			$upload_path = WP_CONTENT_DIR . '/uploads';
		}
		
		$file_ext = array_pop(explode('.', $_FILES[$this->name]['name']));
		
		// Build file name (+path)
		$file_path = $this->name . '/' . $this->post_id . '-' . time() . '.' . $file_ext;
		
		$file_dest = $upload_path . DIRECTORY_SEPARATOR . $file_path;
		if ( !file_exists( dirname($file_dest) ) ) {
			mkdir( dirname($file_dest) );
		}
		
		if ( !empty($this->value) && $this->value != $file_path) {
			if ( file_exists($upload_path . DIRECTORY_SEPARATOR . $this->value) ) {
				unlink($upload_path . DIRECTORY_SEPARATOR . $this->value);
			}
		}
		
		// Move file
		if ( move_uploaded_file($_FILES[$this->name]['tmp_name'], $file_dest) != FALSE ) {
	    	$this->set_value($file_path);
		}
	}
}
class ECF_FieldSeparator extends ECF_Field {
	function render() {
		$field_html = '';
		
	    return $this->render_row($field_html);
	}
	function render_row($field_html) {
	    return '
		<tr class="ecf-field-container">
			<td class="ecf-label">&nbsp;</td>
			<td>' . (( !empty($this->label) ) ? '<strong>' . $this->label . '</strong>' : '') . '&nbsp;</td>
		</tr>
		';
	}
}

class ECF_FieldMap extends ECF_Field {
	var $lat, $long, $zoom, $api_key;
	function init() {
		$this->description = 'Double click on the map and marker will appear.<br/>Drag &amp; Drop the marker to new position on the map.';
		$this->lat = $this->long = 0;
		$this->api_key = '';
		$this->zoom = 1;
	}
	function render() {
		ob_start();
		include_once('tpls/ecf_fieldmap.php');
	    return $this->render_row(ob_get_clean());
	}
	function set_api_key($_key) {
		$this->api_key = $_key;
		return $this;
	}
	function set_position($_lat, $_long, $_zoom) {
		$this->lat = $_lat;
		$this->long = $_long;
		$this->zoom = $_zoom;
		return $this;
	}
}

?>