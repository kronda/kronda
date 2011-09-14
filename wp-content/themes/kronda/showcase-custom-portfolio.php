<?php
/**
 * Template Name: Custom Showcase Template
 * Description: A Page Template that showcases portfolio pieces, 
 *
 * The showcase template in Twenty Eleven consists of a featured posts section using sticky posts,
 * another recent posts area (with the latest post shown in full and the rest as a list)
 * and a left sidebar holding aside posts.
 *
 * We are creating two queries to fetch the proper posts and a custom widget for the sidebar.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
get_header(); ?>

		<div id="primary">
			
			<div id="content" role="main">
				
				<ul class="portfolio floatcontainer">
					<li class="floatcontainer">
						<h2>PACE: Pacific Asian Consortium in Employment</h2>
						<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/portfolio/pace.jpg" alt="PACE site">
						<div class="technologies">
							<ul>
								<li>Technical specifications</li>
								<li>Drupal development</li>
								<li>Theming</li>
							</ul>
							
						</div><!-- technologies -->
						
						<div class="description">
							<p>The PACE development project focused on delivering an attractive and flexible platform using the Drupal content management system to deliver information about their many programs related to improving job opportunities for diverse ethnic communities in the Los Angeles area.</p>
							<p>This was the first project I got to see through from beginning to end in my position at Metal Toad Media. I got to delve into the land of views and API's to incorporate outside photos, videos and event feeds.</p>
							<a href="http://www.pacela.org" class="visit external_link" target="_blank">Visit Site</a>
						</div>	
					</li>
					
					<li class="floatcontainer">
						<h2>JillMalone.com</h2>
						<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/portfolio/jillmalone.jpg" alt="Jill Malone.com">
						<div class="technologies">
							<ul>
								<li>Design</li>
								<li>Wordpress Development</li>
								<li>jQuery</li>
							</ul>
							
						</div><!-- technologies -->
						
						<div class="description">
							<p>Author Jill Malone wanted a fresh look to her Wordpress website that reflected the sensibility of her work. I designed a custom Wordpress theme and implemented a home page to showcase her books. The site features widgets to facilitate easy updating, and new and improved search functionality. A tabbed sidebar invites users to explore featured blog posts and the latest visitor comments.</p>
							<a href="http://www.jillmalone.com" class="visit external_link" target="_blank">Visit Site</a>
						</div>	
					</li>
					
					<li class="floatcontainer">
						<h2>Sorella Forte Cycling Club</h2>
						<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/portfolio/sorella.jpg" alt="PACE site">
						<div class="technologies">
							<ul>
								<li>Design</li>
								<li>Drupal Development</li>
								<li>Payment Gateway</li>
							</ul>
							
						</div><!-- technologies -->
						
						<div class="description">
							<p>The Sorella site was what I like to call 'table-licious'. The technologies used were slightly outdated and not easily maintainable. My goal in redesigning the site was to give it a fresh look and take advantage of the Drupal content management system to allow for easy administration by team members. The site now includes team member profiles, a team blog, and complete new member sign up capability with payment via Paypal, among other enhancements.</p>
							<a href="http://www.sorellaforte.com" class="visit external_link" target="_blank">Visit Site</a>
						</div>	
					</li>
					
					<li class="floatcontainer">
						<h2>Che Cafe Food Cart</h2>
						<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/portfolio/checafe.jpg" alt="Che Cafe Food cart site">
						<div class="technologies">
							<ul>
								<li>Design</li>
								<li>Photography</li>
								<li>Development (HTML5)</li>
								<li>Responsive layout</li>
							</ul>
							
						</div><!-- technologies -->
						
						<div class="description">
							<p>Finding myself in need of another class project, I approached Ryan of Che Cafe and made him an offer he couldn't refuse. The new site was my attempt to reverse everything I hate about bad restaurant websites and focuses on the goods: Where are you, when are you open and what's on the menu? Twitter integration and a responsive layout were no-brainers.</p>
							<a href="http://waronpink.kronda.com" class="visit external_link" target="_blank">Visit Site</a>
						</div>	
					</li>
					
					<li class="floatcontainer">
						<h2>Che Cafe Food Cart</h2>
						<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/portfolio/waronpink.jpg" alt="War on Pink: a custom blog project">
						<div class="technologies">
							<ul>
								<li>HTML</li>
								<li>CSS</li>
								<li>Database Design</li>
								<li>PHP</li>
								<li>MySQL</li>
							</ul>
							
						</div><!-- technologies -->
						
						<div class="description">
							<p>In my first PHP class, I took on the challenge of coding my own custom blog from scratch. It includes an admin area to create new posts and takes comments from visitors. In 10 weeks I went from zero PHP knowledge to a functional blog. Doing everything on my own, gave me even more respect for the fine developers at Wordpress.</p>
							<a href="http://www.checafepdx.com" class="visit external_link" target="_blank">Visit Site</a>
						</div>	
					</li>
					
					<li class="floatcontainer">
						<h2>When The Hell Do I Graduate?</h2>					
							<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/portfolio/wdig.jpg" alt="When Do I Graduate app">						
						<div class="technologies">
							<ul>
								<li>HTML</li>
								<li>CSS</li>
								<li>jQuery</li>							
							</ul>
							
						</div><!-- technologies -->
						
						<div class="description">
							<p>This app was inspired by the crime against UX that is the Art Institute of Portland  degree audit page. It's <em>supposed</em> to track your classes and give students some clue when they might graduate. What it actually does is cause a lot of consternation, confusion and head scratching.</p>
							<p>I made the class list into a simple form, gave it a nicer UI and put in a stylish calculator to determine approximate graduation date. On my wish list of things to add when I have time are, better form clicking (the whole square instead of just the checkbox), and a database to save the user information.</p>
							<a href="http://wdig.kronda.com" class="visit external_link" target="_blank">Visit Site</a>
						</div>	
					</li>
					
					<li class="floatcontainer">
						<h2>Trendless PDX</h2>					
							<img src="<?php echo CHILD_TEMPLATE_DIRECTORY;?>/images/portfolio/trendless.jpg" alt="Trendless PDX microsite">						
						<div class="technologies">
							<ul>
								<li>Product Curation</li>
								<li>Server Administration</li>
								<li>CSS</li>							
							</ul>
							
						</div><!-- technologies -->
						
						<div class="description">
							<p>This project was the culmination of my Rapid Web Development class. I worked with a team to create a microsite which featured products that we curated and integrated Facebook comments and Likes on products which then post back to the user's Facebook wall.</p>
							<a href="http://trendlesspdx.kronda.com" class="visit external_link" target="_blank">Visit Site</a>
						</div>	
					</li>
				</ul>
				
			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>