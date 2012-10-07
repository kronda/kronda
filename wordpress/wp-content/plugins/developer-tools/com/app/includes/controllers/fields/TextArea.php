<?php
class TextArea extends Field
{	
	public function __construct($featureName = false, $duplicateCounter = false, $value = false, $fieldSettings = false)
	{
		$this->singleField = true;
		$this->SetField();	
		
		$this->SetFieldSettings($featureName,$duplicateCounter,$value,$fieldSettings);
		
		$this->output = $this->field;
	}
	
	public function SetField()
	{
		$this->fieldType = __( 'Textarea', 'developer-tools' );
		$this->fieldBeginning = '<textarea ';
		$this->fieldBeforeValue = '>';
		$this->fieldEnd = '</textarea>';
	}	
}