<?php
class ContangoAdmin {
		
		/** Constructor Method */
		function __construct() {
	
			/* Hook the settings page function to 'admin_menu'. */
			add_action( 'admin_menu', array( &$this, 'settings_page_init' ) );

			/** Load the admin_init functions. */
			add_action( 'admin_init', array( &$this, 'admin_init' ) );					
	
		}
		
		/** Initializes any admin-related features needed for the framework. */
		function admin_init() {
			
			/** Registers admin JavaScript and Stylesheet files for the framework. */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_register_scripts' ), 1 );
		
			/** Loads admin JavaScript and Stylesheet files for the framework. */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
			
		}
		
		/** Registers admin JavaScript and Stylesheet files for the framework. */
		function admin_register_scripts() {
			
			/** Register Admin Stylesheet */
			wp_register_style( 'contango-admin-css-style', esc_url( CONTANGO_ADMIN_URI . 'style.css' ) );
			
			/** Register Admin Scripts */
			wp_register_script( 'contango-admin-js-contango', esc_url( CONTANGO_ADMIN_URI . 'common.js' ) );
			wp_register_script( 'contango-admin-js-jquery-cookie', esc_url( CONTANGO_JS_URI . 'jquery-cookie/jquery.cookie.js' ) );
			
		}
		
		/** Loads admin JavaScript and Stylesheet files for the framework. */
		function admin_enqueue_scripts() {			
		}
		
		/** Initializes all the theme settings page functionality. This function is used to create the theme settings page */
		function settings_page_init() {
			
			global $contango;
			
			/** Register theme settings. */
			register_setting( 'contango_options_group', 'contango_options', array( &$this, 'contango_options_validate' ) );
			
			/* Create the theme settings page. */
			$contango->settings_page = add_theme_page( 
				esc_html( __( 'Contango Options', 'contango' ) ),	/** Settings page name. */
				esc_html( __( 'Contango Options', 'contango' ) ),	/** Menu item name. */
				$this->settings_page_capability(),				/** Required capability */
				'contango-options', 								/** Screen name */
				array( &$this, 'settings_page' )				/** Callback function */
			);
			
			/* Check if the settings page is being shown before running any functions for it. */
			if ( !empty( $contango->settings_page ) ) {
				
				/** Add contextual help to the theme settings page. */
				add_action( 'load-'. $contango->settings_page, array( &$this, 'settings_page_contextual_help' ) );
				
				/* Load the JavaScript and stylesheets needed for the theme settings screen. */
				add_action( 'admin_enqueue_scripts', array( &$this, 'settings_page_enqueue_scripts' ) );
				
				/** Configure settings Sections and Fileds. */
				$this->settings_sections();
				
				/** Configure default settings. */
				$this->settings_default();				
				
			}
			
		}
		
		/** Returns the required capability for viewing and saving theme settings. */
		function settings_page_capability() {
			return 'edit_theme_options';
		}
		
		/** Displays the theme settings page. */
		function settings_page() {
			require( CONTANGO_ADMIN_DIR . 'page.php' );
		}
		
