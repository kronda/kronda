<?php



/**

 * W3 ObjectCache plugin

 */

if (!defined('W3TC')) {

    die();

}



w3_require_once(W3TC_LIB_W3_DIR . '/Plugin.php');



/**

 * Class W3_Plugin_ObjectCache

 */

class W3_Plugin_ObjectCache extends W3_Plugin {

    /**

     * Runs plugin

     */

    function run() {

        add_filter('cron_schedules', array(

            &$this,

            'cron_schedules'

        ));



        if ($this->_config->get_string('objectcache.engine') == 'file') {

            add_action('w3_objectcache_cleanup', array(

                &$this,

                'cleanup'

            ));

        }



        add_action('publish_phone', array(

            &$this,

            'on_change'

        ), 0);



        add_action('wp_trash_post', array(

            &$this,

            'on_change'

        ), 0);



        add_action('save_post', array(

            &$this,

            'on_change'

        ), 0);



        global $wp_version;

        if (version_compare($wp_version,'3.5', '>=')) {

            add_action('clean_post_cache', array(

                &$this,

                'on_change'

            ), 0, 2);

        }



        add_action('comment_post', array(

            &$this,

            'on_change'

        ), 0);



        add_action('edit_comment', array(

            &$this,

            'on_change'

        ), 0);



        add_action('delete_comment', array(

            &$this,

            'on_change'

        ), 0);



        add_action('wp_set_comment_status', array(

            &$this,

            'on_change'

        ), 0);



        add_action('trackback_post', array(

            &$this,

            'on_change'

        ), 0);



        add_action('pingback_post', array(

            &$this,

            'on_change'

        ), 0);



        add_action('switch_theme', array(

            &$this,

            'on_change'

        ), 0);



        add_action('updated_option', array(

            &$this,

            'on_change_option'

        ), 0, 1);

        add_action('added_option', array(

            &$this,

            'on_change_option'

        ), 0, 1);



        add_action('switch_blog', array(

            &$this,

            'switch_blog'

        ), 0, 2);



        add_action('edit_user_profile_update', array(

            &$this,

            'on_change_profile'

        ), 0);



        if (w3_is_multisite()) {

            add_action('delete_blog', array(

                &$this,

                'on_change'

            ), 0);

        }



        add_action('delete_post', array(

            &$this,

            'on_change'

        ), 0);

    }



    /**

     * Activate plugin action (called by W3_Plugins)

     */

    function activate() {

        w3_require_once(W3TC_INC_DIR . '/functions/activation.php');



        try{

            w3_copy_if_not_equal(W3TC_INSTALL_FILE_OBJECT_CACHE, W3TC_ADDIN_FILE_OBJECT_CACHE);

        } catch (Exception $ex){}



        $this->schedule();

    }



    /**

     * Deactivate plugin action (called by W3_Plugins)

     */

    function deactivate() {

        $this->unschedule();

        return null;

    }



    /**

     * Schedules events

     */

    function schedule() {

        if ($this->_config->get_boolean('objectcache.enabled') && $this->_config->get_string('objectcache.engine') == 'file') {

            if (!wp_next_scheduled('w3_objectcache_cleanup')) {

                wp_schedule_event(current_time('timestamp'), 'w3_objectcache_cleanup', 'w3_objectcache_cleanup');

            }

        } else {

            $this->unschedule();

        }

    }



    /**

     * Unschedules events

     */

    function unschedule() {

        if (wp_next_scheduled('w3_objectcache_cleanup')) {

            wp_clear_scheduled_hook('w3_objectcache_cleanup');

        }

    }



    /**

     * Does disk cache cleanup

     *

     * @return void

     */

    function cleanup() {

        w3_require_once(W3TC_LIB_W3_DIR . '/Cache/File/Cleaner.php');



        $w3_cache_file_cleaner = new W3_Cache_File_Cleaner(array(

            'cache_dir' => w3_cache_blog_dir('object'),

            'clean_timelimit' => $this->_config->get_integer('timelimit.cache_gc')

        ));



        $w3_cache_file_cleaner->clean();

    }



    /**

     * Cron schedules filter

     *

     * @param array $schedules

     * @return array

     */

    function cron_schedules($schedules) {

        $gc = $this->_config->get_integer('objectcache.file.gc');



        return array_merge($schedules, array(

            'w3_objectcache_cleanup' => array(

                'interval' => $gc,

                'display' => sprintf('[W3TC] Object Cache file GC (every %d seconds)', $gc)

            )

        ));

    }



    /**

     * Change action

     */

    function on_change($post_id = 0, $post = null) {

        static $flushed = false;



        if (!$flushed) {

            if (is_null($post))

                $post = $post_id;



            if ($post_id> 0 && !w3_is_flushable_post($post, 'objectcache', $this->_config)) {

                return;

            }



            $flush = w3_instance('W3_CacheFlush');

            $flush->objectcache_flush();

            $flushed = true;

        }

    }



    /**

     * Change action

     */

    function on_change_option($option) {

        static $flushed = false;



        if (!$flushed) {

            if ($option != 'cron') {

                $flush = w3_instance('W3_CacheFlush');

                $flush->objectcache_flush();

                $flushed = true;

            }

        }

    }



    /**

     * Flush cache when user profile is updated

     * @param int $user_id

    */

    function on_change_profile($user_id) {

        static $flushed = false;



        if (!$flushed) {

            if(w3_is_multisite()) {

                $blogs = get_blogs_of_user($user_id, true);

                if ($blogs) {

                    global $w3_multisite_blogs;

                    $w3_multisite_blogs = $blogs;

                }

            }



            $flush = w3_instance('W3_CacheFlush');

            $flush->objectcache_flush();



            $flushed = true;

        }

    }



    /**

     * Switch blog action

     */

    function switch_blog($blog_id, $previous_blog_id) {

        $o = w3_instance('W3_ObjectCache');

        $o->switch_blog($blog_id);

    }

}

