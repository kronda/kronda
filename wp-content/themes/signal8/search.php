<?php get_header(); ?>
	<!-- Content -->
	<div id="content" class="grid_11 pull_5">
	<div class="posts">
	<div class="cl">&nbsp;</div>
	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<div class="post">
				<?php
				$post_img = get_post_meta(get_the_ID(), 'image', 1);
				?>
				<?php if ($post_img): ?>
					<img src="<?php echo $post_img ?>" />
				<?php endif ?>
				
				<div class="cnt">
					<h3><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h3>
					<div class="excerpt">
						<?php echo shorten_excerpt(!empty($post_img), get_the_content('')); ?>
					</div>
				</div>
				<div class="meta">
					<div class="cnt">
						<div class="cl">&nbsp;</div>
						<a href="<?php the_permalink() ?>" class="more">Read More</a>
						<?php the_tags('<p>', ', ', '</p>') ?>
						<div class="cl">&nbsp;</div>
					</div>
				</div>
			</div>

		<?php endwhile; ?>

		<div class="cl">&nbsp;</div>
		<div class="blog-nav">
			<div class="cl">&nbsp;</div>
			<span class="prev"><?php next_posts_link('Older') ?></span>
			<span class="next"><?php previous_posts_link('Newer') ?></span>
			<div class="cl">&nbsp;</div>
		</div>

	<?php else : ?>

		<h2 class="center">No posts found. Try a different search?</h2>
		<?php get_search_form(); ?>

	<?php endif; ?>
		</div>
	</div>
	<!-- END Content -->
<?php get_footer(); ?>