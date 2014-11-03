<?php
/**
 * Output class.
 *
 * @since   2.0.0
 *
 * @package Optin_Monster
 * @author  Thomas Griffin
 */
class Optin_Monster_Output {

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
     * Checks to see if we are in preview mode.
     *
     * @since 2.0.0
     *
     * @var bool
     */
    public $preview = false;

    /**
     * Holds optin data.
     *
     * @since 2.0.0
     *
     * @var array
     */
    public $data = array();

    /**
     * Flag to check if the IE polyfill has been output.
     *
     * @since 2.0.0
     *
     * @var bool
     */
    public $ie = false;

    /**
     * Primary class constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Optin_Monster::get_instance();

        // Set the preview variable.
        if ( isset( $_GET['om_preview_frame'] ) && $_GET['om_preview_frame'] || isset( $_GET['om_view'] ) && 'preview' == $_GET['om_view'] ) {
            $this->preview = true;
        }

        // Set other necessary class properties.
        if ( isset( $_GET['om_optin_id'] ) ) {
            $this->optin_id = absint( $_GET['om_optin_id'] );
        } else if ( isset( $_POST['id'] ) ) {
            $this->optin_id = absint( $_POST['id'] );
        } else if ( $this->preview ) {
            $this->optin_id = absint( $_GET['om_preview_optin'] );
        } else {
            $this->optin_id = false;
        }

        // Only set the optin and meta properties if the optin ID can be found.
        if ( $this->optin_id ) {
            $this->optin = get_post( $this->optin_id );
            $this->meta  = get_post_meta( $this->optin_id, '_om_meta', true );
        }

        // Return early if necessary.
        if ( ! $this->base->get_license_key() || $this->base->get_license_key_errors() ) {
	        return;
        }

        // Handle all processes to load OptinMonster on the page.
        add_action( 'wp_enqueue_scripts', array( $this, 'api_script' ) );
        add_filter( 'optin_monster_query_filter', array( $this, 'query_filter' ) );

        // Run the check to load OptinMonster.
        $this->maybe_load_optinmonster();

    }

    /**
     * Enqueues the OptinMonster API script.
     *
     * @since 2.0.0
     */
    public function api_script() {

        if ( $this->is_preview() ) {
            return;
        }

        wp_enqueue_script( $this->base->plugin_slug . '-api-script', OPTINMONSTER_APIURL, array( 'jquery' ), $this->base->version );

    }

    /**
     * Set the default query arg filter for OptinMonster.
     *
     * @since 2.0.0
     *
     * @param bool $bool Whether or not to alter the query arg filter.
     *
     * @return bool      True or false based on query arg detection.
     */
    public function query_filter( $bool ) {

        // If "omhide" is set, the query filter exists.
        if ( isset( $_GET['omhide'] ) && $_GET['omhide'] ) {
            return true;
        }

        return $bool;

    }

    /**
     * Conditionally loads the OptinMonster script based on the query filter detection.
     *
     * @since 2.0.0
     */
    public function maybe_load_optinmonster() {

        // If a URL suffix is set to not load optinmonster, don't do anything.
        if ( apply_filters( 'optin_monster_query_filter', false ) ) {
            // Set a global cookie since likely we do not want folks seeing popups who have the parameter passed to begin with.
            // This will use the global cookie value from the Misc settings. If it is 0, we default to 30 days and provide a filter for it.
            $option = get_option( 'optin_monster' );
            if ( ! empty( $option['cookie'] ) && (bool) $option['cookie'] ) {
                $global_cookie = $option['cookie'];
            } else {
                $global_cookie = 30;
            }

            $global_cookie = apply_filters( 'optin_monster_query_cookie', $global_cookie );
            if ( $global_cookie ) {
                setcookie( 'om-global-cookie', 1, time() + 3600 * 24 * (int) $global_cookie, COOKIEPATH, COOKIE_DOMAIN, false );
            }

            return;
        }

        // Return early if necessary.
        if ( ! $this->base->get_license_key() || $this->base->get_license_key_errors() ) {
	        return;
        }

        // Add the hook to allow OptinMonster to process.
        add_action( 'pre_get_posts', array( $this, 'load_optinmonster_inline' ), 9999 );
        add_action( 'wp_footer', array( $this, 'load_optinmonster' ), 9999 );

    }

