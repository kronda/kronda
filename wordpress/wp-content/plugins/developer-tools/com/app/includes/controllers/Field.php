<?php
class Field
{	
	public $output;
	protected $field;
	protected $fieldType;
	protected $singleField;
	protected $fieldBeginning;
	protected $fieldBeforeValue;
	protected $fieldAfterValue;
	protected $fieldChecked;
	protected $fieldEnd;
	
	protected function SetFieldSettings($featureName,$duplicateCounter,$value,$fieldSettings)
	{
	  // begin field id
    $fieldId = $featureName;
    
    if( $duplicateCounter )
      $fieldId .= '-'.$duplicateCounter;

    if( $fieldSettings['name'] && $fieldSettings['name'] != '' /* added for multicheckbox */ )
      $fieldId .= '-'.$fieldSettings['name'];
      
    if( $fieldSettings['checkboxID'] )
      $fieldId .= '-'.$fieldSettings['checkboxID'];       

    $fieldId .= '-field';	  
	  // end field id
	  
    // begin the field html
		$this->field = '<';
		
		if( $this->singleField )
			$this->field .= 'div';
		else
			$this->field .= 'span';
		
		$this->field .= ' class="single_field';
		
		if( $this->singleField )
		{
			if( $fieldSettings['unmodifiableAfterSave'] )
			{
				$this->field .= ' unmodifiableAfterSave';
				if( $value )
					$this->field .= ' existing';
				else
					$this->field .= ' new';
			}
		}
		if( $fieldSettings['advanced'] )
			$this->field .= ' advanced '.$fieldSettings['advanced'];		
			
		$this->field .= '">';
		
		if( $fieldSettings['unmodifiableAfterSave'] )
			$this->field .= '<div class="new">';
    
		// begin field label
		if( $fieldSettings['label'] )
		{
			$this->field .= '<label';
			
			// begin for attribute
			$this->field .= ' for="'.$fieldId.'">';
			if( $fieldSettings['advanced'] )
				$this->field .= '<span class="advanced_label">' . __('Advanced', 'developer-tools' ) . '</span> ';
			$this->field .= $fieldSettings['label'];
			$this->field .= '</label>&nbsp;';
		}
		// end field label
		
    if( $fieldSettings['required'] )
    {
      $this->field .= '<a class="required_icon';
      if( ( $fieldSettings['fieldType'] != 'SelectListUploader' && strlen($value) ) || ( $fieldSettings['fieldType'] == 'SelectListUploader' && $fieldSettings['selectListSet'] ) || !$fieldSettings['featureItemEnabled'] )
        $this->field .= ' hidden';
      $this->field .= '" title="' . __( 'This is a required field', 'developer-tools') . '">!</a>';
    }		
		
		$this->field .= $this->fieldBeginning;
		
		if( $fieldId )
			$this->field .= 'id="'.$fieldId.'" ';
		
		// begin field name
		$this->field .= 'name="dt['.$featureName.']';
		if( $duplicateCounter )
			$this->field .= '['.$featureName.'-'.$duplicateCounter.']';
		
		if( $fieldSettings['name'] )
			$this->field .= '['.$fieldSettings['name'].']';
		
		if( $fieldSettings['fieldType'] == 'MultipleCheckboxes' || $fieldSettings['NestedCheckboxes'] )
			$this->field .= '[]';
		
		$this->field .= '" ';
		// end field name
		
		// begin field's css classes
	  $this->field .= 'class="'.$fieldSettings['fieldType'];

    if( $fieldSettings['cssClass'] && is_string( $fieldSettings['cssClass'] ) )
      $this->field .= ' '.str_replace( '.', null, $fieldSettings['cssClass'] );
      
    if( $fieldSettings['cssClass'] && is_array( $fieldSettings['cssClass'] ) )
      foreach( $fieldSettings['cssClass'] as $cssClass ) 
        if( is_string( $cssClass ) ) 
          $this->field .= ' '.str_replace( '.', null, $cssClass );

		if( $fieldSettings['required'] )
			$this->field .= ' required_field';
		
		if( $fieldSettings['characterSet'] )
			$this->field .= ' '.$fieldSettings['characterSet'];

		if( $fieldSettings['unmodifiableAfterSave'] )
			$this->field .= ' unmodifiableAfterSaveValue';

		if( $fieldSettings['codeReplaceClass'] )	
			$this->field .= ' '.$fieldSettings['codeReplaceClass'].' replace-text';
		
		if( $fieldSettings['uploaderClass'] )
			$this->field .= ' '.$fieldSettings['uploaderClass'];
		
    if( $fieldSettings['fieldSelector'] )
      $this->field .= ' field_selector';
      
		$this->field .= '" ';
		// end field's css classes
		
		if( $this->fieldChecked )
			$this->field .= $this->fieldChecked.' ';
		
		if( $this->fieldBeforeValue )
			$this->field .= $this->fieldBeforeValue;

		if( strlen($value) )
			$this->field .= stripcslashes( $value );

		if( $this->fieldAfterValue )
			$this->field .= $this->fieldAfterValue;

		// begin field value
		
		// end field value
		$this->field .= $this->fieldEnd;
		
    if( $fieldSettings['afterLabel'] )
      $this->field .= '<span class="after_label"> ' . $fieldSettings['afterLabel'] . '</span>'."\n";    
    
		if( $this->singleField )
		{
			if( $fieldSettings['unmodifiableAfterSave'] )
				$this->field .= '</div><!-- .new -->'."\n";
			
			if( $fieldSettings['unmodifiableAfterSave'] )
			{
				$this->field .= '<div class="existing"><div class="unmodifiable '.$fieldSettings['fieldType'].'">';
				if( $value )
					$this->field .= stripcslashes( $value );
				$this->field .= '</div></div><!-- .existing -->'."\n";
			}
			
			$this->field .= '</div>'."\n";
		}
		else
		{
			$this->field .= '</span>'."\n";
		}
	}		
}