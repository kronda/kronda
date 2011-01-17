<?php
wp_enqueue_script('jquery');// Include jquery

automatic_feed_links();

register_sidebar(array(
	'name' => 'Right Sidebar',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<h3 class="widgettitle">',
    'after_title' => '</h3>',
));

register_sidebar(array(
	'name' => 'Footer',
    'before_widget' => '<div id="%1$s" class="col widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="widgettitle">',
    'after_title' => '</h3>',
));
include_once('lib/twitter/versions-proxy.php');
include_once('lib/hacks.php');
include_once('lib/video-functions.php');
include_once('lib/flickr_gallery.php');

function attach_theme_settings() {
	include_once('lib/theme-options/theme-options.php');
	include_once('options/theme-options.php');
	// include_once('options/other-options.php');
	
	include_once('lib/custom-widgets/widgets.php');
	include_once('options/theme-widgets.php');
}

attach_theme_settings();

$numbers = array('one', 'two', 'three');

function get_tiny_url($url) {
	$cache_key = md5("tiny_urk::$url");
	$cached = get_option($cache_key, -1);
	if ($cached!==-1) {
		return $cached;
	}
	$r = wp_remote_get('http://tinyurl.com/api-create.php?url='.$url);
	$url = $r['body'];
	update_option($cache_key, $url);
	return $data;  
}

function get_upload_dir () {
	$updir = wp_upload_dir();
	return $updir['basedir'];
}

function get_upload_url () {
	$updir = wp_upload_dir();
	return $updir['baseurl'];
}

/**
 * Shortcut function for acheiving
 * $no_nav_pages = _get_page_by_name('no-nav-pages');
 * wp_list_pages('sort_column=menu_order&exclude_tree=' . $no_nav_pages->ID);
 * with:
 * wp_list_pages('sort_column=menu_order&' . exclude_no_nav());
 */
function exclude_no_nav($no_nav_pages_slug='no-nav-pages') {
    $no_nav_page = _get_page_id_by_name($no_nav_pages_slug);
    return "exclude_tree=$no_nav_page";
}

/**
 * Checks if particular page ID has parent with particular slug
 */
$__has_parent_depth = 0;
function has_parent($id, $parent_name) {
    global $__has_parent_depth;
    $__has_parent_depth++;
    if ($__has_parent_depth==100) {
    	exit('too much recursion');
    }
    $post = get_post($id);
    
    if ($post->post_name==$parent_name) {
    	return true;
    }
    if ($post->post_parent==0) {
    	return false;
    }
    $__has_parent_depth--;
    return has_parent($post->post_parent, $parent_name);
}

/**
 * Example function for printing comments
 */
function print_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; 
	$depth_nice = ($depth > 3) ? 3 : $depth ; ?>
	<div class="post-comment <?php if($depth_nice != 1) { echo 'comment-reply-'.($depth_nice-1); } else { echo 'nb'; } ?>" id="comment-<?php echo $comment->comment_ID ?>">
		<?php if ( $depth_nice != 1): ?>
			<span class="notext arrow">&nbsp;</span>
		<?php endif ?>
		<div class="cl">&nbsp;</div>
		<div class="right-side">
			<a href="#" class="img"><?php echo get_avatar( $comment, 86, get_bloginfo('stylesheet_directory') . '/images/blank.gif' ); ?></a>
			<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
		</div>
		<div class="hld">
			<div class="comment-entry">
				<h4><?php comment_author(); ?> <span>Says</span>,</h4>
				<p><?php comment_text(); ?></p>	
			</div>
			<span class="date">on <?php comment_date('F d, Y') ?> at <?php comment_time('h:i A') ?></span>
		</div>
		
		<div class="cl">&nbsp;</div>
	</div>
	<?php
}

define('STR_WORD_COUNT_FORMAT_ADD_POSITIONS', 2);
/**
 * >>> shortalize('lorem ipsum dolor sit amet');
 * ... lorem ipsum dolor sit amet
 * >>> shortalize('lorem ipsum dolor sit amet', 5);
 * ... lorem ipsum dolor sit amet
 * >>> shortalize('lorem ipsum dolor sit amet', 4);
 * ... lorem ipsum dolor sit...
 * >>> shortalize('lorem ipsum dolor sit amet', -1);
 */
function shortalize($input, $words_limit=15) {
	$input = strip_tags($input);
    $words_limit = abs(intval($words_limit));
    if ($words_limit==0) {
        return $input;
    }
    $words = str_word_count($input, STR_WORD_COUNT_FORMAT_ADD_POSITIONS);
    if (count($words)<=$words_limit + 1) {
        return $input;
    }
    $loop_counter = 0;
    foreach ($words as $word_position => $word) {
        $loop_counter++;
        if ($loop_counter==$words_limit + 1) {
            return substr($input, 0, $word_position) . '...';
        }
    }
}

# crawls the pages tree up to top level page ancestor 
# and returns that page as object
function get_page_ancestor($page_id) {
    $page_obj = get_page($page_id);
    while($page_obj->post_parent!=0) {
        $page_obj = get_page($page_obj->post_parent);
    }
    return get_page($page_obj->ID);
}

# example function for filtering page template
function filter_template_name() {
    global $post;
    
	$page_tpl = get_post_meta($post->ID, '_wp_page_template', 1);
	
	if ($page_tpl!="default") {
		return TEMPLATEPATH . '/' . $page_tpl;
	}
    /*
	# example logic here ... 
    $page_ancestor = get_page_ancestor($post->ID);
    
    if ($page_ancestor->post_name!='pages-branch-name') {
    	return TEMPLATEPATH . "/my-branch-template.php";
    }
    
    return TEMPLATEPATH . "/page.php";
    */
}
// add_filter('page_template', 'filter_template_name');

