<?php
class ECF_Panel {
	/* Only pages that are children of the page with this path will have the panel */
	var $page_path;
	/* Only posts in this category will have the panel */
	var $cat_slug;
	/* Only posts with this page template will have the panel */
	var $template_name;
	
	// loaded post info
	var $post_type;
	var $post_id;
	
	/**
	 * Constructor. Pretty close to add_meta_box signature
	 */
	function ECF_Panel($id, $title, $post_type, $context, $priority) {
	    $this->id = $id;
	    $this->title = $title;
	    $this->post_type = $post_type;
	    $this->context = $context;
	    $this->priority = $priority;
	    
	    add_action('admin_menu', array($this, '_attach'));
	    add_action('save_post', array($this, 'save'));
	    
	    // since more than one panel could be added to
	    // single page we need to make sure that styles
	    // are added only once
	    $theme_root = get_bloginfo('stylesheet_directory');
	    wp_enqueue_style(
	    	'ecf_styles',
	    	"$theme_root/lib/enhanced-custom-fields/tpls/style.css"
	    );
	}
	function _attach() {
	    add_meta_box(
	    	$this->id, 
	    	$this->title, 
	    	array($this, 'render'), 
	    	$this->post_type, 
	    	$this->context, 
	    	$this->priority
	    );
	}
	function render() {
		if (isset($_GET['post'])) {
			// editing post -- take the post type from GET
			$post_id = intval($_GET['post']);
			$this->post_type = get_post_type($post_id);
			$this->set_post_id($post_id);
		} else {
			// adding new post -- get the post type from root file name
			$root_file = basename($_SERVER['PHP_SELF']);
			
			if ($root_file=='post-new.php') {
				$this->post_type = 'post';
			} else if ($root_file=='page-new.php') {
				$this->post_type = 'page';
			} else {
				ecf_conf_error("Unknow post type");
			}
		}

		if ($this->post_type=='page' && !empty($this->page_path)) {
			$this->parent_page = get_page_by_path($this->page_path);
			if (!$this->parent_page) {
				ecf_conf_error("Unknow page parent $this->page_path");
			}
			add_action('admin_footer', array($this, '_print_pages_js'));
		} elseif ($this->post_type=='post' && !empty($this->cat_slug)) {
			$this->cat = get_category_by_slug($this->cat_slug);
			add_action('admin_footer', array($this, '_print_pages_js'));
		} elseif($this->post_type=='page' && !empty($this->template_name)) {
			add_action('admin_footer', array($this, '_print_pages_js'));
		}
		// Print the actual content
		$html = '<table width="100%">';
	    foreach ($this->fields as $field) {
	    	$html .= $field->render();
	    }
	    $html .= '</table>';
	    echo $html;
	}
	function _print_pages_js() {
	    include 'tpls/js.php';
	}
	function add_fields($fields) {
	    $this->fields = $fields;
	}
	function show_on_page($page_path) {
	    $this->page = $page_path;
	}
	function show_on_cat($cat_slug) {
	    $this->cat_slug = $cat_slug;
	}	
	function show_on_template($template_path) {
		$this->template_path = $template_path;
	}
	
	function set_post_id($post_id) {
		if ( $rev_post_id = wp_is_post_revision($post_id) )
			$post_id = $rev_post_id;
			
	    $this->post_id = $post_id;
	    foreach ($this->fields as $f) {
	    	$f->post_id = $post_id;
	    	$f->load();
	    }
	}
	function save($post_id) {
		// Make sure that this isn't a revision
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}
		
		$this->set_post_id($post_id);
		
	    foreach ($this->fields as $field) {
	    	$field->set_value_from_input();
	    	$field->save();
	    }
	}
}
?>