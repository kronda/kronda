<?php

$padd_themename = 'Matiyaga-blue';
$padd_shortname = 'matiyaga-blue';
$padd_prefix = 'padd';

if (function_exists('register_sidebar')) {
	register_sidebar(array(
			'name' => 'Side Bar',
			'before_widget' => '<div class="box box-%2$s">',
			'after_widget' => '</div></div>',
			'before_title' => '<h2>',
			'after_title' => '</h2><div class="interior">',
		)
	);
}

require get_theme_root() . '/' . $padd_shortname . '/functions/option.php';
require get_theme_root() . '/' . $padd_shortname . '/functions/advertisement.php';

$options_general = array(
	new Option(
		$padd_prefix . '_twitter_username',
		'Twitter Username',
		'Your <a href="http://twitter.com">Twitter</a> user name. You may leave it blank if you don\'t have one but we recommend 
		to <a href="http://twitter.com/signup">create an account</a>.',
		'textbox',
		'250'
	),
	new Option(
		$padd_prefix . '_delicious_id',
		'Delicious ID',
		'Your <a href="http://www.delicious.com">Delicious</a> ID.',
		'textbox',
		'250'
	),
	new Option(
		$padd_prefix . '_digg_id',
		'Digg ID',
		'Your <a href="http://www.digg.com">Digg</a> ID.',
		'textbox',
		'250'
	),
	new Option(
		$padd_prefix . '_facebook_id',
		'Facebook ID',
		'Your <a href="http://www.facebook.com">Facebook</a> ID.',
		'textbox',
		'250'
	),					
	new Option(
		$padd_prefix . '_stumbleupon_id',
		'StumbleUpon ID',
		'Your <a href="http://www.stumbleupon.com">StumbleUpon</a> ID.',
		'textbox',
		'250'
	),
	new Option(
		$padd_prefix . '_technorati_id',
		'Technorati ID',
		'Your <a href="http://www.technorati.com">Technorati</a> ID.',
		'textbox',
		'250'
	),


	new Option(
		$padd_prefix . '_google_analytics',
		'Google Analytics Code',
		'The code provided by Google Analytics. This is optional, though.',
		'textarea'
	),	
	new Option(
		$padd_prefix . '_youtube_code',
		'YouTube Embed Code',
		'The code provided by YouTube for displaying a video located at the sidebar. In order to fit inside the sidebar, set the 
		<code>&lt;object&gt;</code> and <code>&lt;embed&gt;</code> width to 250 and height to 201. The YouTube provides the smallest possible
		video size is 250 x 201 (without borders).',
		'textarea'
	),
);

$options_gallery = array(
	new Option(
		$padd_prefix . '_featured_slug',
		'Category Slug',
		'The category slug for the featured gallery slide show.',
		'textbox',
		'250'
	),
	new Option(
		$padd_prefix . '_featured_count',
		'Posts To Show',
		'Number of posts to be included in the featured gallery slide show. Value must not be less than 2 or the slideshow will not work.',
		'textbox',
		'250'
	)
);


$options_google = array(
	new Option(
		$padd_prefix . '_ad_468_60',
		'Google Adsense Banner (468x60) Ad Code',
		'This is for the Google Adsense Banner Ad located above every blog entry, search result, and categories.',
		'textarea'
	),
);

$options_yourads = array(
	new Advertisement(
		$padd_prefix . '_banner_250',
		'Sidebar Banner (250x250)',
		'The advertisement found at the sidebar.'
	),
	new Advertisement(
		$padd_prefix . '_sqbtn_1',
		'Square Ad 1 (125x125)',
		'The advertisement found at the top of the rightmost side bar.'
	),
	new Advertisement(
		$padd_prefix . '_sqbtn_2',
		'Square Ad 2 (125x125)',
		'The advertisement found at the top of the rightmost side bar.'
	),
	new Advertisement(
		$padd_prefix . '_sqbtn_3',
		'Square Ad 3 (125x125)',
		'The advertisement found at the top of the rightmost side bar.'
	),
	new Advertisement(
		$padd_prefix . '_sqbtn_4',
		'Square Ad 4 (125x125)',
		'The advertisement found at the top of the rightmost side bar.'
	),
);



