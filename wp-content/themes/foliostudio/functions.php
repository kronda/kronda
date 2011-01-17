<?php
wp_enqueue_script('jquery');// Include jquery

automatic_feed_links();

register_sidebar(array(
	'name' => 'Default Sidebar',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="widgettitle">',
    'after_title' => '</h3>',
));

register_sidebar(array(
	'name' => 'Footer Sidebar',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<h3 class="widgettitle">',
    'after_title' => '</h3>',
));

include_once('lib/twitter/versions-proxy.php');
include_once('lib/hacks.php');
include_once('lib/video-functions.php');
include_once('lib/SimpleValidator.php');

function attach_theme_options() {
	include_once('lib/theme-options/theme-options.php');
	include_once('options/theme-options.php');
	include_once('options/homepage-options.php');
	
	include_once('lib/custom-widgets/widgets.php');
	include_once('options/theme-widgets.php');
	
	include_once('lib/enhanced-custom-fields/enhanced-custom-fields.php');
}

attach_theme_options();

if (function_exists('wp_nav_menu')) {
	if ( !is_nav_menu('head-nav') ) {
		$header_nav_menu = wp_create_nav_menu('Header Navigation', array('slug' => 'head-nav'));
	} else {
		$header_nav_menu = wp_get_nav_menu_object('head-nav');
	}
	
	add_theme_support('nav-menus');
}

/* CUSTOM POST IMAGE ECF PANEL */
$custom_post_image_panel = new ECF_Panel('custom-post-image', 'Custom Image', 'post', 'normal', 'high');

$custom_post_image = ECF_Field::factory('image', 'custom_post_image', 'Image');
$custom_post_image->set_size(590, 220);
$custom_post_image->add_thumbnail('thumb', 100, 100);
$custom_post_image->set_description('Recommended Size: 590x220');

$custom_post_image_panel->add_fields(array(
    $custom_post_image,
));

/* ABOUT PAGE ECF PANEL */
$about_page_panel = new ECF_Panel('about-page-image', 'About Page Properties', 'page', 'normal', 'high');
$about_page_panel->template_name = 'about-page';

$about_page_image = ECF_Field::factory('image', 'about_page_image', 'Image');
$about_page_image->set_size(890, 190);
$about_page_image->set_description('Recommended size: 890x190');

$about_page_panel->add_fields(array(
	ECF_Field::factory('text', 'about_page_title', 'Custom Page Title'),
    $about_page_image,
	ECF_Field::factory('select', 'show_team_members', 'Show Team Members?')->add_options(array('y' => 'Yes', 'n' => 'No')),
));

/* CONTACT PAGE ECF PANEL */
$contact_page_panel = new ECF_Panel('contact-page-image', 'Contact Page Properties', 'page', 'normal', 'high');
$contact_page_panel->template_name = 'contact-page';

$contact_page_panel->add_fields(array(
	ECF_Field::factory('textarea', 'address', 'Address'),
	ECF_Field::factory('text', 'phone', 'Phone Number'),
	ECF_Field::factory('text', 'fax', 'Fax'),
	ECF_Field::factory('text', 'email', 'E-mail address'),
	ECF_Field::factory('map', 'location', 'Location')->set_api_key(get_option('google_map_api_key', '')),
));

/* TEAM MEMBERS ECF PANEL */
$team_members_panel = new ECF_Panel('team-member_properties', 'Team Member Properties', 'team_members', 'normal', 'high');

$team_member_image = ECF_Field::factory('image', 'team_member_image', 'Photo');
$team_member_image->set_size(90, 90);

$team_members_panel->add_fields(array(
	$team_member_image,
	ECF_Field::factory('textarea', 'description', 'Description'),
	ECF_Field::factory('text', 'position', 'Position'),
	ECF_Field::factory('text', 'twitter', 'Twitter account'),
	ECF_Field::factory('text', 'homepage', 'home Page'),
));

