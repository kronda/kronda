<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<title><?php wp_title('|', true, 'left'); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />


<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <nav id="access" role="navigation">
        <h1 class="assistive-text section-heading"><?php _e( 'Main menu', 'infoist' ); ?></h1>
        <div class="skip-link screen-reader-text"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'infoist' ); ?>"><?php _e( 'Skip to content', 'infoist' ); ?></a></div>
        <div class="inner-nav">
        <?php infoist_main_nav(); // Adjust using Menus in Wordpress Admin ?>
        </div>
    </nav><!-- #access -->

	
    
	<div id="container">
	<?php do_action( 'before' ); ?>
    
    <header id="branding" role="banner">
      <div id="inner-header" class="clearfix">
		<hgroup id="site-heading">
			<h1 id="site-title"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<h2 id="site-description"><?php bloginfo( 'description' ); ?></h2>
			<?php get_search_form(); ?>
		</hgroup>
      </div>
	</header><!-- #branding -->