# shortcut for get_post_meta. Returns the string value 
# of the custom field if it exist. 
# second arg is required if you're not in the loop
function get_meta($key, $id=null) {
	if (!isset($id)) {
	    global $post;
	    if (empty($post->ID)) {
	    	return null;
	    }
	    $id = $post->ID;
    }
    return get_post_meta($id, $key, true);
}

/**
 * Returns posts page as object -- user can setup this page from
 * Settings > Reading.
 *
 * if there is no page for posts null is returned
 */
function get_posts_page() {
    $posts_page_id = get_option('page_for_posts');
    if ($posts_page_id) {
    	return get_page($posts_page_id);
    }
    return null;
}

/**
 * Parses custom field values to hash array. Expected 
 * custom field value format:
 * {{{
 * title: my cool title
 * image: http://example.com/images/1.jpg
 * caption: my cool image
 * }}}
 * Return array looks like:
 * array(
 *     'title'=>'my cool title',
 *     'image'=>'http://example.com/images/1.jpg',
 *     'caption'=>'my cool image',
 * )
 */
function parse_custom_field($details) {
    $lines = array_filter(preg_split('~\r|\n~', $details));
    $res = array();
    foreach ($lines as $line) {
        if(!preg_match('~(.*?):(.*)~', $line, $pieces)) {
            continue;
        }
        $label = trim($pieces[1]);
        $val = trim($pieces[2]);
        $res[$label] = $val;
    }
    return $res;
}

function get_page_id_by_path($page_path) {
    $p = get_page_by_path($page_path);
    if (empty($p)) {
    	return null;
    }
    return $p->ID;
}

/**
 * Parses tweet text, returning the links in proper link format
 *
 * Example:
 * echo filter_tweet_text('Test text with a link http://test.com/link')
 *
 * Returned string from this example is (without the quotation marks):
 * "Test text with a link <a href="http://test.com/link">http://test.com/link</a>"
 */
function filter_tweet_text($tweet_text) {
	$filtered = preg_replace('~(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)~', '<a href="$1" target="_blank">$1</a>', $tweet_text);
	return $filtered;
}

function get_bottom_sidebar() {
	if( !dynamic_sidebar('Footer') ) {
		include_once('bottom-sidebar.php');
	}
}

function apollo_is_template($id, $template_name) {
	$page_template = get_post_custom_values('_wp_page_template', $id );
	return $page_template[0] == $template_name;
}

function is_gallery_subpage( $id ) {
	$page = get_page($id);
	if (empty($page->post_parent)) {
		return false;
	}
	return apollo_is_template($page->post_parent, 'gallery-page.php');
}

function is_full_width_page( $id ) {
	return apollo_is_template($id, 'page-no-sidebar.php');
}
/* CUSTOM IMAGE METABOX*/
function print_custom_image_meta_box() {
	if ( isset($_GET['post']) ) {
		$post_id = intval($_GET['post']);
		$image = get_post_meta($post_id, '_custom_post_image', true);
		if ( !empty($image) ) {
			echo '<p><a href="' . get_upload_url() . '/' . $image . '">View current image</a></p>';
			echo '<p><input type="checkbox" name="delete_custom_post_image" id="delete_custom_post_image"/><label for="delete_custom_post_image">Delete image</label></p>';
		}
	}
	$script = "
		<script>jQuery(document).ready(function($){ $('form#post').attr('enctype', 'multipart/form-data') })</script>
	";
	echo '<p><input type="file" name="custom_post_image" value="" id="custom_post_image"><span style="display: block;">Recommended image width: 610px</span></p>' . $script;
}
function attach_custom_image() {
	add_meta_box('customiamge', 'Post Image', 'print_custom_image_meta_box', 'post', 'side', 'low');
	add_meta_box('customiamge', 'Post Image', 'print_custom_image_meta_box', 'page', 'side', 'low');
}
function save_custom_image( $post_id) {
	if ( $the_post = wp_is_post_revision($post_id) )
		$post_id = $the_post;
		
	// Build destination path
	$upload_path = get_upload_dir();
		
	if ( isset($_POST['delete_custom_post_image'])) {
		$old_path_r = get_post_meta($post_id, '_custom_post_image_r', true);
		if ( !empty($old_path_r) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_r);
			delete_post_meta($post_id, '_custom_post_image_r');
		}
		$old_path_square = get_post_meta($post_id, 'custom_post_image_square', true);
		if ( !empty($old_path_square) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_square);
			delete_post_meta($post_id, 'custom_post_image_square');
		}
		$old_path = get_post_meta($post_id, '_custom_post_image', true);
		if ( !empty($old_path) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path);
			delete_post_meta($post_id, '_custom_post_image');
		}
	} elseif ( !isset($_FILES['custom_post_image']) || $_FILES['custom_post_image']['error'] != UPLOAD_ERR_OK ) {
		return;
	}
	
	$file_ext = array_pop(explode('.', $_FILES['custom_post_image']['name']));
	
	// Build image name (+path)
	$image_path = 'custom_post_images/' . time() . '.' . $file_ext;
	
	$file_dest = $upload_path . DIRECTORY_SEPARATOR . $image_path;
	if ( !file_exists( dirname($file_dest) ) ) {
		mkdir( dirname($file_dest) );
	}
	
	// Move file
	if ( move_uploaded_file($_FILES['custom_post_image']['tmp_name'], $file_dest) != FALSE ) {
		$res = image_resize($file_dest , 610, 0, false, 'r');
		// There is a resized version of the image
		$old_path_r = get_post_meta($post_id, '_custom_post_image_r', true);
		if ( !empty($old_path_r) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_r);
		}
		if ( is_string($res) ) {
			// The image was resized
			update_post_meta($post_id, '_custom_post_image_r', 'custom_post_images/' . basename($res));
		}
		
		$res = image_resize($file_dest , 60, 60, true, 'square');
		// There is a square version of the image
		$old_path_square = get_post_meta($post_id, 'custom_post_image_square', true);
		if ( !empty($old_path_square) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_square);
		}
		if ( is_string($res) ) {
			// The image was resized
			update_post_meta($post_id, 'custom_post_image_square', 'custom_post_images/' . basename($res));
		}
		
		// Remove old image
		$old_path = get_post_meta($post_id, '_custom_post_image', true);
		if ( !empty($old_path) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path);
		}
		// Update meta
		update_post_meta($post_id, '_custom_post_image', $image_path);
	}
}


