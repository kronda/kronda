<div id="theme-widget-about-us-4" class="col widget wide-widget">
	<h3 class="widgettitle">About Us</h3>		
	<div class="about-us-widget-content">
		<a href="#" class="img"><img alt="" src="<?php bloginfo('stylesheet_directory'); ?>/images/about-img.jpg"/></a>
		<div class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</div>
	</div>
</div>
<div id="theme-widget-testimonials-3" class="col widget testimonials-widget">
	<h3 class="widgettitle">Testimonials</h3>			
	<div class="testimonial ">
		<p>Lorem ipsum dolor sit amet, consectetur</p>
	</div>
	<div class="testimonial testimonial-last">
		<p>Sed ut perspiciatis unde omnis iste natus</p>	
	</div>
</div>
<div id="linkcat-2" class="col widget widget_links">
	<h3 class="widgettitle">Blogroll</h3>
	<ul class='xoxo blogroll'>
		<li><a href="http://wordpress.org/development/">Development Blog</a></li>
		<li><a href="http://codex.wordpress.org/">Documentation</a></li>
		<li><a href="http://wordpress.org/extend/plugins/">Plugins</a></li>
		<li><a href="http://wordpress.org/extend/ideas/">Suggest Ideas</a></li>
		<li><a href="http://wordpress.org/support/">Support Forum</a></li>
		<li><a href="http://wordpress.org/extend/themes/">Themes</a></li>
		<li><a href="http://planet.wordpress.org/">WordPress Planet</a></li>
	</ul>
</div>

<?php// $w = new NewsletterContact(); $w->front_end(null, null); ?>
<?php the_widget('NewsletterContact', array('contact-title' => 'Quick Contact', 'newsletter-title'=> 'Join the Newsletter!')); ?>
<div class="cl">&nbsp;</div>