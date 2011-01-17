<?php
/*
Template Name: Archives
*/
?>

<?php get_header(); ?>
<!-- Content -->
<div id="content" class="grid_11 pull_5">
	<?php the_post(); ?>
	<div id="single">
		<div class="post">
			<div class="entry">
				<h3>Search:</h3>
				<?php get_search_form(); ?><br />
				
				<h3>Archives by Month:</h3>
				<ul>
					<?php wp_get_archives('type=monthly'); ?>
				</ul>
				<h3>Archives by Subject:</h3>
				<ul>
					<?php wp_list_categories(); ?>
				</ul>
			</div>
		</div>
	</div>
</div><!-- /#content -->
<?php get_footer(); ?>
