<?php if ( !in_array( get_theme_mod( 'theme_layout' ), array( '1c', '1c-narrow' ) ) ) : // If not a one-column layout. ?>

	<aside <?php hybrid_attr( 'sidebar', 'primary' ); ?>>

		<?php if ( is_active_sidebar( 'primary' ) ) : // If the sidebar has widgets. ?>

			<?php dynamic_sidebar( 'primary' ); // Displays the primary sidebar. ?>

		<?php else : // If the sidebar has no widgets. ?>


		<?php endif; // End widgets check. ?>

	</aside><!-- #sidebar-primary -->

<?php endif; // End layout check. ?>