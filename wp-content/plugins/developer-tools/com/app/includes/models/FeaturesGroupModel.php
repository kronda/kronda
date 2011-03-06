<?php
class FeaturesGroupModel
{
	public $groups;
	public $groupGroups;
	
	public function __construct()
	{
		$this->_init();
	}
	
	private function _init()
	{
		$this->groupGroups = array(
		  __( 'Developer Tools', 'developer-tools' ) => 'home',
			__( 'Front-end', 'developer-tools' ) => 'front_end',
			__( 'Back-end', 'developer-tools' ) => 'back_end',
			__( 'View all', 'developer-tools' ) => 'all'
		);
		$this->groups = array(
      __( 'Why hello there...', 'developer-tools' ) =>
        array(
          'enabled' => true,        
          'uid1' => false,
          'closed' => false,          
          'form' => false,          
          'group' => 'home',
          'type' => 'view',
          'data' => 
            array(
              'view' => 'admin-ui-page-content-group-welcome' 
            )
      ),			      
       __( 'Plugin settings', 'developer-tools' ) =>
       	array(
          'enabled' => true,
          'uid1' => false,
          'closed' => true,          
          'form' => array( // Note, the 'form' value must match the nonce
            'action' => 'Save',
            'button_text' => __( 'Save', 'developer-tools' ),
            'begin' => true,
            'end' => false
          ),
          'group' => 'home',
          'type' => 'features',
          'data' => array(        
            'DtMaxUploadSize'
          )                 		
       	),       	
			__( 'Theme Libraries', 'developer-tools' ) =>
				array(
          'enabled' => true,				
				  'uid1' => false,
          'closed' => false,				  		  
				  'form' => array( // Note, the 'form' value must match the nonce
            'action' => 'Save',
            'button_text' => __( 'Save', 'developer-tools' ),            
            'begin' => false,
            'end' => false
          ),
          'group' => 'front_end',				  
				  'type' => 'features',
				  'data' => array(        
              'LoadWpCoreJavascriptLibraries',
              'EnableCufon',        
              'EnableSifr',
              'EnableSwfObject',    
              'EnableJqueryImageReflection',
              'EnableDdBelatedPngFix',
              'EnabledHtml5shiv'
          )
			),
			__( 'Front-end Configurations', 'developer-tools' ) =>
				array(
          'enabled' => true,				
				  'uid1' => false,
          'closed' => false,				  
          'form' => array( // Note, the 'form' value must match the nonce
            'action' => 'Save',
            'button_text' => __( 'Save', 'developer-tools' ),            
            'begin' => false,
            'end' => false
          ),
					'group' => 'front_end',
          'type' => 'features',
          'data' =>
						array(
						    'EnableGoogleAnalytics',
						    'OpenExternalLinksInNewWindow',
						    'RemoveAdminMenuBar',
						    'EnableJavascriptRequiredMessage'					
						)
			),
      __( 'Back-end Features', 'developer-tools' ) =>
        array(
          'enabled' => true,        
          'uid1' => false,
          'closed' => false,          
          'form' => array( // Note, the 'form' value must match the nonce
            'action' => 'Save',
            'button_text' => __( 'Save', 'developer-tools' ),            
            'begin' => false,
            'end' => false
          ),
					'group' => 'back_end',
          'type' => 'features',
          'data' =>
            array(
              'ExcerptLength',
              'AddMenu',  
              'AddSidebar',
              'AddTaxonomy',
              'AddPostType',
              'AddPostFormat',
              'EnableFeaturedImageThumbnails',            
              // 'AddMetaBoxes',  
              'DisablePostPageMetaBoxes',   
              'AddImageSize',
              'EnableImageQuality'      
            )
      ),			
			__( 'Back-end Configurations', 'developer-tools' ) => 
				array(
          'enabled' => true,				
				  'uid1' => false,
          'closed' => false,				  
          'form' => array( // Note, the 'form' value must match the nonce
            'action' => 'Save',
            'button_text' => __( 'Save', 'developer-tools' ),            
            'begin' => false,
            'end' => true
          ),
					'group' => 'back_end',
          'type' => 'features',
          'data' =>
						array(
							'HidePlugin',
							'HideDefaultAdmin',
						  'DisableWpCoreUpdates',
						  'DisablePluginUpdates',		
						  'DisableTinyMceAutoFormatting',
						  'DisableAdminMenuItems',
              'DisableDashboardWidgets',							
						  'CustomLoginImage'						
						)
			),	
			__( 'Enabled features', 'developer-tools' ) =>
				array(
          'enabled' => true,				
				  'uid1' => false,
          'closed' => false,				  
          'form' => false, 				  
          'group' => 'home',
          'type' => 'property',
          'data' => '_showEnabledFeatures'
			), 					
			__( 'Server', 'developer-tools' ) =>
			 array(
          'enabled' => true,			 
			    'uid1' => false,
          'closed' => true,			    
          'form' => false,
          'group' => 'home',
          'type' => 'method',          
          'data' => '_ServerConfiguration'     
       ),       
      __( 'Reset', 'developer-tools' ) =>
        array(
          'enabled' => true,        
          'uid1' => false,
          'closed' => true,          
          'form' => array( // Note, the 'form' value must match the nonce
            'action' => 'Reset',
            'button_text' => __( 'Reset', 'developer-tools' ),            
            'begin' => true,
            'end' => true
          ),
          'group' => 'global',
          'type' => 'view',
          'data' => 
            array(
              'view' =>'admin-ui-page-content-group-reset'
             )
      ),       
      __( 'Import', 'developer-tools' ) =>
        array(
          'enabled' => true,        
          'uid1' => false,
          'closed' => true,          
          'form' => array( // Note, the 'form' value must match the nonce
            'action' => 'Import',
            'button_text' => __( 'Import', 'developer-tools' ),            
            'begin' => true,
            'end' => true
          ),
          'group' => 'home',
          'type' => 'view',
          'data' => 
            array(
              'view' => 'admin-ui-page-content-group-import'
            )
      ),       
      __( 'Export', 'developer-tools' ) =>
        array(
          'enabled' => true,        
          'uid1' => false,
          'closed' => true,          
          'form' => array( // Note, the 'form' value must match the nonce
            'action' => 'Export',
            'button_text' => __( 'Export', 'developer-tools' ),            
            'begin' => true,
            'end' => true
          ),
          'group' => 'home',
          'type' => 'view',
          'data' => 
            array(
              'view' => 'admin-ui-page-content-group-export'
            )
      ),
      __( 'Database', 'developer-tools' ) =>
        array(
          'enabled' => true,        
          'uid1' => true,
          'closed' => true,          
          'form' => false,
          'group' => 'home',        
          'type' => 'view',
          'data' => 
            array(
              'view' => 'admin-ui-page-content-group-database',
              'viewData' => array(
                'action' => DEVELOPER_TOOLS_URL.'libs/sqlbuddy/login.php'
              )
            )
      ),      
      __( 'Debug', 'developer-tools' ) =>
        array(
          'enabled' => false,        
          'uid1' => true,
          'closed' => false,         
          'form' => false,      
          'group' => 'home',        
          'type' => 'method',
          'data' => '_KrumoSetValues'
      )      		
		);
	}
}