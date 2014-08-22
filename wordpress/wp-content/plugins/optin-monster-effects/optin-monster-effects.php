<?php
/**
 * OptinMonster is the #1 lead generation and email list building tool.
 *
 * @package   OptinMonster
 * @author    Thomas Griffin
 * @license   GPL-2.0+
 * @link      http://optinmonster.com/
 * @copyright 2013 Retyp, LLC. All rights reserved.
 *
 * @wordpress-plugin
 * Plugin Name:  OptinMonster Effects
 * Plugin URI:   http://optinmonster.com/
 * Description:  Adds custom display effects to OptinMonster lightbox popups.
 * Version:      1.0.1
 * Author:       Thomas Griffin
 * Author URI:   http://thomasgriffinmedia.com/
 * Text Domain:  optin-monster-effects
 * Contributors: griffinjt
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:  /lang
 */

add_action( 'init', 'om_effects_automatic_upgrades', 20 );
function om_effects_automatic_upgrades() {

    global $optin_monster_license;

    // Load the plugin updater.
    if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) :
        if ( ! empty( $optin_monster_license['key'] ) ) {
			$args = array(
				'remote_url' 	=> 'http://optinmonster.com/',
				'version' 		=> '1.0.1',
				'plugin_name'	=> 'OptinMonster Effects',
				'plugin_slug' 	=> 'optin-monster-effects',
				'plugin_path' 	=> plugin_basename( __FILE__ ),
				'plugin_url' 	=> WP_PLUGIN_URL . '/optin-monster-effects',
				'time' 			=> 43200,
				'key' 			=> $optin_monster_license['key']
			);

			// Load the updater class.
			$optin_monster_effects_updater = new optin_monster_updater( $args );
		}
    endif;

}

add_filter( 'optin_monster_template_output', 'om_effects_add_effect', 10, 6 );
function om_effects_add_effect( $html, $type, $theme, $base_class, $hash, $optin_id ) {

    // If not a lightbox or canvas popup, do nothing.
    if ( 'lightbox' !== $type && 'canvas' !== $type ) {
        return $html;
    }

    // Prepend the custom effect CSS to the optin if an effect has been chosen.
    $meta = get_post_meta( $optin_id, '_om_meta', true );
    if ( empty( $meta['effect'] ) ) {
        return $html;
    }

    $effect = om_effects_get_effect( $meta['effect'], $type, $theme );
    return $effect . $html;

}

add_action( 'optin_monster_save_design', 'om_effects_save_optin_effects', 10, 4 );
function om_effects_save_optin_effects( $type, $theme, $optin_id, $data ) {

    if ( empty( $data['optin_effect'] ) ) {
        return;
    }

    $meta = get_post_meta( $optin_id, '_om_meta', true );
    $meta['effect'] = esc_attr( $data['optin_effect'] );
    update_post_meta( $optin_id, '_om_meta', $meta );

}

add_action( 'optin_monster_load_theme', 'om_effects_load_effect' );
function om_effects_load_effect( $object ) {

    // If not a lightbox or canvas popup, do nothing.
    $meta = $object->meta;
    if ( 'lightbox' !== $meta['type'] && 'canvas' !== $meta['type'] ) {
        return;
    }

    ob_start();
    ?>
    $('.accordion-area').find('h3:last').next().after('<?php echo om_effects_get_effect_select_dialog( $object ); ?>');
    $('.accordion-area').accordion('refresh');
    <?php
    echo ob_get_clean();

}

add_action( 'optin_monster_design_script', 'om_effects_dynamic_effect' );
function om_effects_dynamic_effect( $object ) {

    // If not a lightbox or canvas popup, do nothing.
    $meta = $object->meta;
    if ( 'lightbox' !== $meta['type'] && 'canvas' !== $meta['type'] ) {
        return;
    }

    ob_start();
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            var theme_type = $('#om-optin-design .optin-item.selected').find('.design-customizer-ui').attr('data-optin-theme');
            $(document).on('change', '#om-effects-select-effect', function(e){
                $('#om-effects-<?php echo $object->type; ?>-' + theme_type).remove();
                $.post(ajaxurl, { action: 'load_optin_effect', type: '<?php echo $object->type; ?>', theme: theme_type, effect: $(this).val() }, function(res){
                    $('#om-<?php echo $object->optin->post_name; ?>').prepend(res);
                }, 'json');
            });
        });
    </script>
    <?php
    echo ob_get_clean();

}

