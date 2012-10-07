<?php
class SelectListUploader extends Uploader
{
	public function __construct($featureName = false, $duplicateCounter = false, $value = false, $fieldSettings = false)
	{
		$this->singleField = true;

// $value must use GetFilesInDirectory lib
		$filesDir = new GetFilesInDirectory($featureName);
		if( $filesDir->files )
		{
		  $fieldSettings['selectListSet'] = false;
			foreach( $filesDir->files as $file )
			{
				$selectOptions .= '<option value="'.$file.'"';
				if( $value == $file )
        {
          $fieldSettings['selectListSet'] = true;
					$selectOptions .= ' selected="selected"';
        }
				$selectOptions .= '>'.$file.'</option>';
			}
		}
		$value = $selectOptions;
		
		//This must happen before $this->SetField();
		$this->setUploader( $fieldSettings['description'], $featureName, $duplicateCounter, $value);
		
		$this->SetField();
		
		// This is required so the image and image preview section get populated after upload (with jquery)
		$fieldSettings['uploaderClass'] = 'value '.$featureName.( $duplicateCounter ? '-'.$duplicateCounter : '' ).'_fileupload';	
		
		$this->SetFieldSettings($featureName,$duplicateCounter,$value,$fieldSettings);
		
		$this->output = $this->field;
	}
	
	public function SetField()
	{
		$this->fieldType = __( 'File uploader with select list', 'developer-tools' );
		$this->fieldBeginning = '<select ';
		$this->fieldBeforeValue = '><option value="" class="red">' . __( 'Select file', 'developer-tools' ) . '</option>';
		$this->fieldBeforeValue .= '<option value="" class="upload-new-option">Upload new</option>'; // This cannot be internationalized because it is used by the javascript application
		$this->fieldEnd = '</select>';
	}	
}