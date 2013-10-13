<?php
add_action('init', 'maxbuttons_add_button_to_tinymce');
function maxbuttons_add_button_to_tinymce() {
	if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
		return;
	}
	
	// Add only in rich editor mode (the Visual tab)
	if (get_user_option('rich_editing') == 'true') {
		add_filter('mce_external_plugins', 'maxbuttons_mce_external_plugins');
		add_filter('mce_buttons', 'maxbuttons_mce_buttons');
	}
}

function maxbuttons_mce_external_plugins($plugin_array) {
	$plugin_array['MaxButtons'] = MAXBUTTONS_PLUGIN_URL . '/tinymce/plugin.js';
	return $plugin_array;
}

function maxbuttons_mce_buttons($buttons) {
	array_push($buttons, '|', 'MaxButtons');
	return $buttons;
}
?>