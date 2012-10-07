/* ---------------------------------------------------------------------
Original Author: Kelly Meath
------------------------------------------------------------------------ */
if( typeof jQuery == 'function' )
  jQuery(function($){
    DeveloperTools.init();
  });

var DeveloperTools = {
  
  dt : null,
  
  $ : jQuery,
  
  $currentFeature : null,
  
  uploadInputType : null,
  
  uploadedFileName : null,
  
  uploadedFileUrl : null,  
  
  init : function()
  { 
    this.dt = this.$('#developer_tools');
    this.staticButtonEvents();
    this.registerFeatureButtonEvents( false );
  },  
  
  registerFeatureButtonEvents : function( $obj )
  {
    var _ = this;

    $obj = _.dt; /* if $obj doesnt exist, thats because init fired this method, so lets add events to all elements on the page */
    
    /* show code sample, also replaces replace-text values in code dom element */
    $obj.find('.feature_buttons a.code_button').live( 'click', function(){
      var $currentFeature = _.$(this).parents('.single_feature');
      _.showCodeSample( $currentFeature );

    });
    
    /* show advanced fields */
    $obj.find('.feature_buttons a.show_advanced').live( 'click', function(){
      var $currentFeature = _.$(this).parents('.single_feature');
      _.showAdvancedFields( $currentFeature );
    });
    
    /* remove feature group item */
    $obj.find('.feature_buttons a.remove_group').live( 'click', function(){
      var $currentFeature = _.$(this).parents('.single_feature');
      _.removeDuplicatedFeature( $currentFeature );
    });    
    
    /* required fields ( keeps buttons hidden until all have valid data ) */
    $obj.find('.required_field').live( 'change', function(){
      var $currentFeature = _.$(this).parents('.single_feature');
      _.onInputChange( $currentFeature );
    });
    
    /* show template code */
    $obj.find('.code_template div.close a').live( 'click', function(){
      var $templateCodeContainer = _.$(this).parents('.code_template');
      _.showHide( $templateCodeContainer );
    });
    
    /* select list uploader */
    $obj.find('select').live( 'blur, change', function(){
      var $currentFeature = _.$(this).parents('.single_feature');
      if( _.$(this).children('option:selected').text() == 'Upload new' )
      {
        _.showUploader( $currentFeature );
        _.uploadInputType = 'select';
      }
      _.onInputChange( $currentFeature );
    });
    
    /* image file uploader */
    $obj.find( 'a.upload-new-image-file' ).live( 'click', function(){
      var $currentFeature = _.$(this).parents('.single_feature');
      _.showUploader( $currentFeature );
      _.uploadInputType = 'image';
    });
    
    /* is this needed? */
    $obj.find( ':input' ).live( 'keyup', function(){
      var $currentFeature = _.$(this).parents('.single_feature');
      _.onInputChange( $currentFeature );
      _.updateTemplateCode( $currentFeature );
    });
    
    _.alphaNumericInit( $obj ); /* init alphanumeric for the whole application */
    
    /* field selector select list */
    $obj.find('.field_selector').live( 'blur, change', function(){
      var $currentFeature = _.$(this).parents('.single_feature');
      _.showFieldContainer( $currentFeature );
    });
    
  },
  
  showFieldContainer : function( $obj )
  {
    //alert($obj.attr('id'));
  },
  
  onInputChange : function( $obj )
  {
    var $requiredFields = $obj.find('.required_field');
    var totalRequiredValues = $requiredFields.length;
    var setRequiredValues = 0;
    if( totalRequiredValues == 0 )
      $obj.find('.feature_buttons .code_button').removeClass('hidden');
      
    for( var i = 0; i < totalRequiredValues; i++ )
    {
      if( $requiredFields.eq( i ).val() && $requiredFields.eq( i ).val() != 'Upload new' && $requiredFields.eq( i ).val() != "" )
      {
        setRequiredValues++;
        $requiredFields.eq( i ).removeClass('red');
        $requiredFields.eq( i ).prev('.required_icon').addClass('hidden');
      }
      else
      {
        $requiredFields.eq( i ).addClass('red');
        $requiredFields.eq( i ).prev('.required_icon').removeClass('hidden');
      }
    }
    if( totalRequiredValues == setRequiredValues )
      $obj.find('.feature_buttons .code_button').removeClass('hidden');
    else
      $obj.find('.feature_buttons .code_button').addClass('hidden');
  },  
  
  alphaNumericInit : function( $obj )
  { 
    $obj.find( '.alphaNumericSpaceHyphenUnderscore' ).alphanumeric( { allow : " -_" } );
    $obj.find( '.cssSelectors' ).alphanumeric( { allow : " !-_.#,:*+>~=[]()|^$" } );
    $obj.find( '.numeric' ).numeric();
    $obj.find( '.numericDecimal' ).numeric( { allow : "." } );
    $obj.find( '.alphaNumericSpace' ).alphanumeric( { allow : " " } );
    $obj.find( '.alphaNumericHyphenUnderscore' ).alphanumeric( { allow : "-_" } );
  },  
  
  addAnotherFeature : function( $currentFeature )
  {
      var _ = this;
      
      var $featureGroupContainer = $currentFeature.parent('.group');
      
      /* disable any unmodifable fields */
      $currentFeature.find('.unmodifiableAfterSave').removeClass('new').addClass('existing');
      $currentFeature.find('.unmodifiableAfterSave').each( function(){
        /* THIS CURRENTLY ONLY HAS BEEN TESTED WITH TEXT INPUT FIELDS */
        _.$( this ).find('.existing .unmodifiable').html( _.$( this ).find('.new .unmodifiableAfterSaveValue').val() );
      });
      
      /* get total groups to create next group ID */
      var totalGroups = $featureGroupContainer.children('.single_feature').length;
      var newFeatureId = $featureGroupContainer.parent('.feature').attr('id') + '-' + ( totalGroups + 1 );
      
      /* copy original html and replace old feature id with new feature id */
      var groupHTML = $currentFeature.html();
      while ( groupHTML != ( groupHTML = groupHTML.replace( $currentFeature.attr('id'), newFeatureId ) ) );
      $featureGroupContainer.append( '<div id="' + newFeatureId + '" class="single_feature">'+groupHTML+'</div>' );      
      
      var $newFeatureObj = _.$( '#' + newFeatureId );    
      
       /* reset the feature visisble options */    
      $newFeatureObj.find(':input').not(':button, :submit, :reset').each( function(){
        _.$( this ).val('');
        _.$( this ).removeAttr('value');
        _.$( this ).find('option').removeAttr('selected');
        _.$( this ).removeAttr('checked');
      });
/*
 * TODO: above ^
 * After you duplicate a feature, its suppose to clear the form out, which it visually appears to do.
 * But if you remove any of the duplicated features, all of the newly created features inherit the original features, data..
 * idfk this happens. >:(
 */
      $newFeatureObj.find('.unmodifiable').html('');
      $newFeatureObj.find('.unmodifiableAfterSave').removeClass('existing').addClass('new');
      
      /* hide items */
      var hideElements = [ '.remove_group', '.code_button', '.code_template', '.advanced', '.required_icon' ];
      /*
      if( $newFeatureObj.find('.required_field').length > 0 )
        hideElement.push('.code_button', '.code_template', '.advanced', '.required_icon');
      */
      for( var i = 0; i < hideElements.length; i++ ) $newFeatureObj.find( hideElements[i] ).addClass('hidden');
      
      _.alphaNumericInit( $newFeatureObj ); /* we have to init this for the new dom element */

  },
  
  removeDuplicatedFeature : function( $currentFeature )
  {
      var _ = this;
      
      var $featureGroupContainer = $currentFeature.parent('.group');
      
      var totalGroups = $featureGroupContainer.children('.single_feature').length;
      
      if( totalGroups == 1 )
        _.addAnotherFeature( $currentFeature );
      
      var featureName = $featureGroupContainer.parent('.feature').attr('id');
      $currentFeature.remove();

      $featureGroupContainer.children('.single_feature').each( function( index ){

        $feature = _.$( this );

        var currentFeatureID = $feature.attr('id');
        var newFeatureId = featureName + '-' + ( index + 1 );
        
        var featureHTML = $feature.html();
        
        while ( featureHTML != ( featureHTML = featureHTML.replace( currentFeatureID, newFeatureId ) ) );
        
        $feature.html( featureHTML ).attr( 'id', newFeatureId );
        
        _.alphaNumericInit( $feature );
      });
      
  },
  
  showCodeSample : function( $featureItem )
  { 
    /* show / hiden code template block */  
    this.showHide( $featureItem.children('.code_template') );
    /* the code block must be visible before it can be activated */     
    this.updateTemplateCode( $featureItem ); 
  },
  
  updateTemplateCode : function( $featureItem )
  {
    var _ = this;
    var $codeBlock = $featureItem.children('.code_template');
    if( $codeBlock && !$codeBlock.hasClass('hidden') )
      $featureItem.find('.single_field .replace-text').each(function(){
        var replaceText = _.$( this );
          $codeBlock.find('span').each(function(){
          if ( replaceText.hasClass( _.$( this ).attr( 'class' ) ) ) 
            _.$( this ).html( replaceText.val() ); /* THIS CURRENTLY ONLY HAS BEEN TESTED WITH TEXT INPUT FIELDS */
        });
      });    
  },
  
  showAdvancedFields : function( $feature )
  {
      /*show hidden advanced fields */
      var $advancedFields = $feature.find('.advanced');
      this.showHide( $advancedFields );
  },
  
  showHide : function( $obj )
  {
    if( $obj.hasClass('hidden') )
      $obj.removeClass('hidden');
    else
      $obj.addClass('hidden');
  },

  showUploader : function( $feature )
  {
    var $uploader = $feature.parents('.feature').next('.uploader');
    this.$currentFeature = $feature;
    $feature.parents('.feature').next('.fancybox').trigger('click');
  },
  
  doneUploading : function()
  {
      var $feature = this.$currentFeature;
      switch( this.uploadInputType )
      {
        case 'select' : 
          $feature.find('select.SelectListUploader').append('<option value="' + this.uploadedFileName + '" selected="selected">' + this.uploadedFileName + '</option>');
        break;
        case 'image' :
          $feature.find('.image_preview img').attr('src', this.uploadedFileUrl + this.uploadedFileName );
          $feature.find('input.SingleImageUploader').val( this.uploadedFileName );
        break;
      }
      this.closeUploader( false );
  },  
  
  closeUploader : function( resetValues )
  {
    var _ = this;
    var $feature = _.$currentFeature;
    var featureId = '#' + $feature.attr('id');
    _.scrollToId( featureId );
    
    if( resetValues )
    {
      switch( _.uploadInputType )
      {
        case 'select' : 
          $feature.find('select.SelectListUploader option:first').attr('selected', 'selected');
        break;
      }
    }
    
    _.onInputChange( $feature );
    
    _.$('#fancybox-close').trigger('click');
    
    /* reset uploader referrence */
    _.$currentFeature = null;
    _.uploadInputType = null;
    _.uploadedFileUrl = null;
    _.uploadedFileName = null;
  },
  
  showTabSection : function( tabSection )
  {
    var $postBox = this.$( '.meta-box-sortables div.postbox:not(.global)' );
    $postBox.addClass( 'hidden' );
    if( tabSection != 'all' )
      $postBox = this.$( '.meta-box-sortables div.postbox.' + tabSection );
      
    this.showHide( $postBox ); 
  },
  
  scrollToId : function( id )
  {
    this.$(window).scrollTo( id, 500, { offset: { top: -100 } } );
  },
  
  staticButtonEvents : function()
  {
    var _ = this;
    
    /* add another button click */
    _.$('.duplicate_feature a.add_another').click( function(){
      var $currentFeature = _.$(this).parents('.duplicate_feature').prev('.group').find('.single_feature').eq(0);
      if($currentFeature.length > 0 )
        _.addAnotherFeature( $currentFeature );
    });    
    
    /* message when user clicks on an unmodifiable field */
    _.$('.existing div.unmodifiable').click( function(){
      alert('This field cannot be modifed. Add a new one first, then delete the old one. You will need to update any references to the new item after you save.');
    });
    
/* TODO: this currently only works for non-duplicatable features, this should eventually be moved to registerFeatureButtonEvents ( It will for sure need to be moved once this field type becomes avaible as a meta box ) */
    _.$('.checkbox_parent div.single_field input[type=checkbox]').click( function(){
      var $checkboxChildren = _.$(this).parents('.checkbox_parent').children('.checkbox_children');
      if(_.$(this).is(':checked') )
        _.showHide( $checkboxChildren );
      else
        _.showHide( $checkboxChildren );
    });
    
    /* show hide feature group containers */
    _.$('.postbox .hndle, .postbox .handlediv').click(function(){
      var $postBox = _.$(this).parent('.postbox');
      if( $postBox.hasClass('closed') )
        $postBox.removeClass('closed');
      else
        $postBox.addClass('closed');
    });
    
    /* show / hide feature and save data in cookie for use after user saves */
    _.$('.feature_title .toggle_feature').click(function(){ 
      var $feature = _.$(this).parents('.feature_title').next('.feature');
      var $checkbox = _.$(this).children('input[type=checkbox]');
      if( $checkbox.is( ':checked' ) )
        $feature.removeClass( 'hidden' );
      else
        $feature.addClass( 'hidden' );
    });
    
    _.$('.swfupload_container .footer .show_instructions').click( function(){
      var $uploaderIntructions = _.$( this ).parents( '.uploader' ).children( '.uploader_info' );
      _.showHide( $uploaderIntructions );
    });
    
    /* close uploader */
    _.$('.uploader .close_uploader').click( function(){
      _.closeUploader( true );
    });
    
    /* reset button */
    _.$('.form_submit_button.Reset input.button-primary, .form_submit_button.Import input.button-primary').click( function(){
      window.confirm('Are you sure you want to do this? This will erase all saved values.');
    });
    
    /* show default js required button */
    _.$( '#preview_default_javascript_required_link, #PreviewJavaScriptRequired' ).click( function(){
      _.showHide( _.$( '#PreviewJavaScriptRequired' ) );      
    });
    
    _.$( '.show_information' ).click( function(){
      var $feature = _.$(this).parents('.feature_title').next('.feature');
      var $checkbox = _.$(this).next('.toggle_feature').children('input[type=checkbox]');
      var $featureInfo = $feature.children('.feature_information');      
      if( $checkbox.is( ':checked' ) )
      {
		_.showHide( $featureInfo );
      }
      else
      {
      	$checkbox.attr('checked', 'checked');
        $feature.removeClass( 'hidden' );
        _.showHide( $featureInfo );
      }
    });
    _.$('.fancybox').fancybox({ centerOnScroll : true, padding : 0 });

  }
}