<?php
new DeveloperTools();
class DeveloperTools { 
  private $_pluginSettings;
	private $_data;
  private $_setSavedValues = false;
  private $_errors = false;
  private $_messages = false;
  private $_globals;
  private $_valueNotSet = false;
  private $_advancedFields = false;
  private $_advancedFieldsCounter = 0;
  private $_enabledAdvancedFieldsCounter = 0;
  private $_nonce;
  private $_currentForm = false;
  private $_importedFile; 
  
  public function __construct()
  {
    $this->_init();
  }
  
  private function _init()
  {
    define( "DEVELOPER_TOOLS_DIR", WP_PLUGIN_DIR.'/developer-tools/' );
    define( "DEVELOPER_TOOLS_URL", WP_PLUGIN_URL.'/developer-tools/' );
    define( "DEVELOPER_TOOLS_APP_DIR", DEVELOPER_TOOLS_DIR.'com/app/' );
    define( "DEVELOPER_TOOLS_APP_URL", DEVELOPER_TOOLS_URL.'com/app/' );

    define( "DEVELOPER_TOOLS_PAGE_SLUG", "developer-tools" );
    define( "DEVELOPER_TOOLS_PAGE_URL", $_SERVER['REQUEST_URI'] );

    define( "DEVELOPER_TOOLS_VIEWS_DIR", DEVELOPER_TOOLS_APP_DIR.'views/' );
    define( "DEVELOPER_TOOLS_INCLUDES_DIR", DEVELOPER_TOOLS_APP_DIR.'includes/' ); 

    define( "IS_WP_ADMIN", ( is_admin() ? TRUE : FALSE ) );

    include_once DEVELOPER_TOOLS_DIR.'libs/krumo/class.krumo.php';
    include_once(ABSPATH . WPINC . '/feed.php');

    add_filter( 'wp_feed_cache_transient_lifetime', create_function('$fixrss', 'return 1800;') );

    //Load all these class files in this exact order
    $this->_LoadIncludes(DEVELOPER_TOOLS_INCLUDES_DIR.'models', true);
    $this->_LoadIncludes(DEVELOPER_TOOLS_INCLUDES_DIR.'libs', true);
    $this->_LoadIncludes(DEVELOPER_TOOLS_INCLUDES_DIR.'controllers');
    $this->_LoadIncludes(DEVELOPER_TOOLS_INCLUDES_DIR.'controllers/features');
    $this->_LoadIncludes(DEVELOPER_TOOLS_INCLUDES_DIR.'controllers/fields', false, true);
    $this->_LoadIncludes(DEVELOPER_TOOLS_INCLUDES_DIR.'controllers/fields/extends', false, true);

    $this->_PluginSetup();
  }
  
  private function _LoadIncludes( $path, $recursive = false, $field = false )
  {
    $d = dir($path);
    while (false !== ($file = $d->read()))
      if( $file != '.' && $file != '..' && !preg_match("/^\./", $file ) )
			{
        if( is_dir( "$path/$file" ) && $recursive )
				{
          $this->_LoadIncludes( "$path/$file", $recursive, $field );
				}
        elseif( !is_dir( "$path/$file" ) )
				{
          include_once "$path/$file";
				}
			}
    $d->close();
  } 
  
  private function _PluginSetup()
  {
    if( version_compare( $GLOBALS['wp_version'], '3.0.0', '<' ) )
      $this->_errors[] = sprintf( __( 'The Developer Tools plugin requires WordPress 3.0.0 or greater. The version of WordPress installed is %s.', 'developer-tools' ), $GLOBALS['wp_version'] );     
    
    $getData = ValuesModel::getInstance();
    $getData->GetData();
    $getData = (array)$getData;
		$this->_pluginSettings = $getData['values'];
    if( $this->_pluginSettings['dt'] && is_array( $this->_pluginSettings['dt'] ) )
      $this->_data = $getData['values']['dt'];
    
    $setUploadsDirectory = new SetUploadsDirectory();
    if( $setUploadsDirectory->errors )
      $this->_errors[] = $setUploadsDirectory->errors;
    
    $current_user = wp_get_current_user();
    define( 'CURRENT_UID', $current_user->data->ID );
    
    define( 'UID1_USERNAME', get_userdata(1)->user_login );
    
    define('DEVELOPER_TOOLS_ACCESS', ( IS_WP_ADMIN && ( CURRENT_UID == 1 || $this->_data['HidePlugin']['enabled'] != 'on' ) ? true : false) );
    
    if( $this->_errors && DEVELOPER_TOOLS_ACCESS )
      new DisplayAdminMessagesController(false, $this->_errors);
    else
      $this->_RunApplication();
  }
  
