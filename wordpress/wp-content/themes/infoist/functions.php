<?php
/**
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 */


if ( ! function_exists( 'infoist_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function infoist_setup() {
		
	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 */
	load_theme_textdomain( 'infoist', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'infoist' ),
	) );

	add_theme_support('post-thumbnails'); 
	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();
	
	
	// custom backgrounds
	$infoist_custom_background = array(
		// Background color default
		'default-color' => 'f7f7f7',
		// Background image default
		'default-image' => get_template_directory_uri() . '/library/images/bg.jpg',
		'wp-head-callback' => '_custom_background_cb'
	);
	add_theme_support('custom-background', $infoist_custom_background );

	
	// adding post format support
	add_theme_support( 'post-formats', 
		array( 
			'aside', /* Typically styled without a title. Similar to a Facebook note update */
			'gallery', /* A gallery of images. Post will likely contain a gallery shortcode and will have image attachments */
			'link', /* A link to another site. Themes may wish to use the first <a href=ÓÓ> tag in the post content as the external link for that post. An alternative approach could be if the post consists only of a URL, then that will be the URL and the title (post_title) will be the name attached to the anchor for it */
			'image', /* A single image. The first <img /> tag in the post could be considered the image. Alternatively, if the post consists only of a URL, that will be the image URL and the title of the post (post_title) will be the title attribute for the image */
			'quote', /* A quotation. Probably will contain a blockquote holding the quote content. Alternatively, the quote may be just the content, with the source/author being the title */
			'status', /*A short status update, similar to a Twitter status update */
			'video', /* A single video. The first <video /> tag or object/embed in the post content could be considered the video. Alternatively, if the post consists only of a URL, that will be the video URL. May also contain the video as an attachment to the post, if video support is enabled on the blog (like via a plugin) */
			'audio', /* An audio file. Could be used for Podcasting */
			'chat' /* A chat transcript */
		)
	);
}
endif;
add_action( 'after_setup_theme', 'infoist_setup' );

/**
 * Set the content width based on the theme's design and stylesheet.
 */
function infoist_content_width() {
	global $content_width;
	if (!isset($content_width))
		$content_width = 550; /* pixels */
}
add_action( 'after_setup_theme', 'infoist_content_width' );


/**
 * Admin options page for the social media buttons
 */
function infoist_theme_menu() {  
    add_theme_page(  
        'Infoist Theme Options', 	// The title to be displayed in the browser window for this page.  
        'Social Media Options',            		// The text to be displayed for this menu item  
        'administrator',            			// Which type of users can see this menu item  
        'infoist_theme_options',    			// The unique ID - that is, the slug - for this menu item  
        'infoist_theme_display'     			// The name of the function to call when rendering this menu's page  
    );  
} 
add_action('admin_menu', 'infoist_theme_menu'); 

function infoist_theme_intialize_social_options() {   
    if( false == get_option( 'infoist_theme_social_options' ) ) {  
        add_option( 'infoist_theme_social_options' );  
    } 
	
	add_settings_section(  
		'social_settings_section',          // ID used to identify this section and with which to register options  
		'Social Media Buttons',             // Title to be displayed on the administration page  
		'infoist_social_options_callback',  // Callback used to render the description of the section  
		'infoist_theme_social_options'      // Page on which to add this section of options  
	);

	add_settings_field(  
        'facebook',  
        'Facebook',  
        'infoist_facebook_callback',  
        'infoist_theme_social_options',  
        'social_settings_section'  
    );
	
	add_settings_field(  
		'twitter',  
		'Twitter',  
		'infoist_twitter_callback',  
		'infoist_theme_social_options',  
		'social_settings_section'  
	);  
	
    add_settings_field(  
        'googleplus',  
        'Google+',  
        'infoist_googleplus_callback',  
        'infoist_theme_social_options',  
        'social_settings_section'  
    ); 
	
	add_settings_field(  
        'pinterest',  
        'Pinterest',  
        'infoist_pinterest_callback',  
        'infoist_theme_social_options',  
        'social_settings_section'  
    ); 
	
	add_settings_field(  
        'linkedin',  
        'LinkedIn',  
        'infoist_linkedin_callback',  
        'infoist_theme_social_options',  
        'social_settings_section'  
    ); 
	
	register_setting(  
		'infoist_theme_social_options',  
		'infoist_theme_social_options',  
		'infoist_theme_sanitize_social_options'  
	);
} 
add_action( 'admin_init', 'infoist_theme_intialize_social_options' ); 

