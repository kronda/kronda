<?php
class AddCustomThemeOption extends Feature
{
  public function SetSettings()
  {
    $this->title          = __( 'Add custom theme options', 'developer-tools' );
    $this->multiple       = true;    
    $this->description    = __( 'Once enabled, a new admin tab titled "Theme options" appears under "Appearance" admin menu tab.  Users with theme access can then configure the theme options. Use the template code to make it appear in your theme.', 'developer-tools' );
    $this->information    = __( 'Ideal for a global telephone / fax number, facebook / twitter url, ect. that appear on the site and the client wants control of.', 'developer-tools' );
    $this->codeSample     = array(
      'code' => '&lt;?php echo $GLOBALS[\'developer_tools\'][\'theme_options\'][\'<span class="replace-1"></span>\']; ?&gt;',
      'placement' => 'outside'
    );
    $this->fields         = array(
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Unique identifier', 'developer-tools' ),
        'name' => 'id',
        'required' => true,
        'characterSet' => 'alphaNumericHyphenUnderscore',
        'unmodifiableAfterSave' => true,
        'codeReplaceClass' => 'replace-1'
      ),
      array( 
        'fieldType' => 'SelectList', 
        'label' => 'Field type',
        'name' => 'type',
        'fieldDataModel' => 'FieldsModel',
        'fieldSelector' => true,
        'required' => true
      ),
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Field label', 'developer-tools' ),
        'name' => 'label',
        'advanced' => true
      ),
      array( 
        'fieldType' => 'TextArea', 
        'label' => __( 'Field description', 'developer-tools' ),
        'name' => 'description',
        'advanced' => true
      ),
      array( 
        'fieldType' => 'TextArea', 
        'label' => __( 'Default value(s)', 'developer-tools' ),
        'name' => 'default',
        'afterLabel' => 'Form elements such as <strong>select lists</strong> or <strong>multiple checkboxes</strong>, use a key / value pair, each on its own line.<br /><strong>Example:</strong><br />value 1|Label 1<br />value 2|Label 2'
      )
    );
  }
  
  public function Enabled($value)
  {
    $this->value = $value;
    add_action('admin_menu', array(&$this, 'AdminUiPageSetup'));
		//add_action('admin_init', array(&$this, 'AdminUiPageInit'));
		
		if( $_GET['page'] == 'theme-options' && $_GET['action'] == 'save' && $_POST && wp_verify_nonce($_POST['theme_options_nonce'],'developer-tools-theme-options') )
    {
      update_option( 'developer-tools-theme-options', maybe_unserialize($_POST['dt']['theme_options'] ) );
    }

    global $developer_tools;
    $developer_tools['theme_options'] = get_option( 'developer-tools-theme-options' );
  }
	
  public function AdminUiPageInit()
  {
    //wp_create_nonce( 'developer-tools-theme-options' );
  }
  
  public function AdminUiPageSetup()
	{
		$themeOptions = add_submenu_page( 'themes.php', __( 'Theme Options', 'developer-tools' ), __('Theme Options', 'developer-tools' ), 'edit_themes', 'theme-options', array(&$this, 'ThemeOptions'));
	}

	public function ThemeOptions()
	{
	  ?>
      <div class='wrap'>
        <div class='icon32' id='icon-themes'><br></div>
        <h2 style="margin-bottom: 20px;">Theme Options</h2>
        <?php
          switch( $_GET['action'] ) :
            case 'save' : 
              if ( empty($_POST) || !wp_verify_nonce($_POST['theme_options_nonce'],'developer-tools-theme-options') ) : 
                ?>
                  <div class="message error below-h2">
                    <p><?php _e( 'Sorry, theme options form nonce did not verify.', 'developer-tools' ) ?></p>
                  </div>
                <?php
                exit;
              else : 
                ?>
                  <div class="message updated below-h2">
                    <p><?php _e( 'Theme options updated.', 'developer-tools' ) ?></p>
                  </div>           
                <?php
              endif;
            break;
          endswitch;
        ?>        
        <form method="post" enctype="multipart/form-data" name="theme_options" action="?page=<?php print $_GET['page'] ?>&action=save">
          <?php
            global $developer_tools;
            foreach( $this->value as $field ) : 
              ?>
                <div style="margin-bottom: 10px">
                  <?php
                    $value = false;
                    if( $developer_tools['theme_options'][$field['id']] )
                    {
                      $value = $developer_tools['theme_options'][$field['id']];
                    }
                    else
                    {
                      if( $developer_tools['theme_options'] && !array_key_exists($field['id'], $developer_tools['theme_options']) && $field['default'] != '' )
                      {
                        $value = $field['default'];
                      }
                    }
                    
                    $fieldSettings = array();
                    $fieldSettings['label'] = $field['label'];
                    $fieldSettings['afterLabel'] = $field['description'];
                    $fieldSettings['name'] = $field['id'];
                    if( $field['type'] == 'SelectList' ) $fieldSettings['data'] = $field['default'];
                    $field = new $field['type']('theme_options', false, $value, $fieldSettings);
                    print $field->output;
                  ?>
                </div>
              <?php
            endforeach;
            wp_nonce_field( 'developer-tools-theme-options', 'theme_options_nonce' );
          ?>
          <input type="submit" value="<?php _e('Save Changes', 'developer-tools' ) ?>" class="button-primary" />
        </form>
      </div>
		<?php
	}
}
