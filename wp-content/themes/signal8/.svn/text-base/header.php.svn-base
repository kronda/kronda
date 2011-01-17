<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
<script type="text/javascript" charset="utf-8">
	;(function ($) {
		$(function() {
			$('#main-nav ul li').hover(function() {
				if ($(this).find('ul').length) {
					$(this).find('a:eq(0)').addClass('hover');
				};
				$(this).find('ul').show();
			}, function() {
				$(this).find('a:eq(0)').removeClass('hover');
				$(this).find('ul').hide();
			});
			$('input.field, textarea.field').
		    focus(function() {
		        if(this.title==this.value) {
		            this.value = '';
		        }
		    }).
		    blur(function(){
		        if(this.value=='') {
		            this.value = this.title;
		        }
		    });
		});
	})(jQuery);
</script>
<?php $logo = get_option('signal_logo'); ?>
<?php if ($logo) : ?>
<style type="text/css" media="screen">
	#logo a {
		background: url(<?php echo $logo; ?>) no-repeat 0 0 !important;
	}
</style>
<?php endif; ?>
<!--[if lte IE 6]>
<style type="text/css">
body.default #upperbar .bg { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/upperbar.png', sizingMethod='scale'); 
}
body.default #logo a { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/logo.png', sizingMethod='image'); 
}
body.default #featured .ribbon { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/ribbon-featured.png', sizingMethod='image'); 
}
body.default #footer .bar .bg { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/footer-bar-bg.png', sizingMethod='scale'); 
}
body.red #upperbar .bg { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/red/upperbar.png', sizingMethod='scale'); 
}
body.red #logo a { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/red/logo.png', sizingMethod='image'); 
}
body.red #featured .ribbon { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/red/ribbon-featured.png', sizingMethod='image'); 
}
body.red #footer .bar .bg { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/red/footer-bar-bg.png', sizingMethod='scale'); 
}
body.light #upperbar .bg { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/light/upperbar.png', sizingMethod='scale'); 
}
body.light #logo a { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/light/logo.png', sizingMethod='image'); 
}
body.light #featured .ribbon { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/light/ribbon-featured.png', sizingMethod='image'); 
}
body.light #footer .bar .bg { background-image: none; 
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php bloginfo('stylesheet_directory'); ?>/images/light/footer-bar-bg.png', sizingMethod='scale'); 
}
<?php if ($logo) : ?>
	#logo a {
		background-image: none !important; 
		filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $logo; ?>', sizingMethod='scale') !important; 
	}
<?php endif; ?>
</style>
<![endif]-->
</head>
<body <?php body_class(get_theme_color_scheme()); ?>>
	<!-- Page -->
	<div id="page">
		<!-- Upper Bar -->
		<div id="upperbar">
			<div class="bg">
				<div class="container_16">
					<p class="grid_16">
						<?php $pages = get_pages('sort_column=menu_order&hierarchical=0') ?>
						<?php $loop_counter = 1; foreach ($pages as $_page): ?>
							<a href="<?php echo get_permalink($_page->ID) ?>"><?php echo apply_filters('the_title', $_page->post_title) ?></a>
							<?php if ($loop_counter!=count($pages)): ?>
								<span>|</span>
							<?php endif ?>
						<?php $loop_counter++; endforeach ?>
					</p>
				</div>	
			</div>
		</div>
		<!-- END Upper Bar -->
		
		<!-- Main Part -->
		<div id="main" class="container_16">
			<div id="top" class="grid_16">
				
				<!-- Header -->
				<div id="header">
					<div class="cl">&nbsp;</div>
					<h1 id="logo"><a href="<?php echo get_option('home'); ?>"><?php bloginfo('name'); ?></a></h1>
					<?php if (get_option('signal_banner_display') == 'Display') : ?>
						<div class="ad">
							<a href="<?php echo get_option('signal_banner_link'); ?>"><img src="<?php echo get_option('signal_banner_image'); ?>" alt="" /></a>
						</div>
					<?php endif; ?>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- END Header -->
				
				<!-- Main Navigation -->
				<div id="main-nav">
					<div class="cl">&nbsp;</div>
					<ul>
						<li><a href="<?php echo get_option('home'); ?>" class="<?php echo is_home() ? 'active' : '' ?>"><span>Home</span></a></li>
						<?php print_navigation(); ?>
					</ul>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- END Main Navigation -->
				<script type="text/javascript" charset="utf-8">
					;(function ($) {
						$lc = 0 ;
					    while($('#main-nav').height() > 43) {
					    	$('#main-nav > ul > li:last-child').remove();
					    	if ($lc==1500) {
					    		// Infinite loop
					    		break;
					    	}
					    	$lc++;
					    }
					    	
					})(jQuery);
				</script>
			</div>
			<div class="cl">&nbsp;</div>
				<?php get_sidebar() ?>