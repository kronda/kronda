
<?php get_header(); ?>

<div id="content">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	
	<div class="post singlepost" id="post-<?php the_ID(); ?>">

		<div class="entry">
		<div class="title">
			<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			<div class="postmeta-single">
						<div class="postmeta">
                        	    
								<div class="section-1"> By <?php the_author_posts_link(); ?> on <?php the_time('F. j. Y'); ?> | <?php //the_comments() ?> </div> 
								
						</div>
            </div>

		</div>
				<?php
				$ad_468_60 = get_option('padd_ad_468_60');
				if (!empty($ad_468_60)) :
			?>
			<div class="singlegoogle-page">
				<?php echo stripslashes($ad_468_60); ?>
			</div>
			<?php endif; ?>
            <div class="twtbutton"><? if (function_exists('tweetmeme')) echo tweetmeme(); ?></div>
            <div class="text">
            			
				<?php the_content(); ?>
				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
             </div>
			<div class="postmeta">
				<?php if ( function_exists('the_tags') ) { the_tags('<p><span id="tags"><strong>Tagged as:</strong> ', ', ', '</span></p>'); } ?>
			</div>
		</div>

	</div>

	<?php comments_template(); ?>
	
	<?php endwhile; else: ?>
	
	<div class="post singlepost">
		<div class="title">
			<h2>No Page Found</h2>
		</div>
		<div class="entry errorentry">
			<p>Sorry, but you are looking for a page that isn't here.</p>
		</div>
	</div>

	<?php endif; ?>
<div id="bottom"></div>	
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