    /**
     * Loads an inline optin form (sidebar and after post) by checking against the current query.
     *
     * @since 2.0.0
     *
     * @param object $query The current main WP query object.
     */
    public function load_optinmonster_inline( $query ) {

        // If we are in the admin or not on the main query, do nothing.
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        // Return early if necessary.
        if ( ! $this->base->get_license_key() || $this->base->get_license_key_errors() ) {
	        return;
        }

        // If we are in a preview state, immediately load the optin. Do this conditionally for a sidebar optin.
        if ( $this->is_preview() ) {
        	// Only load for sidebar and after post optins.
	        $type  = isset( $this->meta['type'] ) ? $this->meta['type'] : '';
	        if ( 'sidebar' !== $type && 'post' !== $type ) {
	            return;
	        }

            $priority = apply_filters( 'optin_monster_post_priority', 999 );
            add_filter( 'the_content', array( $this, 'load_optinmonster_inline_preview' ), $priority );
        } else {
            $priority = apply_filters( 'optin_monster_post_priority', 999 );
            add_filter( 'the_content', array( $this, 'load_optinmonster_inline_content' ), $priority );
        }

    }

    /**
     * Filters the content to output an inline optin form in Preview mode.
     *
     * @since 2.0.0
     *
     * @param string $content The current HTML string of main content.
     */
    public function load_optinmonster_inline_preview( $content ) {

        global $post;

		// First, we check against the preview ID. If we are not on our actual preview page, we continue.
        // If we are not on a single post, the global $post is not set or the post status is not published, return early.
        $preview_id = absint( get_option( 'optin_monster_preview_page' ) );
        if ( $preview_id !== $post->ID ) {
        	if ( ! is_singular( 'post' ) || empty( $post ) || isset( $post->ID ) && 'publish' !== get_post_status( $post->ID ) ) {
            	return $content;
			}
		}

		// Don't do anything for excerpts.
        // This prevents the optin accidentally being output when get_the_excerpt() or wp_trim_excerpt() is
        // called by a theme or plugin, and there is no excerpt, meaning they call the_content and break us.
		global $wp_current_filter;

        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }

        if ( in_array( 'wp_trim_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }

        $type  = isset( $this->meta['type'] ) ? $this->meta['type'] : '';
        $optin = $this->get_optin_monster( $this->optin_id );

        return 'sidebar' == $type && $this->is_preview() ? $optin . $content : $content . $optin;

    }

    /**
     * Filters the content to output an optin form.
     *
     * @since 2.0.0
     *
     * @param string $content The current HTML string of main content.
     */
    public function load_optinmonster_inline_content( $content ) {

        global $post;

        // If we are not on a single post, the global $post is not set or the post status is not published, return early.
        if ( ! is_singular( 'post' ) || empty( $post ) || isset( $post->ID ) && 'publish' !== get_post_status( $post->ID ) ) {
           	return $content;
        }

        // Don't do anything for excerpts.
        // This prevents the optin accidentally being output when get_the_excerpt() or wp_trim_excerpt() is
        // called by a theme or plugin, and there is no excerpt, meaning they call the_content and break us.
		global $wp_current_filter;

        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }

        if ( in_array( 'wp_trim_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }

        // Prepare variables.
        $post_id = get_queried_object_id();
        if ( ! $post_id && 'page' == get_option( 'show_on_front' ) ) {
            $post_id = get_option( 'page_for_posts' );
        } else {
            $post_id = isset( $post ) ? $post->ID : 0;
        }
        $optins       = $this->base->get_optins();
        $init         = array();

        // If no optins are found, return early.
        if ( ! $optins ) {
        	return $content;
        }

        // Loop through each optin and optionally output it on the site.
        foreach ( $optins as $optin ) {
	        // Don't try to load if the optin is not enabled. Otherwise, try split test.
	        $meta = get_post_meta( $optin->ID, '_om_meta', true );
            if ( empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] ) {
                continue;
            }

            // If the optin has split tests, overwrite the main data with the clone data.
            $original    = $optin;
            $split_tests = $this->base->get_split_tests( $optin->ID );
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
                continue;
            }

            // Prepare variables for the selected optin.
            $meta = get_post_meta( $optin->ID, '_om_meta', true );
            $slug = $optin->post_name;
            $type = $meta['type'];

            // If the type is a sidebar or after post optin, pass over it.
            if ( 'post' !== $type ) {
                continue;
            }

            // If the optin is not enabled, pass over to the next optin.
            if ( empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] ) {
                continue;
            }

            // If the optin is to be hidden for logged in users, pass over it.
            if ( isset( $meta['logged_in'] ) && $meta['logged_in'] && is_user_logged_in() ) {
                continue;
            }

            // If the optin is only to be shown on specific post IDs, get the code and break.
            if ( ! empty( $meta['display']['exclusive'] ) ) {
                if ( in_array( $post_id, $meta['display']['exclusive'] ) ) {
                    $content .= $this->get_optin_monster( $optin->ID );
                    $this->track_manual( $optin->ID );
                    continue;
                }
            }

            // Exclude posts/pages from optin display.
            if ( ! empty( $meta['display']['never'] ) ) {
                if ( in_array( $post_id, (array) $meta['display']['never'] ) ) {
                    continue;
                }
            }

            // If the optin is only to be shown on particular categories, get the code and break.
            $category_match = false;
            if ( ! empty( $meta['display']['categories'] ) && ( 'post' == get_post_type() ) ) {
                // Don't try to load on the blog home page even if a category that is selected appears in the loop.
                if ( is_home() ) {
                    // Run a check for archive-type pages.
                    if ( ! empty( $meta['display']['show'] ) ) {
                        // If showing on index pages and we are on an index page, show the optin.
                        if ( in_array( 'index', (array) $meta['display']['show'] ) ) {
                            if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                                $content .= $this->get_optin_monster( $optin->ID );
                                $this->track_manual( $optin->ID );
                                continue;
                            }
                        }

                        // Check if we should show on a selected post type.
                        if ( in_array( get_post_type(), (array) $meta['display']['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                            $content .= $this->get_optin_monster( $optin->ID );
                            $this->track_manual( $optin->ID );
                            continue;
                        }
                    }
                }

                $categories = wp_get_object_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
                foreach ( (array) $categories as $category_id ) {
                    if ( in_array( $category_id, $meta['display']['categories'] ) && ! is_archive() ) {
                        $content .= $this->get_optin_monster( $optin->ID );
                        $this->track_manual( $optin->ID );
                        continue 2;
                    }
                }
            }

            // If the optin is set to be automatically displayed, show it.
            if ( isset( $meta['display']['automatic'] ) && $meta['display']['automatic'] ) {
                $content .= $this->get_optin_monster( $optin->ID );
                $this->track_manual( $optin->ID );
                continue;
            }
        }

        // Return the content.
        return $content;

    }

