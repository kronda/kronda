<?php get_header(); ?>
			<div class="cl">&nbsp;</div>
			<h2 id="tagline">We would Love to Hear Form You! Get in Touch with Us Now</h2>
		</div>
	</div>
	<div id="content">
		<div id="main">
			<div class="shell">
				<div class="breadcrumbs">
					<?php
						if(function_exists('bcn_display')) {
							bcn_display();
						}
					?>
				</div>
				<div id="blog">
					<div id="widecolumn">
						<h2 class="center page-title post-title">Error 404 - Not Found</h2>
						<p>Please check the URL for proper spelling and capitalization. If you're having trouble locating a destination, try visiting the <a href="<?php echo get_option('home') ?>">home page</a>.</p>
						<div style="height: 200px;">&nbsp;</div>
						<div class="cl">&nbsp;</div>
					</div>
					<?php get_sidebar() ?>
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
<?php get_footer(); ?>