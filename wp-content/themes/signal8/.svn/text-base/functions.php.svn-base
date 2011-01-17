<?php
wp_enqueue_script('jquery');
wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox');

# older wp versions are not defining this function
if (function_exists('automatic_feed_links'))
	automatic_feed_links();

# Right Sidebar
register_sidebar(array(
	'name' => 'Sidebar',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div></div>',
    'before_title' => '<h2 class="widgettitle">',
    'after_title' => '</h2><div class="widgetcontent">',
));

# Footer is registered as sidebar in order to be dynamic
register_sidebar(array(
	'name' => 'Footer',
    'before_widget' => '<div id="%1$s" class="bar grid_4 %2$s"><div class="bg">',
    'after_widget' => '</div></div>',
    'before_title' => '<h2>',
    'after_title' => '</h2>',
));

# some libs
include_once('lib/theme-options/theme-options.php');
include_once('lib/custom-widgets/widgets.php');

# Theme options
$flickr_atom_url = wp_option::factory('text', 'flickr_atom_url');
$flickr_atom_url->help_text('You can find this URL on your Flickr photostream page, by clicking on orange RSS icon in your browser address bar');

$choose_about_page = wp_option_choose_page::factory('choose_page', 'choose_about_page');
$choose_about_page->help_text('This page will be pointed from "View more" link in "About Me" section in the footer');

/* Color schemes */
$default_color_scheme = new color_scheme("Default");
$default_color_scheme->add_colors(array('494740', 'b7b3a2', '0095cc'));

$red_color_scheme = new color_scheme("Red");
$red_color_scheme->add_colors(array('333334', 'd00049', '000'));

$light_color_scheme = new color_scheme("Light");
$light_color_scheme->add_colors(array('c7c7c7', '353535', '575757'));
/* / Color schemes */

# create the option object
$choose_color_scheme = wp_option::factory('choose_color_scheme', 'choose_color_scheme');
# bind color schemes objects to wordpress option object
$choose_color_scheme->add_color_schemes(array(
	$default_color_scheme,
	$red_color_scheme,
	$light_color_scheme,
));
# setup "Default" color scheme to be loaded when the theme is installed
$choose_color_scheme->set_default_value('Default');

# how many featured posts are we showing on index page?
$num_featured_posts = wp_option::factory('select', 'num_featured_posts', "Number of featured posts on home page");
$num_featured_posts->add_options(range(2, 5));

# how many regular posts are we showing on index page?
$index_posts_per_page = wp_option::factory('select', 'index_posts_per_page', 'Number of Sub-Posts on Homepage');
$index_posts_per_page->add_options(range(2, 14, 2));
$index_posts_per_page->set_default_value(10);

$logo = wp_option::factory('image', 'signal_logo', "Logo");
$logo->set_default_value('');
$logo->help_text('Recommended size: 245x80');

$banner_display = wp_option::factory('select', 'signal_banner_display', 'Banner: Display');
$banner_display->set_default_value('Display');
$banner_display->add_options(array('Display', 'Hide'));

$banner_link = wp_option::factory('text', 'signal_banner_link', 'Banner: Link');
$banner_link->set_default_value('http://www.mojo-themes.com/');

$banner_image = wp_option::factory('image', 'signal_banner_image', "Banner: Image");
$banner_image->set_default_value(get_bloginfo('stylesheet_directory') . '/images/ad-468x60.jpg');

$options = new OptionsPage(array(
	$logo,
	$banner_display,
	$banner_link,
	$banner_image,
	$choose_color_scheme,
	wp_option::factory('choose_category', 'featured_posts_category'),
	$num_featured_posts,
	$index_posts_per_page,
	$flickr_atom_url,
	wp_option::factory('text', 'flickr_photostream_url'),
	wp_option::factory('text', 'twitter_URL'),
	wp_option::factory('text', 'facebook_URL'),
	$choose_about_page,
));
$options->attach_to_wp();

/* Hack. This code modifies main wordpress query and makes it to take particular number of posts*/
$paging_modified = false;
add_action('pre_get_posts', 'modify_homepage_query');
function modify_homepage_query($wp_query) {
	global $paging_modified;
	if ($paging_modified || !is_home()) {
		return;
	}
	$paging_modified = true;
	$wp_query->query_vars['posts_per_page'] = get_option('index_posts_per_page', get_option('posts_per_page'));
	return $wp_query;
}

