<?php
class TextInput extends Field
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
		$this->fieldType = __( 'Text input', 'developer-tools' );
		$this->fieldBeginning = '<input type="text" ';
		$this->fieldBeforeValue = 'value="';
		$this->fieldAfterValue = '"';
		$this->fieldEnd = ' />';
	}	
}