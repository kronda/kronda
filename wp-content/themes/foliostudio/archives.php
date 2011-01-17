<?php
/*
Template Name: Archives
*/
?>
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
							<div id="pagination">
								<a href="<?php echo get_option('home'); ?>">Home</a> / <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>">Blog</a> / <a href="#" class="active">Archive</a>
							</div>
							<div id="content">
							<?php  
							$archive_tree = f_get_archive_tree();
							$month_names = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "Novemeber", "December");
							?>
								<ul class="archive-list">
									<?php $i=0; foreach ($archive_tree as $year => $months): $i++ ?>
								    <li <?php if($i==1) echo 'class="active"'; ?>><a href="<?php echo get_year_link( $year ) ?>" class="bullet"><?php echo $year ?></a>
								    	<ul>
								    		<?php foreach ($months as $month => $posts): ?>
								    	    	<li><a href="<?php echo get_month_link( $year, $month ) ?>"><?php echo $month_names[intval($month)-1] ?> <span><?php echo $posts ?></span></a></li>
								    		<?php endforeach ?>
								    	</ul>
								    </li>
									<?php endforeach ?>
								</ul>
							</div>
							<div id="sidebar">
								<?php get_sidebar(); ?>
							</div>
							<div class="cl">&nbsp;</div>
						</div>
<?php get_footer(); ?>