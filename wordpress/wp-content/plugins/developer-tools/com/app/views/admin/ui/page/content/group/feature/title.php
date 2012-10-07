<a name="<?php print $view['name'] ?>-anchor"></a>
<div class="feature_title<?php print $view['enabled'] ?><?php if ( $view['enabled'] ) print '" title="' . __( 'Feature enabled', 'developer-tools' ); ?>"><span class="enabled_feature"><?php _e( 'Feature enabled', 'developer-tools' ) ?></span><?php print $view['title'] ?>
  <?php if( $view['information'] ) : ?><a class="show_information information" title="<?php _e( 'Feature information', 'developer-tools' ) ?>">i</a><?php endif; ?>
  <span class="toggle_feature">
    <label for="<?php print $view['name'] ?>-hidden"><?php _e( 'Show', 'developer-tools' ) ?></label>
    <input id="<?php print $view['name'] ?>-hidden" type="checkbox" name="show[]" value="<?php print $view['name'] ?>"<?php print $view['checked'] ?> />
  </span>
</div>