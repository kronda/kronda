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
			add_action('admin_head', array(&$this, 'HideDefaultAdminHead'));
		}		
	}
	
	public function EnqueueJquery()
	{
		wp_enqueue_script('jquery');
	}
	
	public function HideDefaultAdminHeadUsers()
	{ ?>
		<script type='text/javascript'>
			if( typeof jQuery == 'function' )
				(function($){
					$(function(){
						$('#user-1').remove();
						$('.subsubsub li a').each(function(){
							if($(this).attr('href') == 'users.php' || $(this).attr('href') == 'users.php?role=administrator')
							{
								var userCount = $('.count', this).html().replace('(', '').replace(')', '');
								var newUserCount = parseInt(userCount)-1;
								$('.count', this).html('('+newUserCount+')');
							}
						});
					});
				})(jQuery);
		</script>
		<style type='text/css'> #user-1{ display: none !important; height: 0 !important; } </style>
		<?php
	}
	
	public function HideDefaultAdminHeadUsersEdit()
	{
		if($_GET['user_id'] == 1) : ?>
		<script type='text/javascript'>
			window.onload = function()
			{
				var profilePageContainer = document.getElementById('wpbody-content');
				var profilePage = document.getElementById('profile-page');
				profilePageContainer.removeChild(profilePage);
			}
			window.location = 'users.php';
		</script>
		<style type='text/css'>#profile-page{ display: none !important; height: 0 !important; }</style>
	    <?php endif;		
	}
	
	public function HideDefaultAdminHead()
	{ ?>
		<script type='text/javascript'>
			if( typeof jQuery == 'function' )
				(function($){
					$(function(){
						if($('#post_author_override').length > 0)
							$('#post_author_override option').each( function(){
								if($(this).val() == 1 ) $(this).remove();
							});
						$('#contextual-help-link-wrap, #footer-upgrade').remove();
					});
				})(jQuery);
		</script>
		<style type='text/css'> #user-1, #footer-upgrade, #contextual-help-link-wrap{ display: none !important; height: 0 !important; } </style>
	    <?php		
	}
}