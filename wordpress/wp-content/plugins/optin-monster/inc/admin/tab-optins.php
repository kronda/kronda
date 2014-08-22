<?php
/**
 * "Optins" tab class.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Loads post types.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_tab_optins {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $base, $user, $optins, $tab;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        // Bring base class into scope.
        global $optin_monster_account, $wpdb;
        $this->base    = optin_monster::get_instance();
        $this->user    = wp_get_current_user();
        $this->tab     = 'optins';
        $this->optin   = isset( $_GET['edit'] ) ? get_posts( array( 'post_type' => 'optin', 'posts_per_page' => 1, 'name' => $_GET['edit'] ) ) : false;
        $this->cc_auth = isset( $_GET['cc_auth'] ) && $_GET['cc_auth'] ? true : false;
		$this->optin   = $this->optin ? $this->optin[0] : false;
		$this->meta    = $this->optin ? get_post_meta( $this->optin->ID, '_om_meta', true ) : false;
		$this->type    = isset( $_GET['type'] ) ? $_GET['type'] : false;
		$this->account = $optin_monster_account;
		$this->table   = $wpdb->prefix . 'om_hits_log';

        // Add all necessary hooks.
        add_action( 'optin_monster_tab_' . $this->tab, array( $this, 'do_tab' ) );
        add_action( 'optin_monster_build', array( $this, 'do_optin_build' ) );
        add_action( 'optin_monster_config', array( $this, 'do_optin_config' ) );
        add_action( 'optin_monster_design', array( $this, 'do_optin_design' ) );
        add_action( 'optin_monster_code', array( $this, 'do_optin_code' ) );

    }

    /**
	 * Handles the HTML output of a tab.
	 *
	 * @since 1.0.0
	 */
    public function do_tab() {

        // If we have authenticated to Constant Contact, handle the processing now.
        if ( $this->cc_auth )
            $this->do_cc_auth();

        // Handle A/B tests.
        if ( ! empty( $_GET['replicate'] ) )
            $this->do_optin_split_test();

        // Handle duplicating optins.
        if ( ! empty( $_GET['duplicate'] ) )
            $this->do_optin_duplicate();

        // Handle taking a B test and replacing the parent.
        if ( ! empty( $_GET['make-primary'] ) )
            $this->do_optin_make_primary();

        // Handle resetting stats.
        if ( ! empty( $_GET['reset-stats'] ) )
            $this->do_optin_reset_stats();

        // Handle test mode.
        if ( ! empty( $_GET['test-mode'] ) )
            $this->do_optin_test_mode();

        // Handle live mode.
        if ( ! empty( $_GET['live-mode'] ) )
            $this->do_optin_live_mode();

        // Handle deleting an optin.
        if ( ! empty( $_GET['delete'] ) )
            $this->do_optin_delete();

        // If doing config for a tab, do that and return.
        if ( ! empty( $_GET['config'] ) ) {
            do_action( 'optin_monster_config' );
            $this->do_optin_config_script();
            return;
        }

        // If doing design for a tab, do that and return.
        if ( ! empty( $_GET['design'] ) ) {
            do_action( 'optin_monster_design' );
            add_action( 'admin_footer-' . $this->base->hook, array( $this, 'do_optin_design_script' ), 1000 );
            return;
        }

        // If doing the optin final embed, do that and return.
        if ( ! empty( $_GET['code'] ) ) {
            do_action( 'optin_monster_code' );
            $this->do_optin_code_script();
            return;
        }

        // If creating a new optin, do that and return.
        if ( ! empty( $_GET['action'] ) && 'build' == $_GET['action'] ) {
            do_action( 'optin_monster_build' );
            $this->do_optin_build_script();
            return;
        }

        // Do final base tab output.
        $this->do_tab_output();

    }

    /**
	 * Handles authenticating Constant Contact.
	 *
	 * @since 1.0.0
	 */
    public function do_cc_auth() {

        $providers = $this->account->get_email_providers();
		$uniqid = uniqid();
		$label = trim( strip_tags( $_GET['label'] ) );
		$providers['constant-contact'][$uniqid]['label'] = $label;
		$providers['constant-contact'][$uniqid]['token'] = $_GET['token'];
		update_option( 'optin_monster_providers', $providers );


    }

	/**
	 * Does a split test for the optin
	 *
	 * @since 1.0.0
	 */
	public function do_optin_split_test() {

    	$post = get_post( absint( $_GET['replicate'] ) );

    	$original_meta = $new_meta = get_post_meta( $post->ID, '_om_meta', true );

    	// Return early if trying to duplicate parent that has a clone or is a clone itself.
    	if ( isset( $original_meta['has_clone'] ) || isset( $original_meta['is_clone'] ) ) return;

        $new_slug = $this->generate_postname_hash() . '-' . $original_meta['type'];
    	$new_post = array(
        	'menu_order'     => $post->menu_order,
        	'comment_status' => $post->comment_status,
        	'ping_status'    => $post->ping_status,
        	'post_author'    => $post->post_author,
        	'post_content'   => $post->post_content,
        	'post_excerpt'   => $post->post_excerpt,
        	'post_mime_type' => $post->post_mime_type,
        	'post_parent'    => 0,
        	'post_password'  => $post->post_password,
        	'post_status'    => 'publish',
        	'post_title'     => ! empty( $post->post_title ) ? $post->post_title . ' Clone' : $post->post_name . ' Clone',
        	'post_type'      => $post->post_type,
        	'post_date'      => date( 'Y-m-d H:i:s', strtotime( '-5 seconds', strtotime( $post->post_date ) ) ),
        	'post_date_gmt'  => date( 'Y-m-d H:i:s', strtotime( '-5 seconds', strtotime( $post->post_date_gmt ) ) ),
        	'post_name'      => $new_slug
    	);

        // Insert the clone into the database.
    	$new_post_id = wp_insert_post( $new_post );

    	// Update the original optin with a reference to the cloned instance.
    	$original_meta['has_clone'] = $new_post_id;
    	update_post_meta( $post->ID, '_om_meta', $original_meta );

    	// If there is any custom CSS, update the slug references.
    	if ( ! empty( $original_meta['custom_css'] ) ) {
        	$new_meta['custom_css'] = str_replace( $post->post_name, strtolower( $new_slug ), $original_meta['custom_css'] );
    	}

    	// Update the new optin with a reference to the parent clone.
    	$new_meta['is_clone'] = $post->ID;
    	update_post_meta( $new_post_id, '_om_meta', $new_meta );

    	// If the original optin had an image, carry over to the new clone.
    	if ( has_post_thumbnail( $post->ID ) )
    	    set_post_thumbnail( $new_post_id, get_post_thumbnail_id( $post->ID ) );

    	// Delete any transient data from the original optin to ensure the clone starts working immediately.
    	delete_transient( 'om_optin_' . $post->ID );
        delete_transient( 'om_optin_' . $post->post_name );
        delete_transient( 'om_optin_' . $new_post_id );
        delete_transient( 'om_optin_meta_' . $post->post_name );

        // Set an update message.
        $this->base->set_error( 'om-success', 'You have created a split test for ' . ( ! empty( $post->post_title ) ? $post->post_title : $post->post_name ) . ' successfully!' );

	}

	/**
	 * Does a split test for the optin
	 *
	 * @since 1.0.0
	 */
	public function do_optin_duplicate() {

    	$post = get_post( absint( $_GET['duplicate'] ) );

    	$original_meta = $new_meta = get_post_meta( $post->ID, '_om_meta', true );

        $new_slug = $this->generate_postname_hash() . '-' . $original_meta['type'];
    	$new_post = array(
        	'menu_order'     => $post->menu_order,
        	'comment_status' => $post->comment_status,
        	'ping_status'    => $post->ping_status,
        	'post_author'    => $post->post_author,
        	'post_content'   => $post->post_content,
        	'post_excerpt'   => $post->post_excerpt,
        	'post_mime_type' => $post->post_mime_type,
        	'post_parent'    => 0,
        	'post_password'  => $post->post_password,
        	'post_status'    => 'publish',
        	'post_title'     => ! empty( $post->post_title ) ? $post->post_title . ' Duplicate' : $post->post_name . ' Duplicate',
        	'post_type'      => $post->post_type,
        	'post_date'      => date( 'Y-m-d H:i:s', strtotime( '-5 seconds', strtotime( $post->post_date ) ) ),
        	'post_date_gmt'  => date( 'Y-m-d H:i:s', strtotime( '-5 seconds', strtotime( $post->post_date_gmt ) ) ),
        	'post_name'      => $new_slug
    	);

        // Insert the clone into the database.
    	$new_post_id = wp_insert_post( $new_post );

    	// Update the original post meta to the duplicate.
    	$custom = get_post_custom( $post->ID );
    	foreach ( $custom as $key => $value ) {
    	    if ( empty( $value[0] ) ) {
        	    continue;
    	    }

    	    $value = maybe_unserialize( $value[0] );
            update_post_meta( $new_post_id, $key, $value );
        }

    	// If there is any custom CSS, update the slug references.
    	if ( ! empty( $original_meta['custom_css'] ) ) {
        	$new_meta['custom_css'] = str_replace( $post->post_name, strtolower( $new_slug ), $original_meta['custom_css'] );
    	}

    	// Update the post meta.
    	update_post_meta( $new_post_id, '_om_meta', $new_meta );

    	// If the original optin had an image, carry over to the new clone.
    	if ( has_post_thumbnail( $post->ID ) )
    	    set_post_thumbnail( $new_post_id, get_post_thumbnail_id( $post->ID ) );

    	// Delete any transient data from the original optin to ensure the clone starts working immediately.
    	delete_transient( 'om_optin_' . $post->ID );
        delete_transient( 'om_optin_' . $post->post_name );
        delete_transient( 'om_optin_' . $new_post_id );
        delete_transient( 'om_optin_meta_' . $post->post_name );

        // Set an update message.
        $this->base->set_error( 'om-success', 'You have duplicated ' . ( ! empty( $post->post_title ) ? $post->post_title : $post->post_name ) . ' successfully!' );

	}

	/**
	 * Makes a B optin from a split test the primary optin and deletes the original.
	 *
	 * @since 1.0.0
	 */
	public function do_optin_make_primary() {

    	$clone_to_make_primary      = get_post( absint( $_GET['make-primary'] ) );
    	if ( ! $clone_to_make_primary ) return;

    	$clone_to_make_primary_meta = get_post_meta( $clone_to_make_primary->ID, '_om_meta', true );
    	$original_id_to_update      = $clone_to_make_primary_meta['is_clone'];
    	$original_optin             = get_post( $original_id_to_update );

    	// Update the details of the original optin.
    	$original_to_update = array(
    	    'ID'             => $original_id_to_update,
        	'menu_order'     => $clone_to_make_primary->menu_order,
        	'comment_status' => $clone_to_make_primary->comment_status,
        	'ping_status'    => $clone_to_make_primary->ping_status,
        	'post_author'    => $clone_to_make_primary->post_author,
        	'post_content'   => $clone_to_make_primary->post_content,
        	'post_excerpt'   => $clone_to_make_primary->post_excerpt,
        	'post_mime_type' => $clone_to_make_primary->post_mime_type,
        	'post_parent'    => 0,
        	'post_password'  => $clone_to_make_primary->post_password,
        	'post_status'    => 'publish',
        	'post_title'     => ! empty( $clone_to_make_primary->post_title ) ? trim( str_replace( 'Clone', '', $clone_to_make_primary->post_title ) ) : $original_optin->post_name,
        	'post_type'      => $clone_to_make_primary->post_type,
        	'post_date'      => date( 'Y-m-d H:i:s', strtotime( $clone_to_make_primary->post_date ) ),
        	'post_date_gmt'  => date( 'Y-m-d H:i:s', strtotime( $clone_to_make_primary->post_date_gmt ) )
    	);

        // Update the original optin with the new information.
    	$update = wp_update_post( $original_to_update );

    	// Update the original optin meta with the cloned meta (and remove original clone reference).
    	unset( $clone_to_make_primary_meta['is_clone'] );
    	update_post_meta( $original_id_to_update, '_om_meta', $clone_to_make_primary_meta );
    	update_post_meta( $original_id_to_update, 'om_counter', (int) get_post_meta( $clone_to_make_primary->ID, 'om_counter', true ) );
    	update_post_meta( $original_id_to_update, 'om_conversions', (int) get_post_meta( $clone_to_make_primary->ID, 'om_conversions', true ) );

    	// Delete the clone altogether.
    	wp_delete_post( $clone_to_make_primary->ID, true );

    	// Delete any transient data from the original optin.
    	delete_transient( 'om_optin_' . $original_id_to_update );
        delete_transient( 'om_optin_' . $original_optin->post_name );
        delete_transient( 'om_optin_meta_' . $original_optin->post_name );

        // Set an update message.
        $this->base->set_error( 'om-success', 'You have made your split test ' . ( ! empty( $clone_to_make_primary->post_title ) ? trim( str_replace( 'Clone', '', $clone_to_make_primary->post_title ) ) : $original_optin->post_name ) . ' the primary optin!' );

	}

	/**
	 * Resets the optin stats.
	 *
	 * @since 1.0.0
	 */
	public function do_optin_reset_stats() {

    	$optin_to_reset_stats = get_post( absint( $_GET['reset-stats'] ) );
    	if ( ! $optin_to_reset_stats ) return;

    	// Reset the meta stat counters.
    	delete_post_meta( $optin_to_reset_stats->ID, 'om_counter' );
    	delete_post_meta( $optin_to_reset_stats->ID, 'om_conversions' );

        // Set an update message.
        $this->base->set_error( 'om-success', sprintf( __( 'You have successfully reset the stats for %s!', 'optin-monster' ), ( empty( $optin_to_reset_stats->post_title ) ? $optin_to_reset_stats->post_name : $optin_to_reset_stats->post_title ) ) );

	}

	/**
	 * Puts the optin in test mode.
	 *
	 * @since 1.2.0
	 */
	public function do_optin_test_mode() {

    	$optin = get_post( absint( $_GET['test-mode'] ) );
    	if ( ! $optin ) return;

    	// Set optin test mode.
    	update_post_meta( $optin->ID, '_om_test_mode', true );

        // Set an update message.
        $this->base->set_error( 'om-success', sprintf( __( 'You have placed %s in test mode! No cookies will be set while test mode is activated.', 'optin-monster' ), ( empty( $optin->post_title ) ? $optin->post_name : $optin->post_title ) ) );

	}

	/**
	 * Puts the optin in live mode.
	 *
	 * @since 1.2.0
	 */
	public function do_optin_live_mode() {

    	$optin = get_post( absint( $_GET['live-mode'] ) );
    	if ( ! $optin ) return;

    	// Set optin test mode.
    	delete_post_meta( $optin->ID, '_om_test_mode' );

        // Set an update message.
        $this->base->set_error( 'om-success', sprintf( __( 'You have placed %s in live mode! Cookies will be set while the optin is live.', 'optin-monster' ), ( empty( $optin->post_title ) ? $optin->post_name : $optin->post_title ) ) );

	}

	/**
	 * Deletes an optin.
	 *
	 * @since 1.0.0
	 */
	public function do_optin_delete() {

        global $wpdb;
        $delete = get_posts( array( 'post_type' => 'optin', 'name' => $_GET['delete'], 'posts_per_page' => '1' ) );
        if ( ! $delete ) return;

		$title = ! empty( $delete[0]->post_title ) ? $delete[0]->post_title : $delete[0]->post_name;

		// If is a clone, remove references to parent optin.
		$meta = get_post_meta( $delete[0]->ID, '_om_meta', true );
		if ( isset( $meta['is_clone'] ) ) {
			$parent = get_post( $meta['is_clone'] );
			$parent_meta = get_post_meta( $parent->ID, '_om_meta', true );
			unset( $parent_meta['has_clone'] );
			update_post_meta( $parent->ID, '_om_meta', $parent_meta );
		}

		// If has a clone, delete the clone too.
		if ( isset( $meta['has_clone'] ) ) {
			// Delete the optin.
            wp_delete_post( $meta['has_clone'], true );

            // Remove the optin stats from the DB.
		    $wpdb->delete( $this->table, array( 'optin_id' => $meta['has_clone'] ), array( '%d' ) );
		}

		// If the optin has an image, possibly delete the image.
		if ( has_post_thumbnail( $delete[0]->ID ) ) {
		    if ( isset( $meta['is_clone'] ) ) {
		        // Only delete the image if the parent image is not the same.
		        $clone_thumb = get_post_thumbnail_id( $delete[0]->ID );
		        if ( has_post_thumbnail( $parent->ID ) ) {
    		        $parent_thumb = get_post_thumbnail_id( $parent->ID );

    		        // If the two don't match, go ahead and delete the clone image.
    		        if ( $clone_thumb !== $parent_thumb )
    		            wp_delete_attachment( get_post_thumbnail_id( $delete[0]->ID ), true );
		        } else {
		            // Delete the image because the clone parent does not have one.
    		        wp_delete_attachment( get_post_thumbnail_id( $delete[0]->ID ), true );
		        }
		    } else {
		        // Delete anyways since clone will be deleted too.
    		    wp_delete_attachment( get_post_thumbnail_id( $delete[0]->ID ), true );
		    }
        }

		// Delete the optin.
		wp_delete_post( $delete[0]->ID, true );

		// Remove the optin stats from the DB.
		$wpdb->delete( $this->table, array( 'optin_id' => $delete[0]->ID ), array( '%d' ) );

		// Set message.
		$this->base->set_error( 'om-success', 'The optin ' . ( empty( $delete[0]->post_title ) ? $delete[0]->post_name : $delete[0]->post_title ) . ' has been deleted successfully!' );

	}

	/**
	 * Outputs the content for the specified tab.
	 *
	 * @since 1.0.0
	 */
	public function do_tab_output() {

        $this->optins = get_posts( array( 'post_type' => 'optin', 'posts_per_page' => '-1' ) );
	    if ( ! empty( $this->base->errors ) ) : ?>
                <?php foreach ( $this->base->errors as $id => $message ) : ?>
                    <?php $class = 'om-success' == $id ? ' alert-success' : ' alert-error'; ?>
                    <div class="alert <?php echo $class; ?> <?php sanitize_html_class( $id ); ?>">
                        <p><strong><?php echo $message; ?></strong></p>
                    </div>
                <?php endforeach; ?>
        <?php endif;

        echo '<div class="account-dashboard om-clear">';
			echo '<div class="left pull-left">';
				echo '<a class="button button-primary button-large" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build' ), admin_url( 'admin.php' ) ) . '" title="'.__('Create New Optin','optin-monster').'">'.__('Create New Optin','optin-monster').'</a>';
			echo '</div>';
			echo '<div class="right pull-right">';
				echo '<table id="optin-list" class="optin-table">';
					echo '<thead>';
						echo '<tr>';
							echo '<th class="first">'.__('Name','optin-monster').'</th>';
							echo '<th>'.__('Impressions','optin-monster').'</th>';
							echo '<th>'.__('Conversions','optin-monster').'</th>';
							echo '<th>'.__('% Conversions','optin-monster').'</th>';
							echo '<th>'.__('Active','optin-monster').'</th>';
							echo '<th class="last">'.__('Settings','optin-monster').'</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
						if ( ! $this->optins ) :
							echo '<tr>';
								echo '<td colspan="6"><p class="no-padding no-margin">'.__('Looks like you haven\'t created any optins yet! ','optin-monster').'<a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build' ), admin_url( 'admin.php' ) ) . '" class="create-new-optin" title="'.__('Click Here to Create Your First Optin','optin-monster').'">'.__('Click here to create your first optin!','optin-monster').'</a></p></td>';
							echo '</tr>';
						else :
							$i = 1;
							foreach ( (array) $this->optins as $optin ) :
								$counter 	 = get_post_meta( $optin->ID, 'om_counter', true );
								$conversions = get_post_meta( $optin->ID, 'om_conversions', true );
								if ( 0 == $conversions || ! $counter ) {
									$conversion_display = '0.00';
								} else {
									$conversion_display = number_format( ($conversions/$counter) * 100, 2 );
								}
								$test = get_post_meta( $optin->ID, '_om_test_mode', true );
								$meta = get_post_meta( $optin->ID, '_om_meta', true );
								$clone = isset( $meta['is_clone'] ) ? 'is-clone' : '';
								$clone = isset( $meta['has_clone'] ) ? 'has-clone' : $clone;
								$is_test = $test ? 'om-is-test' : '';
								echo '<tr class="' . $clone . ' ' . $is_test . '">';
									echo '<td class="first">' . ( $optin->post_title ? $optin->post_title : $optin->post_name ) . ( $test ? ' <span class="om-test-mode">'.__('test mode','optin-monster').'</span>' : '' ) . '</td>';
									echo '<td>' . number_format( (int) $counter ) . '</td>';
									echo '<td>' . number_format( (int) $conversions ) . '</td>';
									echo '<td>' . $conversion_display . '%</td>';
									echo '<td>' . ( isset( $meta['display']['enabled'] ) && $meta['display']['enabled'] ? '<strong class="green">'.__('Yes','optin-monster').'</strong>' : '<strong class="red">'.__('No','optin-monster').'</strong>' ) . '</td>';
									echo '<td class="last">';
										echo '<div class="optin-settings-box">';
										echo '<a href="#" class="optin-settings" title="'.__('Optin Settings','optin-monster').'"></a>';
											echo '<ul class="optin-action-links" style="display: none;">';
												echo '<li><a class="first" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'config' => true, 'type' => $meta['type'], 'edit' => $optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Modify Optin','optin-monster').'">'.__('Modify','optin-monster').'</a></li>';

												// Only output if this optin has no clone or is not a clone.
												if ( empty( $clone ) )
												    echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'replicate' => $optin->ID ), admin_url( 'admin.php' ) ) . '" title="'.__('Split Test Optin','optin-monster').'">'.__('Split Test','optin-monster').'</a></li>';
												else if ( 'is-clone' == $clone )
												    echo '<li><a class="optin-primary" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'make-primary' => $optin->ID ), admin_url( 'admin.php' ) ) . '" title="'.__('Make Primary','optin-monster').'">'.__('Make Primary','optin-monster').'</a></li>';
												echo '<li><a class="optin-duplicate" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'duplicate' => $optin->ID ), admin_url( 'admin.php' ) ) . '" title="'.__('Duplicate','optin-monster').'">'.__('Duplicate','optin-monster').'</a></li>';
												echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'code' => 1, 'type' => $meta['type'], 'edit' => $optin->post_name ), admin_url( 'admin.php' ) ) . '" data-optin="#optin-code-' . $i . '" class="optin-embed" title="'.__('Output Settings','optin-monster').'">'.__('Output Settings','optin-monster').'</a></li>';
												if ( $this->base->is_reporting_active() ) {
												    echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'reports', 'switch' => $optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Optin Reports','optin-monster').'">'.__('Reports','optin-monster').'</a></li>';
												}
												echo '<li><a class="optin-reset" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'reset-stats' => $optin->ID ), admin_url( 'admin.php' ) ) . '" title="'.__('Reset Stats','optin-monster').'" data-optin="' . $optin->post_title . '">'.__('Reset Stats','optin-monster').'</a></li>';
												if ( $test ) {
												    echo '<li><a class="optin-test" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'live-mode' => $optin->ID ), admin_url( 'admin.php' ) ) . '" title="'.__('Disable Test Mode','optin-monster').'" data-optin="' . $optin->post_title . '">'.__('Disable Test Mode','optin-monster').'</a></li>';
												} else {
												    echo '<li><a class="optin-test" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'test-mode' => $optin->ID ), admin_url( 'admin.php' ) ) . '" title="'.__('Enable Test Mode','optin-monster').'" data-optin="' . $optin->post_title . '">'.__('Enable Test Mode','optin-monster').'</a></li>';
												}
												echo '<li><a class="optin-delete" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'delete' => $optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Delete Optin','optin-monster').'" data-optin="' . $optin->post_title . '">'.__('Delete','optin-monster').'</a></li>';
												echo '<li><p class="om-slug-name"><em>Unique Optin Slug</em><br /><strong class="om-slug-output">' . $optin->post_name . '</strong></p></li>';
											echo '</ul>';
										echo '</div>';
									echo '</td>';
								echo '</tr>';
								$i++;
							endforeach;
						endif;
					echo '</tbody>';
				echo '</table>';
			echo '</div>';
		echo '</div>';

		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('.optin-settings').on('click', function(e){
					e.preventDefault();
					$('.optin-action-links').removeClass('open');
					if ( ! $(this).next().is(':visible') )
						$(this).next().show().addClass('open');
					$('.optin-action-links:not(.open)').hide();
				});
				$('.optin-delete').on('click', function(){
					return confirm('Are you sure you want to delete ' + $(this).data('optin') + '?');
				});
				$('.optin-primary').on('click', function(){
    				return confirm('Are you sure you want to make this split test the primary optin? The current primary optin will be overwritten with your split test data.');
				});
				$('.optin-reset').on('click', function(){
					return confirm('Are you sure you want to reset the stats for ' + $(this).data('optin') + '?');
				});
			});
		</script>
		<?php

	}

	public function do_optin_build() {

    	echo '<div class="create-optin-wrap om-clearfix">';
			echo '<ul class="create-optin-nav om-clearfix">';
				echo '<li><a class="active" href="#" title="'.__('Setup Your Optin','optin-monster').'" data-tab="om-optin-setup">'.__('1. Setup','optin-monster').'</a></li>';
				echo '<li><a class="disabled" href="#" title="'.__('Design Your Optin','optin-monster').'" data-tab="om-optin-configure">'.__('2. Configure','optin-monster').'</a></li>';
				echo '<li><a class="disabled" href="#" title="'.__('Configure Your Optin','optin-monster').'" data-tab="om-optin-design">'.__('3. Design','optin-monster').'</a></li>';
				echo '<li><a class="disabled" href="#" title="'.__('Output Settings Your Optin','optin-monster').'" data-tab="om-optin-embed">'.__('4. Output Settings','optin-monster').'</a></li>';
			echo '</ul>';
			echo '<div class="create-optin-area" class="om-clearfix">';
				echo '<div id="om-optin-setup" class="optin-ui">';
					echo '<h2>Select Optin Type</h2>';
					echo '<div class="optin-select-wrap om-clearfix">';
						echo '<div class="optin-item one-fourth first" data-optin-type="lightbox">';
							echo '<h4>' . __( 'Lightbox', 'optin-monster' ) . '</h4>';
							echo '<img src="' . plugins_url( 'inc/css/images/lightboxicons.png', $this->base->file ) . '" />';
						echo '</div>';

						// Run an action hook to add new optin types to the mix.
						do_action( 'optin_monster_optin_types' );
					echo '</div>';
				echo '</div>';
			echo '</div>';
			echo '<div class="create-optin-toolbar om-clearfix">';
				echo '<a class="button grey previous-step disabled button-secondary button-large" href="#" title="'.__('Back to Previous Step','optin-monster').'" disabled="disabled" data-tab="">'.__('Back','optin-monster').'</a>';
				echo '<a class="button orange next-step disabled button-primary button-large" href="#" title="'.__('Forward to Next Step','optin-monster').'" data-tab="om-optin-setup">'.__('Next Step','optin-monster').'</a>';
			echo '</div>';
		echo '</div>';

	}

	public function do_optin_build_script() {

    	?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				var step, optin;
				$(document.body).on('click', '.optin-item', function(e){
					e.preventDefault();
					var selected = $(this);
					$(this).parent().parent().find('.selected').removeClass('selected');
					$(this).addClass('selected');
					$('.next-step').removeClass('disabled').attr('href', '<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'optins', 'action' => 'build', 'config' => true ) ); ?>');
				});
				$(document.body).on('click', '.next-step', function(e){
				    e.preventDefault();
				    var $this = $(this),
				        selected = $('.optin-item.selected'),
				        url = $this.attr('href');
				    window.location.href = url + '&type=' + selected.data('optin-type');
				});
			});
		</script>
		<?php

		// Run a hook for any extra build scripts.
		do_action( 'optin_monster_build_script' );

	}

	public function do_optin_config() {

    	echo '<div class="create-optin-wrap om-clearfix">';
			echo '<ul class="create-optin-nav om-clearfix">';
				echo '<li><a class="disabled" href="#" title="'.__('Setup Your Optin','optin-monster').'" data-tab="om-optin-setup">'.__('1. Setup','optin-monster').'</a></li>';
				echo '<li><a class="active" href="#" title="'.__('Configure Your Optin','optin-monster').'" data-tab="om-optin-configure">'.__('2. Configure','optin-monster').'</a></li>';
				if ( $this->meta )
				    echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'design' => 1, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Design Your Optin','optin-monster').'" data-tab="om-optin-design">'.__('3. Design','optin-monster').'</a></li>';
				else
				    echo '<li><a class="disabled" href="#" title="'.__('Design Your Optin'.'optin-monster').'" data-tab="om-optin-design">'.__('3. Design','optin-monster').'</a></li>';

				if ( $this->meta )
				    echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'code' => true, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Output Settings Your Optin','optin-monster').'" data-tab="om-optin-embed">'.__('4. Output Settings','optin-monster').'</a></li>';
				else
				    echo '<li><a class="disabled" href="#" title="'.__('Output Settings Your Optin','optin-monster').'" data-tab="om-optin-embed">'.__('4. Output Settings','optin-monster').'</a></li>';
			echo '</ul>';
			echo '<div class="create-optin-area om-clearfix">';
				echo '<form id="om-optin-configure" class="optin-ui">';
					if ( $this->cc_auth )
						echo '<div class="alert alert-success" style="margin-top: 0;"><p><strong>'.__('You have authenticated OptinMonster with Constant Contact successfully. You can continue building your optin.','optin-monster').'</strong></p></div>';
					echo '<h2>'.__('Configure Optin Settings','optin-monster').'</h2>';
					echo '<div id="optin-configuration">';
						echo '<div class="optin-config-box">';
							echo '<h4><label for="optin-campaign-title">'.__('Optin Title','optin-monster').'</label></h4>';
							echo '<p class="description">'.__('This is the internal title of your optin for easy reference. Think of it as your optin campaign title.','optin-monster').'</p>';
							echo '<input id="optin-campaign-title" type="text" name="optin_campaign_title" value="' . ( isset( $this->optin->post_title ) ? $this->optin->post_title : '' ) . '" placeholder="'.__('Email List Explosion','optin-monster').'" />';
						echo '</div>';
						echo '<div class="optin-config-box">';
							echo '<h4><label for="optin-delay">'.__('Optin Loading Delay','optin-monster').'</label></h4>';
							echo '<p class="description">'.__('This is how long the page should wait (<span class="blue">in milliseconds</span>) before loading the optin (defaults to 0 for no delay).','optin-monster').'</p>';
							echo '<input id="optin-delay" type="text" name="optin_delay" value="' . $this->get_field( 'delay' ) . '" placeholder="0" />';
						echo '</div>';
						echo '<div class="optin-config-box">';
							echo '<h4><label for="optin-cookie">'.__('Optin Cookie Duration','optin-monster').'</label></h4>';
							echo '<p class="description">'.__('This is the length of time before the optin will display again after a user exits the optin (defaults to 7 days).','optin-monster').'</p>';
							echo '<input id="optin-cookie" type="text" name="optin_cookie" value="' . $this->get_field( 'cookie' ) . '" placeholder="7" />';
						echo '</div>';
						if ( 'canvas' !== $this->type ) {
    						echo '<div class="optin-config-box">';
    							echo '<h4><label for="optin-redirect">'.__('Redirect on Optin Success?','optin-monster').'</label></h4>';
                                echo '<p class="description">'.__('Optionally specify a URL to redirect to after a visitor has successfully opted in to this optin.','optin-monster').'</p>';
    							echo '<input id="optin-rdirect" type="text" name="optin_redirect" value="' . $this->get_field( 'redirect' ) . '" placeholder="'.__('e.g. http://yourdomain.com/thanks/','optin-monster').'" />';
    						echo '</div>';
                        }

						echo '<div class="optin-config-box">';
							echo '<h4><label for="optin-second">'.__('Load on Second Pageview?','optin-monster').'</label></h4>';
                            echo '<input id="optin-second" type="checkbox" name="optin_second" value="' . $this->get_field( 'second' ) . '"' . checked( $this->get_field( 'second' ), 1, false ) . ' />';
                            echo '<label class="description" for="optin-second" style="font-weight:400;display:inline;margin-left:5px">'.__('Checking this setting forces the optin to load on the second pageview for the visitor, not the first.','optin-monster').'</label>';
						echo '</div>';

						echo '<div class="optin-config-box">';
							echo '<h4><label for="optin-logged-in-hide">'.__('Hide for Logged-in Users?','optin-monster').'</label></h4>';
                            echo '<input id="optin-logged-in-hide" type="checkbox" name="optin_logged_in" value="' . $this->get_field( 'logged_in' ) . '"' . checked( $this->get_field( 'logged_in' ), 1, false ) . ' />';
                            echo '<label class="description" for="optin-logged-in-hide" style="font-weight:400;display:inline;margin-left:5px">'.__('Checking this setting will hide this optin for logged in users.','optin-monster').'</label>';
						echo '</div>';

                        if ( $this->type && 'lightbox' == $this->type || $this->type && 'canvas' == $this->type ) {
    						echo '<div class="optin-config-box">';
    							echo '<h4><label for="optin-powered-by">'.__('Show Powered By Link?','optin-monster').'</label></h4>';
    							if ( ! isset( $this->meta['powered_by'] ) && ! isset( $this->meta['cookie'] ) ) {
                                    echo '<input id="optin-powered-by" type="checkbox" name="optin_powered_by" value="1" checked="checked" />';
                                } else {
                                    echo '<input id="optin-powered-by" type="checkbox" name="optin_powered_by" value="' . $this->get_field( 'powered_by' ) . '"' . checked( $this->get_field( 'powered_by' ), 1, false ) . ' />';
                                }
                                echo '<label class="description" for="optin-powered-by" style="font-weight:400;display:inline;margin-left:5px">'.__('Show a "powered by" link below your optin. If your affiliate link is set in the','optin-monster').' <a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'misc' ), admin_url( 'admin.php' ) ) . '" title="'.__('Misc Settings">Misc settings','optin-monster').'</a>, '.__('it will be used.','optin-monster').'</label>';
    						echo '</div>';
                        }

						// Hook to add in custom config options.
						do_action( 'optin_monster_config_settings', $this->optin, $this->type );

                        if ( 'canvas' !== $this->type ) {
    						echo '<div class="optin-config-box">';
    							echo '<h4><label for="optin-providers">'.__('Email Provider Settings','optin-monster').'</label></h4>';
    							echo '<p class="description">'.__('It\'s time to connect this optin to an email marketing provider. Select one of our supported providers from the list below and fill out the necessary details. You can always update your list of email marketing providers on your account page.','optin-monster').'</p>';
    							echo '<select id="optin-providers" name="optin_email_provider">';
    								echo '<option value="none">'.__('Select your email marketing provider...','optin-monster').'</option>';
    								foreach ( $this->account->get_email_services() as $array => $data ) :
    									echo '<option value="' . $data['value'] . '">' . $data['name'] . '</option>';
    								endforeach;
    							echo '</select>';
    						echo '</div>';
                        }
					echo '</div>';
				echo '</form>';
			echo '</div>';
			echo '<div class="create-optin-toolbar om-clearfix">';
				echo '<a class="button button-secondary button-large grey previous-step" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build' ), admin_url( 'admin.php' ) ) . '" title="'.__('Back to Previous Step','optin-monster').'" data-tab="">'.__('Back','optin-monster').'</a>';
				echo '<a class="button button-primary button-large orange next-step" href="#" title="'.__('Next Step!','optin-monster').'">'.__('Save and Design Optin','optin-monster').'</a>';
			echo '</div>';
		echo '</div>';

	}

	public function do_optin_config_script() {

    	?>
		<script type="text/javascript">
			var optin_id = '<?php echo ( isset( $_GET['edit'] ) && $_GET['edit'] ) ? $_GET['edit'] : false; ?>',
				email_provider = '<?php echo $this->get_field( 'email', 'provider' ); ?>',
				email_account = '<?php echo $this->get_field( 'email', 'account' ); ?>',
				email_account_changed = false,
				email_list = '<?php echo $this->get_field( 'email', 'list_id' ); ?>',
				email_list_changed = false,
				email_client = '<?php echo $this->get_field( 'email', 'client_id' ); ?>',
				email_client_changed = false,
				email_segments = '<?php echo json_encode( $this->get_field( 'email', 'segments' ) ); ?>',
				email_segments_changed = false,
				icon = '<?php echo includes_url() . 'images/wpspin.gif'; ?>';
			jQuery(document).ready(function($){
			    $(document).on('change', '#optin-providers', function(e){
					$('.loading, #om-email-creds, #om-email-accounts, #om-email-lists, #om-email-clients').remove();
					var $this = $(this).find(':selected');
					if ( $this.hasClass('selected') ) return;
					$(this).after('<img style="margin-left: 5px;" class="loading" src="' + icon + '" alt="" />');
					$(e.target).find('.selected').removeClass('selected');
					$.post(ajaxurl, { action: 'get_all_email_accounts', email: $(this).val(), type: '<?php echo $this->type; ?>', optin: optin_id }, function(resp){
					    $(e.target).after(resp);
					    $this.addClass('selected');
					    $('.loading').remove();

					    // If using MailPoet, force loading of lists to select.
					    if ( 'mailpoet' == $this.val() ) {
    					    $('#om-email-account option[value="mailpoet"]').attr('selected', 'selected');
                            $('#om-email-account').val($this.val()).trigger('change');
                            return;
					    }

					    // If we have an email list, let's do stuff.
						if ( email_account.length > 0 && ! email_account_changed ) {
							email_account_changed = true;
							$('#om-email-account option').each(function(){
								if ( $(this).val() == email_account ) {
									$(this).attr('selected', 'selected');
									$('#om-email-account').val(email_account).trigger('change');
									return false;
								}
							});
						}
					}, 'json');
				});

				// If there is already a provider selected, trigger and retrieve the data.
				if ( email_provider.length > 0 ) {
					$('#optin-providers option').each(function(){
						if ( $(this).val() == email_provider ) {
							$(this).attr('selected', 'selected');
							$('#optin-providers').val(email_provider).trigger('change');
							return false;
						}
					});
				}

                $(document).on('change', '#om-email-account', function(e){
				    $('.loading, #om-email-creds, #om-email-lists, #om-email-clients').remove();
				    var $this = $(this).find(':selected');
					if ( $this.hasClass('selected') || 'none' == $this.val() ) return;
					$(this).after('<img style="margin-left: 5px;" class="loading" src="' + icon + '" alt="" />');
					$(e.target).find('.selected').removeClass('selected');
				    if ( 'new' == $(this).val() ) {
				        $.post(ajaxurl, { action: 'get_new_email_provider', email: $('#optin-providers').find(':selected').val(), type: '<?php echo $this->type; ?>' }, function(resp){
    						$(e.target).after(resp);
    						$this.addClass('selected');
    						$('.loading').remove();
    					}, 'json');
    				} else {
    				    $.post(ajaxurl, { action: 'get_email_provider', email: $this.val(), provider: $('#optin-providers').find(':selected').val(), type: '<?php echo $this->type; ?>' }, function(resp){
    						$(e.target).after(resp);
    						$this.addClass('selected');
    						$('.loading').remove();
    						// If we have an email list, let's do stuff.
    						if ( email_list.length > 0 && ! email_list_changed && email_client.length <= 0 ) {
    							email_list_changed = true;
    							$('#om-email-list option').each(function(){
    								if ( $(this).val() == email_list ) {
    									$(this).attr('selected', 'selected');
    									$('#om-email-list').val(email_list).trigger('change');
    									return false;
    								}
    							});
    						}

    						// If we have an email client to choose from, let's do stuff.
    						if ( email_client.length > 0 && ! email_client_changed ) {
    							email_client_changed = true;
    							$('#om-email-client option').each(function(){
    								if ( $(this).val() == email_client ) {
    									$(this).attr('selected', 'selected');
    									$('#om-email-client').val(email_client).trigger('change');
    									return false;
    								}
    							});
    						}
    					}, 'json');
    				}
				});

				$(document).on('change', '#om-email-client', function(e){
					var $this = $(this).find(':selected');
					if ( $this.hasClass('selected') ) return;
					$('#om-email-lists, #om-email-segments').slideUp(300).remove();
					$(this).after('<img style="margin-left: 5px;" class="loading" src="' + icon + '" alt="" />');
					$('#om-email-client').find('.selected').removeClass('selected');
					$.post(ajaxurl, { action: 'get_email_provider_data', provider: $('#optin-providers').find(':selected').val(), email: $('#om-email-account').find(':selected').val(), client: $(this).find(':selected').val() }, function(resp){
						$('#om-email-client').after(resp);
						$this.addClass('selected');
						$('.loading').remove();
						// If we have an email list, let's do stuff.
						if ( email_list.length > 0 && ! email_list_changed ) {
							email_list_changed = true;
							$('#om-email-list option').each(function(){
								if ( $(this).val() == email_list ) {
									$(this).attr('selected', 'selected');
									$('#om-email-list').val(email_list).trigger('change');
									return false;
								}
							});
						}
					}, 'json');
				});
				$(document).on('change', '#om-email-list', function(e){
					var $this = $(this).find(':selected');
					if ( $this.hasClass('selected') ) return;
					$('#om-email-segments').slideUp(300).remove();
					$(this).after('<img style="margin-left: 5px;" class="loading" src="' + icon + '" alt="" />');
					$('#om-email-list').find('.selected').removeClass('selected');
					$.post(ajaxurl, { action: 'get_email_provider_segment', provider: $('#optin-providers').find(':selected').val(), email: $('#om-email-account').find(':selected').val(), list: $(this).find(':selected').val(), client: $('#om-email-client').find(':selected').val() }, function(resp){
						$('#om-email-list').after(resp);
						$this.addClass('selected');
						$('.loading').remove();
						// If we have email segments, let's do stuff.
						if ( email_segments.length > 0 && ! email_segments_changed ) {
							email_segments_changed = true;
							email_segments = $.parseJSON(email_segments);
							if ( 'mailchimp' == email_provider ) {
    							$.each(email_segments, function(group, segments){
    								$('#om-email-segments').find('input[type="checkbox"]').each(function(i){
    									var value = $(this).attr('data-subgroup-name'),
    										group_id = $(this).attr('data-group-id');
    									if ( group === group_id && segments.indexOf(value) >= 0 ) {
    										$(this).prop('checked', true);
    									}
    								});
    							});
                            } else if ( 'campaign-monitor' == email_provider ) {
                                $.each(email_segments, function(i, segment){
    								$('#om-email-segments').find('input[type="checkbox"]').each(function(i){
    									var value = $(this).attr('data-subgroup-name');
    									if ( segment.indexOf(value) >= 0 ) {
    										$(this).prop('checked', true);
    									}
    								});
    							});
                            }
						}
					}, 'json');
				});

				$(document).on('click', '.connect-api', function(e){
					e.preventDefault();
					var $this = $(this),
					default_txt = $this.text();
					$this.text('Connecting...');
					$('.error').remove();
					$(this).after('<img style="margin-left: 5px;" class="loading" src="' + icon + '" alt="" />');
					$.post(ajaxurl, { action: 'connect_email', data: $('#om-email-creds').serialize(), type: $this.data('email-provider') }, function(resp){
						if ( resp && resp.error ) {
							$('#om-email-creds').append('<p class="error no-margin">' + resp.error + '</p>');
							$('.loading').remove();
						} else {
						    var email_account_node = $('#om-email-account');
						    if ( email_account_node.length > 0 ) {
							    $('<option value="' + resp.email_id + '" selected="selected">' + resp.email_label + '</option>').insertBefore($('#om-email-account option[value="new"]'));
							    $('#om-email-creds').html(resp.success);
                            } else {
                                $('#om-email-creds').html('<div id="om-email-accounts"><p class="padding-top"><strong>Select a ' + $('#optin-providers option:selected').text() + ' account to use for this optin.</strong></p><select id="om-email-account" name="optin_email_account" data-email-provider="' + $('#optin-providers').val() + '"><option value="none">Select your ' + $('#optin-providers option:selected').text() + ' account...</option><option value="' + resp.email_id + '" selected="selected">' + resp.email_label + '</option><option value="new">Add a new account...</option></select></div>' + resp.success);
                                $('.loading').remove();
                            }

                        }
						$('.connect-api').text(default_txt);
					}, 'json');
				});
				$(document).on('click', '.next-step', function(e){
					e.preventDefault();
					var $this = $(this),
						text  = $this.text();
					$this.text('Saving...');
					$('.error').remove();

					// If no email provider has been selected, require them to do so first.
					<?php if ( 'canvas' !== $this->type ) : ?>
					if ( 'none' == $('#optin-providers').find(':selected').val() ) {
					    $('.create-optin-area').append('<p class="error text-right no-margin"><?php _e('An email provider must be selected before you can continue.','optin-monster'); ?></p>');
                        $this.text(text);
                        return;
                    }

                    // If no email provider has been selected, require them to do so first.
					if ( 'none' == $('#om-email-account').find(':selected').val() ) {
					    $('.create-optin-area').append('<p class="error text-right no-margin"><?php _e('An email account must be selected before you can continue.','optin-monster'); ?></p>');
                        $this.text(text);
                        return;
                    }

                    // If no email list has been selected, require them to do so first.
					if ( ( $('#om-email-list').length === 0 || 'none' == $('#om-email-list').find(':selected').val() ) && 'custom' !== $('#optin-providers').find(':selected').val() ) {
					    $('.create-optin-area').append('<p class="error text-right no-margin"><?php _e('An email list must be selected before you can continue.','optin-monster'); ?></p>');
                        $this.text(text);
                        return;
                    }
                    <?php endif; ?>

					var data = {
						action: 'save_optin_config',
						type: '<?php echo $this->type; ?>',
						optin: optin_id,
						data: $('#om-optin-configure, #om-email-creds').serialize(),
					};

					$.post(ajaxurl, data, function(resp){
						if ( resp && resp.error ) {
							$('.create-optin-area').append('<p class="error text-right no-margin">' + resp.error + '</p>');
							$this.text(text);
						} else {
							window.location.href = '<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'design' => true, 'type' => $this->type ), admin_url( 'admin.php' ) ); ?>&edit=' + resp;
						}
					}, 'json');
				});
				var aw_auth = false;
				$(document).on('click', '.aweber-auth', function(e){
				    e.preventDefault();
				    $('.error').remove();
				    var label = $('#email-label'),
				        $this = $(this),
				        default_txt = $this.text();
				    if ( label.val().length == 0 ) {
    				    $('.create-optin-area').append('<p class="error text-right no-margin"><?php _e('You must enter a label for this account before proceeding.','optin-monster'); ?></p>');
                        return;
				    }
				    if ( ! aw_auth ) {
				        window.open( 'https://auth.aweber.com/1.0/oauth/authorize_app/f5b114f8', '', 'resizable=yes,location=no,width=750,height=600,top=0,left=0' ); aw_auth = true; return;
				    } else {
    				    $this.after('<img style="margin-left: 5px;" class="loading" src="' + icon + '" alt="" />');
    					$.post(ajaxurl, { action: 'connect_email', data: $('#om-email-creds').serialize(), type: $this.data('email-provider') }, function(resp){
    						if ( resp && resp.error ) {
    							$('#om-email-creds').append('<p class="error no-margin">' + resp.error + '</p>');
    							$('.loading').remove();
    							aw_auth = false;
    						} else {
    						    var email_account_node = $('#om-email-account');
    						    if ( email_account_node.length > 0 ) {
    							    $('<option value="' + resp.email_id + '" selected="selected">' + resp.email_label + '</option>').insertBefore($('#om-email-account option[value="new"]'));
    							    $('#om-email-creds').html(resp.success);
                                } else {
                                    $('#om-email-creds').html('<div id="om-email-accounts"><p class="padding-top"><strong>Select a ' + $('#optin-providers option:selected').text() + ' account to use for this optin.</strong></p><select id="om-email-account" name="optin_email_account" data-email-provider="' + $('#optin-providers').val() + '"><option value="none">Select your ' + $('#optin-providers option:selected').text() + ' account...</option><option value="' + resp.email_id + '" selected="selected">' + resp.email_label + '</option><option value="new">Add a new account...</option></select></div>' + resp.success);
                                    $('.loading').remove();
                                }

                            }
    						$('.connect-api').text(default_txt);
    					}, 'json');
				    }
				});
				$(document).on('click', '.cc-auth', function(e){
				    e.preventDefault();
				    $('.error').remove();
				    var label = $('#email-label'),
				        $this = $(this);
				    if ( label.val().length == 0 ) {
    				    $('.create-optin-area').append('<p class="error text-right no-margin"><?php _e('You must enter a label for this account before proceeding.','optin-monster'); ?></p>');
                        return;
				    }
				    var optin = '<?php echo $this->optin ? $this->optin->post_name : ""; ?>';
				    $.post(ajaxurl, { action: 'get_cc_auth_url', type: '<?php echo $this->type; ?>', label: label.val(), optin: optin }, function(resp){
				        var res = $.parseJSON(resp);
    				    window.location.href = res;
				    });
				});
			});
			function uniqid (prefix, more_entropy) {
                  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                  // +    revised by: Kankrelune (http://www.webfaktory.info/)
                  // %        note 1: Uses an internal counter (in php_js global) to avoid collision
                  // *     example 1: uniqid();
                  // *     returns 1: 'a30285b160c14'
                  // *     example 2: uniqid('foo');
                  // *     returns 2: 'fooa30285b1cd361'
                  // *     example 3: uniqid('bar', true);
                  // *     returns 3: 'bara20285b23dfd1.31879087'
                  if (typeof prefix === 'undefined') {
                    prefix = "";
                  }

                  var retId;
                  var formatSeed = function (seed, reqWidth) {
                    seed = parseInt(seed, 10).toString(16); // to hex str
                    if (reqWidth < seed.length) { // so long we split
                      return seed.slice(seed.length - reqWidth);
                    }
                    if (reqWidth > seed.length) { // so short we pad
                      return Array(1 + (reqWidth - seed.length)).join('0') + seed;
                    }
                    return seed;
                  };

                  // BEGIN REDUNDANT
                  if (!this.php_js) {
                    this.php_js = {};
                  }
                  // END REDUNDANT
                  if (!this.php_js.uniqidSeed) { // init seed with big random int
                    this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
                  }
                  this.php_js.uniqidSeed++;

                  retId = prefix; // start with prefix, add current milliseconds hex string
                  retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
                  retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
                  if (more_entropy) {
                    // for more entropy we add a float lower to 10
                    retId += (Math.random() * 10).toFixed(8).toString();
                  }

                  return retId;
            }
		</script>
		<?php

		// Run a hook for any extra config scripts.
		do_action( 'optin_monster_config_script' );

	}

	public function do_optin_design() {

    	// Load necessary scripts and styles for the design output.
    	wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' );
		wp_enqueue_script( 'plupload-all' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'iris' );
	    wp_enqueue_script( 'wp-color-picker' );
	    wp_enqueue_script( 'google-font-loader', '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js', array(), '1.4.7', true );
	    wp_enqueue_script( 'jquery-resize', plugins_url( '/inc/js/resize.js', $this->base->file ), array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'jquery-color', plugins_url( '/inc/js/jquery.color.min.js', $this->base->file ), array( 'jquery' ), '1.0.0', true );

	    echo '<div class="create-optin-wrap om-clearfix">';
			echo '<ul class="create-optin-nav om-clearfix">';
				echo '<li><a class="disabled" href="#" title="'.__('Setup Your Optin','optin-monster').'" data-tab="om-optin-setup">1. Setup</a></li>';
				if ( $this->meta )
				    echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'config' => 1, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Configure Your Optin','optin-monster').'" data-tab="om-optin-configure">'.__('2. Configure','optin-monster').'</a></li>';
				else
				    echo '<li><a class="disabled" href="#" title="'.__('Configure Your Optin','optin-monster').'" data-tab="om-optin-configure">'.__('2. Configure','optin-monster').'</a></li>';

				echo '<li><a class="active" href="#" title="'.__('Design Your Optin','optin-monster').'" data-tab="om-optin-design">'.__('3. Design','optin-monster').'</a></li>';
				if ( $this->meta )
				    echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'code' => 1, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Output Settings Your Optin','optin-monster').'" data-tab="om-optin-embed">'.__('4. Output Settings','optin-monster').'</a></li>';
				else
				    echo '<li><a class="disabled" href="#" title="'.__('Output Settings Your Optin','optin-monster').'" data-tab="om-optin-embed">'.__('4. Output Settings','optin-monster').'</a></li>';
			echo '</ul>';
			echo '<div class="create-optin-area" class="om-clearfix">';
				echo '<div id="om-optin-design" class="optin-ui">';
					echo '<h2>'.__('Design Your Optin Experience','optin-monster').'</h2>';
					echo '<p>'.__('Select an optin theme from the options below and then click on the "Open Design Customizer" to customize your optin experience.','optin-monster').'</p>';
					if ( 'lightbox' == $this->type ) :
    					echo '<div class="optin-select-wrap om-clearfix">';
    						echo '<div class="optin-item one-fourth first ' . ( isset( $this->meta['theme'] ) && 'balance-theme' == $this->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Balance Theme">';
    							echo '<h4>Balance Theme</h4>';
    							echo '<img src="' . plugins_url( 'inc/css/images/balancethemeicon.png', $this->base->file ) . '" />';
    							echo '<form id="balance-theme" data-optin-theme="balance-theme">';
    							    echo $this->get_balance_theme( 'balance-theme' );
                                echo '</form>';
    						echo '</div>';
                            echo '<div class="optin-item one-fourth ' . ( isset( $this->meta['theme'] ) && 'case-study-theme' == $this->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Case Study Theme">';
    							echo '<h4>Case Study Theme</h4>';
    							echo '<img src="' . plugins_url( 'inc/css/images/casestudyicon.png', $this->base->file ) . '" />';
    							echo '<form id="case-study-theme" data-optin-theme="case-study-theme">';
    							    echo $this->get_case_study_theme( 'case-study-theme' );
                                echo '</form>';
    						echo '</div>';
    						echo '<div class="optin-item one-fourth ' . ( isset( $this->meta['theme'] ) && 'clean-slate-theme' == $this->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Clean Slate Theme">';
    							echo '<h4>Clean Slate Theme</h4>';
    							echo '<img src="' . plugins_url( 'inc/css/images/cleanslateicon.png', $this->base->file ) . '" />';
    							echo '<form id="clean-slate-theme" data-optin-theme="clean-slate-theme">';
    							    echo $this->get_clean_slate_theme( 'clean-slate-theme' );
                                echo '</form>';
    						echo '</div>';
    						echo '<div class="optin-item one-fourth last ' . ( isset( $this->meta['theme'] ) && 'bullseye-theme' == $this->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Bullseye Theme">';
    							echo '<h4>Bullseye Theme</h4>';
    							echo '<img src="' . plugins_url( 'inc/css/images/customtopicon.png', $this->base->file ) . '" />';
    							echo '<form id="bullseye-theme" data-optin-theme="bullseye-theme">';
    							    echo $this->get_bullseye_theme( 'bullseye-theme' );
                                echo '</form>';
    						echo '</div>';
    						echo '<div class="optin-item one-fourth first ' . ( isset( $this->meta['theme'] ) && 'transparent-theme' == $this->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Transparent Theme">';
    							echo '<h4>Transparent Theme</h4>';
    							echo '<img src="' . plugins_url( 'inc/css/images/transparentthemeicon.png', $this->base->file ) . '" />';
    							echo '<form id="transparent-theme" data-optin-theme="transparent-theme">';
    							    echo $this->get_transparent_theme( 'transparent-theme' );
                                echo '</form>';
    						echo '</div>';
							echo '<div class="optin-item one-fourth ' . ( isset( $this->meta['theme'] ) && 'chalkboard-theme' == $this->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Chalkboard Theme">';
								echo '<h4>Chalkboard Theme</h4>';
								echo '<img src="' . plugins_url( 'inc/css/images/chalkboardthemeicon.png', $this->base->file ) . '" />';
								echo '<form id="chalkboard-theme" data-optin-theme="chalkboard-theme">';
									echo $this->get_chalkboard_theme( 'chalkboard-theme' );
								echo '</form>';
							echo '</div>';
							echo '<div class="optin-item one-fourth ' . ( isset( $this->meta['theme'] ) && 'postal-theme' == $this->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Postal Theme">';
								echo '<h4>Postal Theme</h4>';
								echo '<img src="' . plugins_url( 'inc/css/images/postalthemeicon.png', $this->base->file ) . '" />';
								echo '<form id="postal-theme" data-optin-theme="postal-theme">';
									echo $this->get_postal_theme( 'postal-theme' );
								echo '</form>';
							echo '</div>';
							echo '<div class="optin-item one-fourth last ' . ( isset( $this->meta['theme'] ) && 'target-theme' == $this->meta['theme'] ? 'selected' : '' ) . '" data-optin-theme="Target Theme">';
								echo '<h4>Target Theme</h4>';
								echo '<img src="' . plugins_url( 'inc/css/images/targetthemeicon.png', $this->base->file ) . '" />';
								echo '<form id="target-theme" data-optin-theme="target-theme">';
									echo $this->get_target_theme( 'target-theme' );
								echo '</form>';
							echo '</div>';
						echo '</div>';
    				else :
    				    // Provide a hook for other optin types.
    				    do_action( 'optin_monster_design_' . $this->type );
    				endif;
					echo '<p class="center">';
						echo '<a href="#" class="design-customizer button button-primary button-large green" title="'.__('Open the Design Customizer','optin-monster').'">'.__('Open Design Customizer','optin-monster').'</a>';
					echo '</p>';
				echo '</div>';
			echo '</div>';
			echo '<div class="create-optin-toolbar om-clearfix">';
				echo '<a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'config' => 1, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" class="button button-secondary button-large grey previous-step" href="#" data-optin-tab="om-optin-configure" title="'.__('Back to Previous Step','optin-monster').'">'.__('Back','optin-monster').'</a>';
				echo '<a class="button button-primary button-large orange next-step final-step" href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'code' => 1, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Forward to the Final Step!','optin-monster').'">'.__('Manage Output Settings','optin-monster').'</a>';
			echo '</div>';
		echo '</div>';

	}

	public function do_optin_design_script() {

    	?>
		<script type="text/javascript">
			// Load Google web fonts asynchronously.
			WebFont.load({
		    	google: {
					families: [<?php echo "'" . implode( "','", $this->account->get_available_fonts( false ) ) . "'"; ?>]
				}
		    });

			var icon = '<?php echo includes_url() . 'images/wpspin.gif'; ?>',
				image_container, image_input, theme_type;
			jQuery(document).ready(function($){
			    function omInitializeAccordion() {
        		    // Initialize accordion.
    				$('.accordion-area').accordion({
    					collapsible: true,
    					heightStyle: 'content',
    					activate: function(e, ui){
    					    if ( 'lightbox' == '<?php echo $this->type; ?>' || 'post' == '<?php echo $this->type; ?>' || 'sidebar' == '<?php echo $this->type; ?>') {
        						if ( $(ui.newPanel).hasClass('content-area') ) {
        							$('#plupload-browse-button-<?php echo $this->optin->post_name; ?>').attr('data-container', 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-image-container').prependTo($('.design-customizer-ui[data-optin-theme="' + theme_type + '"] #browse-button-<?php echo $this->optin->post_name; ?>'));
                                    $('#om-uploader-browser-<?php echo $this->optin->post_name; ?>').remove();
        						} else {
        							pluploadRefresh();
        						}
                            }
    					}
    				});
    		    }

			    // If we already have a theme selected, go ahead and apply it.
			    if ( $('#om-optin-design .optin-item.selected').length > 0 ) {
			        $('.design-customizer').addClass('disabled');
			        theme_type = $('#om-optin-design .optin-item.selected').find('.design-customizer-ui').data('optin-theme');
			        $.post(ajaxurl, { action: 'load_theme', type: '<?php echo $this->type; ?>', theme: theme_type, optin: '<?php echo $this->optin->post_name; ?>', optin_id: '<?php echo $this->optin->ID; ?>', plan: '' }, function(res){
			            $('#om-optin-design .optin-item.selected').find('.design-content').empty().append(res);
			            omInitializeAccordion();
			            $('.design-customizer').removeClass('disabled');
			            <?php do_action( 'optin_monster_load_theme', $this ); ?>
			        }, 'json');
                }

				// Initialize color picker.
				$('.om-color-picker').wpColorPicker({
					change: function(e, ui){
					    if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-footer' == $(e.target).attr('data-target') && 'clean-slate-theme' == theme_type || 'om-<?php echo $this->type; ?>-' + theme_type + '-footer' == $(e.target).attr('data-target') && 'bullseye-theme' == theme_type ) {
						    $('#' + $(e.target).attr('data-target')).css('border-color', ui.color.toString());
					    } else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin' == $(e.target).attr('data-target') && 'chalkboard-theme' == theme_type ) {
						    $('#' + $(e.target).attr('data-target')).css('background-color', ui.color.toString());
                        } else {
						    $('#' + $(e.target).attr('data-target')).css('color', ui.color.toString());
						}
					}
				});
				$('.om-bgcolor-picker').wpColorPicker({
					change: function(e, ui){
					    if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-submit' == $(e.target).attr('data-target') ) {
					        $('#' + $(e.target).attr('data-target')).css({ 'background-color': ui.color.toString() });
						    if ( 'postal-theme' == theme_type || 'target-theme' == theme_type || 'fabric-theme' == theme_type || 'banner-theme' == theme_type || 'valley-theme' == theme_type ) {
							    var border = $.Color(ui.color.toString()).lightness(+0.45);
							    $('#' + $(e.target).attr('data-target')).css({ 'border-color': border });
							    set_hidden_input('optin_submit_border_color', border);
						    }
                        } else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin .om-<?php echo $this->type; ?>-open-holder' == $(e.target).attr('data-target') ) {
						    $('#' + $(e.target).attr('data-target')).css('background-color', ui.color.toString());
						    $('#om-<?php echo $this->type; ?>-' + theme_type + '-header').css('background-color', ui.color.toString());

						    // If background colors match for header and content, remove extra padding, otherwise add it.
						    if ( $('#' + $(e.target).attr('data-target')).css('background-color') == $('#om-<?php echo $this->type; ?>-' + theme_type + '-content').css('background-color') )
						    	$('#om-<?php echo $this->type; ?>-' + theme_type + '-content').css('padding-top', '0');
						    else
						    	$('#om-<?php echo $this->type; ?>-' + theme_type + '-content').css('padding-top', '10px');
					    } else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-content' == $(e.target).attr('data-target') ) {
						    $('#' + $(e.target).attr('data-target')).css('background-color', ui.color.toString());
						    $('#om-<?php echo $this->type; ?>-' + theme_type + '-footer').css('background-color', ui.color.toString());

						    // If background colors match for header and content, remove extra padding, otherwise add it.
						    if ( $('#' + $(e.target).attr('data-target')).css('background-color') == $('#om-<?php echo $this->type; ?>-' + theme_type + '-header').css('background-color') )
						    	$('#om-<?php echo $this->type; ?>-' + theme_type + '-content').css('padding-top', '0');
						    else
						    	$('#om-<?php echo $this->type; ?>-' + theme_type + '-content').css('padding-top', '10px');
					    } else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-wrap' == $(e.target).attr('data-target') ) {
                            $('#' + $(e.target).attr('data-target')).css('border-color', ui.color.toString());
						    if( 'chalkboard-theme' != theme_type ) {
							    $('#om-close').css('background-color', ui.color.toString());
						    }
						    if ( 'target-theme' == theme_type ){
							    $('#om-close').css('background-color', 'transparent');
							    $('#om-close').css('color', ui.color.toString());
						    }
                        } else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-footer' == $(e.target).attr('data-target') && 'clean-slate-theme' == theme_type || 'om-<?php echo $this->type; ?>-' + theme_type + '-footer' == $(e.target).attr('data-target') && 'bullseye-theme' == theme_type ) {
                            $('#' + $(e.target).attr('data-target')).css('background-color', ui.color.toString());
                        } else if ( 'om-arrow' == $(e.target).attr('data-target') ) {
                            $('.om-arrow').each(function(){ $(this).css('border-left-color', ui.color.toString()); });
                        } else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-footer' == $(e.target).attr('data-target') && 'transparent-theme' == theme_type ) {
						    var color = ui.color.toString();
						    $('#' + $(e.target).attr('data-target')).css('background-color', 'rgba(' + hexToRgb(color).r + ',' + hexToRgb(color).g + ',' + hexToRgb(color).b + ',.15)');
					    } else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-title' == $(e.target).attr('data-target') && 'banner-theme' == theme_type ) {
						    $('#' + $(e.target).attr('data-target')).css( 'background-color', ui.color.toString());
						    var border = $.Color(ui.color.toString()).lightness(+0.45);
						    $('#' + $(e.target).attr('data-target')).css({ 'border-color': border });
						    set_hidden_input('optin_header_border_color', border);
					    } else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-title' == $(e.target).attr('data-target') && 'valley-theme' == theme_type ) {
						    $('#' + $(e.target).attr('data-target')).css( 'background-color', ui.color.toString());
						    $('#om-sidebar-valley-theme-valley-left').css( 'border-color', ui.color.toString());
						    $('#om-sidebar-valley-theme-valley-right').css( 'border-color', ui.color.toString());
                        } else {
                            $('#' + $(e.target).attr('data-target')).css('background-color', ui.color.toString());
                        }
					}
				});
				$('#om-optin-design .optin-item img').on('click', function(e){
					if ( e.target !== this ) return;
					e.preventDefault();
					var selected = $(this).parent().parent().find('.selected');
					selected.find('.design-content').empty();
					selected.removeClass('selected');
					$(this).parent().addClass('selected');
					theme_type = $(this).next().data('optin-theme');
					$('.design-customizer').addClass('disabled');
					$.post(ajaxurl, { action: 'load_theme', type: '<?php echo $this->type; ?>', theme: theme_type, optin: '<?php echo $this->optin->post_name; ?>', optin_id: '<?php echo $this->optin->ID; ?>', plan: '' }, function(res){
			            $('#om-optin-design .optin-item.selected').find('.design-content').empty().append(res);
			            omInitializeAccordion();
			            $('.design-customizer').removeClass('disabled');
			            <?php do_action( 'optin_monster_load_theme', $this ); ?>
			        }, 'json');
				});
				$(document).on('click', '.design-customizer', function(e){
					e.preventDefault();
					$('.optin-item.selected').find('.design-customizer-ui').fadeIn(300, function(){
						var om_jq_init = 'om_js_<?php echo str_replace( '-', '_', $this->optin->post_name ); ?>';
						var om_init = new window[om_jq_init];
						om_init.init($);
					});
				});
				$(document).on('click', '.close-design', function(e){
					e.preventDefault();
					$('.optin-item.selected').find('.design-customizer-ui').fadeOut(300);
				});

				var poll = (function(){
				    var timer = 0;
				    return function(callback, ms){
				        clearTimeout(timer);
				        timer = setTimeout(callback, ms);
				    };
				})();
				$(document).on('keyup keydown', '.design-sidebar input.main-field, .design-sidebar textarea.main-field', function(){
					var value = $(this).val(),
						$this = $(this),
						target = $(this).attr('data-target'),
						values = $this.parent().next();
					poll(function(){
						if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-bullet-list' == target ) {
							if ( $('#' + target).find('li:eq(' + $this.attr('data-number') + ')').length <= 0 ) {
							    if ( 'clean-slate-theme' == theme_type )
								    $('<li data-number="' + $this.attr('data-number') + '" />').appendTo('#' + target).html('<div class="om-arrow"></div>' + value);
								else
								    $('<li data-number="' + $this.attr('data-number') + '" />').appendTo('#' + target).html(value);
								var new_target = $('#' + target).find('li:eq(' + $this.attr('data-number') + ')');
								$this.next().find('.active').each(function(){
									new_target.css($(this).data('property'), $(this).data('style'));
								});
								new_target.css({ 'color' : values.find('input[name="optin_bullet_color"]').val()});
							} else {
							    if ( 'clean-slate-theme' == theme_type )
								    $('#' + target + ' li:eq(' + $this.attr('data-number') + ')').html('<div class="om-arrow"></div>' + value);
								else
								    $('#' + target + ' li:eq(' + $this.attr('data-number') + ')').html(value);
                            }
						} else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-name' == target || 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-email' == target ) {
							$('#' + target).attr('placeholder', value);
						} else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-submit' == target ) {
							$('#' + target).val(value);
						} else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-title-closed' == target ) {
							$('#' + target).html(value + '<span class="om-<?php echo $this->type; ?>-open-content" style="line-height:' + $('#' + target).height() + 'px;">&#43;</span>');
						} else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-title-open' == target ) {
							$('#' + target).html(value + '<span class="om-<?php echo $this->type; ?>-close-content" style="line-height:' + $('#' + target).height() + 'px;">&#120;</span>');
						} else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-video' == target ) {
							if ( '' == value ) {
								$('.' + target).hide();
							} else {
								$('.' + target).attr( 'src', value).show();
							}
                        } else {
							$('#' + target).html(value);
						}
					}, 50);
				});
				$(document).on('click', '.input-control-handler', function(e){
					e.preventDefault();
					var $this = $(this),
					    target = $this.parent().prev().attr('data-target'),
					    input  = $this.parent().prev().attr('name'),
					    prop   = $(this).attr('data-property'),
					    style  = $(this).attr('data-style');

					// Modify input for optin bullets.
					if ( input.indexOf('optin_bullet') > -1 ) {
						input = input.split('[');
						input = input[0];
					}

					var hidden = $(this).parent().find('input[name="' + input + '_' + prop + '"]');

					// If already active, remove active states.
					if ( $(this).hasClass('active') ) {
						// Remove the active class and the hidden input along with it.
						$(this).removeClass('active');
						if ( hidden.length > 0 && 'text-align' == prop ) {
							if ( 'optin_submit_placeholder' == input )
						    	hidden.val('center');
						    else
						    	hidden.val('left');
						} else {
							switch ( prop ) {
								case 'font-weight' :
									hidden.val('normal');
									break;
								case 'font-style' :
									hidden.val('normal');
									break;
								case 'text-decoration' :
									hidden.val('none');
									break;
								case 'text-align' :
									if ( 'optin_submit_placeholder' == input )
								    	hidden.val('center');
								    else
								    	hidden.val('left');
									break;
							}
                        }

						if ( 'optin_bullet' == input ) {
							$('#' + target).find('li').each(function(){
								switch ( prop ) {
									case 'font-weight' :
										$(this).css('font-weight', 'normal');
										return;
									case 'font-style' :
										$(this).css('font-style', 'normal');
										return;
									case 'text-decoration' :
										$(this).css('text-decoration', 'none');
										return;
									case 'text-align' :
										$(this).css('text-align', 'left');
										return;
								}
							});
						} else {
							switch ( prop ) {
								case 'font-weight' :
									$('#' + target).css('font-weight', 'normal');
									return;
								case 'font-style' :
									$('#' + target).css('font-style', 'normal');
									return;
								case 'text-decoration' :
									$('#' + target).css('text-decoration', 'none');
									return;
								case 'text-align' :
									$('#' + target).css('text-align', 'left');
									return;
							}
						}
					} else {
						if ( 'text-align' == prop ) {
							$(this).parent().find('a[data-property="text-align"]').each(function(){
								if ( $(this).hasClass('active') )
									$(this).removeClass('active');
							});
						}
						$(this).addClass('active');
						if ( 'optin_bullet' == input ) {
							$('#' + target).find('li').each(function(){
								$(this).css(prop, style);
							});
						} else {
							$('#' + target).css(prop, style);
						}
						if ( hidden.length > 0 ) {
						    hidden.val(style);
						} else if ( hidden.length <= 0 ) {
						    $(this).parent().append('<input type="hidden" name="' + input + '_' + prop + '" value="' + style + '" />');
                        }
					}
				});
				$(document).on('change', '.optin-font', function(){
					var $this  = $(this),
						target = $(this).data('target');
					if ( 'optin_bullet_font' == $this.attr('name') ) {
						var new_target = $('#' + target).find('li');
						new_target.each(function(){
							$(this).css('font-family', $this.val());
						});
					} else {
						$('#' + $this.attr('data-target')).css('font-family', $this.val());
					}
				});
				$(document).on('blur keyup keydown', '.optin_dimensions', function(e){
					if ( e.target !== this ) return;
					var $this = $(this),
						value = $(this).val(),
						target = $(this).attr('data-target'),
						attr   = $this.attr('data-attr');
                    poll(function(){
                        $('#' + target).css(attr, value + 'px');
                    }, 500);
				});
				$(document).on('click', '.add-new-bullet', function(e){
					e.preventDefault();
					var $this = $(this),
						num = $this.parent().prev().attr('data-number');
					if (num) num = parseInt(num) + 1;
					if ( num )
						$this.parent().prev().after('<input style="margin-top:3px;" class="main-field" data-target="om-<?php echo $this->type; ?>-' + theme_type + '-optin-bullet-list" name="optin_bullet[' + num + ']" data-number="' + num + '" type="text" value="" placeholder="e.g. bullet point here" />');
				});
				$(document).on('click', '.remove-bullet', function(e){
					e.preventDefault();
					var $this = $(this),
						num = $this.parent().prev().attr('data-number'),
						target = $this.parent().prev().attr('data-target');
					if ( 0 == num ) {
						$('#' + target).find('li[data-number="' + num + '"]').remove();
						$this.parent().prev().val('');
						return;
					}
					$this.parent().prev().remove();
					$('#' + target).find('li[data-number="' + num + '"]').remove();
				});

				$(document).on('click', '.save-design', function(e){
					e.preventDefault();
					var $this = $(this),
					    text = $this.text();
						data = {
    						action: 'save_optin_design',
    						type: '<?php echo $this->type; ?>',
    						theme: theme_type,
    						optin: <?php echo $this->optin->ID; ?>,
    						hash: '<?php echo $this->optin->post_name; ?>',
    						data: $('#' + theme_type).serialize()
						};

                    // If there is a custom HTML form, pass that data too.
                    var custom_html_form = $('.om-custom-html-form');
                    if ( custom_html_form.length > 0 )
                        data['form'] = $("<div/>").append($('.om-custom-html-form').clone()).html();

                    $this.text('Saving...');
					$.post(ajaxurl, data, function(resp){
    					$this.text('Saved!');
    					setTimeout(function(){
        					$this.text(text);
    					}, 1000);
					}, 'json');
				});

				// If doing a lightbox optin, make sure we load plupLoad.
				if ( 'lightbox' == '<?php echo $this->type; ?>' || 'post' == '<?php echo $this->type; ?>' || 'sidebar' == '<?php echo $this->type; ?>' )
				    doPlupLoad();

	            // Helper function for adding/changing hidden input fields for saves.
	            function set_hidden_input(name, value){
    	            var input_field = $('#om-optin-design input[name="' + name + '"]');
    	            if ( input_field.length > 0 )
    	                input_field.val(value);
                    else
                        $('<input type="hidden" name="' + name + '" value="' + value + '" />').appendTo('#om-optin-design');
	            }

	            // Remove an image.
	            $(document).on('click', '.remove-optin-image', function(e){
                    e.preventDefault();
                    $('#' + $(this).data('container')).find('img').attr('src', '').attr('alt', '');
					if( 'post' == '<?php echo $this->type; ?>' && ('fabric-theme' == theme_type || 'postal-theme' == theme_type || 'banner-theme' == theme_type)){
						$('#om-post-' + theme_type + '-left').removeClass('om-has-image');
					}
		            if( 'sidebar' == '<?php echo $this->type; ?>' && ('fabric-theme' == theme_type || 'postal-theme' == theme_type || 'banner-theme' == theme_type)){
			            $('#om-sidebar-' + theme_type + '-top').removeClass('om-has-image');
		            }
                    $.post(ajaxurl, { action: 'remove_optin_image', optin: <?php echo $this->optin->ID; ?> }, function(res){}, 'json');
	            });

	            // Show/hide name field.
	            if ( $('input[name="optin_name_show"]').is(':checked') ) {
	            	$('input[name="optin_name_show"]').parent().next().show();
		            $('input[name="optin_name_show"]').parent().parent().next().show();
	            } else {
	            	$('input[name="optin_name_show"]').parent().next().hide();
		            $('input[name="optin_name_show"]').parent().parent().next().hide();
	            }

	            $('input[name="optin_name_show"]').on('change', function(){
	            	var $this = $(this),
	            		target = $this.parent().next().data('target');
		            if ( $(this).is(':checked') ) {
		            	$this.parent().next().show();
						$this.parent().parent().next().show();

						// If item doesn't exist just yet, add it.
						if ( $('#' + target).length <= 0 )
							$('<input type="text" disabled="disabled" id="om-<?php echo $this->type; ?>-' + theme_type + '-optin-name" value="" placeholder="' + $(this).parent().next().val() + '" style="' + $('.om-has-email input[type="email"]').attr('style') + '" />').insertBefore($('#om-<?php echo $this->type; ?>-' + theme_type + '-optin-email'));

                        if ( 'balance-theme' == theme_type )
			                $('#' + target).show().parent().removeClass().addClass('om-clearfix om-has-name-email');
                        else
                            $('#' + target).show().parent().removeClass().addClass('om-has-name-email');
		            } else {
		            	$this.parent().next().hide();
			            $this.parent().parent().next().hide();
			            if ( 'balance-theme' == theme_type )
			                $('#' + target).hide().parent().removeClass().addClass('om-clearfix om-has-email');
                        else
                            $('#' + target).hide().parent().removeClass().addClass('om-has-email');
		            }
	            });

	            $('textarea[name="optin_custom_css"]').on('blur', function(){
	            	var $this = $(this);
	            	if ( $('.optin_custom_css_applied').length > 0 ) {
	            	    $('.optin_custom_css_applied').html($this.val());
                    } else {
                        $('<style />').addClass('optin_custom_css_applied').attr('type', 'text/css').html($this.val()).insertBefore('#om-<?php echo $this->type; ?>-' + theme_type + '-optin');
                    }
	            });

	            $(document).on('blur keyup keydown', '.optin-size', function(e){
					if ( e.target !== this ) return;
					var $this = $(this),
						value = $(this).val(),
						target = $(this).attr('data-target');
					poll(function(){
						if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-name' == target || 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-email' == target ) {
							$('#' + target).css({ 'font-size': value + 'px', 'line-height': (parseFloat(value) + 10) + 'px' });
						} else if ( 'om-<?php echo $this->type; ?>-' + theme_type + '-optin-submit' == target ) {
							$('#' + target).css({ 'font-size': value + 'px', 'line-height': value + 'px' });
						} else if ( 'optin_bullet_size' == $this.attr('name') ) {
							var new_target = $('#' + target).find('li');
							new_target.each(function(){
								$(this).css('font-size', value + 'px');
							});
						} else {
							$('#' + target).css('font-size', value + 'px');
						}
					}, 500);
				});

	            $('textarea[name="optin_custom_canvas_html"]').on('blur', function(){
	            	var $this = $(this);
		            canvas_data = {
			            'action': 'optinmonster_canvas_shortcode',
			            'text': $this.val()
		            };
	            	if ( $('.optin_custom_html_applied').length > 0 ) {
			            $.post(ajaxurl, canvas_data, function(res){$('.optin_custom_html_applied').html(res)}, 'json');
                    } else {
			            $.post(ajaxurl, canvas_data, function(res){$('<div />').addClass('optin_custom_html_applied').html(res).prependTo('#om-<?php echo $this->type; ?>-' + theme_type + '-optin-wrap');}, 'json');
                    }

                    // Run some social helpers for Canvas.
                    omSocialServices();
	            });

	            function omSocialServices(){
    	            if (typeof(FB) != "undefined" && FB != null ) {FB.XFBML.parse();}
    	            // Twitter helper.
                    if (typeof(twttr) != 'undefined' && twttr != null ) {
                        twttr.widgets.load();
                    }
	            }

	            $(document).on('keydown', '.om-custom-css', function(e) {
                  var keyCode = e.keyCode || e.which;

                  if (keyCode == 9) {
                    e.preventDefault();
                    var start = $(this).get(0).selectionStart;
                    var end = $(this).get(0).selectionEnd;

                    // set textarea value to: text before caret + tab + text after caret
                    $(this).val($(this).val().substring(0, start)
                                + "\t"
                                + $(this).val().substring(end));

                    // put caret at right position again
                    $(this).get(0).selectionStart =
                    $(this).get(0).selectionEnd = start + 1;
                  }
                });

	            // Always have the browser button in the DOM.
				function pluploadRefresh(){
					var container = $('#om-uploader-browser-<?php echo $this->optin->post_name; ?>');
					if ( ! container.length ) {
						var container = $('<div class="om-uploader-browser" />').css({
							position: 'fixed',
							top: '-1000px',
							left: '-1000px',
							height: 0,
							width: 0
						}).attr('id', 'om-uploader-browser-<?php echo $this->optin->post_name; ?>').appendTo('#om-optin-design');
					}
					container.append($('#plupload-browse-button-<?php echo $this->optin->post_name; ?>'));
				}

	            function doPlupLoad(){
    				pluploadRefresh();

    				// Initialize uploader.
    				var config = <?php echo json_encode( array(
    				    'runtimes'            => 'html5,silverlight,flash,html4',
    				    'browse_button'       => 'plupload-browse-button-' . $this->optin->post_name,
    				    'container'           => 'browse-button-' . $this->optin->post_name,
    				    'drop_element'        => 'drag-drop-area',
    				    'file_data_name'      => 'async-upload',
    				    'multiple_queues'     => true,
    				    'max_file_size'       => wp_max_upload_size() . 'b',
    				    'url'                 => admin_url( 'admin-ajax.php' ),
    				    'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
    				    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
    				    'filters'             => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
    				    'multipart'           => true,
    				    'urlstream_upload'    => true,

    				    // additional post data to send to our ajax hook
    				    'multipart_params'    => array(
    				      'action' => $this->type . '_image_upload',
    				      'optin'  => $this->optin->ID
    				    )
    				) ); ?>;

    				var uploader_<?php echo str_replace( '-', '_', $this->optin->post_name ); ?> = new plupload.Uploader(config);

    	            // checks if browser supports drag and drop upload, makes some css adjustments if necessary
    	            uploader_<?php echo str_replace( '-', '_', $this->optin->post_name ); ?>.bind('Init', function(up){
    		            var uploaddiv = $('#plupload-upload-ui');


    		            if(up.features.dragdrop){
    		              uploaddiv.addClass('drag-drop');
    		                $('#drag-drop-area')
    		                  .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
    		                  .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

    		            }else{
    		              uploaddiv.removeClass('drag-drop');
    		              $('#drag-drop-area').unbind('.wp-uploader');
    		            }
    	            });

    	            uploader_<?php echo str_replace( '-', '_', $this->optin->post_name ); ?>.init();

    	            // a file was added in the queue
    	            uploader_<?php echo str_replace( '-', '_', $this->optin->post_name ); ?>.bind('FilesAdded', function(up, files){
    		            image_container = $('#plupload-browse-button-<?php echo $this->optin->post_name; ?>').attr('data-container');
    		            $('#' + image_container).css({ 'background-image': 'url(' + icon + ')', 'background-position': '50% 50%', 'background-repeat' : 'no-repeat' });
    		            var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

    		            plupload.each(files, function(file){
    		              if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){
    		                // file size error?

    		              }else{
    		            }
    	            });

    		            up.refresh();
    		            up.start();
    	            });

    	            // a file was uploaded
    	            uploader_<?php echo str_replace( '-', '_', $this->optin->post_name ); ?>.bind('FileUploaded', function(up, file, response) {
    		            var resp = $.parseJSON(response.response);
    		            if ( resp && ! resp.error ) {
    		            	$('#' + image_container).css('background', '').empty().append('<img class="<?php echo $this->type; ?>-optin-image-uploaded" src="' + resp.url + '" alt="" />');
    		            	$('input[name="optin_image"]').val(resp.url);
			                if( 'post' == '<?php echo $this->type; ?>' && ('fabric-theme' == theme_type || 'postal-theme' == theme_type || 'banner-theme' == theme_type)){
				                $('#om-post-' + theme_type + '-left').addClass('om-has-image');
			                }
			                if( 'sidebar' == '<?php echo $this->type; ?>' && ('fabric-theme' == theme_type || 'postal-theme' == theme_type || 'banner-theme' == theme_type)){
				                $('#om-sidebar-' + theme_type + '-top').addClass('om-has-image');
			                }
    		            }
    	            });
	            }
	            function hexToRgb(hex) {
                    // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
                    var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
                    hex = hex.replace(shorthandRegex, function(m, r, g, b) {
                        return r + r + g + g + b + b;
                    });

                    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                    return result ? {
                        r: parseInt(result[1], 16),
                        g: parseInt(result[2], 16),
                        b: parseInt(result[3], 16)
                    } : null;
                }
			});
		</script>
		<?php

		// Run a hook for any extra design scripts.
		do_action( 'optin_monster_design_script', $this );

	}

	public function get_field( $field, $subfield = '', $default = '' ) {

		if ( ! $this->optin ){
			return '';
        }

		if ( ! empty( $subfield ) ){
			return isset( $this->meta[$field][$subfield] ) ? $this->meta[$field][$subfield] : $default;
        }
		else{
			return isset( $this->meta[$field] ) ? $this->meta[$field] : $default;
        }

	}

	public function get_meta_controls( $field, $align = true, $defaults = array() ) {

		$meta = $this->get_field( $field, 'meta' );
		// Merge the saved data with the field defaults
		$meta = wp_parse_args( $meta, $defaults );
		$controls = '';

		$active    = isset( $meta['font_weight'] ) && 'normal' !== $meta['font_weight'] ? ' active' : '';
		$controls .= '<a href="#" class="input-control-handler' . $active . '" data-property="font-weight" data-style="bold"><i class="icon-bold"></i></a>';

		$active    = isset( $meta['font_style'] ) && 'normal' !== $meta['font_style'] ? ' active' : '';
		$controls .= '<a href="#" class="input-control-handler' . $active . '" data-property="font-style" data-style="italic"><i class="icon-italic"></i></a>';

		$active    = isset( $meta['text_decoration'] ) && 'none' !== $meta['text_decoration'] ? ' active' : '';
		$controls .= '<a href="#" class="input-control-handler' . $active . '" data-property="text-decoration" data-style="underline"><i class="icon-underline"></i></a>';

        if ( $align ) {
    		$active    = isset( $meta['text_align'] ) && 'left' == $meta['text_align'] ? ' active' : ''; // change empty to $default
    		$controls .= '<a href="#" class="input-control-handler' . $active . '" data-property="text-align" data-style="left"><i class="icon-align-left"></i></a>';

    		$active    = isset( $meta['text_align'] ) && 'center' == $meta['text_align'] ? ' active' : '';
    		$controls .= '<a href="#" class="input-control-handler' . $active . '" data-property="text-align" data-style="center"><i class="icon-align-center"></i></a>';

    		$active    = isset( $meta['text_align'] ) && 'right' == $meta['text_align'] ? ' active' : '';
    		$controls .= '<a href="#" class="input-control-handler' . $active . '" data-property="text-align" data-style="right"><i class="icon-align-right"></i></a>';

    		$active    = isset( $meta['text_align'] ) && 'justify' == $meta['text_align'] ? ' active' : '';
    		$controls .= '<a href="#" class="input-control-handler last' . $active . '" data-property="text-align" data-style="justify"><i class="icon-align-justify"></i></a>';
        }

		return $controls;

	}

	public function get_balance_theme( $theme_type ) {

        ob_start();
    	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
        	echo '<div class="design-sidebar">';
        		echo '<div class="controls-area om-clearfix">';
        			echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
        			echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
        		echo '</div>';
        		echo '<div class="title-area om-clearfix">';
        			echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
        			echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
        		echo '</div>';
        		echo '<div class="accordion-area om-clearfix">';
        			echo '<h3>'.__('Background Colors','optin-monster').'</h3>';
        			echo '<div class="colors-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-header-bg">'.__('Header Background Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-header-bg" class="om-bgcolor-picker" name="optin_header_bg" value="' . $this->get_field( 'background', 'header', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-header" />';
        				echo '</p>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-content-bg">'.__('Content Background Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-content-bg" class="om-bgcolor-picker" name="optin_content_bg" value="' . $this->get_field( 'background', 'content', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-content" />';
        				echo '</p>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-footer-bg">'.__('Footer Background Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-footer-bg" class="om-bgcolor-picker" name="optin_footer_bg" value="' . $this->get_field( 'background', 'footer', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-footer" />';
        				echo '</p>';
        			echo '</div>';

        			echo '<h3>'.__('Title and Tagline','optin-monster').'</h3>';
        			echo '<div class="title-tag-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-headline">'.__('Optin Title','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-headline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . htmlentities( $this->get_field( 'title', 'text', 'OptinMonster Rules!' ) ) . '" placeholder="'.__('e.g. OptinMonster Rules!','optin-monster').'" />';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'title' );
        						foreach ( (array) $this->get_field( 'title', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-color">'.__('Optin Title Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $this->get_field( 'title', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-title" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-font">'.__('Optin Title Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-size">'.__('Optin Title Font Size','optin-monster').'</label>';
        						echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $this->get_field( 'title', 'size', '24' ) . '" placeholder="'.__('e.g. 24','optin-monster').'" />';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-tagline">'.__('Optin Tagline','optin-monster').'</label>';
        					echo '<textarea id="om-lightbox-' . $theme_type . '-tagline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="'.__('e.g. OptinMonster explodes your email list!','optin-monster').'" rows="4">' . htmlentities( $this->get_field( 'tagline', 'text', 'OptinMonster explodes your email list!' ) ) . '</textarea>';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'tagline' );
        						foreach ( (array) $this->get_field( 'tagline', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-color">'.__('Optin Tagline Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $this->get_field( 'tagline', 'color' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-tagline" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-font">'.__('Optin Tagline Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-size">'.__('Optin Tagline Font Size','optin-monster').'</label>';
        						echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $this->get_field( 'tagline', 'size', '16' ) . '" placeholder="e.g. 16" />';
        					echo '</p>';
        				echo '</div>';
        			echo '</div>';

        			echo '<h3>'.__('Content','optin-monster').'</h3>';
        			echo '<div class="content-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-content-bullets">'.__('Optin Bullets','optin-monster').'</label>';
        					$bullets = $this->get_field( 'bullet', 'text' );
        					if ( ! empty( $bullets ) ) {
        						foreach ( (array) $bullets as $i => $bullet ) {
        						    $style = 0 !== $i ? ' style="margin-top:3px;"' : '';
        						    echo '<input id="om-lightbox-' . $theme_type . '-content-bullets" class="main-field"' . $style . ' data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" name="optin_bullet[' . $i . ']" data-number="' . $i . '" type="text" value="' . htmlentities( $bullet ) . '" placeholder="'.__('e.g. bullet point here','optin-monster').'" />';
        						}
        					} else {
        					    echo '<input id="om-lightbox-' . $theme_type . '-content-bullets" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" name="optin_bullet[0]" data-number="0" type="text" value="" placeholder="'.__('e.g. bullet point here','optin-monster').'" />';
        					}
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'bullet' );
        						echo '<a style="margin-top:3px;" href="#" class="bullet-button add-new-bullet" title="'.__('Add New Bullet','optin-monster').'">'.__('Add Bullet','optin-monster').'</a>';
        						echo '<a style="margin-top:3px;" href="#" class="bullet-button remove-bullet" title="'.__('Remove Bullet','optin-monster').'">'.__('Remove Bullet','optin-monster').'</a>';
        						foreach ( (array) $this->get_field( 'bullet', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_bullet_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-bullet-color">'.__('Optin Bullet Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-bullet-color" class="om-color-picker" name="optin_bullet_color" value="' . $this->get_field( 'bullet', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-bullet-font">'.__('Optin Bullet Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-bullet-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" data-property="font-family" name="optin_bullet_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'bullet', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-bullet-size">'.__('Optin Bullet Font Size','optin-monster').'</label>';
        						echo '<input id="om-lightbox-' . $theme_type . '-bullet-size" data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" name="optin_bullet_size" class="optin-size" type="text" value="' . $this->get_field( 'bullet', 'size', '16' ) . '" placeholder="e.g. 16" />';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-content-image">Optin Image</label>';
        					echo '<small>'.__('Click the button below to upload an image for this optin. It should be 225x175 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
        					echo '<input type="hidden" name="optin_image" value="' . $this->get_field( 'image' ) . '" />';
        					echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
        						echo '<div id="browse-button-' . $this->optin->post_name . '"><a id="plupload-browse-button-' . $this->optin->post_name . '" class="bullet-button" data-container="om-lightbox-' . $theme_type . '-optin-image-container" href="#">Upload Image</a><a href="#" class="bullet-button remove-optin-image" data-container="om-lightbox-' . $theme_type . '-optin-image-container">Remove Image</a></div>';
        					echo '</div>';
        				echo '</p>';
        			echo '</div>';

                    if ( ! $this->meta['custom_html'] ) :
        			echo '<h3>'.__('Fields and Buttons','optin-monster').'</h3>';
        			echo '<div class="fields-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-name"><input style="width:auto;margin-right:3px;" type="checkbox" id="om-lightbox-' . $theme_type . '-name" name="optin_name_show" value="' . $this->get_field( 'name', 'show' ) . '"' . checked( $this->get_field( 'name', 'show' ), 1, false ) . ' />'.__(' Show Optin Name Field?','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $this->get_field( 'name', 'placeholder', 'Your Name' ) . '" placeholder="'.__('e.g. Your Name','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-color">'.__('Optin Name Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $this->get_field( 'name', 'color' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-name" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-font">'.__('Optin Name Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-email">'.__('Optin Email Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $this->get_field( 'email', 'placeholder', 'Your Email' ) . '" placeholder="'.__('e.g. Your Email','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-color">'.__('Optin Email Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $this->get_field( 'email', 'color' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-email" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-font">'.__('Optin Email Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-submit">'.__('Optin Submit Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $this->get_field( 'submit', 'placeholder', 'Sign Me Up!' ) . '" placeholder="'.__('e.g. Sign Me Up!','optin-monster').'" />';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'submit' );
        						foreach ( (array) $this->get_field( 'submit', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-field-color">'.__('Optin Submit Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $this->get_field( 'submit', 'field_color' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-bg-color">'.__('Optin Submit Background Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $this->get_field( 'submit', 'bg_color' ) . '" data-default-color="#484848" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-font">'.__('Optin Submit Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        			echo '</div>';
        			endif;

        			echo '<h3>'.__('Custom Optin CSS','optin-monster').'</h3>';
        			echo '<div class="custom-css-area">';
        				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
        				echo '<p><strong><code>html div#om-' . $this->optin->post_name . '</code></strong></p>';
        				echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $this->optin->post_name . ' input[type=submit], html div#' . $this->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $this->get_field( 'custom_css', '', '' ) . '</textarea>';
        				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
        			echo '</div>';
        		echo '</div>';
        	echo '</div>';
        	echo '<div class="design-content">';
        	echo '</div>';
        echo '</div>';

        return ob_get_clean();

	}

	public function get_case_study_theme( $theme_type ) {

        ob_start();
    	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
        	echo '<div class="design-sidebar">';
        		echo '<div class="controls-area om-clearfix">';
        			echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
        			echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
        		echo '</div>';
        		echo '<div class="title-area om-clearfix">';
        			echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
        			echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
        		echo '</div>';
        		echo '<div class="accordion-area om-clearfix">';
        			echo '<h3>'.__('Title and Tagline','optin-monster').'</h3>';
        			echo '<div class="title-tag-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-headline">'.__('Optin Title','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-headline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . htmlentities( $this->get_field( 'title', 'text' ) ) . '" placeholder="'.__('e.g. OptinMonster Rules!','optin-monster').'" />';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'title' );
        						foreach ( (array) $this->get_field( 'title', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-color">'.__('Optin Title Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $this->get_field( 'title', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-title" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-font">'.__('Optin Title Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-size">'.__('Optin Title Font Size','optin-monster').'</label>';
        						echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $this->get_field( 'title', 'size', '24' ) . '" placeholder="'.__('e.g. 24','optin-monster').'" />';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-tagline">'.__('Optin Tagline','optin-monster').'</label>';
        					echo '<textarea id="om-lightbox-' . $theme_type . '-tagline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="'.__('e.g. OptinMonster explodes your email list!','optin-monster').'" rows="4">' . htmlentities( $this->get_field( 'tagline', 'text', 'OptinMonster explodes your email list!' ) ) . '</textarea>';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'tagline' );
        						foreach ( (array) $this->get_field( 'tagline', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-color">'.__('Optin Tagline Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $this->get_field( 'tagline', 'color' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-tagline" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-font">'.__('Optin Tagline Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-size">'.__('Optin Tagline Font Size','optin-monster').'</label>';
        						echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $this->get_field( 'tagline', 'size', '16' ) . '" placeholder="'.__('e.g. 16','optin-monster').'" />';
        					echo '</p>';
        				echo '</div>';
        			echo '</div>';

        			echo '<h3>'.__('Content','optin-monster').'</h3>';
        			echo '<div class="content-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-content-image">'.__('Optin Image','optin-monster').'</label>';
        					echo '<small>'.__('Click the button below to upload an image for this optin. It should be 280x245 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
        					echo '<input type="hidden" name="optin_image" value="' . $this->get_field( 'image' ) . '" />';
        					echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
        						echo '<div id="browse-button-' . $this->optin->post_name . '"><a href="#" class="bullet-button remove-optin-image" data-container="om-lightbox-' . $theme_type . '-optin-image-container">'.__('Remove Image','optin-monster').'</a></div>';
        					echo '</div>';
        				echo '</p>';
        			echo '</div>';

                    if ( ! $this->meta['custom_html'] ) :
        			echo '<h3>'.__('Fields and Buttons','optin-monster').'</h3>';
        			echo '<div class="fields-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-name"><input style="width:auto;margin-right:3px;" type="checkbox" id="om-lightbox-' . $theme_type . '-name" name="optin_name_show" value="' . $this->get_field( 'name', 'show' ) . '"' . checked( $this->get_field( 'name', 'show', 'Show Optin Name Field?' ), 1, false ) . ' /> '.__('Show Optin Name Field?','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $this->get_field( 'name', 'placeholder', 'Your Name' ) . '" placeholder="'.__('e.g. Your Name','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-color">'.__('Optin Name Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $this->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-name" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-font">'.__('Optin Name Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-email">'.__('Optin Email Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $this->get_field( 'email', 'placeholder', 'Your Email' ) . '" placeholder="'.__('e.g. Your Email','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-color">'.__('Optin Email Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $this->get_field( 'email', 'color' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-email" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-font">'.__('Optin Email Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-submit">'.__('Optin Submit Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $this->get_field( 'submit', 'placeholder', 'Sign Me Up!' ) . '" placeholder="'.__('e.g. Sign Me Up!','optin-monster').'" />';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'submit' );
        						foreach ( (array) $this->get_field( 'submit', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-field-color">'.__('Optin Submit Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $this->get_field( 'submit', 'field_color' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-bg-color">'.__('Optin Submit Background Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $this->get_field( 'submit', 'bg_color', '#484848' ) . '" data-default-color="#484848" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-font">'.__('Optin Submit Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';

        			echo '</div>';
        			endif;

        			echo '<h3>'.__('Custom Optin CSS','optin-monster').'</h3>';
        			echo '<div class="custom-css-area">';
        				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
        				echo '<p><strong><code>html div#om-' . $this->optin->post_name . '</code></strong></p>';
        				echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $this->optin->post_name . ' input[type=submit], html div#' . $this->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $this->get_field( 'custom_css', '', '' ) . '</textarea>';
        				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
        			echo '</div>';
        		echo '</div>';
        	echo '</div>';
        	echo '<div class="design-content">';
        	echo '</div>';
        echo '</div>';

        return ob_get_clean();

	}

	public function get_clean_slate_theme( $theme_type ) {

        ob_start();
    	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
        	echo '<div class="design-sidebar">';
        		echo '<div class="controls-area om-clearfix">';
        			echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
        			echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
        		echo '</div>';
        		echo '<div class="title-area om-clearfix">';
        			echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
        			echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
        		echo '</div>';
        		echo '<div class="accordion-area om-clearfix">';
        			echo '<h3>'.__('Background and Border Colors','optin-monster').'</h3>';
        			echo '<div class="colors-area">';
        			    echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-border-bg">'.__('Outer Border Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-border-bg" class="om-bgcolor-picker" name="optin_border_bg" value="' . $this->get_field( 'background', 'border', '000' ) . '" data-default-color="#000" data-target="om-lightbox-' . $theme_type . '-optin-wrap" />';
        				echo '</p>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-footer-bg">'.__('Footer Background Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-footer-bg" class="om-bgcolor-picker" name="optin_footer_bg" value="' . $this->get_field( 'background', 'footer', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-footer" />';
        				echo '</p>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-footer-border">'.__('Footer Top Border Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-footer-border" class="om-color-picker" name="optin_footer_border" value="' . $this->get_field( 'background', 'footer_border', '#bbb' ) . '" data-default-color="#bbb" data-target="om-lightbox-' . $theme_type . '-footer" />';
        				echo '</p>';
        			echo '</div>';

        			echo '<h3>'.__('Title and Tagline','optin-monster').'</h3>';
        			echo '<div class="title-tag-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-headline">'.__('Optin Title','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-headline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . htmlentities( $this->get_field( 'title', 'text', 'OptinMonster Rules!' ) ) . '" placeholder="'.__('e.g. OptinMonster Rules!','optin-monster').'" />';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'title' );
        						foreach ( (array) $this->get_field( 'title', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-color">'.__('Optin Title Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $this->get_field( 'title', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-title" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-font">'.__('Optin Title Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-headline-size">'.__('Optin Title Font Size','optin-monster').'</label>';
        						echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $this->get_field( 'title', 'size', '24' ) . '" placeholder="'.__('e.g. 24','optin-monster').'" />';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-tagline">'.__('Optin Tagline','optin-monster').'</label>';
        					echo '<textarea id="om-lightbox-' . $theme_type . '-tagline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="'.__('e.g. OptinMonster explodes your email list!','optin-monster').'" rows="4">' . htmlentities( $this->get_field( 'tagline', 'text', 'OptinMonster explodes your email list!' ) ) . '</textarea>';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'tagline' );
        						foreach ( (array) $this->get_field( 'tagline', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-color">'.__('Optin Tagline Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $this->get_field( 'tagline', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-tagline" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-font">'.__('Optin Tagline Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-tagline-size">'.__('Optin Tagline Font Size','optin-monster').'</label>';
        						echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $this->get_field( 'tagline', 'size', '16' ) . '" placeholder="'.__('e.g. 16','optin-monster').'" />';
        					echo '</p>';
        				echo '</div>';
        			echo '</div>';

        			echo '<h3>'.__('Content','optin-monster').'</h3>';
        			echo '<div class="content-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-content-bullets">'.__('Optin Bullets','optin-monster').'</label>';
        					$bullets = $this->get_field( 'bullet', 'text' );
        					if ( ! empty( $bullets ) ) {
        						foreach ( (array) $bullets as $i => $bullet ) {
        						    $style = 0 !== $i ? ' style="margin-top:3px;"' : '';
        						    echo '<input id="om-lightbox-' . $theme_type . '-content-bullets" class="main-field"' . $style . ' data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" name="optin_bullet[' . $i . ']" data-number="' . $i . '" type="text" value="' . htmlentities( $bullet ) . '" placeholder="e.g. bullet point here" />';
        						}
        					} else {
        					    echo '<input id="om-lightbox-' . $theme_type . '-content-bullets" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" name="optin_bullet[0]" data-number="0" type="text" value="" placeholder="'.__('e.g. bullet point here','optin-monster').'" />';
        					}
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'bullet' );
        						echo '<a style="margin-top:3px;" href="#" class="bullet-button add-new-bullet" title="'.__('Add New Bullet','optin-monster').'">'.__('Add Bullet','optin-monster').'</a>';
        						echo '<a style="margin-top:3px;" href="#" class="bullet-button remove-bullet" title="'.__('Remove Bullet','optin-monster').'">'.__('Remove Bullet','optin-monster').'</a>';
        						foreach ( (array) $this->get_field( 'bullet', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_bullet_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-bullet-color">'.__('Optin Bullet Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-bullet-color" class="om-color-picker" name="optin_bullet_color" value="' . $this->get_field( 'bullet', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-bullet-arrow-color">'.__('Optin Bullet Arrow Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-bullet-arrow-color" class="om-bgcolor-picker" name="optin_bullet_arrow_color" value="' . $this->get_field( 'bullet', 'arrow_color', '#ff6201' ) . '" data-default-color="#ff6201" data-target="om-arrow" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-bullet-font">'.__('Optin Bullet Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-bullet-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" data-property="font-family" name="optin_bullet_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'bullet', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-bullet-size">'.__('Optin Bullet Font Size','optin-monster').'</label>';
        						echo '<input id="om-lightbox-' . $theme_type . '-bullet-size" data-target="om-lightbox-' . $theme_type . '-optin-bullet-list" name="optin_bullet_size" class="optin-size" type="text" value="' . $this->get_field( 'bullet', 'size', '16' ) . '" placeholder="'.__('e.g. 16','optin-monster').'" />';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-content-image">'.__('Optin Image','optin-monster').'</label>';
        					echo '<small>'.__('Click the button below to upload an image for this optin. It should be 230x195 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
        					echo '<input type="hidden" name="optin_image" value="' . $this->get_field( 'image' ) . '" />';
        					echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
        						echo '<div id="browse-button-' . $this->optin->post_name . '"><a href="#" class="bullet-button remove-optin-image" data-container="om-lightbox-' . $theme_type . '-optin-image-container">'.__('Remove Image','optin-monster').'</a></div>';
        					echo '</div>';
        				echo '</p>';
        			echo '</div>';

                    if ( ! $this->meta['custom_html'] ) :
        			echo '<h3>'.__('Fields and Buttons','optin-monster').'</h3>';
        			echo '<div class="fields-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-name"><input style="width:auto;margin-right:3px;" type="checkbox" id="om-lightbox-' . $theme_type . '-name" name="optin_name_show" value="' . $this->get_field( 'name', 'show','Show Optin Name Field?' ) . '"' . checked( $this->get_field( 'name', 'show' ), 1, false ) . ' /> '.__('Show Optin Name Field?','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $this->get_field( 'name', 'placeholder', 'Your Name' ) . '" placeholder="'.__('e.g. Your Name','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-color">'.__('Optin Name Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $this->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-name" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-font">'.__('Optin Name Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-email">'.__('Optin Email Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $this->get_field( 'email', 'placeholder', 'Your Email' ) . '" placeholder="'.__('e.g. Your Email','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-color">'.__('Optin Email Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $this->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-email" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-font">'.__('Optin Email Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-submit">'.__('Optin Submit Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $this->get_field( 'submit', 'placeholder', 'Sign Me Up!' ) . '" placeholder="e.g. Sign Me Up!" />';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'submit' );
        						foreach ( (array) $this->get_field( 'submit', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-field-color">'.__('Optin Submit Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $this->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-bg-color">'.__('Optin Submit Background Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $this->get_field( 'submit', 'bg_color', '#484848' ) . '" data-default-color="#484848" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-font">'.__('Optin Submit Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';

        			echo '</div>';
        			endif;

        			echo '<h3>'.__('Custom Optin CSS','optin-monster').'</h3>';
        			echo '<div class="custom-css-area">';
        				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
        				echo '<p><strong><code>html div#om-' . $this->optin->post_name . '</code></strong></p>';
        				echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $this->optin->post_name . ' input[type=submit], html div#' . $this->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $this->get_field( 'custom_css', '', '' ) . '</textarea>';
        				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
        			echo '</div>';
        		echo '</div>';
        	echo '</div>';
        	echo '<div class="design-content">';
        	echo '</div>';
        echo '</div>';

        return ob_get_clean();

	}

	public function get_bullseye_theme( $theme_type ) {

        ob_start();
    	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
        	echo '<div class="design-sidebar">';
        		echo '<div class="controls-area om-clearfix">';
        			echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
        			echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
        		echo '</div>';
        		echo '<div class="title-area om-clearfix">';
        			echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
        			echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
        		echo '</div>';
        		echo '<div class="accordion-area om-clearfix">';
        			echo '<h3>'.__('Background and Border Colors','optin-monster').'</h3>';
        			echo '<div class="colors-area">';
        			    echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-border-bg">'.__('Outer Border Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-border-bg" class="om-bgcolor-picker" name="optin_border_bg" value="' . $this->get_field( 'background', 'border', '#000' ) . '" data-default-color="#000" data-target="om-lightbox-' . $theme_type . '-optin-wrap" />';
        				echo '</p>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-footer-bg">'.__('Footer Background Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-footer-bg" class="om-bgcolor-picker" name="optin_footer_bg" value="' . $this->get_field( 'background', 'footer', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-footer" />';
        				echo '</p>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-footer-border">'.__('Footer Top Border Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-footer-border" class="om-color-picker" name="optin_footer_border" value="' . $this->get_field( 'background', 'footer_border', '#bbb' ) . '" data-default-color="#bbb" data-target="om-lightbox-' . $theme_type . '-footer" />';
        				echo '</p>';
        			echo '</div>';

        			echo '<h3>'.__('Content','optin-monster').'</h3>';
        			echo '<div class="content-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-content-image">'.__('Optin Image','optin-monster').'</label>';
        					echo '<small>'.__('Click the button below to upload an image for this optin. It should be 700x350 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
        					echo '<input type="hidden" name="optin_image" value="' . $this->get_field( 'image' ) . '" />';
        					echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
        						echo '<div id="browse-button-' . $this->optin->post_name . '"><a href="#" class="bullet-button remove-optin-image" data-container="om-lightbox-' . $theme_type . '-optin-image-container">'.__('Remove Image','optin-monster').'</a></div>';
        					echo '</div>';
        				echo '</p>';
        			echo '</div>';

                    if ( ! $this->meta['custom_html'] ) :
        			echo '<h3>'.__('Fields and Buttons','optin-monster').'</h3>';
        			echo '<div class="fields-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-name"><input style="width:auto;margin-right:3px;" type="checkbox" id="om-lightbox-' . $theme_type . '-name" name="optin_name_show" value="' . $this->get_field( 'name', 'show' ) . '"' . checked( $this->get_field( 'name', 'show' ), 1, false ) . ' /> '.__('Show Optin Name Field?','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $this->get_field( 'name', 'placeholder', 'Your Name' ) . '" placeholder="'.__('e.g. Your Name','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-color">'.__('Optin Name Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $this->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-name" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-font">'.__('Optin Name Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-email">'.__('Optin Email Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $this->get_field( 'email', 'placeholder', 'Your Email' ) . '" placeholder="'.__('e.g. Your Email','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-color">'.__('Optin Email Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $this->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-email" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-font">'.__('Optin Email Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-submit">'.__('Optin Submit Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $this->get_field( 'submit', 'placeholder', 'Sign Me Up!' ) . '" placeholder="'.__('e.g. Sign Me Up!','optin-monster').'" />';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'submit' );
        						foreach ( (array) $this->get_field( 'submit', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-field-color">'.__('Optin Submit Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $this->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-bg-color">'.__('Optin Submit Background Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $this->get_field( 'submit', 'bg_color', '#484848' ) . '" data-default-color="#484848" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-font">'.__('Optin Submit Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';

        			echo '</div>';
        			endif;

        			echo '<h3>'.__('Custom Optin CSS','optin-monster').'</h3>';
        			echo '<div class="custom-css-area">';
        				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
        				echo '<p><strong><code>html div#om-' . $this->optin->post_name . '</code></strong></p>';
        				echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $this->optin->post_name . ' input[type=submit], html div#' . $this->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $this->get_field( 'custom_css', '', '' ) . '</textarea>';
        				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
        			echo '</div>';
        		echo '</div>';
        	echo '</div>';
        	echo '<div class="design-content">';
        	echo '</div>';
        echo '</div>';

        return ob_get_clean();

	}

	public function get_transparent_theme( $theme_type ) {

        ob_start();
    	echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
        	echo '<div class="design-sidebar">';
        		echo '<div class="controls-area om-clearfix">';
        			echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
        			echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
        		echo '</div>';
        		echo '<div class="title-area om-clearfix">';
        			echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
        			echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
        		echo '</div>';
        		echo '<div class="accordion-area om-clearfix">';
        			echo '<h3>'.__('Background and Border Colors','optin-monster').'</h3>';
        			echo '<div class="colors-area">';
        			    echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-border-bg">'.__('Outer Border Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-border-bg" class="om-bgcolor-picker" name="optin_border_bg" value="' . $this->get_field( 'background', 'border', '#000"' ) . '" data-default-color="#000" data-target="om-lightbox-' . $theme_type . '-optin-wrap" />';
        				echo '</p>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-footer-bg">'.__('Footer Background Color','optin-monster').'</label>';
        					echo '<input type="text" id="om-lightbox-' . $theme_type . '-footer-bg" class="om-bgcolor-picker" name="optin_footer_bg" value="' . $this->get_field( 'background', 'footer', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-footer" />';
        				echo '</p>';
        			echo '</div>';

        			echo '<h3>'.__('Content','optin-monster').'</h3>';
        			echo '<div class="content-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-content-image">'.__('Optin Image','optin-monster').'</label>';
        					echo '<small>'.__('Click the button below to upload an image for this optin. It should be 700x450 pixels. Images not this size will be cropped to meet this size requirement.','optin-monster').'</small><br />';
        					echo '<input type="hidden" name="optin_image" value="' . $this->get_field( 'image' ) . '" />';
        					echo '<div id="plupload-upload-ui" class="hide-if-no-js">';
        						echo '<div id="browse-button-' . $this->optin->post_name . '"><a href="#" class="bullet-button remove-optin-image" data-container="om-lightbox-' . $theme_type . '-optin-image-container">Remove Image</a></div>';
        					echo '</div>';
        				echo '</p>';
        			echo '</div>';

                    if ( ! $this->meta['custom_html'] ) :
        			echo '<h3>'.__('Fields and Buttons','optin-monster').'</h3>';
        			echo '<div class="fields-area">';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-name"><input style="width:auto;margin-right:3px;" type="checkbox" id="om-lightbox-' . $theme_type . '-name" name="optin_name_show" value="' . $this->get_field( 'name', 'show' ) . '"' . checked( $this->get_field( 'name', 'show' ), 1, false ) . ' /> '.__('Show Optin Name Field?','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $this->get_field( 'name', 'placeholder', 'Your Name' ) . '" placeholder="'.__('e.g. Your Name','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-color">'.__('Optin Name Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $this->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-name" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-name-font">'.__('Optin Name Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-email">'.__('Optin Email Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $this->get_field( 'email', 'placeholder', 'Your Email' ) . '" placeholder="'.__('e.g. Your Email','optin-monster').'" />';
        				echo '</p>';
        				echo '<div class="optin-input-meta">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-color">'.__('Optin Email Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $this->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-email" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-email-font">'.__('Optin Email Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';
        				echo '<p>';
        					echo '<label for="om-lightbox-' . $theme_type . '-submit">'.__('Optin Submit Field','optin-monster').'</label>';
        					echo '<input id="om-lightbox-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $this->get_field( 'submit', 'placeholder', 'Sign Me Up!' ) . '" placeholder="'.__('e.g. Sign Me Up!','optin-monster').'" />';
        					echo '<span class="input-controls">';
        						echo $this->get_meta_controls( 'submit' );
        						foreach ( (array) $this->get_field( 'submit', 'meta' ) as $prop => $style )
        							echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
        					echo '</span>';
        				echo '</p>';
        				echo '<div class="optin-input-meta last">';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-field-color">'.__('Optin Submit Field Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $this->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-bg-color">'.__('Optin Submit Background Color','optin-monster').'</label>';
        						echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $this->get_field( 'submit', 'bg_color', '#484848' ) . '" data-default-color="#484848" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
        					echo '</p>';
        					echo '<p>';
        						echo '<label for="om-lightbox-' . $theme_type . '-submit-font">'.__('Optin Submit Field Font','optin-monster').'</label>';
        						echo '<select id="om-lightbox-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
        						foreach ( $this->account->get_available_fonts() as $font ) :
        							$selected = $this->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
        							echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
        						endforeach;
        						echo '</select>';
        					echo '</p>';
        				echo '</div>';

        			echo '</div>';
        			endif;

        			echo '<h3>'.__('Custom Optin CSS','optin-monster').'</h3>';
        			echo '<div class="custom-css-area">';
        				echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
        				echo '<p><strong><code>html div#om-' . $this->optin->post_name . '</code></strong></p>';
        				echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $this->optin->post_name . ' input[type=submit], html div#' . $this->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $this->get_field( 'custom_css', '', '' ) . '</textarea>';
        				echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
        			echo '</div>';
        		echo '</div>';
        	echo '</div>';
        	echo '<div class="design-content">';
        	echo '</div>';
        echo '</div>';

        return ob_get_clean();

	}

	public function get_chalkboard_theme( $theme_type ) {

		ob_start();
		echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
		echo '<div class="design-sidebar">';
		echo '<div class="controls-area om-clearfix">';
		echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
		echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
		echo '</div>';
		echo '<div class="title-area om-clearfix">';
		echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
		echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
		echo '</div>';
		echo '<div class="accordion-area om-clearfix">';
		echo '<h3>'.__('Background and Border Colors','optin-monster').'</h3>';
		echo '<div class="colors-area">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-border-bg">'.__('Outer Border Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-border-bg" class="om-bgcolor-picker" name="optin_border_bg" value="' . $this->get_field( 'background', 'border', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-wrap" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-body-bg">'.__('Body Background Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-body-bg" class="om-color-picker" name="optin_body_bg" value="' . $this->get_field( 'background', 'body', '#4f6e81' ) . '" data-default-color="#4f6e81" data-target="om-lightbox-' . $theme_type . '-optin" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-footer-bg">'.__('Footer Background Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-footer-bg" class="om-bgcolor-picker" name="optin_footer_bg" value="' . $this->get_field( 'background', 'footer', '#2e3c44' ) . '" data-default-color="#2e3c44" data-target="om-lightbox-' . $theme_type . '-footer" />';
		echo '</p>';
		echo '</div>';

		echo '<h3>'.__('Title and Tagline','optin-monster').'</h3>';
		echo '<div class="title-tag-area">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline">'.__('Optin Title','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . htmlentities( $this->get_field( 'title', 'text', 'OptinMonster Rules!' ) ) . '" placeholder="'.__('e.g. OptinMonster Rules!','optin-monster').'" />';
		echo '<span class="input-controls">';
		echo $this->get_meta_controls( 'title', true, array('font_weight'=>'bold', 'text_align'=>'center') );
		foreach ( (array) $this->get_field( 'title', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-color">'.__('Optin Title Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $this->get_field( 'title', 'color', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-title" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-font">'.__('Optin Title Font','optin-monster').'</label>';
		echo '<select id="om-lightbox-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
		foreach ( $this->account->get_available_fonts() as $font ) :
			$selected = $this->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-size">'.__('Optin Title Font Size','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $this->get_field( 'title', 'size', '72' ) . '" placeholder="'.__('e.g. 72','optin-monster').'" />';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline">'.__('Optin Tagline','optin-monster').'</label>';
		echo '<textarea id="om-lightbox-' . $theme_type . '-tagline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="'.__('e.g. OptinMonster explodes your email list!','optin-monster').'" rows="4">' . htmlentities( $this->get_field( 'tagline', 'text', 'OptinMonster explodes your email list!' ) ) . '</textarea>';
		echo '<span class="input-controls">';
		echo $this->get_meta_controls( 'tagline', true, array('text_align'=>'center') );
		foreach ( (array) $this->get_field( 'tagline', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-color">'.__('Optin Tagline Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $this->get_field( 'tagline', 'color', '#b8d4e5' ) . '" data-default-color="#b8d4e5" data-target="om-lightbox-' . $theme_type . '-optin-tagline" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-font">'.__('Optin Tagline Font','optin-monster').'</label>';
		echo '<select id="om-lightbox-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
		foreach ( $this->account->get_available_fonts() as $font ) :
			$selected = $this->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-size">'.__('Optin Tagline Font Size','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $this->get_field( 'tagline', 'size', '20' ) . '" placeholder="'.__('e.g. 20','optin-monster').'" />';
		echo '</p>';
		echo '</div>';
		echo '</div>';

		if ( ! $this->meta['custom_html'] ) :
			echo '<h3>'.__('Fields and Buttons','optin-monster').'</h3>';
			echo '<div class="fields-area">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name"><input style="width:auto;margin-right:3px;" type="checkbox" id="om-lightbox-' . $theme_type . '-name" name="optin_name_show" value="' . $this->get_field( 'name', 'show','Show Optin Name Field?' ) . '"' . checked( $this->get_field( 'name', 'show' ), 1, false ) . ' /> '.__('Show Optin Name Field?','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $this->get_field( 'name', 'placeholder', 'Your Name' ) . '" placeholder="'.__('e.g. Your Name','optin-monster').'" />';
			echo '</p>';
			echo '<div class="optin-input-meta">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name-color">'.__('Optin Name Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $this->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-name" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name-font">'.__('Optin Name Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email">'.__('Optin Email Field','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $this->get_field( 'email', 'placeholder', 'Your Email' ) . '" placeholder="'.__('e.g. Your Email','optin-monster').'" />';
			echo '</p>';
			echo '<div class="optin-input-meta">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email-color">'.__('Optin Email Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $this->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-email" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email-font">'.__('Optin Email Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit">'.__('Optin Submit Field','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $this->get_field( 'submit', 'placeholder', 'Sign Me Up!' ) . '" placeholder="e.g. Sign Me Up!" />';
			echo '<span class="input-controls">';
			echo $this->get_meta_controls( 'submit', true, array('text_align'=>'center') );
			foreach ( (array) $this->get_field( 'submit', 'meta' ) as $prop => $style )
				echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
			echo '</span>';
			echo '</p>';
			echo '<div class="optin-input-meta last">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-field-color">'.__('Optin Submit Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $this->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-bg-color">'.__('Optin Submit Background Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $this->get_field( 'submit', 'bg_color', '#e4822a' ) . '" data-default-color="#e4822a" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-font">'.__('Optin Submit Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';
			echo '</div>';
		endif;

		echo '<h3>'.__('Custom Optin CSS','optin-monster').'</h3>';
		echo '<div class="custom-css-area">';
		echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
		echo '<p><strong><code>html div#om-' . $this->optin->post_name . '</code></strong></p>';
		echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $this->optin->post_name . ' input[type=submit], html div#' . $this->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $this->get_field( 'custom_css', '', '' ) . '</textarea>';
		echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="design-content">';
		echo '</div>';
		echo '</div>';

		return ob_get_clean();
	}
	public function get_postal_theme( $theme_type ) {


		ob_start();
		echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
		echo '<div class="design-sidebar">';
		echo '<div class="controls-area om-clearfix">';
		echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
		echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
		echo '</div>';
		echo '<div class="title-area om-clearfix">';
		echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
		echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
		echo '</div>';
		echo '<div class="accordion-area om-clearfix">';
		echo '<h3>'.__('Title and Tagline','optin-monster').'</h3>';
		echo '<div class="title-tag-area">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline">'.__('Optin Title','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . htmlentities( $this->get_field( 'title', 'text', 'OptinMonster Rules!' ) ) . '" placeholder="'.__('e.g. OptinMonster Rules!','optin-monster').'" />';
		echo '<span class="input-controls">';
		echo $this->get_meta_controls( 'title', true, array('font_weight'=>'bold', 'text_align'=>'center') );
		foreach ( (array) $this->get_field( 'title', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-color">'.__('Optin Title Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $this->get_field( 'title', 'color', '#5a6870' ) . '" data-default-color="#5a6870" data-target="om-lightbox-' . $theme_type . '-optin-title" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-font">'.__('Optin Title Font','optin-monster').'</label>';
		echo '<select id="om-lightbox-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
		foreach ( $this->account->get_available_fonts() as $font ) :
			$selected = $this->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-size">'.__('Optin Title Font Size','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $this->get_field( 'title', 'size', '42' ) . '" placeholder="'.__('e.g. 42','optin-monster').'" />';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline">'.__('Optin Tagline','optin-monster').'</label>';
		echo '<textarea id="om-lightbox-' . $theme_type . '-tagline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="'.__('e.g. OptinMonster explodes your email list!','optin-monster').'" rows="4">' . htmlentities( $this->get_field( 'tagline', 'text', 'OptinMonster explodes your email list!' ) ) . '</textarea>';
		echo '<span class="input-controls">';
		echo $this->get_meta_controls( 'tagline', true, array('text_align'=>'center') );
		foreach ( (array) $this->get_field( 'tagline', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-color">'.__('Optin Tagline Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $this->get_field( 'tagline', 'color', '#8d9aa1' ) . '" data-default-color="#8d9aa1" data-target="om-lightbox-' . $theme_type . '-optin-tagline" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-font">'.__('Optin Tagline Font','optin-monster').'</label>';
		echo '<select id="om-lightbox-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
		foreach ( $this->account->get_available_fonts() as $font ) :
			$selected = $this->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-size">'.__('Optin Tagline Font Size','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $this->get_field( 'tagline', 'size', '30' ) . '" placeholder="'.__('e.g. 30','optin-monster').'" />';
		echo '</p>';
		echo '</div>';
		echo '</div>';

		if ( ! $this->meta['custom_html'] ) :
			echo '<h3>'.__('Fields and Buttons','optin-monster').'</h3>';
			echo '<div class="fields-area">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name"><input style="width:auto;margin-right:3px;" type="checkbox" id="om-lightbox-' . $theme_type . '-name" name="optin_name_show" value="' . $this->get_field( 'name', 'show','Show Optin Name Field?' ) . '"' . checked( $this->get_field( 'name', 'show' ), 1, false ) . ' /> '.__('Show Optin Name Field?','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $this->get_field( 'name', 'placeholder', 'Your Name' ) . '" placeholder="'.__('e.g. Your Name','optin-monster').'" />';
			echo '</p>';
			echo '<div class="optin-input-meta">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name-color">'.__('Optin Name Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $this->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-name" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name-font">'.__('Optin Name Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email">'.__('Optin Email Field','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $this->get_field( 'email', 'placeholder', 'Your Email' ) . '" placeholder="'.__('e.g. Your Email','optin-monster').'" />';
			echo '</p>';
			echo '<div class="optin-input-meta">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email-color">'.__('Optin Email Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $this->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-email" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email-font">'.__('Optin Email Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit">'.__('Optin Submit Field','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $this->get_field( 'submit', 'placeholder', 'Sign Me Up!' ) . '" placeholder="e.g. Sign Me Up!" />';
			echo '<span class="input-controls">';
			echo $this->get_meta_controls( 'submit', true, array('text_align'=>'center') );
			foreach ( (array) $this->get_field( 'submit', 'meta' ) as $prop => $style )
				echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
			echo '</span>';
			echo '</p>';
			echo '<div class="optin-input-meta last">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-field-color">'.__('Optin Submit Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $this->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-bg-color">'.__('Optin Submit Background Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $this->get_field( 'submit', 'bg_color', '#50bbe8' ) . '" data-default-color="#50bbe8" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
			echo '<input type="hidden" name="optin_submit_border_color" value="' . $this->get_field( 'submit', 'border_color', '#429bc0' ) . '" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-font">'.__('Optin Submit Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';

			echo '</div>';
		endif;

		echo '<h3>'.__('Custom Optin CSS','optin-monster').'</h3>';
		echo '<div class="custom-css-area">';
		echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
		echo '<p><strong><code>html div#om-' . $this->optin->post_name . '</code></strong></p>';
		echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $this->optin->post_name . ' input[type=submit], html div#' . $this->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $this->get_field( 'custom_css', '', '' ) . '</textarea>';
		echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="design-content">';
		echo '</div>';
		echo '</div>';

		return ob_get_clean();
	}
	public function get_target_theme( $theme_type ) {

		ob_start();
		echo '<div class="design-customizer-ui" data-optin-theme="' . $theme_type . '">';
		echo '<div class="design-sidebar">';
		echo '<div class="controls-area om-clearfix">';
		echo '<a class="button button-secondary button-large grey pull-left close-design" href="#" title="'.__('Close Customizer','optin-monster').'">'.__('Close','optin-monster').'</a>';
		echo '<a class="button button-primary button-large orange pull-right save-design" href="#" title="'.__('Save Changes','optin-monster').'">'.__('Save','optin-monster').'</a>';
		echo '</div>';
		echo '<div class="title-area om-clearfix">';
		echo '<p class="no-margin">'.__('You are now previewing:','optin-monster').'</p>';
		echo '<h3 class="no-margin">' . ucwords( str_replace( '-', ' ', $theme_type ) ) . '</h3>';
		echo '</div>';
		echo '<div class="accordion-area om-clearfix">';
		echo '<h3>'.__('Background and Border Colors','optin-monster').'</h3>';
		echo '<div class="colors-area">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-border-bg">'.__('Left Border Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-border-bg" class="om-bgcolor-picker" name="optin_border_bg" value="' . $this->get_field( 'background', 'border', '#58ce55' ) . '" data-default-color="#58ce55" data-target="om-lightbox-' . $theme_type . '-optin-wrap" />';
		echo '</p>';
		echo '</div>';

		echo '<h3>'.__('Title and Tagline','optin-monster').'</h3>';
		echo '<div class="title-tag-area">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline">'.__('Optin Title','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title" type="text" value="' . htmlentities( $this->get_field( 'title', 'text', 'OptinMonster Rules!' ) ) . '" placeholder="'.__('e.g. OptinMonster Rules!','optin-monster').'" />';
		echo '<span class="input-controls">';
		echo $this->get_meta_controls( 'title', true, array('font_weight' => 'bold', 'text_align'=>'center') );
		foreach ( (array) $this->get_field( 'title', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_title_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-color">'.__('Optin Title Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-headline-color" class="om-color-picker" name="optin_title_color" value="' . $this->get_field( 'title', 'color', '#5a6870' ) . '" data-default-color="#5a6870" data-target="om-lightbox-' . $theme_type . '-optin-title" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-font">'.__('Optin Title Font','optin-monster').'</label>';
		echo '<select id="om-lightbox-' . $theme_type . '-headline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-title" data-property="font-family" name="optin_title_font">';
		foreach ( $this->account->get_available_fonts() as $font ) :
			$selected = $this->get_field( 'title', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-headline-size">'.__('Optin Title Font Size','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-title" name="optin_title_size" class="optin-size" type="text" value="' . $this->get_field( 'title', 'size', '28' ) . '" placeholder="'.__('e.g. 28','optin-monster').'" />';
		echo '</p>';
		echo '</div>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline">'.__('Optin Tagline','optin-monster').'</label>';
		echo '<textarea id="om-lightbox-' . $theme_type . '-tagline" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-tagline" type="text" name="optin_tagline" placeholder="'.__('e.g. OptinMonster explodes your email list!','optin-monster').'" rows="4">' . htmlentities( $this->get_field( 'tagline', 'text', 'OptinMonster explodes your email list!' ) ) . '</textarea>';
		echo '<span class="input-controls">';
		echo $this->get_meta_controls( 'tagline', true, array('text_align'=>'center') );
		foreach ( (array) $this->get_field( 'tagline', 'meta' ) as $prop => $style )
			echo '<input type="hidden" name="optin_tagline_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
		echo '</span>';
		echo '</p>';
		echo '<div class="optin-input-meta last">';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-color">'.__('Optin Tagline Color','optin-monster').'</label>';
		echo '<input type="text" id="om-lightbox-' . $theme_type . '-tagline-color" class="om-color-picker" name="optin_tagline_color" value="' . $this->get_field( 'tagline', 'color', '#8d9aa1' ) . '" data-default-color="#8d9aa1" data-target="om-lightbox-' . $theme_type . '-optin-tagline" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-font">'.__('Optin Tagline Font','optin-monster').'</label>';
		echo '<select id="om-lightbox-' . $theme_type . '-tagline-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-tagline" data-property="font-family" name="optin_tagline_font">';
		foreach ( $this->account->get_available_fonts() as $font ) :
			$selected = $this->get_field( 'tagline', 'font' ) == $font ? ' selected="selected"' : '';
			echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
		endforeach;
		echo '</select>';
		echo '</p>';
		echo '<p>';
		echo '<label for="om-lightbox-' . $theme_type . '-tagline-size">'.__('Optin Tagline Font Size','optin-monster').'</label>';
		echo '<input id="om-lightbox-' . $theme_type . '-headline-size" data-target="om-lightbox-' . $theme_type . '-optin-tagline" name="optin_tagline_size" class="optin-size" type="text" value="' . $this->get_field( 'tagline', 'size', '18' ) . '" placeholder="'.__('e.g. 18','optin-monster').'" />';
		echo '</p>';
		echo '</div>';
		echo '</div>';

		if ( ! $this->meta['custom_html'] ) :
			echo '<h3>'.__('Fields and Buttons','optin-monster').'</h3>';
			echo '<div class="fields-area">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name"><input style="width:auto;margin-right:3px;" type="checkbox" id="om-lightbox-' . $theme_type . '-name" name="optin_name_show" value="' . $this->get_field( 'name', 'show','Show Optin Name Field?' ) . '"' . checked( $this->get_field( 'name', 'show' ), 1, false ) . ' /> '.__('Show Optin Name Field?','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-name-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-name" type="text" name="optin_name_placeholder" value="' . $this->get_field( 'name', 'placeholder', 'Your Name' ) . '" placeholder="'.__('e.g. Your Name','optin-monster').'" />';
			echo '</p>';
			echo '<div class="optin-input-meta">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name-color">'.__('Optin Name Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-name-color" class="om-color-picker" name="optin_name_color" value="' . $this->get_field( 'name', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-name" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-name-font">'.__('Optin Name Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-name-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-name" data-property="font-family" name="optin_name_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'name', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email">'.__('Optin Email Field','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-email-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-email" type="text" name="optin_email_placeholder" value="' . $this->get_field( 'email', 'placeholder', 'Your Email' ) . '" placeholder="'.__('e.g. Your Email','optin-monster').'" />';
			echo '</p>';
			echo '<div class="optin-input-meta">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email-color">'.__('Optin Email Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-email-color" class="om-color-picker" name="optin_email_color" value="' . $this->get_field( 'email', 'color', '#282828' ) . '" data-default-color="#282828" data-target="om-lightbox-' . $theme_type . '-optin-email" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-email-font">'.__('Optin Email Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-email-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-email" data-property="font-family" name="optin_email_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'email', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit">'.__('Optin Submit Field','optin-monster').'</label>';
			echo '<input id="om-lightbox-' . $theme_type . '-submit-placeholder" class="main-field" data-target="om-lightbox-' . $theme_type . '-optin-submit" type="text" name="optin_submit_placeholder" value="' . $this->get_field( 'submit', 'placeholder', 'Subscribe' ) . '" placeholder="e.g. Subscribe!" />';
			echo '<span class="input-controls">';
			echo $this->get_meta_controls( 'submit', true, array('font_weight'=>'bold', 'text_align'=>'center') );
			foreach ( (array) $this->get_field( 'submit', 'meta' ) as $prop => $style )
				echo '<input type="hidden" name="optin_submit_' . str_replace( '_', '-', $prop ) . '" value="' . $style . '" />';
			echo '</span>';
			echo '</p>';
			echo '<div class="optin-input-meta last">';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-field-color">'.__('Optin Submit Field Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-field-color" class="om-color-picker" name="optin_submit_field_color" value="' . $this->get_field( 'submit', 'field_color', '#fff' ) . '" data-default-color="#fff" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-bg-color">'.__('Optin Submit Background Color','optin-monster').'</label>';
			echo '<input type="text" id="om-lightbox-' . $theme_type . '-submit-bg-color" class="om-bgcolor-picker" name="optin_submit_bg_color" value="' . $this->get_field( 'submit', 'bg_color', '#58ce55' ) . '" data-default-color="#58ce55" data-target="om-lightbox-' . $theme_type . '-optin-submit" />';
			echo '<input type="hidden" name="optin_submit_border_color" value="' . $this->get_field( 'submit', 'border_color', '#429bc0' ) . '" />';
			echo '</p>';
			echo '<p>';
			echo '<label for="om-lightbox-' . $theme_type . '-submit-font">'.__('Optin Submit Field Font','optin-monster').'</label>';
			echo '<select id="om-lightbox-' . $theme_type . '-submit-font" class="main-field optin-font" data-target="om-lightbox-' . $theme_type . '-optin-submit" data-property="font-family" name="optin_submit_font">';
			foreach ( $this->account->get_available_fonts() as $font ) :
				$selected = $this->get_field( 'submit', 'font' ) == $font ? ' selected="selected"' : '';
				echo '<option value="' . $font . '"' . $selected . '>' . $font . '</option>';
			endforeach;
			echo '</select>';
			echo '</p>';
			echo '</div>';

			echo '</div>';
		endif;

		echo '<h3>'.__('Custom Optin CSS','optin-monster').'</h3>';
		echo '<div class="custom-css-area">';
		echo '<p><small>' . __( 'The textarea below is for adding custom CSS to this particular optin. Each of your custom CSS statements should be on its own line and be prefixed with the following declaration:', 'optin-monster' ) . '</small></p>';
		echo '<p><strong><code>html div#om-' . $this->optin->post_name . '</code></strong></p>';
		echo '<textarea id="om-lightbox-' . $theme_type . '-custom-css" name="optin_custom_css" placeholder="e.g. html div#om-' . $this->optin->post_name . ' input[type=submit], html div#' . $this->optin->post_name . ' button { background: #ff6600; }" class="om-custom-css">' . $this->get_field( 'custom_css', '', '' ) . '</textarea>';
		echo '<small><a href="http://optinmonster.com/docs/custom-css/" title="' . __( 'Custom CSS with OptinMonster', 'optin-monster' ) . '" target="_blank"><em>'.__('Click here for help on using custom CSS with OptinMonster.','optin-monster').'</em></a></small>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="design-content">';
		echo '</div>';
		echo '</div>';

		return ob_get_clean();
	}
	public function do_optin_code() {

		wp_enqueue_script( 'jquery-chosen', plugins_url( '/inc/js/jquery-chosen/chosen.jquery.min.js', $this->base->file ), array( 'jquery' ), '1.0.0', true );
		wp_enqueue_style( 'jquery-chosen-css', plugins_url( '/inc/js/jquery-chosen/chosen.min.css', $this->base->file ) );

    	$slug = $this->optin->post_name;
    	echo '<div class="create-optin-wrap om-clearfix">';
			echo '<ul class="create-optin-nav om-clearfix">';
				echo '<li><a class="disabled" href="#" title="'.__('Setup Your Optin','optin-monster').'" data-tab="om-optin-setup">'.__('1. Setup','optin-monster').'</a></li>';
				echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'config' => true, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Configure Your Optin','optin-monster').'" data-tab="om-optin-configure">'.__('2. Configure','optin-monster').'</a></li>';
				echo '<li><a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'design' => true, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" title="'.__('Design Your Optin','optin-monster').'" data-tab="om-optin-design">'.__('3. Design','optin-monster').'</a></li>';
				echo '<li><a class="active" href="#" title="Output Settings Your Optin" data-tab="om-optin-embed">'.__('4. Output Settings','optin-monster').'</a></li>';
			echo '</ul>';
			echo '<div class="create-optin-area" class="om-clearfix">';
				echo '<div id="om-optin-design" class="optin-ui">';
                	echo '<form id="om-edit-optin" method="post">';
                        echo '<input type="hidden" name="optinmonster-slug" value="' . $slug . '" />';
                        echo '<h2>'.__('Configure Optin Output Settings','optin-monster').'</h2>';
                        do_action( 'optin_monster_code_top' );
                        echo '<div class="optin-config-box">';
							echo '<h4><label for="om-enabled">' . __( 'Enable optin on site?', 'optin-monster' ) . '</label></h4>';
                            echo '<input id="om-enabled" name="om-enabled" type="checkbox" tabindex="57" value="' . $this->get_optin_setting( 'enabled' ) . '"' . checked( $this->get_optin_setting( 'enabled' ), 1, false ) . ' />';
                            echo '<label class="description" for="om-enabled" style="font-weight:400;display:inline;margin-left:5px">'.__('The optin will not be displayed on this site unless this setting is checked.','optin-monster').'</label>';
						echo '</div>';
						echo '<div class="optin-config-box">';
							echo '<h4><label for="om-global">' . __( 'Load optin globally?', 'optin-monster' ) . '</label></h4>';
                            echo '<input id="om-global" name="om-global" type="checkbox" tabindex="58" value="' . $this->get_optin_setting( 'global' ) . '"' . checked( $this->get_optin_setting( 'global' ), 1, false ) . ' />';
                            echo '<label class="description" for="om-global" style="font-weight:400;display:inline;margin-left:5px">'.__('If checked, the optin code will be loaded on all pages of your site except those explicitly selected in the field below. <strong>All other settings will be ignored.</strong>','optin-monster').'</strong></label>';
						echo '</div>';
						$posts = $this->get_posts_array();
						$never_selected = (array) $this->get_optin_setting( 'never' );
						echo '<div class="optin-config-box">';
							echo '<h4><label for="om-never">' . __( 'Never load optin on:', 'optin-monster' ) . '</label></h4>';
							echo '<select id="om-never" name="om-never[]" class="chosen-posts" data-placeholder="Choose posts" tabindex="59" style="width: 400px;" multiple>';
								$this->do_post_select( $posts, $never_selected );
							echo '</select>';
							echo '<label class="description" for="om-exclusive" style="font-weight:400;">'.__('Prevents the optin from loading on the selected posts.','optin-monster').'</label>';
						echo '</div>';
						$exclusive_selected = (array) $this->get_optin_setting( 'exclusive' );
						echo '<div class="optin-config-box">';
							echo '<h4><label for="om-exclusive">' . __( 'Only load optin on:', 'optin-monster' ) . '</label></h4>';
							echo '<select id="om-exclusive" name="om-exclusive[]" class="chosen-posts" data-placeholder="Choose posts" tabindex="59" style="width: 400px;" multiple>';
								$this->do_post_select( $posts, $exclusive_selected );
							echo '</select>';
                            echo '<label class="description" for="om-exclusive" style="font-weight:400;">'.__('Loads the optin on only the selected posts. <strong>All other settings below will be ignored.','optin-monster').'</strong></label>';
						echo '</div>';
						echo '<div class="optin-config-box">';
							echo '<h4><label for="om-categories-0">' . __( 'Load optin on post categories:', 'optin-monster' ) . '</label></h4>';
                            $categories = get_categories();
                            if ( $categories ) :
                                wp_category_checklist( 0, 0, (array) $this->get_optin_setting( 'categories' ), false, null, false );
                            endif;
                            echo '<p><label class="description" for="om-categories-0" style="font-weight:400;">'.__('Loads the optin on posts that are in one of the selected categories. <strong>The "Posts" checkbox below will be ignored.','optin-monster').'</strong></label></p>';
						echo '</div>';
						echo '<div class="optin-config-box">';
							echo '<h4><label for="om-show-index">' . __( 'Load optin on:', 'optin-monster' ) . '</label></h4>';
                            echo '<input type="checkbox" id="om-show-index" name="om-show[]" value="index"' . checked( in_array( 'index', (array) $this->get_optin_setting( 'show' ) ), 1, false ) . ' />';
                            echo '<label for="om-show-index" style="font-weight:400;display:inline;margin-left:5px">' . __( 'Front Page, Archive Pages and Search Results', 'optin-monster' ) . '</label><br>';
                            $post_types = get_post_types( array( 'public' => true ) ); foreach ( (array) $post_types as $show ) : $pt_object = get_post_type_object( $show ); $label = $pt_object->labels->name;
                                echo '<input type="checkbox" id="om-show-' . esc_html( strtolower( $label ) ) . '" name="om-show[]" value="' . $show . '"' . checked( in_array( $show, (array) $this->get_optin_setting( 'show' ) ), 1, false ) . ' />';
                                echo '<label for="om-show-' . esc_html( strtolower( $label ) ) . '" style="font-weight:400;display:inline;margin-left:5px">' . esc_html( $label ) . '</label><br>';
                             endforeach;
                            echo '<p><label class="description" for="om-show-index" style="font-weight:400;">'.__('Loads the optin on posts that match the selection criteria.','optin-monster').'</label></p>';
						echo '</div>';
						do_action( 'optin_monster_code_bottom' );
                    echo '</form>';
                echo '</div>';
            echo '</div>';
            echo '<div class="create-optin-toolbar om-clearfix">';
				echo '<a href="' . add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'action' => 'build', 'design' => 1, 'type' => $this->type, 'edit' => $this->optin->post_name ), admin_url( 'admin.php' ) ) . '" class="button button-secondary button-large grey previous-step" href="#" data-optin-tab="om-optin-configure" title="'.__('Back to Previous Step','optin-monster').'">'.__('Back','optin-monster').'</a>';
				echo '<a class="button button-primary button-large orange next-step final-step save-embed-code" href="#" title="'.__('Save Optin Output Settings','optin-monster').'">'.__('Save Optin Output Settings','optin-monster').'</a>';
			echo '</div>';
        echo '</div>';

	}

	public function get_posts_array() {

		global $wpdb;

		$args = array(
			'post_status' => array( 'publish' ),
			'posts_per_page' => -1,
		);

		$posts = $wpdb->get_results(
			"
			SELECT ID, post_title, post_type, post_date
			FROM $wpdb->posts
			WHERE post_status = 'publish'
				AND post_type NOT IN ('attachment', 'revision', 'nav_menu_item', 'optin')
			ORDER BY post_type, post_date DESC
			"
		);

		return $posts;

	}

	public function do_post_select( $posts, $selected ) {

		$prior_group = '';
		foreach ( $posts as $post ) {
			$type = get_post_type_object( $post->post_type );
			$type = $type->labels->name;
			if ( $type != $prior_group ) {
				if ( '' != $prior_group ) {
					echo "</optgroup>";
				}
				echo '<optgroup label="' . $type . '">';
			}

			if ( in_array( $post->ID, $selected ) ) {
				$selection = 'selected';
			} else {
				$selection = '';
			}
			echo '<option value="' . $post->ID . '" ' . $selection . ' >' . $post->post_title . '</option>';
			$prior_group = $type;
		}
		echo '</optgroup>';

	}

	public function do_optin_code_script() {

    	?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
	            $('.chosen-posts').chosen();
                $('.save-embed-code').on('click', function(e){
                    e.preventDefault();
                    var $this = $(this),
                        text  = $this.text();
                    $this.text('Saving...');
                    $.post(ajaxurl, { action: 'save_optin_output', optin: '<?php echo $this->optin->ID; ?>', hash: '<?php echo $this->optin->post_name; ?>', data: $('#om-edit-optin').serialize() }, function(res){
                        $this.text('Saved!');
    					setTimeout(function(){
        					$this.text(text);
    					}, 1000);
                    }, 'json');
                });
            });
        </script>
        <?php

        // Run a hook for any extra code scripts.
		do_action( 'optin_monster_code_script' );

	}

	public function generate_postname_hash( $length = 10, $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789' ) {

		$str   = '';
	    $count = strlen( $charset );
	    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	    $alpha_count = strlen( $alpha );

	    while ( $length-- ) {
	        $str .= $charset[mt_rand( 0, $count - 1 )];
	    }

	    return substr_replace( $str, $alpha[mt_rand( 0, $alpha_count - 1 )], 0, 1 );

	}

    /**
     * Returns a specific optin setting.
     *
     * @since 1.0.0
     */
    public function get_optin_setting( $setting ) {

        return isset( $this->meta['display'][$setting] ) ? $this->meta['display'][$setting] : '';

    }

}

// Initialize the class.
global $optin_monster_tab_optins;
$optin_monster_tab_optins = new optin_monster_tab_optins();