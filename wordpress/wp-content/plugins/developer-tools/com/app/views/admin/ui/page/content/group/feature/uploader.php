<a href="#<?php print $view['id'] ?>_fileupload_uploader" id="<?php print $view['id'] ?>_fb" class="fancybox hidden">Show fancybox</a>
<div class="hidden">
  <div id='<?php print $view['id'] ?>_fileupload_uploader' class='uploader'>
  	<div class='uploader_header'><?php _e( 'Upload new file', 'developer-tools' ) ?></div>
  	<div class="swfupload_container">
  		<div class="fieldset flash" id="<?php print $view['id'] ?>fsUploadProgress">
  			<span class="legend"><?php _e( 'File Upload Progress', 'developer-tools' ) ?></span>
  		</div>
  		<div class="footer">
  			<span id="<?php print $view['id'] ?>spanButtonPlaceholder"></span>
  			<input id="<?php print $view['id'] ?>btnCancel" class="cancel_button" type="button" value="Cancel Uploads" onclick="cancelQueue(<?php print $view['id'] ?>);" disabled="disabled" />
  			<?php if( $view['upload_label'] ) : ?>
  				<a class="button-primary show_instructions"><?php _e( 'Instructions', 'developer-tools' ) ?></a>
  				<div class="clear"></div>
  			<?php endif; ?>		
  		</div>
  	</div>	  
  	<?php if( $view['upload_label'] ): ?>
  		<div id="<?php print $view['id'] ?>_uploader_info" class="uploader_info hidden"><?php print $view['upload_label'] ?></div>
  	<?php endif; ?>
  </div><!-- #<?php print $view['id'] ?>_fileupload_uploader -->
</div>
<script type="text/javascript">
  var <?php print $view['id'] ?> = new SWFUpload({
    // Backend Settings
    upload_url: "<?php print DEVELOPER_TOOLS_URL ?>libs/swfupload/upload.php",
    post_params: {
      "PHPSESSID" : "<?php print session_id(); ?>",
      "UPLOADDIR" : "<?php print DEVELOPER_TOOLS_UPLOADS_DIR.$view['id'] ?>",
      "ADMINEMAIL" : "<?php print $GLOBALS['user_email'] ?>"
    },

    // File Upload Settings
    file_size_limit : "<?php print $view['max_upload_size'] ?>",  // 5MB
    file_types : "<?php print $view['upload_types'] ?>",
    file_types_description : "Upload file",
    file_upload_limit : 0,
    file_queue_limit : 0,

    // Event Handler Settings (all my handlers are in the Handler.js file)
    swfupload_preload_handler : preLoad,
    swfupload_load_failed_handler : loadFailed,
    file_dialog_start_handler : fileDialogStart,
    file_queued_handler : fileQueued,
    file_queue_error_handler : fileQueueError,
    file_dialog_complete_handler : fileDialogComplete,
    upload_start_handler : uploadStart,
    upload_progress_handler : uploadProgress,
    upload_error_handler : uploadError,
    upload_success_handler : function(file, serverData) {
      try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setComplete();
        progress.setStatus("Complete.");
        progress.toggleCancel( false );
            DeveloperTools.uploadedFileName = file.name;
            DeveloperTools.uploadedFileUrl = "<?php print DEVELOPER_TOOLS_UPLOADS_URL.$view['id'] ?>/";
        DeveloperTools.doneUploading();
    
      } catch (ex) {
        this.debug(ex);
      }
    },
    upload_complete_handler : uploadComplete,

    // Button Settings
    button_image_url : "<?php print DEVELOPER_TOOLS_URL ?>libs/swfupload/upload_btn.png",
    button_placeholder_id : "<?php print $view['id'] ?>spanButtonPlaceholder",
    button_width: 69,
    button_height: 23,
    
    // Flash Settings
    flash_url : "<?php print DEVELOPER_TOOLS_URL ?>libs/swfupload/swfupload.swf",
    flash9_url : "<?php print DEVELOPER_TOOLS_URL ?>libs/swfupload/swfupload_fp9.swf",
  

    custom_settings : {
      progressTarget : "<?php print $view['id'] ?>fsUploadProgress",
      cancelButtonId : "<?php print $view['id'] ?>btnCancel"
    },
    
    // Debug Settings
    debug: false
  });             
</script>