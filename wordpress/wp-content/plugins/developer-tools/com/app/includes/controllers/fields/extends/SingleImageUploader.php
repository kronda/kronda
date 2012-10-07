<?php
class SingleImageUploader extends Uploader
{	
	public function __construct($featureName = false, $duplicateCounter = false, $value = false, $fieldSettings = false)
	{
		$this->singleField = true;
		
		//This must happen before $this->SetField();
		$this->setUploader( $fieldSettings['description'], $featureName, $duplicateCounter, $value);
		
		$this->SetField();
		
		// This is required so the image and image preview section get populated after upload (with jquery)
		$fieldSettings['uploaderClass'] = $featureName.( $duplicateCounter ? '-'.$duplicateCounter : '' ).'_fileupload';	
		
		$this->SetFieldSettings($featureName,$duplicateCounter,$value,$fieldSettings);
		
		$this->output = $this->field;
	}
	
	public function SetField()
	{
		$this->fieldType = __( 'Image uploader', 'developer-tools' );	
		$this->fieldBeginning = '<a class="upload_new upload-new-image-file button-primary">' . __( 'Upload new', 'developer-tools' ) . '</a>';
		$this->fieldBeginning .= '<div class="clear"></div><div class="image_legend">' . __( 'Image preview', 'developer-tools' ) . '</div><div class="clear"></div>';
		$this->fieldBeginning .= '<div class="'.$this->featureName.( $this->duplicateCounter ? '-'.$this->duplicateCounter : '' ).'_fileupload image_preview">';
		$this->fieldBeginning .= '<img src="'.DEVELOPER_TOOLS_UPLOADS_URL.$this->featureName.'/'.$this->value.'"';
		if( $this->uploadDescription )
			$this->fieldBeginning .= ' alt="'.$this->uploadDescription.'"';
		$this->fieldBeginning .= ' /></div><div class="clear"></div>';
		$this->fieldBeginning .= '<input type="hidden" ';
		$this->fieldBeforeValue = 'value="';
		$this->fieldAfterValue = '"';
		$this->fieldEnd = ' />';
	}	
}