<?php

/* HOMEPAGE OPTIONS */

$homepage_options = new OptionsPage(array(
    wp_option::factory('select', 'slideshow_view_mode')->add_options(array('gallery' => 'Gallery', 'slider' => 'Slider')),
    wp_option::factory('select', 'autorotation', 'Auto Rotation')->add_options(array('y' => 'Enabled', 'n' => 'Disabled')),
	wp_option::factory('text', 'rotation_interval')->set_default_value('8000')->help_text('The next image will be displayed after that time (in miliseconds, 1000ms = 1s).'), //in miliseconds
	wp_option::factory('text', 'transition_speed')->set_default_value('500')->help_text('The time it takes for the new image to be displayed (in miliseconds)'), //in miliseconds
	wp_option::factory('separator', 'sub_features'),
	wp_option::factory('select', 'get_started', '"Get Started" field')->add_options(array('y' => 'Enabled', 'n' => 'Disabled')),
	wp_option::factory('text', 'get_started_text', '"Get Started" text')->set_default_value('Do you still need more reasons to get started?'),
	wp_option::factory('text', 'get_started_url', '"Get Started" destination link')->set_default_value('#'),
	wp_option::factory('separator', 'homepage_areas'),
	wp_option::factory('select', 'homepage_areas_count')->add_options(array('1' => 'One', '2' => 'Two', '3' => 'Three')),
	wp_option::factory('image', 'homepage_area_1_bullet', 'Area 1 bullet')->set_default_value(get_bloginfo('stylesheet_directory') . '/color-schemes/turquoise/images/main-text-block-1.gif')->help_text('Recommended Size: 30x45'),
	wp_option::factory('rich_text', 'homepage_area_1', 'Area 1 text')->set_default_value('<p><strong>Welcome to turquoise</strong>, Sed perspiciatis unde omnisistenat error sit voluptatemaccusanti doloremque laudantium, totare aperiam, eaque ipsa quae.</p>'),
	wp_option::factory('image', 'homepage_area_2_bullet', 'Area 2 bullet')->set_default_value(get_bloginfo('stylesheet_directory') . '/color-schemes/turquoise/images/main-text-block-2.gif')->help_text('Recommended Size: 30x45'),
	wp_option::factory('rich_text', 'homepage_area_2', 'Area 2 text')->set_default_value('<p><strong>Neque porro quisquam estqui</strong> dolorem ipsum quia dolor sit met, consectetur, adipisci velit, seduol quia non numquam eius modifiqe <a href="#">tempora incidunt</a>.</p>'),
	wp_option::factory('image', 'homepage_area_3_bullet', 'Area 3 bullet')->set_default_value(get_bloginfo('stylesheet_directory') . '/color-schemes/turquoise/images/main-text-block-3.gif')->help_text('Recommended Size: 30x45'),
	wp_option::factory('rich_text', 'homepage_area_3', 'Area 3 text')->set_default_value('<p><strong>Temporibus utem qibusdam</strong> et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae <a href="#">nonrecsande</a>.</p>'),
));
$homepage_options->title = 'Homepage';
$homepage_options->file = basename(__FILE__);
$homepage_options->parent = "theme-options.php";
$homepage_options->attach_to_wp();

$homepage_options->add_script('
	jQuery(function($) {
		$(".field-homepage_areas_count select").change(function() {
			 change_box_count();
		});
	 	change_box_count();
		function change_box_count() {
			switch($(".field-homepage_areas_count select").val()) {
				case "1":
					$(".field-homepage_area_1_bullet, .field-homepage_area_1").show();
					$(".field-homepage_area_2_bullet, .field-homepage_area_2, .field-homepage_area_3, .field-homepage_area_3_bullet").hide();
				break;
				case "2":
					$(".field-homepage_area_1_bullet, .field-homepage_area_1_bullet, .field-homepage_area_2, .field-homepage_area_2_bullet").show();
					$(".field-homepage_area_3, .field-homepage_area_3_bullet").hide();
				break;
				case "3":
					$(".field-homepage_area_1_bullet, .field-homepage_area_1_bullet, .field-homepage_area_2, .field-homepage_area_2_bullet, .field-homepage_area_3, .field-homepage_area_3_bullet").show();
				break;
			}
		}
	})
');

?>