function infoist_social_options_callback() {
	_e('<p>Provide the URL to the social networks you\'d like to display. Leave it blank if you don\'t want to display the button.</p>', 'infoist');
} 

function infoist_facebook_callback() {  
    // First, we read the social options collection  
    $options = get_option( 'infoist_theme_social_options' );  
    // Next, we need to make sure the element is defined in the options. If not, we'll set an empty string.  
    $url = ''; 
    if( isset( $options['facebook'] ) ) { 
        $url = $options['facebook']; 
    } // end if 
    // Render the output 
    echo '<input type="text" id="facebook" name="infoist_theme_social_options[facebook]" value="' . $options['facebook'] . '" />';  
}


function infoist_twitter_callback() {  
    // First, we read the social options collection  
    $options = get_option( 'infoist_theme_social_options' );  
    // Next, we need to make sure the element is defined in the options. If not, we'll set an empty string.  
    $url = ''; 
    if( isset( $options['twitter'] ) ) { 
        $url = $options['twitter']; 
    } // end if 
    // Render the output 
    echo '<input type="text" id="twitter" name="infoist_theme_social_options[twitter]" value="' . $options['twitter'] . '" />';  
}

function infoist_googleplus_callback() {  
    // First, we read the social options collection  
    $options = get_option( 'infoist_theme_social_options' );  
    // Next, we need to make sure the element is defined in the options. If not, we'll set an empty string.  
    $url = ''; 
    if( isset( $options['googleplus'] ) ) { 
        $url = $options['googleplus']; 
    } // end if 
    // Render the output 
    echo '<input type="text" id="googleplus" name="infoist_theme_social_options[googleplus]" value="' . $options['googleplus'] . '" />';  
}

function infoist_pinterest_callback() {  
    // First, we read the social options collection  
    $options = get_option( 'infoist_theme_social_options' );  
    // Next, we need to make sure the element is defined in the options. If not, we'll set an empty string.  
    $url = ''; 
    if( isset( $options['pinterest'] ) ) { 
        $url = $options['pinterest']; 
    } // end if 
    // Render the output 
    echo '<input type="text" id="pinterest" name="infoist_theme_social_options[pinterest]" value="' . $options['pinterest'] . '" />';  
}

function infoist_linkedin_callback() {  
    // First, we read the social options collection  
    $options = get_option( 'infoist_theme_social_options' );  
    // Next, we need to make sure the element is defined in the options. If not, we'll set an empty string.  
    $url = ''; 
    if( isset( $options['linkedin'] ) ) { 
        $url = $options['linkedin']; 
    } // end if 
    // Render the output 
    echo '<input type="text" id="linkedin" name="infoist_theme_social_options[linkedin]" value="' . $options['linkedin'] . '" />';  
}

function infoist_theme_sanitize_social_options( $input ) {  
    // Define the array for the updated options  
    $output = array();  
    // Loop through each of the options sanitizing the data  
    foreach( $input as $key => $val ) {  
        if( isset ( $input[$key] ) ) {  
            $output[$key] = esc_url_raw( strip_tags( stripslashes( $input[$key] ) ) );  
        } // end if  
    } // end foreach  
    // Return the new collection  
    return apply_filters( 'infoist_theme_sanitize_social_options', $output, $input );  
}


function infoist_theme_display() {  
?>  
	<!-- Create a header in the default WordPress 'wrap' container -->  
	<div class="wrap">  
		<!-- Add the icon to the page -->  
		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e('Infoist Theme Options', 'infoist'); ?></h2>  
		<!-- Make a call to the WordPress function for rendering errors when settings are saved. -->  
		<?php settings_errors(); ?>  
		<!-- Create the form that will be used to render our options -->  
		<form method="post" action="options.php">  
			    <?php settings_fields( 'infoist_theme_social_options' ); ?>  
    			<?php do_settings_sections( 'infoist_theme_social_options' ); ?>   
			<?php submit_button(); ?>  
		</form>  
	</div><!-- /.wrap -->  
<?php  
} // end infoist_theme_display  


/**
 * Title filter 
 */