/* ---------------------------------------------- */
/**
 * Returns page object by page slug
 * @param page_name - page slug
 * @return page object
 */
function _get_page_by_name($page_name, $output = OBJECT) { 
    global $wpdb; 
    $page = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'", $page_name )); 
    if ( $page ) 
        return get_page($page, $output); 
    return null; 
}
function get_post_thumbnail($post_id) {
    $images = get_children('post_type=attachment&post_mime_type=image&post_parent=' . $post_id);
    if (!$images) {
    	return null;
    }
    $first_img = array_slice($images, 0, 1);
    return wp_get_attachment_thumb_url($first_img[0]->ID);
}
// For internal usage; Bolds everything between stars. Will not match bold on many lines or nested stars
function do_bolding($plain_text, $bold_tag='strong') {
    return preg_replace('~\*(.*?)\*~', '<' . $bold_tag . '>$1</' . $bold_tag . '>', $plain_text);
}
/**
 * Tries to convert plain text to HTML. Currentlly supported:
 *    In: htmlize("this is *something important*");
 *    Out: "this is <strong>something important</strong>"
 */
function htmlize($plain_text) {
    $html = do_bolding($plain_text);
    return $html;
}

function get_excerpt($text) {
    $pieces = preg_split('~<!--more-->|<span id="more-\d+"></span>~', $text);
    return $pieces[0];
}

/**
 * Shortcut function for acheiving following effect:
 * $no_nav_pages = _get_page_by_name('no-nav-pages');
 * wp_list_pages('sort_column=menu_order&exclude_tree=' . $no_nav_pages->ID);
 * with more simple code:
 * wp_list_pages('sort_column=menu_order&' . exclude_no_nav());
 */
function exclude_no_nav($no_nav_pages_slug='no-nav-pages') {
    $no_nav_page = _get_page_id_by_name($no_nav_pages_slug);
    return "exclude_tree=$no_nav_page";
}
/**
 * Very similiar to _get_page_by_name() but it returns only page ID instead 
 * of whole page object
 */
function _get_page_id_by_name($name) {
    $page = _get_page_by_name($name);
    return $page->ID;
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
	$orig_input = $input;
	
	$input = strip_tags($input);
	
    $words_limit = abs(intval($words_limit));
    if ($words_limit==0) {
        return $orig_input;
    }
    $words = str_word_count($input, STR_WORD_COUNT_FORMAT_ADD_POSITIONS);
    if (count($words)<=$words_limit + 1) {
        return $input;
    }
    $loop_counter = 0;
    foreach ($words as $word_position => $word) {
        $loop_counter++;
        if ($loop_counter==$words_limit + 1) {
            return wpautop(substr($input, 0, $word_position) . '...');
        }
    }
}


function print_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; ?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<div class="comment-body" id="comment-<?php comment_ID(); ?>">
		    <div class="comment-author vcard">
		        <?php echo get_avatar($comment, 70); ?>
		        <p><strong><?php comment_author_link() ?></strong> says,</p>
		    </div>
		    
		    <?php if ($comment->comment_approved == '0') : ?>
		        <em><?php _e('Your comment is awaiting moderation.') ?></em><br />
		    <?php endif; ?>
		    
		    <?php comment_text() ?>
		    <?php edit_comment_link(__('(Edit)'),'  ','') ?>
		    
		    <div class="cl">&nbsp;</div>
			<div class="reply">
				<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
			</div>
			<p class="comment-meta">on <?php comment_date('d F Y') ?> / <?php comment_time('g:i A') ?></p>
			<div class="cl">&nbsp;</div>
		</div>
	<?php
}

/**
 * Prints Naviagtion for signal 8 theme. This theme has categories navigation
 */