/* CUSTOM BIG IMAGE METABOX*/
function print_custom_big_image_meta_box() {
	if ( isset($_GET['post']) ) {
		$post_id = intval($_GET['post']);
		$image = get_post_meta($post_id, '_custom_big_post_image', true);
		if ( !empty($image) ) {
			echo '<p><a href="' . get_upload_url() . '/' . $image . '">View current image</a></p>';
			echo '<p><input type="checkbox" name="delete_custom_big_post_image" id="delete_custom_big_post_image"/><label for="delete_custom_big_post_image">Delete image</label></p>';
		}
	}
	$script = "
		<script>jQuery(document).ready(function($){ $('form#post').attr('enctype', 'multipart/form-data') })</script>
	";
	echo '<p><input type="file" name="custom_big_post_image" value="" id="custom_big_post_image"><span style="display: block;">Recommended image width: 910px</span></p>' . $script;
}
function attach_custom_big_image() {
	if ( !isset($_GET['post']) || !is_full_width_page($_GET['post']) ) {
		return;
	}
	add_meta_box('bigcustomiamge', 'Big Post Image', 'print_custom_big_image_meta_box', 'page', 'side', 'low');
}
function save_custom_big_image( $post_id) {
	if ( $the_post = wp_is_post_revision($post_id) )
		$post_id = $the_post;
		
	// Build destination path
	$upload_path = get_upload_dir();
		
	if ( isset($_POST['delete_custom_big_post_image'])) {
		$old_path_r = get_post_meta($post_id, '_custom_big_post_image_r', true);
		if ( !empty($old_path_r) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_r);
			delete_post_meta($post_id, '_custom_big_post_image_r');
		}
		$old_path_square = get_post_meta($post_id, 'custom_big_post_image_square', true);
		if ( !empty($old_path_square) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_square);
			delete_post_meta($post_id, 'custom_big_post_image_square');
		}
		$old_path = get_post_meta($post_id, '_custom_big_post_image', true);
		if ( !empty($old_path) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path);
			delete_post_meta($post_id, '_custom_big_post_image');
		}
	} elseif ( !isset($_FILES['custom_big_post_image']) || $_FILES['custom_big_post_image']['error'] != UPLOAD_ERR_OK ) {
		return;
	}
	
	$file_ext = array_pop(explode('.', $_FILES['custom_big_post_image']['name']));
	
	// Build image name (+path)
	$image_path = 'custom_big_post_image/' . time() . '.' . $file_ext;
	
	$file_dest = $upload_path . DIRECTORY_SEPARATOR . $image_path;
	if ( !file_exists( dirname($file_dest) ) ) {
		mkdir( dirname($file_dest) );
	}
	
	// Move file
	if ( move_uploaded_file($_FILES['custom_big_post_image']['tmp_name'], $file_dest) != FALSE ) {
		$res = image_resize($file_dest , 910, 0, false, 'r');
		// There is a resized version of the image
		$old_path_r = get_post_meta($post_id, '_custom_big_post_image_r', true);
		if ( !empty($old_path_r) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_r);
		}
		if ( is_string($res) ) {
			// The image was resized
			update_post_meta($post_id, '_custom_big_post_image_r', 'custom_big_post_images/' . basename($res));
		}
		
		// Remove old image
		$old_path = get_post_meta($post_id, '_custom_big_post_image', true);
		if ( !empty($old_path) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path);
		}
		// Update meta
		update_post_meta($post_id, '_custom_big_post_image', $image_path);
	}
}

/* GALLERY IMAGE METABOX*/
function print_gallery_image_meta_box() {
	if ( isset($_GET['post']) ) {
		$post_id = intval($_GET['post']);
		$image = get_post_meta($post_id, '_gallery_post_image', true);
		if ( !empty($image) ) {
			echo '<p><a href="' . get_upload_url() . '/' . $image . '" onclick="window.open(this.href); return false;">View current image</a></p>';
			echo '<p><input type="checkbox" name="delete_gallery_post_image" id="delete_gallery_post_image"/><label for="delete_gallery_post_image">Delete image</label></p>';
		}
	}
	$script = "
		<script>jQuery(document).ready(function($){ $('form#post').attr('enctype', 'multipart/form-data') })</script>
	";
	echo '<p><input type="file" name="gallery_post_image" value="" id="gallery_post_image"><span style="display: block;">Recommended image width: 610px</span></p>' . $script;
}

