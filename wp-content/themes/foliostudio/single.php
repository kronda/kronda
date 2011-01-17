<?php get_header(); ?>
	<div class="container">
		<?php top_blog_section(); ?>
		<?php print_breadcrumbs('<div id="pagination">', ' / ', '</div>'); ?>
		<div id="content">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div class="main-post doubleborder">
				<h2 class="post-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h2>
				<div class="post-info">
					Posted on <?php the_time('F j, Y') ?> by <?php the_author_posts_link(); ?> in <?php the_category(', '); ?>
				</div>
				<?php  
				$image = get_post_meta(get_the_id(), '_custom_post_image', true);
				 if ( !empty($image) ): ?>
					<div class="image">
						<a href="<?php the_permalink(); ?>"><img src="<?php echo get_upload_url() ?>/<?php echo $image ?>" alt="" /></a>
						<a href="<?php comments_link(); ?>" class="comment-num"><?php comments_number('0', '1', '%') ?></a>
					</div>
				<?php endif ?>
				<div class="content">
					<?php the_content() ?>
				</div>
			</div>
			<?php comments_template(); ?>
		<?php endwhile; else: ?>
			<p>Sorry, no posts matched your criteria.</p>
		<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
		<div class="cl">&nbsp;</div>
	</div>
<?php get_footer(); ?>