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

	public function processImageAction($url, $filePath, $ID, $time) {
		$this->processImage($url, $filePath, $ID, $time);
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
			'key' => $this->_apiKey,
			'lossy' => $this->_compressionType,
			'urllist' => $imageList
		);
		
		$response = wp_remote_post($this->_apiEndPoint, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
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
		$data = str_replace('Warning: Division by zero in /usr/local/important/web/api.shortpixel.com/lib/functions.php on line 33', '', $data);
		$data = $this->parseJSON($data);
		return $data;
	}

	//handles the processing of the image using the ShortPixel API
	public function processImage($url, $filePath, $ID = null, $startTime = 0) {
		
		if($startTime == 0) { $startTime = time(); }		
		if(time() - $startTime > MAX_EXECUTION_TIME) {//keeps track of time
			$meta = wp_get_attachment_metadata($ID);
			$meta['ShortPixelImprovement'] = 'Could not determine compression';
			unset($meta['ShortPixel']['WaitingProcessing']);
			wp_update_attachment_metadata($ID, $meta);
			return 'Could not determine compression';
		}

		$response = $this->doRequests($url, $filePath, $ID);//send requests to API
		if(!$response) return $response;

		if($response['response']['code'] != 200) {//response <> 200 -> there was an error apparently?
			printf('ShortPixel API service accesibility error. Please try again later.');
			return false;
		}

		$data = $this->parseResponse($response);//get the actual  response from API
		
		if( !is_array($data) ) {// the answer from API isn't good
			printf('Web service returned an error. Please try again later.');
			return false;
		}
		
		$firstImage = $data[0];//extract as object first image
		
		switch($firstImage->Status->Code) {
			case 1:
				//handle image has been scheduled
				sleep(1);
				return $this->processImage($url, $filePath, $ID, $startTime);
				break;
			case 2:
				//handle image has been processed
				$this->handleSuccess($data, $url, $filePath, $ID);
				break;
			case -403:
				return 'Quota exceeded</br>';
			case -401:
				return 'Wrong API Key</br>';
			case -302:
				return 'Images does not exists</br>';
			default:
				//handle error
				return $data->Status->Message;
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
			
			if ( $counter == 0 )//save percent improvement for main file
				$percentImprovement = $fileData->PercentImprovement;

			$correctFileSize = $fileData->$fileSize;
			$tempFiles[$counter] = download_url(urldecode($fileData->$fileType));
			
			if(is_wp_error( $tempFiles[$counter] )) //also tries with http instead of https
				$tempFiles[$counter] = download_url(str_replace('https://', 'http://', urldecode($fileData->$fileType)));
				
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
			$source[$imageIndex] = $filePath;
			
			//create destination dir if it isn't already created
			@mkdir( SP_BACKUP_FOLDER . $uploadDir['subdir'], 0777, true);
			
			$destination[$imageIndex] = SP_BACKUP_FOLDER . $uploadDir['subdir'] . DIRECTORY_SEPARATOR . basename($source[$imageIndex]);

			foreach ( $meta['sizes'] as $pictureDetails )
			{
				$imageIndex++;
				$source[$imageIndex] = $uploadDir['path'] . DIRECTORY_SEPARATOR . $pictureDetails['file'];
				$destination[$imageIndex] = SP_BACKUP_FOLDER . $uploadDir['subdir'] . DIRECTORY_SEPARATOR . basename($source[$imageIndex]);

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
		$SubDir = trim(substr($meta['file'],0,strrpos($meta['file'],"/")+1));
		if ( strlen($SubDir) == 0 )//it is likely a PDF file so we treat this differently
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
		
		foreach ( $tempFiles as $tempFile )
		{ 
			
			$sourceFile = $tempFile;
			$destinationFile = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $SubDir . basename($url[$counter]);	
			
			@unlink( $destinationFile );			
			$success = @rename( $sourceFile, $destinationFile );
	
			if (!$success) {
				$copySuccess = copy($sourceFile, $destinationFile);
				unlink($sourceFile);
			}
	
			//save data to counters
			if($success || $copySuccess) {
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
			$meta['ShortPixelImprovement'] = $percentImprovement;
			wp_update_attachment_metadata($ID, $meta);
		}
		

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
