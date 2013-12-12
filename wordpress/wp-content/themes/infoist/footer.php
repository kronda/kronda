
	<footer id="colophon" role="contentinfo">
		<div id="site-generator">
			
			<?php echo __('&copy; ', 'infoist') . esc_attr( get_bloginfo( 'name', 'display' ) );  ?>
            <?php if ( is_front_page() && ! is_paged() ) : ?>
            <?php _e('- Powered by ', 'infoist'); ?><a href="<?php echo esc_url( __( 'http://wordpress.org/', 'infoist' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'infoist' ); ?>"><?php _e('Wordpress' ,'infoist'); ?></a>
			<?php _e(' and ', 'infoist'); ?><a href="<?php echo esc_url( __( 'http://wpthemes.co.nz/', 'infoist' ) ); ?>"><?php _e('WPThemes.co.nz', 'infoist'); ?></a>
            <?php endif; ?>
            
		</div>
	</footer><!-- #colophon -->
</div><!-- #container -->

<?php wp_footer(); ?>

</body>
</html>