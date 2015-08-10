<?php
if ( !function_exists( 'download_url' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

class ShortPixelAPI {
    
    const STATUS_SUCCESS = 1;
    const STATUS_UNCHANGED = 0;
    const STATUS_ERROR = -1;
    const STATUS_FAIL = -2;
    const STATUS_QUOTA_EXCEEDED = -3;
    const STATUS_SKIP = -4;
    const STATUS_NOT_FOUND = -5;

    private $_apiKey = '';
    private $_compressionType = '';
    private $_CMYKtoRGBconversion = '';
    private $_maxAttempts = 10;
    private $_apiEndPoint = 'https://api.shortpixel.com/v2/reducer.php';

    public function setCompressionType($compressionType) {
        $this->_compressionType = $compressionType;
    }

    public function setCMYKtoRGB($CMYK2RGB) {
        $this->_CMYKtoRGBconversion = $CMYK2RGB;
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

    public function __construct($apiKey, $compressionType, $CMYK2RGB) {
        $this->_apiKey = $apiKey;
        $this->setCompressionType($compressionType);
        $this->setCMYKtoRGB($CMYK2RGB);
        add_action('processImageAction', array(&$this, 'processImageAction'), 10, 4);
    }

    public function processImageAction($url, $filePaths, $ID, $time) {
        $this->processImage($URLs, $PATHs, $ID, $time);
    }

    public function doRequests($URLs, $Blocking, $ID) {
        
        $requestParameters = array(
            'plugin_version' => PLUGIN_VERSION,
            'key' => $this->_apiKey,
            'lossy' => $this->_compressionType,
            'cmyk2rgb' => $this->_CMYKtoRGBconversion,
            'urllist' => $URLs
        );
        $arguments = array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 3,
            'sslverify' => false,
            'httpversion' => '1.0',
            'blocking' => $Blocking,
            'headers' => array(),
            'body' => json_encode($requestParameters),
            'cookies' => array()
        );

        $response = wp_remote_post($this->_apiEndPoint, $arguments );
        
        //only if $Blocking is true analyze the response
        if ( $Blocking )
        {
            //there was an error, save this error inside file's SP optimization field
            if ( is_object($response) && get_class($response) == 'WP_Error' ) 
            {
                $errorMessage = $response->errors['http_request_failed'][0];
                $errorCode = 503;
            }
            elseif ( isset($response['response']['code']) && $response['response']['code'] <> 200 )
            {
                $errorMessage = $response['response']['code'] . " - " . $response['response']['message'];
                $errorCode = $response['response']['code'];
            }
            
            if ( isset($errorMessage) )
            {//set details inside file so user can know what happened
                $meta = wp_get_attachment_metadata($ID);
                $meta['ShortPixelImprovement'] = 'Error: <i>' . $errorMessage . '</i>';
                unset($meta['ShortPixel']['WaitingProcessing']);
                wp_update_attachment_metadata($ID, $meta);
                return array("response" => array("code" => $errorCode, "message" => $errorMessage ));
            }
            
            return $response;//this can be an error or a good response
        }
        
        return $response;
    }

    public function parseResponse($response) {
        $data = $response['body'];
        $data = $this->parseJSON($data);
        return (array)$data;
    }

    //handles the processing of the image using the ShortPixel API
    public function processImage($URLs, $PATHs, $ID = null, $startTime = 0) 
    {    
        
        $PATHs = self::CheckAndFixImagePaths($PATHs);//check for images to make sure they exist on disk
        if ( $PATHs === false )
            return array("Status" => self::STATUS_SKIP, "Message" => 'The file(s) do not exist on disk, Image #$ID');
        
        //tries multiple times (till timeout almost reached) to fetch images.
        if($startTime == 0) { 
            $startTime = time(); 
        }        
        $apiRetries = get_option('wp-short-pixel-api-retries');
        if( time() - $startTime > MAX_EXECUTION_TIME) 
        {//keeps track of time
            if ( $apiRetries > MAX_API_RETRIES )//we tried to process this time too many times, giving up...
            {
                $meta = wp_get_attachment_metadata($ID);
                $meta['ShortPixelImprovement'] = 'Timed out while processing.';
                unset($meta['ShortPixel']['WaitingProcessing']);
                update_option('wp-short-pixel-api-retries', 0);//fai added to solve a bug?
                wp_update_attachment_metadata($ID, $meta);
                return array("Status" => self::STATUS_SKIP, "Message" => 'Skip this image, tries the next one.');                
            }
            else
            {//we'll try again next time user visits a page on admin panel
                $apiRetries++;
                update_option('wp-short-pixel-api-retries', $apiRetries);
                return array("Status" => self::STATUS_ERROR, "Message" => 'Timed out while processing. (pass '.$apiRetries.')');   
            }
        }
        $response = $this->doRequests($URLs, true, $ID);//send requests to API
    
        if($response['response']['code'] != 200)//response <> 200 -> there was an error apparently?
            return array("Status" => self::STATUS_FAIL, "Message" => "There was an error and your request was not processed.");
        
        $APIresponse = $this->parseResponse($response);//get the actual response from API, its an array

        if ( isset($APIresponse[0]) )//API returned image details
        {
            foreach ( $APIresponse as $imageObject )//this part makes sure that all the sizes were processed and ready to be downloaded
            {
                if     ( $imageObject->Status->Code == 0 || $imageObject->Status->Code == 1  )
                {
                    sleep(1);
                    return $this->processImage($URLs, $PATHs, $ID, $startTime);    
                }        
            }
            
            $firstImage = $APIresponse[0];//extract as object first image
            switch($firstImage->Status->Code) 
            {
            case 2:
                //handle image has been processed
                update_option( 'wp-short-pixel-quota-exceeded', 0);//reset the quota exceeded flag
                return $this->handleSuccess($APIresponse, $URLs, $PATHs, $ID);
                break;
            default:
                //handle error
                if ( !file_exists($PATHs[0]) )
                    return array("Status" => self::STATUS_NOT_FOUND, "Message" => "File not found on disk.");
                elseif ( isset($APIresponse[0]->Status->Message) ) 
                    return array("Status" => self::STATUS_FAIL, "Message" => "There was an error and your request was not processed (" . $APIresponse[0]->Status->Message . ").");                
                
                return array("Status" => self::STATUS_FAIL, "Message" => "There was an error and your request was not processed");
                break;
            }
        }
        
        switch($APIresponse['Status']->Code) 
        {   
            
            case -403:
                @delete_option('bulkProcessingStatus');
                update_option( 'wp-short-pixel-quota-exceeded', 1);
                return array("Status" => self::STATUS_QUOTA_EXCEEDED, "Message" => "Quota exceeded.");
                break;                
        }
        
        //sometimes the response array can be different
        if ( is_numeric($APIresponse['Status']->Code) )
            return array("Status" => self::STATUS_FAIL, "Message" => $APIresponse['Status']->Message);
        else
            return array("Status" => self::STATUS_FAIL, "Message" => $APIresponse[0]->Status->Message);
        
    }
    
    public function handleDownload($fileData,$counter){
        //var_dump($fileData);
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
        
        //if there is no improvement in size then we do not download this file
        if ( $fileData->OriginalSize == $fileData->$fileSize )
            return array("Status" => self::STATUS_UNCHANGED, "Message" => "File wasn't optimized so we do not download it.");
        
        $correctFileSize = $fileData->$fileSize;
        $tempFiles[$counter] = download_url(urldecode($fileData->$fileType));
        //var_dump($tempFiles);
                
        if(is_wp_error( $tempFiles[$counter] )) //also tries with http instead of https
        {
            $tempFiles[$counter] = download_url(str_replace('https://', 'http://', urldecode($fileData->$fileType)));
        }    
        //on success we return this
        $returnMessage = array("Status" => self::STATUS_SUCCESS, "Message" => $tempFiles[$counter]);
        
        if ( is_wp_error( $tempFiles[$counter] ) ) {
            @unlink($tempFiles[$counter]);
            $returnMessage = array("Status" => self::STATUS_ERROR, "Message" => "Error downloading file " . $tempFiles[$counter]->get_error_message());
        } 
        //check response so that download is OK
        elseif( filesize($tempFiles[$counter]) != $correctFileSize) {
            $size = filesize($tempFiles[$counter]);
            @unlink($tempFiles[$counter]);
            $returnMessage = array("Status" => self::STATUS_ERROR, "Message" => "Error downloading file - incorrect file size (downloaded: {$size}, correct: {$correctFileSize} )");
        }
        elseif (!file_exists($tempFiles[$counter])) {
            $returnMessage = array("Status" => self::STATUS_ERROR, "Message" => "Unable to locate downloaded file " . $tempFiles[$counter]);
        }
        return $returnMessage;        
    }

    public function handleSuccess($APIresponse, $URLs, $PATHs, $ID) {
        $counter = $savedSpace =  $originalSpace =  $optimizedSpace =  $averageCompression = 0;

        //download each file from array and process it
        foreach ( $APIresponse as $fileData )
        {
            if ( $fileData->Status->Code == 2 ) //file was processed OK
            {
                if ( $counter == 0 )//save percent improvement for main file
                    $percentImprovement = $fileData->PercentImprovement;
                else //count thumbnails only
                    update_option( 'wp-short-pixel-thumbnail-count', get_option('wp-short-pixel-thumbnail-count') + 1 );
                $downloadResult = $this->handleDownload($fileData,$counter);
                //when the status is STATUS_UNCHANGED we just skip the array line for that one
                if ( $downloadResult['Status'] == self::STATUS_SUCCESS ) {
                    $tempFiles[$counter] = $downloadResult['Message'];
                } 
                elseif ( $downloadResult['Status'] <> self::STATUS_UNCHANGED ) 
                    return array("Status" => $downloadResult['Status'], "Message" => $downloadResult['Message']);
            }    
            else //there was an error while trying to download a file
                $tempFiles[$counter] = "";
                
            $counter++;
        }

        //figure out in what SubDir files should land
        $SubDir = $this->returnSubDir(get_attached_file($ID));
        
        //if backup is enabled - we try to save the images
        if( get_option('wp-short-backup_images') )
        {
            $uploadDir = wp_upload_dir();
            $source = $PATHs;//array with final paths for this files

            if( !file_exists(SP_BACKUP_FOLDER) && !@mkdir(SP_BACKUP_FOLDER, 0777, true) ) {//creates backup folder if it doesn't exist
                return array("Status" => self::STATUS_FAIL, "Message" => "Backup folder does not exist and it cannot be created");
            }
            //create subdir in backup folder if needed
            @mkdir( SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir, 0777, true);
            
            foreach ( $source as $fileID => $filePATH )//create destination files array
            {
                $destination[$fileID] = SP_BACKUP_FOLDER . DIRECTORY_SEPARATOR . $SubDir . self::MB_basename($source[$fileID]);     
            }
            
            //now that we have original files and where we should back them up we attempt to do just that
            if(is_writable(SP_BACKUP_FOLDER)) 
            {
                foreach ( $destination as $fileID => $filePATH )
                {
                    if ( !file_exists($filePATH) )
                    {                        
                        if ( !@copy($source[$fileID], $destination[$fileID]) )
                        {//file couldn't have been saved in backup folder
                            ShortPixelAPI::SaveMessageinMetadata($ID, 'Cannot save file <i>' . self::MB_basename($source[$fileID]) . '</i> in backup directory');
                            return array("Status" => self::STATUS_FAIL, "Message" => 'Cannot save file <i>' . self::MB_basename($source[$fileID]) . '</i> in backup directory');
                        }
                    }
                }
            } else {//cannot write to the backup dir, return with an error
                ShortPixelAPI::SaveMessageinMetadata($ID, 'Cannot save file in backup directory');
                return array("Status" => self::STATUS_FAIL, "Message" => 'Cannot save file in backup directory');
            }

        }//end backup section


        $writeFailed = 0;
        
        if ( !empty($tempFiles) )
        {
            //overwrite the original files with the optimized ones
            foreach ( $tempFiles as $tempFileID => $tempFilePATH )
            { 
                if ( file_exists($tempFilePATH) && file_exists($PATHs[$tempFileID]) && is_writable($PATHs[$tempFileID]) )
                {
                    copy($tempFilePATH, $PATHs[$tempFileID]);
                    @unlink($tempFilePATH);
                }
                else
                    $writeFailed++;
                
                if ( $writeFailed > 0 )//there was an error
                {
                    ShortPixelAPI::SaveMessageinMetadata($ID, 'Error: optimized version of ' . $writeFailed . ' file(s) couldn\'t be updated.');
                    update_option('bulkProcessingStatus', "error");
                    return array("Status" => self::STATUS_FAIL, "Message" => 'Error: optimized version of ' . $writeFailed . ' file(s) couldn\'t be updated.');
                }
                else
                {//all files were copied, optimization data regarding the savings locally in DB
                    $fileType = ( $this->_compressionType ) ? "LossySize" : "LoselessSize";
                    $savedSpace += $APIresponse[$tempFileID]->OriginalSize - $APIresponse[$tempFileID]->$fileType;
                    $originalSpace += $APIresponse[$tempFileID]->OriginalSize;
                    $optimizedSpace += $APIresponse[$tempFileID]->$fileType;
                    $averageCompression += $fileData->PercentImprovement;
                    
                    //add the number of files with < 5% optimization
                    if ( ( ( 1 - $APIresponse[$tempFileID]->$fileType/$APIresponse[$tempFileID]->OriginalSize ) * 100 ) < 5 )
                        update_option( 'wp-short-pixel-files-under-5-percent', get_option('wp-short-pixel-files-under-5-percent') + 1); 
                        
                }
            }        
        }
        //old average counting
        update_option('wp-short-pixel-savedSpace', get_option('wp-short-pixel-savedSpace') + $savedSpace);
        $averageCompression = get_option('wp-short-pixel-averageCompression') * get_option('wp-short-pixel-fileCount');
        $averageCompression = $averageCompression /  (get_option('wp-short-pixel-fileCount') + count($APIresponse));
        update_option('wp-short-pixel-averageCompression', $averageCompression);
        update_option('wp-short-pixel-fileCount', get_option('wp-short-pixel-fileCount') + count($APIresponse));
        //new average counting
        update_option('wp-short-pixel-total-original', get_option('wp-short-pixel-total-original') + $originalSpace);
        update_option('wp-short-pixel-total-optimized', get_option('wp-short-pixel-total-optimized') + $optimizedSpace);
        //update metadata for this file
        $meta = wp_get_attachment_metadata($ID);
        $meta['ShortPixelImprovement'] = round($percentImprovement,2);
        wp_update_attachment_metadata($ID, $meta);
        //we reset the retry counter in case of success
        update_option('wp-short-pixel-api-retries', 0);
        
        return array("Status" => self::STATUS_SUCCESS, "Message" => 'Success: No pixels remained unsqueezed :-)', "PercentImprovement" => $percentImprovement);
    }//end handleSuccess
    
    static public function returnSubDir($file)//return subdir for that particular attached file
    {
        $Atoms = explode("/", $file);
        $Counter = count($Atoms);
        $SubDir = $Atoms[$Counter-3] . DIRECTORY_SEPARATOR . $Atoms[$Counter-2] . DIRECTORY_SEPARATOR;

        return $SubDir;
    }
    
    //a basename alternative that deals OK with multibyte charsets (e.g. Arabic)
    static public function MB_basename($Path){
        $Separator = " qq ";
        $Path = preg_replace("/[^ ]/u", $Separator."\$0".$Separator, $Path);
        $Base = basename($Path);
        $Base = str_replace($Separator, "", $Base);
        return $Base;  
    }
    
    //sometimes, the paths to the files as defined in metadata are wrong, we try to automatically correct them
    static public function CheckAndFixImagePaths($PATHs){
        
        $ErrorCount = 0;
        $uploadDir = wp_upload_dir();
        $Tmp = explode("/", $uploadDir['basedir']);
        $TmpCount = count($Tmp);
        $StichString = $Tmp[$TmpCount-2] . "/" . $Tmp[$TmpCount-1];
        //files exist on disk?
        foreach ( $PATHs as $Id => $File )
        {
            //we try again with a different path
            if ( !file_exists($File) ){
                $NewFile = $uploadDir['basedir'] . substr($File,strpos($File, $StichString)+strlen($StichString));
                if ( file_exists($NewFile) )
                    $PATHs[$Id] = $NewFile;
                else
                    $ErrorCount++;
            }
        }
        
        if ( $ErrorCount > 0 )
            return false;
        else
            return $PATHs;
        
    }
    
    
    static private function SaveMessageinMetadata($ID, $Message)
    {
        $meta = wp_get_attachment_metadata($ID);
        $meta['ShortPixelImprovement'] = $Message;
        unset($meta['ShortPixel']['WaitingProcessing']);
        wp_update_attachment_metadata($ID, $meta);
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
