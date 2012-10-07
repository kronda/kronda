<?php
class RemoveAdminMenuBar extends Feature
{
	public function SetSettings()
	{
		$this->minWpVersion			= "3.1";
		$this->title				    = __( 'Remove admin menu bar', 'developer-tools' );
		$this->information			= __( 'Remove the WordPress admin menu bar that appears on the front-end for logged in users.', 'developer-tools' );
		$this->fields				    = array(
			array( 
				'fieldType' => 'Checkbox',
				'name' => 'enabled',
				'label' => __( 'Remove', 'developer-tools' )
			),
      array( 
        'fieldType' => 'Checkbox',
        'name' => 'admin_access',
        'advanced' => true,
        'uid1accessOnly' => true,
        'label' => __( 'Do not disable for user account', 'developer-tools' ) . ': <strong>'.UID1_USERNAME.'</strong>'
      )
		);
	}
	
	public function Enabled($value)
	{
    if( CURRENT_UID == 1 && $value['admin_access'] ){}
    else{ add_filter( 'show_admin_bar', '__return_false' ); }
	}
}