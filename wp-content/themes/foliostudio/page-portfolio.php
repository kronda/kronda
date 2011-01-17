<?php
/*
	Template Name: Portfolio Page
*/
?>
<?php get_header(); ?>

	<?php if (have_posts()): ?>
		<?php while(have_posts()): the_post(); ?>
			<div class="container">
				<div id="top-text-section">
					<div class="doubleborder">
						<?php 
							$portfolio_page = get_page_by_path('portfolio');
							$pagetitle = get_post_meta($portfolio_page->ID, '_custom_teaser_text', 1); 
						?>
						<h2 class="tc cufon-plain"><?php echo ($pagetitle) ? htmlize($pagetitle) : get_the_title(); ?></h2>		
					</div>	
				</div>
				<div id="pagination">
					<?php print_breadcrumbs('', ' / ', ''); ?>
				</div>
				<div id="portfolio" class="category-list">
					<?php $portfolio_cats = get_posts('post_type=portfolio&post_parent=0'); ?>
					<?php if ($portfolio_cats): $loopID = 0; ?>
						<?php foreach ($portfolio_cats as $p): $loopID++; ?>
							<?php
								$children = get_posts('post_type=portfolio&post_parent=' . $p->ID);
								if (!$children) {
									continue;
								}
								$loopID_ = 0;
							?>
							<div class="category <?php echo ($loopID == 1) ? 'first-category ' : ''; ?> <?php echo ($loopID < count($portfolio_cats)) ? 'doubleborder' : ''; ?>">
								<h2 class="cufon-plain"><a href="<?php echo get_permalink($p->ID); ?>"><?php echo $p->post_title; ?></a></h2>
								<ul>
									<?php foreach ($children as $child): $loopID_++; ?>
										<?php if($loopID_ > 3) { break; } ?>
									    <li <?php echo ($loopID_ == count($children)) ? 'class="last"' : ''; ?>>
									    	<div class="image">
									    		<a href="<?php echo get_permalink($child->ID); ?>"><img src="<?php echo get_upload_url() . '/' . get_post_meta($child->ID, '_portfolio_image_thumb', 1); ?>" alt="" /></a>
									    	</div>
									    	<div class="info">
									    		<h3 class="cufon-plain"><a href="<?php echo get_permalink($child->ID); ?>"><?php echo $child->post_title; ?></a></h3>
									    		<p><?php echo get_post_meta($child->ID, '_description', 1); ?></p>
									    	</div>
									    </li>										
									<?php endforeach; ?>
								</ul>
								<div class="cl">&nbsp;</div>
							</div>							
						<?php endforeach; ?>
					<?php endif ?>
				</div>
			</div>			
		<?php endwhile; ?>
	<?php endif ?>

<?php get_footer(); ?>