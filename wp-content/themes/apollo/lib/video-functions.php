<?php
# this file contains functions that are related with videos

/**
 * Filters/resizes video embed codes.
 */
function filter_video($html, $wmode = false, $width = false, $height = false) {
	$final_html = $html;
	if ($wmode) {
		$final_html = str_replace('<embed', '<param name="wmode" value="transparent"></param><embed wmode="transparent" ', $final_html);
	}
	if (is_numeric($width)) {
		$final_html = preg_replace('~width="[\d]+"~', 'width="'.$width.'"', $final_html);
	}
	if (is_numeric($height)) {
		$final_html = preg_replace('~height="[\d]+"~', 'height="'.$height.'"', $final_html);
	}
	
	return $final_html;
}

/**
 * Return the thumbnail src for Youtube and Vimeo videos
 * $embed_code = the full video embed code
 */
function get_video_thumb($embed_code) {
	$return = '';
	if (preg_match('~youtube~', $embed_code)) {
		preg_match('~v/(.*?)&~', $embed_code, $video_id);
		$return = "http://img.youtube.com/vi/".$video_id[1]."/0.jpg";
	} elseif (preg_match('~vimeo~', $embed_code)) {
		preg_match('~clip_id=(.*?)&~', $embed_code, $video_id);
		$thumb = get_vimeo_thumb($video_id[1]);
		$return = $thumb[0]['thumbnail_medium'];
	}

	return $return;
}


/**
 * Return the thumbnail src for Vimeo videos
 * $video_id = the Vimeo video id
 */
function get_vimeo_thumb($videoid) {
	$url = "http://vimeo.com/api/v2/video/".$videoid.".php";
	$cache_id = 'vimeocache::' . md5($url);
	$cache_lifetime = 300;
	
	$cached = get_option($cache_id, -1);
	$has_cache = $cached !== -1;
	
	$is_expired = isset($cached['expires']) && time() > $cached['expires'];
	
	if (!$has_cache || $is_expired) {
		$data = wp_remote_get($url);
		$data = $data['body'];
		
		$video_cache = array(
			'data'=>$data,
			'expires'=>time() + $cache_lifetime,
		);
		update_option($cache_id, $video_cache);
	} else {
		$data = $cached['data'];
	}
	
	$finaldata = unserialize($data);
	
	return $finaldata;
}
?>