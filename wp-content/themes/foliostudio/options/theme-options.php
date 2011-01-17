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

$logo_image = wp_option::factory('image', 'logo_image');
$logo_image->set_default_value(get_bloginfo('stylesheet_directory') . '/images/logo.png');
$logo_image->set_max_dimensions(array(400, 300));

$show_logo_or_title = wp_option::factory('select', 'show_logo_or_title', 'Use Logo or Site Title');
$show_logo_or_title->add_options(array(
	'use-logo' => 'Use Logo',
	'use-title' => 'Use Site Title'
));
$show_logo_or_title->set_default_value('use-logo');

$color_scheme = wp_option::factory('select', 'color_scheme');
$color_scheme->add_options(array(
	'turquoise' => 'Turquoise',
	'blue' => 'Blue',
	'orangebrown' => 'Orange-Brown',
	'dark' => 'Dark'
));
$color_scheme->set_default_value('turquoise');

$enable_breadcrumbs = wp_option::factory('select', 'enable_breadcrumbs');
$enable_breadcrumbs->add_options(array('yes' => 'Yes', 'no' => 'No'));
$enable_breadcrumbs->set_default_value('yes');

$enable_cufon = wp_option::factory('select', 'enable_cufon');
$enable_cufon->add_options(array('yes' => 'Yes', 'no' => 'No'));
$enable_cufon->set_default_value('yes');

$header_intro_text = wp_option::factory('text', 'header_intro_text', 'Tagline Text');
$header_intro_text->set_default_value('This is where your tagline will be shown');

$enable_intro_text = wp_option::factory('select', 'enable_intro_text', 'Enable Tagline')->add_options(array('y' => 'Enabled', 'n' => 'Disabled'));
$enable_intro_text->set_default_value('y');

$default_teaser = wp_option::factory('text', 'default_teaser_text');
$default_teaser->set_default_value('This is where the teaser text will be shown');

$inner_options = new OptionsPage(array(
	$logo_image,
	$show_logo_or_title,
	$color_scheme,
	$enable_breadcrumbs,
	$enable_cufon,
	$header_intro_text,
	$enable_intro_text,
	$default_teaser,
	wp_option::factory('text', 'footer_text')->set_default_value('Copyright &copy; ' . date('Y') . '. All rights reserved.'),
	wp_option::factory('text', 'google_map_api_key', 'GoogleMaps API Key'),
    wp_option::factory('header_scripts', 'header_script'),
    wp_option::factory('footer_scripts', 'footer_script'),
));
$inner_options->title = 'General';
$inner_options->file = basename(__FILE__);
$inner_options->parent = "theme-options.php";
$inner_options->attach_to_wp();

?>