register_post_type('team_members', array(
	'labels' => array(
		'name'	 => 'The Team',
		'singular_name' => 'Team Member',
		'add_new' => __( 'Add Member' ),
		'view_item' => 'View Member',
		'edit_item' => 'Edit Member Details',
	),
	'public' => false,
	'exclude_from_search' => true,
	'show_ui' => true,
	'capability_type' => 'post',
	'hierarchical' => false,
	'rewrite' => false,
	'query_var' => false,
	'supports' => array('title')
));

add_filter("manage_edit-team_members_columns", "team_members_columns_filter");

function team_members_columns_filter($columns) {
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => "Name",
		"position" => "Position",
	);
	return $columns;
}

function clamp($value, $a, $b) {
	return ($value < $a ) ? $a : (($value > $b) ? $b : $value) ;
}

/* PAGES ECF PANEL */
$pages_panel = new ECF_Panel('custom-teaser-text', 'Page Options', 'page', 'normal', 'high');
$pages_panel->add_fields(array(
	ECF_Field::factory('text', 'custom_teaser_text', 'Custom Teaser Text')->set_description('Leave blank to show the default text.'),
));

/* PORTFOLIO ITEMS */
register_post_type('portfolio', array(
	'labels' => array(
		'name'	 => 'Portfolio',
		'singular_name' => 'Portfolio Item',
		'add_new' => __( 'Add Portfolio Item' ),
		'view_item' => 'View Portfolio Item',
		'edit_item' => 'Edit Portfolio Item',
	),
	'public' => true,
	'exclude_from_search' => true,
	'show_ui' => true,
	'capability_type' => 'post',
	'hierarchical' => true,
	'_edit_link' =>  'post.php?post=%d',
	'rewrite' => array(
		"slug" => "portfolios",
		"with_front" => false,
	),
	'query_var' => true,
	'supports' => array('title', 'page-attributes', 'editor'),
));

/* PORTFOLIO ITEMS ECF PANEL */
$portfolio_panel = new ECF_Panel('portfolio_properties', 'Portfolio Properties', 'portfolio', 'normal', 'high');

$portfolio_image = ECF_Field::factory('image', 'portfolio_image', 'Photo');
$portfolio_image->add_thumbnail('thumb', 276, 140);
$portfolio_image->add_thumbnail('medium', 590, 220);
$portfolio_image->set_description('Recommended size: 590x220');

$portfolio_featured = ECF_Field::factory('select', 'portfolio_featured', 'Featured Item?');
$portfolio_featured->options = array(
	'no' => 'No',
	'yes' => 'Yes'
);
$portfolio_featured->set_default_value('no');

$portfolio_panel->add_fields(array(
	$portfolio_image,
	$portfolio_featured,
	ECF_Field::factory('textarea', 'description', 'Description'),
));

add_filter("manage_edit-portfolio_columns", "portfolio_columns_filter");

function portfolio_columns_filter($columns) {
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => "Name",
		"description" => "Description",	
	);
	return $columns;
}

/* SLIDER ITEMS */
register_post_type('slide', array(
	'labels' => array(
		'name'	 => 'Slider',
		'singular_name' => 'Slide',
		'add_new' => __( 'Add Slide' ),
		'view_item' => 'View Slide',
		'edit_item' => 'Edit Slide',
	),
	'public' => false,
	'exclude_from_search' => true,
	'show_ui' => true,
	'capability_type' => 'post',
	'hierarchical' => false,
	'rewrite' => false,
	'query_var' => false,
	'supports' => array('page-attributes'),
));

add_filter("manage_edit-slide_columns", "slide_columns_filter");

function slide_columns_filter($columns) {
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"slide_title" => "Title",	
	);
	return $columns;
}

/* SLIDER ITEMS ECF PANEL */
$slide_panel = new ECF_Panel('slider_properties', 'Slide Properties', 'slide', 'normal', 'high');

$slide_big_image = ECF_Field::factory('image', 'slide_big_image', 'Big Image');
$slide_big_image->set_size(940, 350);
$slide_big_image->set_description('Recommended Image Size: 940x350');

$slide_small_image = ECF_Field::factory('image', 'slide_small_image', 'Small Image');
$slide_small_image->set_size(140, 52);
$slide_small_image->set_description('Recommended Image Size: maximum 140x52');

