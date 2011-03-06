<div class="feature_buttons">
	
	<?php if( $view['advanced_show'] ) : ?>
	  <a class="button-primary show_advanced <?php print $view['advanced_show'] ?>"><?php _e( 'Advanced Fields', 'developer-tools' ) ?></a>
	<?php endif; ?>
	
	<?php if( $view['code_show'] ) : ?>
	 <a class="button-primary code_button <?php print $view['code_show'] ?>"><?php _e( 'Template Code', 'developer-tools' ) ?></a>
  <?php endif; ?>	

  <?php if( $view['remove_button'] ) : ?>
    <a class="button-primary remove_group <?php print $view['remove_button'] ?>"><?php _e( 'Remove', 'developer-tools' ) ?></a>
  <?php endif; ?> 
	
</div><!-- .feature_buttons -->