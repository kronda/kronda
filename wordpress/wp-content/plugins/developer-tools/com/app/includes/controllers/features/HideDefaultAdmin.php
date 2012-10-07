<?php
class HideDefaultAdmin extends Feature
{
	public function SetSettings()
	{
		$this->uid1accessOnly		= true;
		$this->title				    = __( 'Hide default admin', 'developer-tools' );
		$this->description	    = __( 'Hide user account:', 'developer-tools' ) . ' <strong>'.UID1_USERNAME.'</strong> ' . __( 'from all other admin user accounts.', 'developer-tools' );
		$this->fields				    = array(
			array( 
				'fieldType' => 'Checkbox',
				'name' => 'enabled',
				'label' => __( 'Enable', 'developer-tools' )
			)
		);		
	}
								
	public function Enabled($value)
	{
		if( CURRENT_UID != 1 )
		{ // Allow default admin to see and edit their own profile
			add_action('admin_init', array(&$this, 'EnqueueJquery'));
			add_action('admin_head-users.php', array(&$this, 'HideDefaultAdminHeadUsers'));
			add_action('admin_head-user-edit.php', array(&$this, 'HideDefaultAdminHeadUsersEdit'));
		}		
	}
	
	public function EnqueueJquery()
	{
		wp_enqueue_script('jquery');
	}
	
	public function HideDefaultAdminHeadUsers()
	{ ?>
	  <style type='text/css'> #user-1{ display: none !important; height: 0 !important; } </style>
		<script type='text/javascript'>
			if( typeof jQuery == 'function' )
        jQuery(function($){
					$('#user-1').remove();
          if( $('ul.subsubsub li.administrator') ) $('ul.subsubsub li.administrator a span.count').text( '(' + $('#the-list td.column-role:contains(Administrator)').length + ')' );
          $('ul.subsubsub li.all a span.count').text( '(' + $('#the-list > tr').length + ')' );
				});
		</script>
		<?php
	}
	
	public function HideDefaultAdminHeadUsersEdit()
	{
		if($_GET['user_id'] == 1) header( "Location: users.php");		
	}
}