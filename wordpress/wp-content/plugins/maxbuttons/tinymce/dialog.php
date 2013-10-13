<?php
require('../../../../wp-load.php');

$published_buttons = maxbuttons_get_published_buttons();
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php _e('Insert Button', 'maxbuttons') ?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo MAXBUTTONS_PLUGIN_URL ?>/styles.css" />
	<script type="text/javascript" src="<?php echo includes_url() ?>js/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?php echo includes_url() ?>js/tinymce/tiny_mce_popup.js"></script>
	<script type="text/javascript">
		function insertShortcode(button_id) {
			var shortcode = '';
			if (button_id != '') {
				shortcode = '[maxbutton id="' + button_id + '"]';
			}
			
			if (window.tinyMCE) {
				window.tinyMCE.execInstanceCommand("content", "mceInsertContent", false, shortcode);
				tinyMCEPopup.editor.execCommand("mceRepaint");
				tinyMCEPopup.close();
			}
		}
	</script>
</head>

<body>

<div id="maxbuttons">
	<div class="tinymce">
		<p><?php _e('Choose the button you want to insert from the list below. The shortcode will be placed into the content editor at the location of the cursor.', 'maxbuttons') ?></p>
		
		<table cellpadding="5" cellspacing="5" width="100%">
		<?php foreach ($published_buttons as $button) { ?>
			<tr>
				<td>
					<a href="#" onclick="insertShortcode(<?php echo $button->id ?>); return false;"><?php _e('Insert This Button', 'maxbuttons') ?></a> <span class="raquo">&raquo;</span>
				</td>
				<td style="padding: 15px 0px 15px 0px;">
					<?php echo do_shortcode('[maxbutton id="' . $button->id . '" externalcss="false" ignorecontainer="true"]') ?>
				</td>
			</tr>
		<?php } ?>
		</table>
	</div>
</div>

</body>

</html>
