
<?php get_header(); ?>
<div id="content">
<div id="top"></div>
<?php if (have_posts()) : ?>

	<div class="pagetitle">
		<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
		<?php /* If this is a category archive */ if (is_category()) { ?>
		<h2 class="pagetitle">Archive for the &#8216; <?php single_cat_title(); ?> &#8217; Category</h2>
		<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
		<h2 class="pagetitle">Posts Tagged &#8216; <?php single_tag_title(); ?> &#8217;</h2>
		<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('F jS, Y'); ?></h2>
		<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('F, Y'); ?></h2>
		<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('Y'); ?></h2>
		<?php /* If this is an author archive */ } elseif (is_author()) { ?>
		<h2 class="pagetitle">Author Archive</h2>
		<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
		<h2 class="pagetitle">Blog Archives</h2><?php } ?>
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
	<?php while (have_posts()) : the_post(); ?>

		<div class="post listpost" id="post-<?php the_ID(); ?>">
			<div class="title">
				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			<div class="postmeta-single">
                <p>
                    <span class="section section-1">In <?php the_category(', ') ?> </span> 
                    <span class="nodisplay">|</span>
                    on<span class="section section-2"> <?php the_time('F j, Y'); ?></span>
                </p>
            </div>
			</div>
			<div class="entry">
				<?php the_excerpt(); ?>
			</div>

			
		</div>

	<?php endwhile; ?>
	</div>
		


<?php else : ?>

	<div class="postgroup">
		<div class="post listpost">
			<div class="title">
				<h2>No Archive Found</h2>
			</div>
			<div class="entry errorentry">
				<p>Sorry, but you are looking for an archive that isn't here.</p>
			</div>
		</div>
	</div>

<?php endif; ?>
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
