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
	
	<aside id="contact" class="orange-gradient floatcontainer">
		<div class="center">
			<div id="resumelink" class="three"><a href="<?php echo get_bloginfo('url'); ?>/wp-content/uploads/2011/09/kronda_adair_resume.pdf">Download my resume</a></div>
			<div id="socialicons" class="three">
					<ul  class="floatcontainer">
						<li>
							</a><p>LinkedIn</p><a href="http://www.linkedin.com/in/krondaadair">
							<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>	/images/socialmedia/linkedin.png" alt="Linkedin"></a>
						</li>
						<li>
							<p>Twitter</p>
							<a href="http://www.twitter.com/ephanypdx"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/socialmedia/twitter.png" alt="Twitter"></a>
						</li>
						<li>
							<p>Google+</p>
							<a href="https://plus.google.com/115239154121685812568"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/socialmedia/google.png" alt="Google+"></a>
						</li>
						<li>
							<p>Flickr</p>
							<a href="http://www.flickr.com/photos/ephany"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/socialmedia/flickr.png" alt="Flickr"></a>
						</li>
						<li>
							<p>Tumblr</p>
							<a href="http://kronda.tumblr.com/" title="140+"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/socialmedia/tumblr.png" alt="Tumblr"></a>
						</li>
				</ul>
			</div><!-- socialicons -->
		
			<div id="contactformlink" class="three"><a href="">Drop me a line</a></div>
		</div><!-- center -->
	</aside><!-- contact -->
	
	<footer id="colophon" role="contentinfo">
			
			<?php
				/* A sidebar in the footer? Yep. You can can customize
				 * your footer with three columns of widgets.
				 */
				get_sidebar( 'footer' );
			?>	 	
		
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

				</div><!-- busymeter -->
				
				
				<div id="supplementary" class="two"> 

					<div id="first" class="widget-area" role="complementary"> 
						<aside id="blogteaser" class="widget widget_tag_cloud">
							<h3>From The Blog</h3>
							<?php show_featured_post($PreFeature = '', $PostFeature = '', $AlwaysShow = false); ?>					
						</aside><!-- blogteaser -->
					</div><!-- #first .widget-area --> 
					
					<div id="second" class="widget-area" role="complementary"> 
						<aside id="tweet" class="widget">
							<h3>What Is She Talking About?</h3>
						</aside><!-- tweet -->
					</div><!-- #second .widget-area --> 
					
				</div><!-- #supplementary -->
				<?php endif; ?>			
				
			<div id="site-generator">
				<?php do_action( 'twentyeleven_credits' ); ?>
				<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'twentyeleven' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'twentyeleven' ); ?>" rel="generator"><?php printf( __( 'Proudly powered by %s', 'twentyeleven' ), 'WordPress' ); ?></a>
			</div><!-- site-generator -->

			<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
			
			<div id="site-info">	
					<?php if (is_front_page() ) : ?>
						<span class="photocredit">Author photo by <a href="http://www.linkedin.com/in/jakobferrier" title="Jakob Ferrier  | LinkedIn">Jakob Ferrier</a></span>
						<?php endif; ?>				
						<span class="copyright">kronda.com is &copy; Kronda Adair 2011</span>
			</div><!-- #site-info -->
			
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>