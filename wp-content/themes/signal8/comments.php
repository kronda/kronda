<?php
// Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if ( post_password_required() ) { ?>
		<p class="nocomments">This post is password protected. Enter the password to view comments.</p>
	<?php
		return;
	}
?>

<!-- You can start editing here. -->

<?php if ( have_comments() ) : ?>
	<h3 id="comments">There <?php comments_number('are <span>No Comments', 'is <span>One Comment', 'are <span>% Comments' );?></span> about this post</h3>

	<div class="navigation">
		<div class="alignleft"><?php previous_comments_link() ?></div>
		<div class="alignright"><?php next_comments_link() ?></div>
	</div>

	<ol class="commentlist">
		<?php wp_list_comments('callback=print_comment'); ?>
	</ol>

	<div class="navigation">
		<div class="alignleft"><?php previous_comments_link() ?></div>
		<div class="alignright"><?php next_comments_link() ?></div>
	</div>
 <?php else : // this is displayed if there are no comments so far ?>

	<?php if ( comments_open() ) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments">Comments are closed.</p>

	<?php endif; ?>
<?php endif; ?>

</div><!-- /#single -->
<?php if ( comments_open() ) : ?>

<div id="respond">

<div class="cl">&nbsp;</div>
<?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>
	<p>You must be <a href="<?php echo wp_login_url( get_permalink() ); ?>">logged in</a> to post a comment.</p>
<?php else : ?>
	<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
		<h3>Do you have something to say?</h3>
		<div class="cancel-comment-reply">
			<small><?php cancel_comment_reply_link(); ?></small>
		</div>
		<div class="cl">&nbsp;</div>
		<div class="col">
			<?php if ( is_user_logged_in() ) : ?>
				<p>
					Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>.
					<a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a>
				</p>
			<?php else : ?>
				<label for="author">Name <?php if ($req) echo "*"; ?></label>
				<input type="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" size="22" tabindex="1" class="field" <?php if ($req) echo "aria-required='true'"; ?> />
			
				<label for="email">Email Address (will not be published) <?php if ($req) echo "*"; ?></label>
				<input type="text" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" size="22" tabindex="2" class="field" <?php if ($req) echo "aria-required='true'"; ?> />
			
				<label for="url">Website</label>
				<input type="text" name="url" id="url" class="field" value="<?php echo esc_attr($comment_author_url); ?>" size="22" tabindex="3" />
			<?php endif; ?>
		</div>
		<div class="col omega">
			<label>Comment *</label>
			<textarea name="comment" id="comment" class="field" tabindex="4"></textarea>
		</div>
		
		<div class="cl">&nbsp;</div>
		<input name="submit" type="submit" id="submit" tabindex="5" class="button" value="POST" />
		<div class="cl">&nbsp;</div>
		<?php comment_id_fields(); ?>
		<?php do_action('comment_form', $post->ID); ?>
	</form>
<?php endif; // If registration required and not logged in ?>
</div>

<?php endif; // if you delete this the sky will fall on your head ?>