$slide_panel->add_fields(array(
	ECF_Field::factory('text', 'slide_title', 'Slide Title'),
	$slide_big_image,
	$slide_small_image,
));


function menage_columns_action($column) {
	global $post;
	$position = get_post_meta($post->ID, '_position', true);
	if ("ID" == $column) {echo $post->ID;}
	elseif ("position" == $column) {echo $position;}
	elseif ("description" == $column) {echo get_post_meta($post->ID, '_description', true);}
	elseif ("slide_title" == $column) {
		$title = get_post_meta($post->ID, '_slide_title', true);
		if ( empty($title) ) {
			$title = "(Slide " . $post->ID . ')';
		}
		echo '<strong><a href="' . get_edit_post_link($post->ID) . '" class="row-title">' . $title . '</a></strong>';
	};
}
add_action("manage_posts_custom_column", "menage_columns_action");

function get_upload_dir() {
	$updir = wp_upload_dir();
	return $updir['basedir'];
}

function get_upload_url() {
	$updir = wp_upload_dir();
	return $updir['baseurl'];
}

function anti_gpc($field) {
	$return = get_magic_quotes_gpc() ? stripslashes($field) : $field;
	return $return;
}

function theme_sendmail($from_mail, $subject, $mailcontent) {
	$headers = "From: " . $from_mail . "\nReply-To: " . $from_mail . "\n";
	$headers .= "Content-Transfer-Encoding: 8bit\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\n";
	@mail(get_bloginfo('admin_email'), $subject, $mailcontent, $headers);
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
	$GLOBALS['comment'] = $comment; ?>
	<div class="comment">
		<?php echo get_avatar($comment, 40, $default); ?>
		<p><strong><?php comment_author() ?></strong> on  <span class="light-blue"><?php comment_date() ?> at <?php comment_time() ?></span>  says:<br><br></p>
		<p><?php comment_text() ?></p>
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
	exit('filter_template_name');
    global $post;
    
	$page_tpl = get_post_meta($post->ID, '_wp_page_template', 1);
	
	if ($page_tpl!="default") {
		return TEMPLATEPATH . '/' . $page_tpl;
	}
	
	if ($post->post_type=='postfolio') {
		return TEMPLATEPATH . '/portfolio.php';
	}
    
    return TEMPLATEPATH . "/page.php";
}
add_filter('portfolio_template', 'filter_template_name');

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
 * Returns posts page as object (setuped from Settings > Reading > Posts Page).
 *
 * If the page for posts is not chosen null is returned
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
 * Returned array looks like:
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

/*
PHP 4.2.x Compatibility function
http://www.php.net/manual/en/function.file-get-contents.php#80707
*/
if (!function_exists('file_get_contents')) {
	function file_get_contents($filename, $incpath = false, $resource_context = null) {
		if (false === $fh = fopen($filename, 'rb', $incpath)) {
			trigger_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
			return false;
		}
		
		clearstatcache();
		if ($fsize = @filesize($filename)) {
			$data = fread($fh, $fsize);
		} else {
			$data = '';
			while (!feof($fh)) {
				$data .= fread($fh, 8192);
			}
		}
		
		fclose($fh);
		return $data;
	}
}

function _print_ie6_styles() {
    $ie_css_file = dirname(__FILE__) . '/ie6.css';
    
	if (!file_exists($ie_css_file)) {
    	return;
    }
    $ie6_hacks = file_get_contents($ie_css_file);
    if (empty($ie6_hacks)) {
    	return;
    }
    
    echo '
<!--[if IE 6]>
<style type="text/css" media="screen">';
    echo "\n\n" . str_replace(
    	'css/images/', 
    	get_bloginfo('stylesheet_directory') . '/images/', 
    	$ie6_hacks
    );
    echo '

</style>
<![endif]-->';
    
}
add_action('wp_head', '_print_ie6_styles');

function f_get_tagline() {
	if ( is_page() ) {
		# check for custom tagline
	}
	
	return get_option('header_intro_text', 'This is where your tagline will be shown');
}