function attach_gallery_image() {
	if ( !isset($_GET['post']) || !is_gallery_subpage($_GET['post']) ) {
		return;
	}
	add_meta_box('galleryiamge', 'Gallery Image', 'print_gallery_image_meta_box', 'page', 'side', 'low');
}

function save_gallery_image( $post_id) {
	if ( $the_post = wp_is_post_revision($post_id) )
		$post_id = $the_post;
		
	// Build destination path
	$upload_path = get_upload_dir();
		
	if ( isset($_POST['delete_gallery_post_image'])) {
		$old_path_thumb = get_post_meta($post_id, '_gallery_post_image_thumb', true);
		if ( !empty($old_path_thumb) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_thumb);
			delete_post_meta($post_id, '_gallery_post_image_thumb');
		}
		$old_path = get_post_meta($post_id, '_gallery_post_image', true);
		if ( !empty($old_path) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path);
			delete_post_meta($post_id, '_gallery_post_image');
		}
	} elseif ( !isset($_FILES['gallery_post_image']) || $_FILES['gallery_post_image']['error'] != UPLOAD_ERR_OK ) {
		return;
	}
	
	$file_ext = array_pop(explode('.', $_FILES['gallery_post_image']['name']));
	
	// Build image name (+path)
	$current_time = time();
	$image_path = 'gallery_post_images/' . $current_time . '.' . $file_ext;
	
	$file_dest = $upload_path . DIRECTORY_SEPARATOR . $image_path;
	if ( !file_exists( dirname($file_dest) ) ) {
		mkdir( dirname($file_dest) );
	}
	
	// Move file
	if ( move_uploaded_file($_FILES['gallery_post_image']['tmp_name'], $file_dest) != FALSE ) {
		$res = image_resize($file_dest , 250, 147, false, 'small');
		// There is a resized version of the image
		$old_path_small = get_post_meta($post_id, '_gallery_post_image_small', true);
		if ( !empty($old_path_small) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path_small);
		}
		if ( is_string($res) ) {
			// The image was resized
			update_post_meta($post_id, '_gallery_post_image_small', 'gallery_post_images/' . basename($res));
		} else {
			update_post_meta($post_id, '_gallery_post_image_small', $image_path);
		}
		
		$res = image_resize($file_dest , 610, 0, false, 'orig');
		if ( is_string($res) ) {
			safe_unlink($file_dest);
			$image_path = 'gallery_post_images/' . $current_time . '-orig.' . $file_ext;
		}
		// Remove old image
		$old_path = get_post_meta($post_id, '_gallery_post_image', true);
		if ( !empty($old_path) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path);
		}
		update_post_meta($post_id, '_gallery_post_image', $image_path);
	}
}

/* FEATURED IMAGE METABOX*/
function print_featured_image_meta_box() {
	if ( isset($_GET['post']) ) {
		$post_id = intval($_GET['post']);
		$image = get_post_meta($post_id, '_featured_post_image', true);
		if ( !empty($image) ) {
			echo '<p><a href="' . get_upload_url() . '/' . $image . '" onclick="window.open(this.href); return false;">View current image</a></p>';
			echo '<p><input type="checkbox" name="delete_featured_post_image" id="delete_featured_post_image"/><label for="delete_featured_post_image">Delete image</label></p>';
		}
	}
	$script = "
		<script>jQuery(document).ready(function($){ $('form#post').attr('enctype', 'multipart/form-data') })</script>
	";
	echo '<p><input type="file" name="featured_post_image" value="" id="featured_post_image"><span style="display: block;">Recommended image width: 940px</span></p>' . $script;
}
function attach_featured_image() {
	add_meta_box('featurediamge', 'Featured Image', 'print_featured_image_meta_box', 'post', 'side', 'low');
	add_meta_box('featurediamge', 'Featured Image', 'print_featured_image_meta_box', 'page', 'side', 'low');
}
function save_featured_image( $post_id) {
	if ( $the_post = wp_is_post_revision($post_id) )
		$post_id = $the_post;
		
	// Build destination path
	$upload_path = get_upload_dir();
		
	if ( isset($_POST['delete_featured_post_image'])) {
		$old_path = get_post_meta($post_id, '_featured_post_image', true);
		if ( !empty($old_path) ) {
			safe_unlink($upload_path . DIRECTORY_SEPARATOR . $old_path);
			delete_post_meta($post_id, '_featured_post_image');
		}
	} elseif ( !isset($_FILES['featured_post_image']) || $_FILES['featured_post_image']['error'] != UPLOAD_ERR_OK ) {
		return;
	}
	
	$file_ext = array_pop(explode('.', $_FILES['featured_post_image']['name']));
	
	// Build image name (+path)
	$image_path = 'featured_post_images/' . time() . '.' . $file_ext;
	
	$file_dest = $upload_path . DIRECTORY_SEPARATOR . $image_path;
	if ( !file_exists( dirname($file_dest) ) ) {
		mkdir( dirname($file_dest) );
	}
	
	// Move file
	if ( move_uploaded_file($_FILES['featured_post_image']['tmp_name'], $file_dest) != FALSE ) {
		$res = image_resize($file_dest , 940, 0, true, 'f');
		// There is a square version of the image
		$old_path = get_post_meta($post_id, '_featured_post_image', true);
		if ( !empty($old_path) ) {
			safe_unlink($upload_path . '/' . $old_path);
		}
		if ( is_string($res) ) {
			// The image was resized
			safe_unlink($file_dest);
			update_post_meta($post_id, '_featured_post_image', 'featured_post_images/' . basename($res));
		} else {
			update_post_meta($post_id, '_featured_post_image', $image_path);
		}
	}
}
/* GALLERY TESTIMONIAL */
function save_gallery_testimonial( $post_id ) {
	if ( $the_post = wp_is_post_revision($post_id) )
		$post_id = $the_post;
	
	if ( !is_gallery_subpage($post_id) ) {
		return;
	}
	
	$testim_text = isset($_POST['apollo_testim_text']) ? $_POST['apollo_testim_text'] : '' ;
	$testim_client = isset($_POST['apollo_testim_client']) ? $_POST['apollo_testim_client'] : '' ;
	$testim_company = isset($_POST['apollo_testim_company']) ? $_POST['apollo_testim_company'] : '' ;
	
	update_post_meta($post_id, '_gallery_testimonial_text', $testim_text);
	update_post_meta($post_id, '_gallery_testimonial_client', $testim_client);
	update_post_meta($post_id, '_gallery_testimonial_company', $testim_company);
	
}
function print_gallery_testimonial_meta_box() {
	if ( isset($_GET['post']) ) {
		$client = get_post_meta($_GET['post'], '_gallery_testimonial_client', true);
		$company = get_post_meta($_GET['post'], '_gallery_testimonial_company', true);
		$text = get_post_meta($_GET['post'], '_gallery_testimonial_text', true);
	} else {
		$client = $company = $text = '';
	}
	?>
	<h5>Client name</h5><input type="text" name="apollo_testim_client" value="<?php echo $client ?>" id="client_name" style="width: 100%;" />
	<h5>Company name</h5><input type="text" name="apollo_testim_company" value="<?php echo $company ?>" id="company_name" style="width: 100%;" />
	<h5>Text</h5><textarea name="apollo_testim_text" rows="8" cols="40" id="testim_text" style="width: 100%;"><?php echo $text ?></textarea>
	<?php
}
function attach_gallery_testimonial() {
	if ( !isset($_GET['post']) || !is_gallery_subpage($_GET['post']) ) {
		return;
	}
	add_meta_box('gallery_testimonial', 'Testimonial', 'print_gallery_testimonial_meta_box', 'page', 'side', 'low');
}

