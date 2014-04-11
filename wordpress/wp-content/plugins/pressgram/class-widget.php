<?php
/*
Code borrowed and modified from Clean Social Share Widget by Josh Broton
Author URI: http://joshbroton.com
License: GPL2
*/

class Pressgram_Widget extends WP_Widget {

	// constructor
	public function __construct() {
		parent::WP_Widget( false, $name = __( 'Pressgram', 'pressgram-locale') );
	}

	// widget form creation
	public function form( $instance ) {
		// Check values
		if( $instance ) {
			// Set variables from properties inputs
			$title = esc_attr( $instance['title'] );
			$columns = esc_attr( $instance['columns'] );
			$count = esc_attr( $instance['count'] );
			$linkto = esc_attr( $instance['linkto'] );
			$queryby = esc_attr( $instance['queryby'] );
		} else {
			$title = '';
			$columns = '2';
			$count = '4';
			$linkto = 'parent';
			$queryby = 'image';
		}
		?>
		<div class="wrap">
			<ul>
				<li>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'pressgram-locale' ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				</li>
				<li>
					Number of columns: 
					<select id="<?php echo $this->get_field_id('columns'); ?>" name="<?php echo $this->get_field_name('columns'); ?>">
						<option <?php selected( $instance['columns'], '1'); ?> value="1">1</option>
					    <option <?php selected( $instance['columns'], '2'); ?> value="2">2</option>
					    <option <?php selected( $instance['columns'], '3'); ?> value="3">3</option>
						<option <?php selected( $instance['columns'], '4'); ?> value="4">4</option>
					</select>
				</li>
				<li>
					Number of images: 
					<select id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>">
						<option <?php selected( $instance['count'], '1'); ?> value="1">1</option>
					    <option <?php selected( $instance['count'], '2'); ?> value="2">2</option>
					    <option <?php selected( $instance['count'], '3'); ?> value="3">3</option>
						<option <?php selected( $instance['count'], '4'); ?> value="4">4</option>
						<option <?php selected( $instance['count'], '5'); ?> value="5">5</option>
					    <option <?php selected( $instance['count'], '6'); ?> value="6">6</option>
					    <option <?php selected( $instance['count'], '7'); ?> value="7">7</option>
						<option <?php selected( $instance['count'], '8'); ?> value="8">8</option>
						<option <?php selected( $instance['count'], '9'); ?> value="9">9</option>
					    <option <?php selected( $instance['count'], '10'); ?> value="10">10</option>
					    <option <?php selected( $instance['count'], '11'); ?> value="11">11</option>
						<option <?php selected( $instance['count'], '12'); ?> value="12">12</option>
						<option <?php selected( $instance['count'], '13'); ?> value="13">13</option>
					    <option <?php selected( $instance['count'], '14'); ?> value="14">14</option>
					    <option <?php selected( $instance['count'], '15'); ?> value="15">15</option>
						<option <?php selected( $instance['count'], '16'); ?> value="16">16</option>
					</select>
				</li>
			</ul>
		</div>

		<?php
	}

	// widget update
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		// Fields
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['columns'] = strip_tags($new_instance['columns']);
		$instance['count'] = strip_tags($new_instance['count']);

		return $instance;
	}

	// widget display
	public function widget( $args, $instance ) {
		extract( $args );
		// these are the widget options
		$title = apply_filters( 'widget_title', $instance['title'] );
		$columns = $instance['columns'];
		$count = $instance['count'];

		// Variable used to build content string
		$widget_content = '';

		// Set image size
		switch ( $columns ) {
			case '1':
				$img_size = array( 350, 350 );
				break;
			case '2':
				$img_size = array( 175, 175 );
				break;
			case '3':
				$img_size = array( 125, 125 );
				break;
			case '4':
				$img_size = array( 100, 100 );
				break;
			default:
				break;
		}

		// Display the widget
		echo $before_widget;
		echo '<div class="widget-text wp_widget_plugin_box pressgram-widget-wrapper">';

		// Check if title is set
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		// The arguments
		$args = array(
			'post_type'      => get_post_types( array( 'public' => TRUE ) ),
			'meta_key'       => '_pressgram_post',
			'meta_value'     => TRUE,
			'posts_per_page' => $count,
			);

		// Get the pressgram posts
		$pressgram_array = get_posts( $args );

		// The Loop to link images to their post
		foreach ( $pressgram_array as $pressgram_post ) {

			// Do this if featured image is set
			if ( '' != get_the_post_thumbnail( $pressgram_post->ID ) ) {
				$widget_content .= '<a class="pressgram_widget_text" href="' . get_permalink( $pressgram_post->ID ) . '" title="' . esc_attr( $pressgram_post->post_title ) . '">' . get_the_post_thumbnail( $pressgram_post->ID, $img_size ) . '</a>';
			} else { // Otherwise
				// Get the first attachment image
				$attachment = get_children( "post_parent=$pressgram_post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );  // get child attachments of type image
					
				// Get post ID of attachment
				$attachment_ID = current( array_keys( $attachment ) );

				$widget_content .= '<a class="pressgram_widget_text" href="' . get_permalink( $pressgram_post->ID ) . '" title="' . esc_attr( $pressgram_post->post_title ) . '">' . wp_get_attachment_image( $attachment_ID, $img_size, FALSE, array( 'class' => 'wp-post-image' ) ) . '</a>';
			}
		}

		// Output content
		echo '<div class="pressgram-links count_' . $count . ' columns_' . $columns . '">';
		echo $widget_content . '</div></div>';
		echo $after_widget;
	}
}

function pressgram_widget_styles() {
	wp_enqueue_style( 'pressgram-widget-style', PRESSGRAM_DIR_URL . 'css/pressgram-widget.css', array(), PRESSGRAM_VERSION, 'screen' );
}

add_action( 'wp_enqueue_scripts', 'pressgram_widget_styles' );

function pressgram_register_widget() {
	register_widget( 'Pressgram_Widget' );
}

// register widget
add_action( 'widgets_init', 'pressgram_register_widget' );

?>