<?php get_header(); ?>
			<h2 id="tagline"><?php echo get_option('teaser_text') ?></h2>
		</div>
	</div>
	
	<div id="content">
		<div id="main">
			<div class="shell">
				<div class="breadcrumbs">
					<?php print_breadcrumbs() ?>
				</div>
				
				<div id="blog">
					<div id="widecolumn">
						<?php if (have_posts()) : ?>
							<?php while (have_posts()) : the_post(); ?>
								<div class="post">
									<h2 class="post-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h2>
									<p class="postmetadata"><a href="<?php the_permalink() ?>#comments" class="comments"><?php comments_number('No comments', '1 Comment', '% Comments'); ?></a> by <?php the_author_link(); ?> on <?php the_time('F d, Y') ?></p>
									
									<?php  
									$image = get_post_meta(get_the_ID(), '_custom_post_image_r', true);
									if ( empty($image) ) {
										$image = get_post_meta(get_the_ID(), '_custom_post_image', true);
									}
									?>
									<?php if ( !empty($image) ): ?>
										<a href="<?php the_permalink() ?>" class="img">
											<img src="<?php echo get_upload_url(); ?>/<?php echo $image ?>" alt="" />
										</a>
										<div class="shadow notext">&nbsp;</div>
									<?php endif ?>
									
									<div class="entry">
										<?php the_content('', true) ?>
									</div>
									
									<p class="more"><a href="<?php the_permalink() ?>" class="button"><span>Read More</span></a></p>
									<div class="cl">&nbsp;</div>
								</div>
					
							<?php endwhile; ?>
					
							<div>
								<?php display_navigation(); ?>
								<div class="cl">&nbsp;</div>
							</div>
							
						<?php else : ?>
							<h2 class="center">Not Found</h2>
							<p class="center">Sorry, but you are looking for something that isn't here.</p>
							<div style="height: 200px;">&nbsp;</div>
						<?php endif; ?>
						
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
<?php get_footer(); ?>