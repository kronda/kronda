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

	public function doRequests($url, $filePath, $ID = null) {
		$response = $this->doBulkRequest(array($url), true);

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
		if(time() - $startTime > MAX_EXECUTION_TIME) {
			$meta = wp_get_attachment_metadata($ID);
			$meta['ShortPixelImprovement'] = 'Could not determine compression';
			unset($meta['ShortPixel']['WaitingProcessing']);
			wp_update_attachment_metadata($ID, $meta);
			return 'Could not determine compression';
		}

		$response = $this->doRequests($url, $filePath, $ID);

		if(!$response) return $response;

		if($response['response']['code'] != 200) {
			printf('Web service did not respond. Please try again later.');
			return false;
		}

		$data = $this->parseResponse($response);
		$data = $data[0];

		if(!is_object($data) || !isset($data->Status->Code)) {
			printf('Web service returned an error. Please try again later.');
			return false;
		}

		switch($data->Status->Code) {
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

		if($this->_compressionType) {
			//lossy
			$correctFileSize = $callData->LossySize;
			$tempFile = download_url(urldecode($callData->LossyURL));
			if(is_wp_error( $tempFile )) {
				$tempFile = download_url(str_replace('https://', 'http://', urldecode($callData->LossyURL)));
			}
		} else {
			//lossless
			$correctFileSize = $callData->LoselessSize;
			$tempFile = download_url(urldecode($callData->LosslessURL));
			if(is_wp_error( $tempFile )) {
				$tempFile = download_url(str_replace('https://', 'http://', urldecode($callData->LosslessURL)));
			}
		}

		if ( is_wp_error( $tempFile ) ) {
			@unlink($tempFile);
			return sprintf("Error downloading file (%s)", $tempFile->get_error_message());
			die;
		}

		//check response so that download is OK
		if(filesize($tempFile) != $correctFileSize) {
			return sprintf("Error downloading file - incorrect file size");
			die;
		}

		if (!file_exists($tempFile)) {
			return sprintf("Unable to locate downloaded file (%s)", $tempFile);
			die;
		}

		//if backup is enabled
		if(get_option('wp-short-backup_images')) {

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
		}



/////////////////////////


		@unlink( $filePath );
		$success = @rename( $tempFile, $filePath );

		if (!$success) {
			$copySuccess = copy($tempFile, $filePath);
			unlink($tempFile);
		}

		if($success || $copySuccess) {
			//update statistics
			if(isset($callData->LossySize)) {
				$savedSpace = $callData->OriginalSize - $callData->LossySize;
			} else {
				$savedSpace = $callData->OriginalSize - $callData->LoselessSize;
			}

			update_option(
				'wp-short-pixel-savedSpace',
				get_option('wp-short-pixel-savedSpace') + $savedSpace
			);
			$averageCompression = get_option('wp-short-pixel-averageCompression') * get_option('wp-short-pixel-fileCount');
			$averageCompression += $callData->PercentImprovement;
			$averageCompression = $averageCompression /  (get_option('wp-short-pixel-fileCount') + 1);
			update_option('wp-short-pixel-averageCompression', $averageCompression);
			update_option('wp-short-pixel-fileCount', get_option('wp-short-pixel-fileCount')+1);

			//update metadata
			if(isset($ID)) {
				$meta = wp_get_attachment_metadata($ID);
				$meta['ShortPixelImprovement'] = $callData->PercentImprovement;
				wp_update_attachment_metadata($ID, $meta);
			}
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