    /**
     * Loads the appropriate OptinMonster optins on the requested page.
     *
     * @since 2.0.0
     */
    public function load_optinmonster() {

    	// Return early if necessary.
        if ( ! $this->base->get_license_key() || $this->base->get_license_key_errors() ) {
	        return;
        }

        // If we are in a preview state, immediately load the optin.
        if ( $this->is_preview() ) {
            return $this->load_preview();
        }

        // Prepare variables.
        global $post;
        $post_id = get_queried_object_id();
        if ( ! $post_id && 'page' == get_option( 'show_on_front' ) ) {
            $post_id = get_option( 'page_for_posts' );
        } else {
            $post_id = isset( $post ) ? $post->ID : 0;
        }
        $optins       = $this->base->get_optins();
        $init         = array();

        // If no optins are found, return early.
        if ( ! $optins ) {
            return;
        }

        // Loop through each optin and optionally output it on the site.
        foreach ( $optins as $optin ) {
	        // Don't try to load if the optin is not enabled. Otherwise, try split test.
	        $meta = get_post_meta( $optin->ID, '_om_meta', true );
            if ( empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] ) {
                continue;
            }

            // If the optin has split tests, overwrite the main data with the clone data.
            $original    = $optin;
            $split_tests = $this->base->get_split_tests( $optin->ID );
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
                continue;
            }

            // Prepare variables for the selected optin.
            $meta = get_post_meta( $optin->ID, '_om_meta', true );
            $slug = $optin->post_name;
            $type = $meta['type'];

            // If the type is a sidebar or after post optin, pass over it.
            if ( 'sidebar' == $type || 'post' == $type ) {
                continue;
            }

