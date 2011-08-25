<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

	</div><!-- #main -->

	<footer id="colophon" role="contentinfo">
    <div id="widgets">
        
			<?php
				/* A sidebar in the footer? Yep. You can can customize
				 * your footer with three columns of widgets.
				 */
				get_sidebar( 'footer' );
			?>

      <div id="site-info">
				<a href="<?php echo home_url( '/' ) ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			</div><!-- #site-info -->
			
			<div id="site-generator">
				<?php do_action( 'twentyeleven_credits' ); ?>
				<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'twentyeleven' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'twentyeleven' ); ?>" rel="generator"><?php printf( __( 'Proudly powered by %s', 'twentyeleven' ), 'WordPress' ); ?></a>
			</div><!-- #site-generator -->
			
			<?php if ( is_front_page() ): ?>
				
				<div id="busymeter" class="footercolumn">
					<h3>What Am I Doing?</h3>
					<div>
						<img id="piechart" src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/6_piechart.png" width="170" height="180" alt="Busy Meter Pie Chart" />
						<ul>
							<li>
								<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/1_school.png" width="24" height="20" alt="School: 36%">
								<small>School</small>
							</li>
							<li>
								<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/2_work.png" width="24" height="20" alt="Work: 15%">
								<small>Work</small>
							</li>
							<li>
								<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/3_sleep.png" width="24" height="20" alt="Sleep: 29%">
								<small>Sleep</small>
							</li>
							<li>
								<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/4_fun.png" width="24" height="20" alt="Fun: 4%">
								<small>Fun</small>
							</li>
							<li>
								<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/5_undercats.png" width="24" height="20" alt="Trapped Under Cats: 16%">
								<small>Trapped<span class="block"> Under&nbsp;Cats</span></small>
							</li>
						</ul>
					</div>
										<!-- <p id="gradclock">Time to Graduation: <span id="gradtimer">Clock</span></p> -->
					
				</div><!-- busymeter -->
				
				<div id="social" class="footercolumn">
					<h3>My Hangouts</h3>
					<ul>
						<li class="first"><a href="http://twitter.com/ephanypdx">Twitter</a></li>
						<li><a href="http://www.linkedin.com/in/krondaadair">LinkedIn</a></li>
						<li><a href="http://kronda.tumblr.com/">Tumblr</a></li>
						<li class="last"><a href="http://flickr.com/photos/ephany">Flickr</a></li>
						
					</ul>
				</div><!-- Social -->
				
			
				<div id="blogteaser" class="footercolumn">
					<h3>From The Blog</h3>
				
					<?php show_featured_post($PreFeature = '', $PostFeature = '', $AlwaysShow = false); ?>
				
				</div><!-- blogteaser -->

 				<div id="tweet" class="footercolumn">
					<h3>What Is She Talking About?</h3>
				</div><!-- tweet -->
			<?php endif ?>
			</div><!-- widgets -->
	</footer><!-- #colophon -->


</div><!-- #page -->
</div><!-- wrapper -->
<?php wp_footer(); ?>

</body>
</html>