function print_navigation() {
	// Exlude uncategorized; get only top level categories
	$cats = get_categories('exclude=1&child_of=0&parent=0&number=6');
	
	$out = '';
	$outer_loop_counter = 1;
	foreach ($cats as $cat) {
		$class = "";
		if ($outer_loop_counter==count($cats)) {
			$class = "last top-last";
		}
		if (is_category()) {
			$link_class = '';
			
			$cat_name = single_cat_title('', false);
			if ($cat_name==$cat->name) {
				$link_class = 'active';
			} else {
				$cat_id = get_cat_id($cat_name);
				$cat_obj = get_category($cat_id);
				if ($cat_obj->parent) {
					$cat_parent = get_category($cat_obj->parent);
					if ($cat_parent->name==$cat->name) {
						$link_class = 'active';
					}
				}
			}
		} else if (is_single()) {
			// 
		}
		
		$out .= '<li class="' . $class . '"><a href="' . get_category_link($cat->term_id) . '" class="' . $link_class . '"><span>' . $cat->name . '</span></a>';
		
		$children = get_categories('child_of=' . $cat->term_id . '&parent=' . $cat->term_id);
		
		if ($children) {
			$out .= "<ul>";
			$inner_loop_counter = 1;
			foreach ($children as $child) {
				$class = "";
				if ($inner_loop_counter==count($children)) {
					$class = "last";
				}
				$out .= '<li class="' . $class . '"><a href="' . get_category_link($child->term_id) . '"><span>' . $child->name . '</span></a>';
				$inner_loop_counter++;
			}
			$out .= "</ul>";
		}
		$out .= '</li>';
		$outer_loop_counter++;
	}
	echo $out;
}
// Gets all featured posts for the home page
function get_featured_posts() {
    $feat_cat = get_option('featured_posts_category');
    
    if (empty($feat_cat)) {
    	return false;
    }
    # exclude pages that does not have featured-image custom field
    $feat_posts = get_posts('category=' . $feat_cat . '&meta_key=featured-image&numberposts=' . get_option('num_featured_posts', 5));
    
    foreach ($feat_posts as $index=>$post) {
    	$feat_img = get_post_meta($post->ID, 'featured-image', true);
    	$feat_posts[$index]->feat_image = $feat_img;
    }
    
    return $feat_posts;
}
function get_recent_comments($limit=5) {
    global $wpdb;
    $res = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT $limit");
    return $res;
}
function get_most_discussed_posts($limit=5) {
	global $wpdb;
    return $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_type='post' ORDER BY comment_count DESC LIMIT $limit");
}
function get_latest_flickr_images($limit=8) {
    $atom_url = get_option('flickr_atom_url');
    $cache_dir = WP_CONTENT_DIR . '/uploads/';
    if (file_exists($cache_dir) && is_writable($cache_dir)) {
    	$cache_file = $cache_dir . md5($atom_url) . ".cache";
    	
    	# how long do we keep cached result from flickr? 5 minutes(300 seconds) sounds reasanble.
	    $cache_lifetime = 300;
	    
	    # dirty caching. This is needed because wordpress requires user to enable wp cache system
	    # for plugins / themes explicitly in wp-settings
	    if (!file_exists($cache_file) || time() - filemtime($cache_file) > $cache_lifetime) {
	    	$res = @file_get_contents($atom_url);
	    	$fp = fopen($cache_file, 'w');
	    	fwrite($fp, $res);
	    	fclose($fp);
	    } else {
	    	$res = file_get_contents($cache_file);
	    }
    } else {
    	$res = file_get_contents($atom_url);
    }
    
    if (!$res) {
    	return array();
    }
    # The theme needs to be compitable with PHP 4, so DOM parsing is not an option here. 
    # SAX parser is too much hasle for this case, so just use 2 regexes to extract needed 
    # information.
    
    preg_match_all('~<entry>(.*?)</entry>~s', $res, $entries);
    $entries = $entries[1];
	
    $flickr_images = array();
    $lc = 1;
    foreach ($entries as $entry) {
    	preg_match('~<link rel="enclosure" type="image/jpeg" href="([^"]*)"~', $entry, $small_image);
    	preg_match('~<link rel="alternate" type="text/html" href="([^"]*)"~', $entry, $flickr_link);
    	preg_match('~<title>(.*?)</title>~', $entry, $title);
    	$flickr_images[] = array(
    		'url'=>$flickr_link[1],
    		'image'=>$small_image[1],
    		'title'=>$title[1],
    	);
    	if ($lc++==$limit) {
    		break;
    	}
    }
    return $flickr_images;
}
function get_theme_color_scheme() {
    $color_scheme = strtolower(get_option('choose_color_scheme'));
    if (empty($color_scheme)) {
    	$color_scheme = 'default';
    }
    return $color_scheme;
}
function shorten_excerpt($has_image, $post_excerpt) {
	$words_limit = $has_image ? 25 : 70;
    return shortalize($post_excerpt, $words_limit);
}
?>