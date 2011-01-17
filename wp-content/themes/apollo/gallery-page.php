<?php  
/*
Template Name: Gallery page
*/
?>
<?php get_header() ?>
			<?php 
			$teaser = get_post_meta($post->ID, 'teaser_text', true);
			if ( empty($teaser) ) {
				$teaser = get_option('teaser_text'); // TODO
			}
			?>
			<h2 id="tagline"><?php echo $teaser ?></h2>
		</div>
	</div>
	
	<div id="content" class="top-pull">
		<div id="main">
			<div class="shell">
				<div class="breadcrumbs">
					<?php print_breadcrumbs() ?>
				</div>
				<div class="gallery-thumbs">
					<div class="cl">&nbsp;</div>
					<ul>
						<?php
						//The Query
						query_posts('posts_per_page=-1&post_type=page&post_parent=' . $post->ID );
						if ( have_posts() ) : $i=0; while ( have_posts() ) : the_post(); $i++;
							$image = get_post_meta(get_the_ID(), '_gallery_post_image_small', true);
						?>
						<?php if ( $i == 4) {echo '</ul><div class="cl">&nbsp;</div><ul>'; $i=1; } ?>
						    <li <?php if( $i==2 ) echo 'class="middle"'; ?>>
						    	<?php if ( !empty($image) ): ?>
							    	<a href="<?php the_permalink() ?>" class="img">
							    		<?php $updir = wp_upload_dir() ?>
							    		<img src="<?php echo $updir['baseurl'] ?>/<?php echo $image ?>" alt="" />
							    	</a>
						    		<div class="shadow notext">&nbsp;</div>
						    	<?php endif; ?>
						    	<h3><?php the_title(); ?></h3>
						    	<p><?php echo get_post_meta( get_the_ID(), 'gallery_description', true) ?></p>
						    	<a href="<?php the_permalink() ?>" class="button"><span>Learn More</span></a>
						    </li>
						<?php endwhile; else: ?>
							 Nothing found!
						<?php
							endif;
							//Reset Query
							wp_reset_query();
						?>
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
<?php get_footer() ?>