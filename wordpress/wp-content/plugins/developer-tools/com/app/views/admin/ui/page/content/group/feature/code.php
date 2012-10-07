<div class="code_template hidden">
	<div class="close"><a title="<?php _e('Close', 'developer-tools' ) ?>">X</a></div>
	
  <?php if( is_string( $view['code'] ) ) : ?>
    <code><?php print $view['code'] ?></code>
	<?php elseif( is_array( $view['code'] ) ) : foreach( $view['code'] as $description => $code ) : ?>
	  <div class="description"><?php print $description ?></div>
		<code><?php print $code ?></code>
	<?php endforeach; endif; ?>	
	
	<?php if( $view['placement'] ) : ?>
	 <div class="placement"><strong><?php _e( 'Code placement:', 'developer-tools' ) ?> </strong><?php 
	     switch( $view['placement'] )
       {
         case 'inside' :
           _e( 'Inside', 'developer-tools' );
           break;
         case 'right_before' :
           _e( 'Right before', 'developer-tools' );
           break;
         case 'outside' : 
           _e( 'Outside', 'developer-tools' );
       }
	   ?> <a href="http://codex.wordpress.org/The_Loop" target="_blank"><?php _e( 'the WordPress loop', 'developer-tools' ) ?></a>
	 </div>
	<?php endif; ?>
	
  <?php if( $view['link'] ) : ?>	
    <a class="codex_link" href="<?php print $view['link'] ?>" target="_blank"><?php _e( 'More info here', 'developer-tools' ) ?></a>
  <?php endif; ?>
	
</div><!-- .code_template -->