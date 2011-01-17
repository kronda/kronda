<?php 
/*
Template Name: About Page
*/
get_header(); if (have_posts()) : the_post(); ?>	
	<div class="container">
		<div id="top-text-section">
			<div class="doubleborder">
				<?php $title = get_post_meta(get_the_id(), '_about_page_title', true); ?>
				<h2 class="tc cufon-plain"><?php echo htmlize($title); ?></h2>		
			</div>	
		</div>
		<div id="pagination">
			<?php print_breadcrumbs('', ' / ', ''); ?>
		</div>
		<div id="about">
			<h2 class="cufon-plain"><?php the_title(); ?></h2>
			<?php  
			$image = get_post_meta(get_the_id(), '_about_page_image', true);
			if ( !empty($image) ) :
			?>
			<div class="image">
				<a href="#"><img src="<?php echo get_upload_url() ?>/<?php echo $image ?>" alt="" /></a>
			</div>
			<?php endif; ?>
			<div class="post">
				<div class="entry">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
		<?php if (get_post_meta(get_the_id(), '_show_team_members', true) != 'n'): ?>
			<div id="meet-the-team">
				<h2 class="cufon-plain">Meet The Team</h2>
				<?php  
				$members = get_posts('post_type=team_members');
				$i=0;
				foreach ($members as $member): $i++; ?>
				<?php if($i%2) echo '<div class="cl">&nbsp;</div>'; ?>
				<div class="team-member <?php if ($i%2) { echo 'left'; } else { echo 'right'; } ?>">
					<?php  
					$image = get_post_meta($member->ID , '_team_member_image', true);
					if( !empty($image) ):
					?>
					<div class="image">
						<a href="#"><img src="<?php echo geT_upload_url() ?>/<?php echo $image ?>" alt="" /></a>
					</div>
					<?php endif; ?>
					<div class="info">
						<h5><?php echo apply_filters('the_title', $member->post_title); ?></h5>
						<em><?php echo get_post_meta($member->ID, '_position', true); ?></em>
						<p><?php echo nl2br(get_post_meta($member->ID, '_description', true)); ?></p>
						<div class="links">
							<?php 
							$twitter = get_post_meta($member->ID, '_twitter', true);
							$homepage = get_post_meta($member->ID, '_homepage', true);
							if ( !empty( $twitter ) ): ?>
							<a href="http://twitter.com/<?php echo $twitter ?>" class="small-ico-twitter">@<?php echo $twitter ?></a>
							<?php endif ?>
							<?php if ( !empty( $homepage ) ): ?>
							<a href="http://<?php echo $homepage ?>" class="small-ico-home"><?php echo $homepage ?></a>
							<?php endif ?>
						</div>
					</div>
				</div>
				<?php endforeach ?>
				<div class="cl">&nbsp;</div>
			</div>
		<?php endif ?>
	</div>
<?php endif; get_footer(); ?>	