<?php
/**
 * Widget class.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Widget extends WP_Widget {

    /**
     * Holds the class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 2.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 2.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Constructor. Sets up and creates the widget with appropriate settings.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        $widget_ops = apply_filters( 'optin_monster_widget_ops',
            array(
                'classname'   => 'optin-monster',
                'description' => __( 'Place a OptinMonster optin into a widgetized area.', 'optin-monster' )
            )
        );

        $control_ops = apply_filters( 'optin_monster_widget_control_ops',
            array(
                'id_base' => 'optin-monster',
                'height'  => 350,
                'width'   => 225
            )
        );

        parent::__construct(
            'optin-monster',
            apply_filters( 'optin_monster_widget_name', __( 'OptinMonster', 'optin-monster' ) ),
            $widget_ops,
            $control_ops
        );

    }

    /**
     * Outputs the widget within the widgetized area.
     *
     * @since 2.0.0
     *
     * @param array $args     The default widget arguments.
     * @param array $instance The input settings for the current widget instance.
     *
     * @return void
     */
    public function widget( $args, $instance ) {

        $title    = apply_filters( 'widget_title', $instance['title'] );
        $optin_id = $instance['optin_monster_id'];

        do_action( 'optin_monster_widget_before_output', $args, $instance );

        echo $args['before_widget'];

        do_action( 'optin_monster_widget_before_title', $args, $instance );

        // If a title exists, output it.
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        do_action( 'optin_monster_widget_before_optin', $args, $instance );

        // If a optin has been selected, output it.
        if ( $optin_id ) {
        	// If we are in preview mode, don't output the widget.
        	if ( Optin_Monster_Output::get_instance()->is_preview() ) {
	        	return;
        	}

        	// Grab the optin object. If it does not exist, return early.
        	$optin = absint( $optin_id ) ? Optin_Monster::get_instance()->get_optin( $optin_id ) : Optin_Monster::get_instance()->get_optin_by_slug( $optin_id );
        	if ( ! $optin ) {
	        	return;
        	}

            // Check for split tests.
            $original    = $optin;
            $split_tests = Optin_Monster::get_instance()->get_split_tests( $optin->ID );
            if ( $split_tests ) {
                // Merge the main optin with the split tests, shuffle the array, and set the optin to the first item in the array.
                $split_tests[] = $optin;
                shuffle( $split_tests );
                $optin = $split_tests[0];
            }

            // If a clone is selected but it is not enabled, default back to parent optin.
            $clone = get_post_meta( $optin->ID, '_om_is_clone', true );
            if ( ! empty( $clone ) ) {
                $meta = get_post_meta( $optin->ID, '_om_meta', true );
                if ( empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] ) {
                    $optin = $original;
                }
            }

            // If in test mode but not logged in, skip over the optin.
            $test = get_post_meta( $optin->ID, '_om_test_mode', true );
            if ( ! empty( $test ) && ! is_user_logged_in() ) {
                return;
            }

        	// Load the optin.
            optin_monster( $optin->ID );
        }

        do_action( 'optin_monster_widget_after_optin', $args, $instance );

        echo $args['after_widget'];

        do_action( 'optin_monster_widget_after_output', $args, $instance );

    }

    /**
     * Sanitizes and updates the widget.
     *
     * @since 2.0.0
     *
     * @param array $new_instance The new input settings for the current widget instance.
     * @param array $old_instance The old input settings for the current widget instance.
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {

        // Set $instance to the old instance in case no new settings have been updated for a particular field.
        $instance = $old_instance;

        // Sanitize user inputs.
        $instance['title']            = trim( $new_instance['title'] );
        $instance['optin_monster_id'] = absint( $new_instance['optin_monster_id'] );

        return apply_filters( 'optin_monster_widget_update_instance', $instance, $new_instance );

    }

    /**
     * Outputs the widget form where the user can specify settings.
     *
     * @since 2.0.0
     *
     * @param array $instance The input settings for the current widget instance.
     *
     * @return void
     */
    public function form( $instance ) {

        // Get all available optins and widget properties.
        $optins   = $this->base->get_optins();
        $title    = isset( $instance['title'] ) ? $instance['title'] : '';
        $optin_id = isset( $instance['optin_monster_id'] ) ? $instance['optin_monster_id'] : false;

        do_action( 'optin_monster_widget_before_form', $instance );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'optin-monster' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%;" />
        </p>
        <?php do_action( 'optin_monster_widget_middle_form', $instance ); ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'optin_monster_id' ); ?>"><?php _e( 'Optin', 'optin-monster' ); ?></label>
            <select id="<?php echo esc_attr( $this->get_field_id( 'optin_monster_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'optin_monster_id' ) ); ?>" style="width: 100%;">
                <?php
                foreach ( $optins as $optin ) {
                    $meta = get_post_meta( $optin->ID, '_om_meta', true );

                    // Only allow sidebar types.
                    if ( 'sidebar' != $meta['type'] ) {
                        continue;
                    }

                    // Check if the optin is disabled. If so, display a notice.
                    $disabled = empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] ? true : false;

					// Display disabled or enabled selection.
					if ( $disabled ) {
                    	echo '<option value="' . $optin->ID . '" disabled="disabled"' . selected( $optin->ID, $optin_id, false ) . '>' . $optin->post_title . ' (' . __( 'Not Enabled', 'optin-monster' ) . ')</option>';
                    } else {
	                    echo '<option value="' . $optin->ID . '"' . selected( $optin->ID, $optin_id, false ) . '>' . $optin->post_title . '</option>';
                    }
                }
                ?>
            </select>
        </p>
        <?php
        do_action( 'optin_monster_widget_after_form', $instance );

    }

}