<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<link rel="shortcut icon" type="image/x-icon" href="<?php bloginfo('stylesheet_directory'); ?>/images/favicon.ico" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/style.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/themes/<?php echo get_option('color_theme', 'default'); ?>.css" type="text/css" media="all" />
	<?php //Translates the css/images/ path in the ie6 png fixes. IE6 style name should be ie6.css ?>
		<!--[if IE 6]>
		<style type="text/css" media="screen">
			<?php include_once('ie6.php'); ?>
		</style>
		<![endif]-->		
		<!--[if IE 7]>
		<style type="text/css" media="screen">
			.wp-pagenavi .nextpostslink { top:-7px; }
		</style>
		<![endif]-->
	<?php  ?>
	<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
	<script src="<?php bloginfo('stylesheet_directory'); ?>/js/jquery.jcarousel.pack.js" type="text/javascript"></script>
	
<?php if ( !in_array('disable_cufon', get_option('advanced_settings', array()) ) ): ?>
	<script src="<?php bloginfo('stylesheet_directory'); ?>/js/cufon-yui.js" type="text/javascript"></script>
	<script src="<?php bloginfo('stylesheet_directory'); ?>/js/Foundry_Monoline_Light_200.font.js" type="text/javascript"></script>
	<script src="<?php bloginfo('stylesheet_directory'); ?>/js/Foundry_Monoline_UltraLight_200.font.js" type="text/javascript"></script>
	<script src="<?php bloginfo('stylesheet_directory'); ?>/js/Foundry_Monoline_Bold_700.font.js" type="text/javascript"></script>
	<script type="text/javascript">
		Cufon.replace('#slogan, .slider-description h3', { fontFamily: "Foundry Monoline UltraLight" } );
		Cufon.replace('#features h3', { fontFamily: "Foundry Monoline Bold" } );
		Cufon.replace('#bottom h3, #tagline', { fontFamily: "Foundry Monoline Light" } );
		Cufon.replace('#blog h2, #sidebar h3, .related-posts h3', { fontFamily: "Foundry Monoline Bold"} );
		Cufon.replace('.post h3,.recent-posts h4, .related-posts h4', { fontFamily: "Foundry Monoline Medium" } );
	</script>
<?php endif ?>
	<?php
		$autorotation = get_option('enable_autorotation') == 'y';
		$speed = get_option('transition_speed', '0');
		if ( empty($speed) ) { $autorotation = false; $speed="0"; }
	?>
	<script type="text/javascript" charset="utf-8">
		var autorotation = <?php if($autorotation) { echo'true';} else {echo'false';} ?>;
		var speed = <?php echo $speed ?>*1000;
	</script>
	<script src="<?php bloginfo('stylesheet_directory'); ?>/js/jquery-func.js" type="text/javascript"></script>
	
	<?php  
	$settings = get_option('advanced_settings');
	?>
</head>
<body id="<?php echo get_option('color_theme'); ?>" <?php body_class(); ?>>
	<div id="header" <?php if( apollo_is_template($post->ID, 'home-page.php') ) { echo 'class="header-long"';} ?>>
		<div class="shell">
			<div class="header-image notext">&nbsp;</div>
			<h1 id="logo" class="fl notext">
				<?php $logo = get_option('logo') ?>
				<a href="<?php bloginfo('url'); ?>" <?php if( !empty($logo) ) { echo 'style="background-image: url(\'' . $logo . '\');"'; } ?>><?php bloginfo('name'); ?></a>
			</h1>
			<div id="navigation">
				<ul>
					<?php apollo_print_nav() ?>
				</ul>
			</div>
			<div class="cl">&nbsp;</div>
			