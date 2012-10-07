<?php
class DisableAdminMenuItems extends Feature
{
	//http://codex.wordpress.org/Function_Reference/remove_menu_page for wp 3.1.0 +	
	public function SetSettings()
	{
		$this->title				= __( 'Hide admin menu items', 'developer-tools' );
		$this->fields				= array(
			array( 
				'fieldType' => 'NestedCheckboxGroups',
				'name' => '',
				'globalVariable' => 'menuItems'
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
		$this->value = $value;
		if( CURRENT_UID == 1 && $value['admin_access'] ){}
		else{ add_action('admin_head', array(&$this,'HeadInclude')); }
	}
	
	public function HeadInclude()
	{ ?>
		<script type='text/javascript'>
			if(typeof jQuery == 'function')
				(function($){ 
					$(function(){
						if( $('#adminmenu .wp-first-item').hasClass('wp-menu-separator')) 
							$('#adminmenu .wp-first-item').remove();
					});
				})(jQuery);
		</script>
		<?php
		global $menu, $submenu, $menuItems;
		if( $this->value['adminPages'] )
			foreach($this->value['adminPages'] as $id)
				if( $id != '' )
					unset($menu[$id]);
		if( $this->value['adminSubPages'] )
			foreach($this->value['adminSubPages'] as $ids){
				if( $ids != '' ){
					$sub = explode('|', $ids);
					unset($submenu["$sub[0]"][$sub[1]]);
				}
			}		
	}
}