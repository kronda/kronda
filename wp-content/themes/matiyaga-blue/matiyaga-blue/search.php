
<?php get_header(); ?>
<div id="content">
	<div id="top"></div>
	<div class="pagetitle">
		<h2 class="pagetitle">Search Results</h2>
	</div>

	<?php
		$ad_468 = get_option('padd_ad_468');
		if (!empty($ad_468)) {
	?>
	<div class="singlegoogle-page">
		<?php echo stripslashes($ad_468); ?>
	</div>
	<?php
		}
	?>
	
	<div class="postgroup">
<?php if (have_posts()) : ?>

	<?php while (have_posts()) : the_post(); ?>

	<div class="post listpost" id="post-<?php the_ID(); ?>">
		
		<div class="entry">
<div class="title">
			<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
						<div class="postmeta">
                        	    
								<div class="section-1"> By <?php the_author_posts_link(); ?> on <?php the_time('F. j. Y'); ?> | <?php comments_popup_link('No Comments', '1 Comment', '% Comments'); ?> </div> 
								
						</div>
		</div>        
			<?php the_excerpt(); ?>
		</div>
	</div>

	<?php endwhile; ?>
	
<?php else : ?>
	
	<div class="post listpost">
		<div class="title">
			<h2>Not Found</h2>
		</div>
		<div class="entry errorentry">
			<p>Sorry, but you are looking for something that isn't here.</p>
		</div>
	</div>
		
<?php endif; ?>
	</div>
    <div id="bottom"></div>
</div>
<?php get_sidebar(); ?>
<div id="pagenav">
<div id="pagenav-top"> </div>
    <?php 
        if (function_exists('wp_pagenavi')) : 
            wp_pagenavi();  
        else : 
    ?>
    <div class="simplenavi">
        <?php posts_nav_link(' &nbsp;&nbsp;','&laquo; Previous Entries','Next Entries &raquo;') ?></div>
    </div>
    <?php
        endif;
    ?>
</div>
<div id="pagenav-bottom"> </div>
<?php get_footer(); ?>