  private function _RunApplication()
  {   
    $this->_LoadEnabledFeatures();
    
    add_action('init', array( &$this, 'LoadTranslationFile') );    
		
    if( DEVELOPER_TOOLS_ACCESS )
      $this->_DeveloperToolsActivate();
  }
	
  private function _LoadEnabledFeatures()
  {
    if( $this->_data )
    {
      foreach( $this->_data as $class => $value )
      {
        if(class_exists($class))
        {
          $runClass = new $class();
          if( method_exists( $runClass, 'Enabled' ) )
            $runClass->Enabled($value);
        } 
      }
    }
  }
  
  public function LoadTranslationFile() { load_plugin_textdomain( 'developer-tools', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); }  
  
  private function _DeveloperToolsActivate()
  {
    add_action('admin_menu', array(&$this, 'AdminUiPageSetup'));
    if( isset( $_GET['page'] ) && $_GET['page'] == DEVELOPER_TOOLS_PAGE_SLUG ) // we only want to do all of this if we are on the developer tools plugin page
    {
      define("DEVELOPER_TOOLS_ACTION_SET", ( isset( $_GET['action'] ) ? TRUE : FALSE ));
      
      if( DEVELOPER_TOOLS_ACTION_SET ) // if a form action is set, run that action
        $this->_ProcessActions();
      else
        if( !session_id() ) session_start(); // this is required by swfupload 
      
      add_action('admin_init', array(&$this, 'AdminUiPageInit'));
    }
  }
  
  public function AdminUiPageInit()
  { 
    wp_create_nonce( 'developer-tools' );
    $this->_formNonce['Save'] = wp_nonce_url( DEVELOPER_TOOLS_PAGE_URL . '&action=save', 'developer-tools-save' );
    $this->_formNonce['Reset'] = wp_nonce_url( DEVELOPER_TOOLS_PAGE_URL . '&action=reset', 'developer-tools-reset' );
    $this->_formNonce['Export'] = wp_nonce_url( DEVELOPER_TOOLS_PAGE_URL . '&action=export', 'developer-tools-export' );
    $this->_formNonce['Import'] = wp_nonce_url( DEVELOPER_TOOLS_PAGE_URL . '&action=import', 'developer-tools-import' );
    
		/* Plugin js and css */
    wp_register_script( 'developer_tools-fancybox', DEVELOPER_TOOLS_URL.'libs/fancybox/jquery.fancybox-1.3.4.pack.js', array('jquery'), '1.3.4');
    wp_register_script( 'developer_tools-swfobject_fileprogress', DEVELOPER_TOOLS_URL.'libs/swfupload/js/fileprogress.js', array('swfupload', 'swfupload-queue'));
    wp_register_script( 'developer_tools-swfobject_handlers', DEVELOPER_TOOLS_URL.'libs/swfupload/js/handlers.js', array('swfupload', 'swfupload-queue'));
    wp_register_script( 'developer_tools-jquery_alphanumeric', DEVELOPER_TOOLS_URL.'js/jquery.alphanumeric.pack.js', array('jquery'));
    wp_register_script( 'developer_tools-jquery_scrollto', DEVELOPER_TOOLS_URL.'js/jquery.scrollTo-min.js', array('jquery'));
    wp_register_script( 'developer_tools-plugin', DEVELOPER_TOOLS_URL.'js/developer-tools-plugin.js', array('developer_tools-fancybox', 'developer_tools-swfobject_fileprogress', 'developer_tools-swfobject_handlers', 'developer_tools-jquery_alphanumeric', 'developer_tools-jquery_scrollto'));
    
    wp_register_style( 'developer_tools-fancybox', DEVELOPER_TOOLS_URL.'libs/fancybox/jquery.fancybox-1.3.4.css' );
    wp_register_style( 'developer_tools-plugin', DEVELOPER_TOOLS_URL.'css/developer-tools-plugin.css', array('developer_tools-fancybox') );
  }
  
