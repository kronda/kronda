<?php
class SelectList extends Field
{
	public function __construct($featureName = false, $duplicateCounter = false, $value = false, $fieldSettings = false)
	{
		$this->singleField = true;
		$this->SetField();			

    $fieldSettings['selectListSet'] = false;
    foreach( $fieldSettings['selectListValues'] as $id => $text )
    {
      $selectOptions .= '<option value="'.$id.'"';
      if( $value == $id )
      {
        $fieldSettings['selectListSet'] = true;
        $selectOptions .= ' selected="selected"';
      }
      $selectOptions .= '>'.$text.'</option>';
    }
    $value = $selectOptions;


		$this->SetFieldSettings($featureName,$duplicateCounter,$value,$fieldSettings);
		
		$this->output = $this->field;		
	}
	
	public function SetField()
	{
		$this->metaBoxField = true;
		$this->fieldType = __( 'Select list', 'developer-tools' );
		$this->fieldBeginning = '<select ';
		$this->fieldBeforeValue = '><option value=""></option>';
		$this->fieldEnd = '</select>';
	}	
}