function themefunction_add_admin() {
	global $padd_themename, $padd_shortname, $options_general, $options_gallery, $options_google, $options_yourads;
	
	if ( $_GET['page'] == basename(__FILE__) ) {
		if ( 'save' == $_REQUEST['action'] ) {

			foreach ($options_general as $opt) {
				update_option($opt->getKeyword(),$_REQUEST[$opt->getKeyword()]);
			}

			foreach ($options_gallery as $opt) {
				update_option($opt->getKeyword(),stripslashes($_REQUEST[$opt->getKeyword()]));
			}
			
			foreach ($options_google as $opt) {
				update_option($opt->getKeyword(),stripslashes($_REQUEST[$opt->getKeyword()]));
			}

			foreach ($options_yourads as $opt) {
				update_option($opt->getKeyword('img'),$_REQUEST[$opt->getKeyword('img')]);
				update_option($opt->getKeyword('web'),$_REQUEST[$opt->getKeyword('web')]);
			}

			header("Location: themes.php?page=functions.php&saved=true");
			die;
			
		} else if ( 'reset' == $_REQUEST['action'] ) {

			foreach ($options_general as $opt) {
				delete_option($opt->getKeyword());
			}

			foreach ($options_gallery as $opt) {
				delete_option($opt->getKeyword());
			}

			foreach ($options_google as $opt) {
				delete_option($opt->getKeyword());
			}

			foreach ($options_yourads as $opt) {
				delete_option($opt->getKeyword('img'));
				delete_option($opt->getKeyword('web'));
			}
			
			header("Location: themes.php?page=functions.php&reset=true");
			die;
		}
	}
	
	add_theme_page($padd_themename ." Options", $padd_themename . " Options", 'edit_themes', basename(__FILE__), 'themefunction_admin');
}

function themefunction_admin() {
    global $padd_themename, $padd_shortname, $options_general, $options_gallery, $options_google, $options_yourads;

    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$padd_themename.' settings saved.</strong></p></div>';
    if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$padd_themename.' settings reset.</strong></p></div>';
    
	require get_theme_root() . '/' . $padd_shortname . '/functions/userinterface.php';
}

add_action('admin_menu', 'themefunction_add_admin');



/********************************************/
/**** Functions used for hooking filters ****/
/********************************************/

function themefunction_alter_list_pages($string) {
	$string = str_replace(array("\n","\r","\t"),'', $string);

	$pattern = array('/<ul[^<>]*>/','/<\/ul[^<>]*>/');
	$replace = array('','');
	$string = preg_replace($pattern,$replace,$string);
	$pattern = array('/<a[^<>]*>/','/<\/a[^<>]*>/');
	$replace = array('$0<span><span>','</span></span>$0');
	$string = preg_replace($pattern,$replace,$string);

	$string = str_replace(array('</a><li','</li></li>'),array('</a></li><li','</li>'),$string);
	return $string;
}

function themefunction_alter_page_menu($string) {
	$string = themefunction_alter_list_pages($string);
	$pattern = array('/<div[^<>]*>/','/<\/div[^<>]*>/');
	$replace = array('','');
	$string = preg_replace($pattern,$replace,$string);
	return $string;
}

function themefunction_alter_category_menu($string) {
	$string = themefunction_alter_list_pages($string);
	$pattern = array('/<div[^<>]*>/','/<\/div[^<>]*>/');
	$replace = array('<span>$0','$0</span>');
	$string = preg_replace($pattern,$replace,$string);
	return $string;
}

function themefunction_alter_links($string) {
	$pattern = array('/<a[^<>]*>/','/<\/a[^<>]*>/');
	$replace = array('<span>$0','$0</span>');
	$string = preg_replace($pattern,$replace,$string);
	return $string;
}

