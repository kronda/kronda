<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div class="cl">&nbsp;</div>
			<?php 
			$teaser = get_post_meta( get_the_ID(), 'teaser_text', true);
			if ( empty($teaser) ) {
				$teaser = get_option('teaser_text'); // TODO
			}
			?>
			<h2 id="tagline"><?php echo $teaser ?></h2>
		</div>
	</div>
	<div id="content">
		<div id="main">
			<div class="shell">
				<div class="breadcrumbs">
					<?php print_breadcrumbs() ?>
				</div>
				<div id="blog">
					<div id="widecolumn">
						<div class="post post-single">
							<h2 class="post-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h2>
							<p class="postmetadata"><a href="comments" class="comments"><?php comments_number('No comments', '1 Comment', '% Comments'); ?></a> by <?php the_author_link(); ?> on <?php the_time('F d, Y') ?></p>
							
							<?php  
							$image = get_post_meta(get_the_ID(), '_custom_post_image_r', true);
							if ( empty($image) ) {
								$image = get_post_meta(get_the_ID(), '_custom_post_image', true);
							}
							?>
							<?php if ( !empty($image) ): ?>
								<a href="<?php the_permalink() ?>" class="img">
									<img src="<?php echo get_upload_url(); ?>/<?php echo $image ?>" alt="" />
								</a>
								<div class="shadow notext">&nbsp;</div>
							<?php endif ?>
							
							<div class="entry">
								<?php the_content() ?>
							</div>	
							<div class="bottom-meta" > 
								<p class="fr"><span>Category: <?php the_category( ','); ?></span></p>
								<p><?php the_tags('<strong>Tags :</strong> ', ',') ?></p>
								<div class="cl">&nbsp;</div>
							</div>
							<?php if (get_option('enable_author_box') == 'y'): ?>
							<div class="about-author">
								<div class="bottom">
									<div class="top">
										<a href="<?php echo get_the_author_meta('url') ?>" class="img">
											<?php echo get_avatar(get_the_author_id(), 69, get_bloginfo('stylesheet_directory') . '/images/blank.gif') ?>
										</a>
										<div class="hld">
											<h4><strong>About The Author</strong> - <?php the_author_meta( 'display_name' ); ?> </h4>
											<p><?php the_author_meta('description'); ?> </p>
										</div>
										<div class="cl">&nbsp;</div>
									</div>
								</div>
							</div>
							<?php endif ?>
							
							<div class="share">
								<span class="notext share-h">Share It To The World!</span>
								<div class="links">
									<a href="http://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink($post->ID)) ?>&t=<?php echo urlencode(get_the_title()) ?>" class="facebook">facebook</a>
									<a href="http://twitter.com/home?status=<?php echo urlencode('Currently reading ' . get_tiny_url(get_permalink(get_the_ID()))) ?>" title="Click to share this post on Twitter" class="twitter">twitter</a>
									<a href="http://digg.com/submit?url=<?php echo urlencode(get_permalink($post->ID)) ?>" class="digg">digg</a>
									<a href="http://delicious.com/save" onclick="window.open('http://delicious.com/save?v=5&noui&jump=close&url='+encodeURIComponent(location.href)+'&title='+encodeURIComponent(document.title), 'delicious','toolbar=no,width=550,height=550'); return false;" class="delicious">delicious</a>
									<a href="http://www.stumbleupon.com/toolbar/badge_click.php?r=<?php echo urlencode(get_permalink($post->ID)) ?>" class="stumbleupon">stumble upon</a>
									<a href="http://www.reddit.com/submit" onclick="window.location = 'http://www.reddit.com/submit?url=' + encodeURIComponent(window.location); return false" class="reddit">reddit</a>
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<?php if ( function_exists('wp_related_posts') ): ?>
								<?php apollo_wp_related_posts(); ?>
							<?php endif ?>
						</div>
					<?php comments_template(); ?>
					</div>
						<?php get_sidebar() ?>
					<div class="cl">&nbsp;</div>
				</div>
			</div>
		</div>
		<div id="bottom">
			<div class="shell">
				<?php get_bottom_sidebar(); ?>
				<div class="cl">&nbsp;</div>
			</div>
		</div>
	</div>
<?php endwhile; else: ?>
			<div class="cl">&nbsp;</div>
			<h2 id="tagline">We would Love to Hear Form You! Get in Touch with Us Now</h2>
		</div>
	</div>
	<div id="content">
		<div id="main">
			<div class="shell">
				<div class="breadcrumbs">
					<a href="#">Home</a> &rsaquo; Blog
				</div>
				<div id="blog">
					<div id="widecolumn">
						<h2 class="center page-title post-title">Error 404 - Not Found</h2>
						<p>Please check the URL for proper spelling and capitalization. If you're having trouble locating a destination, try visiting the <a href="<?php echo get_option('home') ?>">home page</a>.</p>
						<div class="cl">&nbsp;</div>
					</div>
					<?php get_sidebar() ?>
					<div class="cl">&nbsp;</div>
				</div>
			</div>
		</div>
		<div id="bottom">
			<div class="shell">
				<?php get_bottom_sidebar(); ?>
				<div class="cl">&nbsp;</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php get_footer(); ?>