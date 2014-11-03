<?php
/**
 * Handles all admin ajax interactions for the OptinMonster plugin.
 *
 * @since 2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */

add_action( 'wp_ajax_optin_monster_pointer', 'optin_monster_ajax_pointer' );
/**
 * Closes out the OptinMonster Trends pointer.
 *
 * @since 2.1.0
 */
function optin_monster_ajax_pointer() {

    // Run a security check first.
    check_ajax_referer( 'optin-monster-pointer', 'nonce' );

    // Prepare variables.
    $type   = stripslashes( $_POST['type'] );
    $option = get_option( 'optin_monster' );

    // Turn Trends on/off based on response.
    if ( 'allow' == $type ) {
        $option['allow_reporting'] = 1;
        update_option( 'optin_monster', $option );
    }

    // Update the user meta field to not show the pointer again.
    update_user_meta( get_current_user_id(), '_om_pointer_check', true );

    // Send back our response.
    die( json_encode( true ) );

}

add_action( 'wp_ajax_optin_monster_save_settings', 'optin_monster_ajax_save_settings' );
/**
 * Saves the option settings for OptinMonster.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_save_settings() {

    // Prepare variables.
    $option = get_option( 'optin_monster' );
    $fields = array();
    wp_parse_str( $_POST['fields'], $fields );

    // Save the option fields.
    if ( isset( $fields['cookie'] ) ) {
        $option['cookie'] = absint( $fields['cookie'] );
    }

    $option['leads']           = isset( $fields['leads'] ) ? 1 : 0;
    $option['admin_preview']   = isset( $fields['admin_preview'] ) ? 1 : 0;
	$option['allow_reporting'] = isset( $fields['allow_reporting'] ) ? 1 : 0;

    if ( isset( $fields['affiliate-link'] ) ) {
        $option['affiliate_link'] = esc_url( $fields['affiliate-link'] );
    }

    if ( isset( $fields['affiliate-link-position'] ) ) {
        $option['affiliate_link_position'] = esc_attr( $fields['affiliate-link-position'] );
    }

    // Allow option data to be filtered.
    $option = apply_filters( 'optin_monster_save_settings', $option, $fields );

    // Update the option.
    update_option( 'optin_monster', $option );

    // Build the theme UI and send it back to the script.
    die( json_encode( add_query_arg( array( 'optin-monster-updated' => true, 'page' => 'optin-monster-settings#!optin-monster-tab-settings' ), admin_url( 'admin.php' ) ) ) );

}

add_action( 'wp_ajax_optin_monster_sort_optins', 'optin_monster_ajax_sort_optins' );
/**
 * Allows optins to be sorted for priority in displaying on the website.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_sort_optins() {

    // Prepare variables.
    $order = explode( ',', $_POST['order'] );
    $i     = 0;

    // Loop through each item and save the menu order.
    foreach ( $order as $id ) {
        $sort               = array();
        $sort['ID']         = $id;
        $sort['menu_order'] = $i;
        wp_update_post( $sort );

        // Flush any optin caches.
        Optin_Monster_Common::get_instance()->flush_optin_caches( $id );

        $i++;
    }

    do_action( 'optin_monster_ajax_sort_optins', $order );

    // Build the theme UI and send it back to the script.
    die( json_encode( true ) );

}

add_action( 'wp_ajax_optin_monster_load_themes', 'optin_monster_ajax_load_themes' );
/**
 * Loads themes for selection when creating a new optin.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_load_themes() {

    // Run a security check first.
    check_ajax_referer( 'optin-monster-type', 'nonce' );

    // Prepare variables.
    $type = stripslashes( $_POST['type'] );

    // If the class isn't loaded, load it now.
    if ( ! class_exists( 'Optin_Monster_Views_New' ) ) {
        require plugin_dir_path( Optin_Monster::get_instance()->file ) . 'includes/admin/views/new.php';
    }

    // Build the theme UI and send it back to the script.
    die( json_encode( Optin_Monster_Views_New::get_instance()->get_theme_ui( $type ) ) );

}

add_action( 'wp_ajax_optin_monster_create_optin', 'optin_monster_ajax_create_optin' );
/**
 * Creates a new optin based on the theme selection of the user.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_create_optin() {

    // Run a security check first.
    check_ajax_referer( 'optin-monster-create', 'nonce' );

    // Prepare variables.
    $theme = stripslashes( $_POST['theme'] );
    $type  = stripslashes( $_POST['type'] );
    $title = stripslashes( $_POST['title'] );

    // Create the new optin based on the selection of the user.
    $data = array(
        'post_name'   => optin_monster_ajax_generate_postname_hash() . '-' . $type,
        'post_type'   => 'optin',
        'post_status' => 'publish',
        'post_title'  => $title,
        'menu_order'  => -1
    );
    $optin_id = wp_insert_post( $data );

    // Save the optin metadata for the theme, type and title.
    $meta = array(
        'type'  => $type,
        'theme' => $theme
    );
    update_post_meta( $optin_id, '_om_meta', $meta );

    // Flush any optin caches.
    Optin_Monster_Common::get_instance()->flush_optin_caches( $optin_id, $data['post_name'] );

    // Send back a redirect link.
    die( json_encode( add_query_arg( array( 'om_view' => 'edit', 'om_optin_id' => $optin_id ), admin_url( 'admin.php?page=optin-monster-settings' ) ) ) );

}

add_action( 'wp_ajax_optin_monster_hide_tips', 'optin_monster_ajax_hide_tips' );
/**
 * Hides the helpful tips dialog in the Preview area.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_hide_tips() {

    // Hide the tips.
    update_user_meta( get_current_user_id(), '_om_hide_tips', 1 );

    // Send back a response.
    die( json_encode( true ) );

}

add_action( 'wp_ajax_optin_monster_save_optin', 'optin_monster_ajax_save_optin' );
/**
 * Saves all the optin settings.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_save_optin() {

    // Run a security check first.
    check_ajax_referer( 'optin-monster-save', 'nonce' );

    // Prepare variables.
    $parsed   = array();
    wp_parse_str( $_POST['fields'], $parsed );
    $fields   = $parsed['optin_monster'];
    $optin_id = absint( $_POST['id'] );
    $split    = isset( $_POST['split'] ) && $_POST['split'] ? true : false;
    $meta     = get_post_meta( $optin_id, '_om_meta', true );
    $forced   = isset( $_POST['forced'] ) && 'true' == $_POST['forced'] ? true : false;
    $theme    = Optin_Monster_Output::get_instance()->get_optin_monster_theme( $meta['theme'], $optin_id, true );

    // Load the theme ajax and controls filters to allow data to be saved.
    $theme->ajax();
    $theme->controls();

    // If we have post category fields, add them.
    if ( isset( $parsed['post_category'] ) ) {
        $fields['post_category'] = $parsed['post_category'];
    }

    // Save the optin title.
    if ( isset( $fields['campaign_title'] ) ) {
        $campaign               = array();
        $campaign['ID']         = $optin_id;
        $campaign['post_title'] = strip_tags( trim( $fields['campaign_title'] ) );
        wp_update_post( $campaign );
    }

    // Save the custom CSS field.
    $meta['custom_css'] = isset( $fields['custom_css'] ) ? trim( esc_textarea( $fields['custom_css'] ) ) : '';

    // Save each configuration field.
    $meta['delay']      = isset( $fields['delay'] ) ? absint( $fields['delay'] ) : 5000;
    $meta['cookie']     = isset( $fields['cookie'] ) ? absint( $fields['cookie'] ) : 30;
    $meta['success']    = isset( $fields['success'] ) ? trim( $fields['success'] ) : '';
    $meta['redirect']   = isset( $fields['redirect'] ) ? esc_url( $fields['redirect'] ) : '';
    $meta['redirect_pass'] = isset( $fields['redirect_pass'] ) ? 1 : 0;
    $meta['second']     = isset( $fields['second'] ) ? 1 : 0;
    $meta['logged_in']  = isset( $fields['logged_in'] ) ? 1 : 0;
    $meta['powered_by'] = isset( $fields['powered_by'] ) ? 1 : 0;

    // Save the integration fields.
    $meta['email']['provider']  = isset( $fields['provider'] ) ? esc_attr( $fields['provider'] ) : 'none';
    $meta['email']['client_id'] = isset( $fields['provider_client'] ) ? esc_attr( $fields['provider_client'] ) : false;
    $meta['email']['account']   = isset( $fields['provider_account'] ) ? esc_attr( $fields['provider_account'] ) : false;
    $meta['email']['list_id']   = isset( $fields['provider_list'] ) ? esc_attr( $fields['provider_list'] ) : false;
    $meta['email']['segments']  = isset( $fields['email_segments'] ) ? stripslashes_deep( $fields['email_segments'] ) : array();

    // Save the output fields.
    $meta['display']['enabled']    = isset( $fields['enabled'] ) ? 1 : 0;
    $meta['display']['global']     = isset( $fields['global'] ) ? 1 : 0;
    $meta['display']['never']      = isset( $fields['never'] ) ? stripslashes_deep( $fields['never'] ) : array();
    $meta['display']['exclusive']  = isset( $fields['exclusive'] ) ? stripslashes_deep( $fields['exclusive'] ) : array();
    $meta['display']['categories'] = isset( $fields['post_category'] ) ? stripslashes_deep( $fields['post_category'] ) : array();
    $meta['display']['show']       = isset( $fields['show'] ) ? stripslashes_deep( $fields['show'] ) : array();
    $meta['display']['automatic']  = isset( $fields['automatic'] ) ? 1 : 0;

    // Ensure the type of optin is being set.
    $meta['type']                  = isset( $theme->type ) ? $theme->type : $meta['type'];
    if ( empty( $meta['type'] ) ) {
        $meta['type'] = '';
    }

    // If not a forced save, save the theme as well.
    if ( ! $forced ) {
        $meta['theme']             = isset( $_POST['theme'] ) ? $_POST['theme'] : $meta['theme'];
    }

    // Allow the meta fields to be filtered.
    $meta = apply_filters( 'optin_monster_save_optin', $meta, $optin_id, $fields, $_POST );

    // Provide a hook for people wanting to save different types of data.
    do_action( 'optin_monster_save_optin_data', $optin_id, $fields, $_POST, $meta );

    // Save the meta field.
    update_post_meta( $optin_id, '_om_meta', $meta );

    // Flush the optin caches.
    Optin_Monster_Common::get_instance()->flush_optin_caches( $optin_id );
    if ( $split ) {
        $parent_id = get_post_meta( $optin_id, '_om_is_clone', true );
        Optin_Monster_Common::get_instance()->flush_optin_caches( $parent_id );
    }

    // Die and send back the proper redirect link if exiting the optin.
    $url = $split ? add_query_arg( array( 'om_view' => 'split', 'om_optin_id' => get_post_meta( $optin_id, '_om_is_clone', true ) ), admin_url( 'admin.php?page=optin-monster-settings' ) ) : admin_url( 'admin.php?page=optin-monster-settings' );
    die( json_encode( add_query_arg( 'om_saved', true, $url ) ) );

}

add_action( 'wp_ajax_optin_monster_parse_canvas_shortcodes', 'optin_monster_ajax_parse_canvas_shortcodes' );
add_action( 'wp_ajax_nopriv_optin_monster_parse_canvas_shortcodes', 'optin_monster_ajax_parse_canvas_shortcodes' );
/**
 * Parses shortcodes in the Canvas addon for preview
 *
 * @since 2.0.0
 */
