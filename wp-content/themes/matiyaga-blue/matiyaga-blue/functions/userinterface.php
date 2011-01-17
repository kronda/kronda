
<div class="wrap">
<h2><?php echo $padd_themename; ?> Options</h2>
<form method="post">
	
	<h3>General Settings</h3>	
	<div>General settings for <?php echo $padd_themename; ?> theme to work.</div>
	<table class="form-table">
	<?php
		foreach ($options_general as $opt) {
			echo $opt->toHTMLForm();
		}
	?>
	</table>
	<h3>Featured Gallery Slideshow Settings</h3>
	<div>Gallery slideshow settings for <?php echo $padd_themename; ?> theme to work.</div>
	<table class="form-table">
	<?php
		foreach ($options_gallery as $opt) {
			echo $opt->toHTMLForm();
		}
	?>
	</table>
	<h3>Googe Adsense Settings</h3>
	<div>Google Adsense settings for <?php echo $padd_themename; ?> theme.</div>
	<table class="form-table">
	<?php
		foreach ($options_google as $opt) {
			echo $opt->toHTMLForm();
		}
	?>
	</table>
	
	<h3>Custom Advertisement Settings</h3>
	<div>You can make your own advertisement in this settings.</div>
	<table class="form-table">
		<?php
		foreach ($options_yourads as $opt) {
			echo $opt->toHTMLForm();
		}
	?>
	</table>

	<p class="submit">
		<button class="button" name="action" type="submit" value="save">Save Settings</button>
		<button class="button" name="action" type="submit" value="reset">Reset</button>
	</p>
</form>
</div>

