<?php
class HidePlugin extends Feature
{
	public function SetSettings()
	{	
		$this->uid1accessOnly		= true;	
		$this->title				    = __( 'Hide Developer Tools plugin', 'developer-tools' );
		$this->description			= __( 'Only allow user account:', 'developer-tools' ) . ' <strong>'.UID1_USERNAME.'</strong> ' . __('to access to the Developer Tools plugin.', 'developer-tools' );
		$this->fields			    	= array(
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
	  {
	    add_action('admin_init', array(&$this, 'EnqueueJquery'));
	    add_action('admin_head-plugins.php', array( &$this, 'AdminHead') );
      add_action('admin_head-plugin-editor.php', array(&$this, 'AdminHeadEdit'));
    }
	}
  
  public function EnqueueJquery()
  {
    wp_enqueue_script('jquery');
  }  
  
  public function AdminHead()
  { ?>
    <style type="text/css"> #the-list tr#developer-tools{ display: none; } </style>
    <script type="text/javascript">
      if( typeof jQuery == 'function' )
        jQuery(function($){ 
          $('#developer-tools').remove();
          $('ul.subsubsub li.all a span.count').text( '(' + $('#the-list tr').length + ')' );
          $('ul.subsubsub li.active a span.count').text( '(' + $('#the-list tr.active').length + ')' );
        });
    </script>
  <?php
  }

  public function AdminHeadEdit()
  {
    if( $_GET['file'] == 'developer-tools/DeveloperTools.php' || $_GET['plugin'] == 'developer-tools/DeveloperTools.php' ) header( "Location: plugins.php" );
  }
}