function optin_monster_ajax_parse_canvas_shortcodes() {

    $parsed_text = stripcslashes( $_POST['val'] );
    $parsed_text = str_replace( array( 'ajax="true"', 'ajax=true' ), '', $parsed_text );
    $parsed_text = do_shortcode( $parsed_text );

    die( json_encode( $parsed_text ) );

}

add_action( 'wp_ajax_optin_monster_save_optin_content', 'optin_monster_ajax_save_optin_content' );
/**
 * Saves the content for the specified field in the optin.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_save_optin_content() {

    // Prepare variables.
    $optin_id = absint( $_POST['id'] );
    $data     = isset( $_POST['data'] ) ? stripslashes_deep( (array) $_POST['data'] ) : array();
    $fonts    = isset( $_POST['fonts'] ) ? stripslashes_deep( $_POST['fonts'] ) : array();
    $meta     = get_post_meta( $optin_id, '_om_meta', true );
    $theme    = Optin_Monster_Output::get_instance()->get_optin_monster_theme( $meta['theme'], $optin_id, true );

    // Sanitize the data.
    $sani = array();
    foreach ( (array) $data as $field => $content ) {
        $sani[$field] = urldecode( stripslashes( $content ) );
    }

    // Allow the theme ajax and controls to be registered for filtering data.
    $theme->ajax();
    $theme->controls();

    // Save each field and its data.
    foreach ( (array) $sani as $field => $content ) {
        $meta[$field]['text'] = $content;
    }

    // Remove any font duplicates and purge the array of any non-Google fonts.
    $google = Optin_Monster_Output::get_instance()->get_supported_fonts( true );
    foreach ( (array) $fonts as $slug => $font ) {
        $font = ucwords( $font );
        if ( ! in_array( $font, $google ) ) {
            unset( $fonts[$slug] );
        }

        $fonts[$slug] = $font;
    }
    // Now we must merge in the fonts from the fields as well.
    $field_fonts   = array_map( 'optin_monster_ajax_map_fonts', $meta );
    $field_fonts   = array_filter( $field_fonts );
    $field_fonts   = array_values( $field_fonts );
    $meta['fonts'] = array();
    $final_fonts   = array_merge( (array) $meta['fonts'], $field_fonts, $fonts );
    $meta['fonts'] = array_values( array_unique( $final_fonts ) );

    // Allow the meta fields to be filtered.
    $meta = apply_filters( 'optin_monster_save_optin_content', $meta, $optin_id, $data, $fonts );

    // Provide a hook for people wanting to save different types of data.
    do_action( 'optin_monster_save_optin_data', $meta, $optin_id, $data, $fonts );

    // Save the meta field.
    update_post_meta( $optin_id, '_om_meta', $meta );

    // Flush the optin caches.
    Optin_Monster_Common::get_instance()->flush_optin_caches( $optin_id );

    die( json_encode( true ) );

}

/**
 * Map fonts for saving.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_map_fonts( $array ) {

	return isset( $array['font'] ) ? $array['font'] : '';

}

add_action( 'wp_ajax_optin_monster_save_image', 'optin_monster_ajax_save_image' );
/**
 * Saves an optin image.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_save_image() {

    // Prepare variables.
    $attach_id = absint( $_POST['attach'] );
    $optin_id  = absint( $_POST['id'] );
    $theme     = stripslashes( $_POST['theme'] );
    $type      = stripslashes( $_POST['type'] );

    // Set the post thumbnail with the ID provided.
    set_post_thumbnail( $optin_id, $attach_id );

    // Flush the optin caches.
    Optin_Monster_Common::get_instance()->flush_optin_caches( $optin_id );

    // Die and send back the image HTML.
    die( json_encode( optin_monster_ajax_get_image_thumbnail( $optin_id, $type, $theme ) ) );

}

add_action( 'wp_ajax_optin_monster_remove_image', 'optin_monster_ajax_remove_image' );
/**
 * Saves an optin image.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_remove_image() {

    // Prepare variables.
    $optin_id = absint( $_POST['id'] );
    $theme    = stripslashes( $_POST['theme'] );
    $type     = stripslashes( $_POST['type'] );

    // Remove the post thumbnail.
    delete_post_thumbnail( $optin_id );

    // Flush the optin caches.
    Optin_Monster_Common::get_instance()->flush_optin_caches( $optin_id );

    // Die and send back the image placeholder HTML.
    die( json_encode( optin_monster_ajax_get_image_placeholder( $optin_id, $type, $theme ) ) );

}

add_action( 'wp_ajax_optin_monster_init_provider', 'optin_monster_ajax_init_provider' );
/**
 * Initializes the email provider that is tied to the optin.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_init_provider() {

    // Prepare variables.
    $provider  = stripslashes( $_POST['provider'] );
    $optin_id  = absint( $_POST['id'] );
    $base      = Optin_Monster::get_instance();
    $providers = Optin_Monster_Common::get_instance()->get_email_providers( true );

    // If this is for the default "none" provider, don't do anything.
    if ( 'none' == $provider ) {
        die( json_encode( false ) );
    }

    // If this is for a custom HTML optin, do some... custom ...stuff!
    if ( 'custom' == $provider ) {
        die( json_encode( optin_monster_ajax_get_custom_output( $optin_id ) ) );
    }

    // Load the view class to handle optin editing functions and utility methods.
    if ( ! class_exists( 'Optin_Monster_Views_Edit' ) ) {
        require plugin_dir_path( $base->file ) . 'includes/admin/views/edit.php';
    }

    $view      = Optin_Monster_Views_Edit::get_instance();
    $email_id  = $view->get_email_setting( 'account' );
    $client_id = $view->get_email_setting( 'client_id' );
    $list_id   = $view->get_email_setting( 'list_id' );

    // If there are no accounts associated with the provider yet, we need to generate the new account form.
    if ( empty( $providers[$provider] ) ) {
        die( json_encode( optin_monster_ajax_get_new_account( $provider, $optin_id ) ) );
    }

    // If there is no account associated on the optin with the one selected, send back the account selection UI.
    if ( empty( $providers[$provider][$email_id] ) ) {
        $accounts = optin_monster_ajax_get_email_accounts( $providers[$provider], $provider, $email_id );
        die( json_encode( $accounts ) );
    }

    // Now retrieve the API object, account and lists output.
    $api      = optin_monster_ajax_get_email_provider( $provider );
    $accounts = optin_monster_ajax_get_email_accounts( $providers[$provider], $provider, $email_id );
    $lists    = 'campaign-monitor' == $provider ? $api->get_clients( $providers[$provider][$email_id], $client_id, $list_id, $email_id, $optin_id ) : $api->get_lists( $providers[$provider][$email_id], $list_id, $email_id, $optin_id );

    // If there is an error retrieving the lists, let the user know.
    if ( is_wp_error( $lists ) ) {
        die( json_encode( array( 'error' => $accounts . '<p class="om-error">' . $lists->get_error_message() . '</p>' ) ) );
    }

    // Send back the HTML response.
    die( json_encode( $accounts . $lists ) );

}

add_action( 'wp_ajax_optin_monster_get_provider', 'optin_monster_ajax_get_provider' );
/**
 * Retrieves the email provider API data.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_get_provider() {

    // Prepare variables.
    $provider  = stripslashes( $_POST['provider'] );
    $account   = stripslashes( $_POST['account'] );
    $optin_id  = absint( $_POST['id'] );
    $base      = Optin_Monster::get_instance();
    $providers = Optin_Monster_Common::get_instance()->get_email_providers( true );

    // If the user is adding a new account, send back the new account UI.
    if ( 'new' == $account ) {
        die( json_encode( optin_monster_ajax_get_new_account( $provider, $optin_id ) ) );
    }

    // Load the view class to handle optin editing functions and utility methods.
    if ( ! class_exists( 'Optin_Monster_Views_Edit' ) ) {
        require plugin_dir_path( $base->file ) . 'includes/admin/views/edit.php';
    }

    $view    = Optin_Monster_Views_Edit::get_instance();
    $list_id = $view->get_email_setting( 'list_id' );

    // Add the client if using Campaign Monitor.
    if ( 'campaign-monitor' == $provider ) {
        $providers[$provider][$account]['client'] = stripslashes( $_POST['client'] );
    }

    // Now retrieve the API object and lists output.
    $api   = optin_monster_ajax_get_email_provider( $provider );
    $lists = $api->get_lists( $providers[$provider][$account], $list_id );

    // If there is an error retrieving the lists, let the user know.
    if ( is_wp_error( $lists ) ) {
        die( json_encode( array( 'error' => '<p class="om-error">' . $lists->get_error_message() . '</p>' ) ) );
    }

    // Send back the HTML response.
    die( json_encode( $lists ) );

}

add_action( 'wp_ajax_optin_monster_get_provider_segments', 'optin_monster_ajax_get_provider_segments' );
/**
 * Retrieves the email provider API data.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_get_provider_segments() {

    // Prepare variables.
    $provider  = stripslashes( $_POST['provider'] );
    $account   = stripslashes( $_POST['account'] );
    $list_id   = stripslashes( $_POST['list'] );
    $optin_id  = absint( $_POST['id'] );
    $providers = Optin_Monster_Common::get_instance()->get_email_providers( true );

    // Only continue if the provider explicitly supports list segments.
    $supports = apply_filters( 'optin_monster_provider_supports_segments', array( 'mailchimp', 'infusionsoft' ), $provider, $optin_id );
    if ( ! in_array( $provider, (array) $supports ) ) {
        die( json_encode( array( 'error' => __( 'This email provider does not support segments.', 'optin-monster' ) ) ) );
    }

    // Now retrieve the API object and segments output.
    $api      = optin_monster_ajax_get_email_provider( $provider );
    $segments = $api->get_segments( $providers[$provider][$account], $list_id, $optin_id );

    // If there is an error retrieving the lists, let the user know.
    if ( is_wp_error( $segments ) ) {
        die( json_encode( array( 'error' => $segments->get_error_message() ) ) );
    }

    // Send back the HTML response.
    die( json_encode( $segments ) );

}

add_action( 'wp_ajax_optin_monster_get_provider_clients', 'optin_monster_ajax_get_provider_clients' );
/**
 * Retrieves the email provider API data.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_get_provider_clients() {

    // Prepare variables.
    $provider  = stripslashes( $_POST['provider'] );
    $account   = stripslashes( $_POST['account'] );
    $optin_id  = absint( $_POST['id'] );
    $providers = Optin_Monster_Common::get_instance()->get_email_providers( true );

    // If the user is adding a new account, send back the new account UI.
    if ( 'new' == $account ) {
        die( json_encode( optin_monster_ajax_get_new_account( $provider, $optin_id ) ) );
    }

    // Now retrieve the API object and segments output.
    $api     = optin_monster_ajax_get_email_provider( $provider );
    $clients = $api->get_clients( $providers[$provider][$account] );

    // Send back the HTML response.
    die( json_encode( $clients ) );

}

add_action( 'wp_ajax_optin_monster_connect_provider', 'optin_monster_ajax_connect_provider' );
/**
 * Initializes the email provider that is tied to the optin.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_connect_provider() {

    // Prepare variables.
    $provider = stripslashes( $_POST['provider'] );
    $optin_id = absint( $_POST['id'] );
    $base     = Optin_Monster::get_instance();
    $data     = array();
    wp_parse_str( $_POST['fields'], $data );

    // Now retrieve the API object and attempt to authenticate the credentials.
    $api = optin_monster_ajax_get_email_provider( $provider );
    $ret = $api->authenticate( $data, $optin_id );

    // If there is an error retrieving the lists, let the user know.
    if ( is_wp_error( $ret ) ) {
        die( json_encode( array( 'error' => '<p class="om-error">' . $ret->get_error_message() . '</p>' ) ) );
    }

    // Now retrieve the accounts.
    $providers = Optin_Monster_Common::get_instance()->get_email_providers( true );
    $accounts  = optin_monster_ajax_get_email_accounts( $providers[$provider], $provider, Optin_Monster_Output::get_instance()->get_email_setting( 'account' ) );

    // Send back the HTML response.
    die( json_encode( $accounts . $ret ) );

}

add_action( 'wp_ajax_optin_monster_save_custom_html', 'optin_monster_ajax_save_custom_html' );
/**
 * Saves the custom HTML provider data.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_save_custom_html() {

    $optin_id = absint( $_POST['id'] );
    $data = array(
        'content' => stripslashes( $_POST['content'] ),
    );

    $api = optin_monster_ajax_get_email_provider( 'custom' );
    $ret = $api->authenticate( $data, $optin_id );

    die( json_encode( true ) );

}

add_action( 'wp_ajax_optin_monster_install_addon', 'optin_monster_ajax_install_addon' );
/**
 * Installs an Optin_Monster addon.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_install_addon() {

    // Run a security check first.
    check_ajax_referer( 'optin-monster-install', 'nonce' );

    // Install the addon.
    if ( isset( $_POST['plugin'] ) ) {
        $download_url = $_POST['plugin'];
        global $hook_suffix;

        // Set the current screen to avoid undefined notices.
        set_current_screen();

        // Prepare variables.
        $method = '';
        $url    = add_query_arg(
            array(
                'page' => 'optin-monster-settings'
            ),
            admin_url( 'admin.php' )
        );

        // Start output bufferring to catch the filesystem form if credentials are needed.
        ob_start();
        if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, null ) ) ) {
            $form = ob_get_clean();
            echo json_encode( array( 'form' => $form ) );
            die;
        }

        // If we are not authenticated, make it happen now.
        if ( ! WP_Filesystem( $creds ) ) {
            ob_start();
            request_filesystem_credentials( $url, $method, true, false, null );
            $form = ob_get_clean();
            echo json_encode( array( 'form' => $form ) );
            die;
        }

        // We do not need any extra credentials if we have gotten this far, so let's install the plugin.
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once plugin_dir_path( Optin_Monster::get_instance()->file ) . 'includes/admin/skin.php';

        // Create the plugin upgrader with our custom skin.
        $installer = new Plugin_Upgrader( $skin = new Optin_Monster_Skin() );
        $installer->install( $download_url );

        // Flush the cache and return the newly installed plugin basename.
        wp_cache_flush();
        if ( $installer->plugin_info() ) {
            $plugin_basename = $installer->plugin_info();
            echo json_encode( array( 'plugin' => $plugin_basename ) );
            die;
        }
    }

    // Send back a response.
    echo json_encode( true );
    die;

}

add_action( 'wp_ajax_optin_monster_activate_addon', 'optin_monster_ajax_activate_addon' );
/**
 * Activates an Optin_Monster addon.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_activate_addon() {

    // Run a security check first.
    check_ajax_referer( 'optin-monster-activate', 'nonce' );

    // Activate the addon.
    if ( isset( $_POST['plugin'] ) ) {
        $activate = activate_plugin( $_POST['plugin'] );

        if ( is_wp_error( $activate ) ) {
            echo json_encode( array( 'error' => $activate->get_error_message() ) );
            die;
        }
    }

    echo json_encode( true );
    die;

}

add_action( 'wp_ajax_optin_monster_deactivate_addon', 'optin_monster_ajax_deactivate_addon' );
/**
 * Deactivates an Optin_Monster addon.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_deactivate_addon() {

    // Run a security check first.
    check_ajax_referer( 'optin-monster-deactivate', 'nonce' );

    // Deactivate the addon.
    if ( isset( $_POST['plugin'] ) ) {
        $deactivate = deactivate_plugins( $_POST['plugin'] );
    }

    echo json_encode( true );
    die;

}

add_action( 'wp_ajax_optin_monster_delete_integration', 'optin_monster_ajax_delete_integration' );
/**
 * Deletes an integration for an email provider.
 *
 * @since 2.0.0
 */
