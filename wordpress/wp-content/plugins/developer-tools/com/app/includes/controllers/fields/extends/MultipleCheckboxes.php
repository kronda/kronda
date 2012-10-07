<?php 
class MultipleCheckboxes extends Checkbox
{
	public function __construct($featureName = false, $duplicateCounter = false, $value = false, $fieldSettings = false)
	{	
		$this->featureName = $featureName;
		$this->singleField = false;
		$this->SetField();
		$this->output .= '<div class="single_field in_multiple_checkboxes_field">';
		if( $fieldSettings['label'] )
			$this->output .= '<label class="multiple_checkboxes_label">'.$fieldSettings['label'].'</label>';
		$checkboxes = explode("\n", $fieldSettings['data']);
		foreach( $checkboxes as $checkbox )
		{
			if( $checkbox != null && $checkbox != '' )
			{
				$checkboxItems = explode('|',$checkbox);
				if( count($checkboxItems) == 2 )
				{
					if( $value && in_array($checkboxItems[0], $value) )
						$this->fieldChecked = 'checked="checked"';
					else
						$this->fieldChecked = false;
					$fieldSettings['label'] = $checkboxItems[1];
					$fieldSettings['checkboxID'] = $checkboxItems[0];			
					$this->SetFieldSettings($featureName,$duplicateCounter,$checkboxItems[0],$fieldSettings);
					$this->output .= $this->field;
				}
			}
		}
		$this->output .= '</div>';
		$this->fieldType = __( 'Multiple checkboxes', 'developer-tools' );
	}
}