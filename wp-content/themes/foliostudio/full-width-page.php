<?php 
/*
Template Name: Full Width Page
*/
get_header(); if (have_posts()) : the_post(); ?>	
	<div class="container">
		<div id="top-text-section">
			<div class="doubleborder">
				<?php 
				$title = get_post_meta(get_the_id(), '_custom_teaser_text', true);
				if ( empty($title) ) {
					$title = get_option('default_teaser_text');
				}
			 	?>
				<h2 class="tc cufon-plain"><?php echo htmlize($title); ?></h2>		
			</div>	
		</div>
		<?php print_breadcrumbs('<div id="pagination">', ' / ', '</div>'); ?>
		<div class="default-page">
			<h2 class="cufon-plain"><?php the_title(); ?></h2>
			<div class="entry">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
<?php endif; get_footer(); ?>	