		/** Text for the contextual help for the theme settings page in the admin. */
		function settings_page_contextual_help() {
			
			/** Get the parent theme data. */
			$theme = contango_theme_data();
			$AuthorURI = $theme['AuthorURI'];
			$ThemeURI = $theme['ThemeURI'];
		
			/** Get the current screen */
			$screen = get_current_screen();
			
			/** Add theme reference help screen tab. */
			$screen->add_help_tab( array(
				
				'id' => 'contango-theme',
				'title' => __( 'Theme Support', 'contango' ),
				'content' => implode( '', file( CONTANGO_ADMIN_DIR . 'help/support.html' ) ),				
				
				)
			);
			
			/** Add license reference help screen tab. */
			$screen->add_help_tab( array(
				
				'id' => 'contango-license',
				'title' => __( 'License', 'contango' ),
				'content' => implode( '', file( CONTANGO_ADMIN_DIR . 'help/license.html' ) ),				
				
				)
			);
			
			/** Add changelog reference help screen tab. */
			$screen->add_help_tab( array(
				
				'id' => 'contango-changelog',
				'title' => __( 'Changelog', 'contango' ),
				'content' => implode( '', file( CONTANGO_ADMIN_DIR . 'help/changelog.html' ) ),				
				
				)
			);
			
			/** Help Sidebar */
			$sidebar = '<p><strong>' . __( 'For more information:', 'contango' ) . '</strong></p>';
			if ( !empty( $AuthorURI ) ) {
				$sidebar .= '<p><a href="' . esc_url( $AuthorURI ) . '" target="_blank">' . __( 'Contango Project', 'contango' ) . '</a></p>';
			}
			if ( !empty( $ThemeURI ) ) {
				$sidebar .= '<p><a href="' . esc_url( $ThemeURI ) . '" target="_blank">' . __( 'Contango Official Page', 'contango' ) . '</a></p>';
			}			
			$screen->set_help_sidebar( $sidebar );
			
		}
		
		/** Loads admin JavaScript and Stylesheet files for displaying the theme settings page in the WordPress admin. */
		function settings_page_enqueue_scripts( $hook ) {
			
			/** Load Scripts For Contango Options Page */
			if( $hook === 'appearance_page_contango-options' ) {
				
				/** Load Admin Stylesheet */
				wp_enqueue_style( 'contango-admin-css-style' );
				
				/** Load Admin Scripts */
				wp_enqueue_script( 'contango-admin-js-contango' );
				wp_enqueue_script( 'contango-admin-js-jquery-cookie' );
				
			}
				
		}
		
		/** Configure settings Sections and Fileds */		
		function settings_sections() {
		
			/** Blog Section */
			add_settings_section( 'contango_section_blog', 'Blog Options', array( &$this, 'contango_section_blog_fn' ), 'contango_section_blog_page' );			
			
			add_settings_field( 'contango_field_nav_style', __( 'Navigation Style', 'contango' ), array( &$this, 'contango_field_nav_style_fn' ), 'contango_section_blog_page', 'contango_section_blog' );
			
			/** Post Section */
			add_settings_section( 'contango_section_post', 'Post Options', array( &$this, 'contango_section_post_fn' ), 'contango_section_post_page' );
			
			add_settings_field( 'contango_field_post_style', __( 'Post Style', 'contango' ), array( &$this, 'contango_field_post_style_fn' ), 'contango_section_post_page', 'contango_section_post' );
			add_settings_field( 'contango_field_featured_image_control', __( 'Post Featured Image', 'contango' ), array( &$this, 'contango_field_featured_image_control_fn' ), 'contango_section_post_page', 'contango_section_post' );
			
			/** Footer Section */
			add_settings_section( 'contango_section_footer', 'Footer Options', array( &$this, 'contango_section_footer_fn' ), 'contango_section_footer_page' );
			
			add_settings_field( 'contango_field_copyright_control', __( 'Use Copyright', 'contango' ), array( &$this, 'contango_field_copyright_control_fn' ), 'contango_section_footer_page', 'contango_section_footer' );
			add_settings_field( 'contango_field_copyright', __( 'Enter Copyright Text', 'contango' ), array( &$this, 'contango_field_copyright_fn' ), 'contango_section_footer_page', 'contango_section_footer' );
			
			/** General Section */
			add_settings_section( 'contango_section_general', 'General Options', array( &$this, 'contango_section_general_fn' ), 'contango_section_general_page' );
			
			add_settings_field( 'contango_field_reset_control', __( 'Reset Theme Options', 'contango' ), array( &$this, 'contango_field_reset_control_fn' ), 'contango_section_general_page', 'contango_section_general' );
		
		}
		
		/** Configure Settings */
		function settings_default() {

			$contango_options = get_option( 'contango_options' );
			if( !is_array( $contango_options ) ) {
				update_option( 'contango_options', contango_settings_default() );
			}
		
		}
		