function f_print_navigation() {
	wp_nav_menu(array('link_before' => '<span>', 'link_after' => '</span>', 'slug' => $header_nav_menu['slug']));
	?>
    <script type="text/javascript" charset="utf-8">
    	jQuery(function($) {
    		//Login form
    		$("#navigation li.login").each(function() {
    			$(this).append('<div class="login-dd dd"><form action=""><div class="form-holder"><div class="login-field"><input type="text" class="field blink" value="Username or email" title="Username or email" /></div><div class="login-field"><input type="password" class="field blink" value="Password" title="Password" /></div><input type="submit" value="Login" class="login-button" /></div></form></div>')
    		});
    	});						    	
    </script>		
	<?php
}


/**
 * Returns an array in the format: year: { month: number_posts}
 */
function f_get_archive_tree($ascending = false) {
	global $wpdb;
	
	$order = $ascending ? 'ASC' : 'DESC' ;
	
	$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
	$key = md5($query);
	$cache = wp_cache_get( 'wp_get_archives' , 'general');
	if ( !isset( $cache[ $key ] ) ) {
		$arcresults = $wpdb->get_results($query);
		$cache[ $key ] = $arcresults;
		wp_cache_set( 'wp_get_archives', $cache, 'general' );
	} else {
		$arcresults = $cache[ $key ];
	}
	
	$archive_tree = array();
	
	if ( $arcresults ) {
		foreach ( (array) $arcresults as $arcresult ) {
			$archive_tree[$arcresult->year][$arcresult->month] = $arcresult->posts;
		}
	}
	return $archive_tree;
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
		echo $before."\n";
		
		if ( $paged > 1) {
			echo '<a href="' . previous_posts(false) . '" class="prev">&laquo;</a>' ;
		}
		
		$larger_page_start = 0;
		foreach($larger_pages_array as $larger_page) {
			if ($larger_page < $start_page && $larger_page_start < $larger_page_to_show) {
				$page_text = number_format_i18n($larger_page);
				echo '<a href="'.clean_url(get_pagenum_link($larger_page)).'" class="page" title="'.$page_text.'">'.$page_text.'</a>';
				$larger_page_start++;
			}
		}
		for($i = $start_page; $i  <= $end_page; $i++) {	
			$page_text = number_format_i18n($i);
			echo '<a href="'.clean_url(get_pagenum_link($i)).'" class="page' . ($i==$paged? ' active':'') . '" title="'.$page_text.'">'.$page_text.'</a>';
		}
		$larger_page_end = 0;
		foreach($larger_pages_array as $larger_page) {
			if ($larger_page > $end_page && $larger_page_end < $larger_page_to_show) {
				$page_text =number_format_i18n($larger_page);
				echo '<a href="'.clean_url(get_pagenum_link($larger_page)).'" class="page" title="'.$page_text.'">'.$page_text.'</a>';
				$larger_page_end++;
			}
		}
		
		$nextpage = intval($paged) + 1;
		if ( $nextpage <= $max_page ) {
			echo '<a href="' . next_posts($max_page, false) . '" class="next">&raquo;</a>' ;
		}
		echo $after."\n";
	}
}

