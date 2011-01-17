<?php
define("INVALID_VALUE", -1);
define("VALID_VALUE", 1);
/*
 = ThemeOptions = 
Framework for rapid creation of theme options pages in WordPress administration.
Released under GPL license ( http://www.gnu.org/copyleft/gpl.html )
You should have received a copy of the GNU General Public License along with 
WordPress. If not, see <http://www.gnu.org/licenses/>.

 = Usage =
Pretty simple. You have the OptionsPage class and different option classes.
First, you create all options that you need. Then, you instantiate OptionsPage
class and pass created option objects to it's constructor. Then you attach the page 
to wordpress administration. That's it!

 = Example =
Let's say you need options page for following options: twitter & facebook username,
number of posts to show in featured section in the front end.

Followig above steps:
 == 1. First, you create all options that you need. ==
Creating options is done via factory method of base option class. You should pass
two arguments -- option type and option name. There is 3rd argument, that is optional --
option label. If you ommit it the label will be created from the option name.
<?php
	$twitter_username = wp_option::factory('text', 'twitter_username'); // The label in this case will be "Twitter Username"
	$facebook_username = wp_option::factory('text', 'facebook_username');
	$num_feat_posts = wp_option::factory('text', 'num_feat_posts');
	// The label in this case will be "num Feat Posts" which is not pretty.
	// So you may want to pass the 3rd argument:
	$num_feat_posts = wp_option::factory('text', 'num_feat_posts', 'Number of featured posts to display');
?>

 == 2. You instantiate OptionsPage class and pass created option objects to it's constructor ==
<?php
	$options = new OptionsPage(array(
		$twitter_username,
		$facebook_username,
		$num_feat_posts,
	));
?>
 == 3. You attach the page to wordpress administration ==
<?php
	$options->attach_to_wp();
?>

 = PHP4 vs PHP5 =
This code will work just fine on PHP4. However, you'll be not able to use method chaining(because PHP4 does not allow it).
So this:
<?php
$options = new OptionsPage(array(
	wp_option: wp_option::factory('text', 'twitter_username')->help_text('Your Twitter Username');
));
?>
will not work on PHP4, but it will work on PHP5. To make it work on both PHP versions you'll need to recode it this way:
<?php
$twitter_username = wp_option::factory('text', 'twitter_username');
$twitter_username->help_text('Your Twitter Username');
$options = new OptionsPage(array(
	$twitter_username
));
?>

 = Built-in options documentation =
Check out comments above each wp_option_* class for brief description what it does.
*/
/* -------------------------------------------------------------------- */