/* CUSTOM OPTIONS */
function save_custom_options($page_id) {
	if ( $the_post = wp_is_post_revision($post_id) )
		$post_id = $the_post;
	
	if ( isset($_POST['apollo_featured_text']) ) {
		if ( isset($_POST['apollo_featured_check']) ) {
			update_post_meta($page_id, 'featured_check', 'true');
			update_post_meta($page_id, 'featured', $_POST['apollo_featured_text']);
		} else {
			update_post_meta($page_id, 'featured_check', 'false');
		}
	}
	if (isset($_POST['apollo_teaser_text'])) {
		update_post_meta($page_id, 'teaser_text', $_POST['apollo_teaser_text']);
	}
	if ( is_gallery_subpage($page_id) && isset($_POST['apollo_gallery_description']) ) {
		update_post_meta($page_id, 'gallery_description', $_POST['apollo_gallery_description']);
	}
}
function print_custom_options_meta_box() {
	if ( isset($_GET['post']) ) {
		$featured_text = get_post_meta($_GET['post'], 'featured', true);
		$featured_check = get_post_meta($_GET['post'], 'featured_check', true) == 'true';
		$teaser_text = get_post_meta($_GET['post'], 'teaser_text', true);
		if (is_gallery_subpage($_GET['post'])) {
			$gallery_desc = get_post_meta($_GET['post'], 'gallery_description', true);
		}
	} else {
		$featured_check = false;
		$featured_text = $teaser_text = $gallery_desc = '';
	}
	?>
	<label for="apollo_options_featured" class="selectit"><input type="checkbox" name="apollo_featured_check" value="" <?php if($featured_check) echo 'checked="checked"'; ?> id="apollo_options_featured" />Featured</label>
	<h5>Featured Text</h5>
	<textarea name="apollo_featured_text" rows="2" cols="30" style="width: 95%;"><?php echo $featured_text ?></textarea>
	<h5>Teaser text</h5>
	<textarea name="apollo_teaser_text" rows="2" cols="30" style="width: 95%;"><?php echo $teaser_text ?></textarea>
	<?php if ( isset($_GET['post']) && is_gallery_subpage($_GET['post']) ): ?>
		<h5>Gallery Description</h5>
		<textarea name="apollo_gallery_description" rows="2" cols="30"  style="width: 95%;"><?php echo $gallery_desc ?></textarea>
	<?php endif ?>
	<?php
}
function attach_custom_options() {
	add_meta_box('custom_options', 'Custom Options', 'print_custom_options_meta_box', 'post', 'normal', 'high');
	add_meta_box('custom_options', 'Custom Options', 'print_custom_options_meta_box', 'page', 'normal', 'high');
}

function safe_unlink($path) {
	if ( file_exists($path) ) {
		unlink($path);
	}
}
add_action('admin_menu', 'attach_custom_image');
add_action('save_post', 'save_custom_image');

add_action('admin_menu', 'attach_custom_big_image');
add_action('save_post', 'save_custom_big_image');

add_action('admin_menu', 'attach_featured_image');
add_action('save_post', 'save_featured_image');

add_action('admin_menu', 'attach_gallery_image');
add_action('save_post', 'save_gallery_image');

