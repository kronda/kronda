<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  
  <?php if ( 'post' == get_post_type() ) : ?>
  
  <div class="entry-meta-group clearfix">
    <div class="grid_2 alpha">
      <?php echo contango_post_date(); ?>
    </div>
    <div class="grid_8 omega">
      <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr( 'Permalink to %s' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
      <div class="entry-meta">    
		<?php echo contango_post_author() . contango_post_comments() . contango_post_sticky() . contango_post_edit_link(); ?>
      </div><!-- .entry-meta -->
    </div>
  </div>
  
  <?php else: ?>
  
  <h2 class="entry-title entry-title-page"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr( 'Permalink to %s' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
  <?php if ( contango_post_edit_link() != '' ) : ?>  
  <div class="entry-meta entry-meta-page"> 
    <?php echo contango_post_edit_link(); ?> 
  </div>
  <?php endif; ?> 
   
  <?php endif; ?>  
  
  <?php contango_featured_image(); ?>  
  
  <div class="entry-content clearfix">
	<?php contango_post_style(); ?>
  </div> <!-- end .entry-content -->
  
  <?php echo contango_link_pages(); ?>
  
  <?php if ( 'post' == get_post_type() ) : ?>
  <div class="entry-meta-bottom">    
  <?php echo contango_post_category() . contango_post_tags(); ?>    
  </div><!-- .entry-meta-bottom -->
  <?php endif; ?>

</div> <!-- end #post-<?php the_ID(); ?> .post_class -->