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
            __( 'Debug settings', 'developer-tools' ) =>
                array(
                    'enabled' => false,
                    'uid1' => true,
                    'closed' => false, 
                    'form' => false,
                    'group' => 'home',
                    'type' => 'method',
                    'data' => '_DebugSettings'
                ),
		
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
			
            __( 'JavaScript Utilities', 'developer-tools' ) =>
                array(
                    'enabled' => true,
                    'uid1' => false,
                    'closed' => false,
                    'form' => 
                        array( // Note, the 'form' value must match the nonce
                            'action' => 'Save',
                            'button_text' => __( 'Save Changes', 'developer-tools' ),
                            'begin' => true,
                            'end' => false
                        ),
                    'group' => 'front_end',
                    'type' => 'features',
                    'data' => 
                        array(
                            'LoadWpCoreJavascriptLibraries',
                            'EnableCufon',
                            'EnableSifr',
                            'EnableSwfObject',
                            'EnableJqueryImageReflection',
                            'EnableDdBelatedPngFix',
                            'EnabledHtml5shiv',
                            'EnabledSelectivizr',
                            'EnabledModernizr'
                        )
                ),
            
            __( 'Front-end Configurations', 'developer-tools' ) =>
                array(
                    'enabled' => true,
                    'uid1' => false,
                    'closed' => false,
                    'form' => 
                        array( // Note, the 'form' value must match the nonce
                            'action' => 'Save',
                            'button_text' => __( 'Save Changes', 'developer-tools' ),
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
                    'form' => 
                        array( // Note, the 'form' value must match the nonce
                            'action' => 'Save',
                            'button_text' => __( 'Save Changes', 'developer-tools' ),
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
                            // 'AddMetaBox',
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
                    'form' => 
                        array( // Note, the 'form' value must match the nonce
                            'action' => 'Save',
                            'button_text' => __( 'Save Changes', 'developer-tools' ),
                            'begin' => false,
                            'end' => false
                        ),
                    'group' => 'back_end',
                    'type' => 'features',
                    'data' =>
                        array(
                            'AddCustomThemeOption',
                        		'EnableBackgroundThemeOption',
                        		'EnableCustomHeaderThemeOption',
                            'HidePlugin',
                            'HideDefaultAdmin',
                            'DisableWpCoreUpdates',
                            'DisablePluginUpdates',
                            'DisableTinyMceAutoFormatting',
                            'DisableTinyMceVisualEditor',
                            'DisableAdminMenuItems',
                            'DisableDashboardWidgets',
                            'CustomLoginImage'
                        )
                ),
            
             __( 'Plugin settings', 'developer-tools' ) =>
                 array(
                    'enabled' => true,
                    'uid1' => false,
                    'closed' => true,
                    'form' => 
                        array( // Note, the 'form' value must match the nonce
                            'action' => 'Save',
                            'button_text' => __( 'Save Changes', 'developer-tools' ),
                            'begin' => false,
                            'end' => true
                        ),
                    'group' => 'home',
                    'type' => 'features',
                    'data' => 
                        array(
                            'DtMaxUploadSize'
                        ) 
                ),			
			
            __( 'SQL Buddy Database Management', 'developer-tools' ) =>
                array(
                    'enabled' => true,
                    'uid1' => false,
                    'closed' => true,
                    'form' => false,
                    'group' => 'home',
                    'type' => 'view',
                    'data' => 
                        array(
                            'view' => 'admin-ui-page-content-group-database',
                            'viewData' => 
                                array(
                                    'action' => DEVELOPER_TOOLS_URL.'libs/sqlbuddy/login.php'
                                )
                        )
                ),			
			
            __( 'Export settings', 'developer-tools' ) =>
                array(
                    'enabled' => true,
                    'uid1' => false,
                    'closed' => true,
                    'form' => 
                        array( // Note, the 'form' value must match the nonce
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
            
            __( 'Import settings', 'developer-tools' ) =>
                array(
                    'enabled' => true,
                    'uid1' => false,
                    'closed' => true,
                    'form' => 
                        array( // Note, the 'form' value must match the nonce
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
            
            __( 'Clear settings', 'developer-tools' ) =>
                array(
                    'enabled' => true,
                    'uid1' => false,
                    'closed' => true,
                    'form' => 
                        array( // Note, the 'form' value must match the nonce
                            'action' => 'Reset',
                            'button_text' => __( 'Clear', 'developer-tools' ),
                            'begin' => true,
                            'end' => true
                        ),
                    'group' => 'global',
                    'type' => 'view',
                    'data' => 
                        array(
                            'view' =>'admin-ui-page-content-group-reset'
                        )
                )
        );
    }
}