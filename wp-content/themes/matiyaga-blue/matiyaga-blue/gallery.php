<?php wp_reset_query(); ?>
<?php
	$featured = get_option('padd_featured_slug');
	$count = get_option('padd_featured_count');
	$featcat = 1;
	if (!empty($featured)) {
		$temp = get_category_by_slug($featured);
		$featcat = (int) $temp->term_id;
	}
	$count = ((int)$count < 4) ? 4 : $count;
	query_posts('showposts=' . $count . '&cat=' . $featcat);

?>       
         
            <ul id="mycarousel" class="jcarousel-skin-tango">
            	<?php while (have_posts()) : the_post(); ?>

			

                               <li >
                               <a href="<?php the_permalink() ?>">
                               		<img class="header" src="<?php bloginfo('template_url'); ?>/functions/timthumb.php?src=
						<?php echo themefunction_catch_that_image() ?>&amp;w=373&amp;h=132&amp;zc=1" class="full" alt="<?php the_title(); ?>"/>
                        		</a>

                            </li>

                    <?php endwhile; ?>
            
    
            </ul>


<?php wp_reset_query(); ?>