  public function AdminUiPageSetup()
  { 
    $developerToolsPage = add_menu_page('Developer Tools', 'Developer&nbsp;Tools', 'activate_plugins', DEVELOPER_TOOLS_PAGE_SLUG, array(&$this, 'AdminUiPageContent'));
    add_action('admin_head-'.$developerToolsPage, array(&$this, 'AdminUiPageHeader'));
    add_action('admin_print_scripts-'.$developerToolsPage, array(&$this, 'AdminUiPageJsLibs'));
    add_action('admin_print_styles-'.$developerToolsPage, array(&$this, 'AdminUiPageCss'));

  }  
  
  public function AdminUiPageHeader()
  {
    $this->_SetGlobals(); //These are for wp global vars that need to be assigned to a feature later
    
    $viewData = array(
      'redirect' => DEVELOPER_TOOLS_ACTION_SET, 
      'url' => '?page=' . DEVELOPER_TOOLS_PAGE_SLUG
    );
    $this->_LoadView('admin-ui-page-header', $viewData );
  }
  
  public function AdminUiPageCss(){ wp_enqueue_style('developer_tools-plugin'); }
  
  public function AdminUiPageJsLibs(){ wp_enqueue_script('developer_tools-plugin'); }
  
  public function AdminUiPageContent()
  {
    add_action('rendered_developer_tools_content', array(&$this, 'WpActionRenderedContent'), 1);
      
    if( !DEVELOPER_TOOLS_ACTION_SET )
      $this->_LoadFeatureGroups();
    else
      $this->_LoadView( 'admin-ui-page-title', array( 'page_title' => __( 'Developer Tools', 'developer-tools' ) ) );

    if( $this->_errors || $this->_messages )  
      new DisplayAdminMessagesController($this->_messages, $this->_errors);
            
    $this->_LoadView('admin-ui-page-content-footer');
  }
  
  public function WpActionRenderedContent() { return true; }
  
  private function _LoadFeatureGroups()
  {
    
    $activeFeatureGroup = ( $_COOKIE['developer_tools_current_menu_item'] ? $_COOKIE['developer_tools_current_menu_item'] : 'home' );
    $featureGroups = new FeaturesGroupModel();
    $this->_LoadView('admin-ui-page-content-header');    
    
    foreach( $featureGroups->groups as $groupTitle => $featuresGroup )
    {
      if( $featuresGroup['enabled'] == false )
        continue;
      if( $featuresGroup['uid1'] && CURRENT_UID != 1 )
        continue;
      
      $viewData = array( 
        'group_title' => $groupTitle, 
        'action' => ( $featuresGroup['form'] ? $this->_formNonce[$featuresGroup['form']['action']] : false ),
        'begin_form' => ( $featuresGroup['form'] ? $featuresGroup['form']['begin'] : false ),
        'closed' => ( $featuresGroup['closed'] ? ' closed' : '' )
      );
      $this->_LoadView('admin-ui-page-content-group-header', $viewData);
      
// TODO: Error reporting here?      
      switch( $featuresGroup['type'] )
      {
        case 'features' :
          foreach( $featuresGroup['data'] as $className ) $this->_LoadFeature( $className );        
          break;
          
        case 'view' :
          $this->_LoadView( $featuresGroup['data']['view'], $featuresGroup['data']['viewData'] );
          break;
          
        case 'method' : 
          if( method_exists( &$this, $featuresGroup['data'] ) ) $this->$featuresGroup['data']();
          break;    
        case 'property' : 
          if( property_exists( &$this, $featuresGroup['data'] ) ) print ( $this->$featuresGroup['data'] ? $this->$featuresGroup['data'] : 'No data' );
          break;          
      } 
      
      $viewData = array( 
        'action' => ( $featuresGroup['form'] ? $featuresGroup['form']['action'] : false ),
        'button_text' => ( $featuresGroup['form'] ? $featuresGroup['form']['button_text'] : false ),
        'end_form' => ( $featuresGroup['form'] ? $featuresGroup['form']['end'] : false )
      );
      $this->_LoadView('admin-ui-page-content-group-footer', $viewData );
    }   
  }
  
