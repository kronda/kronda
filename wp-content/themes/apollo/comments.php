<?php
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');
	
	if ( post_password_required() ) { ?>
		<p class="nocomments">This post is password protected. Enter the password to view comments.</p>
	<?php
		return;
	}
?>

<?php if ( have_comments() ) : ?>
	<div class="post-comments" id="comments">
		<h2>There <?php comments_number('are no Comments', 'is 1 Comment', 'are % Comments' );?> About This Post</h2>
		<?php wp_list_comments('callback=print_comment'); ?>
	
		<div class="navigation">
			<div class="alignleft"><?php previous_comments_link() ?></div>
			<div class="alignright"><?php next_comments_link() ?></div>
		</div>
	</div>
<?php else : ?>
	<?php if ( comments_open() ) : ?>
        <!-- If comments are open, but there are no comments. -->
	 <?php else : // comments are closed ?>
		<p class="nocomments">Comments are closed.</p>
	<?php endif; ?>
<?php endif; ?>

<?php if ( comments_open() ) : ?>
	<div id="respond">
		<h2>Leave Your Comment</h2>
		<?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>
			<p class="note">You must be <a href="<?php echo wp_login_url( get_permalink() ); ?>">logged in</a> to post a comment.</p>
		<?php else : ?>
			<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
				<fieldset>
					<?php if ( is_user_logged_in() ) : ?>
						<p class="note">
							Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. 
							<a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a>
						</p>
					<?php else: ?>
					<div class="col">
						<label for="author">Name <?php if ($req) echo "*"; ?></label>
						<div class="field"><input type="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" tabindex="1" <?php if ($req) echo "aria-required='true'"; ?> /></div>
					</div>
					<div class="col col-mid">
						<label for="email">Email <?php if ($req) echo "*"; ?></label>
						<div class="field"><input type="text" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" tabindex="2" <?php if ($req) echo "aria-required='true'"; ?> /></div>
					</div>
					<div class="col">
						<label for="url">Website <em>(optional)</em></label>
						<div class="field"><input type="text" name="url" id="url" value="<?php echo esc_attr($comment_author_url); ?>" tabindex="3" /></div>
					</div>
					<div class="cl">&nbsp;</div>
					<?php endif; ?>
					
					<label for="comment">Comment *</label>
					<textarea cols="50" rows="5" name="comment" id="comment" tabindex="4"></textarea>
	                <input class="submit" name="submit" type="submit" id="submit" tabindex="5" value="Add Comment" />
				</fieldset>
				<?php comment_id_fields(); ?>
				<?php do_action('comment_form', $post->ID); ?>
			</form>
		<?php endif; ?>
	</div>
<?php endif; ?>