function _get_category_parents($cat_id) {
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

function print_breadcrumbs($before='', $glue=' &raquo; ', $after='') {
    $enable_breadcrumbs = get_option('enable_breadcrumbs');
    if ($enable_breadcrumbs == 'no') {
    	return;
    }
    global $post;
    
    $stack = array();

    $page_for_posts = get_option('page_for_posts', 0);
    array_push($stack, array(
            'title'=>'Home',
            'link'=>get_option('home'),
        )
    );
   	
    if (get_post_type($post) == 'portfolio') {
	    array_push($stack, array(
	            'title'=>'Portfolio',
	            'link'=>get_permalink(get_page_id_by_path('portfolio')),
	        )
	    );
        $page_id = $post->ID;
        $page_obj = get_post($page_id);
       
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
    } else if (is_page()) {
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
        
        if ( is_archive() ) {
            array_push($stack, array(
                'title'=>'Archive',
                'link'=>get_permalink(get_page_id_by_path('archive'))
            ));
        }
       
        if (is_single()) {
        	if ($post->post_type == 'post') {
	            $categories = get_the_category();
	            $category = $categories[0];
	            $ancestors = array_reverse(_get_category_parents($category));
	           
	            foreach ($ancestors as $breadcrumb_elem) {
	                array_push($stack, $breadcrumb_elem);
	            }
	           
	            array_push($stack, array(
	                'title'=>get_the_title(),
	                'link'=>get_permalink(),
	            ));
        	}

        } else if (is_category()) {
            $category = get_query_var('cat');
            $ancestors = array_reverse(_get_category_parents($category));
           
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
                'title' => 'Search results',
                'link' => '#'
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
            $html = '<a href="' . $elem['link'] . '" class="active">' . $elem['title'] . '</a>';
        } else {
            $html = '<a href="' . $elem['link'] . '">' . $elem['title'] . '</a>';
        }        
        $elems[] = $html;
        $i++;
    }
    
    echo $before . implode($glue, $elems) . $after;
}

function not_singular_content() {
	global $post;
	?>
	<?php if ( have_posts() ):  ?>
	<?php if ( !is_paged() ): the_post(); ?>
	<div class="main-post post doubleborder">	
		<h2 class="post-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h2>
		<div class="post-info">
			Posted on <?php the_time('F j, Y') ?> by <?php the_author_posts_link(); ?> in <?php the_category(', '); ?>
		</div>
		<?php  
		$image = get_post_meta(get_the_id(), '_custom_post_image', true);
		 if ( !empty($image) ): ?>
			<div class="image">
				<a href="<?php the_permalink(); ?>"><img src="<?php echo get_upload_url() ?>/<?php echo $image ?>" alt="" /></a>
				<a href="<?php comments_link(); ?>" class="comment-num"><?php comments_number('0', '1', '%') ?></a>
			</div>
		<?php endif ?>
		<div class="cl">&nbsp;</div>
		<div class="content">
			<?php  
			if ( empty($post->post_excerpt) ) {
				the_content();
			} else {
				the_excerpt();
			}
			?>
			<a href="<?php the_permalink() ?>" class="btn-read-more notext right">read more</a>
			<div class="cl">&nbsp;</div>
		</div>
	</div>
	<div class="cl">&nbsp;</div>
	<?php endif ?>
	<div class="posts">
		<?php while( have_posts() ): the_post(); ?>
			<div class="post doubleborder">
				<?php  
				$image = get_post_meta(get_the_id(), '_custom_post_image_thumb', true);
				 if ( !empty($image) ): ?>
					<div class="image">
						<a href="<?php the_permalink(); ?>"><img src="<?php echo get_upload_url() ?>/<?php echo $image ?>" alt="" /></a>
						<a href="<?php comments_link(); ?>" class="comment-num"><?php comments_number('0', '1', '%') ?></a>
					</div>
				<?php endif ?>
				<div class="content">
					<h2><a href="<?php the_permalink(); ?>"><?php the_title() ?></a></h2>
					<div class="post-info">
						Posted on <?php the_time('F j, Y') ?> by <?php the_author_posts_link(); ?> in <?php the_category(', '); ?>
					</div>
					<?php  
					if ( empty($post->post_excerpt) ) {
						the_content();
					} else {
						the_excerpt();
					}
					?>
				</div>
				<div class="cl">&nbsp;</div>
			</div>
		<?php endwhile; ?>
		<div class="navigation">
			<div class="older-post notext"><?php next_posts_link('&laquo; Older Posts') ?></div>
			<div class="newer-post notext"><?php previous_posts_link('Newer Posts &raquo;') ?></div>
			<div class="cl">&nbsp;</div>
		</div>
	</div>
	<?php else: ?>
		<h2 class="post-title">Sorry, nothing found.</h2>
		<br />
		<?php get_search_form(); ?>
	<?php endif ?>	
	<?php
}

function top_blog_section() {
	if (is_home() || is_single()) {
		$title = 'Blog';
	} elseif(is_archive()) {
		$title = 'Archive';
	} elseif(is_search()) {
		$title = 'Search Results';
	}
	?>
	<div id="top-text-section">
		<div class="doubleborder">
			<h2 class="cufon-plain left"><strong><?php echo $title; ?></strong></h2>		
			<a href="#" class="subscribers right"><strong>21,354</strong><br />Subscribers
				<span class="icon">&nbsp;</span> 
			</a>
			<div class="cl">&nbsp;</div>
		</div>	
	</div>	
	<?php
}

function get_prev_month($m, $y) {
	if($m == 1) {
		$m = 12;
		$y -= 1;
	} else {
		$m--;
	}
	return array('month' => $m, 'year' => $y);
}

function get_next_month($m, $y) {
	if($m == 12) {
		$m = 1;
		$y += 1;
	} else {
		$m++;
	}
	return array('month' => $m, 'year' => $y);
}

function prev_month_link($m, $y) {
	$date_attr = get_prev_month($m, $y);
	return get_month_link($date_attr['year'], $date_attr['month']);
}

function next_month_link($m, $y) {
	$date_attr = get_next_month($m, $y);
	return get_month_link($date_attr['year'], $date_attr['month']);
}

function htmlize($text, $tag = 'strong') {
	return preg_replace('~\*(.*?)\*~', "<$tag>$1</$tag>", $text);
}

function get_url($replace_key=null, $replace_value=null) {
	$adr = 'http://' . $_SERVER['HTTP_HOST'];
	$gets = array();
	$replaced = false;
	
	preg_match('~^(.*)\?~', $_SERVER['REQUEST_URI'], $gets);
	if ( !empty($gets) ) {
		$adr .= $gets[1];
		$get_vars = array();
		foreach ($_GET as $key => $value) {
			if ( $replace_key!==null &&  $replace_value!==null && $key == $replace_key) {
				$value = $replace_value;
				$replaced = true;
			}
			$get_vars[] = $key . '=' . urlencode($value);
		}
		if ( !empty($get_vars) ) {
			$adr .= '?' . implode('&' , $get_vars);
		}
		if ($replace_key!==null &&  $replace_value!==null && !$replaced) {
			if (empty($get_vars)) {
				$adr .= '?' . $replace_key . '=' . urlencode($replace_value);
			} else {
				$adr .= '&' . $replace_key . '=' . urlencode($replace_value);
			}
		}
	} else {
		$adr .= $_SERVER['REQUEST_URI'];	
		if ($replace_key!==null && $replace_value!==null) {
			$adr .= '?' . $replace_key . '=' . $replace_value;
		}
	}
	return $adr;
}

function f_handle_contact_submit() {
	$rules = f_get_contact_rules();
	$validator = new SimpleValidator($rules);
	
    if ($validator->validateRules($_POST)) {
        $subject = get_bloginfo('title') . ': Contact request';
        
        $mailcontent = '<strong>From:</strong> ' . anti_gpc($_POST['ctc-name']) . '<br />';
        if ( !empty($_POST['ctc-website']) ) {
        	$mailcontent .= '<strong>Website:</strong> ' . $_POST['ctc-website'] . '<br />';
        }
        
		$mailcontent .= '<p>' . nl2br($_POST['ctc-comment']) . '</p>';
		
        theme_sendmail($_POST['ctc-email'], $subject, $mailcontent);
    	return null;
	} 
	
	return $validator->getErrors();
}

function f_get_contact_rules() {
	return array(
	    'ctc-name'=>array(
	        'rule'=>'not-empty',
	        'error'=>'Please enter your name.',
	        'not'=>'Name(required)',
	    ),
	    'ctc-email'=>array(
	        'rule'=>'mail',
	        'error'=>'Please enter a valid email address.',
	        'title' => 'Email',
	        'not'=>'Email(not published, required)',
	    ),
	    'ctc-comment'=>array(
	        'rule'=>'not-empty',
	        'error'=>'Please enter your comments.',
	        'title' => 'Comments',
	    )
	);
}

function from_post($key, $default='', $always_default = false){
	if ($always_default) {
		echo $default;
	}elseif ( isset($_POST[$key]) ) {
		echo $_POST[$key];
	} elseif($default != '') {
		echo $default;
	}
}

?>