  private function _LoadFeature($className)
  {
    if( !class_exists($className) )
    {
      $this->_errors[] = sprintf( __( 'The &s feature class, as defined in the features group model, does not exist.', 'developer-tools' ), $className );
      return; 
    }
    
    $feature = new $className();

    if( !method_exists($feature,'SetSettings') )
    {
      $this->_errors[] = '<span title="'.DEVELOPER_TOOLS_INCLUDES_DIR.'controllers/features/'.$className.'.php" class="class_file">'.$className.'->SetSettings()</span> ' . __( 'method does not exist.', 'developer-tools' );
      return;
    }

    $feature->SetSettings();

    if( $feature->minWpVersion && version_compare( $feature->minWpVersion, $GLOBALS['wp_version'], '>' ) )
      return;
      
    if( $feature->maxWpVersion && version_compare( $feature->maxWpVersion, $GLOBALS['wp_version'], '<' ) )
      return;      

    if( $feature->uid1accessOnly && CURRENT_UID != 1 )
      return;

    // create uploads dir if feature uses uploader and it's upload dir doestn exist
    if( $feature->uploads && !is_dir( DEVELOPER_TOOLS_UPLOADS_DIR.$className ) )
    {
      $createUploadsDir = new CreateDirectory( DEVELOPER_TOOLS_UPLOADS_DIR.$className );
      if( $createUploadsDir->errors )
      {
        $this->_errors[] = $createUploadsDir->errors;
        return;
      }
    }
    
    $featureEnabled = false;
    if( $this->_data[$className] )
      $featureEnabled = true;
    
    $viewData['enabled'] = ( $featureEnabled ? ' enabled' : '' );
    
    $viewData['name'] = $className;
      
    if( $feature->title )
      $viewData['title'] = $feature->title;
    else
      $viewData['title'] = '<span class="untitled">Untitled Feature:</span> '.$className;

    $showFeature = ( $this->_pluginSettings['show'] ? in_array( $className, $this->_pluginSettings['show'] ) : false);
    
    $viewData['checked'] = ( $showFeature ? 'checked="checked"' : '' );
    
    if( $feature->information )
      $viewData['information'] = $feature->information;    
    
    $this->_LoadView('admin-ui-page-content-group-feature-title', $viewData);

    $viewData['hide'] = ( $showFeature ? '' : ' hidden');
      
    if( $feature->description )
      $viewData['description'] = $feature->description;
      
    if( $feature->multiple )
    {
      $viewData['id'] = $className.'-containers';
      $viewData['class'] = 'group';
    }
    else
    {
      $viewData['id'] = $className.'-1';
      $viewData['class'] = 'single_feature';      
    }     
    
    $this->_LoadView('admin-ui-page-content-group-feature-header', $viewData);  
    
    if( !$feature->fields )
    {
      $this->_errors[] = '<span title="'.DEVELOPER_TOOLS_INCLUDES_DIR.'controllers/features/'.$className.'.php" class="class_file">'.$className.'->fields</span> ' . __( 'property does not exist.', 'developer-tools' );
      return;
    }
    
    $numberOfMultipleDuplicates = ( $feature->multiple ? count($this->_data[$className]) : 1 );
    $numberOfMultipleDuplicates = ( $numberOfMultipleDuplicates == 0 ? 1 : $numberOfMultipleDuplicates );
    
    for( $i = 1; $i <= $numberOfMultipleDuplicates; $i++ )
    {
      $this->_LoadFields($feature, $className, $i, $numberOfMultipleDuplicates);
    } 
    
    if( $feature->multiple )
      $viewData['add_another'] = true;
    
    $this->_LoadView('admin-ui-page-content-group-feature-footer', $viewData);   
    
// TODO: Error check here to be sure uploads dir exists?
    if( $feature->uploads )
    {
      $viewDataFeatureUploader = array( 
        'id' => $className,
        'upload_types' => $feature->uploads['allowedFileTypes'],
        'upload_label' => $feature->uploads['uploadDescription'],
        'max_upload_size' => ( defined('DEVELOPER_TOOLS_MAX_UPLOAD_SIZE') ? DEVELOPER_TOOLS_MAX_UPLOAD_SIZE : 5120 )
      );
      $this->_LoadView('admin-ui-page-content-group-feature-uploader', $viewDataFeatureUploader);
    }
    
  }
  
