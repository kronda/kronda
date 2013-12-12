<?php
add_action('after_setup_theme', 'writer_setup');
function writer_setup()
{
load_theme_textdomain('writer', get_template_directory() . '/languages');
add_theme_support('automatic-feed-links');
add_theme_support('post-thumbnails');
global $content_width;
if ( ! isset( $content_width ) ) $content_width = 960;
}
add_action('wp_enqueue_scripts', 'writer_load_scripts');
function writer_load_scripts()
{
wp_enqueue_script('jquery');
wp_register_script('writer-videos', get_template_directory_uri().'/videos.js');
wp_enqueue_script('writer-videos');
}
add_action('wp_head', 'writer_print_custom_scripts', 99);
function writer_print_custom_scripts()
{
if(!is_admin()){
?>
<script type="text/javascript">
jQuery(document).ready(function($){
$("#wrapper").vids();
});
</script>
<?php
}
}
add_action('comment_form_before', 'writer_enqueue_comment_reply_script');
function writer_enqueue_comment_reply_script()
{
if (get_option('thread_comments')) { wp_enqueue_script('comment-reply'); }
}
add_filter('the_title', 'writer_title');
function writer_title($title) {
if ($title == '') {
return '&rarr;';
} else {
return $title;
}
}
add_filter('wp_title', 'writer_filter_wp_title');
function writer_filter_wp_title($title)
{
return $title . esc_attr(get_bloginfo('name'));
}
function writer_custom_pings($comment)
{
$GLOBALS['comment'] = $comment;
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>"><?php echo comment_author_link(); ?></li>
<?php 
}
add_filter('get_comments_number', 'writer_comments_number');
function writer_comments_number($count)
{
if (!is_admin()) {
global $id;
$comments_by_type = &separate_comments( get_comments( 'status=approve&post_id=' . $id ) );
return count($comments_by_type['comment']);
} else {
return $count;
}
}