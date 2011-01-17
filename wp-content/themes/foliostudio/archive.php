<?php get_header(); ?>
	<div class="container">
		<div id="top-text-section">
			<div class="doubleborder">
				<h2 class="cufon-plain left"><strong>Archive</strong></h2>		
				<a href="#" class="subscribers right"><strong>21,354</strong><br />Subscribers
					<span class="icon">&nbsp;</span> 
				</a>
				<div class="cl">&nbsp;</div>
			</div>	
		</div>
		
		<?php print_breadcrumbs('<div id="pagination">', ' / ', '</div>'); ?>
		<div id="content">
			<?php if (have_posts()): $loopID = 0; $post = $posts[0]; ?>
				<div class="archive-per-month doubleborder">
					<div class="top-info doubleborder">
						<h2 class="left">
							<?php if (is_category()) { ?>
								<?php single_cat_title(); ?>
							<?php } elseif( is_tag() ) { ?>
								<?php single_tag_title(); ?>
							<?php } elseif (is_day()) { ?>
								<?php the_time('F jS, Y'); ?>
							<?php } elseif (is_month()) { ?>
								<?php the_time('F Y'); ?>
							<?php } elseif (is_year()) { ?>
								<?php the_time('Y'); ?>
							<?php } elseif (is_author()) { ?>
								Author Archive
							<?php } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
								Blog Archives
							<?php } ?>
						</h2>
						<a href="<?php echo get_permalink(get_page_id_by_path('archive')); ?>" class="btn-archive notext right">all archive</a>
						<div class="cl">&nbsp;</div>
					</div>
					<div class="post-archive-list">
						<?php while(have_posts()): the_post(); $loopID++; ?>
						    <div class="item <?php echo ($loopID % 2 == 0) ? 'right' : 'left'; ?>">
						    	<div class="image">
						    		<a href="<?php the_permalink(); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/archive-list-pic.jpg" alt="" /></a>
									<a href="<?php the_permalink(); ?>#comments" class="comment-num"><?php comments_number('0', '1', '%'); ?></a>
						    	</div>
						    	<div class="content">
						    		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						    		<div class="post-info">
						    			Posted on <?php the_time('F d, Y'); ?><br />by <?php the_author_posts_link(); ?> in <?php the_category(', '); ?>
						    		</div>
						    	</div>
						    	<div class="cl">&nbsp;</div>
						    </div>
						<?php if ($loopID % 2 == 0): ?>
							<div class="cl">&nbsp;</div>
						<?php endif; ?>
					    <?php endwhile; ?>
					    <div class="cl">&nbsp;</div>
					</div>
					<div class="pages">
						<?php display_navigation(); ?>
					</div>
				</div>									
				<?php 
					// Get Prev/Next month links
					$current_month = get_the_time('n');
					$current_year = get_the_time('Y');
					$arch_flat = array();
					
					$arch_tree = f_get_archive_tree(true);
					
					foreach ($arch_tree as $year => $months) {
						foreach ($months as $month => $num_posts) {
							$arch_flat[] = $year . '-' . $month;
						}
					}
					foreach ($arch_flat as $key => $year_month) {
						if ( $year_month == $current_year . '-' . $current_month ) {
							list($next_year, $next_month)  = explode('-', $arch_flat[$key + 1]);
							list($prev_year, $prev_month)  = explode('-', $arch_flat[$key - 1]);
							break;
						}
					}
				?>
				<div class="archive-navigation">
					<?php if ( isset($prev_year) && isset($prev_month) ): ?>
						<a href="<?php echo get_month_link($prev_year, $prev_month); ?>" class="btn-prev-month notext">prev month</a>
					<?php endif; ?>
					<?php if ( isset($next_year) && isset($next_month) ): ?>
						<a href="<?php echo get_month_link($next_year, $next_month); ?>" class="btn-next-month notext">next month</a>
					<?php endif; ?>
					<div class="cl">&nbsp;</div>
				</div>
			<?php endif ?>

		</div>
		<?php get_sidebar(); ?>
		<div class="cl">&nbsp;</div>
	</div>
<?php get_footer(); ?>