<?php get_header(); ?>
	<!-- Content -->
	<div id="content" class="grid_11 pull_5">
		<?php the_post(); ?>
		<div id="single">
			<div class="post">
				<h2><?php the_title(); ?></h2>
				
				<div class="entry">
					<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
				</div>
			</div>
		</div>
	</div><!-- /#content -->
<?php get_footer(); ?>