function optin_monster_ajax_delete_integration() {

    $hash      = stripslashes( $_POST['hash'] );
    $provider  = stripslashes( $_POST['provider'] );
    $providers = Optin_Monster_Common::get_instance()->get_email_providers( true );
    $response  = array();

    if ( isset( $providers[$provider][$hash] ) ) {
        unset( $providers[$provider][$hash] );
        if ( 0 == count( $providers[$provider] ) ) {
            unset( $providers[$provider] );
            $response['provider_empty'] = true;
        }
    }

    $updated = update_option( 'optin_monster_providers', $providers );
    $response['updated'] = $updated;
    die( json_encode( $response ) );

}

/**
 * Returns the provider API object for handling email provider interactions.
 *
 * @since 2.0.0
 *
 * @param string $provider The provider to target.
 * @return object $api     The provider API object.
 */
function optin_monster_ajax_get_email_provider( $provider ) {

    // Prepare the provider object based on the provider selected.
    $base = Optin_Monster::get_instance();
    $api  = false;
    switch ( $provider ) {
        case 'activecampaign' :
            require plugin_dir_path( $base->file ) . 'includes/providers/activecampaign.php';
            $api = new Optin_Monster_Provider_ActiveCampaign();
            break;
        case 'feedblitz' :
            require plugin_dir_path( $base->file ) . 'includes/providers/feedblitz.php';
            $api = new Optin_Monster_Provider_Feedblitz();
            break;
        case 'mailchimp' :
            require plugin_dir_path( $base->file ) . 'includes/providers/mailchimp.php';
            $api = new Optin_Monster_Provider_Mailchimp();
            break;
        case 'sendinblue' :
            require plugin_dir_path( $base->file ) . 'includes/providers/sendinblue.php';
            $api = new Optin_Monster_Provider_SendinBlue();
            break;
        case 'pardot' :
            require plugin_dir_path( $base->file ) . 'includes/providers/pardot.php';
            $api = new Optin_Monster_Provider_Pardot();
            break;
        case 'madmimi' :
            require plugin_dir_path( $base->file ) . 'includes/providers/madmimi.php';
            $api = new Optin_Monster_Provider_MadMimi();
            break;
        case 'getresponse' :
            require plugin_dir_path( $base->file ) . 'includes/providers/getresponse.php';
            $api = new Optin_Monster_Provider_GetResponse();
            break;
        case 'totalsend' :
            require plugin_dir_path( $base->file ) . 'includes/providers/totalsend.php';
            $api = new Optin_Monster_Provider_TotalSend();
            break;
        case 'aweber' :
            require plugin_dir_path( $base->file ) . 'includes/providers/aweber.php';
            $api = new Optin_Monster_Provider_AWeber();
            break;
        case 'constant-contact' :
            require plugin_dir_path( $base->file ) . 'includes/providers/constantcontact.php';
            $api = new Optin_Monster_Provider_ConstantContact();
            break;
        case 'hubspot' :
            require plugin_dir_path( $base->file ) . 'includes/providers/hubspot.php';
            $api = new Optin_Monster_Provider_HubSpot();
            break;
        case 'campaign-monitor' :
            require plugin_dir_path( $base->file ) . 'includes/providers/campaignmonitor.php';
            $api = new Optin_Monster_Provider_CampaignMonitor();
            break;
        case 'infusionsoft' :
            require plugin_dir_path( $base->file ) . 'includes/providers/infusionsoft.php';
            $api = new Optin_Monster_Provider_Infusionsoft();
            break;
        case 'icontact' :
            require plugin_dir_path( $base->file ) . 'includes/providers/icontact.php';
            $api = new Optin_Monster_Provider_iContact();
            break;
        case 'emma' :
            require plugin_dir_path( $base->file ) . 'includes/providers/emma.php';
            $api = new Optin_Monster_Provider_Emma();
            break;
        case 'customerio' :
            require plugin_dir_path( $base->file ) . 'includes/providers/customerio.php';
            $api = new Optin_Monster_Provider_Customerio();
            break;
        case 'custom' :
            require plugin_dir_path( $base->file ) . 'includes/providers/custom.php';
            $api = new Optin_Monster_Provider_Custom();
            break;
        case 'mailpoet' :
            require plugin_dir_path( $base->file ) . 'includes/providers/mailpoet.php';
            $api = new Optin_Monster_Provider_Mailpoet();
            break;
	    case 'marketo' :
		    require plugin_dir_path( $base->file ) . 'includes/providers/marketo.php';
		    $api = new Optin_Monster_Provider_Marketo();
		    break;
	    case 'mailerlite' :
		    require plugin_dir_path( $base->file ) . 'includes/providers/mailerlite.php';
		    $api = new Optin_Monster_Provider_MailerLite();
		    break;
    }

    return apply_filters( 'optin_monster_provider_object', $api, $provider );

}