add_action('admin_menu', 'attach_gallery_testimonial');
add_action('save_post', 'save_gallery_testimonial');

add_action('admin_menu', 'attach_custom_options');
add_action('save_post', 'save_custom_options');

function apollo_wp_get_related_posts($before_title="",$after_title="") {	
	global $wpdb, $post,$table_prefix;
	$wp_rp = get_option("wp_rp");
	
	$wp_rp_title = $wp_rp["wp_rp_title"];
	
	$exclude = explode(",",$wp_rp["wp_rp_exclude"]);	
	if ( $exclude != '' ) {
		$q = 'SELECT tt.term_id FROM '. $table_prefix .'term_taxonomy tt, ' . $table_prefix . 'term_relationships tr WHERE tt.taxonomy = \'category\' AND tt.term_taxonomy_id = tr.term_taxonomy_id AND tr.object_id = '.$post->ID;

		$cats = $wpdb->get_results($q);
		
		foreach(($cats) as $cat) {
			if (in_array($cat->term_id, $exclude) != false){
				return;
			}
		}
	}
		
	if(!$post->ID){return;}
	$now = current_time('mysql', 1);
	$tags = wp_get_post_tags($post->ID);
	
	$taglist = "'" . $tags[0]->term_id. "'";
	
	$tagcount = count($tags);
	if ($tagcount > 1) {
		for ($i = 1; $i < $tagcount; $i++) {
			$taglist = $taglist . ", '" . $tags[$i]->term_id . "'";
		}
	}
	
	$limit = $wp_rp["wp_rp_limit"];
	if ($limit) {
		$limitclause = "LIMIT $limit";
	}	else {
		$limitclause = "LIMIT 10";
	}
	
	$q = "SELECT p.ID, p.post_title, p.post_content,p.post_excerpt, p.post_date,  p.comment_count, count(t_r.object_id) as cnt FROM $wpdb->term_taxonomy t_t, $wpdb->term_relationships t_r, $wpdb->posts p WHERE t_t.taxonomy ='post_tag' AND t_t.term_taxonomy_id = t_r.term_taxonomy_id AND t_r.object_id  = p.ID AND (t_t.term_id IN ($taglist)) AND p.ID != $post->ID AND p.post_status = 'publish' AND p.post_date_gmt < '$now' GROUP BY t_r.object_id ORDER BY cnt DESC, p.post_date_gmt DESC $limitclause;";
	
	$related_posts = $wpdb->get_results($q);
	
	$output = "";
	
	if (!$related_posts){
		$wp_no_rp = $wp_rp["wp_no_rp"];
		$wp_no_rp_text = $wp_rp["wp_no_rp_text"];
	
		if(!$wp_no_rp || ($wp_no_rp == "popularity" && !function_exists('akpc_most_popular'))) $wp_no_rp = "text";
		
		if($wp_no_rp == "text"){
			if(!$wp_no_rp_text) $wp_no_rp_text= __("No Related Post",'wp_related_posts');
			$output  .= '<li>'.$wp_no_rp_text .'</li>';
		}	else{
			if($wp_no_rp == "random"){
				if(!$wp_no_rp_text) $wp_no_rp_text= __("Random Posts",'wp_related_posts');
				$related_posts = wp_get_random_posts($limitclause);
			}	elseif($wp_no_rp == "commented"){
				if(!$wp_no_rp_text) $wp_no_rp_text= __("Most Commented Posts",'wp_related_posts');
				$related_posts = wp_get_most_commented_posts($limitclause);
			}	elseif($wp_no_rp == "popularity"){
				if(!$wp_no_rp_text) $wp_no_rp_text= __("Most Popular Posts",'wp_related_posts');
				$related_posts = wp_get_most_popular_posts($limitclause);
			}
			$wp_rp_title = $wp_no_rp_text;
		}
	}
	$i = 0;
	$output .= '<div class="col fl"><ul>';
	foreach ($related_posts as $related_post ){
		$i ++;
		if ( $i == 3) {
			$output .= '</ul></div><div class="col fr"><ul><li>';
		} else {
			$output .= '<li>';
		}
		$thumb_meta = get_post_meta($related_post->ID, $wp_rp["wp_rp_thumbnail_post_meta"], true);
		if ($wp_rp["wp_rp_thumbnail"] && !empty($thumb_meta) ){
			$output .=  '<a class="img" href="'.get_permalink($related_post->ID).'" title="'.wptexturize($related_post->post_title).'"><img src="' . get_upload_url() .  '/' . $thumb_meta .'" alt="'.wptexturize($related_post->post_title).'" /></a>';
		} else {
			$output .=  '<a class="img" href="'.get_permalink($related_post->ID).'" title="'.wptexturize($related_post->post_title).'"><img src="' . get_bloginfo('stylesheet_directory') . '/images/recent-post-img1.gif" alt="'.wptexturize($related_post->post_title).'" /></a>';
		}
		
		if ((!$wp_rp["wp_rp_thumbnail"])||($wp_rp["wp_rp_thumbnail"] && $wp_rp["wp_rp_thumbnail_text"])){
		
			if ($wp_rp["wp_rp_date"]){
				$dateformat = get_option('date_format');
				$output .= mysql2date($dateformat, $related_post->post_date) . " -- ";
			}
			$output .= '<div class="hdl">';
			$output .=  '<h4><a href="'.get_permalink($related_post->ID).'" title="'.wptexturize($related_post->post_title).'">'.wptexturize($related_post->post_title).'</a></h4>';
			
			if ($wp_rp["wp_rp_comments"]){
				$output .=  "<a href=\"" . get_permalink($related_post->ID) . "#comments\">";
				if ($related_post->comment_count == 0) {
					$output .=  'No Comments';
				} else if ( $related_post->comment_count == 1) {
					$output .=  '1 Comment';
				} else {
					$output .=  $related_post->comment_count . ' Comment';
				}
				$output .=  "</a>";
			}
			$output .= '</div>';
			
			if ($wp_rp["wp_rp_except"]){
				$wp_rp_except_number = trim($wp_rp["wp_rp_except_number"]);
				if(!$wp_rp_except_number) $wp_rp_except_number = 200;
				if($related_post->post_excerpt){
					$output .= '<br /><small>'.(mb_substr(strip_tags($related_post->post_excerpt),0,$wp_rp_except_number)).'...</small>';
				}else{
					$output .= '<br /><small>'.(mb_substr(strip_tags($related_post->post_content),0,$wp_rp_except_number)).'...</small>';
				}
			}	
		}
		$output .=  '<div class="cl">&nbsp;</div></li>';
	}
	
	$output .= '</ul></div>';
		
	$wp_rp_title_tag = $wp_rp["wp_rp_title_tag"];
	if($before_title){
		if($wp_rp_title != '') $output = $before_title.$wp_rp_title .$after_title. $output;
	}else{
		if(!$wp_rp_title_tag) $wp_rp_title_tag ='h3';
		if($wp_rp_title != '') $output =  '<'.$wp_rp_title_tag.'  class="related_post_title">'.$wp_rp_title .'</'.$wp_rp_title_tag.'>'. $output;
	}
	$output = '<div class="related-posts">' . $output . '<div class="cl">&nbsp;</div></div>';
	return $output;
}

