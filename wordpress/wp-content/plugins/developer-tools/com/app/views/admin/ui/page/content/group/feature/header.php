<div id="<?php print $view['name'] ?>" class="feature<?php print $view['hide'] ?>">
	
  <?php if( $view['information'] ) : ?>
   <div class="feature_information hidden"><span class="information">i</span><?php print $view['information'] ?></div>
  <?php endif; ?>	
	
  <?php if( $view['description'] ) : ?>
   <div class="feature_description"><?php print $view['description'] ?></div>
  <?php endif; ?>
	
	<div id="<?php print $view['id'] ?>" class="<?php print $view['class'] ?>">