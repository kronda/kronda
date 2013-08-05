<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?><!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width; initial-scale=1.0" />
<meta name="viewport" content="initial-scale=1, maximum-scale=1">
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyeleven' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_directory'); ?>/css/flexslider.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_directory'); ?>/css/more.css" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<?php
	/* We add some JavaScript to pages with the comment form
	 * to support sites with threaded comments (when in use).
	 */
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head();
?>
</head>

<body <?php body_class(); ?>>
<div id="wrapper"> <!-- Sad panda had to wrap the page to not break rest of wp site -->

<div id="page" class="hfeed">
	<header id="branding" role="banner">
	 
	  	<nav id="access" role="navigation">
				<h3 class="assistive-text"><?php _e( 'Main menu', 'twentyeleven' ); ?></h3>
				<?php /*  Allow screen readers / text browsers to skip the navigation menu and get right to the good stuff. */ ?>
				<div class="skip-link"><a class="assistive-text" href="#content" title="<?php esc_attr_e( 'Skip to primary content', 'twentyeleven' ); ?>"><?php _e( 'Skip to primary content', 'twentyeleven' ); ?></a></div>
				<div class="skip-link"><a class="assistive-text" href="#secondary" title="<?php esc_attr_e( 'Skip to secondary content', 'twentyeleven' ); ?>"><?php _e( 'Skip to secondary content', 'twentyeleven' ); ?></a></div>
				<?php /* Our navigation menu.  If one isn't filled out, wp_nav_menu falls back to wp_page_menu. The menu assiged to the primary position is the one used. If none is assigned, the menu with the lowest ID is used. */ ?>
				<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
				
				<div id="logo">
					<p><span>{</span>Kronda Adair<span>}</span></p>					
				</div><!-- logo -->
				
				<?php
					// Has the text been hidden?
					if ( 'blank' == get_header_textcolor() ) :
				?>
					<div class="only-search<?php if ( ! empty( $header_image ) ) : ?> with-image<?php endif; ?>">
					<?php get_search_form(); ?>
					</div>
				<?php
					else :
				?>
					<?php get_search_form(); ?>
				<?php endif; ?>
			</nav><!-- #access -->
	  	
			<div id="masthead">
				
		    <!-- custom header image -->
  			<div id="header_img">
  				<?php if ( is_front_page() ): ?>
  				<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/me_bigger.png"  width="380" height="231" alt="Me, my laptop and my favorite cargo bike, geeking out on the go" title="Photo by Jakob Ferrier">
  			</div><!-- header_img -->
  			
  			<!-- insert custom header element -->
  			<div id="object-container">
  				<ul id="object">
  					<li class="first">
  						<span class="label">Name:</span>
  						<span>Kronda Adair</span>;
  					</li>

  					<li id="title">
  						<span class="label">Title:</span>
  						<span>Web Developer</span>;
  					</li>

  					<li id="work">
  						<span class="label">Work:</span>
  						<span id="worksub">
								<span>Creating standards based</span>
								<span>websites using HTML,</span> 
											CSS &amp; Javascript
								<span id="semicolon">;</span>
							</span>
  					</li>

  					<li id="play">
  						<span class="label">Play:</span>
  						<span> Ride. Read. Write.</span>;
  					</li>
  				</ul>
  			</div><!-- object-container -->
        <?php endif ?>
			
			<hgroup>
				<h1 id="site-title"><span><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></span></h1>
				<h2 id="site-description"><?php bloginfo( 'description' ); ?></h2>
			</hgroup>

			<?php
				//check if this is the front page before showing the normal header img
				if ( !is_front_page() ) :
				
				// Check to see if the header image has been removed
				$header_image = get_header_image();
				if ( ! empty( $header_image ) ) :
			?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php
					// The header image
					// Check if this is a post or page, if it has a thumbnail, and if it's a big one
					if ( is_singular() &&
							has_post_thumbnail( $post->ID ) &&
							( /* $src, $width, $height */ $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( HEADER_IMAGE_WIDTH, HEADER_IMAGE_WIDTH ) ) ) &&
							$image[1] >= HEADER_IMAGE_WIDTH ) :
						// Houston, we have a new header image!
						echo get_the_post_thumbnail( $post->ID, 'post-thumbnail' );
					else : ?>
					<img src="<?php header_image(); ?>" width="<?php echo HEADER_IMAGE_WIDTH; ?>" height="<?php echo HEADER_IMAGE_HEIGHT; ?>" alt="" />
				<?php endif; // end check for featured image or standard header ?>
			</a>
			<?php endif; // end check for removed header image ?>
      <?php endif; // end check for front page ?> 
      
			</div><!-- masthead -->
	</header><!-- #branding -->


	<div id="main">