<div class="wrap contango-settings">
  
  <?php 
  /** Get the parent theme data. */
  $contango_theme_data = contango_theme_data();
  screen_icon();
  ?>
  
  <h2><?php echo sprintf( __( '%1$s Theme Settings', 'contango' ), $contango_theme_data['Name'] ); ?></h2>    
  
  <?php settings_errors(); ?>
  
  <div id="contango-pro-wrapper">
    <a href="http://designorbital.com/contango-pro/" class="button button-primary button-hero" target="_blank"><?php _e( 'Contango Pro Features', 'contango' ); ?></a>
    <a href="http://designorbital.com/free-wordpress-themes/" class="button button-hero" target="_blank"><?php _e( 'Our Free Themes', 'contango' ); ?></a>
  </div>
  
  <form action="options.php" method="post" id="contango-form-wrapper">
    
    <div id="contango-form-header" class="contango-clearfix">
      <input type="submit" name="" id="" class="button button-primary" value="<?php _e( 'Save Changes', 'contango' ); ?>">      
    </div>
	
	<?php settings_fields('contango_options_group'); ?>
    
    <div id="contango-sidebar">
      
      <ul id="contango-group-menu">
        <li id="0_section_group_li" class="contango-group-tab-link-li active"><a href="javascript:void(0);" id="0_section_group_li_a" class="contango-group-tab-link-a" data-rel="0"><span><?php _e( 'Blog Settings', 'contango' ); ?></span></a></li>
        <li id="1_section_group_li" class="contango-group-tab-link-li"><a href="javascript:void(0);" id="1_section_group_li_a" class="contango-group-tab-link-a" data-rel="1"><span><?php _e( 'Post Settings', 'contango' ); ?></span></a></li>
        <li id="2_section_group_li" class="contango-group-tab-link-li"><a href="javascript:void(0);" id="2_section_group_li_a" class="contango-group-tab-link-a" data-rel="2"><span><?php _e( 'Footer Settings', 'contango' ); ?></span></a></li>
        <li id="3_section_group_li" class="contango-group-tab-link-li"><a href="javascript:void(0);" id="3_section_group_li_a" class="contango-group-tab-link-a" data-rel="3"><span><?php _e( 'General Settings', 'contango' ); ?></span></a></li>
      </ul>
    
    </div>
    
    <div id="contango-main">
    
      <div id="0_section_group" class="contango-group-tab">
        <?php do_settings_sections( 'contango_section_blog_page' ); ?>
      </div>
      
      <div id="1_section_group" class="contango-group-tab">
        <?php do_settings_sections( 'contango_section_post_page' ); ?>
      </div>
      
      <div id="2_section_group" class="contango-group-tab">
        <?php do_settings_sections( 'contango_section_footer_page' ); ?>
      </div>
      
      <div id="3_section_group" class="contango-group-tab">
        <?php do_settings_sections( 'contango_section_general_page' ); ?>
      </div>
      
    </div>
    
    <div class="clear"></div>
    
    <div id="contango-form-footer" class="contango-clearfix">
      <input type="submit" name="" id="" class="button button-primary" value="<?php _e( 'Save Changes', 'contango' ); ?>">
    </div>
    
  </form>

</div>