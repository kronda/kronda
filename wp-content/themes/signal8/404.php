<?php
get_header();
?>
	<!-- Content -->
	<div id="content" class="grid_11 pull_5">
		<?php the_post(); ?>
		<div id="single">
			<div class="post">
				<h2>404 Error - Not Found</h2>
				<p>The page - <strong><?php echo $_SERVER['HTTP_HOST'] ?><?php echo $_SERVER['REQUEST_URI'] ?></strong> - does not exist. </p>
				<p>Check the spelling of the address you typed</p>
			</div>
		</div>
	</div><!-- /#content -->
<?php get_footer(); ?>