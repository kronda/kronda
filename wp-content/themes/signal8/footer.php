				<div class="cl">&nbsp;</div>
			</div>
			<!-- END Main Part -->			
			<!-- Footer -->
			<div id="footer">
				<!-- Bars -->
				<div class="bars">
					<div class="container_16">
						<?php if(!dynamic_sidebar(2)) : ?>
							<?php /* Default footer */ ?>
							<div class="bar grid_4 comments">
								<div class="bg">
									<h2>Recent Comments</h2>
									<?php
									$recent_comments = get_recent_comments(5);
									?>
									<div class="cnt">
										<?php foreach ($recent_comments as $comment): ?>
											<div class="comment">
												<p class="author"><strong><?php echo $comment->comment_author ?></strong> says,</p>
												<p><?php echo shortalize($comment->comment_content, 10) ?></p>
											</div>
										<?php endforeach ?>
									</div>
								</div>
							</div>
							<div class="bar grid_4 disc-posts">
								<div class="bg">
									<h2>Most Discussed Posts </h2>
									<?php $most_disucussed = get_most_discussed_posts();  ?>
									<div class="cnt">
										<?php foreach ($most_disucussed as $_post): ?>
											<div class="txt">
												<p><a href="<?php echo get_permalink($_post->ID) ?>"><?php echo apply_filters('the_title', $_post->post_title) ?></a></p>
											</div>
										<?php endforeach ?>
									</div>
								</div>
							</div>
							<div class="bar grid_4 flickr-gallery">
								<div class="bg">
									<h2><img src="<?php bloginfo('stylesheet_directory'); ?>/images/ico-flickr.gif" class="ico-flickr" alt="" /> Flickr Gallery</h2>
									<div class="cnt">
										<div class="cl">&nbsp;</div>
										<?php
										$flickr_imgs = get_latest_flickr_images();
										foreach ($flickr_imgs as $flickr_img) : ?>
											<a href="<?php echo $flickr_img['url'] ?>" class="image" title="<?php echo $flickr_img['title'] ?>" target="_balnk"><img src="<?php echo str_replace('_m', '_s', $flickr_img['image']) ?>" width="88px" alt="<?php echo $flickr_img['title'] ?>" /></a>
										<?php endforeach ?>
										<div class="cl">&nbsp;</div>
										<?php
										$flickr_photostream_url = get_option('flickr_photostream_url');
										?>
										<?php if ($flickr_photostream_url): ?>
											<a href="<?php echo $flickr_photostream_url ?>" class="more">View More</a>
										<?php endif ?>
										<div class="cl">&nbsp;</div>
									</div>
								</div>
							</div>
							<div class="bar grid_4 about">
								<div class="bg">
									<h2>About Me</h2>
									<div class="cnt">
										<?php echo get_avatar(get_option('admin_email'), 47) ?>
										<div class="cl">&nbsp;</div>
										<?php echo wpautop(get_usermeta(1, 'description')) ?>
										<div class="cl">&nbsp;</div>
										<?php
										$about_page = get_option('choose_about_page');
										if ($about_page) : ?>
											<a href="<?php echo get_permalink($about_page) ?>" class="more">View More</a>
										<?php endif;?>
										<div class="cl">&nbsp;</div>
									</div>
								</div>
							</div>
						<?php else : ?>
							<script type="text/javascript" charset="utf-8">
							(function($){
								$('#footer .container_16 > div:nth-child(4n+0)').after('<div class="cl" style="height: 15px;">&nbsp;</div>');
							})(jQuery)
							</script>
						<?php endif; ?>
						<div class="cl">&nbsp;</div>
					</div>
					<div class="cl">&nbsp;</div>
				</div>
				
				<!-- END Bars -->
				<div class="powered">
					<div class="container_16">
						<div class="cl">&nbsp;</div>
						<div class="grid_16">
							<div class="cl">&nbsp;</div>
							<p class="right"><?php bloginfo('name'); ?> - <?php bloginfo('description'); ?>. Powered by <a href="http://wordpress.org" target="_blank">WordPress</a>.</p>
							<p>
								<?php $pages = get_pages('sort_column=menu_order&hierarchical=0') ?>
								<?php $loop_counter = 1; foreach ($pages as $_page): ?>
									<a href="<?php echo get_permalink($_page->ID) ?>"><?php echo apply_filters('the_title', $_page->post_title) ?></a>
									<?php if ($loop_counter!=count($pages)): ?>
										<span>|</span>
									<?php endif ?>
								<?php $loop_counter++; endforeach ?>
							</p>
							<div class="cl">&nbsp;</div>
						</div>
						<div class="cl">&nbsp;</div>
					</div>
				</div>
				<!-- Copryrights -->
				<div class="copy">
					<div class="container_16">
						<div class="cl">&nbsp;</div>
						<p class="grid_16">&copy; <?php echo date('Y') ?> All Rights Reserved. <a href="http://www.mojo-themes.com">Premium Wordpress Themes</a> from <a href="http://www.mojo-themes.com">MOJO themes.</a></p>
						<div class="cl">&nbsp;</div>
					</div>
				</div>
				<!-- END Copryrights -->
			</div>
			<!-- END Footer -->
		</div>
		<!-- END Page -->
		<?php wp_footer(); ?>
		<script type="text/javascript" charset="utf-8">
			;(function ($) {
			    $('.widget ul li:last-child, .widget ol li:last-child').addClass('last-child');
			    $('.widgetcontent ul, .widgetcontent ol').each(function() {$(this).parents('div.widgetcontent:eq(0)').css('padding', '0')});
			    
			    var _max = -1;
			    $('#footer .container_16 > div.bar').each(function () {_max =$(this).height() > _max ? $(this).height() : _max}).height(_max);
			})(jQuery);
		</script>
	</body>
</html>