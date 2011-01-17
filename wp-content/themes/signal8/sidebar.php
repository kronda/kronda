<!-- Sidebar -->
<div id="sidebar" class="grid_5 push_11">
	<form action="<?php echo get_option('home'); ?>" id="searchform" method="get" role="search">
		<div class="cl">&nbsp;</div>
		<input type="text" class="field" id="s" name="s" value="Find Something Interesting" title="Find Something Interesting" />
		<input type="submit" class="button" value="Search" id="searchsubmit" />
		<div class="cl">&nbsp;</div>
	</form>
	<div class="comm-links">
		<div class="cl">&nbsp;</div>
		<a href="<?php bloginfo('rss_url'); ?>" class="rss">rss</a>
		<?php 
		$twitter_URL = get_option('twitter_URL');
		if ($twitter_URL): ?>
			<a href="<?php echo $twitter_URL ?>" class="twitter" target="_blank">twitter</a>
		<?php endif ?>
		<?php 
		$facebook_URL = get_option('facebook_URL');
		if ($facebook_URL): ?>
			<a href="<?php echo $facebook_URL ?>" class="facebook" target="_blank">facebook</a>
		<?php endif ?>
		<div class="cl">&nbsp;</div>
	</div>

	<?php dynamic_sidebar(1) ?>
</div>