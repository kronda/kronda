  </div><!-- #<?php print $view['id'] ?> -->
  <?php if( $view['add_another'] ) : ?>
    <div class="duplicate_feature">
      <a class="button-primary add_another" title="<?php _e( 'Add another', 'developer-tools' ) ?>"><span class="plus_minus">+</span> <?php _e( 'Add another', 'developer-tools' ) ?></a>
    </div>
  <?php endif; ?>
</div><!-- #<?php print $view['name'] ?> -->