/**
 * Retrieves an HTML representation of all the email accounts for a provider.
 *
 * @since 2.0.0
 *
 * @param array $accounts  Array of accounts for a provider.
 * @param string $provider The provider to target.
 * @param string $email_id The email ID for the account.
 * @return string          HTML output of the available accounts.
 */
function optin_monster_ajax_get_email_accounts( $accounts, $provider, $email_id ) {

    $output = '<div class="optin-monster-field-box optin-monster-provider-accounts optin-monster-clear">';
        $output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-provider-account">' . __( 'Email provider account', 'optin-monster' ) . '</label><br />';
            $output .= '<select id="optin-monster-provider-account" name="optin_monster[provider_account]">';
                $output .= '<option value="none">' . __( 'Select your account...', 'optin-monster' ) . '</option>';

                foreach ( $accounts as $id => $data ) {
                    $output .= '<option value="' . $id . '"' . selected( $email_id, $id, false ) . '>' . $data['label'] . '</option>';
                }

                $output .= '<option value="new">' . __( 'Add new account', 'optin-monster' ) . '</option>';
            $output .= '</select>';
        $output .= '</p>';
    $output .= '</div>';

    return apply_filters( 'optin_monster_email_accounts', $output, $accounts, $provider );

}

/**
 * Returns the HTML output for adding a custom HTML optin form.
 *
 * @since 2.0.0
 *
 * @param int $id          The optin ID to target.
 * @return string $html    HTML representation of the custom form.
 */
