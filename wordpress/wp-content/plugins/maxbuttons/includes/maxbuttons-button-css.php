<?php
require('../../../../wp-load.php');
?>

<html>
	<head>
		<title><?php _e('Button CSS', 'maxbuttons') ?></title>
		<?php wp_head() ?>
		<style type="text/css">
			body {
				margin: 20px;
			}
			p {
				line-height: 1.5em;
				font-family: Arial, Helvetica;
			}
			.note {
				width: 550px;
				text-align: left;
			}
			#maxbutton-css {
				width: 700px;
				height: 500px;
				font-family: Consolas, 'Courier New', 'Courier';
				font-size: 13.5px;
				margin-top: 20px;
			}
		</style>
	</head>
	
	<body>
		<div align="center">
			<div class="note">
				<p><?php _e('If the "Use External CSS" option is enabled for this button, copy and paste the CSS code below into your theme stylesheet.', 'maxbuttons') ?></p>
			</div>
			<textarea id="maxbutton-css"><?php echo do_shortcode('[maxbutton id="' . $_GET['id'] . '" externalcsspreview="true"]') ?></textarea>
		</div>
	</body>
	
	<?php wp_footer() ?>
</html>