add_action( 'wp_ajax_load_optin_effect', 'om_effects_ajax_effect' );
add_action( 'wp_ajax_nopriv_load_optin_effect', 'om_effects_ajax_effect' );
function om_effects_ajax_effect() {

    $type   = stripslashes( $_POST['type'] );
    $theme  = stripslashes( $_POST['theme'] );
    $effect = stripslashes( $_POST['effect'] );

    die( json_encode( om_effects_get_effect( $effect, $type, $theme ) ) );

}

function om_effects_get_effect_select_dialog( $object ) {

    $meta = get_post_meta( $object->optin->ID, '_om_meta', true );
    $output  = '<h3>' . __( 'Effects', 'optin-monster' ) . '</h3>';
    $output .= '<div class="effects-area">';
        $output .= '<p>';
            $output .= '<label for="om-effects-select-effect">'.__('Optin Display Effect','optin-monster').'</label>';
            $output .= '<select id="om-effects-select-effect" name="optin_effect">';
                $effects = om_effects_get_effect_list();
                foreach ( (array) $effects as $effect ) {
                    $selected = isset( $meta['effect'] ) && $effect['value'] == $meta['effect'] ? ' selected="selected"' : '';
                    $output .= '<option value="' . $effect['value'] . '"' . $selected . '>' . $effect['name'] . '</option>';
                }
            $output .= '</select>';
        $output .= '</p>';
    $output .= '</div>';
    return $output;

}

function om_effects_get_effect_list() {

    $effects = array(
        array(
            'name'  => __( 'No Effect', 'optin-monster' ),
            'value' => 'none'
        ),
        array(
            'name'  => __( 'Bounce', 'optin-monster' ),
            'value' => 'bounce'
        ),
        array(
            'name'  => __( 'Flash', 'optin-monster' ),
            'value' => 'flash'
        ),
        array(
            'name'  => __( 'Pulse', 'optin-monster' ),
            'value' => 'pulse'
        ),
        array(
            'name'  => __( 'Rubber Band', 'optin-monster' ),
            'value' => 'rubberBand'
        ),
        array(
            'name'  => __( 'Shake', 'optin-monster' ),
            'value' => 'shake'
        ),
        array(
            'name'  => __( 'Swing', 'optin-monster' ),
            'value' => 'swing'
        ),
        array(
            'name'  => __( 'Tada', 'optin-monster' ),
            'value' => 'tada'
        ),
        array(
            'name'  => __( 'Wobble', 'optin-monster' ),
            'value' => 'wobble'
        ),
        array(
            'name'  => __( 'Bounce In', 'optin-monster' ),
            'value' => 'bounceIn'
        ),
        array(
            'name'  => __( 'Bounce In (Down)', 'optin-monster' ),
            'value' => 'bounceInDown'
        ),
        array(
            'name'  => __( 'Bounce In (Left)', 'optin-monster' ),
            'value' => 'bounceInLeft'
        ),
        array(
            'name'  => __( 'Bounce In (Right)', 'optin-monster' ),
            'value' => 'bounceInRight'
        ),
        array(
            'name'  => __( 'Bounce In (Up)', 'optin-monster' ),
            'value' => 'bounceInUp'
        ),
        array(
            'name'  => __( 'Flip', 'optin-monster' ),
            'value' => 'flip'
        ),
        array(
            'name'  => __( 'Flip Down', 'optin-monster' ),
            'value' => 'flipInX'
        ),
        array(
            'name'  => __( 'Flip Side', 'optin-monster' ),
            'value' => 'flipInY'
        ),
        array(
            'name'  => __( 'Light Speed', 'optin-monster' ),
            'value' => 'lightSpeedIn'
        ),
        array(
            'name'  => __( 'Rotate', 'optin-monster' ),
            'value' => 'rotateIn'
        ),
        array(
            'name'  => __( 'Rotate (Down Left)', 'optin-monster' ),
            'value' => 'rotateInDownLeft'
        ),
        array(
            'name'  => __( 'Rotate (Down Right)', 'optin-monster' ),
            'value' => 'rotateInDownRight'
        ),
        array(
            'name'  => __( 'Rotate (Up Left)', 'optin-monster' ),
            'value' => 'rotateInUpLeft'
        ),
        array(
            'name'  => __( 'Rotate (Up Right)', 'optin-monster' ),
            'value' => 'rotateInUpRight'
        ),
        array(
            'name'  => __( 'Slide In (Down)', 'optin-monster' ),
            'value' => 'slideInDown'
        ),
        array(
            'name'  => __( 'Slide In (Left)', 'optin-monster' ),
            'value' => 'slideInLeft'
        ),
        array(
            'name'  => __( 'Slide In (Right)', 'optin-monster' ),
            'value' => 'slideInRight'
        ),
        array(
            'name'  => __( 'Roll In', 'optin-monster' ),
            'value' => 'rollIn'
        ),
    );
    return apply_filters( 'optin_monster_effects', $effects );

}

