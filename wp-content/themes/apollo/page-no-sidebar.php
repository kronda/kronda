<?php  
/*
Template Name: Full Width Page
*/
?>
<?php get_header(); ?>
<?php if (have_posts()) : ?>
	<?php while (have_posts()) : the_post(); ?>
			<?php 
			$teaser = get_post_meta($post->ID, 'teaser_text', true);
			if ( empty($teaser) ) {
				$teaser = get_option('teaser_text'); // TODO
			}
			?>
			<h2 id="tagline"><?php echo $teaser ?></h2>
		</div>
	</div>
	
	<div id="content">
		<div id="main">
			<div class="shell full-width-shell">
				<div class="breadcrumbs">
					<?php print_breadcrumbs() ?>
				</div>
				<div class="gallery-details">
					<div class="description">
						<?php 
						$img_metas = array('_custom_big_post_image_r', '_custom_big_post_image', '_custom_post_image_r', '_custom_post_image');
						foreach ($img_metas as $meta) {
							$image = get_post_meta(get_the_ID(), $meta, true);
							if ( !empty($image) ) {
								break;
							}
						}
					 	?>
						<?php if ( !empty($image) ): ?>
						<a href="<?php the_permalink() ?>" class="img">
							<img src="<?php echo get_upload_url(); ?>/<?php echo $image ?>" alt="" />
						</a>
						<div class="shadow notext">&nbsp;</div>
						<?php endif ?>
						<h3><?php the_title() ?></h3>
						<?php the_content() ?>
					</div>
					<div class="right-side">
						<?php if (is_gallery_subpage(get_the_ID())): ?>
						<div class="menu">
							<ul>
								<?php
								//The Query
								query_posts('posts_per_page=-1&post_type=page&post_parent=' . $post->post_parent );
								$current_page_id = get_the_ID();
								if ( have_posts() ) : while ( have_posts() ) : the_post();
								?>
							   		<li><a href="<?php the_permalink() ?>" <?php if($current_page_id == get_the_ID()) echo 'class="active"'; ?>><span><?php the_title() ?></span></a></li>
								<?php endwhile; endif;?>
								<?php
									//Reset Query
									wp_reset_query();
								?>
							</ul>
						</div>
						<?php  
						$testimonial_text = get_post_meta(get_the_ID(), '_gallery_testimonial_text', true);
						$testimonial_client = get_post_meta(get_the_ID(), '_gallery_testimonial_client', true);
						$testimonial_company = get_post_meta(get_the_ID(), '_gallery_testimonial_company', true);
						?>
						<?php if ( !empty($testimonial_text) ): ?>
						<div class="testimonial">
							<div class="box">
								<div class="bottom">
									<div class="top">
										<p><?php echo $testimonial_text ?></p>
									</div>
								</div>
							</div>
							<span><strong><?php echo $testimonial_client ?></strong> <?php echo $testimonial_company ?></span>
						</div>
						<?php endif ?>
						<?php endif ?>
					</div>
					<div class="cl">&nbsp;</div>
				</div>
			</div>
		</div>
		<div id="bottom">
			<div class="shell">
				<?php get_bottom_sidebar(); ?>
				<div class="cl">&nbsp;</div>
			</div>
		</div>
	</div>	
	<?php endwhile; ?>
<?php else: ?>
			<div class="cl">&nbsp;</div>
			<h2 id="tagline">We would Love to Hear Form You! Get in Touch with Us Now</h2>
		</div>
	</div>
	<div id="content">
		<div id="main">
			<div class="shell">
				<div class="breadcrumbs">
					<a href="#">Home</a> &rsaquo; Blog
				</div>
				<div id="blog">
					<div id="widecolumn">
						<h2 class="center page-title post-title">Error 404 - Not Found</h2>
						<p>Please check the URL for proper spelling and capitalization. If you're having trouble locating a destination, try visiting the <a href="<?php echo get_option('home') ?>">home page</a>.</p>
						<div class="cl">&nbsp;</div>
					</div>
					<?php get_sidebar() ?>
					<div class="cl">&nbsp;</div>
				</div>
			</div>
		</div>
		<div id="bottom">
			<div class="shell">
				<?php get_bottom_sidebar(); ?>
				<div class="cl">&nbsp;</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php get_footer(); ?>