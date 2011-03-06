<form action="<?php print $view['action'] ?>" method="post" target="_blank">
  <input type="hidden" name="HOST" value="<?php print DB_HOST ?>" />
  <input type="hidden" name="USER" value="<?php print DB_USER ?>" />
  <input type="hidden" name="PASS" value="<?php print DB_PASSWORD ?>" />
  <input type="hidden" name="DATABASE" value="<?php print DB_NAME ?>" /><?php /* TODO: this isnt being used yet */ ?>
  <input type="submit" class="button-primary" value="<?php _e( 'Login', 'developer-tools' ) ?>" />
</form>