function apollo_wp_related_posts(){
	
	$output = apollo_wp_get_related_posts() ;

	echo $output;
}

function previous_posts_link_class() {
	return 'class="previouspostslink"';
}

function next_posts_link_class() {
	return 'class="nextpostslink"';
}
		
add_filter('previous_posts_link_attributes','previous_posts_link_class');
add_filter('next_posts_link_attributes','next_posts_link_class');

function apollo_print_nav() {
	global $post;
	$current_id = $post->ID;
	$is_blog = (is_home() && !is_front_page()) || (is_single() || is_archive() || is_search());
	$is_gallery = apollo_is_template($current_id, 'gallery-page.php') || is_gallery_subpage($current_id);
	
	$pages = get_option('header_navigation', array()); 
	foreach($pages as $id): ?>
		<?php
			if ( $is_gallery && apollo_is_template($id, 'gallery-page.php') ) {
				$is_active = true;
			} elseif ( !$is_blog) {
				$is_active = $current_id == $id;
			} else {
				$is_active = get_option('page_for_posts') == $id;
			}
		?>
		<li><a href="<?php echo get_permalink($id) ?>" <?php if($is_active) { echo 'class="active"';} ?>><span><?php echo get_the_title($id) ?></span></a></li>
	<?php endforeach;
}

function display_navigation() {
	global $wpdb, $wp_query;

	if (is_single()) 
		return;
	
	$request = $wp_query->request;
	$posts_per_page = intval(get_query_var('posts_per_page'));
	$paged = intval(get_query_var('paged'));
	$numposts = $wp_query->found_posts;
	$max_page = intval($wp_query->max_num_pages);
	
	if (empty($paged) || $paged == 0)
		$paged = 1;
		
	$pages_to_show = 5;
	$larger_page_to_show = 3;
	$larger_page_multiple =10;
	$pages_to_show_minus_1 = $pages_to_show - 1;
	$half_page_start = floor($pages_to_show_minus_1/2);
	$half_page_end = ceil($pages_to_show_minus_1/2);
	$start_page = $paged - $half_page_start;
	
	if ($start_page <= 0)
		$start_page = 1;

	$end_page = $paged + $half_page_end;
	if (($end_page - $start_page) != $pages_to_show_minus_1) {
		$end_page = $start_page + $pages_to_show_minus_1;
	}

	if ($end_page > $max_page) {
		$start_page = $max_page - $pages_to_show_minus_1;
		$end_page = $max_page;
	}

	if ($start_page <= 0)
		$start_page = 1;

	$larger_pages_array = array();
	if ( $larger_page_multiple )
		for ( $i = $larger_page_multiple; $i <= $max_page; $i += $larger_page_multiple )
			$larger_pages_array[] = $i;
		
	if ($max_page > 1) {
		echo $before.'<div class="wp-pagenavi">'."\n";
		next_posts_link('&raquo;', $max_page);
		$larger_page_start = 0;
		foreach($larger_pages_array as $larger_page) {
			if ($larger_page < $start_page && $larger_page_start < $larger_page_to_show) {
				$page_text = number_format_i18n($larger_page);
				echo '<a href="'.clean_url(get_pagenum_link($larger_page)).'" class="page" title="'.$page_text.'">'.$page_text.'</a>';
				$larger_page_start++;
			}
		}
		previous_posts_link('&laquo;');
		for($i = $start_page; $i  <= $end_page; $i++) {						
			if ($i == $paged) {
				$current_page_text = number_format_i18n($i);
				echo '<span class="current">'.$current_page_text.'</span>';
			} else {
				$page_text = number_format_i18n($i);
				echo '<a href="'.clean_url(get_pagenum_link($i)).'" class="page" title="'.$page_text.'">'.$page_text.'</a>';
			}
		}
		$larger_page_end = 0;
		foreach($larger_pages_array as $larger_page) {
			if ($larger_page > $end_page && $larger_page_end < $larger_page_to_show) {
				$page_text =number_format_i18n($larger_page);
				echo '<a href="'.clean_url(get_pagenum_link($larger_page)).'" class="page" title="'.$page_text.'">'.$page_text.'</a>';
				$larger_page_end++;
			}
		}
		echo '</div>'.$after."\n";
	}
}

