<?php 
/* Template name: Custom Home Page */
	get_header();
	
?>
	
	<div id="content">
		
		<div id="slider">
		   	<ul>				
				<li id="jillmalone" class="case_study">
					<div class="img_holder">
						<a href="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/jill_bg.jpg"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/jill.jpg" width="300" height="600" alt="Jill"></a>
					</div><!-- img_holder -->
					
					<div class="checkboxes">
						<dl class="checks">
							<dt>Design</dt>
							<dt>HTML</dt>
							<dt>CSS</dt>
							<span class="row2">
								<dt>jQuery</dt>									
								<dt>Wordpress</dt>
								<dt>PHP</dt>
							</span>
						</dl>
					</div><!-- checkboxes -->
					
					<div class="desc">
						<h2>Wordpress Redesign</h2>
						<p>Author Jill Malone wanted a fresh look to her Wordpress website that reflected the sensiblity of her work. I designed a custom Wordpress theme and implemented a home page to showcase her books. The site features widgets to facilitate easy updating, and new and improved search functionality. A tabbed sidebar invites users to explore featured blog posts and the latest visitor comments.<a class="visit" href="http://www.jillmalone.com/">Visit Site</a></p>
					</div>
				</li><!-- jillmalone -->
				
				<li id="waronpink" class="case_study">
					<div class="img_holder">
						<a href="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/wop_bg.jpg"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/wop.jpg" width="300" height="600" alt="Jill"></a>
					</div><!-- img_holder -->
					<div class="checkboxes">
						<dl class="checks">
							<dt>HTML</dt>
							<dt>CSS</dt>
							<dt>PHP</dt>
							<span class="row2">
								<dt>MYSQL</dt>										
							</span>
						</dl>
					</div><!-- checkboxes -->
					
					<div class="desc">
						<h2>PHP Class Final Project</h2>
						<p>In my first PHP class, I took on the challenge of coding my own custom blog from scratch. It includes an admin area to create new posts and takes comments from visitors. Let's just say I have deep respect for the fine developers of Wordpress! <a class="visit" href="http://waronpink.kronda.com/" title="War On Pink">Visit Site</a></p>
					</div>
				</li><!-- waronpink -->
				

				<li id="sorella" class="case_study">
					<div class="img_holder">
						<a href="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/sorella_bg.jpg"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/sorella.jpg" width="300" height="600" alt="Sorella"></a>
					</div><!-- img_holder -->
					
					<div class="checkboxes">
						<dl class="checks">
							<dt>Design</dt>
							<dt>HTML</dt>
							<dt>CSS</dt>
							<span class="row2">
								<dt>Drupal</dt>
								<dt>PHP</dt>
								<dt>Custom Module</dt>
							</span>
						</dl>
					</div><!-- checkboxes -->
					
					<div class="desc">
						<h2>Sorella Forte Custom Drupal site</h2>
						<p>This might come as a surprise, but I'm really into bikes. As I ventured into the world of Drupal, updating my cycling team's outdated website seemed like the perfect match. With a CMS in place, any team member can now contribute blog posts, race reports and photos, keeping the site fresh and up to date. <a class="visit" href="http://www.sorellaforte.com/">Visit Site</a></p>
					</div>
				</li><!-- sorella -->

				<li id="checafe" class="case_study">
					<div class="img_holder">
						<a href="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/che_bg.jpg"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/checafe.jpg" width="300" height="600" alt="Jill"></a>
					</div><!-- img_holder -->
					
					<div class ="checkboxes">
						<dl class="checks">
							<dt>Design</dt>
							<dt>HTML</dt>
							<dt>CSS</dt>
							<span class="row2">
								<dt>jQuery</dt>
								<dt>Optimized<span>for mobile</span></dt>
							</span>
						</dl>
					</div><!-- checkboxes -->
					
					<div class="desc">
						<h2>Foodcart Website</h2>
						<p>Finding myself in need of another class project, I approached Ryan of Che Cafe and made him an offer he couldn't refuse. The new site was my attempt to reverse everything I hate about bad restaurant websites and focuses on the goods: Where are you, when are you open and what's on the menu? Twitter integration and mobile optimized layout were no-brainers.<a class="visit" href="http://checafepdx.kronda.com/">Visit Site</a></p>
					</div>
				</li><!-- checafe -->
				
				<li id="trendless" class="case_study">
					<div class="img_holder">
						<a href="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/trendless_bg.jpg"><img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/trendless.jpg" width="300" height="600" alt="Jill"></a>
					</div><!-- img_holder -->
					
					<div class="checkboxes">
						<dl class="checks">
							<dt>Product Curation</dt>
							<dt>Hosting</dt>
							<dt>Facebook Integration</dt>
						</dl>
					</div><!-- checkboxes -->
					
					<div class="desc">
						<h2>Rapid Web Development Final Project</h2>
						<p>Team projects in school settings can be a hit or miss affair. In my Rapid Web Development class I was priviledged to team up with a hard working group that brought tons of skill and commitment to the table. The challenge: a dynamic and restful Threadless-themed microsite that highlights products we think are cool and creates buzz using Facebook integration.<a class="visit" href="http://trendlesspdx.kronda.com/">Visit Site</a></p>
					</div>
				</li><!-- trendless -->
			
			</ul>
		</div><!--slider -->
		
		<div id="showbusy">
			<p>Busy Meter</p>
		</div><!-- showbusy -->
		
	</div><!-- content -->


<?php get_footer();?>