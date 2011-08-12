<?php
class NestedCheckboxGroups
{
	public function __construct($featureName = false, $duplicateCounter = false, $value = false, $fieldSettings = false)
	{
		foreach( $fieldSettings['data'] as $page => $values )
		{
			$this->output .='<div class="checkbox_parent">';
			if( count($values) == 1 )
			{
// TODO: Begin single checkbox, NO subpages here
				foreach( $values as $pageID => $pageName )
				{
					$newFieldSettings = $fieldSettings;
					$newFieldSettings['fieldType'] = __( 'Checkbox', 'developer-tools' );
					$newFieldSettings['name'] = 'adminPages';
					$newFieldSettings['NestedCheckboxes'] = true;
					if( is_array($value['adminPages']) && in_array($pageID, $value['adminPages'] ) )
						$newFieldSettings['NestedCheckboxSet'] = true;					
					$newFieldSettings['label'] = $pageName;
					$newFieldSettings['checkboxID'] = preg_replace('/[^A-Za-z0-9-]/', '', $pageName);
					$singleCheckbox = new Checkbox($featureName, false, $pageID, $newFieldSettings);
					$this->output .= $singleCheckbox->output;
				}
// TODO: End single checkbox, NO subpages here			
			}
			if( count($values) == 2 )
			{	
				$valuesCounter = 0;			
				foreach( $values as $pageID => $pageValues )
				{
					$valuesCounter++;
					if( $valuesCounter == 1 )
					{
						$newFieldSettings = $fieldSettings;
						$newFieldSettings['fieldType'] = __( 'Checkbox', 'developer-tools' );
						$newFieldSettings['name'] = 'adminPages';
						$newFieldSettings['NestedCheckboxes'] = true;
						if( is_array($value['adminPages']) && in_array($pageID, $value['adminPages']) )
							$newFieldSettings['NestedCheckboxSet'] = true;
						$newFieldSettings['label'] = $pageValues;
						$newFieldSettings['checkboxID'] = preg_replace('/[^A-Za-z0-9-]/', '', $pageValues);
						$singleCheckbox = new Checkbox($featureName, false, $pageID, $newFieldSettings);
						$this->output .= $singleCheckbox->output;
					}
					if( $valuesCounter == 2 )
					{
						$this->output .='<div class="checkbox_children';
						if( $newFieldSettings['NestedCheckboxSet'] )
							$this->output .= ' hidden';
						$this->output .= '">';
						
						if( $pageValues )
							foreach( $pageValues as $subPageID => $subPage )
							{					
							
								$newFieldSettings = $fieldSettings;
								$newFieldSettings['fieldType'] = __( 'Checkbox', 'developer-tools' );
								$newFieldSettings['name'] = 'adminSubPages';
								$newFieldSettings['NestedCheckboxes'] = true;
								$newFieldSettings['NestedChildCheckbox'] = true;
								if( is_array($value['adminSubPages']) && in_array($page."|".$subPageID, $value['adminSubPages']) )
									$newFieldSettings['NestedCheckboxSet'] = true;
								$newFieldSettings['label'] = $subPage;
								$newFieldSettings['checkboxID'] = preg_replace('/[^A-Za-z0-9-]/', '', $page."-".$subPageID);
								$singleCheckbox = new Checkbox($featureName, false, $page."|".$subPageID, $newFieldSettings);
								$this->output .= $singleCheckbox->output;
							}
												
						$this->output .='</div><!-- .checkbox_children -->';
					}
				}								
			}
			$this->output .='</div><!-- .checkbox_parent -->';
		}		
	}
}