/***********************************************/
/**** Add filters when necessary  **************/
/***********************************************/

add_filter('wp_list_pages','themefunction_alter_links');
add_filter('wp_list_cats','themefunction_alter_links');
add_filter('wp_list_bookmarks','themefunction_alter_links');
add_filter('get_archives_link','themefunction_alter_links');




/***********************************************/
/**** Functions used for modifying the look ****/
/***********************************************/

function themefunction_page_menu() {
	add_filter('wp_page_menu','themefunction_alter_page_menu');
	wp_page_menu('show_home=1&title_li=');
	remove_filter('wp_page_menu','themefunction_alter_page_menu');
}


function themefunction_category_menu() {
	add_filter('wp_list_categories','themefunction_alter_category_menu');
	wp_list_categories('sort_column=name&optioncount=0&hierarchical=0&list=0&title_li=');
	remove_filter('wp_list_categories','themefunction_alter_page_menu');
}

function themefunction_cleanup($str) {
	global $akpc, $post;
	$show = true;
	$show = apply_filters('akpc_display_popularity', $show, $post);
	if (is_feed() || is_admin_page() || get_post_meta($post->ID, 'hide_popularity', true) || !$show) {
		return $str;
	}
	return $str.'';
}

function themefunction_list_bookmarks() {
	$array = array();
	$array[] = 'category_before=';
	$array[] = 'category_after=';
	$array[] = 'categorize=0';
	$array[] = 'title_li=';
	wp_list_bookmarks(implode('&',$array)); 
}

function themefunction_recent_post() {
	echo '<ul>';
	wp_get_archives('type=postbypost&limit=5');
	echo '</ul>';
}

function themefunction_get_categories($cat_id) {
	if ('' != get_the_category_by_ID($cat_id)) {
		echo '<li>';
		echo '<a href="' . get_category_link($cat_id) . '">' . get_the_category_by_ID($cat_id) . '</a>';
		if ('' != (get_category_children($cat_id))) {
			echo '<ul>';
			wp_list_categories('hide_empty=0&title_li=&child_of=' . $cat_id);
			echo '</ul>';
		}
	echo '</li>';
	}
}

