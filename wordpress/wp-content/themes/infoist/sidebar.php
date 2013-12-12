<?php
/**
 * The Sidebar containing the main widget areas.
 */
?>
		<div id="sidebar" class="widget-area col300" role="complementary">
        
        	<?php do_action( 'before_sidebar' ); ?>
            
            <div id="social-media" class="clearfix">
				<?php $social_options = get_option ( 'infoist_theme_social_options' ); ?>
                
                <?php
				if ( !isset($social_options['facebook']) ) {
					$social_options['facebook'] = '';
				} else {
                    echo $social_options['facebook'] ? '<a href="' . $social_options['facebook'] . '" class="infoist-fb" title="' . $social_options['facebook'] . '">Facebook</a>' : '';
                } ?> 
                <?php 
				if ( !isset($social_options['twitter']) ) {
					$social_options['twitter'] = '';
				} else {
                    echo $social_options['twitter'] ? '<a href="' . $social_options['twitter'] . '" class="infoist-tw" title="' . $social_options['twitter'] . '">Twitter</a>' : ''; 
                } ?>   
                <?php 
				if ( !isset($social_options['googleplus']) ) {
					$social_options['googleplus'] = '';
				} else {
                    echo $social_options['googleplus'] ? '<a href="' . $social_options['googleplus'] . '" class="infoist-gp" title="' . $social_options['googleplus'] . '">Google+</a>' : ''; 
                } ?>
                <?php 
				if ( !isset($social_options['pinterest']) ) {
					$social_options['pinterest'] = '';
				} else {
                    echo $social_options['pinterest'] ? '<a href="' . $social_options['pinterest'] . '" class="infoist-pi" title="' . $social_options['pinterest'] . '">Pinterest</a>' : ''; 
                } ?>
                <?php 
				if ( !isset($social_options['linkedin']) ) {
					$social_options['linkedin'] = '';
				} else {
                    echo $social_options['linkedin'] ? '<a href="' . $social_options['linkedin'] . '" class="infoist-li" title="' . $social_options['linkedin'] . '">RSS</a>' : ''; 
                } ?>
			</div>
            
			<?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>

				<aside id="archives" class="widget">
					<h2 class="widget-title"><?php _e( 'Archives', 'infoist' ); ?></h2>
					<ul>
						<?php wp_get_archives( array( 'type' => 'monthly' ) ); ?>
					</ul>
				</aside>

				<aside id="meta" class="widget">
					<h2 class="widget-title"><?php _e( 'Meta', 'infoist' ); ?></h2>
					<ul>
						<?php wp_register(); ?>
						<aside><?php wp_loginout(); ?></aside>
						<?php wp_meta(); ?>
					</ul>
				</aside>

			<?php endif; // end sidebar widget area ?>
		</div><!-- #sidebar .widget-area -->