function optin_monster_ajax_get_custom_output( $optin_id ) {

    $meta = get_post_meta( $optin_id, '_om_meta', true );
    $content = '';
    if ( isset( $meta['custom_html'] ) ) {
        $content = $meta['custom_html'];
    }

    $output = '<div class="optin-monster-field-box optin-monster-provider-lists optin-monster-clear">';
        $output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-custom-html">' . __( 'Custom HTML Form Code', 'optin-monster' ) . '</label><br />';
            $output .= '<textarea id="optin-monster-custom-html" class="om-custom-html-editor" rows="5">' . $content . '</textarea>';
        $output .= '</p>';
    $output .= '</div>';

    return apply_filters( 'optin_monster_custom_form', $output, $optin_id );

}

/**
 * Returns the HTML output for adding a new email provider account.
 *
 * @since 2.0.0
 *
 * @param string $provider The email provider to add an account for.
 * @param int $id          The optin ID to target.
 * @return string $html    HTML representation of the new account form.
 */
function optin_monster_ajax_get_new_account( $provider, $optin_id ) {

    // Prepare variables.
    $html     = '';
    $output   = '';
    $title    = '';
    $doc      = '';
    $href     = '#';
    $external = false;

    // Prepare provider variables.
    switch ( $provider ) {
        case 'activecampaign' :
            $output .= '<input type="text" name="om-api-url" id="om-api-url" placeholder="' . __( 'ActiveCampaign API URL', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'ActiveCampaign API Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'ActiveCampaign Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'ActiveCampaign', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-activecampaign/';
            break;
        case 'feedblitz' :
            $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'Feedblitz API Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'Feedblitz Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'Feedblitz', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-feedblitz/';
            break;
        case 'mailchimp' :
            $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'MailChimp API Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'MailChimp Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'MailChimp', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-mailchimp/';
            break;
        case 'sendinblue' :
            $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'SendinBlue Access Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-secret-key" id="om-secret-key" placeholder="' . __( 'SendinBlue Secret Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'SendinBlue Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'SendinBlue', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-sendinblue/';
            break;
        case 'pardot' :
            $output .= '<input type="text" name="om-email-address" id="om-email-address" placeholder="' . __( 'Pardot Email Address', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-password" id="om-password" placeholder="' . __( 'Pardot Password', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-user-key" id="om-user-key" placeholder="' . __( 'Pardot User Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'Pardot Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'Pardot', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-pardot/';
            break;
        case 'madmimi' :
            $output .= '<input type="text" name="om-email-address" id="om-email-address" placeholder="' . __( 'Mad Mimi Email Address', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'Mad Mimi API Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'Mad Mimi Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'Mad Mimi', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-madmimi/';
            break;
        case 'getresponse' :
            $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'GetResponse API Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'GetResponse Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'GetResponse', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-getresponse/';
            break;
        case 'totalsend' :
            $output .= '<input type="text" name="om-email-address" id="om-email-address" placeholder="' . __( 'TotalSend Email Address', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-password" id="om-password" placeholder="' . __( 'TotalSend Password', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'TotalSend Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'TotalSend', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-totalsend/';
            break;
        case 'aweber' :
            $output  .= '<input type="text" name="om-auth-code" id="om-auth-code" placeholder="' . __( 'AWeber Authorization Code', 'optin-monster' ) . '" value="" />';
            $output  .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'AWeber Account Label', 'optin-monster' ) . '" value="" />';
            $title    = __( 'AWeber', 'optin-monster' );
            $doc      = 'http://optinmonster.com/docs/connect-optinmonster-aweber/';
            $href     = 'https://auth.aweber.com/1.0/oauth/authorize_app/f5b114f8';
            $external = true;
            break;
        case 'constant-contact' :
            $output  .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'Constant Contact Account Label', 'optin-monster' ) . '" value="" />';
            $output  .= '<input type="hidden" name="om-access-token" id="om-access-token" value="" />';
            $output  .= '<input type="hidden" name="om-expires-in" id="om-expires-in" value="" />';
            $title    = __( 'Constant Contact', 'optin-monster' );
            $doc      = 'http://optinmonster.com/docs/connect-optinmonster-constant-contact/';
            $href     = 'https://oauth2.constantcontact.com/oauth2/oauth/siteowner/authorize';
            $external = true;
            break;
        case 'hubspot' :
            $pre_auth  = '<input type="text" name="om-hubspot-portalid" id="om-hubspot-portalid" placeholder="' . __( 'HubSpot Portal ID', 'optin-monster' ) . '" value="" />';
            $output  .= '<input type="hidden" name="om-hubspot-access-token" id="om-hubspot-access-token" value="" />';
            $output  .= '<input type="hidden" name="om-hubspot-refresh-token" id="om-hubspot-refresh-token" value="" />';
            $output  .= '<input type="hidden" name="om-hubspot-expires-in" id="om-hubspot-expires-in" value="" />';
            $output  .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'HubSpot Account Label', 'optin-monster' ) . '" value="" />';
            $title    = __( 'HubSpot', 'optin-monster' );
            $doc      = 'http://optinmonster.com/docs/connect-optinmonster-hubspot/';
            $href     = 'https://app.hubspot.com/auth/authenticate';
            $external = true;
            break;
        case 'campaign-monitor' :
            $output  .= '<input type="hidden" name="om-access-token" id="om-access-token" value="" />';
            $output  .= '<input type="hidden" name="om-refresh-token" id="om-refresh-token" value="" />';
            $output  .= '<input type="hidden" name="om-expires-in" id="om-expires-in" value="" />';
            $output  .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'Campaign Monitor Account Label', 'optin-monster' ) . '" value="" />';
            $title    = __( 'Campaign Monitor', 'optin-monster' );
            $doc      = 'http://optinmonster.com/docs/connect-optinmonster-campaign-monitor/';
            $href     = 'https://api.createsend.com/oauth';
            $external = true;
            break;
        case 'infusionsoft' :
            $output  .= '<input type="text" name="om-subdomain" id="om-subdomain" placeholder="' . __( 'InfusionSoft Subdomain', 'optin-monster' ) . '" value="" />';
            $output  .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'InfusionSoft API Key', 'optin-monster' ) . '" value="" />';
            $output  .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'InfusionSoft Account Label', 'optin-monster' ) . '" value="" />';
            $title    = __( 'InfusionSoft', 'optin-monster' );
            $doc      = 'http://optinmonster.com/docs/connect-optinmonster-infusionsoft/';
            break;
        case 'icontact' :
            $output  .= '<input type="text" name="om-username" id="om-username" placeholder="' . __( 'iContact Username', 'optin-monster' ) . '" value="" />';
            $output  .= '<input type="text" name="om-app-id" id="om-app-id" placeholder="' . __( 'iContact Application ID', 'optin-monster' ) . '" value="" />';
            $output  .= '<input type="text" name="om-app-password" id="om-app-password" placeholder="' . __( 'iContact Application Password', 'optin-monster' ) . '" value="" />';
            $output  .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'iContact Account Label', 'optin-monster' ) . '" value="" />';
            $title    = __( 'iContact', 'optin-monster' );
            $doc      = 'http://optinmonster.com/docs/connect-optinmonster-icontact/';
            $href     = 'https://app.icontact.com/icp/core/registerapp/';
            $external = true;
            break;
        case 'emma' :
            $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'Emma Public Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-secret-key" id="om-secret-key" placeholder="' . __( 'Emma Private Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-id" id="om-account-id" placeholder="' . __( 'Emma Account ID', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'Emma Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'Emma', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-emma/';
            break;
        case 'customerio' :
            $output .= '<input type="text" name="om-site-id" id="om-site-id" placeholder="' . __( 'Customer.io Site ID', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'Customer.io API Key', 'optin-monster' ) . '" value="" />';
            $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'Customer.io Account Label', 'optin-monster' ) . '" value="" />';
            $title   = __( 'Customer.io', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/connect-optinmonster-customerio/';
            break;
        case 'custom' :
            $output .= 'Some text';
            $title   = __( 'Custom HTML Optin Form', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/how-to-connect-optinmonster-with-any-custom-html-form';
            break;
        case 'mailpoet' :
            $title   = __( 'MailPoet', 'optin-monster' );
            $doc     = 'http://optinmonster.com/docs/how-to-connect-optinmonster-mailpoet';
            break;
	    case 'marketo' :
		    $output .= '<input type="text" name="om-client-id" id="om-client-id" placeholder="' . __( 'Marketo Client ID', 'optin-monster' ) . '" value="" />';
		    $output .= '<input type="text" name="om-client-secret" id="om-client-secret" placeholder="' . __( 'Marketo Client Secret', 'optin-monster' ) . '" value="" />';
		    $output .= '<input type="text" name="om-subdomain" id="om-subdomain" placeholder="' . __( 'Marketo Subdomain', 'optin-monster' ) . '" value="" />';
		    $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'Marketo Account Label', 'optin-monster' ) . '" value="" />';
		    $title   = __( 'Marketo', 'optin-monster' );
		    $doc     = 'http://optinmonster.com/docs/connect-optinmonster-marketo/';
		    break;
	    case 'mailerlite' :
		    $output .= '<input type="text" name="om-api-key" id="om-api-key" placeholder="' . __( 'MailerLite Api Key', 'optin-monster' ) . '" value="" />';
		    $output .= '<input type="text" name="om-account-label" id="om-account-label" placeholder="' . __( 'MailerLite Account Label', 'optin-monster' ) . '" value="" />';
		    $title   = __( 'MailerLite', 'optin-monster' );
		    $doc     = 'http://optinmonster.com/docs/connect-optinmonster-mailerlite/';
		    break;
    }

    // Allow the outputs to be filtered.
    $output   = apply_filters( 'optin_monster_account_output', $output, $provider, $optin_id );
    $title    = apply_filters( 'optin_monster_account_title', $title, $provider, $optin_id );
    $doc      = apply_filters( 'optin_monster_account_doc', $doc, $provider, $optin_id );
    $href     = apply_filters( 'optin_monster_account_href', $href, $provider, $optin_id );
    $external = apply_filters( 'optin_monster_account_external', $external, $provider, $optin_id );

    // Build the main output.
    $html .= '<div class="optin-monster-field-box optin-monster-field-box-account optin-monster-clear">';
        if ( 'mailpoet' != $provider ) {
            $html .= '<p class="optin-monster-field-wrap">';
            $html .= __( 'Please fill out all of the fields below to add your new provider account.', 'optin-monster' );
            if ( ! empty( $doc ) ) {
                $html .= ' <span class="description"><a href="' . esc_url( $doc ) . '" title="' . esc_attr__( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) . '" target="_blank">' . __( 'Click here for documentation on connecting to this email provider.', 'optin-monster' ) . '</a></span>';
            }
            $html .= '</p>';
        }

        if ( $external ) {
            $html .= '<p class="optin-monster-field-wrap optin-monster-field-notice">' . sprintf( __( 'Because %1$s requires external authentication, you will need to register our application with %1$s before you can proceed.', 'optin-monster' ), $title ) . '</p>';
            if ( isset( $pre_auth ) ) {
                $html .= '<p class="optin-monster-field-wrap optin-monster-account-fields">' . $pre_auth . '</p>';
            }
            $html .= '<p class="optin-monster-field-wrap"><a class="button button-secondary button-clock om-register-provider" href="' . esc_url( $href ) . '" title="' . esc_attr__( 'Click here to register this email provider with OptinMonster.' ) . '" data-om-email-provider="' . $provider . '">' . sprintf( __( 'Register %s', 'optin-monster' ), $title ) . '</a></p>';
        }

        $html .= '<p class="optin-monster-field-wrap optin-monster-account-fields">';
            $html .= $output;
        $html .= '</p>';
        $html .= '<p class="optin-monster-field-wrap">';
            $html .= '<a class="button button-primary button-block om-connect-provider" href="' . esc_url( $href ) . '" data-om-email-provider="' . $provider . '" title="' . sprintf( esc_attr__( 'Connect to %s', 'optin-monster' ), $title ) . '">' . sprintf( __( 'Connect to %s', 'optin-monster' ), $title ) . '</a> <i class="fa fa-spinner fa-spin"></i>';
        $html .= '</p>';
    $html .= '</div>';

    // Return and allow final output to be filtered.
    return apply_filters( 'optin_monster_account', $html, $provider, $optin_id, $output, $title, $doc );

}

/**
 * Retrieves and generates the HTML for an optin image.
 *
 * @since 2.0.0
 *
 * @param int $optin_id The optin ID to target.
 * @param string $size  The optin image size to retrieve.
 * @param string $type  The type of theme to target.
 * @param string $theme The theme to target for the thumbnail size.
 * @return string       HTML markup for the image and editing buttons.
 */
function optin_monster_ajax_get_optin_image( $optin_id, $size, $type, $theme ) {

	// Retrieve the src for the image.
    $thumb_id = get_post_thumbnail_id( $optin_id );
    $src      = wp_get_attachment_image_src( $thumb_id, $size );
    if ( ! $src ) {
	    return '';
    }

    // Retrieve the alt text for the image.
    $alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );

	// Retrieve the title text for the image and then return the image HTML.
	$attachment = get_post( $thumb_id );
	$title = $attachment->post_title;

    return '<img class="optin-monster-image optin-monster-image-' . $type . '-' . $theme . '" src="' . esc_url( $src[0] ) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $title ) . '" />';

}