  private function _LoadFields($feature, $className, $i, $numberOfMultipleDuplicates)
  {
      $duplicateCounter = ( $feature->multiple ? $i : false);                 
      
      $viewDataFieldGroup['name'] = $className;
      $viewDataFieldGroup['counter'] = $i;
      
      if( $feature->multiple )
        $this->_LoadView('admin-ui-page-content-group-feature-group-header', $viewDataFieldGroup);
      
      // load each single field  
      $this->_valueNotSet = false;
      $this->_advancedFields = false;
      $this->_advancedFieldsCounter = 0;
      $this->_enabledAdvancedFieldsCounter = 0;
      foreach( $feature->fields as $fieldSettings )
      {    
        if( $fieldSettings['uid1accessOnly'] && CURRENT_UID != 1 )
          continue;
        else
          $this->_LoadSingleField( $feature, $className, $fieldSettings, $duplicateCounter, $i );
      }
      
      // feature buttons
      $viewDataFeatureButtons['name'] = $className;     
      $viewDataFeatureButtons['id'] = $className.'-'.$i;
      
      if( $this->_advancedFields ) // advanced button
        $viewDataFeatureButtons['advanced_show'] = true;
      
      if( $feature->codeSample ) // code sample button
        $viewDataFeatureButtons['code_show'] =  ( $this->_valueNotSet ? 'show' : 'hidden' );               

      if ( $feature->multiple ) // remove button
        $viewDataFeatureButtons['remove_button'] = ( $this->_valueNotSet ? 'show' : 'hidden' );
      
      if( $this->_advancedFields || $feature->codeSample || $feature->multiple )
        $this->_LoadView('admin-ui-page-content-group-feature-buttons', $viewDataFeatureButtons);
      
      //code sample
      if( $feature->codeSample ) // template code
      {
        $viewDataCodeSample = array(
          'id' => $className.'-'.$i,
          'code' => $feature->codeSample['code'],
          'placement' => $feature->codeSample['placement'],
          'link' => $feature->codeSample['moreCodexLink']
        );
        $this->_LoadView('admin-ui-page-content-group-feature-code', $viewDataCodeSample);  
      }   
      
      if( $feature->multiple )
        $this->_LoadView('admin-ui-page-content-group-feature-group-footer', $viewDataFieldGroup);
        
  }
  
  private function _LoadSingleField( $feature, $className, $fieldSettings, $duplicateCounter, $i )
  {
// TODO: field['name'] is required, verifiy that it is set here
        $value = ( $duplicateCounter ? $this->_data[$className][$className.'-'.$i] : $this->_data[$className] );
        if( $fieldSettings['name'] )
          $value = $value[$fieldSettings['name']];
// TODO: field['name'] is required, verifiy that it is set here
					
    		$fieldSettings['featureItemEnabled'] = $this->_data && ( $duplicateCounter ? array_key_exists($className, $this->_data) && array_key_exists($className.'-'.$i, $this->_data[$className]) : array_key_exists($className, $this->_data) );
				
        if( $fieldSettings['advanced'] )
        {
          $this->_advancedFields = true;
          $this->_advancedFieldsCounter++;
          if( !$value ) // TODO: This valid?
          {
            $fieldSettings['advanced'] = 'hidden';
          }
          else
          {
            $this->_enabledAdvancedFieldsCounter++;
            $fieldSettings['advanced'] = 'open';
          }
        }
				
// TODO: This valid?
        if( $value )
          $this->_valueNotSet = true;
          
        if( $fieldSettings['fieldDataMethod'] )
        {
          $feature->$fieldSettings['fieldDataMethod']();
          $fieldSettings['data'] = $feature->data;
        }
        
        if( $fieldSettings['fieldDataModel'] )
        {
          $initData = call_user_func( array( $fieldSettings['fieldDataModel'], 'getInstance' ) );
          $initData->GetData();
          $callData = call_user_func( array( $fieldSettings['fieldDataModel'], 'getInstance' ) );
          $fieldData = (array)$callData;
          $fieldSettings['data'] = $fieldData['data'];
        }
        
        if( $fieldSettings['globalVariable'] )
          $fieldSettings['data'] = $this->_globals[$fieldSettings['globalVariable']];      

        $field = new $fieldSettings['fieldType']($className, $duplicateCounter, $value, $fieldSettings);
        
        print $field->output;   
  }
  