            // If the optin is not enabled, pass over to the next optin.
            if ( empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] ) {
                continue;
            }

            // If the optin is to be hidden for logged in users, pass over it.
            if ( isset( $meta['logged_in'] ) && $meta['logged_in'] && is_user_logged_in() ) {
                continue;
            }

            // If the optin is only to be shown on specific post IDs, get the code and break.
            if ( ! empty( $meta['display']['exclusive'] ) ) {
                if ( in_array( $post_id, $meta['display']['exclusive'] ) ) {
                    $init[ $slug ] = $this->get_optin_monster( $optin->ID );
                    continue;
                }
            }

            // Exclude posts/pages from optin display.
            if ( ! empty( $meta['display']['never'] ) ) {
                if ( in_array( $post_id, (array) $meta['display']['never'] ) ) {
                    continue;
                }
            }

            // If the optin is only to be shown on particular categories, get the code and break.
            $category_match = false;
            if ( ! empty( $meta['display']['categories'] ) && ( 'post' == get_post_type() ) ) {
                // Don't try to load on the blog home page even if a category that is selected appears in the loop.
                if ( is_home() ) {
                    // Run a check for archive-type pages.
                    if ( ! empty( $meta['display']['show'] ) ) {
                        // If showing on index pages and we are on an index page, show the optin.
                        if ( in_array( 'index', (array) $meta['display']['show'] ) ) {
                            if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                                $init[ $slug ] = $this->get_optin_monster( $optin->ID );
                                continue;
                            }
                        }

                        // Check if we should show on a selected post type.
                        if ( in_array( get_post_type(), (array) $meta['display']['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                            $init[ $slug ] = $this->get_optin_monster( $optin->ID );
                            continue;
                        }
                    }
                }

                $categories = wp_get_object_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
                foreach ( (array) $categories as $category_id ) {
                    if ( in_array( $category_id, $meta['display']['categories'] ) && ! is_archive() ) {
                        $init[ $slug ] = $this->get_optin_monster( $optin->ID );
                        continue 2;
                    }
                }

                // If no category match is found, iterate to the next optin.
                if ( ! $category_match ) {
                    continue;
                }
            }

            // Run a check for archive-type pages.
            if ( ! empty( $meta['display']['show'] ) ) {
                // If showing on index pages and we are on an index page, show the optin.
                if ( in_array( 'index', (array) $meta['display']['show'] ) ) {
                    if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                        $init[ $slug ] = $this->get_optin_monster( $optin->ID );
                        continue;
                    }
                }

                // Check if we should show on a selected post type.
                if ( in_array( get_post_type(), (array) $meta['display']['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                    $init[ $slug ] = $this->get_optin_monster( $optin->ID );
                    continue;
                }
            }

            // Finally, check the global scope to load an optin.
            if ( isset( $meta['display']['global'] ) && $meta['display']['global'] ) {
                $init[ $slug ] = $this->get_optin_monster( $optin->ID );
                continue;
            }

            // Allow devs to filter the final output for more granular control over optin targeting.
            // Devs should return the value for the slug key as false if the conditions are not met.
            $init = apply_filters( 'optinmonster_output', $init ); // Deprecated. DO NOT USE!
            $init = apply_filters( 'optin_monster_output', $init, $optin, $meta, $post_id );
        }

        // If the init code is empty, do nothing.
        if ( empty( $init ) ) {
            return;
        }

        // Load the optins.
        foreach ( (array) $init as $optin ) {
            if ( $optin ) {
                echo $optin;
            }
        }

    }

    /**
     * Loads an optin in Preview mode.
     *
     * @since 2.0.0
     */
    public function load_preview() {

        // Filter the data for preview mode.
        add_filter( 'optin_monster_data', array( $this, 'preview_data' ), 9999 );
        $optin = $this->get_optin_monster( $this->optin_id );
        remove_filter( 'optin_monster_data', array( $this, 'preview_data' ), 9999 );

        // Output the optin.
        if ( $optin ) {
            echo $optin;
        }

    }

    /**
     * Overrides some optin data to force it into preview mode.
     *
     * @since 2.0.0
     *
     * @param array $data Array of optin data.
     *
     * @return array $data Amended array of optin data.
     */
    public function preview_data( $data ) {

        // Force some settings specific to preview mode.
        $data['test']          = true;
        $data['delay']         = 0;
        $data['cookie']        = 0;
        $data['exit']          = false;
        $data['second']        = false;
        $data['global_cookie'] = false;
        $data['mobile']        = false;
        $data['preview']	   = true;

		// Return the amended data.
        return $data;

    }

    /**
     * Retrieves the optin HTML output.
     *
     * @since 2.0.0
     *
     * @param int $optin_id The optin ID.
     * @param bool $click   Flag for determining if this was loaded via click or page load.
     * @return bool|string  False on failure, otherwise the HTML output for the page.
     */
    public function get_optin_monster( $optin_id, $click = false ) {

        // Return false if the optin already exists on the page and was not retrieved via click.
        if ( ! empty( $this->data[$optin_id] ) && ! $click ) {
        	return false;
        }

        // Return false if no optin data can be retrieved.
        $this->data[$optin_id] = $this->get_optin_monster_data( $optin_id );
        if ( ! $this->data[$optin_id] ) {
            return false;
        }

        // Build the optin output.
        $output = '<!-- This site converts visitors into subscribers and customers with the OptinMonster WordPress plugin v' . $this->base->version . ' - http://optinmonster.com/ -->' . "\n";
        $output .= '<div id="om-' . $this->data[$optin_id]['optin'] . '" class="optin-monster-overlay" style="' . ( 'sidebar' !== $this->data[$optin_id]['type'] && 'post' !== $this->data[$optin_id]['type'] ? 'display:none;' : '' ) . '">';
        $output .= $this->get_optin_monster_theme( $this->data[$optin_id]['theme'], $this->data[$optin_id]['id'] );
        $output .= '</div>' . "\n";

        // If this was not loaded via click, load our JS to load the optin.
        if ( ! $click ) {
            $output .= '<script type="text/javascript">';
            $output .= 'var ' . $this->data[$optin_id]['optin_js'] . ', omo = ' . json_encode( $this->data[$optin_id] ) . '; ' . $this->data[$optin_id]['optin_js'] . ' = new OptinMonster(); ' . $this->data[$optin_id]['optin_js'] . '.init(omo);';
            $output .= '</script>' . "\n";
        }

        // Load the conditional IE polyfill.
        $output .= $this->get_ie_polyfill();
        $output .= '<!-- / OptinMonster WordPress plugin. -->' . "\n";

        // Return the output and allow it to be filtered.
        return apply_filters( 'optin_monster_optin_output', $output, $optin_id );

    }

    /**
     * Retrieves all the necessary data about an optin for loading.
     *
     * @since 2.0.0
     *
     * @param int $optin_id The optin ID.
     *
     * @return bool|array   False on failure, otherwise an array of optin data.
     */
    public function get_optin_monster_data( $optin_id ) {

        // Return false if the optin has already been set in the data.
        if ( ! empty( $this->data[$optin_id] ) ) {
            return false;
        }

        // Return false if the optin cannot be found.
        $optin = Optin_Monster::get_instance()->get_optin( $optin_id );
        if ( ! $optin ) {
            // Attempt to retrieve by slug if necessary.
            $optin = Optin_Monster::get_instance()->get_optin_by_slug( $optin_id );
            if ( ! $optin ) {
                return false;
            }
        }

        // Prepare variables.
        $meta   = get_post_meta( $optin->ID, '_om_meta', true );
        $option = get_option( 'optin_monster' );
        $test   = get_post_meta( $optin->ID, '_om_test_mode', true );
        $data   = array();

        // Attempt to retrieve the post ID.
        global $post;
        $post_id = get_queried_object_id();
        if ( ! $post_id && 'page' == get_option( 'show_on_front' ) ) {
            $post_id = get_option( 'page_for_posts' );
        } else {
            $post_id = isset( $post ) ? $post->ID : 0;
        }

        // Prepare the clones variable based on type of optin being output.
        $is_clone = get_post_meta( $optin->ID, '_om_is_clone', true );
        if ( ! empty( $is_clone ) ) {
            $clones   = (array) get_post_meta( $is_clone, '_om_has_clone', true );
            $clones[] = (int) $is_clone;
            foreach ( $clones as $i => $clone ) {
                if ( $optin->ID == $clone ) {
                    unset( $clones[$i] );
                    break;
                }
            }
            $clones = array_values( $clones );
        } else {
            $clones           = (array) get_post_meta( $optin->ID, '_om_has_clone', true );
            $sanitized_clones = array();
            foreach ( $clones as $i => $clone ) {
                $sanitized_clones[$i] = $clone;
            }
            $clones = array_values( $sanitized_clones );
        }

        // Build the data array.
        $data['id']            = $optin->ID;
        $data['optin']         = $optin->post_name;
        $data['campaign']      = get_the_title( $optin->ID );
        $data['clones']        = $clones;
        $data['hash']          = $optin->post_name;
        $data['optin_js']      = str_replace( '-', '_', $optin->post_name );
        $data['type']          = isset( $meta['type'] ) ? $meta['type'] : false;
        $data['theme']         = ! empty( $_GET['om-live-theme'] ) ? stripslashes( $_GET['om-live-theme'] ) : $meta['theme'];
        $data['cookie']        = isset( $meta['cookie'] ) ? $meta['cookie'] : 30;
        $data['delay']         = isset( $meta['delay'] ) ? $meta['delay'] : 5000;
        $data['second']        = isset( $meta['second'] ) ? $meta['second'] : false;
        $data['exit']          = isset( $meta['exit'] ) ? $meta['exit'] : false;
        $data['redirect']      = isset( $meta['redirect'] ) ? esc_url( $meta['redirect'] ) : false;
        $data['redirect_pass'] = isset( $meta['redirect_pass'] ) && $meta['redirect_pass'] ? true : false;
        $data['custom']        = isset( $meta['email']['provider'] ) && 'custom' == $meta['email']['provider'] ? true : false;
        $data['test']          = ! empty( $test ) ? true : false;
        $data['global_cookie'] = isset( $option['cookie'] ) ? $option['cookie'] : false;
        $data['preview']       = $this->preview;
        $data['ajax']          = $this->get_ajax_route();
        $data['mobile']        = isset( $meta['type'] ) && 'mobile' == $meta['type'] ? true : false;
        $data['post_id']       = $post_id;
        $data['preloader']     = plugins_url( 'assets/css/images/preloader.gif', $this->base->file );
        $data['error']         = __( 'There was an error with your submission. Please try again.', 'optin-monster' );
        $data['ajax_error']    = __( 'There was an error with the AJAX request: ', 'optin-monster' );
        $data['name_error']    = __( 'Please enter a valid name.', 'optin-monster' );
        $data['email_error']   = __( 'Please enter a valid email address.', 'optin-monster' );

        // Get the success message and allow for custom data.
        $data['success']       = $this->get_success_message( $optin, $meta );

        // Deprecated filter - DO NOT USE.
        $data = apply_filters( 'optin_monster_load_optinmonster_bottom', $data );

        // Return and allow the data to be filtered.
        return apply_filters( 'optin_monster_data', $data, $optin->ID, $meta );

    }

    /**
     * Retrieves the custom success message with hooks and filter to add custom content.
     *
     * @since 2.0.0
     *
     * @param object $optin The optin object.
     * @param array $meta   The optin meta.
     * @return string       The content for the success message.
     */
    public function get_success_message( $optin, $meta ) {

        $message = '';
        ob_start();
        do_action( 'optin_monster_success_message', $optin, $meta );
        $custom = ob_get_clean();

        // Add the message if it exists.
        if ( ! empty( $meta['success'] ) ) {
            $message .= $meta['success'];
        }

        // Add the custom content.
        $message .= $custom;

        // Return the message or false if no message is found.
        return ! empty( $message ) ? $message : false;

    }

    /**
     * Retrieves the custom ajax route for all frontend OptinMonster actions.
     *
     * @since 2.0.0
     *
     * @return string The URL for the custom ajax route.
     */
    public function get_ajax_route() {

        $route = add_query_arg( 'optin-monster-ajax-route', true, trailingslashit( get_home_url() ) );
        return apply_filters( 'optin_monster_ajax_route', $route );

    }

    /**
     * Retrieves the HTML optin theme output for the ID requested.
     *
     * @since 2.0.0
     *
     * @param string $theme    The theme to retrieve.
     * @param int    $optin_id The optin ID.
     * @param bool   $api_only Flag to return only the API instance or not.
     *
     * @return string          HTML output of the theme.
     */
    public function get_optin_monster_theme( $theme, $optin_id, $api_only = false ) {

        // Prepare variables.
        $output = '';
        $api    = false;
        $meta   = get_post_meta( $optin_id, '_om_meta', true );
        $theme  = ! empty( $_GET['om-live-theme'] ) ? stripslashes( $_GET['om-live-theme'] ) : $theme;

        // Retrieve the theme API object.
        switch ( $theme ) {
            case 'balance' :
                if ( ! class_exists( 'Optin_Monster_Theme_Balance' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/balance/balance.php';
                }
                $api = new Optin_Monster_Theme_Balance( $optin_id );
                break;
            case 'bullseye' :
                if ( ! class_exists( 'Optin_Monster_Theme_Bullseye' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/bullseye/bullseye.php';
                }
                $api = new Optin_Monster_Theme_Bullseye( $optin_id );
                break;
            case 'case-study' :
                if ( ! class_exists( 'Optin_Monster_Theme_Case_Study' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/case-study/case-study.php';
                }
                $api = new Optin_Monster_Theme_Case_Study( $optin_id );
                break;
            case 'chalkboard' :
                if ( ! class_exists( 'Optin_Monster_Theme_Chalkboard' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/chalkboard/chalkboard.php';
                }
                $api = new Optin_Monster_Theme_Chalkboard( $optin_id );
                break;
            case 'clean-slate' :
                if ( ! class_exists( 'Optin_Monster_Theme_Clean_Slate' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/clean-slate/clean-slate.php';
                }
                $api = new Optin_Monster_Theme_Clean_Slate( $optin_id );
                break;
            case 'postal' :
                if ( ! class_exists( 'Optin_Monster_Theme_Postal' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/postal/postal.php';
                }
                $api = new Optin_Monster_Theme_Postal( $optin_id );
                break;
            case 'simple' :
                if ( ! class_exists( 'Optin_Monster_Theme_Simple' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/simple/simple.php';
                }
                $api = new Optin_Monster_Theme_Simple( $optin_id );
                break;
            case 'target' :
                if ( ! class_exists( 'Optin_Monster_Theme_Target' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/target/target.php';
                }
                $api = new Optin_Monster_Theme_Target( $optin_id );
                break;
            case 'transparent' :
                if ( ! class_exists( 'Optin_Monster_Theme_Transparent' ) ) {
                    require plugin_dir_path( $this->base->file ) . 'includes/themes/transparent/transparent.php';
                }
                $api = new Optin_Monster_Theme_Transparent( $optin_id );
                break;
        }

        // Allow the theme API to be filtered.
        $api = apply_filters( 'optin_monster_theme_api', $api, $theme, $optin_id, $meta['type'] );

        // If no API has been built, return the output with a filter.
        if ( ! $api ) {
            return apply_filters( 'optin_monster_theme_output_error', $api, $theme, $optin_id, $api );
        }

        // If only returning the API, return it now.
        if ( $api_only ) {
            return apply_filters( 'optin_monster_theme_api_only', $api, $theme, $optin_id );
        }

        // Load any fonts if necessary.
        if ( isset( $api->meta['fonts'] ) ) {
            foreach ( (array) $api->meta['fonts'] as $font ) {
                $api->load_font( '\'' . $font . '\'' );
            }
        }

        // Build the theme styles.
        $output .= $api->get_fonts();
        $output .= '<style type="text/css" class="om-theme-' . $api->theme . '-styles">';
        $styles  = apply_filters( 'optin_monster_theme_styles', $api->get_styles(), $theme, $optin_id, $api );
        $output .= $api->minify( $styles );
        $output .= $api->minify( $api->get_global_styles() );
        $output .= '</style>';

        // If we have custom CSS, add it now.
        if ( ! empty( $api->meta['custom_css'] ) ) {
            $output .= '<style type="text/css" class="om-custom-styles">' . $api->minify( html_entity_decode( $api->meta['custom_css'], ENT_QUOTES ) ) . '</style>';
        }

        // Build out the theme HTML.
        $html = apply_filters( 'optin_monster_theme_html', $api->get_html(), $theme, $optin_id, $api );
        $output .= $html;

        // Build out the theme JS.
        $output .= '<script type="text/javascript">';
        $js = apply_filters( 'optin_monster_theme_js', $api->get_js(), $theme, $optin_id, $api );
        $output .= $api->scaffold( $api->minify( $js, 'js' ) );
        $output .= '</script>';

        // Return the theme output and allow it to be filtered.
        return apply_filters( 'optin_monster_theme_output', $output, $theme, $optin_id, $api );

    }

    /**
     * Returns the polyfill for checking for IE 9 or less.
     *
     * @since 2.0.0
     *
     * @return string HTML conditional comment with IE-specific JS object.
     */
    public function get_ie_polyfill() {

        // If we have already loaded the polyfill, return early.
        if ( $this->ie ) {
            return '';
        }

        // Set our flag to true and load our conditional IE polyfill object.
        $this->ie = true;
        return '<!--[if lte IE 9]><script type="text/javascript">var om_ie_browser = true;</script><![endif]-->';

    }

    /**
     * Retrieves an optin meta setting.
     *
     * @since 2.0.0
     *
     * @param string $field   The meta field to retrieve.
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_checkbox_setting( $field, $setting = '', $default = 0 ) {

        if ( empty( $setting ) ) {
            return isset ( $this->meta[ $field ] ) ? $this->meta[ $field ] : $default;
        } else {
            return isset( $this->meta[ $field ][ $setting ] ) ? $this->meta[ $field ][ $setting ] : $default;
        }

    }

    /**
     * Retrieves an optin meta setting.
     *
     * @since 2.0.0
     *
     * @param string $field   The meta field to retrieve.
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_setting( $field, $setting = '', $default = '' ) {

        if ( empty( $setting ) ) {
            return isset( $this->meta[ $field ] ) ? $this->meta[ $field ] : $default;
        } else {
            return isset( $this->meta[ $field ][ $setting ] ) ? $this->meta[ $field ][ $setting ] : $default;
        }

    }

    /**
     * Retrieves an optin meta setting for the display field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_display_setting( $setting, $default = '' ) {

        return isset( $this->meta['display'][ $setting ] ) ? $this->meta['display'][ $setting ] : $default;

    }

    /**
     * Retrieves an optin meta setting for the email field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_email_setting( $setting, $default = '' ) {

        return isset( $this->meta['email'][ $setting ] ) ? $this->meta['email'][ $setting ] : $default;

    }

    /**
     * Retrieves an optin meta setting for the name field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_name_setting( $setting, $default = '' ) {

        return isset( $this->meta['name'][ $setting ] ) ? $this->meta['name'][ $setting ] : $default;

    }

    /**
     * Retrieves an optin meta setting for the background field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_background_setting( $setting, $default = '' ) {

        return isset( $this->meta['background'][ $setting ] ) ? $this->meta['background'][ $setting ] : $default;

    }

    /**
     * Retrieves an optin meta setting for the title field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_title_setting( $setting, $default = '' ) {

        return isset( $this->meta['title'][ $setting ] ) ? $this->meta['title'][ $setting ] : $default;

    }

    /**
     * Retrieves an optin meta setting for the tagline field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_tagline_setting( $setting, $default = '' ) {

        return isset( $this->meta['tagline'][ $setting ] ) ? $this->meta['tagline'][ $setting ] : $default;

    }

    /**
     * Retrieves an optin meta setting for the bullet field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_bullet_setting( $setting, $default = '' ) {

        return isset( $this->meta['bullet'][ $setting ] ) ? $this->meta['bullet'][ $setting ] : $default;

    }

    /**
     * Retrieves an optin meta setting for the submit field.
     *
     * @since 2.0.0
     *
     * @param string $setting The possible subfield for the main field.
     * @param string $default The default if the value is not found.
     *
     * @return string         The setting value or the default if it does not exist.
     */
    public function get_submit_setting( $setting, $default = '' ) {

        return isset( $this->meta['submit'][ $setting ] ) ? $this->meta['submit'][ $setting ] : $default;

    }

    /**
     * Returns the preview state.
     *
     * @since 2.0.0
     *
     * @return bool Preview mode or not.
     */
    public function is_preview() {

        return $this->preview;

    }

    /**
     * Returns the supported theme fonts.
     *
     * @since 2.0.0
     *
     * @return array $fonts Array of supported font families.
     */
    public function get_supported_fonts( $google_only = false ) {

        $base_fonts   = array(
            'Helvetica',
            'Helvetica Neue',
            'Arial',
            'Tahoma',
            'Verdana',
            'Times New Roman',
            'Georgia'
        );
        $google_fonts = array(
            'Droid Sans',
            'Droid Serif',
            'Vollkorn',
            'Lobster',
            'Bree Serif',
            'Playfair Display',
            'Cabin',
            'Cookie',
            'Lora',
            'Ubuntu',
            'Open Sans',
            'Josefin Slab',
            'Arvo',
            'Lato',
            'Abril Fatface',
            'Montserrat',
            'PT Sans',
            'PT Serif',
            'Noto Serif',
            'Libre Baskerville',
            'Oswald',
            'Just Another Hand',
            'Roboto',
            'Roboto Slab'
        );

        if ( $google_only ) {
            $fonts = apply_filters( 'optin_monster_google_fonts', $google_fonts );
        } else {
            $fonts = apply_filters( 'optin_monster_theme_fonts', array_merge( $base_fonts, apply_filters( 'optin_monster_google_fonts', (array) $google_fonts ) ) );
        }

        sort( $fonts );
        return $fonts;

    }

    /**
     * Manually tracks impressions for an optin.
     *
     * @since 2.0.3.2
     *
     * @param int $optin_id  The optin ID to track.
     * @return bool|WP_Error True if successful, WP_Error otherwise.
     */
    public function track_manual( $optin_id ) {

	    // Load the data interfaces.
	    if ( ! interface_exists( 'Optin_Monster_Ajax_Interface' ) ) {
	    	require plugin_dir_path( __FILE__ ) . 'ajax-interface.php';
	    }

	    if ( ! class_exists( 'Optin_Monster_Ajax_Track_Optin' ) ) {
		    require plugin_dir_path( __FILE__ ) . 'ajax-track-optin.php';
	    }

	    if ( ! class_exists( 'Optin_Monster_Track_Datastore' ) ) {
        	require plugin_dir_path( __FILE__ ) . 'track-datastore.php';
        }

        return new Optin_Monster_Ajax_Track_Optin( new Optin_Monster_Track_Datastore( $optin_id ) );

    }

    /**
     * Allows setting of properties.
     *
     * @since 2.0.0
     *
     * @param string $name  The name of the property to set.
     * @param string $value The value of the property.
     */
    public function __set( $name, $value ) {

        $this->{$name} = $value;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.0.0
     *
     * @return object The Optin_Monster_Output object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Optin_Monster_Output ) ) {
            self::$instance = new Optin_Monster_Output();
        }

        return self::$instance;

    }

}

// Load the output class.
$optin_monster_output = Optin_Monster_Output::get_instance();