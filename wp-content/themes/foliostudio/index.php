<?php get_header(); ?>
						<div class="container">
							<?php top_blog_section(); ?>
							<?php print_breadcrumbs('<div id="pagination">', ' / ', '</div>'); ?>
							<div id="content">
								<?php not_singular_content(); ?>
							</div>
							<?php get_sidebar(); ?>
							<div class="cl">&nbsp;</div>
						</div>
<?php get_footer(); ?>