/**
 * Retrieves and generates the HTML for adding an image to an optin along
 * with editing buttons for editing or removing the image.
 *
 * @since 2.0.0
 *
 * @param int $optin_id The optin ID to target.
 * @param string $type  The type of theme to target.
 * @param string $theme The theme to target for the thumbnail size.
 * @return string       HTML markup for the image and editing buttons.
 */
function optin_monster_ajax_get_image_thumbnail( $optin_id, $type, $theme ) {

    $html  = '<div class="optin-monster-image-preview wp-core-ui">';
        $html .= optin_monster_ajax_get_optin_image( $optin_id, 'optin-monster-' . $type . '-theme-' . $theme, $type, $theme );
        $html .= '<a href="#" class="button button-primary button-small om-button-modify" title="' . esc_attr__( 'Modify Image', 'optin-monster' ) . '" data-om-theme="' . $theme . '" data-om-type="' . $type . '">' . __( 'Modify', 'optin-monster' ) . '</a>';
        $html .= '<a href="#" class="button button-secondary button-small om-button-remove" title="' . esc_attr__( 'Remove Image', 'optin-monster' ) . '" data-om-theme="' . $theme . '" data-om-type="' . $type . '">' . __( 'Remove', 'optin-monster' ) . '</a>';
    $html .= '</div>';

    return $html;

}

