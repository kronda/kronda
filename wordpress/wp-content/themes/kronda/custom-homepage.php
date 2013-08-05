<?php 
/* Template name: Custom Home Page */
	get_header();
	
?>
	
	<div id="content">

		<div class="column1">
			<div class="flexcontrols">
					<!-- this is populated by the flexslider script -->			
			</div><!-- flexcontrols -->
			<div id="porfolio"><a href="<?php echo get_bloginfo('url'); ?>/portfolio">View All Projects</a></div>
		</div><!-- column1 -->
	
			<div id="slider" class="flexslider column2">
			   	<ul class="slides">				
						
						<li id="jillmalone" class="case_study">	
								<div class="slide_column">
									<div class="img_holder">
										<a href="http://www.jillmalone.com"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/jillmalone.jpg" alt="Jill Malone site" title="Take me to the site!"></a>
									</div><!-- img_holder -->
							
									<div class="checkboxes">
										<dl class="checks">
											<span class="row">
											<dt>Design</dt>
											<dt>HTML</dt>
											<dt>CSS</dt>
											</span>
											<span class="row">
												<dt>jQuery</dt>									
												<dt>Wordpress</dt>
												<dt>PHP</dt>
											</span>
										</dl>
									</div><!-- checkboxes -->
								</div><!-- slide_column -->
							
							<div class="desc">
								<h2>Wordpress Redesign</h2>
								<p>Author Jill Malone wanted a fresh look to her Wordpress website that reflected the sensibility of her work. I designed a custom Wordpress theme and implemented a home page to showcase her books. The site features widgets to facilitate easy updating, and improved search functionality. A tabbed sidebar invites users to explore featured blog posts and the latest visitor comments.<a class="visit" href="http://www.jillmalone.com/">Visit Site</a></p>
							</div><!-- desc -->
							
						</li><!-- jillmalone -->
				
						<li id="pace" class="case_study">
							<div class="slide_column">
								<div class="img_holder">
									<a href="http://waronpink.kronda.com"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/pace.jpg" alt="Pace site" title="Take me to the site!"></a>
								</div><!-- img_holder -->
							
								<div class="checkboxes">
									<dl class="checks">
										<span class="row">
										<dt>Drupal</dt>
										<dt>CSS</dt>
										<dt>Javascript</dt>
										</span>
									</dl>
								</div><!-- checkboxes -->
							</div><!-- slide_column -->
							
							<div class="desc">
								<h2>PACE Drupal Site</h2>
								<p>The PACE development project focused on delivering an attractive and flexible platform using the Drupal content management system to deliver information about their many programs related to improving job opportunities for diverse ethnic communities in the Los Angeles area.<a class="visit" href="http://pacela.org" title="War On Pink">Visit Site</a></p>
							</div><!-- desc -->
						</li><!-- waronpink -->
				

						<li id="sorella" class="case_study">
							<div class="slide_column">
								<div class="img_holder">
									<a href="http://www.sorellaforte.com"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/sorellaforte.jpg" alt="Sorella Forte site" title="Take me to the site!"></a>
								</div><!-- img_holder -->
							
								<div class="checkboxes">
									<dl class="checks">
										<span class="row">
										<dt>Design</dt>
										<dt>HTML</dt>
										<dt>CSS</dt>
										</span>
										<span class="row">
											<dt>Drupal</dt>
											<dt>PHP</dt>
											<dt>Custom Module</dt>
										</span>
									</dl>
								</div><!-- checkboxes -->	
							</div><!-- slide_column -->
							
							<div class="desc">
								<h2>Sorella Forte Custom Drupal site</h2>
								<p>This might come as a surprise, but I'm really into bikes. As I ventured into the world of Drupal, updating my cycling team's outdated website seemed like the perfect match. With a CMS in place, any team member can now contribute blog posts, race reports and photos, keeping the site fresh and up to date. <a class="visit" href="http://www.sorellaforte.com/">Visit Site</a></p>
							</div><!-- desc -->
						</li><!-- sorella -->

						<li id="checafe" class="case_study">
							<div class="slide_column">
								<div class="img_holder">
									<a href="http://www.checafepdx.com"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/checafe.jpg" alt="Che Cafe Foodcart site" title="Take me to the site!"></a>
								</div><!-- img_holder -->
							
								<div class ="checkboxes">
									<dl class="checks">
										<span class="row">
										<dt>Design</dt>
										<dt>HTML</dt>
										<dt>CSS</dt>
										</span>
										<span class="row">
											<dt>jQuery</dt>
											<dt>Responsive<span>mobile design</span></dt>
										</span>
									</dl>
								</div><!-- checkboxes -->
							</div><!-- slide_column -->
							
							<div class="desc">
								<h2>Foodcart Website</h2>
								<p>Finding myself in need of another class project, I approached Ryan of Che Cafe and made him an offer he couldn't refuse. The new site was my attempt to reverse everything I hate about bad restaurant websites and focuses on the goods: Where are you, when are you open and what's on the menu? Twitter integration and a responsive layout were no-brainers.<a class="visit" href="http://checafepdx.com/">Visit Site</a></p>
							</div><!-- desc -->
						</li><!-- checafe -->
				</ul><!-- slides -->
			</div><!--slider -->

	</div><!-- content -->

<?php get_footer();?>