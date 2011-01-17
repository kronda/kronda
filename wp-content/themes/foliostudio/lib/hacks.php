<?php
# this file contains functions that are using direct SQL queries instead of 
# WP api functions.

/**
 * Gets all pages/posts which have the specified custom field. Does not check wheather it has any value - just if it has the custom field
 * Return empty array if no pages/posts have been found
 */
function _get_content_by_meta_key($meta_key) {
	global $wpdb;
	$result = $wpdb->get_col('
		SELECT DISTINCT(post_id)
		FROM ' . $wpdb->postmeta . '
		WHERE meta_key = "' . $meta_key . '"
	');
	if(empty($result)) {
	    return array();
	}
	return $result;
}

/*
 * Get the mysql row for the previous post
 * $current_post_id = the current $post->ID
 * $exclude_categories_string = comma separated list of categories the previous post shouldn't be a child of
 * Note that to fully exclude a category you should add all of it's child categories' IDs to the exclude string
 */
function _get_previous_post($current_post_id, $exclude_categories_string) {
	global $wpdb;
	$return = $wpdb->get_row('
		SELECT DISTINCT(p.ID), p.*
		FROM ' . $wpdb->posts . ' p
		INNER JOIN ' . $wpdb->term_relationships . ' tr ON tr.object_id = p.ID
		INNER JOIN ' . $wpdb->term_taxonomy . ' tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
		INNER JOIN ' . $wpdb->terms . ' t ON t.term_id = tt.term_id
		WHERE tt.taxonomy = "category"
		AND tt.term_id NOT IN (' . $exclude_categories_string . ')
		AND p.post_type = "post"
		AND p.ID < "' . $current_post_id . '" ORDER BY p.ID DESC
	');
	return $return;
}

/*
* Same as above but takes the next post instead
*/
function _get_next_post($current_post_id, $exclude_categories_string) {
	global $wpdb;
	$return = $wpdb->get_row('
		SELECT DISTINCT(p.ID), p.*
		FROM ' . $wpdb->posts . ' p
		INNER JOIN ' . $wpdb->term_relationships . ' tr ON tr.object_id = p.ID
		INNER JOIN ' . $wpdb->term_taxonomy . ' tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
		INNER JOIN ' . $wpdb->terms . ' t ON t.term_id = tt.term_id
		WHERE tt.taxonomy = "category"
		AND tt.term_id NOT IN (' . $exclude_categories_string . ')
		AND p.post_type = "post"
		AND p.ID > "' . $current_post_id . '" ORDER BY p.ID ASC
	');
	return $return;
}
?>