/**
 * Retrieves and generates the HTML for image placeholders.
 *
 * @since 2.0.0
 *
 * @param int $optin_id The optin ID to target.
 * @param string $type  The type of theme to target.
 * @param string $theme The theme to target for the thumbnail size.
 * @return string       HTML markup for the image placeholder.
 */
function optin_monster_ajax_get_image_placeholder( $optin_id, $type, $theme ) {

    $api = Optin_Monster_Output::get_instance()->get_optin_monster_theme( $theme, $optin_id, true );
    $output  = '<div class="optin-monster-image-placeholder" style="width:' . $api->img_width . 'px;height:' . $api->img_height . 'px;">';
        $output .= '<span class="optin-monster-image-placeholder-dims">' . $api->img_width . '&times;' . $api->img_height . '</span>';
        $output .= '<a class="om-media-modal" href="#" data-om-action="selectable" data-om-theme="' . $theme . '" data-om-type="' . $type . '"></a>';
    $output .= '</div>';

    return $output;

}

/**
 * Generates a suitable postname hash for the optin slug.
 *
 * @since 2.0.0
 *
 * @return string A hashed and sanitized string.
 */
function optin_monster_ajax_generate_postname_hash( $length = 10, $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789' ) {

    $str   = '';
    $count = strlen( $charset );
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $alpha_count = strlen( $alpha );

    while ( $length-- ) {
        $str .= $charset[mt_rand( 0, $count - 1 )];
    }

    return substr_replace( $str, $alpha[mt_rand( 0, $alpha_count - 1 )], 0, 1 );

}