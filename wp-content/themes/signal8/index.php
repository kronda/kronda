<?php  ?>
<?php get_header(); ?>
	<!-- Content -->
	<div id="content" class="grid_11 pull_5">
		<?php $featured = get_featured_posts() ?>
		<?php if ($featured): ?>
			<!-- Featured -->
			<div id="featured">
				<span class="ribbon">featured</span>
				<div class="cl">&nbsp;</div>
				<div class="feat-cont">
					<?php $lc = 1; foreach ($featured as $feat): ?>
					<div class="feat-fragment" style="display: <?php echo $lc==1 ? 'block' : 'none' ?>">
						<div class="image">
							<a href="<?php echo get_permalink($feat->ID) ?>"><img src="<?php echo $feat->feat_image ?>" alt="<?php echo apply_filters('the_title', $feat->post_title) ?>" /></a>
						</div>
						<div class="cnt">
							<h2><a href="<?php echo get_permalink($feat->ID) ?>"><?php echo apply_filters('the_title', $feat->post_title) ?></a></h2>
							<div class="excerpt-handler">
								<?php echo shortalize(get_excerpt(apply_filters('the_content', $feat->post_content)), 30) ?>
							</div>
						</div>
					</div>
					<?php $lc++; endforeach ?>
				</div>
				<div class="cl">&nbsp;</div>
				<?php if (count($featured) > 1): ?>
					<div class="paging">
						<?php for ($i=1; $i<=count($featured); $i++) : ?>
							<a class="<?php echo $i==1 ? 'active' : '' ?>" href="#<?php echo $i ?>"><?php echo $i ?></a>
						<?php endfor; ?>
					</div>
					<script type="text/javascript" charset="utf-8">
						;(function ($) {
							var autp_switch_interval = 6000;
							
							function move_feat_to(idx) {
							    $('.feat-fragment:visible').fadeOut();
						        $('.feat-fragment:eq(' + idx + ')').fadeIn();
						        $('.paging a').removeClass('active').eq(idx).addClass('active');
							}
						    $('.paging a').click(function () {
						    	clearInterval(auto_switch_interval);
						    	auto_switch_interval = setInterval(switch_feat_fragment, autp_switch_interval);
						    	
						        var idx = $('.paging a').index(this);
						        move_feat_to(idx);
						        return false
						    });
						    
						    var auto_switch_interval = undefined;
						    
						    function switch_feat_fragment() {
						        var current = $('.feat-fragment:visible');
						        var next_idx = undefined;
						        if (current.is(':last-child')) {
						        	next_idx = 0;
						        } else {
						        	next_idx = $('.feat-fragment').index(current) + 1;
						        }
						        move_feat_to(next_idx);
						    }
						    
						    auto_switch_interval = setInterval(switch_feat_fragment, autp_switch_interval);
						})(jQuery);
					</script>
				<?php endif ?>
				<div class="cl">&nbsp;</div>
			</div>
			<!-- END Featured -->
		<?php endif ?>
		<div class="posts">
			<div class="cl">&nbsp;</div>
			<?php if (have_posts()) :while (have_posts()) : the_post(); ?>
				<div class="post">
					<?php
					$post_img = get_post_meta(get_the_ID(), 'image', 1);
					?>
					<?php if ($post_img): ?>
						<img src="<?php echo $post_img ?>" />
					<?php endif ?>
					
					<div class="cnt">
						<h3><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h3>
						<div class="excerpt">
							<?php echo shorten_excerpt(!empty($post_img), get_the_content('')); ?>
						</div>
					</div>
					<div class="meta">
						<div class="cnt">
							<div class="cl">&nbsp;</div>
							<a href="<?php the_permalink() ?>" class="more">Read More</a>
							<?php the_tags('<p>', ', ', '</p>') ?>
							<div class="cl">&nbsp;</div>
						</div>
					</div>
				</div>
			<?php endwhile; endif; ?>
			<div class="cl">&nbsp;</div>
			
			<div class="blog-nav">
				<div class="cl">&nbsp;</div>
				<span class="prev"><?php next_posts_link('Older') ?></span>
				<span class="next"><?php previous_posts_link('Newer') ?></span>
				<div class="cl">&nbsp;</div>
			</div>
		</div>
	</div>
	<!-- END Content -->
<?php get_footer(); ?>