function infoist_filter_wp_title( $old_title, $sep, $sep_location ){

	$site_name = get_bloginfo( 'name' );
	$site_description = get_bloginfo( 'description' );
	// add padding to the sep
	$ssep = ' ' . $sep . ' ';
	
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		return $site_name . ' | ' . $site_description;
	} else {
		// find the type of index page this is
		if( is_category() ) $insert = $ssep . __( 'Category', 'infoist' );
		elseif( is_tag() ) $insert = $ssep . __( 'Tag', 'infoist' );
		elseif( is_author() ) $insert = $ssep . __( 'Author', 'infoist' );
		elseif( is_year() || is_month() || is_day() ) $insert = $ssep . __( 'Archives', 'infoist' );
		else $insert = NULL;
		 
		// get the page number we're on (index)
		if( get_query_var( 'paged' ) )
		$num = $ssep . __( 'Page ', 'infoist' ) . get_query_var( 'paged' );
		 
		// get the page number we're on (multipage post)
		elseif( get_query_var( 'page' ) )
		$num = $ssep . __( 'Page ', 'infoist' ) . get_query_var( 'page' );
		 
		// else
		else $num = NULL;
		 
		// concoct and return new title
		return $site_name . $insert . $old_title . $num;
		
	}

}

// call our custom wp_title filter, with normal (10) priority, and 3 args
add_filter( 'wp_title', 'infoist_filter_wp_title', 10, 3 );


/**
* Filter the RSS Feed Site Title
*/
function infoist_blogname_rss( $val, $show ) {
    if( 'name' == $show )
        $out = '';
    else
        $out = $val;

    return $out;
}
add_filter('bloginfo_rss', 'infoist_blogname_rss', 10, 2);




/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 */
if ( ! function_exists( 'infoist_main_nav' ) ) :
function infoist_main_nav() {
	// display the wp3 menu if available
    wp_nav_menu( 
    	array( 
    		'menu' => '', /* menu name */
    		'theme_location' => 'primary', /* where in the theme it's assigned */
    		'container_class' => 'menu', /* container class */
    		'fallback_cb' => 'infoist_main_nav_fallback' /* menu fallback */
    	)
    );
}
endif;

if ( ! function_exists( 'infoist_main_nav_fallback' ) ) :
	function infoist_main_nav_fallback() { wp_page_menu( 'show_home=Home&menu_class=menu' ); }
endif;


function infoist_enqueue_comment_reply() {
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
                wp_enqueue_script( 'comment-reply' );
        }
 }
add_action( 'comment_form_before', 'infoist_enqueue_comment_reply' );


function infoist_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'infoist_page_menu_args' );

/**
 * Register widgetized area and update sidebar with default widgets
 */
function infoist_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Sidebar Right', 'infoist' ),
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	) );

}
add_action( 'widgets_init', 'infoist_widgets_init' );

if ( ! function_exists( 'infoist_content_nav' ) ):
/**
 * Display navigation to next/previous pages when applicable
 */
function infoist_content_nav( $nav_id ) {
	global $wp_query;

	?>
	<nav id="<?php echo $nav_id; ?>">
		<h1 class="assistive-text section-heading"><?php _e( 'Post navigation', 'infoist' ); ?></h1>

	<?php if ( is_single() ) : // navigation links for single posts ?>

		<?php previous_post_link( '<div class="nav-previous">%link</div>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'infoist' ) . '</span> Previous' ); ?>
		<?php next_post_link( '<div class="nav-next">%link</div>', 'Next <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'infoist' ) . '</span>' ); ?>

	<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

		<?php if ( get_next_posts_link() ) : ?>
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'infoist' ) ); ?></div>
		<?php endif; ?>

		<?php if ( get_previous_posts_link() ) : ?>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'infoist' ) ); ?></div>
		<?php endif; ?>

	<?php endif; ?>

	</nav><!-- #<?php echo $nav_id; ?> -->
	<?php
}
endif;


if ( ! function_exists( 'infoist_comment' ) ) :
/**
 * Template for comments and pingbacks.
 */
