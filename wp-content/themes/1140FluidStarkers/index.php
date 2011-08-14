<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query. 
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Starkers
 * @since Starkers 3.0
 */
 

get_header(); ?>

				<div class="row">
					<div class="fourcol">
						<?php get_sidebar(); ?>
					</div>
					<div class="eightcol last">
						<?php
						/* Run the loop to output the posts.
						 * If you want to overload this in a child theme then include a file
						 * called loop-index.php and that will be used instead.
						 */
						 get_template_part( 'loop', 'index' );
						?>
					</div>
				</div>
			
			
			
			<!-- EXAMPLE LAYOUTS, uncomment to view
			
				<style>
					
					.container p {
						font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
						color: #fff;
						line-height: 100px;
						background: #000;
						text-align: center;
						margin: 20px 0 0 0;
					}
				
				</style>
			
				<div class="row">
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="onecol last">
						<p>One</p>
					</div>
				</div>

				<div class="row">
					<div class="twocol">
						<p>Two columns</p>
					</div>
					<div class="twocol">
						<p>Two columns</p>
					</div>
					<div class="twocol">
						<p>Two columns</p>
					</div>
					<div class="twocol">
						<p>Two columns</p>
					</div>
					<div class="twocol">
						<p>Two columns</p>
					</div>
					<div class="twocol last">
						<p>Two columns</p>
					</div>
				</div>

				<div class="row">
					<div class="threecol">
						<p>Three columns</p>
					</div>
					<div class="threecol">
						<p>Three columns</p>
					</div>
					<div class="threecol">
						<p>Three columns</p>
					</div>
					<div class="threecol last">
						<p>Three columns</p>
					</div>
				</div>

				<div class="row">
					<div class="fourcol">
						<p>Four columns</p>
					</div>
					<div class="fourcol">
						<p>Four columns</p>
					</div>
					<div class="fourcol last">
						<p>Four columns</p>
					</div>
				</div>

				<div class="row">
					<div class="onecol">
						<p>One</p>
					</div>
					<div class="elevencol last">
						<p>Eleven columns</p>
					</div>
				</div>

				<div class="row">
					<div class="twocol">
						<p>Two columns</p>
					</div>
					<div class="tencol last">
						<p>Ten columns</p>
					</div>
				</div>

				<div class="row">
					<div class="threecol">
						<p>Three columns</p>
					</div>
					<div class="ninecol last">
						<p>Nine columns</p>
					</div>
				</div>

				<div class="row">
					<div class="fivecol">
						<p>Five columns</p>
					</div>
					<div class="sevencol last">
						<p>Seven columns</p>
					</div>
				</div>

				<div class="row">
					<div class="sixcol">
						<p>Six columns</p>
					</div>
					<div class="sixcol last">
						<p>Six columns</p>
					</div>
				</div>

				<div class="row">
					<div class="sevencol">
						<p>Seven columns</p>
					</div>
					<div class="fivecol last">
						<p>Five columns</p>
					</div>
				</div>

				<div class="row">
					<div class="eightcol">
						<p>Eight columns</p>
					</div>
					<div class="fourcol last">
						<p>Four columns</p>
					</div>
				</div>

				<div class="row">
					<div class="ninecol">
						<p>Nine columns</p>
					</div>
					<div class="threecol last">
						<p>Three columns</p>
					</div>
				</div>

				<div class="row">
					<div class="tencol">
						<p>Ten columns</p>
					</div>
					<div class="twocol last">
						<p>Two columns</p>
					</div>
				</div>

				<div class="row">
					<div class="elevencol">
						<p>Eleven columns</p>
					</div>
					<div class="onecol last">
						<p>One</p>
					</div>
				</div>

				<div class="row">
					<div class="threecol">
						<p>Three columns</p>
					</div>
					<div class="sixcol">
						<p>Six columns</p>
					</div>
					<div class="threecol last">
						<p>Three columns</p>
					</div>
				</div>
				
			-->
			
			
			

<?php get_footer(); ?>