function themefunction_recent_comments($limit=5) {
	global $wpdb, $comments, $comment;

	if ( !$comments = wp_cache_get( 'recent_comments', 'widget' ) ) {
		$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT $limit");
		wp_cache_add( 'recent_comments', $comments, 'widget' );
	}
?>
	<ul id="recentcomments">
	<?php
		if ( $comments ) : foreach ( (array) $comments as $comment) :
			echo  '<li class="recentcomments"><span><span>' . sprintf(__('%1$s on %2$s'), get_comment_author_link(), '<a href="'. get_comment_link($comment->comment_ID) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</span></span></li>';
		endforeach; endif;?>
	</ul>
<?php
}



function themefunction_content($max_char,$more_link_text='(more...)',$stripteaser=0,$more_file='') {
		$content = get_the_content($more_link_text, $stripteaser, $more_file);
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		$content = strip_tags($content);

	if (strlen($content) > $max_char){
		$space = strpos($content," ",$max_char);
	}
	
	if (strlen($_GET['p']) > 0) {
		echo "<p>";
		echo $content;
		echo "&nbsp;<a href='";
		the_permalink();
		echo "'>"."Read More &rarr;</a>";
		echo "</p>";
	} else if ((strlen($content)>$max_char) && $space) {
		$content = substr($content,0,$space);
		$content = $content;
		echo "<p>";
		echo $content;
		echo "...";
		echo "&nbsp;<a href='";
		the_permalink();
		echo "'>".$more_link_text."</a>";
		echo "</p>";
	} 
	else {
		echo "<p>";
		echo $content;
		echo "&nbsp;<a href='";
		the_permalink();
		echo "'>"."Read More &rarr;</a>";
		echo "</p>";
	}
}

function themefunction_comments($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; ?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
		<div class="comment" id="div-comment-<?php comment_ID(); ?>">
			<div class="comment-author vcard">
				<?php echo get_avatar($comment,$size='32',$default='<path_to_url>' ); ?>
				<?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
				<div class="comment-meta commentmetadata">
					<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a> <?php edit_comment_link(__('(Edit)'),'  ','') ?>
				</div>
			</div>
			<?php if ($comment->comment_approved == '0') : ?>
			<em><?php _e('Your comment is awaiting moderation.') ?></em>
			<?php endif; ?>

			<?php comment_text() ?>

			<div class="reply">
				<?php comment_reply_link(array_merge( $args, array('add_below' => 'div-comment', 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
			</div>
		</div>
	<?php
}
function themefunction_imageresizer ($atts, $content=null){
	return '<img src="' . get_theme_root() . '/' . $padd_shortname . '/functions/timthumb.php?src=' . $content . '&amp;w=300&amp;h=250&amp;zc=1" alt="">';
	

	
}
add_shortcode('img', 'themefunction_imageresizer');

function themefunction_catch_that_image() {
	global $post, $posts;
	$first_img = '';
	ob_start();
	ob_end_clean();
	$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
	$first_img = $matches [1] [0];
	
	// no image found display default image instead
	if(empty($first_img)){
	$first_img = get_bloginfo('wpurl') . '/wp-content/themes/matiyaga-blue/images/thumbnail.png';
	}
	return $first_img;
}

function themefunction_getTinyUrl($url) {
    $tinyurl = file_get_contents("http://tinyurl.com/api-create.php?url=".$url);
    return $tinyurl;
}

function themefunction_twitter_parse($feed) {
	$stepOne = explode("<content type=\"html\">", $feed);
	$stepTwo = explode("</content>", $stepOne[1]);
	$tweet = $stepTwo[0];
	$tweet = str_replace("&lt;", "<", $tweet);
	$tweet = str_replace("&gt;", ">", $tweet);
	return $tweet;
}

function themefunction_twitter_paste_links($content) {
	$expression = '/((ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/';
	$content = preg_replace($expression,"<a href=\"$1\">$1</a>",$content);
	$expression = '/[\@]+([A-Za-z0-9-_]+)/';
	$content = preg_replace($expression,"<a href=\"http://twitter.com/$1\">@$1</a>",$content);
	return $content;
}

function themefunction_twitter_get_date($raw) {
	$raw = explode('<published>',$raw);
	$clean = explode('</published>',$raw[1]);
	$date = $clean[0];
	$old_tz = date_default_timezone_get();
	date_default_timezone_set(get_option('timezone_string'));
	$date = date(get_option('time_format') . ' \o\n ' . get_option('date_format') . ' T',strtotime($date));
	date_default_timezone_set($old_tz);
	return $date;
}

function themefunction_twitter_get_recent_entry($user) {
	$feed = 'http://twitter.com/statuses/user_timeline/' . $user . '.atom?count=1';
	$twitterFeed = file_get_contents($feed);
	return themefunction_twitter_parse($twitterFeed);
}

function themefunction_twitter_get_recent_entries($user) {
	$feed = 'http://twitter.com/statuses/user_timeline/' . $user . '.atom?count=3';
	$twitterFeed = file_get_contents($feed);
	$twitterFeed = str_replace("&lt;", "<", $twitterFeed);
	$twitterFeed = str_replace("&gt;", ">", $twitterFeed);
	$clean = explode("<content type=\"html\">", $twitterFeed);
	$amount = count($clean) - 1;

	echo '<ul class="twitter">';

	for ($i = 1; $i <= $amount; $i++) {
		$cleaner = explode("</content>", $clean[$i]);
		$content = html_entity_decode($cleaner[0]);
		$content = str_replace($user . ': ','',$content);
		$content = themefunction_twitter_paste_links($content);
		echo '<li>' . $content . ' &mdash; ' . themefunction_twitter_get_date($cleaner[1]) . '</li>';
	}

	echo '</ul>';
}

?>