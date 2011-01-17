
<?php get_header(); ?>
<div id="content">

<?php if (have_posts()) : ?>

	<div class="pagetitle">
		<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
		<?php if (is_category()) : ?>
		<h2 class="pagetitle">Archive for the &#8216; <?php single_cat_title(); ?> &#8217; Category</h2>
		<?php elseif (isset($_GET['paged']) && !empty($_GET['paged'])) : ?>
		<h2 class="pagetitle">Blog Archives</h2>
		<?php endif; ?>
	</div>
	
	<?php
		$ad_468_60 = get_option('padd_ad_468_60');
		if (!empty($ad_468_60)) :
	?>
	<div class="singlegoogle-page">
		<?php echo stripslashes($ad_468_60); ?>
	</div>
	<?php endif; ?>

	<div class="postgroup">
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
	</div>
		
<?php else : ?>
	<div class="postgroup">
		<div class="post listpost">
			<div class="title">
				<h2>No Category Found</h2>
			</div>
			<div class="entry errorentry">
				<p>Sorry, but you are looking for a category that isn't here.</p>
			</div>
		</div>
	</div>
<?php endif; ?>

</div>
<?php get_sidebar(); ?>
<div id="pagenav">

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

<?php get_footer(); ?>