function infoist_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'infoist' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'infoist' ), ' ' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>">
			<footer class="clearfix comment-head">
				<div class="comment-author vcard">
					<?php echo get_avatar( $comment, 60 ); ?>
					<?php printf( __( '%s', 'infoist' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
				</div><!-- .comment-author .vcard -->
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em><?php _e( 'Your comment is awaiting moderation.', 'infoist' ); ?></em>
					<br />
				<?php endif; ?>

				<div class="comment-meta commentmetadata">
					<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><time pubdate datetime="<?php comment_time( 'c' ); ?>">
					<?php
						/* translators: 1: date, 2: time */
						printf( __( '%1$s at %2$s', 'infoist' ), get_comment_date(), get_comment_time() ); ?>
					</time></a>
					<?php edit_comment_link( __( '(Edit)', 'infoist' ), ' ' );
					?>
				</div><!-- .comment-meta .commentmetadata -->
			</footer>

			<div class="comment-content"><?php comment_text(); ?></div>

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->

	<?php
			break;
	endswitch;
}
endif;

if ( ! function_exists( 'infoist_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function infoist_posted_on() {
	printf( __( '<span class="sep">Posted by </span> <span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span></span>', 'infoist' ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'infoist' ), get_the_author() ) ),
		esc_html( get_the_author() )
	);
}
endif;

/**
 * Adds custom classes to the array of body classes.
 */
function infoist_body_classes( $classes ) {
	// Adds a class of single-author to blogs with only 1 published author
	if ( ! is_multi_author() ) {
		$classes[] = 'single-author';
	}

	return $classes;
}
add_filter( 'body_class', 'infoist_body_classes' );

/**
 * Returns true if a blog has more than 1 category
 */
function infoist_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so infoist_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so infoist_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in infoist_categorized_blog
 */
function infoist_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'infoist_category_transient_flusher' );
add_action( 'save_post', 'infoist_category_transient_flusher' );

/**
 * Filter in a link to a content ID attribute for the next/previous image links on image attachment pages
 */
function infoist_enhanced_image_navigation( $url ) {
	global $post, $wp_rewrite;

	$id = (int) $post->ID;
	$object = get_post( $id );
	if ( wp_attachment_is_image( $post->ID ) && ( $wp_rewrite->using_permalinks() && ( $object->post_parent > 0 ) && ( $object->post_parent != $id ) ) )
		$url = $url . '#main';

	return $url;
}
add_filter( 'attachment_link', 'infoist_enhanced_image_navigation' );


if ( ! function_exists( 'infoist_pagination' ) ) :
function infoist_pagination($pages = '', $range = 4)
{
     $showitems = ($range * 2)+1; 
 
     global $paged;
     if(empty($paged)) $paged = 1;
 
     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages)
         {
             $pages = 1;
         }
     }  
 
     if(1 != $pages)
     {
         printf( __( '<div class="pagination"><span>Page %1$s of %2$s</span>', 'infoist'), $paged, $pages );
         if($paged > 2 && $paged > $range+1 && $showitems < $pages) printf( __( '<a href="%1$s">&laquo; First</a>', 'infoist' ), get_pagenum_link(1) );
         if($paged > 1 && $showitems < $pages) printf( __( '<a href="%1$s">&lsaquo; Previous</a>', 'infoist' ), get_pagenum_link($paged - 1) );
 
         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? "<span class=\"current\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
             }
         }
 
         if ($paged < $pages && $showitems < $pages) printf( __( '<a href="%1$s">Next &rsaquo;</a>', 'infoist' ), get_pagenum_link($paged + 1) );
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) printf( __( '<a href="%1$s">Last &raquo;</a>', 'infoist' ), get_pagenum_link($pages) );
         echo "</div>\n";
     }
}
endif;

function infoist_w3c_valid_rel( $text ) {
	$text = str_replace('rel="category tag"', 'rel="tag"', $text); return $text; 
}
add_filter( 'the_category', 'infoist_w3c_valid_rel' );


function infoist_modernizr_addclass($output) {
    return $output . ' class="no-js"';
}
add_filter('language_attributes', 'infoist_modernizr_addclass');


function infoist_modernizr_script() {
    wp_enqueue_script( 'modernizr', get_template_directory_uri() . '/library/js/modernizr-2.6.1.min.js', false, '2.6.1');
}    
add_action('wp_enqueue_scripts', 'infoist_modernizr_script');


function infoist_custom_scripts() {
	wp_enqueue_script( 'infoist_custom_js', get_template_directory_uri() . '/library/js/scripts.js', array( 'jquery' ), '1.0.0' );
	wp_enqueue_style('infoist_style', get_stylesheet_uri() );
}
add_action('wp_enqueue_scripts', 'infoist_custom_scripts');

?>