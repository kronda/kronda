<?php
function attach_main_options_page() {
	$title = "Theme Options";
	add_menu_page(
		$title,
		$title, 
		'edit_themes', 
	    basename(__FILE__),
		create_function('', '')
	);
}
add_action('admin_menu', 'attach_main_options_page');



/* GENERAL OPTIONS */

$choose_color_scheme = wp_option::factory('choose_color_scheme', 'color_theme', 'Color Theme');
$choose_color_scheme->add_color_schemes( get_color_schemes() );
$choose_color_scheme->set_default_value('default');

$inner_logo = wp_option::factory('image', 'logo');
$inner_logo->set_default_value('');
$inner_logo->help_text('Recommended image height: 50px');

$inner_adv_settings = wp_option::factory('set', 'advanced_settings');
$inner_adv_settings->add_choices(array('disable_cufon' => 'Disable Cufon', 'disable_breadcrumbs' => 'Disable Breadcrumbs'));

$inner_teser_text = wp_option::factory('text', 'teaser_text');
$inner_teser_text->set_default_value('We would Love to Hear Form You! Get in Touch with Us Now');

$inner_feedburner = wp_option::factory('textarea', 'feedburner_form', 'Feedburner Code');
$inner_feedburner->set_default_value('');

$inner_ga_code = wp_option::factory('footer_scripts', 'google_analytics_code');
$inner_ga_code->help_text('Google Analytics tracking code');

$inner_footer_script = wp_option::factory('footer_scripts', 'footer_script');
$inner_footer_script->help_text('If you need to add scripts to your footer, you should enter them in this box.');

$inner_options = new OptionsPage(array(
	$inner_logo,
	$choose_color_scheme,
	$inner_adv_settings,
	$inner_teser_text,
	$inner_feedburner,
    $inner_ga_code,
    wp_option::factory('header_scripts', 'header_script'),
    $inner_footer_script,
));
$inner_options->title = 'General';
$inner_options->file = basename(__FILE__);
$inner_options->parent = "theme-options.php";
$inner_options->attach_to_wp();

/* HOMEPAGE OPTIONS */


$homepage_enable_teaser = wp_option::factory('select', 'enable_teaser');
$homepage_enable_teaser->add_options(array('y'=>'Enabled', 'n'=>'Disabled'));
$homepage_enable_teaser->set_default_value('y');

$homepage_transition = wp_option::factory('text', 'transition_speed');
$homepage_transition->set_default_value('3');
$homepage_transition->help_text('(in seconds)');

$homepage_autorotation = wp_option::factory('select', 'enable_autorotation');
$homepage_autorotation->add_options(array('y'=>'Enabled', 'n'=>'Disabled'));
$homepage_autorotation->set_default_value('n');

$homepage_options = new OptionsPage(array(
	$homepage_enable_teaser,
	wp_option::factory('separator', 'slideshow_options'),
	$homepage_transition,
	$homepage_autorotation,
));
$homepage_options->title = 'Homepage';
$homepage_options->file = 'homepage-' . basename(__FILE__);
$homepage_options->parent = "theme-options.php";
$homepage_options->attach_to_wp();

/* SUB HOMEPAGE OPTIONS*/

$hp_options = array();
$numbers = array('one', 'two', 'three');
for ($i=0; $i < 3; $i++) { 
	$hp_options[] = wp_option::factory('separator', 'feature_' . $numbers[$i]);
	$hp_options[] = wp_option::factory('text', 'title_' . $numbers[$i]);
	
	$image = wp_option::factory('image', 'image_' . $numbers[$i]);
	$image->help_text('Recommended image width: 41px');
	$hp_options[] = $image;
	
	$hp_options[] = wp_option::factory('rich_text', 'description_' . $numbers[$i]);
	$hp_options[] = wp_option::factory('text', 'link_' . $numbers[$i]);
}

$sub_homepage_options = new OptionsPage($hp_options);
$sub_homepage_options->title = 'Sub-Features (Homepage)';
$sub_homepage_options->file = 'features-homepage-' . basename(__FILE__);
$sub_homepage_options->parent = "theme-options.php";
$sub_homepage_options->attach_to_wp();

/* FOOTER OPTIONS */
$footer_text = wp_option::factory('rich_text', 'footer_text');
$footer_text->set_default_value('<a href="http://www.mojo-themes.com/wordpress">Business Wordpress Theme</a> by <a href="http://www.mojo-themes.com">MOJO Themes</a>');

$footer_copyright = wp_option::factory('text', 'footer_copyright_text');
$footer_copyright->set_default_value('©2010 MojoThemes. All Rights Reserved.');

$footer_options = new OptionsPage(array(
	$footer_text,
	$footer_copyright,
));
$footer_options->title = 'Footer';
$footer_options->file = 'footer-' . basename(__FILE__);
$footer_options->parent = "theme-options.php";
$footer_options->attach_to_wp();

/* BLOG OPTIONS */
$blog_author_box = wp_option::factory('select', 'enable_author_box');
$blog_author_box->add_options(array('y'=>'Enabled', 'n'=>'Disabled'));
$blog_author_box->set_default_value('y');

$blog_options = new OptionsPage(array(
	$blog_author_box,
));
$blog_options->title = 'Blog';
$blog_options->file = 'blog-' . basename(__FILE__);
$blog_options->parent = "theme-options.php";
$blog_options->attach_to_wp();

/* NAVIGATION OPTIONS */
$navigation_header = wp_option::factory('choose_pages', 'header_navigation');
$navigation_header->create_draggable();

$navigation_options = new OptionsPage(array(
	$navigation_header,
));
$navigation_options->title = 'Navigation';
$navigation_options->file = 'navigation-' . basename(__FILE__);
$navigation_options->parent = "theme-options.php";
$navigation_options->attach_to_wp();

function get_color_schemes () {
	$black_color_scheme = new color_scheme('black', "Black");
	$black_color_scheme->add_colors(array('151515', '333333', '727272'));
	
	$gold_color_scheme = new color_scheme('gold', "Gold");
	$gold_color_scheme->add_colors(array('221f09', '65591d', 'bea839'));
	
	$green_color_scheme = new color_scheme('green', "Green");
	$green_color_scheme->add_colors(array('082111', '1d6438', '36c060'));
	
	$purple_color_scheme = new color_scheme('purple', "Purple");
	$purple_color_scheme->add_colors(array('14101f', '372c54', '5f4c9d'));
	
	$red_color_scheme = new color_scheme('red', "Red");
	$red_color_scheme->add_colors(array('210d09', '64291b', 'bd4a35'));
	
	$default_color_scheme = new color_scheme('default', "Default");
	$default_color_scheme->add_colors(array('121b23', '2f4155', '51729f'));
	
	$color_schemes = array(
			$black_color_scheme,
			$gold_color_scheme,
			$green_color_scheme,
			$purple_color_scheme,
			$red_color_scheme,
			$default_color_scheme,
		);
	return $color_schemes;
}

?>
