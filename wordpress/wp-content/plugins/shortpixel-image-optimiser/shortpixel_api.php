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
				update_option( 'wp-short-pixel-quota-exceeded', 0);//reset the quota exceeded flag
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
				update_option("wp-short-pixel-query-id-start", 0);//update max and min ID			
				update_option("wp-short-pixel-query-id-stop", 0);
				@delete_option('bulkProcessingStatus');
				update_option( 'wp-short-pixel-quota-exceeded', 1);
				return 'Quota exceeded';
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
			
			//generate SubDir for this file
			$meta = wp_get_attachment_metadata($ID);
			if ( empty($meta['file']) )//file has no metadata attached (like PDF files uploaded before SP plugin)
				{
					$attachedFilePath = get_attached_file($ID);
					$SubDir = $this->returnSubDir($attachedFilePath);
				}
			else
				{
					$SubDir = $this->returnSubDir($meta['file']);
					$source = $filePath;
				}

			//if backup is enabled
			if( get_option('wp-short-backup_images') )
			{
				$imageIndex = 0;
				$uploadDir = wp_upload_dir();
				$source = $filePath;

				if(!file_exists(SP_BACKUP_FOLDER) && !mkdir(SP_BACKUP_FOLDER, 0777, true)) {
					return sprintf("Backup folder does not exist and it could not be created");
				}
				
				//create backup dir if needed
				@mkdir( SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir, 0777, true);
				$destination[$imageIndex] = SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . basename($source[$imageIndex]);//for main file
				if ( !empty($meta['file']) )
				{
					foreach ( $meta['sizes'] as $pictureDetails )//generate paths for all the version of an image
					{
						$imageIndex++;
						$source[$imageIndex] = $uploadDir['basedir'] . DIRECTORY_SEPARATOR . $SubDir . $pictureDetails['file'];
						$destination[$imageIndex] = SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . basename($source[$imageIndex]);
					}
				}
				

				if(is_writable(SP_BACKUP_FOLDER)) {
					if(!file_exists($destination[0])) //do not overwrite backup files
					{					
						foreach ( $source as $imageIndex => $fileSource )
						{
							$fileDestination = $destination[$imageIndex];
							@copy($fileSource, $fileDestination);
						}			
					}
				} else {
					$meta = wp_get_attachment_metadata($ID);
					$meta['ShortPixelImprovement'] = 'Cannot save file in backup directory';
					wp_update_attachment_metadata($ID, $meta);
					return sprintf("Backup folder exists but is not writable");
				}
	
			}//end backup section


		$counter = 0;
		$writeFailed = 0;
		foreach ( $tempFiles as $tempFile )//overwrite the original files with the optimized ones
		{ 
			$sourceFile = $tempFile;
			$destinationFile = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $SubDir . basename($url[$counter]);
				
			if ( $sourceFile <> "" && file_exists($sourceFile) )
			{
				@unlink( $destinationFile );			
				$success = @rename( $sourceFile, $destinationFile );
		
				if (!$success) {
					$copySuccess = @copy($sourceFile, $destinationFile);
					unlink($sourceFile);
				}
			}
			else
				{
					$success = 0;
					$copySuccess = 0;
				}
		
			//save data to counters
			if( $success || $copySuccess) 
			{
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
			else
				$writeFailed++;//the file couldn't have been overwritten, we'll let the user know about this
			$counter++;
		}	

		//update metadata
		if(isset($ID)) {	
			$meta = wp_get_attachment_metadata($ID);
			if ( $writeFailed == 0 ) 
				$meta['ShortPixelImprovement'] = round($percentImprovement,2);
			else
				$meta['ShortPixelImprovement'] = 'Cannot write optimized file';
			wp_update_attachment_metadata($ID, $meta);
		}
	
		//we reset the retry counter in case of success
		update_option('wp-short-pixel-api-retries', 0);
		//set this file as processed -> we decrement the cursor
		update_option("wp-short-pixel-query-id-start", $ID - 1);//update max ID	
	
	}//end handleSuccess
	
	static public function returnSubDir($file)//return subdir for that particular attached file
	{
		
		$uploadDir = wp_upload_dir();	
		
		if ( !isset($file) || strpos($file, "/") === false )
			$SubDir = "";
		else
			$SubDir = trim(substr($file,0,strrpos($file,"/")+1));		
		
		//remove upload dir from the URL if needed
		$SubDir = str_ireplace($uploadDir['basedir'] . DIRECTORY_SEPARATOR ,"", $SubDir);

		return $SubDir;
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
}
