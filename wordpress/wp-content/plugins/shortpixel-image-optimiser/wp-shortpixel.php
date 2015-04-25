<?php
/**
 * Plugin Name: ShortPixel Image Optimizer
 * Plugin URI: https://shortpixel.com/
 * Description: ShortPixel is an image compression tool that helps improve your website performance. The plugin optimizes images automatically using both lossy and lossless compression. Resulting, smaller, images are no different in quality from the original. To install: 1) Click the "Activate" link to the left of this description. 2) <a href="https://shortpixel.com/wp-apikey" target="_blank">Free Sign up</a> for your unique API Key . 3) Check your email for your API key. 4) Use your API key to activate ShortPixel plugin in the 'Plugins' menu in WordPress. 5) Done!
 * Version: 2.1.4
 * Author: ShortPixel
 * Author URI: https://shortpixel.com
 */

require_once('shortpixel_api.php');
require_once( ABSPATH . 'wp-admin/includes/image.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
if ( !is_plugin_active( 'wpmandrill/wpmandrill.php' ) ) {
  require_once( ABSPATH . 'wp-includes/pluggable.php' );//to avoid conflict with wpmandrill plugin
} 

define('PLUGIN_VERSION', "2.1.4");
define('SP_DEBUG', false);
define('SP_LOG', false);
define('SP_MAX_TIMEOUT', 10);
define('SP_BACKUP_FOLDER', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ShortpixelBackups');
define('MUST_HAVE_KEY', true);
define('MAX_API_RETRIES', 5);
define('QUOTA_EXCEEDED', "Quota Exceeded. <a href='https://shortpixel.com/pricing' target='_blank'>Learn more</a>");
$MAX_EXECUTION_TIME = ini_get('max_execution_time');
if ( is_numeric($MAX_EXECUTION_TIME) )
	define('MAX_EXECUTION_TIME', $MAX_EXECUTION_TIME - 3 );   //in seconds
else
	define('MAX_EXECUTION_TIME', 25 );
define("SP_MAX_RESULTS_QUERY", 6);	

class WPShortPixel {

	private $_apiInterface = null;
	private $_apiKey = '';
	private $_compressionType = 1;
	private $_processThumbnails = 1;
	private $_backupImages = 1;
	private $_verifiedKey = false;

	public function __construct() {
		
		$this->populateOptions();
		$this->setDefaultViewModeList();//set default mode as list. only @ first run

		$this->_apiInterface = new shortpixel_api($this->_apiKey, $this->_compressionType);

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
		
		//when plugin is activated run this 
		register_activation_hook( __FILE__, array( &$this, 'shortPixelActivatePlugin' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'shortPixelDeactivatePlugin' ) );

		//automatic optimization
		add_action( 'admin_footer', array( &$this, 'my_action_javascript') );
		add_action( 'wp_ajax_my_action', array( &$this, 'handleImageProcessing') );

		//manual optimization
		add_action('admin_action_shortpixel_manual_optimize', array(&$this, 'handleManualOptimization'));
		//backup restore
		add_action('admin_action_shortpixel_restore_backup', array(&$this, 'handleRestoreBackup'));

		$this->migrateBackupFolder();
	}


	public function populateOptions() {

		if(get_option('wp-short-pixel-apiKey') !== false) {
			$this->_apiKey = get_option('wp-short-pixel-apiKey');
		} else {
			add_option( 'wp-short-pixel-apiKey', '', '', 'yes' );
		}

		if(get_option('wp-short-pixel-verifiedKey') !== false) {
			$this->_verifiedKey = get_option('wp-short-pixel-verifiedKey');
		}

		if(get_option('wp-short-pixel-compression') !== false) {
			$this->_compressionType = get_option('wp-short-pixel-compression');
		} else {
			add_option('wp-short-pixel-compression', $this->_compressionType, '', 'yes');
		}

		if(get_option('wp-short-process_thumbnails') !== false) {
			$this->_processThumbnails = get_option('wp-short-process_thumbnails');
		} else {
			add_option('wp-short-process_thumbnails', $this->_processThumbnails, '', 'yes' );
		}

		if(get_option('wp-short-backup_images') !== false) {
			$this->_backupImages = get_option('wp-short-backup_images');
		} else {
			add_option('wp-short-backup_images', $this->_backupImages, '', 'yes' );
		}

		if(get_option('wp-short-pixel-fileCount') === false) {
			add_option( 'wp-short-pixel-fileCount', 0, '', 'yes' );
		}

		if(get_option('wp-short-pixel-savedSpace') === false) {
			add_option( 'wp-short-pixel-savedSpace', 0, '', 'yes' );
		}

		if(get_option('wp-short-pixel-averageCompression') === false) {
			add_option( 'wp-short-pixel-averageCompression', 0, '', 'yes' );
		}
	
		if(get_option('wp-short-pixel-api-retries') === false) {//sometimes we need to retry processing/downloading a file multiple times
			add_option( 'wp-short-pixel-api-retries', 0, '', 'yes' );
		}
		
		if(get_option('wp-short-pixel-query-id-start') === false) {//current query ID used for postmeta queries
			add_option( 'wp-short-pixel-query-id-start', 0, '', 'yes' );
		}	
	
		if(get_option('wp-short-pixel-query-id-stop') === false) {//min ID used for postmeta queries
			add_option( 'wp-short-pixel-query-id-stop', 0, '', 'yes' );
		}	
		
		if(get_option('wp-short-pixel-quota-exceeded') === false) {//min ID used for postmeta queries
			add_option( 'wp-short-pixel-quota-exceeded', 0, '', 'yes' );
		}	
	
	}
	
	public function shortPixelActivatePlugin()//reset some params to avoid troubles for plugins that were activated/deactivated/activated
	{
			global  $startQueryID,$endQueryID;
			$this->getMaxShortPixelId();//fetch data for endQueryID and startQueryID	
			delete_option('bulkProcessingStatus');		
			delete_option( 'wp-short-pixel-cancel-pointer');
			update_option( 'wp-short-pixel-query-id-stop', $endQueryID );
			update_option( 'wp-short-pixel-query-id-start', $startQueryID );
	}
	
	public function shortPixelDeactivatePlugin()//reset some params to avoid troubles for plugins that were activated/deactivated/activated
	{
			delete_option('bulkProcessingStatus');
			delete_option( 'wp-short-pixel-cancel-pointer');
			update_option( 'wp-short-pixel-query-id-stop', 0 );
			update_option( 'wp-short-pixel-query-id-start', 0 );
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
		if(SP_DEBUG) {
			echo "{$message}</br>";
		}
	}

	function my_action_javascript() { ?>
		<script type="text/javascript" >
			jQuery(document).ready(sendRequest());
			function sendRequest() {
				var data = { 'action': 'my_action' };
				// since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					if(response.search('Empty queue') >= 0 || response.search('Error processing image') >= 0) {
						console.log('Queue is empty');
					} else {
						console.log('Server response: ' + response);
						sendRequest();
					}
				});
			}
		</script> <?php
	}

	function wp_load_admin_js() {
		add_action('admin_print_footer_scripts', array(&$this, 'add_bulk_actions_via_javascript'));
	}

	function add_bulk_actions_via_javascript() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('select[name^="action"] option:last-child').before('<option value="2">Bulk Optimize</option>');
			});
		</script>
	<?php }

	//handling older
	public function WPShortPixel() {
		$this->__construct();
	}

	public function handleImageUpload($meta, $ID = null) {

		if(MUST_HAVE_KEY && $this->_verifiedKey) 
			{
				$bulkProcessingStatus = get_option('bulkProcessingStatus');
				//update variable to keep track of this new attachment but only if bulk isn't running
				if( $bulkProcessingStatus <> 'running' ) 
						update_option("wp-short-pixel-query-id-start", $ID);
				
				self::log("Processing image id {$ID}");
				$url = wp_get_attachment_url($ID);
				$path = get_attached_file($ID);
				if(self::isProcessable($path) != false) 
				{
					if ( empty($meta) && $bulkProcessingStatus <> 'running' )//here's a PDF file most likely, while bulk not running
						{
							$meta['ShortPixel']['WaitingProcessing'] = true;
							return $meta;
						}
					elseif ( empty($meta) && $bulkProcessingStatus == 'running' )//while bulk running
						{
							return $meta;
						}


					$urlList[] = $url;
					$filePath[] = $path;
					//send request for thumbs as well, if needed
					if( !empty($meta['sizes']) ) 
					{
						foreach($meta['sizes'] as $thumbnailInfo) 
						{
							$urlList[] = str_replace(basename($url), $thumbnailInfo['file'], $url);
							$filePath[] = str_replace(basename($path), $thumbnailInfo['file'], $path);
						}
						$this->_apiInterface->doRequests($urlList, $filePath);//send a processing request right after a file was uploaded
					}
					else//file is PDF maybe?
					{
						$uploadFilePath = get_attached_file($ID);
						if ( strtolower(substr($uploadFilePath,strrpos($uploadFilePath, ".")+1)) == "pdf" ) //is a PDF file
						{
							$filePath[0] = $uploadFilePath;
							$this->_apiInterface->doRequests($urlList, $filePath);//send a processing request right after a file was uploaded
						}
					}
					
					if ( $bulkProcessingStatus <> 'running' ) 
						$meta['ShortPixel']['WaitingProcessing'] = true;
					return $meta;
				} 
				else 
				{
					$meta['ShortPixelImprovement'] = 'Optimisation N/A';
					return $meta;
				}
			} 
	}

	public function handleImageProcessing($ID = null) {
		if(MUST_HAVE_KEY && $this->_verifiedKey == false) {
			echo "Missing API Key";
			die();
		}
		//query database for first found entry that needs processing //
		global  $wpdb,$startQueryID,$endQueryID;
		
//////////////////

		$startQueryID = get_option('wp-short-pixel-query-id-start');
		$endQueryID = get_option('wp-short-pixel-query-id-stop');
		
		if ( $startQueryID <= $endQueryID )
		{
			echo 'Empty queue ' . $startQueryID . '->' . $endQueryID;
			die;
		}

		sleep(1);
		$queryPostMeta = "SELECT * FROM " . $wpdb->prefix . "postmeta 
						WHERE ( post_id <= $startQueryID AND post_id > $endQueryID ) AND (
								meta_key = '_wp_attached_file'
								OR meta_key = '_wp_attachment_metadata'
						)
						ORDER BY post_id DESC
						LIMIT " . SP_MAX_RESULTS_QUERY;
		$resultsPostMeta = $wpdb->get_results($queryPostMeta);
		
		if ( empty($resultsPostMeta) )
		{
			$this->getMaxShortPixelId();//fetch data for endQueryID and startQueryID	
			update_option("wp-short-pixel-query-id-start", $startQueryID);//update max and min ID			
			update_option("wp-short-pixel-query-id-stop", $endQueryID);
			echo 'Empty results ' . $startQueryID . '->' . $endQueryID;
			die;
		}
		
		$idList = array();
		$countMeta = 0;
		foreach ( $resultsPostMeta as $itemMetaData )
		{
			if ( $countMeta == 0 )
				{
					$metaCurrentFile = wp_get_attachment_metadata($itemMetaData->post_id);
					$meta = $metaCurrentFile;
				}
			else
				$meta = wp_get_attachment_metadata($itemMetaData->post_id);
			$meta['ShortPixelImprovement'] = ( isset($meta['ShortPixelImprovement']) ) ? $meta['ShortPixelImprovement'] : "";
			$filePath = get_attached_file($itemMetaData->post_id);
			$fileExtension = strtolower(substr($filePath,strrpos($filePath,".")+1));
			
			if ( ( $itemMetaData->meta_key == "_wp_attachment_metadata" && $meta['ShortPixelImprovement'] <> "Optimisation N/A"  && !is_numeric($meta['ShortPixelImprovement']) )  || $fileExtension == "pdf" )//Optimisation N/A = is an unsupported file format
			{
				$idList[] = $itemMetaData;
			}
			
			$countMeta++;
		}

		
		if ( isset($idList[0]) )
		{
			$meta = $metaCurrentFile; //assign the saved meta of the file position [0]
			$meta = wp_get_attachment_metadata($idList[0]->post_id);
			$filePath = get_attached_file($idList[0]->post_id);
			$fileExtension = strtolower(substr($filePath,strrpos($filePath,".")+1));
			
			if ( !self::isProcessable($filePath) )//file has a non-supported extension, we skip it
			{
				$startQueryID = $idList[0]->post_id - 1;
				update_option("wp-short-pixel-query-id-start", $startQueryID);//update max ID
				die();
			}
			elseif( !empty($meta) && !isset($meta['ShortPixel']['WaitingProcessing']) ) //possibly the file wasn't processed in the first pass so we'll wait for it to be completed
			{
				//if ( isset($idList[1]) )
					$startQueryID = ( $idList[0]->post_id );
				//else
				//	$startQueryID = ( $idList[0]->post_id - 1 );
					
				update_option("wp-short-pixel-query-id-start", $startQueryID);//update max ID
			}
			elseif ( empty($meta) && $fileExtension <> "pdf" )//file is not an image or PDF so we just skip to the next batch
			{
				$startQueryID = $startQueryID - 1; //SP_MAX_RESULTS_QUERY;
				update_option("wp-short-pixel-query-id-start", $startQueryID);//update max ID	
				die();			
			}
			else //file was NOT processed in the first pass
			{

				$startQueryID = $idList[0]->post_id;
				update_option("wp-short-pixel-query-id-start", $startQueryID);//update max ID	
			}			
		}
		elseif ( $startQueryID > $endQueryID )
		{
			if ( isset($resultsPostMeta[2]) && is_numeric($resultsPostMeta[2]->post_id) ) 
				$startQueryID = $resultsPostMeta[2]->post_id;
			else	
				$startQueryID = $startQueryID - 1;
			update_option("wp-short-pixel-query-id-start", $startQueryID);//update max ID
			die();
		}


//////////////////////       

		if( empty($idList) && $startQueryID <= $endQueryID ) { //die but before set the $endQueryID so only new files will be processed
			$this->getMaxShortPixelId();//fetch data for endQueryID and startQueryID	
			update_option('wp-short-pixel-query-id-stop', $endQueryID);
			delete_option('bulkProcessingStatus');
			echo 'Empty queue - '.$endQueryID; die;
		}
		
		//send a couple of pre-process requests (if available/needed)	
		if ( isset($idList[1]) )
		{
			$itemDetails = $this->returnURLsAndPaths($idList[1]);
			$this->_apiInterface->doRequests($itemDetails['imageURLs'], $itemDetails['imagePaths']);	
		}

		//send a couple of pre-process requests (if available/needed)
		if ( isset($idList[2]) )
		{
			$itemDetails = $this->returnURLsAndPaths($idList[1]);
			$this->_apiInterface->doRequests($itemDetails['imageURLs'], $itemDetails['imagePaths']);	
		}


		//send a request for the latest item
		$itemDetails = $this->returnURLsAndPaths($idList[0]);
		$meta = $itemDetails['meta'];
		$ID = $itemDetails['ID'];
		$result = $this->_apiInterface->processImage($itemDetails['imageURLs'], $itemDetails['imagePaths'], $ID);//use the API connection to send processing requests for these files.	

		if(is_string($result)) {//there was an error?
			if(isset($meta['ShortPixel']['BulkProcessing'])) { unset($meta['ShortPixel']['BulkProcessing']); }
			if(isset($meta['ShortPixel']['WaitingProcessing'])) { unset($meta['ShortPixel']['WaitingProcessing']); }
			$meta['ShortPixelImprovement'] = $result;
			wp_update_attachment_metadata($ID, $meta);
			echo "Error processing image: " . $result;
			//also decrement last ID for queries so bulk won't hang in such cases
			$startQueryID = get_option("wp-short-pixel-query-id-start");
			update_option("wp-short-pixel-query-id-start", $startQueryID - 1);
			die;
		}

		//$processThumbnails = get_option('wp-short-process_thumbnails');
		if ( isset($meta['ShortPixel']) )
		{
			if ( isset($meta['ShortPixel']['WaitingProcessing']) )
				unset($meta['ShortPixel']['WaitingProcessing']);
	
			if( isset($meta['ShortPixel']['BulkProcessing']) ) 
				unset($meta['ShortPixel']['BulkProcessing']);
		}

		$meta['ShortPixelImprovement'] = round($result[0]->PercentImprovement,2);
		wp_update_attachment_metadata($ID, $meta);
		echo "\nProcessing done succesfully for image #{$ID}";
		
		//set the next ID to be processed (skip to the next valid value)
		if ( isset($isList[1]) )
		{
			$startQueryID = $idList[1]->post_id;
			update_option("wp-short-pixel-query-id-start", $startQueryID);//update max ID	
		}

		die();
	}
	
	
	//return urls and paths to be used but other functions
	public function returnURLsAndPaths($itemDetails)
	{
			global  $wpdb;
			
			$imageIndex=0;
			$ID = $itemDetails->post_id;
			$imageURL =  wp_get_attachment_url($ID);
			$imagePath = get_attached_file($ID);
			$meta = wp_get_attachment_metadata($ID);	
			$uploadDir = wp_upload_dir();

			if ( empty($meta['file']) )//file has no metadata attached (like PDF files uploaded before SP plugin)
				$SubDir = $this->_apiInterface->returnSubDir($imagePath);
			else
				$SubDir = $this->_apiInterface->returnSubDir($meta['file']);

			$imageURLs[] = $uploadDir['baseurl'] . DIRECTORY_SEPARATOR . $SubDir . basename($imagePath);//URL to PDF file
			$imagePaths[] = $uploadDir['basedir'] . DIRECTORY_SEPARATOR . $SubDir . basename($imagePath);
	
			$processThumbnails = get_option('wp-short-process_thumbnails');

			if ( isset($meta['file']) && $processThumbnails )//handle the rest of the thumbnails generated by WP
			{		
				$imageIndex = 0;			
				foreach ( $meta['sizes'] as $pictureDetails )
				{
					$imageIndex++;
					$imageURLs[$imageIndex] = $uploadDir['baseurl'] . DIRECTORY_SEPARATOR . $SubDir . basename($pictureDetails['file']);
					$imagePaths[$imageIndex] = $uploadDir['basedir'] . DIRECTORY_SEPARATOR . $SubDir . basename($pictureDetails['file']);
				}	
			}
			
			if ( isset($imageURLs) )
				return array("imageURLs" => $imageURLs, "imagePaths" => $imagePaths, "meta" => $meta, "ID" => $ID);
			else
				return false;
				
	}//end returnURLsAndPaths
	

	public function handleManualOptimization() {
		$attachmentID = intval($_GET['attachment_ID']);

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
				$urlList[]= str_replace(basename($filePath[0]), $thumbnailInfo['file'], $urlList[0]);
				$filePath[] = str_replace(basename($filePath[0]), $thumbnailInfo['file'], $filePath[0]);
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
		$uploadDir = wp_upload_dir();
		$pathInfo = pathinfo($file);
	
		$fileExtension = strtolower(substr($file,strrpos($file,".")+1));
		$SubDir = $this->_apiInterface->returnSubDir($file);

		//sometimes the month of original file and backup can differ
		if ( !file_exists(SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . basename($file)) )
			$SubDir = date("Y") . "/" . date("m") . "/";

		try {
			//main file	
			@rename(SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . basename($file), $file);

			//overwriting thumbnails
			if( !empty($meta['file']) ) {
				foreach($meta["sizes"] as $size => $imageData) {
					$source = SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . $imageData['file'];
					$destination = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $imageData['file'];
					@rename($source, $destination);
				}
			}
			unset($meta["ShortPixelImprovement"]);
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
		if(self::isProcessable($file) != false) {
			try {
				$uploadDir = wp_upload_dir();
				$SubDir = $this->_apiInterface->returnSubDir($file);
					
				@unlink(SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . basename($file));
				
				if ( !empty($meta['file']) )
				{
					$filesPath =  SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir;//base BACKUP path
					//remove thumbs thumbnails
					if(isset($meta["sizes"])) {
						foreach($meta["sizes"] as $size => $imageData) {
							@unlink($filesPath . basename($imageData['file']));//remove thumbs
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

	public function bulkProcess() {
		global $wpdb,$startQueryID,$endQueryID;
		echo '<h1>Bulk Image Optimization by ShortPixel</h1>';

		if(MUST_HAVE_KEY && $this->_verifiedKey == false) {//invalid API Key
			echo "<p>In order to start processing your images, you need to validate your API key in the ShortPixel Settings. If you don’t have an API Key, you can get one delivered to your inbox.</p>";
			echo "<p>Don’t have an API Key yet? Get it now at <a href=\"https://shortpixel.com/wp-apikey\" target=\"_blank\">www.ShortPixel.com</a>, for free.</p>";
			return;
		}

		if(isset($_GET['cancel'])) 
		{//cancel an ongoing bulk processing, it might be needed sometimes
			$this->cancelProcessing();
		}

		if(isset($_POST["bulkProcess"])) 
		{
			$this->getMaxShortPixelId();//fetch data for endQueryID and startQueryID	
			update_option("wp-short-pixel-query-id-start", $startQueryID);//start downwards from the biggest item ID			
			update_option("wp-short-pixel-query-id-stop", 0);
			update_option("wp-short-pixel-flag-id", $startQueryID);//we use to detect new added files while bulk is running
			add_option('bulkProcessingStatus', 'running');//set bulk flag		
		}//end bulk process  was clicked	
		
		if(isset($_POST["bulkProcessResume"])) 
		{
			$startQueryID = get_option( 'wp-short-pixel-cancel-pointer');
			update_option("wp-short-pixel-query-id-start", $startQueryID);//start downwards from the biggest item ID			
			update_option("wp-short-pixel-query-id-stop", 0);
			update_option("wp-short-pixel-flag-id", $startQueryID);//we use to detect new added files while bulk is running
			add_option('bulkProcessingStatus', 'running');//set bulk flag	
			delete_option( 'wp-short-pixel-cancel-pointer');
		}//resume was clicked

		$bulkProcessingStatus = get_option('bulkProcessingStatus');
		$startQueryID = get_option('wp-short-pixel-query-id-start');
		$endQueryID = get_option('wp-short-pixel-query-id-stop');

		//figure out all the files that could be processed
		$qry = "SELECT count(*) FilesToBeProcessed FROM " . $wpdb->prefix . "postmeta
		WHERE meta_key = '_wp_attached_file' ";
		$allFiles = $wpdb->get_results($qry);
		//figure out the files that are left to be processed
		$qry_left = "SELECT count(*) FilesLeftToBeProcessed FROM " . $wpdb->prefix . "postmeta
		WHERE meta_key = '_wp_attached_file' AND post_id <= $startQueryID";
		$filesLeft = $wpdb->get_results($qry_left);
	
		if ( get_option('wp-short-pixel-quota-exceeded') )//quota exceeded, let the user know
		{
			$noticeHTML = "<br/><div style=\"background-color: #fff; border-left: 4px solid %s; box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1); padding: 1px 12px;\"><p>%s</p></div>";
			$quotaData = $this->getQuotaInformation();
		
			//maybe in the meantime the user added credits to their account?
			
			if ( $quotaData['APICallsQuotaNumeric'] > $quotaData['APICallsMadeNumeric'] )
			{
				update_option('wp-short-pixel-quota-exceeded','0');
			}
			else
			{	
				printf($noticeHTML, '#ff0000', "The plugin has optimized " . number_format($quotaData['APICallsMadeNumeric']) . " images and stopped because it reached the monthly limit which is " . number_format($quotaData['APICallsQuotaNumeric']) . ".<BR> See the other <a href='https://shortpixel.com/pricing' target='_blank'>options availbe</a> and <a href='https://shortpixel.com/login' target='_blank'>log into your account</a> to change your type of subscription.");	
				return;
			}
		}
		
		
		if( $filesLeft[0]->FilesLeftToBeProcessed > 0 && ( $startQueryID <> $endQueryID ) && $bulkProcessingStatus == "running" )//bulk processing was started and is still running
		{

			echo "<p>
					Bulk optimization has started. This process will take some time, depending on the number of images in your library. <BR>Do not worry about the slow speed, it is a necessary measure in order not to interfere with the normal functioning of your site.<BR><BR>
					This is a brief estimation of the bulk processing times:<BR>
					1 to 100 images < 20 min <BR>
					100 to 500 images < 2 hour<BR>
					500 to 1000 images < 4 hours<BR>
					over 1000 images > 4 hours or more<BR><BR>
					
					The latest status of the processing will be displayed here every 30 seconds.<BR>
					In the meantime, you can continue using the admin as usual.<BR> 
					However, <b>you musn’t close the WordPress admin</b>, or the bulk processing will stop.
				  </p>";
			echo '
				<script type="text/javascript" >
					var bulkProcessingRunning = true;
				 </script>
			';

			$imagesLeft = $filesLeft[0]->FilesLeftToBeProcessed;
			$totalImages = $allFiles[0]->FilesToBeProcessed;

			echo "<p>{$imagesLeft} out of {$totalImages} images left to process.</p>";

			echo '
				<a class="button button-secondary" href="' . get_admin_url() .  'upload.php">Media Library</a>
				<a class="button button-secondary" href="' . get_admin_url() .  'upload.php?page=wp-short-pixel-bulk&cancel=1">Cancel Processing</a>
			';

		} else 
		{
			$bulkProcessingStatus = get_option('bulkProcessingStatus');
			if(isset($bulkProcessingStatus) && $bulkProcessingStatus == 'running') 
			{
				echo "<p>Bulk optimization was successful. ShortPixel has finished optimizing all your images.</p>
                      <p>Go to the ShortPixel <a href='" . get_admin_url() . "options-general.php?page=wp-shortpixel#facts'>Stats</a> and see your website's optimized stats (in Settings > ShortPixel). </p>";
                
                $this->getMaxShortPixelId();//fetch data for endQueryID and startQueryID	
				$maxIDbeforeBulk = get_option("wp-short-pixel-flag-id");//what was the max id before bulk was started?

				if ( $startQueryID > $maxIDbeforeBulk )//basically we resume the processing for the files uploaded while bulk was running
				{
					update_option("wp-short-pixel-query-id-start", $startQueryID);
					update_option("wp-short-pixel-query-id-stop", $maxIDbeforeBulk);
					delete_option('bulkProcessingStatus');
				}
				else
				{	
					update_option("wp-short-pixel-query-id-start", $startQueryID);
					update_option("wp-short-pixel-query-id-stop", $endQueryID);      
					delete_option('bulkProcessingStatus');
				}
			}
		
			delete_option('bulkProcessingStatus');
			echo $this->getBulkProcessingForm($allFiles[0]->FilesToBeProcessed);
			echo '
                <script type="text/javascript" >
                    var bulkProcessingRunning = false;
                 </script>
            ';
		}

		echo '
            <script type="text/javascript" >
                jQuery(document).ready(function() {
                    if(bulkProcessingRunning) {
                        console.log("Bulk processing running");
                        setTimeout(function(){
                              window.location = window.location.href;
                            }, 30000);
                    } else {
                        console.log("No bulk processing is currently running");
                    }
                });
            </script>
        ';
	}
	//end bulk processing
	
	
	public function cancelProcessing(){
		//cancel an ongoing bulk processing, it might be needed sometimes 
		global  $wpdb,$startQueryID,$endQueryID;
		$startQueryID = get_option('wp-short-pixel-query-id-start');
		add_option( 'wp-short-pixel-cancel-pointer', $startQueryID);//we save this so we can resume bulk processing
		
		$this->getMaxShortPixelId();//fetch data for endQueryID and startQueryID	
		update_option("wp-short-pixel-query-id-start", $startQueryID);
		update_option("wp-short-pixel-query-id-stop", $endQueryID);
		delete_option('bulkProcessingStatus');
		echo "Empty queue";
	}

	public function renderSettingsMenu() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die('You do not have sufficient permissions to access this page.');
		}
		echo '<h1>ShortPixel Plugin Settings</h1>';
		echo '<p>
                <a href="https://shortpixel.com" target="_blank">ShortPixel.com</a> |
                <a href="https://wordpress.org/plugins/shortpixel-image-optimiser/installation/" target="_blank">Installation </a> |
                <a href="https://shortpixel.com/contact" target="_blank">Support </a>
              </p>';
		echo '<p>New images uploaded to the Media Library will be optimized automatically.<br/>If you have existing images you would like to optimize, you can use the <a href="' . get_admin_url()  . 'upload.php?page=wp-short-pixel-bulk">Bulk Optimization Tool</a>.</p>';

		$noticeHTML = "<br/><div style=\"background-color: #fff; border-left: 4px solid %s; box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1); padding: 1px 12px;\"><p>%s</p></div>";

		if(isset($_POST['submit']) || isset($_POST['validate'])) {
			
			//handle API Key - common for submit and validate
			$_POST['key'] = trim(str_replace("*","",$_POST['key']));
			
			if ( strlen($_POST['key']) <> 20 )
			{
				$KeyLength = strlen($_POST['key']);
				
				printf($noticeHTML, '#ff0000', "The key you provided has " .  $KeyLength . " characters. The API key should have 20 characters, letters and numbers only.<BR> <b>Please check that the API key is the same as the one you received in your confirmation email.</b><BR>
				If this problem persists, please contact us at <a href='mailto:support@shortpixel.com?Subject=API Key issues' target='_top'>support@shortpixel.com</a> or <a href='https://shortpixel.com/contact' target='_blank'>here</a>.");
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
						printf($noticeHTML, '#7ad03a', 'API Key valid!');
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
				update_option('wp-short-process_thumbnails', $this->_processThumbnails);
				update_option('wp-short-backup_images', $this->_backupImages);
			}
		}


		//empty backup
		if(isset($_POST['emptyBackup'])) {
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

		$checked = '';
		if($this->_processThumbnails) { $checked = 'checked'; }

		$checkedBackupImages = '';
		if($this->_backupImages) { $checkedBackupImages = 'checked'; }

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
			$averageCompression = round(get_option('wp-short-pixel-averageCompression'),2);
			$savedBandwidth = self::formatBytes(get_option('wp-short-pixel-savedSpace') * 1000,2);
			$quotaData = $this->getQuotaInformation();
			if (is_numeric($quotaData['APICallsQuota'])) {
				$quotaData['APICallsQuota'] .= "/month";
			}
			$backupFolderSize = self::formatBytes(self::folderSize(SP_BACKUP_FOLDER));
			$remainingImages = (int)str_replace(',', '', $quotaData['APICallsQuota']) - (int)str_replace(',', '', $quotaData['APICallsMade']);
			$remainingImages = number_format($remainingImages);

			$statHTML = <<< HTML
<a id="facts"></a>
<h3>Your ShortPixel Stats</h3>
<table class="form-table">
<tbody><tr>
<th scope="row"><label for="totalFiles">Total number of processed files:</label></th>
<td>{$fileCount}</td>
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

<p style="padding-top: 0px; color: #818181;" >* Saved bandwidth is calculated at 100,000 impressions/image</p>
<table class="form-table">
<tbody><tr>
<th scope="row"><label for="apiQuota">Your ShortPixel plan</label></th>
<td>{$quotaData['APICallsQuota']}</td>
</tr>
<tr>
<th scope="row"><label for="usedQUota">Number of images processed this month:</label></th>
<td>{$quotaData['APICallsMade']}</td>
</tr>
<tr>
<th scope="row"><label for="remainingImages">Remaining images in your plan:  </label></th>
<td>{$remainingImages} images</td>
</tr>
<tr>
<th scope="row"><label for="averagCompression">Average compression of your files:</label></th>
<td>$averageCompression%</td>
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

	public function getBulkProcessingForm($imageCount) {
		
		$message = "</br>
Currently, you have {$imageCount} images in your library. </br>
</br>
<form action='' method='POST' >
<input type='submit' name='bulkProcess' id='bulkProcess' class='button button-primary' value='Compress all your images'>";
		
		if ( get_option( 'wp-short-pixel-cancel-pointer') )//add also the resume bulk processing option
			$message .= "&nbsp;&nbsp;&nbsp;<input type='submit' name='bulkProcessResume' id='bulkProcessResume' class='button button-primary' value='Resume cancelled process'>";

		$message .= "
</form>";
		return $message;
	
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

		if(is_wp_error( $response )) {
			$response = wp_remote_get(str_replace('https://', 'http://', $requestURL), $args);
		}

		$defaultData = array(
			"APIKeyValid" => false,
			"Message" => 'API Key could not be validated. Could not connect Shortpixel service.',
			"APICallsMade" => 'Information unavailable. Please check your API key.',
			"APICallsQuota" => 'Information unavailable. Please check your API key.');

		if(is_object($response) && get_class($response) == 'WP_Error') {
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

		return array(
			"APIKeyValid" => true,
			"APICallsMade" => number_format($data->APICallsMade) . ' images',
			"APICallsQuota" => number_format($data->APICallsQuota) . ' images',
			"APICallsMadeNumeric" => $data->APICallsMade,
			"APICallsQuotaNumeric" => $data->APICallsQuota
		);


	}

	public function generateCustomColumn( $column_name, $id ) {
		if( 'wp-shortPixel' == $column_name ) {
			$data = wp_get_attachment_metadata($id);
			$file = get_attached_file($id);
			$fileExtension = strtolower(substr($file,strrpos($file,".")+1));

			if ( empty($data) )
			{
				if ( $fileExtension <> "pdf" )				
					print 'Optimisation N/A';
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
							print " | <a href=\"admin.php?action=shortpixel_manual_optimize&amp;attachment_ID={$id}\">Optimize now</a>";
							return;
						}
					}
				return;
			}
			elseif ( isset( $data['ShortPixelImprovement'] ) ) 
			{
				if(isset($meta['ShortPixel']['BulkProcessing'])) 
				{
					if ( get_option('wp-short-pixel-quota-exceeded') )
					{
						print QUOTA_EXCEEDED;
						return;
					}
					else
					{
						print 'Waiting for bulk processing';
						print " | <a href=\"admin.php?action=shortpixel_manual_optimize&amp;attachment_ID={$id}\">Optimize now</a>";
						return;
					}
				}

				if( is_numeric($data['ShortPixelImprovement']) && !isset($data['ShortPixel']['NoBackup'])  ) {
					print 'Reduced by ';
					print $data['ShortPixelImprovement'] . '%';
					print " | <a href=\"admin.php?action=shortpixel_restore_backup&amp;attachment_ID={$id}\">Restore backup</a>";
					return;
				}
				elseif ( is_numeric($data['ShortPixelImprovement']) ) 
				{
					print 'Reduced by ';
					print $data['ShortPixelImprovement'];
					print '%';
					return;
				}
				elseif ( $data['ShortPixelImprovement'] <> "Optimisation N/A" )
				{
					if ( trim(strip_tags($data['ShortPixelImprovement'])) == "Quota exceeded" )
						{
							print $data['ShortPixelImprovement'];
							if ( !get_option('wp-short-pixel-quota-exceeded') )
								print " | <a href=\"admin.php?action=shortpixel_manual_optimize&amp;attachment_ID={$id}\">Try again</a>";
							return;
						}
					else
						{
							print $data['ShortPixelImprovement'];
							print " | <a href=\"admin.php?action=shortpixel_manual_optimize&amp;attachment_ID={$id}\">Try again</a>";
							return;
						}
				}	
				else
				{
					print "Optimisation N/A";
					return;
				}
					
					
			} elseif(isset($data['ShortPixel']['WaitingProcessing'])) {
				if ( get_option('wp-short-pixel-quota-exceeded') )
				{
					print QUOTA_EXCEEDED;
					return;
				}
				else
				{
					print 'Image waiting to be processed';
					print " | <a href=\"admin.php?action=shortpixel_manual_optimize&amp;attachment_ID={$id}\">Optimize now</a>";
					return;
				}	
					
			} elseif(isset($data['ShortPixel']['NoFileOnDisk'])) {
				print 'Image does not exist';
				return;
			} else {
				
				if ( wp_attachment_is_image( $id ) ) 
				{
					if ( get_option('wp-short-pixel-quota-exceeded') )
					{
						print QUOTA_EXCEEDED;
						return;
					}
					else
					{
						print 'Image not processed';
						print " | <a href=\"admin.php?action=shortpixel_manual_optimize&amp;attachment_ID={$id}\">Optimize now</a>";
						return;
					}
				}
				elseif ( $fileExtension == "pdf" )
				{
					if ( get_option('wp-short-pixel-quota-exceeded') )
					{
						print QUOTA_EXCEEDED;
						return;
					}
					else
					{
						print 'PDF not processed';
						print " | <a href=\"admin.php?action=shortpixel_manual_optimize&amp;attachment_ID={$id}\">Optimize now</a>";
						return;
					}
				}
			
			}
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
	
	static public function isProcessable($path) {
		$pathParts = pathinfo($path);
		if( isset($pathParts['extension']) && in_array(strtolower($pathParts['extension']), array('jpg', 'jpeg', 'gif', 'png', 'pdf'))) {
				return true;
			} else {
				return false;
			}
	}

	public static function deleteDir($dirPath) {
	    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
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
	
	public function getMaxShortPixelId() {
		global  $wpdb,$startQueryID,$endQueryID;
		$queryMax = "SELECT max(post_id) as startQueryID FROM " . $wpdb->prefix . "postmeta";
		$resultQuery = $wpdb->get_results($queryMax);
		$startQueryID = $resultQuery[0]->startQueryID;
		$endQueryID = $startQueryID;		
		
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


}

$pluginInstance = new WPShortPixel();
global $pluginInstance;

?>
