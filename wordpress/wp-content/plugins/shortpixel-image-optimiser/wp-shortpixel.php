<?php
/**
 * Plugin Name: ShortPixel Image Optimizer
 * Plugin URI: https://shortpixel.com/
 * Description: ShortPixel optimizes images automatically, while guarding the quality of your images. Check your <a href="options-general.php?page=wp-shortpixel" target="_blank">Settings &gt; ShortPixel</a> page on how to start optimizing your image library and make your website load faster. 
 * Version: 3.0.6
 * Author: ShortPixel
 * Author URI: https://shortpixel.com
 */

require_once('shortpixel_api.php');
require_once('shortpixel_queue.php');
require_once('shortpixel_view.php');
require_once( ABSPATH . 'wp-admin/includes/image.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
if ( !is_plugin_active( 'wpmandrill/wpmandrill.php' ) ) {
  require_once( ABSPATH . 'wp-includes/pluggable.php' );//to avoid conflict with wpmandrill plugin
} 

define('SP_RESET_ON_ACTIVATE', false);

define('PLUGIN_VERSION', "3.0.6");
define('SP_MAX_TIMEOUT', 10);
define('SP_BACKUP', 'ShortpixelBackups');
define('SP_BACKUP_FOLDER', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . SP_BACKUP);
define('MAX_API_RETRIES', 5);
$MAX_EXECUTION_TIME = ini_get('max_execution_time');
if ( is_numeric($MAX_EXECUTION_TIME) )
    define('MAX_EXECUTION_TIME', $MAX_EXECUTION_TIME - 5 );   //in seconds
else
    define('MAX_EXECUTION_TIME', 25 );
define("SP_MAX_RESULTS_QUERY", 6);    

class WPShortPixel {
    
    const BULK_EMPTY_QUEUE = 0;

    private $_apiKey = '';
    private $_compressionType = 1;
    private $_processThumbnails = 1;
    private $_CMYKtoRGBconversion = 1;
    private $_backupImages = 1;
    private $_verifiedKey = false;
    
    private $_apiInterface = null;
    private $prioQ = null;
    private $view = null;

    //handling older
    public function WPShortPixel() {
        $this->__construct();
    }

    public function __construct() {
        if(!is_admin()) {
            return;
        }
        if (!session_id()) {
            session_start();
        }
        $this->populateOptions();

        $this->_apiInterface = new ShortPixelAPI($this->_apiKey, $this->_compressionType, $this->_CMYKtoRGBconversion);
        $this->prioQ = new ShortPixelQueue($this);
        $this->view = new ShortPixelView($this);
        
        define('QUOTA_EXCEEDED', "Quota Exceeded. <a href='https://shortpixel.com/login/".$this->_apiKey."' target='_blank'>Extend Quota</a>");        
            
        $this->setDefaultViewModeList();//set default mode as list. only @ first run

        //add hook for image upload processing
        add_filter( 'wp_generate_attachment_metadata', array( &$this, 'handleImageUpload' ), 10, 2 );
        add_filter( 'manage_media_columns', array( &$this, 'columns' ) );//add media library column header
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'generatePluginLinks'));//for plugin settings page

        //add_action( 'admin_footer', array(&$this, 'handleImageProcessing'));
        add_action( 'manage_media_custom_column', array( &$this, 'generateCustomColumn' ), 10, 2 );//generate the media library column

        //add settings page
        add_action( 'admin_menu', array( &$this, 'registerSettingsPage' ) );//display SP in Settings menu
        add_action( 'admin_menu', array( &$this, 'registerAdminPage' ) );
        add_action( 'delete_attachment', array( &$this, 'handleDeleteAttachmentInBackup' ) );
        add_action( 'load-upload.php', array( &$this, 'handleCustomBulk'));
        
        //when plugin is activated run this 
        register_activation_hook( __FILE__, array( &$this, 'shortPixelActivatePlugin' ) );
        register_deactivation_hook( __FILE__, array( &$this, 'shortPixelDeactivatePlugin' ) );

        //automatic optimization
        add_action( 'wp_ajax_shortpixel_image_processing', array( &$this, 'handleImageProcessing') );
        //manual optimization
        add_action( 'wp_ajax_shortpixel_manual_optimization', array(&$this, 'handleManualOptimization'));
        //backup restore
        add_action('admin_action_shortpixel_restore_backup', array(&$this, 'handleRestoreBackup'));
        
        //This adds the constants used in PHP to be available also in JS
        add_action( 'admin_footer', array( &$this, 'shortPixelJS') );


        //example toolbar by fai, to be configured
        add_action( 'admin_bar_menu', array( &$this, 'toolbar_shortpixel_processing'), 999 );

        $this->migrateBackupFolder();
    }

    public function populateOptions() {

        $this->_apiKey = self::getOpt('wp-short-pixel-apiKey', '');
        $this->_verifiedKey = self::getOpt('wp-short-pixel-verifiedKey', $this->_verifiedKey);
        $this->_compressionType = self::getOpt('wp-short-pixel-compression', $this->_compressionType);
        $this->_processThumbnails = self::getOpt('wp-short-process_thumbnails', $this->_processThumbnails);
        $this->_CMYKtoRGBconversion = self::getOpt('wp-short-pixel_cmyk2rgb', $this->_CMYKtoRGBconversion);
        $this->_backupImages = self::getOpt('wp-short-backup_images', $this->_backupImages);
        // the following practically set defaults for options if they're not set
        self::getOpt( 'wp-short-pixel-fileCount', 0);
        self::getOpt( 'wp-short-pixel-thumbnail-count', 0);//amount of optimized thumbnails               
        self::getOpt( 'wp-short-pixel-files-under-5-percent', 0);//amount of optimized thumbnails                       
        self::getOpt( 'wp-short-pixel-savedSpace', 0);
        self::getOpt( 'wp-short-pixel-api-retries', 0);//sometimes we need to retry processing/downloading a file multiple times
        self::getOpt( 'wp-short-pixel-quota-exceeded', 0);
        self::getOpt( 'wp-short-pixel-total-original', 0);//amount of original data
        self::getOpt( 'wp-short-pixel-total-optimized', 0);//amount of optimized
        self::getOpt( 'wp-short-pixel-protocol', 'https');
    }
    
    public function shortPixelActivatePlugin()//reset some params to avoid trouble for plugins that were activated/deactivated/activated
    {
        $this->prioQ->resetBulk();
        if(SP_RESET_ON_ACTIVATE === true && WP_DEBUG === true) { //force reset plugin counters, only on specific occasions and on test environments
            update_option( 'wp-short-pixel-fileCount', 0);
            update_option( 'wp-short-pixel-thumbnail-count', 0);
            update_option( 'wp-short-pixel-files-under-5-percent', 0);
            update_option( 'wp-short-pixel-savedSpace', 0);
            update_option( 'wp-short-pixel-api-retries', 0);//sometimes we need to retry processing/downloading a file multiple times
            update_option( 'wp-short-pixel-quota-exceeded', 0);
            update_option( 'wp-short-pixel-total-original', 0);//amount of original data
            update_option( 'wp-short-pixel-total-optimized', 0);//amount of optimized                
            update_option( 'wp-short-pixel-bulk-ever-ran', 0);
            delete_option('wp-short-pixel-priorityQueue');
            unset($_SESSION["wp-short-pixel-priorityQueue"]);
            delete_option("wp-short-pixel-bulk-previous-percent");
        }
    }
    
    public function shortPixelDeactivatePlugin()//reset some params to avoid trouble for plugins that were activated/deactivated/activated
    {
        $this->prioQ->resetBulk();
    }    
    
    //set default move as "list". only set once, it won't try to set the default mode again.
    public function setDefaultViewModeList() 
    {
        if(get_option('wp-short-pixel-view-mode') === false) 
        {
            add_option('wp-short-pixel-view-mode', 1, '', 'yes' );
            if ( function_exists('get_currentuserinfo') )
                {
                    global $current_user;
                    get_currentuserinfo();
                    $currentUserID = $current_user->ID;
                    update_user_meta($currentUserID, "wp_media_library_mode", "list");
                }
        }
        
    }

    static function log($message) {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
   
    function shortPixelJS() { ?> 
        <script type="text/javascript" >
            jQuery(document).ready(function($){
                ShortPixel.setOptions({
                    STATUS_SUCCESS: <?= ShortPixelAPI::STATUS_SUCCESS ?>,
                    STATUS_EMPTY_QUEUE: <?= self::BULK_EMPTY_QUEUE ?>,
                    STATUS_ERROR: <?= ShortPixelAPI::STATUS_ERROR ?>,
                    STATUS_FAIL: <?= ShortPixelAPI::STATUS_FAIL ?>,
                    STATUS_SKIP: <?= ShortPixelAPI::STATUS_SKIP ?>,
                    STATUS_QUOTA_EXCEEDED: <?= ShortPixelAPI::STATUS_QUOTA_EXCEEDED ?>,
                    WP_PLUGIN_URL: '<?= WP_PLUGIN_URL ?>',
                    API_KEY: "<?= $this->_apiKey ?>"
                });
            });
        </script> <?php
        wp_enqueue_style('short-pixel.css', plugins_url('/css/short-pixel.css',__FILE__) );
    }

    function toolbar_shortpixel_processing( $wp_admin_bar ) {
        wp_enqueue_script('short-pixel.js', plugins_url('/js/short-pixel.js',__FILE__) );
        
        $extraClasses = " shortpixel-hide";
        $tooltip = "ShortPixel optimizing...";
        $icon = "shortpixel.png";
        $link = current_user_can( 'edit_others_posts')? 'upload.php?page=wp-short-pixel-bulk' : 'upload.php';
        $blank = "";
        if($this->prioQ->processing()) {
            $extraClasses = " shortpixel-processing";
        }
        self::log("TOOLBAR: Quota exceeded: " . self::getOpt( 'wp-short-pixel-quota-exceeded', 0));
        if(self::getOpt( 'wp-short-pixel-quota-exceeded', 0)) {
            $extraClasses = " shortpixel-alert shortpixel-quota-exceeded";
            $tooltip = "ShortPixel quota exceeded. Click to top-up";
            $link = "http://shortpixel.com/login/" . $this->_apiKey;
            $blank = '_blank';
            //$icon = "shortpixel-alert.png";
        }
        self::log("TB: Start:  " . $this->prioQ->getStartBulkId() . ", stop: " . $this->prioQ->getStopBulkId() . " PrioQ: "
                 .json_encode($this->prioQ->get()));

        $args = array(
                'id'    => 'shortpixel_processing',
                'title' => '<div title="' . $tooltip . '" ><img src="' 
                         . WP_PLUGIN_URL . '/shortpixel-image-optimiser/img/' . $icon . '"><span class="shp-alert">!</span></div>',
                'href'  => $link,
                'meta'  => array('target'=> $blank, 'class' => 'shortpixel-toolbar-processing' . $extraClasses)
        );
        $wp_admin_bar->add_node( $args );
    }

    public static function getOpt($key, $default) {
        if(get_option($key) === false) {
            add_option( $key, $default, '', 'yes' );
        }
        return get_option($key);
    }

    public function handleCustomBulk() {
        // 1. get the action
        $wp_list_table = _get_list_table('WP_Media_List_Table');
        $action = $wp_list_table->current_action();

        switch($action) {
          // 2. Perform the action
            case 'short-pixel-bulk':
                // security check
                check_admin_referer('bulk-media');
                if(!is_array($_GET['media'])) {
                    break;
                }
                $mediaIds = array_reverse($_GET['media']);
                foreach( $mediaIds as $ID ) {
                    $meta = wp_get_attachment_metadata($ID);
                    if(   (!isset($meta['ShortPixel']) || !isset($meta['ShortPixel']['WaitingProcessing']) || $meta['ShortPixel']['WaitingProcessing'] != true) 
                       && (!isset($meta['ShortPixelImprovement']) || $meta['ShortPixelImprovement'] != 'Optimization N/A')) {
                        $this->prioQ->push($ID);
                        $meta['ShortPixel']['WaitingProcessing'] = true;
                        wp_update_attachment_metadata($ID, $meta);
                    }
                }
                break;
        }
    }

    public function handleImageUpload($meta, $ID = null)
    {
            if( !$this->_verifiedKey) {// no API Key set/verified -> do nothing here, just return
                return $meta;
            }
            //else
            self::log("IMG: Auto-analyzing file ID #{$ID}");

            if( self::isProcessable($ID) == false ) 
            {//not a file that we can process
                $meta['ShortPixelImprovement'] = 'Optimization N/A';
                return $meta;
            }
            else 
            {//the kind of file we can process. goody.
                $this->prioQ->push($ID);
                $URLsAndPATHs = $this->getURLsAndPATHs($ID, $meta);                
                $this->_apiInterface->doRequests($URLsAndPATHs['URLs'], false, $ID);//send a processing request right after a file was uploaded, do NOT wait for response   
                self::log("IMG: sent: " . json_encode($URLsAndPATHs));
                $meta['ShortPixel']['WaitingProcessing'] = true;
                return $meta;
            } 
            
    }//end handleImageUpload

    public function getCurrentBulkItemsCount(){
        global $wpdb;
        
        $startQueryID = $this->prioQ->getFlagBulkId();
        $endQueryID = $this->prioQ->getStopBulkId(); 
        
        if ( $startQueryID <= $endQueryID ) {
            return 0;
        }
        $queryPostMeta = "SELECT COUNT(DISTINCT post_id) items FROM " . $wpdb->prefix . "postmeta 
            WHERE ( post_id <= $startQueryID AND post_id > $endQueryID ) AND (
                    meta_key = '_wp_attached_file'
                    OR meta_key = '_wp_attachment_metadata' )";
        $res = $wpdb->get_results($queryPostMeta);
        return $res[0]->items;
    }
    
    public function getBulkItemsFromDb(){
        global $wpdb;
        
        $startQueryID = $this->prioQ->getStartBulkId();
        $endQueryID = $this->prioQ->getStopBulkId(); 
        $skippedAlreadyProcessed = 0;
        
        if ( $startQueryID <= $endQueryID ) {
            return false;
        }
        $idList = array();
        for ($sanityCheck = 0, $crtStartQueryID = $startQueryID;  
             $crtStartQueryID > $endQueryID && count($idList) < 3; $sanityCheck++) {
 
            self::log("GETDB: current StartID: " . $crtStartQueryID);

            $queryPostMeta = "SELECT * FROM " . $wpdb->prefix . "postmeta 
                WHERE ( post_id <= $crtStartQueryID AND post_id > $endQueryID ) 
                  AND ( meta_key = '_wp_attached_file' OR meta_key = '_wp_attachment_metadata' )
                ORDER BY post_id DESC
                LIMIT " . SP_MAX_RESULTS_QUERY;
            $resultsPostMeta = $wpdb->get_results($queryPostMeta);

            if ( empty($resultsPostMeta) ) {
                $crtStartQueryID -= SP_MAX_RESULTS_QUERY;
                continue;
            }

            foreach ( $resultsPostMeta as $itemMetaData ) {
                $crtStartQueryID = $itemMetaData->post_id;
                if(!in_array($crtStartQueryID, $idList) && self::isProcessable($crtStartQueryID)) {
                    $meta = wp_get_attachment_metadata($crtStartQueryID);
                    if(!isset($meta["ShortPixelImprovement"]) || !is_numeric($meta["ShortPixelImprovement"])) {
                        $idList[] = $crtStartQueryID;
                    } elseif($itemMetaData->meta_key == '_wp_attachment_metadata') { //count skipped
                        $skippedAlreadyProcessed++;
                    }
                }
            }
            if(!count($idList) && $crtStartQueryID <= $startQueryID) {
                //daca n-am adaugat niciuna pana acum, n-are sens sa mai selectez zona asta de id-uri in bulk-ul asta.
                $leapStart = $this->prioQ->getStartBulkId();
                $crtStartQueryID = $startQueryID = $itemMetaData->post_id - 1; //decrement it so we don't select it again
                $res = self::countAllProcessedFiles($leapStart, $crtStartQueryID);
                $skippedAlreadyProcessed += $res["mainFiles"]; 
                $this->prioQ->setStartBulkId($startQueryID);
            } else {
                $crtStartQueryID--;
            }
        }
        return array("ids" => $idList, "skipped" => $skippedAlreadyProcessed);
    }

    /**
     * Get last added items from priority
     * @return type
     */
    public function getFromPrioAndCheck() {
        $ids = array();
        $removeIds = array();
        
        $idsPrio = $this->prioQ->get();
        for($i = count($idsPrio) - 1, $cnt = 0; $i>=0 && $cnt < 3; $i--) {
            $id = $idsPrio[$i];
            if(wp_get_attachment_url($id)) {
                $ids[] = $id; //valid ID
            } else {
                $removeIds[] = $id;//absent, to remove
            }
        }
        foreach($removeIds as $rId){
            self::log("HIP: Unfound ID $rID Remove from Priority Queue: ".json_encode(get_option($this->prioQ->get())));
            $this->prioQ->remove($rId);
        }
        return $ids;
    }

    public function handleImageProcessing($ID = null) {
        //die("bau");
        //0: check key
        if( $this->_verifiedKey == false) {
            echo "Missing API Key";
            die("Missing API Key");
        }
        
        self::log("HIP: 0 Priority Queue: ".json_encode($this->prioQ->get()));
        
        //1: get 3 ids to process. Take them with priority from the queue
        $ids = $this->getFromPrioAndCheck();
        if(count($ids) < 3 ) { //take from bulk if bulk processing active
            $bulkStatus = $this->prioQ->bulkRunning();
            if($bulkStatus =='running') {
                $res = $this->getBulkItemsFromDb();
                $bulkItems = $res['ids'];
                if($bulkItems){
                    $ids = array_merge ($ids, $bulkItems);
                }
            }
        }
        if ($ids === false || count( $ids ) == 0 ){
            $bulkEverRan = $this->prioQ->stopBulk();
            $avg = self::getAverageCompression();
            $fileCount = get_option('wp-short-pixel-fileCount');
            die(json_encode(array("Status" => self::BULK_EMPTY_QUEUE, 
                "Message" => 'Empty queue ' . $this->prioQ->getStartBulkId() . '->' . $this->prioQ->getStopBulkId(),
                "BulkStatus" => ($this->prioQ->bulkRunning() 
                        ? "1" : ($this->prioQ->bulkPaused() ? "2" : "0")),
                "AverageCompression" => $avg,
                "FileCount" => $fileCount,
                "BulkPercent" => $this->prioQ->getBulkPercent())));
        }

        self::log("HIP: 1 Prio Queue: ".json_encode($this->prioQ->get()));

        //2: Send up to 3 files to the server for processing
        for($i = 0; $i < min(3, count($ids)); $i++) {
            $ID = $ids[$i];
            $URLsAndPATHs = $this->sendToProcessing($ID);
            if($i == 0) { //save for later use
                $firstUrlAndPaths = $URLsAndPATHs;
            }
        }
        
        self::log("HIP: 2 Prio Queue: ".json_encode($this->prioQ->get()));

        //3: Retrieve the file for the first element of the list
        $ID = $ids[0];
        $result = $this->_apiInterface->processImage($firstUrlAndPaths['URLs'], $firstUrlAndPaths['PATHs'], $ID);
        $result["ImageID"] = $ID;

        self::log("HIP: 3 Prio Queue: ".json_encode($this->prioQ->get()));

        //4: update counters and priority list
        if( $result["Status"] == ShortPixelAPI::STATUS_SUCCESS) {
            self::log("HIP: Image ID $ID optimized successfully: ".json_encode($result));
            $prio = $this->prioQ->remove($ID);
            if(!$prio && $ID <= $this->prioQ->getStartBulkId()) {
                $this->prioQ->setStartBulkId($ID - 1);
                $this->prioQ->logBulkProgress();
                
                $deltaBulkPercent = $this->prioQ->getDeltaBulkPercent(); 
                $msg = $this->bulkProgressMessage($deltaBulkPercent, $this->prioQ->getTimeRemaining());
                $result["BulkPercent"] = $this->prioQ->getBulkPercent();;
                $result["BulkMsg"] = $msg;
                
                $thumb = $bkThumb = "";
                $percent = 0;
                $meta = wp_get_attachment_metadata($ID);
                if(isset($meta["ShortPixelImprovement"]) && isset($meta["file"])){
                    $percent = $meta["ShortPixelImprovement"];

                    $filePath = explode("/", $meta["file"]);
                    $uploadsUrl = content_url() . "/uploads/";
                    $urlPath = implode("/", array_slice($filePath, 0, count($filePath) - 1));
                    $thumb = (isset($meta["sizes"]["medium"]) ? $meta["sizes"]["medium"]["file"] : (isset($meta["sizes"]["thumbnail"]) ? $meta["sizes"]["thumbnail"]["file"]: ""));
                    if(strlen($thumb) && get_option('wp-short-backup_images') && $this->_processThumbnails) {
                        $bkThumb = $uploadsUrl . SP_BACKUP . "/" . $urlPath . "/" . $thumb;
                    }
                    if(strlen($thumb)) {
                        $thumb = $uploadsUrl . $urlPath . "/" . $thumb;
                    }
                    $result["Thumb"] = $thumb;
                    $result["BkThumb"] = $bkThumb;
                }
            }
        }
        elseif ($result["Status"] == ShortPixelAPI::STATUS_SKIP
             || $result["Status"] == ShortPixelAPI::STATUS_FAIL) {
            $prio = $this->prioQ->remove($ID);
            if(!$prio && $ID <= $this->prioQ->getStartBulkId()) {
                $this->prioQ->setStartBulkId($ID - 1);
            }                
        }
       die(json_encode($result));
    }
    
    private function sendToProcessing($ID) {
        $URLsAndPATHs = $this->getURLsAndPATHs($ID);
        $this->_apiInterface->doRequests($URLsAndPATHs['URLs'], false, $ID);//send a request, do NOT wait for response
        $meta = wp_get_attachment_metadata($ID);
        $meta['ShortPixel']['WaitingProcessing'] = true;
        wp_update_attachment_metadata($ID, $meta);
        return $URLsAndPATHs;
    }

    public function handleManualOptimization() {
        $imageId = intval($_GET['image_id']);
        
        if(self::isProcessable($imageId)) {
            $this->prioQ->push($imageId);
            $this->sendToProcessing($imageId);
            $ret = array("Status" => ShortPixelAPI::STATUS_SUCCESS, "message" => "");
        } else {
            die(var_dump($pathParts));            
        }
        //TODO curata functia asta
        die(json_encode($ret));

        $urlList[] = wp_get_attachment_url($attachmentID);
        $filePath[] = get_attached_file($attachmentID);
        $meta = wp_get_attachment_metadata($attachmentID);

        $processThumbnails = get_option('wp-short-process_thumbnails');

        //process all files (including thumbs)
        if($processThumbnails && !empty($meta['sizes'])) {
            //we generate an array with the URLs that need to be handled
            $SubDir = $this->_apiInterface->returnSubDir($meta['file']);
            foreach($meta['sizes'] as $thumbnailInfo) 
            {
                $urlList[]= str_replace(ShortPixelAPI::MB_basename($filePath[0]), $thumbnailInfo['file'], $urlList[0]);
                $filePath[] = str_replace(ShortPixelAPI::MB_basename($filePath[0]), $thumbnailInfo['file'], $filePath[0]);
            }
        }

        $result = $this->_apiInterface->processImage($urlList, $filePath, $attachmentID);//request to process all the images

        if ( !is_array($result) )//there was an error, we save it in ShortPixelImprovement data
            $this->handleError($attachmentID, $result);

        // store the referring webpage location
        $sendback = wp_get_referer();
        // sanitize the referring webpage location
        $sendback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $sendback);
        // send the user back where they came from
        wp_redirect($sendback);
        // we are done,
    }
    
    //save error in file's meta data
    public function handleError($ID, $result)
    {
        $meta = wp_get_attachment_metadata($ID);
        $meta['ShortPixelImprovement'] = $result;
        wp_update_attachment_metadata($ID, $meta);
    }

    public function handleRestoreBackup() {
        $attachmentID = intval($_GET['attachment_ID']);

        $file = get_attached_file($attachmentID);
        $meta = wp_get_attachment_metadata($attachmentID);
        $pathInfo = pathinfo($file);
    
        $fileExtension = strtolower(substr($file,strrpos($file,".")+1));
        $SubDir = $this->_apiInterface->returnSubDir($file);

        //sometimes the month of original file and backup can differ
        if ( !file_exists(SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . ShortPixelAPI::MB_basename($file)) )
            $SubDir = date("Y") . "/" . date("m") . "/";

        try {
            //main file    
            @rename(SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . ShortPixelAPI::MB_basename($file), $file);

            //overwriting thumbnails
            if( !empty($meta['file']) ) {
                foreach($meta["sizes"] as $size => $imageData) {
                    $source = SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . $imageData['file'];
                    $destination = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $imageData['file'];
                    @rename($source, $destination);
                }
            }
            unset($meta["ShortPixelImprovement"]);
            unset($meta['ShortPixel']['WaitingProcessing']);
            wp_update_attachment_metadata($attachmentID, $meta);

        } catch(Exception $e) {
            //what to do, what to do?
        }
        // store the referring webpage location
        $sendback = wp_get_referer();
        // sanitize the referring webpage location
        $sendback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $sendback);
        // send the user back where they came from
        wp_redirect($sendback);
        // we are done
    }


    public function handleDeleteAttachmentInBackup($ID) {
        $file = get_attached_file($ID);
        $meta = wp_get_attachment_metadata($ID);
        
        if(self::isProcessable($ID) != false) 
        {
            $SubDir = $this->_apiInterface->returnSubDir($file);  
            try {
                    $SubDir = $this->_apiInterface->returnSubDir($file);
                        
                    @unlink(SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . ShortPixelAPI::MB_basename($file));
                    
                    if ( !empty($meta['file']) )
                    {
                        $filesPath =  SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir;//base BACKUP path
                        //remove thumbs thumbnails
                        if(isset($meta["sizes"])) {
                            foreach($meta["sizes"] as $size => $imageData) {
                                @unlink($filesPath . ShortPixelAPI::MB_basename($imageData['file']));//remove thumbs
                            }
                        }
                    }            
                
                } catch(Exception $e) {
                //what to do, what to do?
            }
        }
    }

    public function registerSettingsPage() {
        add_options_page( 'ShortPixel Settings', 'ShortPixel', 'manage_options', 'wp-shortpixel', array($this, 'renderSettingsMenu'));
    }

    function registerAdminPage( ) {
        add_media_page( 'ShortPixel Bulk Process', 'Bulk ShortPixel', 'edit_others_posts', 'wp-short-pixel-bulk', array( &$this, 'bulkProcess' ) );
    }
    
    public function checkQuotaAndAlert() {
        $quotaData = $this->getQuotaInformation();
        if ( !$quotaData['APIKeyValid']) {
            return $quotaData;
        }
        if($quotaData['APICallsQuotaNumeric'] + $quotaData['APICallsQuotaOneTimeNumeric'] > $quotaData['APICallsMadeNumeric'] + $quotaData['APICallsMadeOneTimeNumeric']) {
            update_option('wp-short-pixel-quota-exceeded','0');
            ?><script>var shortPixelQuotaExceeded = 0;</script><?php
        }
        else {    
            $this->view->displayQuotaExceededAlert($quotaData);
            ?><script>var shortPixelQuotaExceeded = 1;</script><?php
        }
        return $quotaData;
    }

    public function bulkProcess() {
        global $wpdb;

        if( $this->_verifiedKey == false ) {//invalid API Key
            $this->view->displayApiKeyAlert();
            return;
        }
        
        $quotaData = $this->checkQuotaAndAlert();
        if(self::getOpt('wp-short-pixel-quota-exceeded', 0) != 0) return;
        
        if(isset($_POST['bulkProcessPause'])) 
        {//pause an ongoing bulk processing, it might be needed sometimes
            $this->prioQ->pauseBulk();
        }

        if(isset($_POST["bulkProcess"])) 
        {
            //set the thumbnails option 
            if ( isset($_POST['thumbnails']) ) {
                update_option('wp-short-process_thumbnails', 1);
            } else {
                update_option('wp-short-process_thumbnails', 0);
            }
            $this->prioQ->startBulk();
            self::log("BULK:  Start:  " . $this->prioQ->getStartBulkId() . ", stop: " . $this->prioQ->getStopBulkId() . " PrioQ: "
                 .json_encode($this->prioQ->get()));
        }//end bulk process  was clicked    
        
        if(isset($_POST["bulkProcessResume"])) 
        {
            $this->prioQ->resumeBulk();
        }//resume was clicked

        //figure out all the files that could be processed
        $qry = "SELECT count(*) FilesToBeProcessed FROM " . $wpdb->prefix . "postmeta
        WHERE meta_key = '_wp_attached_file' ";
        $allFiles = $wpdb->get_results($qry);
        //figure out the files that are left to be processed
        $qry_left = "SELECT count(*) FilesLeftToBeProcessed FROM " . $wpdb->prefix . "postmeta
        WHERE meta_key = '_wp_attached_file' AND post_id <= " . $this->prioQ->getStartBulkId();
        $filesLeft = $wpdb->get_results($qry_left);

        if ( $filesLeft[0]->FilesLeftToBeProcessed > 0 && $this->prioQ->bulkRunning() )//bulk processing was started and is still running
        {
            $msg = $this->bulkProgressMessage($this->prioQ->getDeltaBulkPercent(), $this->prioQ->getTimeRemaining());
            $this->view->displayBulkProcessingRunning($this->prioQ->getBulkPercent(), $msg);

//            $imagesLeft = $filesLeft[0]->FilesLeftToBeProcessed;
//            $totalImages = $allFiles[0]->FilesToBeProcessed;
//            echo "<p>{$imagesLeft} out of {$totalImages} images left to process.</p>";
//            echo ' <a class="button button-secondary" href="' . get_admin_url() .  'upload.php">Media Library</a> ';
        } else 
        {
            if($this->prioQ->bulkRan() && !$this->prioQ->bulkPaused()) {
                $this->prioQ->markBulkComplete();
            }
            
            //image count 
            $imageCount = $this->countAllProcessableFiles();
            $imgProcessedCount = $this->countAllProcessedFiles();
            $imageOnlyThumbs = $imageCount['totalFiles'] - $imageCount['mainFiles'];
            $thumbsProcessedCount = self::getOpt( 'wp-short-pixel-thumbnail-count', 0);//amount of optimized thumbnails
            $under5PercentCount =  self::getOpt( 'wp-short-pixel-files-under-5-percent', 0);//amount of under 5% optimized imgs.

            //average compression
            $averageCompression = self::getAverageCompression();
//            $this->view->displayBulkProcessingForm($imageCount, $imageOnlyThumbs, $this->prioQ->bulkRan(), $averageCompression,
            $this->view->displayBulkProcessingForm($imageCount, $imgProcessedCount, $thumbsProcessedCount, $under5PercentCount,
                    $this->prioQ->bulkRan(), $averageCompression, get_option('wp-short-pixel-fileCount'), 
                    self::formatBytes(get_option('wp-short-pixel-savedSpace')), $this->prioQ->bulkPaused() ? $this->prioQ->getBulkPercent() : false);
        }
    }
    //end bulk processing
    
    public function bulkProgressMessage($percent, $minutes) {
        $timeEst = "";
        self::log("bulkProgressMessage(): percent: " . $percent);
        if($percent < 1 || $minutes == 0) {
            $timeEst = "";
        } elseif( $minutes > 2880) {
            $timeEst = "~ " . round($minutes / 1440) . " days left";
        } elseif ($minutes > 240) {
            $timeEst = "~ " . round($minutes / 60) . " hours left";
        } elseif ($minutes > 60) {
            $timeEst = "~ " . round($minutes / 60) . " hours " . round($minutes%60/10) * 10 . " min. left";
        } elseif ($minutes > 20) {
            $timeEst = "~ " . round($minutes / 10) * 10 . " minutes left";
        } else {
            $timeEst = "~ " . $minutes . " minutes left";
        }
        return $timeEst;
    }
    
    public function emptyBackup(){
            if(file_exists(SP_BACKUP_FOLDER)) {
                
                //extract all images from DB in an array. of course
                $attachments = null;
                $attachments = get_posts( array(
                    'numberposts' => -1,
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image'
                ));
                
            
                //parse all images and set the right flag that the image has no backup
                foreach($attachments as $attachment) 
                {
                    if(self::isProcessable(get_attached_file($attachment->ID)) == false) continue;
                    
                    $meta = wp_get_attachment_metadata($attachment->ID);
                    $meta['ShortPixel']['NoBackup'] = true;
                    wp_update_attachment_metadata($attachment->ID, $meta);
                }

                //delete the actual files on disk
                $this->deleteDir(SP_BACKUP_FOLDER);//call a recursive function to empty files and sub-dirs in backup dir
            }
    }
    
    public function renderSettingsMenu() {
        if ( !current_user_can( 'manage_options' ) )  { 
            wp_die('You do not have sufficient permissions to access this page.');
        }

        $quotaData = $this->checkQuotaAndAlert();

        echo '<h1>ShortPixel Plugin Settings</h1>';
        echo '<p>
                <a href="https://shortpixel.com" target="_blank">ShortPixel.com</a> |
                <a href="https://wordpress.org/plugins/shortpixel-image-optimiser/installation/" target="_blank">Installation </a> |
                <a href="https://shortpixel.com/contact" target="_blank">Support </a>
              </p>';
        if($this->_verifiedKey) {
            echo '<p>New images uploaded to the Media Library will be optimized automatically.<br/>If you have existing images you would like to optimize, you can use the <a href="' . get_admin_url()  . 'upload.php?page=wp-short-pixel-bulk">Bulk Optimization Tool</a>.</p>';
        } else {
            echo '<p>Please enter here the API Key provided by ShortPixel:</p>';
        }

        $noticeHTML = "<br/><div style=\"background-color: #fff; border-left: 4px solid %s; box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1); padding: 1px 12px;\"><p>%s</p></div>";

        //by default we try to fetch the API Key from wp-config.php (if defined)
        if ( !isset($_POST['submit']) && !get_option('wp-short-pixel-verifiedKey') && defined("SHORTPIXEL_API_KEY") && strlen(SHORTPIXEL_API_KEY) == 20 )
        {
            $_POST['validate'] = "validate";
            $_POST['key'] = SHORTPIXEL_API_KEY;        
        }
        
        if(isset($_POST['submit']) || isset($_POST['validate'])) {
            
            //handle API Key - common for submit and validate
            $_POST['key'] = trim(str_replace("*","",$_POST['key']));
            
            if ( strlen($_POST['key']) <> 20 )
            {
                $KeyLength = strlen($_POST['key']);
    
                printf($noticeHTML, '#ff0000', "The key you provided has " .  $KeyLength . " characters. The API key should have 20 characters, letters and numbers only.<BR> <b>Please check that the API key is the same as the one you received in your confirmation email.</b><BR>
                If this problem persists, please contact us at <a href='mailto:help@shortpixel.com?Subject=API Key issues' target='_top'>help@shortpixel.com</a> or <a href='https://shortpixel.com/contact' target='_blank'>here</a>.");
            }
            else
            {
                $validityData = $this->getQuotaInformation($_POST['key'], true);
    
                $this->_apiKey = $_POST['key'];
                $this->_apiInterface->setApiKey($this->_apiKey);
                update_option('wp-short-pixel-apiKey', $_POST['key']);
                if($validityData['APIKeyValid']) {
                    if(isset($_POST['validate'])) {
                        //display notification
                        if(in_array($_SERVER["SERVER_ADDR"], array("127.0.0.1","::1"))) {
                            printf($noticeHTML, '#FFC800', "API Key is valid but your server seems to have a local address. 
                                   Please make sure that your server is accessible from the Internet before using the API or otherwise we won't be able to optimize them.");
                        } else {
                            
                            if ( function_exists("is_multisite") && is_multisite() )
                                printf($noticeHTML, '#7ad03a', "API Key valid! <br>You seem to be running a multisite, please note that API Key can also be configured in wp-config.php like this:<BR> <b>define('SHORTPIXEL_API_KEY', '".$this->_apiKey."');</b>");
                            else
                                printf($noticeHTML, '#7ad03a', 'API Key valid!');
                        }
                    }
                    update_option('wp-short-pixel-verifiedKey', true);
                    $this->_verifiedKey = true;
                    //test that the "uploads"  have the right rights and also we can create the backup dir for ShortPixel
                    if ( !file_exists(SP_BACKUP_FOLDER) && !@mkdir(SP_BACKUP_FOLDER, 0777, true) )
                        printf($noticeHTML, '#ff0000', "There is something preventing us to create a new folder for backing up your original files.<BR>
                        Please make sure that folder <b>" . 
                                        WP_CONTENT_DIR . DIRECTORY_SEPARATOR . "uploads</b> has the necessary write and read rights." );                    
                } else {
                    if(isset($_POST['validate'])) {
                        //display notification
                        printf($noticeHTML, '#ff0000', $validityData["Message"]);
                    }
                    update_option('wp-short-pixel-verifiedKey', false);
                    $this->_verifiedKey = false;
                }
            }


            //if save button - we process the rest of the form elements
            if(isset($_POST['submit'])) {
                update_option('wp-short-pixel-compression', $_POST['compressionType']);
                $this->_compressionType = $_POST['compressionType'];
                $this->_apiInterface->setCompressionType($this->_compressionType);
                if(isset($_POST['thumbnails'])) { $this->_processThumbnails = 1; } else { $this->_processThumbnails = 0; }
                if(isset($_POST['backupImages'])) { $this->_backupImages = 1; } else { $this->_backupImages = 0; }
                if(isset($_POST['cmyk2rgb'])) { $this->_CMYKtoRGBconversion = 1; } else { $this->_CMYKtoRGBconversion = 0; }
                update_option('wp-short-process_thumbnails', $this->_processThumbnails);
                update_option('wp-short-backup_images', $this->_backupImages);
                update_option('wp-short-pixel_cmyk2rgb', $this->_CMYKtoRGBconversion);
            }
        }


        //empty backup
        if(isset($_POST['emptyBackup'])) {
            $this->emptyBackup();
        }

        $checked = '';
        if($this->_processThumbnails) { $checked = 'checked'; }

        $checkedBackupImages = '';
        if($this->_backupImages) { $checkedBackupImages = 'checked'; }
        
        $cmyk2rgb = '';
        if($this->_CMYKtoRGBconversion) { $cmyk2rgb = 'checked'; }
        

        $formHTML = <<< HTML
<form name='wp_shortpixel_options' action=''  method='post' id='wp_shortpixel_options'>
<table class="form-table">
<tbody><tr>
<th scope="row"><label for="key">API Key:</label></th>
<td><input name="key" type="text" id="key" value="{$this->_apiKey}" class="regular-text">
    <input type="submit" name="validate" id="validate" class="button button-primary" title="Validate the provided API key" value="Validate">
</td>
</tr>
HTML;

        if(!$this->_verifiedKey) {

            //if invalid key we display the link to the API Key
            $formHTML .= '<tr><td style="padding-left: 0px;" colspan="2">Don’t have an API Key? <a href="https://shortpixel.com/wp-apikey" target="_blank">Sign up, it’s free.</a></td></tr>';
            $formHTML .= '</form>';
        } else {
            //if valid key we display the rest of the options
            $formHTML .= <<< HTML
<tr><th scope="row">
    <label for="compressionType">Compression type:</label>
</th><td>
HTML;

            if($this->_compressionType == 1) {
                $formHTML .= '<input type="radio" name="compressionType" value="1" checked>Lossy</br></br>';
                $formHTML .= '<input type="radio" name="compressionType" value="0" >Lossless';
            } else {
                $formHTML .= '<input type="radio" name="compressionType" value="1">Lossy</br></br>';
                $formHTML .= '<input type="radio" name="compressionType" value="0" checked>Lossless';
            }

            $formHTML .= <<<HTML
</td>
</tr>
</tbody></table>
<p style="color: #818181;">
<b>Lossy compression: </b>lossy has a better compression rate than lossless compression.</br>The resulting image
is not 100% identical with the original. Works well for photos taken with your camera.</br></br>
<b>Lossless compression: </b> the shrunk image will be identical with the original and smaller in size.</br>Use this
when you do not want to lose any of the original image's details. Works best for technical drawings,
clip art and comics.
</p>
<table class="form-table">
<tbody><tr>
<th scope="row"><label for="thumbnails">Image thumbnails:</label></th>
<td><input name="thumbnails" type="checkbox" id="thumbnails" {$checked}> Apply compression also to image thumbnails.</td>
</tr>
<tr>
<th scope="row"><label for="backupImages">Image backup</label></th>
<td>
<input name="backupImages" type="checkbox" id="backupImages" {$checkedBackupImages}> Save and keep a backup of your original images in a separate folder.
</td>
</tr>
<tr>
<th scope="row"><label for="backupImages">CMYK to RGB conversion</label></th>
<td>
<input name="cmyk2rgb" type="checkbox" id="cmyk2rgb" {$cmyk2rgb}>Adjust your images for computer and mobile screen display.
</td>
</tr>
</tr>
</tbody></table>
<p class="submit">
    <input type="submit" name="submit" id="submit" class="button button-primary" title="Save Changes" value="Save Changes">
    <a class="button button-primary" title="Process all the images in your Media Library" href="upload.php?page=wp-short-pixel-bulk">Bulk Process</a>
</p>
</form>
<script>
var rad = document.wp_shortpixel_options.compressionType;
var prev = null;
for(var i = 0; i < rad.length; i++) {
    rad[i].onclick = function() {

        if(this !== prev) {
            prev = this;
        }
        alert('This type of optimization will apply to new uploaded images. <BR>Images that were already processed will not be re-optimized.');
    };
}
</script>
HTML;
        }

        echo $formHTML;

        if($this->_verifiedKey) {
            $fileCount = number_format(get_option('wp-short-pixel-fileCount'));
            $savedSpace = self::formatBytes(get_option('wp-short-pixel-savedSpace'),2);
            $averageCompression = self::getAverageCompression();
            $savedBandwidth = self::formatBytes(get_option('wp-short-pixel-savedSpace') * 10000,2);
            if (is_numeric($quotaData['APICallsQuota'])) {
                $quotaData['APICallsQuota'] .= "/month";
            }
            $backupFolderSize = self::formatBytes(self::folderSize(SP_BACKUP_FOLDER));
            $remainingImages = $quotaData['APICallsQuotaNumeric'] + $quotaData['APICallsQuotaOneTimeNumeric'] - $quotaData['APICallsMadeNumeric'] - $quotaData['APICallsMadeOneTimeNumeric'];
            $remainingImages = ( $remainingImages < 0 ) ? 0 : number_format($remainingImages);
            $totalCallsMade = number_format($quotaData['APICallsMadeNumeric'] + $quotaData['APICallsMadeOneTimeNumeric']);
            
            $statHTML = <<< HTML
<a id="facts"></a>
<h3>Your ShortPixel Stats</h3>
<table class="form-table">
<tbody>
<tr>
<th scope="row"><label for="averagCompression">Average compression of your files:</label></th>
<td>$averageCompression%</td>
</tr>
<tr>
<th scope="row"><label for="savedSpace">Saved disk space by ShortPixel</label></th>
<td>$savedSpace</td>
</tr>
<tr>
<th scope="row"><label for="savedBandwidth">Bandwith* saved with ShortPixel:</label></th>
<td>$savedBandwidth</td>
</tr>
</tbody></table>

<p style="padding-top: 0px; color: #818181;" >* Saved bandwidth is calculated at 10,000 impressions/image</p>

<h3>Your ShortPixel Plan</h3>
<table class="form-table">
<tbody>
<tr>
<th scope="row" bgcolor="#ffffff"><label for="apiQuota">Your ShortPixel plan</label></th>
<td bgcolor="#ffffff">{$quotaData['APICallsQuota']}/month ( <a href="https://shortpixel.com/login/{$this->_apiKey}" target="_blank">Need More? See the options available</a> )
</tr>
<tr>
<th scope="row"><label for="usedQUota">One time credits:</label></th>
<td>{$quotaData['APICallsQuotaOneTimeNumeric']}</td>
</tr>
<tr>
<th scope="row"><label for="usedQUota">Number of images processed this month:</label></th>
<td>{$totalCallsMade} (<a href="https://api.shortpixel.com/v2/report.php?key={$this->_apiKey}" target="_blank">see report</a>)</td>
</tr>
<tr>
<th scope="row"><label for="remainingImages">Remaining** images in your plan:  </label></th>
<td>{$remainingImages} images</td>
</tr>
</tbody></table>

<p style="padding-top: 0px; color: #818181;" >** Increase your image quota by <a href="https://shortpixel.com/login/{$this->_apiKey}" target="_blank">upgrading</a> your ShortPixel plan.</p>

<table class="form-table">
<tbody>
<tr>
<th scope="row"><label for="totalFiles">Total number of processed files:</label></th>
<td>{$fileCount}</td>
</tr>



HTML;
            if($this->_backupImages) {
                $statHTML .= <<< HTML
<form action="" method="POST">
<tr>
<th scope="row"><label for="sizeBackup">Original images are stored in a backup folder. Your backup folder size is now:</label></th>
<td>
{$backupFolderSize}
<input type="submit"  style="margin-left: 15px; vertical-align: middle;" class="button button-secondary" name="emptyBackup" value="Empty backups"/>
</td>
</tr>
</form>
HTML;
            }

            $statHTML .= <<< HTML
</tbody></table>
HTML;

            echo $statHTML;
        
        
        }
        
    }

    public function getAverageCompression(){
        return get_option('wp-short-pixel-total-optimized') > 0 
               ? round(( 1 -  ( get_option('wp-short-pixel-total-optimized') / get_option('wp-short-pixel-total-original') ) ) * 100, 2) 
               : 0;
    }
    
    public function getQuotaInformation($apiKey = null, $appendUserAgent = false) {
    
        if(is_null($apiKey)) { $apiKey = $this->_apiKey; }

        $requestURL = 'https://api.shortpixel.com/v2/api-status.php';
        $args = array('timeout'=> SP_MAX_TIMEOUT,
            'sslverify'   => false,
            'body' => array('key' => $apiKey)
        );

        if($appendUserAgent) {
            $args['body']['useragent'] = "Agent" . urlencode($_SERVER['HTTP_USER_AGENT']);
        }
        $response = wp_remote_post($requestURL, $args);
        
        if(is_wp_error( $response )) //some hosting providers won't allow https:// POST connections so we try http:// as well
            $response = wp_remote_post(str_replace('https://', 'http://', $requestURL), $args);    
            
        if(is_wp_error( $response ))
            $response = wp_remote_get(str_replace('https://', 'http://', $requestURL), $args);

        $defaultData = array(
            "APIKeyValid" => false,
            "Message" => 'API Key could not be validated due to a connectivity error.<BR>Your firewall may be blocking us. Please contact your hosting provider and ask them to allow connections from your site to IP 176.9.106.46.<BR> If you still cannot validate your API Key after this, please <a href="https://shortpixel.com/contact" target="_blank">contact us</a> and we will try to help. ',
            "APICallsMade" => 'Information unavailable. Please check your API key.',
            "APICallsQuota" => 'Information unavailable. Please check your API key.');

        if(is_object($response) && get_class($response) == 'WP_Error') {
            
            $urlElements = parse_url($requestURL);
            $portConnect = @fsockopen($urlElements['host'],8,$errno,$errstr,15);
            if(!$portConnect)
                $defaultData['Message'] .= "<BR>Debug info: <i>$errstr</i>";
    
            return $defaultData;
        }

        if($response['response']['code'] != 200) {
            return $defaultData;
        }

        $data = $response['body'];
        $data = $this->parseJSON($data);

        if(empty($data)) { return $defaultData; }

        if($data->Status->Code != 2) {
            $defaultData['Message'] = $data->Status->Message;
            return $defaultData;
        }

        if ( ( $data->APICallsMade + $data->APICallsMadeOneTime ) < ( $data->APICallsQuota + $data->APICallsQuotaOneTime ) ) //reset quota exceeded flag -> user is allowed to process more images. 
            update_option('wp-short-pixel-quota-exceeded',0);
        else
            update_option('wp-short-pixel-quota-exceeded',1);//activate quota limiting            
                                    
        return array(
            "APIKeyValid" => true,
            "APICallsMade" => number_format($data->APICallsMade) . ' images',
            "APICallsQuota" => number_format($data->APICallsQuota) . ' images',
            "APICallsMadeOneTime" => number_format($data->APICallsMadeOneTime) . ' images',
            "APICallsQuotaOneTime" => number_format($data->APICallsQuotaOneTime) . ' images',
            "APICallsMadeNumeric" => $data->APICallsMade,
            "APICallsQuotaNumeric" => $data->APICallsQuota,
            "APICallsMadeOneTimeNumeric" => $data->APICallsMadeOneTime,
            "APICallsQuotaOneTimeNumeric" => $data->APICallsQuotaOneTime
        );


    }

    public function generateCustomColumn( $column_name, $id ) {
        if( 'wp-shortPixel' == $column_name ) {
            $data = wp_get_attachment_metadata($id);
            $file = get_attached_file($id);
            $fileExtension = strtolower(substr($file,strrpos($file,".")+1));

            print "<div id='sp-msg-{$id}'>";
            
            if ( empty($data) )
            {
                if ( $fileExtension <> "pdf" )    
                {
                    if(!$this->_verifiedKey)
                        print 'Invalid API Key. <a href="options-general.php?page=wp-shortpixel">Check your Settings</a>';
                    else
                        print 'Optimization N/A';
                }
                else
                {
                    if ( get_option('wp-short-pixel-quota-exceeded') )
                    {
                        print QUOTA_EXCEEDED;
                        return;
                    }
                    else
                    {
                        print 'PDF not processed';
                        print " | <a href=\"javascript:manualOptimization({$id})\">Optimize now</a>";
                        return;
                    }
                }
            }
            elseif ( isset( $data['ShortPixelImprovement'] ) ) 
            {
                if(isset($meta['ShortPixel']['BulkProcessing'])) 
                {
                    if ( get_option('wp-short-pixel-quota-exceeded') )
                    {
                        print QUOTA_EXCEEDED;
                    }
                    else
                    {
                        print 'Waiting for bulk processing';
                        print " | <a href=\"javascript:manualOptimization({$id})\">Optimize now</a>";
                    }
                }
                elseif( is_numeric($data['ShortPixelImprovement']) && !isset($data['ShortPixel']['NoBackup'])  ) {
                    
                    if ( $data['ShortPixelImprovement'] < 5 )
                        {
                            print $data['ShortPixelImprovement'] . '%';   
                            print " optimized<BR> Bonus processing";
                            
                        }
                    else
                        {                    
                            print 'Reduced by ';
                            print $data['ShortPixelImprovement'] . '%';
                        }
                    if ( get_option('wp-short-backup_images') ) //display restore backup option only when backup is active
                        print " | <a href=\"admin.php?action=shortpixel_restore_backup&amp;attachment_ID={$id}\">Restore backup</a>";
                }
                elseif ( is_numeric($data['ShortPixelImprovement']) ) 
                {
                    if ( $data['ShortPixelImprovement'] < 5 )
                        {
                            print $data['ShortPixelImprovement'] . '%';   
                            print " optimized<BR> Bonus processing";
                            
                        }
                    else
                        {                    
                            print 'Reduced by ';
                            print $data['ShortPixelImprovement'] . '%';
                        }
                }
                elseif ( $data['ShortPixelImprovement'] <> "Optimization N/A" )
                {
                    if ( trim(strip_tags($data['ShortPixelImprovement'])) == "Quota exceeded" )
                    {
                        print QUOTA_EXCEEDED;
                        if ( !get_option('wp-short-pixel-quota-exceeded') )
                            print " | <a href=\"javascript:manualOptimization({$id})\">Try again</a>";
                    }
                    elseif ( trim(strip_tags($data['ShortPixelImprovement'])) == "Cannot write optimized file" )
                    {
                        print $data['ShortPixelImprovement'];
                        print " - <a href='https://shortpixel.com/faq#cannot-write-optimized-file' target='_blank'>Why?</a>";
                    } 
                    else
                    {
                        print $data['ShortPixelImprovement'];
                        print " | <a href=\"javascript:manualOptimization({$id})\">Try again</a>";
                    }
                }    
                else
                {
                    print "Optimization N/A";
                }
            } elseif(isset($data['ShortPixel']['WaitingProcessing'])) {
                if ( get_option('wp-short-pixel-quota-exceeded') )
                {
                    print QUOTA_EXCEEDED;
                }
                else
                {
                    print "<img src=\"" . WP_PLUGIN_URL . "/shortpixel-image-optimiser/img/loading.gif\">Image waiting to be processed
                          | <a href=\"javascript:manualOptimization({$id})\">Retry</a></div>";
                    $this->prioQ->push($id); //should be there but just to make sure
                }    

            } elseif(isset($data['ShortPixel']['NoFileOnDisk'])) {
                print 'Image does not exist';

            } else {
                
                if ( wp_attachment_is_image( $id ) ) 
                {
                    if ( get_option('wp-short-pixel-quota-exceeded') )
                    {
                        print QUOTA_EXCEEDED;
                    }
                    else
                    {
                        print 'Image not processed';
                        print " | <a href=\"javascript:manualOptimization({$id})\">Optimize now</a>";
                    }
                }
                elseif ( $fileExtension == "pdf" )
                {
                    if ( get_option('wp-short-pixel-quota-exceeded') )
                    {
                        print QUOTA_EXCEEDED;
                    }
                    else
                    {
                        print 'PDF not processed';
                        print " | <a href=\"javascript:manualOptimization({$id})\">Optimize now</a>";
                    }
                }
            }
            print "</div>";
        }
    }

    public function columns( $defaults ) {
        $defaults['wp-shortPixel'] = 'ShortPixel Compression';
        return $defaults;
    }

    public function generatePluginLinks($links) {
        $in = '<a href="options-general.php?page=wp-shortpixel">Settings</a>';
        array_unshift($links, $in);
        return $links;
    }

    public function parseJSON($data) {
        if ( function_exists('json_decode') ) {
            $data = json_decode( $data );
        } else {
            require_once( 'JSON/JSON.php' );
            $json = new Services_JSON( );
            $data = $json->decode( $data );
        }
        return $data;
    }


    static public function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    static public function isProcessable($ID) {
        $path = get_attached_file($ID);//get the full file PATH
        $pathParts = pathinfo($path);
        if( isset($pathParts['extension']) && in_array(strtolower($pathParts['extension']), array('jpg', 'jpeg', 'gif', 'png', 'pdf'))) {
                return true;
            } else {
                return false;
            }
    }


    //return an array with URL(s) and PATH(s) for this file
    public function getURLsAndPATHs($ID, $meta = NULL) { 
        
        if ( !parse_url(WP_CONTENT_URL, PHP_URL_SCHEME) )
        {//no absolute URLs used -> we implement a hack
           $url = get_site_url() . wp_get_attachment_url($ID);//get the file URL 
        }
        else
            $url = wp_get_attachment_url($ID);//get the file URL
       
        $urlList[] = $url;
        $path = get_attached_file($ID);//get the full file PATH
        $filePath[] = $path;
        if ( $meta == NULL ) {
            $meta = wp_get_attachment_metadata($ID);
        }

        //it is NOT a PDF file and thumbs are processable
        if (    strtolower(substr($filePath[0],strrpos($filePath[0], ".")+1)) != "pdf" 
             && $this->_processThumbnails 
             && isset($meta['sizes']) && is_array($meta['sizes'])) 
        {
            foreach( $meta['sizes'] as $thumbnailInfo ) 
                {
                    $urlList[] = str_replace(ShortPixelAPI::MB_basename($urlList[0]), $thumbnailInfo['file'], $url);
                    $filePath[] = str_replace(ShortPixelAPI::MB_basename($filePath[0]), $thumbnailInfo['file'], $path);
                }            
        }
        if(!isset($meta['sizes']) || !is_array($meta['sizes'])) {
            self::log("getURLsAndPATHs: no meta sizes for ID $ID : " . json_encode($meta));
        }
        return array("URLs" => $urlList, "PATHs" => $filePath);
    }
    

    public static function deleteDir($dirPath) {
        if (substr($dirPath, strlen($dirPath) - 1, 1) !=
         '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
                @rmdir($file);//remove empty dir
            } else {
                @unlink($file);//remove file
            }
        }
    }

    static public function folderSize($path) {
        $total_size = 0;
        if(file_exists($path)) {
            $files = scandir($path);
        } else {
            return $total_size;
        }
        $cleanPath = rtrim($path, '/'). '/';
        foreach($files as $t) {
            if ($t<>"." && $t<>"..") 
            {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    $size = self::folderSize($currentFile);
                    $total_size += $size;
                }
                else {
                    $size = filesize($currentFile);
                    $total_size += $size;
                }
            }
        }
        return $total_size;
    }
    
    public function getMaxMediaId() {
        global  $wpdb;
        $queryMax = "SELECT max(post_id) as QueryID FROM " . $wpdb->prefix . "postmeta";
        $resultQuery = $wpdb->get_results($queryMax);
        return $resultQuery[0]->QueryID;
    }
    
    public function getMinMediaId() {
        global  $wpdb;
        $queryMax = "SELECT min(post_id) as QueryID FROM " . $wpdb->prefix . "postmeta";
        $resultQuery = $wpdb->get_results($queryMax);
        return $resultQuery[0]->QueryID;
    }

    //count all the processable files in media library (while limiting the results to max 10000)
    public function countAllProcessableFiles($maxId = PHP_INT_MAX, $minId = 0){
        global  $wpdb;
        
        $totalFiles = 0;
        $mainFiles = 0;
        $limit = 500;
        $pointer = 0;

        //count all the files, main and thumbs 
        while ( 1 ) 
        {
            $filesList= $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "postmeta
                                        WHERE ( post_id <= $maxId AND post_id > $minId ) 
                                          AND ( meta_key = '_wp_attached_file' OR meta_key = '_wp_attachment_metadata' ) 
                                        LIMIT $pointer,$limit");
            if ( empty($filesList) ) //we parsed all the results
                break;
             
            foreach ( $filesList as $file ) 
            {
                if ( $file->meta_key == "_wp_attached_file" )
                {//count pdf files only
                    $extension = substr($file->meta_value, strrpos($file->meta_value,".") + 1 );
                    if ( $extension == "pdf" )
                    {
                        $totalFiles++;
                        $mainFiles++;
                    }
                }
                else
                {
                    $attachment = unserialize($file->meta_value);
                    if ( isset($attachment['sizes']) )
                        $totalFiles += count($attachment['sizes']);            
    
                    if ( isset($attachment['file']) )
                    {
                        $totalFiles++;
                        $mainFiles++;
                    }
                }
            }   
            unset($filesList);
            $pointer += $limit;
            
        }//end while
 
        return array("totalFiles" => $totalFiles, "mainFiles" => $mainFiles);
}  


    //count all the processable files in media library (while limiting the results to max 10000)
    public function countAllProcessedFiles($maxId = PHP_INT_MAX, $minId = 0){
        global  $wpdb;
        
        $processedMainFiles = $processedTotalFiles = 0;
        $limit = 500;
        $pointer = 0;

        //count all the files, main and thumbs 
        while ( 1 ) 
        {
            $filesList= $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "postmeta
                                        WHERE ( post_id <= $maxId AND post_id > $minId ) 
                                          AND ( meta_key = '_wp_attachment_metadata' ) 
                                        LIMIT $pointer,$limit");
            if ( empty($filesList) ) {//we parsed all the results
                break;
            }
            foreach ( $filesList as $file ) 
            {
                $attachment = unserialize($file->meta_value);
                if ( isset($attachment['ShortPixelImprovement']) && ($attachment['ShortPixelImprovement'] > 0 || $attachment['ShortPixelImprovement'] === 0.0)) {
                    $processedMainFiles++;            
                    $processedTotalFiles++;            
                    if ( isset($attachment['sizes']) ) {
                        $processedTotalFiles += count($attachment['sizes']);            
                    }
                }
            }   
            unset($filesList);
            $pointer += $limit;
            
        }//end while
 
        return array("totalFiles" => $processedTotalFiles, "mainFiles" => $processedMainFiles);
    }  

    public function migrateBackupFolder() {
        $oldBackupFolder = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'ShortpixelBackups';

        if(!file_exists($oldBackupFolder)) return;  //if old backup folder does not exist then there is nothing to do

        if(!file_exists(SP_BACKUP_FOLDER)) {
            //we check that the backup folder exists, if not we create it so we can copy into it
            if(!mkdir(SP_BACKUP_FOLDER, 0777, true)) return;
        }

        $scannedDirectory = array_diff(scandir($oldBackupFolder), array('..', '.'));
        foreach($scannedDirectory as $file) {
            @rename($oldBackupFolder.DIRECTORY_SEPARATOR.$file, SP_BACKUP_FOLDER.DIRECTORY_SEPARATOR.$file);
        }
        $scannedDirectory = array_diff(scandir($oldBackupFolder), array('..', '.'));
        if(empty($scannedDirectory)) {
            @rmdir($oldBackupFolder);
        }

        return;
    }

    public function getApiKey() {
        return $this->_apiKey;
    }
    
    public function backupImages() {
        return $this->_backupImages;
    }

    public function processThumbnails() {
        return $this->_processThumbnails;
    }

}

$pluginInstance = new WPShortPixel();
global $pluginInstance;

?>
