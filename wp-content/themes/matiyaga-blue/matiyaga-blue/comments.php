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

<div id="comments">

	<div class="mid">
		<div class="title">
			<h2>Comments</h2>
			<p><?php comments_number('No Responses', 'One Response', '% Responses' );?> to &#8220;<?php the_title(); ?>&#8221;</p>
		</div>
		<div class="details">
		<?php if ( have_comments() ) : ?>
			<div class="navigation">
				<div class="prev"><?php previous_comments_link() ?></div>
				<div class="next"><?php next_comments_link() ?></div>
			</div>
			<ol class="commentlist">
				<?php wp_list_comments('callback=themefunction_comments'); ?>
			</ol>
			<div class="navigation">
				<div class="prev"><?php previous_comments_link() ?></div>
				<div class="next"><?php next_comments_link() ?></div>
			</div>
		<?php endif; ?>
		</div>
	</div>

</div>

<div id="respond">

	<div class="mid">
		<div class="title">
			<h2>Write a Comment</h2>
		</div>
		
		<div class="details">
			<?php if ('open' == $post->comment_status) : ?>
			<div class="cancel-comment-reply">
				<small><?php cancel_comment_reply_link(); ?></small>
			</div>
				<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
			<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">logged in</a> to post a comment.</p>
				<?php else : ?>
			<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
					<?php if ( $user_ID ) : ?>
				<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a></p>
					<?php else : ?>
				<p class="input">
					<span><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" <?php if ($req) echo "aria-required='true'"; ?> class="required" minlength="3" />
                    
                    </span>
					<label for="author"><small>Name <?php if ($req) echo "(required at least 3 characters)"; ?></small></label>
					<label class="error" generated="true" for="author">This field is required.</label>    
					                
				</p>
				<p class="input">
					<span><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" <?php if ($req) echo "aria-required='true'"; ?> class="required email"/></span>
					<label for="email"><small>Mail (will not be published) <?php if ($req) echo "(required)"; ?></small></label>
					<label class="error" generated="true" for="email">This field is required.</label>    
				</p>
				<p class="input">
					<span><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" class="url"/></span>
					<label for="url"><small>Website</small></label>
					<label class="error" generated="true" for="url">Valid URL.</label>    
				</p>
					<?php endif; ?>
				<!--<p><small><strong>XHTML:</strong> You can use these tags: <code><?php echo allowed_tags(); ?></code></small></p>-->
				<p class="textarea"><span><textarea name="comment" id="comment" cols="50%" rows="10" tabindex="4"></textarea></span></p>
				<p>
					<button name="submit" type="submit" id="submit" tabindex="5">Submit Comment</button>
					<?php comment_id_fields(); ?>
				</p>
				<?php do_action('comment_form', $post->ID); ?>
			</form>
				<?php endif; // If registration required and not logged in ?>
			<?php else : ?>
			<p class="nocomments">Comments are closed.</p>
			<?php endif; // if you delete this the sky will fall on your head ?>
		</div>
	</div>

</div>


	