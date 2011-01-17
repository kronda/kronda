<?php
if (!class_exists('WP_Widget')) {
	return;
}

add_action( 'widgets_init', 'load_widgets' );

function load_widgets() {
	register_widget( 'Recent_News_Widget' );
	register_widget( 'Footer_Recent_Comments_Widget' );
	register_widget( 'Most_Discussed_Posts_Widget' );
	register_widget( 'Flickr_Gallery_Widget' );
	register_widget( 'About_Me_Widget' );
}
class Recent_News_Widget extends WP_Widget {
	function Recent_News_Widget() {
		/* Widget settings. */
		$widget_ops = array(
			'classname'=>'recent_news', 
			'description'=>'Recent News' 
		);

		/* Widget control settings. */
		$control_ops = array(
			'width'=>250,
			'height'=>'auto',
			'id_base'=>'example-widget'
		);

		/* Create the widget. */
		$this->WP_Widget('example-widget', 'Recent News', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		
		extract( $args );

		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$category = $instance['category'];
		$number_of_posts = $instance['number_of_posts'];
		$show_image = isset( $instance['show_image'] ) ? $instance['show_image'] : false;
		$posts_query = array('numberposts'=>$number_of_posts);
		if ($category!=-1) {
			$posts_query['category'] = $category;
		}
		
		$posts = get_posts($posts_query);
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		echo $before_title;
		if ( $title ) echo $title;
		echo $after_title;
		echo '<div class="recent-posts">';
		foreach ($posts as $post) {
			$img = null;
			if ($show_image) {
				$post_image = get_post_thumbnail($post->ID);
			}
			?>
			<div class="post">
				<div class="cl">&nbsp;</div>
				<?php if ($show_image && isset($post_image)): ?>
					<img src="<?php echo $post_image ?>" alt="<?php echo apply_filters('the_title', $post->post_title) ?>" />
					<div class="cnt">
				<?php else: ?>
					<div class="cnt" style="width: 100%">
				<?php endif ?>
					<h3><a href="<?php echo get_permalink($post->ID) ?>"><?php echo apply_filters('the_title', $post->post_title) ?></a></h3>
					<p><?php echo shortalize(apply_filters('the_content', $post->post_content), 15) ?></p>
				</div>
				<div class="cl">&nbsp;</div>
			</div>
			<?php
		}
		echo '</div><!-- Recent Posts -->';
		/* After widget (defined by themes). */
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['number_of_posts'] = strip_tags( $new_instance['number_of_posts'] );
		$instance['show_image'] = strip_tags( $new_instance['show_image'] );
		

		return $instance;
	}
	
	function form( $instance ) {
		/* Set up some default widget settings. */
		$defaults = array(
			'title'=>'Recent News',
			'category' => '0',
			'number_of_posts' => '2',
			'show_image' => '1',
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>">Category:</label>
			<?php wp_dropdown_categories('show_option_none=All Categories&show_count=1&hide_empty=0exclude=1&selected=' . $instance['category'] . 
							'&hierarchical=1&name=' . $this->get_field_name( 'category' ) . '&class=widefat') ?>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('number_of_posts') ?>">Number of posts to show:</label>
			<input id="<?php echo $this->get_field_id('number_of_posts') ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ) ?>" type="text" value="<?php echo $instance['number_of_posts'] ?>" size="3" /><br />
			<small>(at most 15)</small>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_image'], true ); ?> id="<?php echo $this->get_field_id( 'show_image' ); ?>" name="<?php echo $this->get_field_name( 'show_image' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_image' ); ?>">Display Image?<small>(If available)</small></label>
		</p>
		
		<?php 
	}
}
class Footer_Recent_Comments_Widget extends WP_Widget_Recent_Comments {
	function Footer_Recent_Comments_Widget() {
		$widget_ops = array(
			'classname' => 'comments', 
			'description' => __( 'The most recent comments' ) 
		);
		$this->WP_Widget('footer-recent-comments', __('Footer widget: Recent Comments'), $widget_ops);
	}
	function widget($args, $instance) {
		$recent_comments = get_recent_comments(5);
		extract($args);
		
		echo $before_widget;
		
		if (empty($instance['title'])) {
			$instance['title'] = 'Recent Comments';
		}
		/* Title of widget (before and after defined by themes). */
		if ( $instance['title'] )
			echo $before_title . $instance['title'] . $after_title;
		?>
		<div class="cnt">
			<?php foreach ($recent_comments as $comment): ?>
				<div class="comment">
					<p class="author">
						<?php if (!empty($comment->comment_author_url)): ?>
							<a href="<?php echo $comment->comment_author_url ?>" rel="nofollow" target="_blank"><?php echo $comment->comment_author ?></a>
						<?php else: ?>
							<strong><?php echo $comment->comment_author ?></strong>
						<?php endif ?>
						says,</p>
					<a href="<?php echo get_permalink($comment->comment_post_ID) ?>"><?php echo shortalize($comment->comment_content, 10) ?></a>
				</div>
			<?php endforeach ?>
		</div>
		<?php
		echo $after_widget;
	}
}
class Most_Discussed_Posts_Widget extends WP_Widget_Recent_Posts {
	function Most_Discussed_Posts_Widget() {
	    $widget_ops = array(
			'classname' => 'disc-posts',
			'description' => __('The most discussed posts')
		);
		$this->WP_Widget('footer-Most_Discussed_Posts_Widget', __('Footer widget: Most discussed posts'), $widget_ops);
	}
	function widget($args, $instance) {
	    $most_disucussed = get_most_discussed_posts();
		extract($args);
		
		echo $before_widget;
		if (empty($instance['title'])) {
			$instance['title'] = 'Most Discussed';
		}
		/* Title of widget (before and after defined by themes). */
		if ( $instance['title'] )
			echo $before_title . $instance['title'] . $after_title;
		?>
		<div class="cnt">
			<?php foreach ($most_disucussed as $_post): ?>
				<div class="txt">
					<p><a href="<?php echo get_permalink($_post->ID) ?>"><?php echo apply_filters('the_title', $_post->post_title) ?></a></p>
				</div>
			<?php endforeach ?>
		</div>
		<?php
		echo $after_widget;
	}
}
class Flickr_Gallery_Widget extends WP_Widget {
	function Flickr_Gallery_Widget() {
	    $widget_ops = array(
			'classname' => 'flickr-gallery',
			'description' => __('Flickr Latest Photos')
		);
		$this->WP_Widget('footer-widget_flickr_gallery_widget', __('Footer widget: Flickr latest photos'), $widget_ops);
	}
	function widget($args, $instance) {
		$flickr_imgs = get_latest_flickr_images();
		$flickr_photostream_url = get_option('flickr_photostream_url');
		
	    extract($args);
		echo $before_widget;
		
		if (empty($instance['title'])) {
			$instance['title'] = 'Flickr Gallery';
		}
		
		/* Title of widget (before and after defined by themes). */
		if ( $instance['title'] )
			echo $before_title . 
				'<img src="' . get_bloginfo('stylesheet_directory') .'/images/ico-flickr.gif" class="ico-flickr" alt="" /> ' . 
				$instance['title'] . 
				$after_title;
		?>
		<div class="cnt">
			<div class="cl">&nbsp;</div>
			<?php foreach ($flickr_imgs as $flickr_img): ?>
				<a href="<?php echo $flickr_img['url'] ?>" class="image" title="<?php echo $flickr_img['title'] ?>" target="_balnk"><img src="<?php echo str_replace('_m', '_s', $flickr_img['image']) ?>" width="88px" alt="<?php echo $flickr_img['title'] ?>" /></a>
			<?php endforeach ?>
			<div class="cl">&nbsp;</div>
			<?php if ($flickr_photostream_url): ?>
				<a href="<?php echo $flickr_photostream_url ?>" class="more">View More</a>
				<div class="cl">&nbsp;</div>
			<?php endif ?>
		</div>
		<?php
		echo $after_widget;
	}
}
class About_Me_Widget extends WP_Widget {
	function About_Me_Widget() {
	    $widget_ops = array(
			'classname' => 'about',
			'description' => __('Flickr Latest Photos'),
		);
		$this->WP_Widget('footer-about_me_widget', __('Footer widget: About Me'), $widget_ops);
	}
	function widget($args, $instance) {
		$about_page = get_option('choose_about_page');
		
		extract($args);
		
		echo $before_widget;
		if (empty($instance['title'])) {
			$instance['title'] = 'About Me';
		}
		/* Title of widget (before and after defined by themes). */
		if ( $instance['title'] )
			echo $before_title . $instance['title'] . $after_title;
		echo '<div class="cnt">';
		echo get_avatar(get_option('admin_email'), 47); 
		
		?>
		<div class="cl">&nbsp;</div>
		<?php echo wpautop(get_usermeta(1, 'description')) ?>
		<div class="cl">&nbsp;</div>
		<?php
		if ($about_page) : ?>
			<a href="<?php echo get_permalink($about_page) ?>" class="more">View More</a>
		<?php endif;
		
		echo "</div>$after_widget";
	}
}
?>