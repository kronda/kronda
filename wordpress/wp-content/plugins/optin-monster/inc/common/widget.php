<?php
/**
 * Widget class.
 *
 * @since 1.0.0
 *
 * @package optin_monster
 * @author  Thomas Griffin
 */
class optin_monster_widget extends WP_Widget {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Constructor. Sets up and creates the widget with appropriate settings.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = optin_monster::get_instance();

        $widget_ops = apply_filters( 'optin_monster_widget_ops',
            array(
                'classname'   => 'optin-monster',
                'description' => __( 'Place a widget-ready OptinMonster optin into a widgetized area.', 'optin-monster' )
            )
        );

        $control_ops = apply_filters( 'optin_monster_widget_control_ops',
            array(
                'id_base' => 'optin-monster',
                'height'  => 350,
                'width'   => 225
            )
        );

        $this->WP_Widget( 'optin-monster', apply_filters( 'optin_monster_widget_name', __( 'OptinMonster', 'optin-monster' ) ), $widget_ops, $control_ops );

    }

    /**
     * Outputs the widget within the widgetized area.
     *
     * @since 1.0.0
     *
     * @param array $args     The default widget arguments.
     * @param array $instance The input settings for the current widget instance.
     */
    public function widget( $args, $instance ) {

        // Extract arguments into variables.
        extract( $args );

        $title    = apply_filters( 'widget_title', $instance['title'] );
        $optin_id = $instance['optin_monster_id'];

        do_action( 'optin_monster_widget_before_output', $args, $instance );

        echo $before_widget;

        do_action( 'optin_monster_widget_before_title', $args, $instance );

        // If a title exists, output it.
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }

        do_action( 'optin_monster_widget_before_optin', $args, $instance );

        // If an optin has been selected, output it.
        if ( $optin_id ) {
            optin_monster_tag( $optin_id );
        }

        do_action( 'optin_monster_widget_after_optin', $args, $instance );

        echo $after_widget;

        do_action( 'optin_monster_widget_after_output', $args, $instance );

    }

    /**
     * Sanitizes and updates the widget.
     *
     * @since 1.0.0
     *
     * @param array $new_instance The new input settings for the current widget instance.
     * @param array $old_instance The old input settings for the current widget instance.
     */
    public function update( $new_instance, $old_instance ) {

        // Set $instance to the old instance in case no new settings have been updated for a particular field.
        $instance = $old_instance;

        // Sanitize user inputs.
        $instance['title']            = trim( $new_instance['title'] );
        $instance['optin_monster_id'] = trim( $new_instance['optin_monster_id'] );

        return apply_filters( 'optin_monster_widget_update_instance', $instance, $new_instance );

    }

    /**
     * Outputs the widget form where the user can specify settings.
     *
     * @since 1.0.0
     *
     * @param array $instance The input settings for the current widget instance.
     */
    public function form( $instance ) {

        // Get all avilable optins and widget properties.
        $optin_items = get_posts(
            array(
                'post_type' => 'optin',
                'posts_per_page' => -1,
                'post_status' => 'any',
                'no_found_rows' => true,
                'cache_results' => false,
                'nopaging' => true
            )
        );
        $optins = array();
        foreach ( (array) $optin_items as $optin ) {
            $meta = get_post_meta( $optin->ID, '_om_meta', true );
            if ( 'sidebar' !== $meta['type'] && 'post' !== $meta['type'] ) {
                continue;
            }
            $optins[] = $optin;
        }
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
                    echo '<option value="' . $optin->post_name . '"' . selected( $optin->post_name, $optin_id, false ) . '>' . $optin->post_title . '</option>';
                }
                ?>
            </select>
        </p>
        <?php
        do_action( 'optin_monster_widget_after_form', $instance );

    }

}