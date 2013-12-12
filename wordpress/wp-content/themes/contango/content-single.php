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
  
  <div class="entry-content clearfix">
  	<?php the_content(); ?>
  </div> <!-- end .entry-content -->
  
  <?php echo contango_link_pages(); ?>
  
  <div class="entry-meta-bottom">
  <?php echo contango_post_category() . contango_post_tags(); ?>
  </div><!-- .entry-meta -->

</div> <!-- end #post-<?php the_ID(); ?> .post_class -->

<?php contango_author(); ?> 

<?php comments_template( '', true ); ?>