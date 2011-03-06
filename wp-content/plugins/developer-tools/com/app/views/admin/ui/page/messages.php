<?php if( $updated ) : ?>
	<div class="message updated">
		<?php foreach( $updated as $update ) : ?>
			<p><?php print $update ?></p>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
<?php if( $errors ) : ?>
	<div class="message error">
		<?php foreach( $errors as $error ) : ?>
			<p><?php print $error ?></p>
		<?php endforeach; ?>
	</div>
<?php endif; ?>