  private function _LoadView($fileName = false, $view = false)
  {
    $name = str_replace('.php', '', $fileName);
    $name = str_replace('-', '/', $name);
    if( file_exists(DEVELOPER_TOOLS_VIEWS_DIR.$name.'.php') )
      include DEVELOPER_TOOLS_VIEWS_DIR.$name.'.php';
    else
      $this->_errors[] = '<span title="'.DEVELOPER_TOOLS_VIEWS_DIR.$name.'.php" class="class_file">'.$name.'.php</span> ' . __( 'view does not exists.', 'developer-tools' );
  }
  
  private function _ProcessActions()
  {
    if( !is_admin() || !current_user_can(10) || !check_admin_referer( 'developer-tools' ) ) die( __( 'Developer Tools security failure: Error code: 1', 'developer-tools' ) );
    if( DEVELOPER_TOOLS_ACTION_SET )
    {
      $wpnonce = $_GET['_wpnonce'];

      switch( $_GET['action'] )
      {
        case 'save' :
          if( !wp_verify_nonce( $wpnonce, 'developer-tools-save' ) ) die( __( 'Developer Tools security failure: Error code: 2', 'developer-tools' ) ); 
          if( $_POST['show'] ||  $_POST['dt'] )
          {
          	$this->_setSavedValues['version'] = DEVELOPER_TOOLS_VERSION;
            if ( $_POST['show'] )
              $this->_setSavedValues['show'] = $_POST['show'];
             
            $this->_CheckSavedValues();
            
            if( $this->_setSavedValues && update_option( 'developer-tools-values', maybe_unserialize($this->_setSavedValues) ) )
                $this->_messages[] = __( 'Options saved.', 'developer-tools' );
            else
                $this->_errors[] = __( 'No values were set.', 'developer-tools' );

          }
        break;
        case 'reset' :
          if( !wp_verify_nonce( $wpnonce, 'developer-tools-reset' ) ) die( __( 'Developer Tools security failure: Error code: 3', 'developer-tools' ) ); 
          delete_option('developer-tools-values');
          $this->_messages[] = __( 'Options reset.', 'developer-tools' );
        break;
        case 'export' :
          if( !wp_verify_nonce( $wpnonce, 'developer-tools-export' ) ) die( __( 'Developer Tools security failure: Error code: 4', 'developer-tools' ) );          
          new ExportSettings( $this->_pluginSettings );
        break;
        case 'import' :
          if( !wp_verify_nonce( $wpnonce, 'developer-tools-import' ) ) die( __( 'Developer Tools security failure: Error code: 5', 'developer-tools' ) );
					$importSettings = new ImportSettings();
					if( $importSettings->errors )
						$this->_errors[] = $importSettings->errors;
					if( $importSettings->messages )
						$this->_messages[] = $importSettings->messages;
        break;
      }
    }
  }
  
  private function _SetGlobals()
  {
    global $menu;
    global $submenu;
    global $wp_meta_boxes;
    foreach($menu as $id => $value){
      if( $value[0] != '' && $value[2] != DEVELOPER_TOOLS_PAGE_SLUG ){
        $pageTitle = explode(' <', $value[0]);
        $this->_globals['menuItems'][$value[2]][$id] = $pageTitle[0];
        foreach( $submenu as $pageName => $subpages ){
          if( $pageName == $value[2] ){
            $submenuItems = '';
            foreach( $subpages as $subpageID => $subpageValue ){
              if( $value[2] != $subpageValue[2] ){
                $subpageTitle = explode(' <', $subpageValue[0]); 
                $submenuItems[$subpageID] = $subpageTitle[0];
              }
            }
            $this->_globals['menuItems'][$value[2]][$value[2]] = $submenuItems;
          }
        }
      }
    }
  }
  
  private function _CheckSavedValues()
  {
// TODO: Still saves null values
    function _CheckNull( $value )
    {
      if( is_array( $value ) )
        return array_filter( $value, '_CheckNull');
      else
        return( strlen($value) );
    }
    $this->_setSavedValues['dt'] = array_filter( $_POST['dt'], '_CheckNull');
  }

  private function _DebugSettings(){
  	$krumo_data = array();
		$krumo_data['plugin_settings'] = is_array($this->_pluginSettings) ? array_map('stripslashes_deep', $this->_pluginSettings) : stripslashes($this->_pluginSettings);
		global $developer_tools;
		$krumo_data['developer_tools_global'] = $developer_tools;
		krumo( $krumo_data );
  }

}