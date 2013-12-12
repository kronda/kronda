<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  
  <div class="entry-meta-group clearfix">
    <div class="grid_2 alpha">
      <?php echo contango_post_date(); ?>
    </div>
    <div class="grid_8 omega">
      <h1 class="entry-title entry-title-single"><?php the_title(); ?></h1>
      <div class="entry-meta">    
		<?php echo contango_post_author() . contango_post_comments() . contango_post_sticky() . contango_post_edit_link(); ?>
      </div><!-- .entry-meta -->
    </div>
  </div>
  
  <?php contango_loop_nav_singular(); ?>
  
  <div class="entry-content entry-attachment clearfix">
  	<p><a href="<?php echo wp_get_attachment_url( $post->ID ); ?>"><?php echo wp_get_attachment_image( $post->ID, 'large' ); ?></a></p>
    <?php the_excerpt(); ?>
  </div> <!-- end .entry-content -->
  
</div> <!-- end #post-<?php the_ID(); ?> .post_class -->

<?php contango_loop_nav_singular_attachment(); ?>

<?php comments_template( '', true ); ?>