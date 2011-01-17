<?php get_header(); ?>
	<?php if (have_posts()): ?>
		<?php while(have_posts()): the_post() ?>
			<div class="container">
				<div id="top-text-section">
					<div class="doubleborder">
						<?php 
							$portfolio_page = get_page_by_path('portfolio');
							$pagetitle = get_post_meta($portfolio_page->ID, '_portfolio_page_title', 1); 
						?>
						<h2 class="tc cufon-plain"><?php echo ($pagetitle) ? htmlize($pagetitle) : get_the_title(); ?></h2>			
					</div>	
				</div>
				<div id="pagination">
					<?php print_breadcrumbs('', ' / ', ''); ?>
				</div>
				<div id="portfolio">
					<?php $children = get_posts('post_type=portfolio&post_parent=' . get_the_id()) ?>
					<?php if ($children): //If it is a portfolio category ?>
						<div class="all category-list">
							<h2 class="cufon-plain"><?php the_title(); ?></h2>
							<ul>
								<?php 
									$cat_posts =  get_posts('post_type=portfolio&post_parent=' . get_the_id()); 
									$loopID = 0;
									
									$per_page = get_option('posts_per_page');
									$page = 0;
									if ( isset($_GET['page']) ) {
										$page = intval($_GET['page']);
									}
									$children = get_posts('post_type=portfolio&post_parent=' . get_the_id() . '&numberposts=-1');
									$pages = count($children)/$per_page;
									
									if ($page < 0 || $page > $pages) {
										$page = 0;
									}
									
									$children = array_slice($children, $page*$per_page, $per_page);
								?>
								<?php if ($children): ?>
									<?php foreach ($children as $child): $loopID++; ?>
									    <li <?php echo ($loopID % 3 == 0) ? 'class="last"' : ''; ?>>
									    	<div class="image">
									    		<a href="<?php echo get_permalink($child->ID); ?>"><img src="<?php echo get_upload_url() . '/' . get_post_meta($child->ID, '_portfolio_image_thumb', 1); ?>" alt="" /></a>
									    	</div>
									    	<div class="info">
									    		<h3 class="cufon-plain"><a href="<?php echo get_permalink($child->ID); ?>"><?php echo $child->post_title; ?></a></h3>
									    		<p><?php echo get_post_meta($child->ID, '_description', 1); ?></p>
									    	</div>
									    </li>									
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
							<div class="cl">&nbsp;</div>
							<?php if ($pages > 1): ?>
								<div class="pages">
									<?php for($i = 0; $i < $pages; $i++): ?>
										<a href="<?php echo get_url('page', $i); ?>" <?php echo ($i == $page) ? 'class="active"' : '';  ?>><?php echo $i+1; ?></a>
									<?php endfor; ?>
								</div>							
							<?php endif; ?>
						</div>		
					<?php else: //If it is a single portfolio item ?>		
						<div class="default-page">
							<h2 class="cufon-plain"><?php the_title(); ?></h2>
							<?php $description = get_post_meta($post->ID, '_description', 1); ?>
							<?php if ($description): ?>
								<h4 class="portfolio-description cufon-plain"><?php echo $description; ?></h4>
							<?php endif; ?>
							<?php 
								$portfolio_image_medium = get_post_meta($post->ID, '_portfolio_image', 1);
								if ( !empty($portfolio_image_medium) ): ?>
								<div class="image">
									<a href="<?php echo get_upload_url() . '/' . get_post_meta($post->ID, '_portfolio_image', 1); ?>" rel="fancybox"><img src="<?php echo get_upload_url() . '/' . get_post_meta($post->ID, '_portfolio_image', 1); ?>" alt="" /></a>
								</div>								
							<?php endif ?>
							<div class="entry">
								<?php the_content(); ?>
							</div>
						</div>
					<?php endif ?>
				</div>
			</div>			
		<?php endwhile; ?>
	<?php endif ?>

<?php get_footer(); ?>