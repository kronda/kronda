<?php
if ( !function_exists( 'download_url' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

class shortpixel_api {

	private $_apiKey = '';
	private $_compressionType = '';
	private $_maxAttempts = 10;
	private $_apiEndPoint = 'https://api.shortpixel.com/v2/reducer.php';

	public function setCompressionType($compressionType) {
		$this->_compressionType = $compressionType;
	}

	public function getCompressionType() {
		return $this->_compressionType;
	}

	public function setApiKey($apiKey) {
		$this->_apiKey = $apiKey;
	}

	public function getApiKey() {
		return $this->_apiKey;
	}

	public function __construct($apiKey, $compressionType) {
		$this->_apiKey = $apiKey;
		$this->setCompressionType($compressionType);

		add_action('processImageAction', array(&$this, 'processImageAction'), 10, 4);
	}

	public function processImageAction($url, $filePaths, $ID, $time) {
		$this->processImage($url, $filePaths, $ID, $time);
	}

	public function doRequests($urls, $filePath, $ID = null) {
		if ( !is_array($urls) )
			$response = $this->doBulkRequest(array($urls), true);
		else
			$response = $this->doBulkRequest($urls, true);

		if(is_object($response) && get_class($response) == 'WP_Error') {
			return false;
		}

		return $response;
	}

	public function doBulkRequest($imageList = array(), $blocking = false) {
		if(!is_array($imageList)) return false;

		$requestParameters = array(
			'plugin_version' => PLUGIN_VERSION,
			'key' => $this->_apiKey,
			'lossy' => $this->_compressionType,
			'urllist' => $imageList
		);

		$response = wp_remote_post($this->_apiEndPoint, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 3,
			'sslverify' => false,
			'httpversion' => '1.0',
			'blocking' => $blocking,
			'headers' => array(),
			'body' => json_encode($requestParameters),
			'cookies' => array()
		));

		return $response;
	}

	public function parseResponse($response) {
		$data = $response['body'];
		$data = $this->parseJSON($data);
		return $data;
	}

	//handles the processing of the image using the ShortPixel API
	public function processImage($url, $filePaths, $ID = null, $startTime = 0) {
		
		if($startTime == 0) { $startTime = time(); }		
		$apiRetries = get_option('wp-short-pixel-api-retries');
		if(time() - $startTime > MAX_EXECUTION_TIME) {//keeps track of time
			if ( $apiRetries > MAX_API_RETRIES )//we tried to process this time too many times, giving up...
			{
				$meta = wp_get_attachment_metadata($ID);
				$meta['ShortPixelImprovement'] = 'Timed out while processing.';
				unset($meta['ShortPixel']['WaitingProcessing']);
				wp_update_attachment_metadata($ID, $meta);
				//also decrement last ID for queries so bulk won't hang in such cases
				$startQueryID = get_option("wp-short-pixel-query-id-start");
				update_option("wp-short-pixel-query-id-start", $startQueryID - 1);
			}
			else
			{//we'll try again next time user visits a page on admin panel
				$apiRetries++;
				update_option('wp-short-pixel-api-retries', $apiRetries);
				exit('Timed out while processing. (pass '.$apiRetries.')');	
			}
		}

		$response = $this->doRequests($url, $filePaths, $ID);//send requests to API
		if(!$response) return $response;

		if($response['response']['code'] != 200) {//response <> 200 -> there was an error apparently?
			printf('ShortPixel API service accesibility error. Please try again later.');
			return false;
		}

		$data = (array)$this->parseResponse($response);//get the actual response from API, convert it to an array if it is an object
		
		if ( isset($data[0]) )//API returned image details
		{
			$firstImage = $data[0];//extract as object first image
			foreach ( $data as $imageObject )
			{	//this part makes sure that all the sizes were processed and ready to be downloaded
				if 	( $imageObject->Status->Code == 0 || $imageObject->Status->Code == 1  )
				{
					sleep(2);
					return $this->processImage($url, $filePaths, $ID, $startTime);	
				}		
			}
			
			switch($firstImage->Status->Code) {
			case 1:
				//handle image has been scheduled
				sleep(1);
				return $this->processImage($url, $filePaths, $ID, $startTime);
				break;
			case 2:
				//handle image has been processed
				$this->handleSuccess($data, $url, $filePaths, $ID);
				break;
			default:
				//handle error
				if ( isset($data[0]->Status->Message) )
					return $data[0]->Status->Message;
			}
		}
		else//API returned an error
		{
			switch($data['Status']->Code) {
			case -403:
				return 'Quota exceeded</br>';
				break;
			case -401:
				return 'Wrong API Key</br>';
				break;
			case -302:
				return 'Images does not exists</br>';
				break;
			default:
				//handle error
				if ( isset($data[0]->Status->Message) )
					return $data[0]->Status->Message;
			}
			
		}
		
		return $data;
	}


	public function handleSuccess($callData, $url, $filePath, $ID) {

		$counter = 0;
		if($this->_compressionType)
			{
				$fileType = "LossyURL";
				$fileSize = "LossySize";
			}	
		else
			{
				$fileType = "LosslessURL";
				$fileSize = "LoselessSize";
			}

			foreach ( $callData as $fileData )//download each file from array and process it
			{
				if ( $fileData->Status->Code == 2 ) //file was processed OK
				{
					if ( $counter == 0 )//save percent improvement for main file
						$percentImprovement = $fileData->PercentImprovement;
				
					$correctFileSize = $fileData->$fileSize;
					$tempFiles[$counter] = download_url(urldecode($fileData->$fileType));
					
					if(is_wp_error( $tempFiles[$counter] )) //also tries with http instead of https
					{
						sleep(1);
						$tempFiles[$counter] = download_url(str_replace('https://', 'http://', urldecode($fileData->$fileType)));
					}	
					
					if ( is_wp_error( $tempFiles[$counter] ) ) {
						@unlink($tempFiles[$counter]);
						return sprintf("Error downloading file (%s)", $tempFiles[$counter]->get_error_message());
						die;
					}
	
					//check response so that download is OK
					if( filesize($tempFiles[$counter]) != $correctFileSize) {
						return sprintf("Error downloading file - incorrect file size");
						die;
					}
	
					if (!file_exists($tempFiles[$counter])) {
						return sprintf("Unable to locate downloaded file (%s)", $tempFiles[$counter]);
						die;
					}
				}	
				else //there was an error while trying to download a file
					$tempFiles[$counter] = "";
				$counter++;
			}

			
			//if backup is enabled
			if(get_option('wp-short-backup_images')) 
			{
				$imageIndex = 0;
				$uploadDir = wp_upload_dir();
	
				if(!file_exists(SP_BACKUP_FOLDER) && !mkdir(SP_BACKUP_FOLDER, 0777, true)) {
					return sprintf("Backup folder does not exist and it could not be created");
				}
				$meta = wp_get_attachment_metadata($ID);
				$SubDir = ( isset($meta['file']) ) ? trim(substr($meta['file'],0,strrpos($meta['file'],"/")+1)) : "";
				$source = $filePath;

				if ( empty($SubDir) ) //its a PDF?
				{
					$uploadFilePath = get_attached_file($ID);
					$tmp = str_replace($uploadDir['basedir'],"", $uploadFilePath);
					$SubDir = trim(substr($tmp,0,strrpos($tmp,"/")));

					//create destination dir if it isn't already created
					@mkdir( SP_BACKUP_FOLDER . $SubDir, 0777, true);
					$destination[$imageIndex] = SP_BACKUP_FOLDER . $SubDir . DIRECTORY_SEPARATOR . basename($uploadFilePath);
											
				}
				else //it is not PDF, its an image
				{
					@mkdir( SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR. $SubDir, 0777, true);
					$destination[$imageIndex] = SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . basename($source[$imageIndex]);//for main file
	
					foreach ( $meta['sizes'] as $pictureDetails )
					{
						$imageIndex++;
						$source[$imageIndex] = $uploadDir['basedir'] . DIRECTORY_SEPARATOR . $SubDir . $pictureDetails['file'];
						$destination[$imageIndex] = SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . basename($source[$imageIndex]);
					}
				}
				
				if(is_writable(SP_BACKUP_FOLDER)) {
					if(!file_exists($destination[0])) 
					{					
						foreach ( $source as $imageIndex => $fileSource )
						{
							$fileDestination = $destination[$imageIndex];
							@copy($fileSource, $fileDestination);
						}			
					}
				} else {
					return sprintf("Backup folder exists but is not writable");
				}
	
			}//end backup section

		$counter = 0;
		$meta = wp_get_attachment_metadata($ID);//we'll need the metadata for subdir
		if ( !isset($meta['file']) )//it is likely a PDF file so we treat this differently
		{
			global  $wpdb;
			$qry = "SELECT * FROM " . $wpdb->prefix . "postmeta
                WHERE  (
					post_id = $ID AND
					meta_key = '_wp_attached_file'
					)";
			$idList = $wpdb->get_results($qry);
			$metaPDF = $idList[0];	
			$SubDir = trim(substr($metaPDF->meta_value,0,strrpos($metaPDF->meta_value,"/")+1));
		}
		else //its an image
			$SubDir = trim(substr($meta['file'],0,strrpos($meta['file'],"/")+1));
			
		
		foreach ( $tempFiles as $tempFile )//overwrite the original files with the optimized ones
		{ 
			
			$sourceFile = $tempFile;
			$destinationFile = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $SubDir . basename($url[$counter]);	
			
			if ( $sourceFile <> "" && file_exists($sourceFile) )//possibly there was an error and the file couldn't have been processed
			{
				@unlink( $destinationFile );			
				$success = @rename( $sourceFile, $destinationFile );
		
				if (!$success) {
					$copySuccess = copy($sourceFile, $destinationFile);
					unlink($sourceFile);
				}
			}
			else
				{
					$success = 0;
					$copySuccess = 0;
				}
		
			//save data to counters
			if( $success || $copySuccess) {
				//update statistics
				$fileData = $callData[$counter];
				$savedSpace = $fileData->OriginalSize - $fileData->LossySize;

				update_option(
					'wp-short-pixel-savedSpace',
					get_option('wp-short-pixel-savedSpace') + $savedSpace
				);
				$averageCompression = get_option('wp-short-pixel-averageCompression') * get_option('wp-short-pixel-fileCount');
				$averageCompression += $fileData->PercentImprovement;
				$averageCompression = $averageCompression /  (get_option('wp-short-pixel-fileCount') + 1);
				update_option('wp-short-pixel-averageCompression', $averageCompression);
				update_option('wp-short-pixel-fileCount', get_option('wp-short-pixel-fileCount')+1);
	
			}
			$counter++;
		}	

		//update metadata
		if(isset($ID)) {
			$meta = wp_get_attachment_metadata($ID);
			$meta['ShortPixelImprovement'] = round($percentImprovement,2);
			wp_update_attachment_metadata($ID, $meta);
		}
	
		//we reset the retry counter in case of success
		update_option('wp-short-pixel-api-retries', 0);
		//set this file as processed -> we decrement the cursor
		update_option("wp-short-pixel-query-id-start", $ID - 1);//update max ID	
	
	}//end handleSuccess

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
}
