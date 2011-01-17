<?php get_header(); ?>
	<!-- Content -->
	<div id="content" class="grid_11 pull_5">
		
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div id="single">
				<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
					<span class="comments-num"><?php comments_number('<span>0</span> Comments', '<span>1</span> Comment', '<span>%</span> Comments') ?> </span>
					<h2><?php the_title(); ?></h2>
					<p class="author">By <?php the_author() ?> /  Posted on <?php the_time('d F Y') ?></p>
					<div class="entry">
						<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>
					</div>
					<div class="meta">
						<div class="row">
							<div class="cl">&nbsp;</div>
							<p class="title">Categories</p> <p class="text"><?php the_category(', ') ?></p>
							<div class="cl">&nbsp;</div>
						</div>
						<div class="cl">&nbsp;</div>
						<?php the_tags( '<div class="row">
							<div class="cl">&nbsp;</div>
							<p class="title">Tags</p><p class="text">', ', ', '</p><div class="cl">&nbsp;</div>
						</div>'); ?>
					</div>
				</div>
			<?php comments_template(); /*closes div#single */ ?>
			<?php endwhile; else: ?>
				<p>Sorry, no posts matched your criteria.</p>
			<?php endif; ?>
		</div><!-- /div#content -->
<?php get_footer(); ?>