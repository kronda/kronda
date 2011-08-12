<?php
class SelectList extends Field
{
	public function __construct($featureName = false, $duplicateCounter = false, $value = false, $fieldSettings = false)
	{
		$this->singleField = true;
		$this->SetField();

    $fieldSettings['selectListSet'] = false;
    $options = explode("\n", $fieldSettings['data']);
    foreach( $options as $option )
    {
      if( $option != '' && $option != null )
      {
        $optionData = explode("|", $option);
        $selectOptions .= '<option value="'.$optionData[0].'"';
        if( $value == $optionData[0] )
        {
          $fieldSettings['selectListSet'] = true;
          $selectOptions .= ' selected="selected"';
        }
        $selectOptions .= '>'.$optionData[1].'</option>';
      }
    }
    $value = $selectOptions;


		$this->SetFieldSettings($featureName,$duplicateCounter,$value,$fieldSettings);
		
		$this->output = $this->field;		
	}
	
	public function SetField()
	{
		$this->fieldType = __( 'Select list', 'developer-tools' );
		$this->fieldBeginning = '<select ';
		$this->fieldBeforeValue = '><option value=""></option>';
		$this->fieldEnd = '</select>';
	}	
}