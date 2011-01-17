<?php  
/*
Template Name: Home Page
*/
get_header();

	$slides = get_posts('orderby=menu_order&post_type=slide&order=ASC&numberposts=-1');
	$loopID = 0;
	?>
	<?php if ( get_option('slideshow_view_mode') == 'slider' ): ?>
	<div id="cycle-slider" class="slider">
		<div class="slide-mask">&nbsp;</div>
		<div class="slides">
			<?php foreach ($slides as $slide): $loopID++; ?>
				<div class="slide" <?php echo ($loopID == 1) ? 'style="display: block"' : ''; ?>>
					<img src="<?php echo get_upload_url() . '/' .  get_post_meta($slide->ID, '_slide_big_image', 1); ?>" width="940" height="350" alt="<?php echo $slide->post_title; ?>" />
					<h1><?php echo get_post_meta($slide->ID, '_slide_title', true); ?></h1>
				</div>									
			<?php endforeach; ?>
		</div>
		<? /* width = a.length * 17; left = 940-width/2 */ ?>
		<div class="slide-navigation" style="width: <?php echo count($slides) * 17; ?>px; left: <?php echo (940-(count($slides)*17))/2 ?>px;"> 
			<?php for($i = 0; $i < count($slides); $i++): ?>
				<a href="#" <?php echo ($i == 0) ? 'class="active"' : ''; ?>>&nbsp;</a>
			<?php endfor; ?>
		</div>
		<div class="slide-control">
			<a href="#" class="prev">&nbsp;</a>
			<a href="#" class="next">&nbsp;</a>
		</div>
	</div>						
	<?php else: ?>
	<div id="gallery-slider" class="slider">
		<div class="slides">
			<?php foreach ($slides as $slide): $loopID++; ?>
				<div class="slide" <?php echo ($loopID == 1) ? 'style="display: block"' : ''; ?>>
					<img src="<?php echo get_upload_url() . '/' . get_post_meta($slide->ID, '_slide_big_image', 1); ?>" width="940" height="350" alt="<?php echo $slide->post_title; ?>" />
					<h1><?php echo get_post_meta($slide->ID, '_slide_title', true); ?></h1>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="slide-navigation">
			<span class="left-mask"></span>
			<span class="right-mask"></span>		
			<div class="slide-thumbs">
				<div class="slide-thumbs-inner">
					<?php foreach ($slides as $slide): ?>
						<a href="#"><img src="<?php echo get_upload_url() . '/' . get_post_meta($slide->ID, '_slide_small_image', 1); ?>" alt="" /></a>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="slide-control">
				<a href="#" class="prev">&nbsp;</a>
				<a href="#" class="next">&nbsp;</a>
			</div>								
		</div>
	</div>
	<?php endif ?>
	
	<div class="container">
		<?php  
		$boxes = array();
		$boxes_count = clamp(intval(get_option('homepage_areas_count')), 1, 3);
		for ($i=1; $i <= $boxes_count; $i++) {
			$box_text = get_option('homepage_area_' . $i); 
			if ( strip_tags($box_text) ) {
				$boxes[] = array('text' => $box_text, 'image' => get_option('homepage_area_' . $i . '_bullet'));
			}
		}
		?>
		<div id="main-blocks" class="doubleborder main-blocks-columns-<?php echo count($boxes) ?>">
			<?php 
			foreach ($boxes as $count => $box) {
				if ( get_option('color_scheme') == 'dark' 
					&& $box['image'] == get_bloginfo('stylesheet_directory') . '/color-schemes/turquoise/images/main-text-block-' . ($count+1) . '.gif' ) {
					$box['image'] = get_bloginfo('stylesheet_directory') . '/color-schemes/dark/images/main-text-block-' . ($count+1) . '.gif';
				}
				echo '<div class="text-block text-block-' . ($count+1) . '"><img src="' . $box['image'] . '" alt="" class="bullet"/><div class="text">' . $box['text'] . '</div><div class="cl">&nbsp;</div></div>';
			}
			?>
			<div class="cl">&nbsp;</div>
		</div>
		<?php if ( get_option('get_started') != 'n' ): ?>
			<div id="get-started" class="doubleborder">
				<div id="get-started-inner">
					<div class="cl">&nbsp;</div>
					<h2 class="cufon-plain"><?php echo get_option('get_started_text') ?></h2>
					<a href="<?php echo get_option('get_started_url') ?>" class="get-started-button">Get Started</a>
					<div class="cl">&nbsp;</div>									
				</div>
			</div>
		<?php endif ?>
		<div id="featured-projects">
			<h3 class="cufon-plain">Featured Projects</h3>
			<?php 
				$featured_projects = get_posts('post_type=portfolio'); 
				$loopID = 0;
			?>
			<?php foreach ($featured_projects as $project): ?>
				<?php if (get_post_meta($project->ID, '_portfolio_featured', 1) != 'yes'): continue; endif;?>
				<?php 
					$loopID++;
					if ($loopID > 2) {
						break;
					}
				?>
				<div class="featured-project <?php echo ($loopID % 2 == 0) ? 'right' : 'left' ?>">
					<div class="image">
						<a href="<?php echo get_permalink($project->ID); ?>"><img src="<?php echo get_upload_url() . '/' . get_post_meta($project->ID, '_portfolio_image_thumb', 1); ?>" alt="" /></a>
					</div>
					<div class="description">
						<h4 class="cufon-plain"><a href="<?php echo get_permalink($project->ID); ?>"><?php echo $project->post_title; ?></a></h4>
						<p><?php echo get_post_meta($project->ID, '_description', 1); ?></p>
						<a href="<?php echo get_permalink($project->ID); ?>" class="view-project">View Project</a>
					</div>
				</div>									
			<?php endforeach; ?>
		</div>
		<div id="from-the-blog">
			<h3 class="cufon-plain">From The Blog</h3>
			<a href="<?php bloginfo('rss_url'); ?>" class="rss">Subscribe to Blog RSS</a>							
			<div class="cl">&nbsp;</div>
			<div class="blog-posts">
				<?php query_posts('post_type=post&showposts=3'); ?>
				<?php if (have_posts()): ?>
					<?php while(have_posts()): the_post(); ?>
						<div class="blog-post">
							<a href="<?php the_permalink(); ?>" class="thumb"><img src="<?php echo get_upload_url() . '/' . get_post_meta(get_the_id(), '_custom_post_image_thumb', 1); ?>" alt="" /></a>
							<h4 class="cufon-plain"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
							<p><?php echo shortalize(get_the_content(), 15); ?></p>
							<div class="cl">&nbsp;</div>
						</div>	
					<?php endwhile; ?>
				<?php endif; ?>
				<?php wp_reset_query() ?>
			</div>
			<a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" class="visit-the-blog">Visit the blog</a>
		</div>
		<div class="cl">&nbsp;</div>
	</div>
<?php get_footer(); ?>