function is_correct_email($email) {
	return preg_match('~^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$~', $email);
}

function handle_newsletter_contact() {
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		if ( isset($_POST['nl_email']) && is_correct_email($_POST['nl_email'])) {
			// Add $_POST['nl_email'] to the newsletter
			$to = get_option('admin_email');
			
			$from_name = 'Newsletter : ' . $_POST['nl_email'] .' wants to join';
			$body = bloginfo('blogname') . ' Newsletter : ' . $_POST['nl_email'] .' wants  to join in.';
			$from_email = $_POST['nl_email'];
			
			$headers = 'From: ' . $from_email . "\r\n" .
						'Reply-To: ' . $from_email . "\r\n";
			
			@mail($to, bloginfo('blogname') . $from_name,  $body, $headers);
		} elseif( isset($_POST['c_message']) && !empty($_POST['c_message']) ) {
			// Send email to the admin with the user's message
			$to = get_option('admin_email');
			$from_name = isset($_POST['c_email']) ? ': message from ' . $_POST['c_name'] : '' ;
			$from_email = isset($_POST['c_email']) ? $_POST['c_email'] : '' ;
			$headers = 'From: ' . $from_email . "\r\n" .
						'Reply-To: ' . $from_email . "\r\n";
						
			@mail($to, get_bloginfo('blogname') . $from_name,  $_POST['c_message'], $headers);
		}
	}
}
function apollo_get_category_parents($cat_id) {
	$return = array();
	$cat = get_category($cat_id);
	
	do {
        $return[] = array(
        	'title'=>$cat->name,
        	'link'=>get_category_link($cat->term_id)
        );
    } while ($cat->parent!=0 && ($cat = get_category($cat->parent)));
    
    return $return;
}
function print_breadcrumbs($glue=' &raquo; ') {
	global $post;
	$stack = array();
	
	if ( !in_array('print_breadcrumbs', get_option('advanced_settings', array()) ) ) {
		return '&nbsp;';
	}

	$page_for_posts = get_option('page_for_posts', 0);
	array_push($stack, array(
			'title'=>'Home',
			'link'=>get_option('home'),
		)
	);
	
	if (is_page()) {
		$page_id = $post->ID;
		$page_obj = get_page($page_id);
		
		$tmp = array();
		
	    do {
	        $tmp[] = array(
	        	'title'=>apply_filters('the_title', $page_obj->post_title),
	        	'link'=>get_permalink($page_obj->ID)
	        );
	    } while ($page_obj->post_parent!=0 && ($page_obj = get_page($page_obj->post_parent)));
	    
	    $tmp = array_reverse($tmp);
	    
	    foreach ($tmp as $breadcrumb_elem) {
	    	array_push($stack, $breadcrumb_elem);
	    }
	} else {
		if ($page_for_posts) {
			$blog_page = get_page($page_for_posts);
			array_push($stack, array(
		    	'title'=>apply_filters('the_title', $blog_page->post_title),
		    	'link'=>get_permalink($blog_page->ID),
		    ));
		}
		
		if (is_single()) {
			$categories = get_the_category();
			$category = $categories[0];
			$ancestors = array_reverse(apollo_get_category_parents($category));
			
			foreach ($ancestors as $breadcrumb_elem) {
		    	array_push($stack, $breadcrumb_elem);
		    }
		    
		    array_push($stack, array(
		    	'title'=>get_the_title(),
		    	'link'=>get_permalink(),
		    ));
		} else if (is_category()) {
			$category = get_query_var('cat');
			$ancestors = array_reverse(apollo_get_category_parents($category));
			
			foreach ($ancestors as $breadcrumb_elem) {
		    	array_push($stack, $breadcrumb_elem);
		    }
		} else if (is_tag()) {
			array_push($stack, array(
				'title'=>single_tag_title('', false),
			    'link'=>get_tag_link(get_query_var('tag'))
			));
		} else if (is_day() ) {
			array_push($stack, array(
 				'title' => get_the_time('F j, Y'),
				'link' => get_day_link(get_the_time('j'), get_the_time('F'),  get_the_time('Y'))
			));
		} else if (is_month()) {
			array_push($stack, array(
 				'title' => get_the_time('F Y'),
				'link' => get_month_link(get_the_time('F'),  get_the_time('Y'))
			));
		} else if (is_year()) {
			array_push($stack, array(
 				'title' => get_the_time('Y'),
				'link' => get_year_link(get_the_time('Y'))
			));
		} else if (is_search()) {
			array_push($stack, array(
 				'title' => get_search_query(),
				'link' => get_option('home')
			));
		}
	}
	
	if (($page_for_posts && count($stack)<2) || count($stack)<1) {
		return;
	}
	
	$elems = array();
	$i = 0;
	foreach ($stack as $elem) {
		if ($i==count($stack)-1) {
			$html = $elem['title'];
		} else {
			$html = '<a href="' . $elem['link'] . '">' . $elem['title'] . '</a>';
		}
		$elems[] = $html;
		$i++;
	} 
	
	echo implode($glue, $elems);
	// return $stack;
}
?>