/*abstract*/ class base_wp_option {
	/* Name of the option, as it will be insrted in database. No funny charachters please! */
	var $name;
	
	/* Label of the option, as it will be shown on settings page */
	var $label;
	
	/* Type of the option - i.e. text option, checkbox option etc. Every option type is
	   subclass of Wp_option class, and its name is "wp_option_{$option_type}" */
	var $type;
	
	/* Value of the option when the use hasn't setuped it yet.*/
	var $default_value;
	
	/* Value of the option */
	var $value;
	
	/* Hash that keeps key-value pairs of custom HTML attributes */
	var $html_attrs = array();
	
	
	var $help_text;
	
	/*static*/ 
	function factory($opt_type, $name, $label=null) {
	    $class_name = "Wp_option_$opt_type";
	    if (!class_exists($class_name)) {
	    	trigger_error("Cannot create option from type $opt_type -- unknown type", E_USER_ERROR);
	    }
	    
	    if (empty($name)) {
	    	trigger_error("Cannot create option without name", E_USER_ERROR);
	    }
	    if (preg_match('~[^a-zA-Z0-9_]~', $name)) {
	    	trigger_error("Option name can include only latin letters, numbers, dashes and underscores, 
	    			\"$name\" is not valid option name", E_USER_ERROR);
	    }
	    
	    if (is_null($label)) {
	    	$label = ucwords(str_replace(array('_', '-'), ' ', $name));
	    }
	    
	    return new $class_name($name, $label);
	}
	
	/* Cosntructor. Do not override, use init() instead */ 
	/* final */ function base_wp_option($name, $label) {
		$this->name = $name;
		$this->label = $label;
	    $this->init();
	}
	
	/* Implement this in child classes if you need to do some initialization.
	   This will be called immediatlly after the constructor */
	function init() {}
	
	/* Collects information from input and setups object value */
	/*public*/ function set_value_from_input() {
		$this->set_value($_REQUEST[$this->name]);
	}
	
	/* This needs to be implemented in every child class */
	/*abstract*/ function render() {}
	
	function set_value($value) {
	    $this->value = $value;
	}
	
	/* You can setup HTML tag attributes via this field. Pass them in hash array where key is the attribute name
	   and value is attribute value, i.e.:
	   array(
		   'maxlength'=>'32',
		   'style'=>'width: 180px;'
	   )
	 */
	function set_html_attr($attrs) {
	    $this->html_attrs = $attrs;
	    return $this;
	}
	function get_custom_attrs() {
	    $html_attrs = array();
	    foreach ($this->html_attrs as $key => $value) {
	    	$html_attrs[] = $key . '="' . $value . '"';
	    }
	    return implode(' ', $html_attrs);
	}
	
	function help_text($help_text) {
	    $this->help_text = $help_text;
	    return $this;
	}
}
// Base class for all options that use wordpress interface for managing options via 
// add_option / update_option / get_option
/*abstract*/ class wp_option extends base_wp_option {
	function init() {
		$this->set_value(get_option($this->name));
	}
	function admin_init() {
		if (isset($_GET['delete']) && $_GET['delete'] == $this->name) {
			$this->reset_value();
			?>
			<script type="text/javascript" charset="utf-8">
				window.location = "<?php echo str_replace('&delete='.$this->name, '', $_SERVER['REQUEST_URI']); ?>";
			</script>
			<?php
		}
	}
	function render($field_html) {
	    $html = '
			<tr valign="top">
				<th scope="row" class="field-label"><label for="' . $this->name . '">' . $this->label . '</label></th>
				<td class="field">' . $field_html . ' <span class="description">' . $this->help_text . '</span></td>
			</tr>
		';
		return $html;
	}
	function save() {
	    update_option($this->name, $this->value);
	}
	
	function set_default_value($default_vallue) {
		$this->default_value = $default_vallue;
		$current_value = get_option($this->name);
		if ($current_value) {
			return;
		}
	    add_option($this->name, $default_vallue);
	    $this->set_value($default_vallue);
	}
	
	function reset_value() {
		$this->value = $this->default_value;
		$this->save();
	}
	
	function add_to_get($key, $val) {
		return '?' . preg_replace('~&?' . preg_quote($key) . '(=|$|&)([^&]*)?~', '', $_SERVER['QUERY_STRING']) . '&' . $key . '=' . $val;
	}
	
	function get_reset_link() {
		return $this->add_to_get('delete', $this->name);
	}
}
/* The most basic(and common used) option -- text input option */
class wp_option_text extends wp_option {
	function init() {
	    wp_option::init();
	    $this->html_attrs = array_merge(
			array('class'=>'regular-text'),
			$this->html_attrs
		);
	}
	function render() {
		$field_html = '<input type="text" name="' . $this->name . '" value="' . $this->value . '" id="' . $this->name . '" ' . $this->get_custom_attrs() . ' />';
	    return wp_option::render($field_html);
	}
}
/* textarea option */
class wp_option_textarea extends wp_option {
	function init() {
	    wp_option::init();
	    $this->html_attrs = array_merge(
			array('class'=>'regular-text'),
			$this->html_attrs
		);
	}
	function render() {
		$field_html = '<textarea name="' . $this->name . '" id="' . $this->name . '" ' . $this->get_custom_attrs() . '>' . $this->value . '</textarea>';
	    return wp_option::render($field_html);
	}
}

/* Select box with all categories. Commonly used for choosing "Featured" posts category */
class wp_option_choose_category extends wp_option {
	function render() {
		$dropdown_html = wp_dropdown_categories(array(
			'name'=>$this->name,
			'echo'=>false,
			'hide_empty'=>false,
			'hierarchical'=>1,
			'exclude'=>1, // Exclude uncategorized
			'selected'=>$this->value,
			'show_option_none'=>'Please Choose',
		));
	    return wp_option::render($dropdown_html);
	}
}

/* Select box with all pages */
class wp_option_choose_page extends wp_option {
	function render() {
		$dropdown_html = wp_dropdown_pages(array(
			'name'=>$this->name,
			'echo'=>false,
			'hierarchical'=>1,
			'selected'=>$this->value,
			'show_option_none'=>'Please Choose',
		));
	    return wp_option::render($dropdown_html);
	}
}

