<?php  
/*
Template Name: Homepage
*/
?>
<?php get_header(); ?>
			<div class="line notext">&nbsp;</div>
			<h2 id="slogan"><?php bloginfo('description'); ?></h2>
			<div class="line notext">&nbsp;</div>
		</div>
	</div>
	
	<div id="content">
		<div id="main">
			<div class="shell">
				<div class="cl">&nbsp;</div>
				<div id="slider">
					<?php  
						$featured = _get_content_by_meta_key('featured');
						$enable_teaser =  get_option('enable_teaser') == 'y';
					?>
					<div class="slider-nav">
						<ul>
							<?php if ( empty($featured) ): ?>
								<li><a href="#" class="active">1</a></li>
							<?php endif ?>
							<?php $x=0; for ($i=0; $i < count($featured); $i++) : ?>
								<?php  
								if ( get_post_meta($featured[$i], 'featured_check', true) != 'true') { continue; }
								$x++;
								?>
						    	<li><a href="#" <?php if($x == 1) { echo 'class="active"'; } ?>><?php echo $i ?></a></li>
						    <?php endfor; ?>
						</ul>
					</div>
					<div class="slider-hld">
						<ul>
							<?php if ( empty($featured) ): ?>
							<li>
						    	<img alt="" src="<?php bloginfo('stylesheet_directory'); ?>/images/slider-img.jpg">
						    	<div class="slider-description">
						    		<h3>Image Title Goes Here</h3>
						    		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore</p>
						    		<a href="#">Read More</a>
						    	</div>
						    </li>
							<?php endif ?>
							<?php $i=0; foreach ($featured as $id): ?>
								<?php  
								if ( get_post_meta($id, 'featured_check', true) != 'true') { continue; }
								$i++;
								?>
								<li <?php if($i==1) echo 'class="active"'; ?>>
									<img src="<?php echo get_upload_url(); ?>/<?php echo get_post_meta($id, '_featured_post_image', true) ?>" alt="" />
									<?php if ( $enable_teaser ): ?>
									<div class="slider-description">
										<h3><?php echo get_the_title($id) ?></h3>
										<p><?php echo get_post_meta($id, 'featured', true) ?></p>
										<a href="<?php echo get_permalink($id) ?>">Read More</a>
									</div>
									<?php endif ?>
								</li>
							<?php endforeach ?>
						</ul>
					</div>
				</div>
			
				<div id="features">
					<ul>
						<?php $default_feature_img =  get_bloginfo('stylesheet_directory') . '/images/clock.gif'?>
						<?php for ($i=0; $i < 3; $i++): ?>
							<?php 
							$title = get_option('title_' . $numbers[$i], 'Feature');
							$image = get_option('image_' . $numbers[$i], $default_feature_img);
							$description = get_option('description_' . $numbers[$i], 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
							$link = get_option('link_' . $numbers[$i], '#');
							if ( empty($title) && empty($description) && empty($link) && (empty($image) || $image == $default_feature_img) ) {
								continue;
							}
							?>
						    <li <?php if($i==1) {echo 'class="middle"';} ?>>
						    	<h3><?php echo $title ?></h3>
						    	<img src="<?php echo $image ?>" alt="<?php echo $title ?>" />
						    	<div class="text-hld">
						    		<p><?php echo $description ?></p>
						    		<a href="<?php echo $link ?>" class="button"><span>Learn More</span></a>
						    	</div>
						    	<div class="cl">&nbsp;</div>
						    </li>
						<?php endfor; ?>
					</ul>
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