function om_effects_get_effect( $effect, $type, $theme ) {

    $output  = '<style type="text/css" id="om-effects-' . $type . '-' . $theme . '">';

    switch ( $effect ) {
        case 'none' :
            $output .= '';
            break;
        case 'bounce' :
            $output .= '@-webkit-keyframes bounce{0%,100%,20%,50%,80%{-webkit-transform:translateY(0);transform:translateY(0)}40%{-webkit-transform:translateY(-30px);transform:translateY(-30px)}60%{-webkit-transform:translateY(-15px);transform:translateY(-15px)}}@keyframes bounce{0%,100%,20%,50%,80%{-webkit-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}40%{-webkit-transform:translateY(-30px);-ms-transform:translateY(-30px);transform:translateY(-30px)}60%{-webkit-transform:translateY(-15px);-ms-transform:translateY(-15px);transform:translateY(-15px)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-name: bounce;animation-name: bounce;}';
            break;
        case 'flash' :
            $output .= '@-webkit-keyframes flash{0%,100%,50%{opacity:1}25%,75%{opacity:0}}@keyframes flash{0%,100%,50%{opacity:1}25%,75%{opacity:0}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-name: flash;animation-name: flash;}';
            break;
        case 'pulse' :
            $output .= '@-webkit-keyframes pulse{0%{-webkit-transform:scale(1);transform:scale(1)}50%{-webkit-transform:scale(1.1);transform:scale(1.1)}100%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes pulse{0%{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}50%{-webkit-transform:scale(1.1);-ms-transform:scale(1.1);transform:scale(1.1)}100%{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-name: pulse;animation-name: pulse;}';
            break;
        case 'rubberBand' :
            $output .= '@-webkit-keyframes rubberBand{0%{-webkit-transform:scale(1);transform:scale(1)}30%{-webkit-transform:scaleX(1.25) scaleY(0.75);transform:scaleX(1.25) scaleY(0.75)}40%{-webkit-transform:scaleX(0.75) scaleY(1.25);transform:scaleX(0.75) scaleY(1.25)}60%{-webkit-transform:scaleX(1.15) scaleY(0.85);transform:scaleX(1.15) scaleY(0.85)}100%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes rubberBand{0%{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}30%{-webkit-transform:scaleX(1.25) scaleY(0.75);-ms-transform:scaleX(1.25) scaleY(0.75);transform:scaleX(1.25) scaleY(0.75)}40%{-webkit-transform:scaleX(0.75) scaleY(1.25);-ms-transform:scaleX(0.75) scaleY(1.25);transform:scaleX(0.75) scaleY(1.25)}60%{-webkit-transform:scaleX(1.15) scaleY(0.85);-ms-transform:scaleX(1.15) scaleY(0.85);transform:scaleX(1.15) scaleY(0.85)}100%{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-name: rubberBand;animation-name: rubberBand;}';
            break;
        case 'shake' :
            $output .= '@-webkit-keyframes shake{0%,100%{-webkit-transform:translateX(0);transform:translateX(0)}10%,30%,50%,70%,90%{-webkit-transform:translateX(-10px);transform:translateX(-10px)}20%,40%,60%,80%{-webkit-transform:translateX(10px);transform:translateX(10px)}}@keyframes shake{0%,100%{-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}10%,30%,50%,70%,90%{-webkit-transform:translateX(-10px);-ms-transform:translateX(-10px);transform:translateX(-10px)}20%,40%,60%,80%{-webkit-transform:translateX(10px);-ms-transform:translateX(10px);transform:translateX(10px)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-name: shake;animation-name: shake;}';
            break;
        case 'swing' :
            $output .= '@-webkit-keyframes swing{20%{-webkit-transform:rotate(15deg);transform:rotate(15deg)}40%{-webkit-transform:rotate(-10deg);transform:rotate(-10deg)}60%{-webkit-transform:rotate(5deg);transform:rotate(5deg)}80%{-webkit-transform:rotate(-5deg);transform:rotate(-5deg)}100%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}}@keyframes swing{20%{-webkit-transform:rotate(15deg);-ms-transform:rotate(15deg);transform:rotate(15deg)}40%{-webkit-transform:rotate(-10deg);-ms-transform:rotate(-10deg);transform:rotate(-10deg)}60%{-webkit-transform:rotate(5deg);-ms-transform:rotate(5deg);transform:rotate(5deg)}80%{-webkit-transform:rotate(-5deg);-ms-transform:rotate(-5deg);transform:rotate(-5deg)}100%{-webkit-transform:rotate(0deg);-ms-transform:rotate(0deg);transform:rotate(0deg)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-transform-origin:top center;-ms-transform-origin:top center;transform-origin:top center;-webkit-animation-name:swing;animation-name:swing}';
            break;
        case 'tada' :
            $output .= '@-webkit-keyframes tada{0%{-webkit-transform:scale(1);transform:scale(1)}10%,20%{-webkit-transform:scale(0.9) rotate(-3deg);transform:scale(0.9) rotate(-3deg)}30%,50%,70%,90%{-webkit-transform:scale(1.1) rotate(3deg);transform:scale(1.1) rotate(3deg)}40%,60%,80%{-webkit-transform:scale(1.1) rotate(-3deg);transform:scale(1.1) rotate(-3deg)}100%{-webkit-transform:scale(1) rotate(0);transform:scale(1) rotate(0)}}@keyframes tada{0%{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}10%,20%{-webkit-transform:scale(0.9) rotate(-3deg);-ms-transform:scale(0.9) rotate(-3deg);transform:scale(0.9) rotate(-3deg)}30%,50%,70%,90%{-webkit-transform:scale(1.1) rotate(3deg);-ms-transform:scale(1.1) rotate(3deg);transform:scale(1.1) rotate(3deg)}40%,60%,80%{-webkit-transform:scale(1.1) rotate(-3deg);-ms-transform:scale(1.1) rotate(-3deg);transform:scale(1.1) rotate(-3deg)}100%{-webkit-transform:scale(1) rotate(0);-ms-transform:scale(1) rotate(0);transform:scale(1) rotate(0)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:tada;animation-name:tada;}';
            break;
        case 'wobble' :
            $output .= '@-webkit-keyframes wobble{0%{-webkit-transform:translateX(0%);transform:translateX(0%)}15%{-webkit-transform:translateX(-25%) rotate(-5deg);transform:translateX(-25%) rotate(-5deg)}30%{-webkit-transform:translateX(20%) rotate(3deg);transform:translateX(20%) rotate(3deg)}45%{-webkit-transform:translateX(-15%) rotate(-3deg);transform:translateX(-15%) rotate(-3deg)}60%{-webkit-transform:translateX(10%) rotate(2deg);transform:translateX(10%) rotate(2deg)}75%{-webkit-transform:translateX(-5%) rotate(-1deg);transform:translateX(-5%) rotate(-1deg)}100%{-webkit-transform:translateX(0%);transform:translateX(0%)}}@keyframes wobble{0%{-webkit-transform:translateX(0%);-ms-transform:translateX(0%);transform:translateX(0%)}15%{-webkit-transform:translateX(-25%) rotate(-5deg);-ms-transform:translateX(-25%) rotate(-5deg);transform:translateX(-25%) rotate(-5deg)}30%{-webkit-transform:translateX(20%) rotate(3deg);-ms-transform:translateX(20%) rotate(3deg);transform:translateX(20%) rotate(3deg)}45%{-webkit-transform:translateX(-15%) rotate(-3deg);-ms-transform:translateX(-15%) rotate(-3deg);transform:translateX(-15%) rotate(-3deg)}60%{-webkit-transform:translateX(10%) rotate(2deg);-ms-transform:translateX(10%) rotate(2deg);transform:translateX(10%) rotate(2deg)}75%{-webkit-transform:translateX(-5%) rotate(-1deg);-ms-transform:translateX(-5%) rotate(-1deg);transform:translateX(-5%) rotate(-1deg)}100%{-webkit-transform:translateX(0%);-ms-transform:translateX(0%);transform:translateX(0%)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:wobble;animation-name:wobble;}';
            break;
        case 'bounceIn' :
            $output .= '@-webkit-keyframes bounceIn{0%{opacity:0;-webkit-transform:scale(.3);transform:scale(.3)}50%{opacity:1;-webkit-transform:scale(1.05);transform:scale(1.05)}70%{-webkit-transform:scale(.9);transform:scale(.9)}100%{opacity:1;-webkit-transform:scale(1);transform:scale(1)}}@keyframes bounceIn{0%{opacity:0;-webkit-transform:scale(.3);-ms-transform:scale(.3);transform:scale(.3)}50%{opacity:1;-webkit-transform:scale(1.05);-ms-transform:scale(1.05);transform:scale(1.05)}70%{-webkit-transform:scale(.9);-ms-transform:scale(.9);transform:scale(.9)}100%{opacity:1;-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:bounceIn;animation-name:bounceIn;}';
            break;
        case 'bounceInDown' :
            $output .= '@-webkit-keyframes bounceInDown{0%{opacity:0;-webkit-transform:translateY(-2000px);transform:translateY(-2000px)}60%{opacity:1;-webkit-transform:translateY(30px);transform:translateY(30px)}80%{-webkit-transform:translateY(-10px);transform:translateY(-10px)}100%{-webkit-transform:translateY(0);transform:translateY(0)}}@keyframes bounceInDown{0%{opacity:0;-webkit-transform:translateY(-2000px);-ms-transform:translateY(-2000px);transform:translateY(-2000px)}60%{opacity:1;-webkit-transform:translateY(30px);-ms-transform:translateY(30px);transform:translateY(30px)}80%{-webkit-transform:translateY(-10px);-ms-transform:translateY(-10px);transform:translateY(-10px)}100%{-webkit-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:bounceInDown;animation-name:bounceInDown;}';
            break;
        case 'bounceInLeft' :
            $output .= '@-webkit-keyframes bounceInLeft{0%{opacity:0;-webkit-transform:translateX(-2000px);transform:translateX(-2000px)}60%{opacity:1;-webkit-transform:translateX(30px);transform:translateX(30px)}80%{-webkit-transform:translateX(-10px);transform:translateX(-10px)}100%{-webkit-transform:translateX(0);transform:translateX(0)}}@keyframes bounceInLeft{0%{opacity:0;-webkit-transform:translateX(-2000px);-ms-transform:translateX(-2000px);transform:translateX(-2000px)}60%{opacity:1;-webkit-transform:translateX(30px);-ms-transform:translateX(30px);transform:translateX(30px)}80%{-webkit-transform:translateX(-10px);-ms-transform:translateX(-10px);transform:translateX(-10px)}100%{-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:bounceInLeft;animation-name:bounceInLeft;}';
            break;
        case 'bounceInRight' :
            $output .= '@-webkit-keyframes bounceInRight{0%{opacity:0;-webkit-transform:translateX(2000px);transform:translateX(2000px)}60%{opacity:1;-webkit-transform:translateX(-30px);transform:translateX(-30px)}80%{-webkit-transform:translateX(10px);transform:translateX(10px)}100%{-webkit-transform:translateX(0);transform:translateX(0)}}@keyframes bounceInRight{0%{opacity:0;-webkit-transform:translateX(2000px);-ms-transform:translateX(2000px);transform:translateX(2000px)}60%{opacity:1;-webkit-transform:translateX(-30px);-ms-transform:translateX(-30px);transform:translateX(-30px)}80%{-webkit-transform:translateX(10px);-ms-transform:translateX(10px);transform:translateX(10px)}100%{-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:bounceInRight;animation-name:bounceInRight;}';
            break;
        case 'bounceInUp' :
            $output .= '@-webkit-keyframes bounceInUp{0%{opacity:0;-webkit-transform:translateY(2000px);transform:translateY(2000px)}60%{opacity:1;-webkit-transform:translateY(-30px);transform:translateY(-30px)}80%{-webkit-transform:translateY(10px);transform:translateY(10px)}100%{-webkit-transform:translateY(0);transform:translateY(0)}}@keyframes bounceInUp{0%{opacity:0;-webkit-transform:translateY(2000px);-ms-transform:translateY(2000px);transform:translateY(2000px)}60%{opacity:1;-webkit-transform:translateY(-30px);-ms-transform:translateY(-30px);transform:translateY(-30px)}80%{-webkit-transform:translateY(10px);-ms-transform:translateY(10px);transform:translateY(10px)}100%{-webkit-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:bounceInUp;animation-name:bounceInUp;}';
            break;
        case 'flip' :
            $output .= '@-webkit-keyframes flip{0%{-webkit-transform:perspective(800px) translateZ(0) rotateY(0) scale(1);transform:perspective(800px) translateZ(0) rotateY(0) scale(1);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}40%{-webkit-transform:perspective(800px) translateZ(150px) rotateY(170deg) scale(1);transform:perspective(800px) translateZ(150px) rotateY(170deg) scale(1);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}50%{-webkit-transform:perspective(800px) translateZ(150px) rotateY(190deg) scale(1);transform:perspective(800px) translateZ(150px) rotateY(190deg) scale(1);-webkit-animation-timing-function:ease-in;animation-timing-function:ease-in}80%{-webkit-transform:perspective(800px) translateZ(0) rotateY(360deg) scale(.95);transform:perspective(800px) translateZ(0) rotateY(360deg) scale(.95);-webkit-animation-timing-function:ease-in;animation-timing-function:ease-in}100%{-webkit-transform:perspective(800px) translateZ(0) rotateY(360deg) scale(1);transform:perspective(800px) translateZ(0) rotateY(360deg) scale(1);-webkit-animation-timing-function:ease-in;animation-timing-function:ease-in}}@keyframes flip{0%{-webkit-transform:perspective(800px) translateZ(0) rotateY(0) scale(1);-ms-transform:perspective(800px) translateZ(0) rotateY(0) scale(1);transform:perspective(800px) translateZ(0) rotateY(0) scale(1);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}40%{-webkit-transform:perspective(800px) translateZ(150px) rotateY(170deg) scale(1);-ms-transform:perspective(800px) translateZ(150px) rotateY(170deg) scale(1);transform:perspective(800px) translateZ(150px) rotateY(170deg) scale(1);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}50%{-webkit-transform:perspective(800px) translateZ(150px) rotateY(190deg) scale(1);-ms-transform:perspective(800px) translateZ(150px) rotateY(190deg) scale(1);transform:perspective(800px) translateZ(150px) rotateY(190deg) scale(1);-webkit-animation-timing-function:ease-in;animation-timing-function:ease-in}80%{-webkit-transform:perspective(800px) translateZ(0) rotateY(360deg) scale(.95);-ms-transform:perspective(800px) translateZ(0) rotateY(360deg) scale(.95);transform:perspective(800px) translateZ(0) rotateY(360deg) scale(.95);-webkit-animation-timing-function:ease-in;animation-timing-function:ease-in}100%{-webkit-transform:perspective(800px) translateZ(0) rotateY(360deg) scale(1);-ms-transform:perspective(800px) translateZ(0) rotateY(360deg) scale(1);transform:perspective(800px) translateZ(0) rotateY(360deg) scale(1);-webkit-animation-timing-function:ease-in;animation-timing-function:ease-in}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-backface-visibility: visible;-ms-backface-visibility: visible;backface-visibility: visible;-webkit-animation-name:flip;animation-name:flip;}';
            break;
        case 'flipInX' :
            $output .= '@-webkit-keyframes flipInX{0%{-webkit-transform:perspective(800px) rotateX(90deg);transform:perspective(800px) rotateX(90deg);opacity:0}40%{-webkit-transform:perspective(800px) rotateX(-10deg);transform:perspective(800px) rotateX(-10deg)}70%{-webkit-transform:perspective(800px) rotateX(10deg);transform:perspective(800px) rotateX(10deg)}100%{-webkit-transform:perspective(800px) rotateX(0deg);transform:perspective(800px) rotateX(0deg);opacity:1}}@keyframes flipInX{0%{-webkit-transform:perspective(800px) rotateX(90deg);-ms-transform:perspective(800px) rotateX(90deg);transform:perspective(800px) rotateX(90deg);opacity:0}40%{-webkit-transform:perspective(800px) rotateX(-10deg);-ms-transform:perspective(800px) rotateX(-10deg);transform:perspective(800px) rotateX(-10deg)}70%{-webkit-transform:perspective(800px) rotateX(10deg);-ms-transform:perspective(800px) rotateX(10deg);transform:perspective(800px) rotateX(10deg)}100%{-webkit-transform:perspective(800px) rotateX(0deg);-ms-transform:perspective(800px) rotateX(0deg);transform:perspective(800px) rotateX(0deg);opacity:1}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-backface-visibility: visible;-ms-backface-visibility: visible;backface-visibility: visible;-webkit-animation-name:flipInX;animation-name:flipInX;}';
            break;
        case 'flipInY' :
            $output .= '@-webkit-keyframes flipInY{0%{-webkit-transform:perspective(800px) rotateY(90deg);transform:perspective(800px) rotateY(90deg);opacity:0}40%{-webkit-transform:perspective(800px) rotateY(-10deg);transform:perspective(800px) rotateY(-10deg)}70%{-webkit-transform:perspective(800px) rotateY(10deg);transform:perspective(800px) rotateY(10deg)}100%{-webkit-transform:perspective(800px) rotateY(0deg);transform:perspective(800px) rotateY(0deg);opacity:1}}@keyframes flipInY{0%{-webkit-transform:perspective(800px) rotateY(90deg);-ms-transform:perspective(800px) rotateY(90deg);transform:perspective(800px) rotateY(90deg);opacity:0}40%{-webkit-transform:perspective(800px) rotateY(-10deg);-ms-transform:perspective(800px) rotateY(-10deg);transform:perspective(800px) rotateY(-10deg)}70%{-webkit-transform:perspective(800px) rotateY(10deg);-ms-transform:perspective(800px) rotateY(10deg);transform:perspective(800px) rotateY(10deg)}100%{-webkit-transform:perspective(800px) rotateY(0deg);-ms-transform:perspective(800px) rotateY(0deg);transform:perspective(800px) rotateY(0deg);opacity:1}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-backface-visibility: visible;-ms-backface-visibility: visible;backface-visibility: visible;-webkit-animation-name:flipInY;animation-name:flipInY;}';
            break;
        case 'lightSpeedIn' :
            $output .= '@-webkit-keyframes lightSpeedIn{0%{-webkit-transform:translateX(100%) skewX(-30deg);transform:translateX(100%) skewX(-30deg);opacity:0}60%{-webkit-transform:translateX(-20%) skewX(30deg);transform:translateX(-20%) skewX(30deg);opacity:1}80%{-webkit-transform:translateX(0%) skewX(-15deg);transform:translateX(0%) skewX(-15deg);opacity:1}100%{-webkit-transform:translateX(0%) skewX(0deg);transform:translateX(0%) skewX(0deg);opacity:1}}@keyframes lightSpeedIn{0%{-webkit-transform:translateX(100%) skewX(-30deg);-ms-transform:translateX(100%) skewX(-30deg);transform:translateX(100%) skewX(-30deg);opacity:0}60%{-webkit-transform:translateX(-20%) skewX(30deg);-ms-transform:translateX(-20%) skewX(30deg);transform:translateX(-20%) skewX(30deg);opacity:1}80%{-webkit-transform:translateX(0%) skewX(-15deg);-ms-transform:translateX(0%) skewX(-15deg);transform:translateX(0%) skewX(-15deg);opacity:1}100%{-webkit-transform:translateX(0%) skewX(0deg);-ms-transform:translateX(0%) skewX(0deg);transform:translateX(0%) skewX(0deg);opacity:1}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:lightSpeedIn;animation-name:lightSpeedIn;-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}';
            break;
        case 'rotateIn' :
            $output .= '@-webkit-keyframes rotateIn{0%{-webkit-transform-origin:center center;transform-origin:center center;-webkit-transform:rotate(-200deg);transform:rotate(-200deg);opacity:0}100%{-webkit-transform-origin:center center;transform-origin:center center;-webkit-transform:rotate(0);transform:rotate(0);opacity:1}}@keyframes rotateIn{0%{-webkit-transform-origin:center center;-ms-transform-origin:center center;transform-origin:center center;-webkit-transform:rotate(-200deg);-ms-transform:rotate(-200deg);transform:rotate(-200deg);opacity:0}100%{-webkit-transform-origin:center center;-ms-transform-origin:center center;transform-origin:center center;-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0);opacity:1}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:rotateIn;animation-name:rotateIn;}';
            break;
        case 'rotateInDownLeft' :
            $output .= '@-webkit-keyframes rotateInDownLeft{0%{-webkit-transform-origin:left bottom;transform-origin:left bottom;-webkit-transform:rotate(-90deg);transform:rotate(-90deg);opacity:0}100%{-webkit-transform-origin:left bottom;transform-origin:left bottom;-webkit-transform:rotate(0);transform:rotate(0);opacity:1}}@keyframes rotateInDownLeft{0%{-webkit-transform-origin:left bottom;-ms-transform-origin:left bottom;transform-origin:left bottom;-webkit-transform:rotate(-90deg);-ms-transform:rotate(-90deg);transform:rotate(-90deg);opacity:0}100%{-webkit-transform-origin:left bottom;-ms-transform-origin:left bottom;transform-origin:left bottom;-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0);opacity:1}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:rotateInDownLeft;animation-name:rotateInDownLeft;}';
            break;
        case 'rotateInDownRight' :
            $output .= '@-webkit-keyframes rotateInDownRight{0%{-webkit-transform-origin:right bottom;transform-origin:right bottom;-webkit-transform:rotate(90deg);transform:rotate(90deg);opacity:0}100%{-webkit-transform-origin:right bottom;transform-origin:right bottom;-webkit-transform:rotate(0);transform:rotate(0);opacity:1}}@keyframes rotateInDownRight{0%{-webkit-transform-origin:right bottom;-ms-transform-origin:right bottom;transform-origin:right bottom;-webkit-transform:rotate(90deg);-ms-transform:rotate(90deg);transform:rotate(90deg);opacity:0}100%{-webkit-transform-origin:right bottom;-ms-transform-origin:right bottom;transform-origin:right bottom;-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0);opacity:1}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:rotateInDownRight;animation-name:rotateInDownRight;}';
            break;
        case 'rotateInUpLeft' :
            $output .= '@-webkit-keyframes rotateInUpLeft{0%{-webkit-transform-origin:left bottom;transform-origin:left bottom;-webkit-transform:rotate(90deg);transform:rotate(90deg);opacity:0}100%{-webkit-transform-origin:left bottom;transform-origin:left bottom;-webkit-transform:rotate(0);transform:rotate(0);opacity:1}}@keyframes rotateInUpLeft{0%{-webkit-transform-origin:left bottom;-ms-transform-origin:left bottom;transform-origin:left bottom;-webkit-transform:rotate(90deg);-ms-transform:rotate(90deg);transform:rotate(90deg);opacity:0}100%{-webkit-transform-origin:left bottom;-ms-transform-origin:left bottom;transform-origin:left bottom;-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0);opacity:1}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:rotateInUpLeft;animation-name:rotateInUpLeft;}';
            break;
        case 'rotateInUpRight' :
            $output .= '@-webkit-keyframes rotateInUpRight{0%{-webkit-transform-origin:right bottom;transform-origin:right bottom;-webkit-transform:rotate(-90deg);transform:rotate(-90deg);opacity:0}100%{-webkit-transform-origin:right bottom;transform-origin:right bottom;-webkit-transform:rotate(0);transform:rotate(0);opacity:1}}@keyframes rotateInUpRight{0%{-webkit-transform-origin:right bottom;-ms-transform-origin:right bottom;transform-origin:right bottom;-webkit-transform:rotate(-90deg);-ms-transform:rotate(-90deg);transform:rotate(-90deg);opacity:0}100%{-webkit-transform-origin:right bottom;-ms-transform-origin:right bottom;transform-origin:right bottom;-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0);opacity:1}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:rotateInUpRight;animation-name:rotateInUpRight;}';
            break;
        case 'slideInDown' :
            $output .= '@-webkit-keyframes slideInDown{0%{opacity:0;-webkit-transform:translateY(-2000px);transform:translateY(-2000px)}100%{-webkit-transform:translateY(0);transform:translateY(0)}}@keyframes slideInDown{0%{opacity:0;-webkit-transform:translateY(-2000px);-ms-transform:translateY(-2000px);transform:translateY(-2000px)}100%{-webkit-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:slideInDown;animation-name:slideInDown;}';
            break;
        case 'slideInLeft' :
            $output .= '@-webkit-keyframes slideInLeft{0%{opacity:0;-webkit-transform:translateX(-2000px);transform:translateX(-2000px)}100%{-webkit-transform:translateX(0);transform:translateX(0)}}@keyframes slideInLeft{0%{opacity:0;-webkit-transform:translateX(-2000px);-ms-transform:translateX(-2000px);transform:translateX(-2000px)}100%{-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:slideInLeft;animation-name:slideInLeft;}';
            break;
        case 'slideInRight' :
            $output .= '@-webkit-keyframes slideInRight{0%{opacity:0;-webkit-transform:translateX(2000px);transform:translateX(2000px)}100%{-webkit-transform:translateX(0);transform:translateX(0)}}@keyframes slideInRight{0%{opacity:0;-webkit-transform:translateX(2000px);-ms-transform:translateX(2000px);transform:translateX(2000px)}100%{-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:slideInRight;animation-name:slideInRight;}';
            break;
        case 'rollIn' :
            $output .= '@-webkit-keyframes rollIn{0%{opacity:0;-webkit-transform:translateX(-100%) rotate(-120deg);transform:translateX(-100%) rotate(-120deg)}100%{opacity:1;-webkit-transform:translateX(0px) rotate(0deg);transform:translateX(0px) rotate(0deg)}}@keyframes rollIn{0%{opacity:0;-webkit-transform:translateX(-100%) rotate(-120deg);-ms-transform:translateX(-100%) rotate(-120deg);transform:translateX(-100%) rotate(-120deg)}100%{opacity:1;-webkit-transform:translateX(0px) rotate(0deg);-ms-transform:translateX(0px) rotate(0deg);transform:translateX(0px) rotate(0deg)}}';
            $output .= '#om-' . $type . '-' . $theme . '-optin {-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-name:rollIn;animation-name:rollIn;}';
            break;
    }

    $output .= '</style>';
    return $output;

}