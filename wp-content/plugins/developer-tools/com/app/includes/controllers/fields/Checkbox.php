<?php
class Checkbox extends Field
{	
	public function __construct($featureName = false, $duplicateCounter = false, $value = false, $fieldSettings = false)
	{	
		if( $fieldSettings['NestedChildCheckbox'] )
			$this->singleField = false;		
		else
			$this->singleField = true;
			
		$this->SetField();
		if( ( $value && !$fieldSettings['NestedCheckboxes'] ) || $fieldSettings['NestedCheckboxSet'] )
			$this->fieldChecked = 'checked="checked"';
		else
			$this->fieldChecked = false;
		
		if( !$fieldSettings['NestedCheckboxes'] )
			$value = 'on';
		
			
		$this->SetFieldSettings($featureName,$duplicateCounter,$value,$fieldSettings);
		
		$this->output = $this->field;
	}
	
	public function SetField()
	{
		$this->fieldType = __( 'Checkbox', 'developer-tools' );
		$this->fieldBeginning = '<input type="checkbox" ';
		$this->fieldBeforeValue = 'value="';
		$this->fieldAfterValue = '"';
		$this->fieldEnd = ' />';
	}
}