<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title>
<?php if ( is_home() ) { ?><? bloginfo('name'); ?> | <?php bloginfo('description'); ?><?php } ?>

<?php if ( is_search() ) { ?>Search Results for <?php /* Search Count */ $allsearch = &new WP_Query("s=$s&showposts=-1"); $key = wp_specialchars($s, 1); $count = $allsearch->post_count; _e(''); echo $key; _e(' — '); echo $count . ' '; _e('articles'); wp_reset_query(); ?><?php } ?>

<?php if ( is_404() ) { ?><? bloginfo('name'); ?> | 404 Nothing Found<?php } ?>

<?php if ( is_author() ) { ?><? bloginfo('name'); ?> | Author Archives<?php } ?>

<?php if ( is_single() ) { ?><?php wp_title(''); ?> | <?php $category = get_the_category(); echo $category[0]->cat_name; ?><?php } ?>

<?php if ( is_page() ) { ?><? bloginfo('name'); ?> | <?php wp_title(''); ?><?php } ?>

<?php if ( is_category() ) { ?><?php single_cat_title(); ?> <?php } ?>

<?php if ( is_month() ) { ?><? bloginfo('name'); ?> | Archive | <?php the_time('F, Y'); ?><?php } ?>

<?php if ( is_day() ) { ?><? bloginfo('name'); ?> | Archive | <?php the_time('F j, Y'); ?><?php } ?>

<?php if (function_exists('is_tag')) { if ( is_tag() ) { ?><?php single_tag_title("", true); } } ?> | <? bloginfo('name'); ?>
</title>
<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<?php wp_head(); ?>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/mootools.1.2.1.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/mootools.1.2.1.more.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jquery.jcarousel.pack.js"></script>
<!--[if IE]>
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/style-browser-ie.css" type="text/css" media="screen" />
<![endif]-->
<script type="text/javascript">
//<!--
/* <![CDATA[ */


function hookSearchInput() {
	var input = $('s');
	input.addEvents({
		'focus': function () {
			if (input.value == 'Search') { input.value = ''; }
		},
		'blur': function () {
			if (input.value == '') { input.value = 'Search'; }
		}
	});
}

function setEvenList() {
	var lastchild = $$('div#navmenu-wrapper ul > li:last-child a'); 
	if (null != lastchild) {
		lastchild.each(function(li) {
			li.setStyle('background','transparent none');
		});
	}
	
	var lastchild = $$('div.box-twitter div.interior ul li:last-child span'); 
	if (null != lastchild) {
		lastchild.each(function(li) {
			li.setStyle('background','transparent none');
		});
	}

	var lastchild = $$('div.box div.interior ul > li:last-child'); 
	if (null != lastchild) {
		lastchild.each(function(li) {
			li.setStyle('background','transparent none');
		});
	}
	
}

window.addEvent('domready',function () {
	hookSearchInput();
	setEvenList();

});
/* ]]> */
//-->
</script>

<style type="text/css">

/**
 * Overwrite for having a carousel with dynamic width.
 */
.jcarousel-skin-tango .jcarousel-container-horizontal {
    width: 90%;
}

.jcarousel-skin-tango .jcarousel-clip-horizontal {
    width: 90%;
}

</style>

<script type="text/javascript">
$jquery = jQuery.noConflict();
$jquery().ready(function() {
// validate the comment form when it is submitted
$jquery("#commentform").validate();
});
</script>
<script type="text/javascript">
$jquery1 = jQuery.noConflict();
$jquery1().ready(function() {
    $jquery1('#mycarousel').jcarousel({
        visible: 2
    });
});

</script>
</head>

<body>
<div id="container">

	<div id="nav-container">
    	<div id="nav-container-wrapper">
            <div id="navmenu">	
                <div id="navmenu-wrapper">
                    
                    <ul>
                        <?php themefunction_page_menu(); ?>
                    </ul>                       
                 </div>           				
            </div>

          </div>     	
    </div>

	<div id="title">
		<div id="title-wrapper">
			<div id="sitename">
				<h1><a href="<?php echo get_option('home'); ?>"><?php bloginfo('name'); ?></a></h1>
                <div ><p><?php bloginfo('description'); ?></p></div>				
			</div>      
<div id="category-wrapper">
        <div id="category">
        	<div id="category-menu">
                <ul>
                    <?php themefunction_category_menu(); ?>
                 </ul>
			</div>
        </div>
	  </div>
		</div>

	</div>
	 <?php if (is_home()) : ?>
       <div id="featured">
	      <div id="featured-wrapper">
				<?php require get_theme_root() . '/matiyaga-blue/gallery.php'; ?>

    	  </div>	
      </div>
     </div>
	 <?php endif; ?>
     
	<div id="wrapper">

		
		<div id="wrapper-wrapper">
			