class wp_option_file extends wp_option {
	/**
	 * http://xref.limb-project.com/tests_runner/lib/spikephpcoverage/src/util/Utility.php.source.txt
	 * 
	 * Make directory recursively.
	 * (Taken from: http://aidan.dotgeek.org/lib/?file=function.mkdirr.php)
	 *
	 * @param $dir Directory path to create
	 * @param $mode=0755
	 * @return True on success, False on failure
	 * @access protected
	 */
	function mkdir_recursive($dir, $mode=0755) {
	    // Check if directory already exists
	    if (is_dir($dir) || empty($dir)) {
	        return true;
	    }
	    // Crawl up the directory tree
	    $next_pathname = substr($dir, 0, strrpos($dir, DIRECTORY_SEPARATOR));
	    if ($this->mkdir_recursive($next_pathname, $mode)) {
	        if (!file_exists($dir)) {
	            return mkdir($dir, $mode);
	        }
	    }
	
	    return false;
	}
	function render() {
		if (defined('WP_ADMIN')) {
	    	$this->admin_init();
	    }
		$html = '<input type="file" name="' . $this->name . '" />';
		if ($this->value) {
			$html .= "&nbsp;Current File: <a href=" . get_option('upload_path') . $this->value . "'>Download</a>";
			$html .= "&nbsp;|&nbsp;<a href='".$this->get_reset_link()."'>Delete</a>";
		}
		return wp_option::render($html);
	}
	function get_file_extension($filename) {
		$ext = preg_replace('~.*\.~', '', $filename);
		return $ext;
	}
	function validate_file() {
		return VALID_VALUE;
	}
	function set_value_from_input() {
	    if (empty($_FILES) || !is_uploaded_file($_FILES[$this->name]['tmp_name'])) {
	    	return;
	    }
		$valid = $this->validate_file();
		if ($valid == INVALID_VALUE) {
			return $valid;
		}
		$upload_location = wp_upload_dir();
	    $upload_dir = $upload_location['path'];
	    
	    $filename = preg_replace('~[^\w\.]~', '', $_FILES[$this->name]['name']);
	    
	    $destination = $upload_dir . '/' . $filename;
	    
	    $filename_ch = 1;
	    while (file_exists($destination)) {
	    	$destination = $upload_dir . '/' . $filename_ch . '-' . $filename;
	    	$filename_ch++;
	    }
	    
	    if (copy($_FILES[$this->name]['tmp_name'], $destination)) {
	    	if ($this->value && file_exists($upload_dir . $this->value)) {
	    		unlink($upload_dir . $this->value);
	    	}
	    	$this->value = $upload_location['url'] . '/' . $filename;
	    	return VALID_VALUE;
	    } else {
	    	$this->validation_error = "Error occured while writing a file. Please check whether " . $upload_dir . " is a writable directory.";
			return INVALID_VALUE;
	    }
	}
}

class wp_option_image extends wp_option_file {
	function render() {
		if (defined('WP_ADMIN')) {
	    	$this->admin_init();
	    }
		$html = '<input type="file" name="' . $this->name . '" />';
		if ($this->value) {
			$html .= "&nbsp;<a href='".$this->value."?TB_width=800' class='thickbox'>View Current Image</a>";
			$html .= "&nbsp;|&nbsp;<a href='".$this->get_reset_link()."'>Delete</a>";
		}
		$html .= '<br />';
		return wp_option::render($html);
	}
	function validate_file() {
		$valid = getimagesize($_FILES[$this->name]['tmp_name']);
		if (!$valid) {
			$this->validation_error = "The uploaded filetype is invalid (".$this->get_file_extension($_FILES[$this->name]['name']).").";
			return INVALID_VALUE;
		}
		return VALID_VALUE;
	}
}

/* Simple select box that should be populated with values before rendering */
class wp_option_select extends wp_option {
	var $opts = array();
	function add_options($opts) {
	    $this->opts = $opts;
	}
	function render() {
		if (empty($this->opts)) {
			trigger_error("Please add options to \"$this->label\" before rendering.", E_USER_ERROR);
		}
		$html = '<select name="'  . $this->name . '">';
		foreach ($this->opts as $value) {
			$selected = '';
			if ($value==$this->value) {
				$selected = 'selected="selected"';
			}
			$html .= "\n";
			$html .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
		}
		$html .= '</select>';
	    return wp_option::render($html);
	}
}
class OptionsPage {
	var $options;
	var $tpl_vars = array();
	
	function OptionsPage($options) {
	    foreach ($options as $option) {
	    	if (!is_a($option, 'base_wp_option')) {
	    		trigger_error("Not wp_option object was passed to OptionsPage creating method", E_USER_ERROR);
	    	}
	    }
	    $this->options = $options;
	    $this->theme_name = get_current_theme();

	    $this->page_title = $this->theme_name . " Options";
	    $this->menu_title = $this->theme_name . " Options";
	    $this->needed_permissions = 'edit_themes';
	}
	
	function fire_admin() {
	    if ($_SERVER['REQUEST_METHOD']=='POST') {
	    	$this->save_opts();
	    }
	    $this->show_form();
	}
	
	function show_form() {
	    include_once('form.tpl.php');
	}
	function save_opts() {
	    foreach ($this->options as $opt) {
	    	$opt->set_value_from_input();
	    	$opt->save();
	    }
	    $this->tpl_vars['saved'] = 1;
	}
	function attach_to_wp() {
	    add_action('admin_menu', array($this, 'attach_to_wp_admin'));
	}
	function attach_to_wp_admin() {
	    add_theme_page(
	    	$this->page_title, 
	    	$this->menu_title, 
	    	$this->needed_permissions, 
	    	/* file */ basename(__FILE__), 
	    	/* callback */ array($this, 'fire_admin')
		);
	}
}
include_once("choose-color-scheme.php");
?>