		/** Contango Pre-defined Range */
		
		/* Boolean Yes | No */		
		function contango_boolean_pd() {			
			return array( 1 => __( 'yes', 'contango' ), 0 => __( 'no', 'contango' ) );		
		}
		
		/* Nav Style Range */		
		function contango_nav_style_pd() {			
			return array( 'numeric' => __( 'Numeric', 'contango' ), 'older-newer' => __( 'Older / Newer', 'contango' ) );			
		}
		
		/* Post Style Range */		
		function contango_post_style_pd() {			
			return array( 'content' => __( 'Content', 'contango' ), 'excerpt' => __( 'Excerpt', 'contango' ) );			
		}
		
		/* Featured Image Range */		
		function contango_featured_image_pd() {			
			return array( 'manual' => __( 'Use Featured Image', 'contango' ), 'auto' => __( 'Use Featured Image Automatically', 'contango' ), 'no' => __( 'No Featured Image', 'contango' ) );			
		}		
		
		/** Contango Options Validation */				
		function contango_options_validate( $input ) {
			
			/** Default */
			$default = contango_settings_default();

			/** Contango Predefined */
			$contango_boolean_pd = $this->contango_boolean_pd();
			$contango_nav_style_pd = $this->contango_nav_style_pd();
			$contango_post_style_pd = $this->contango_post_style_pd();
			$contango_featured_image_pd = $this->contango_featured_image_pd();						
			
			/* Validation: contango_nav_style */
			if ( ! array_key_exists( $input['contango_nav_style'], $contango_nav_style_pd ) ) {
				 $input['contango_nav_style'] = $default['contango_nav_style'];
			}
			
			/* Validation: contango_post_style */			
			if ( ! array_key_exists( $input['contango_post_style'], $contango_post_style_pd ) ) {
				 $input['contango_post_style'] = $default['contango_post_style'];
			}
			
			/* Validation: contango_featured_image_control */			
			if ( ! array_key_exists( $input['contango_featured_image_control'], $contango_featured_image_pd ) ) {
				 $input['contango_featured_image_control'] = $default['contango_featured_image_control'];
			}										
			
			/* Validation: contango_copyright_control */			
			if ( ! array_key_exists( $input['contango_copyright_control'], $contango_boolean_pd ) ) {
				 $input['contango_copyright_control'] = $default['contango_copyright_control'];
			}
			
			/* Validation: contango_copyright */
			if( !empty( $input['contango_copyright'] ) ) {
				$input['contango_copyright'] = htmlspecialchars ( $input['contango_copyright'] );
			}
			
			/* Validation: contango_reset_control */
			if ( ! array_key_exists( $input['contango_reset_control'], $contango_boolean_pd ) ) {
				 $input['contango_reset_control'] = $default['contango_reset_control'];
			}

			/** Reset Logic */
			if( $input['contango_reset_control'] == 1 ) {
				$input = $default;
			}			
			
			return $input;
		
		}
		
		/** Blog Section Callback */				
		function contango_section_blog_fn() {
			echo '<div class="contango-section-desc">
			  <p class="description">'. __( 'Customize your blog by using the following settings.', 'contango' ) .'</p>
			</div>';
		}
		
		/* Nav Style Callback */		
		function contango_field_nav_style_fn() {
			
			$contango_options = contango_get_settings();
			$items = $this->contango_nav_style_pd();
			
			echo '<select id="contango_nav_style" name="contango_options[contango_nav_style]">';
			foreach( $items as $key => $val ) {
			?>
            <option value="<?php echo $key; ?>" <?php selected( $key, $contango_options['contango_nav_style'] ); ?>><?php echo $val; ?></option>
            <?php
			}
			echo '</select>';
			echo '<div><small>'. __( 'Select navigation style.', 'contango' ) .'</small></div>';
		
		}

		/** Post Section Callback */				
		function contango_section_post_fn() {
			echo '<div class="contango-section-desc">
			  <p class="description">'. __( 'Customize your posts by using the following settings.', 'contango' ) .'</p>
			</div>';
		}
		
		/* Post Style Callback */		
		function contango_field_post_style_fn() {
			
			$contango_options = contango_get_settings();
			$items = $this->contango_post_style_pd();
			
			echo '<select id="contango_post_style" name="contango_options[contango_post_style]">';
			foreach( $items as $key => $val ) {
			?>
            <option value="<?php echo $key; ?>" <?php selected( $key, $contango_options['contango_post_style'] ); ?>><?php echo $val; ?></option>
            <?php
			}
			echo '</select>';
			echo '<div><small>'. __( 'Select post style.', 'contango' ) .'</small></div>';
		
		}
		
		/* Featured Image Callback */		
		function contango_field_featured_image_control_fn() {
			
			$contango_options = contango_get_settings();
			$items = $this->contango_featured_image_pd();
			
			echo '<select id="contango_featured_image_control" name="contango_options[contango_featured_image_control]">';
			foreach( $items as $key => $val ) {
			?>
            <option value="<?php echo $key; ?>" <?php selected( $key, $contango_options['contango_featured_image_control'] ); ?>><?php echo $val; ?></option>
            <?php
			}
			echo '</select>';
			echo '<div><small>'. __( '<strong>Use Featured Image:</strong> which is set in the post.', 'contango' ) .'</small></div>';
			echo '<div><small>'. __( '<strong>Use Featured Image Automatically:</strong> from the images uploaded to the post.', 'contango' ) .'</small></div>';
			echo '<div><small>'. __( '<strong>No Featured Image:</strong> for the post.', 'contango' ) .'</small></div>';
		
		}
		
		/** Footer Section Callback */				
		function contango_section_footer_fn() {
			echo '<div class="contango-section-desc">
			  <p class="description">'. __( 'Customize your footer by using the following settings.', 'contango' ) .'</p>
			</div>';
		}
		
		/* Copyright Control Callback */		
		function  contango_field_copyright_control_fn() {
			
			$contango_options = contango_get_settings();
			$items = $this->contango_boolean_pd();
			
			echo '<select id="contango_copyright_control" name="contango_options[contango_copyright_control]">';
			foreach( $items as $key => $val ) {
			?>
            <option value="<?php echo $key; ?>" <?php selected( $key, $contango_options['contango_copyright_control'] ); ?>><?php echo $val; ?></option>
            <?php
			}
			echo '</select>';
			echo '<div><small>'. __( 'Select yes to override default copyright text.', 'contango' ) .'</small></div>';
		
		}
		
		/* Copyright Callback */
		function contango_field_copyright_fn() {
			
			$contango_options = contango_get_settings();
			echo '<textarea type="textarea" id="contango_copyright" name="contango_options[contango_copyright]" rows="7" cols="50">'. esc_html ( $contango_options['contango_copyright'] ) .'</textarea>';
			echo '<div><small>'. __( 'Enter the copyright text.', 'contango' ) .'</small></div>';
			echo '<div><small>Example: <strong>&amp;copy; Copyright '.date('Y').' - &lt;a href="'. esc_url( home_url( '/' ) ) .'"&gt;'. get_bloginfo('name') .'&lt;/a&gt;</strong></small></div>';
		
		}
		
		/** General Section Callback */				
		function contango_section_general_fn() {
			echo '<div class="contango-section-desc">
			  <p class="description">'. __( 'Here are the general settings to customize your blog.', 'contango' ) .'</p>
			</div>';
		}
		
		/* Reset Congrol Callback */		
		function contango_field_reset_control_fn() {
			
			$contango_options = contango_get_settings();			
			$items = $this->contango_boolean_pd();			
			echo '<label><input type="checkbox" id="contango_reset_control" name="contango_options[contango_reset_control]" value="1" /> '. __( 'Reset Theme Options.', 'contango' ) .'</label>';
		
		}
}

/** Initiate Admin */
new ContangoAdmin();