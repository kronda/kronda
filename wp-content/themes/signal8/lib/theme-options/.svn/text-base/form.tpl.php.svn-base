<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php echo $this->theme_name ?> Options</h2>
	<?php if (isset($this->tpl_vars['saved'])): ?>
		<div id="message" class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
			<p>Settings Saved</p>
		</div>
	<?php endif ?>

	<form method="post" class="" enctype="multipart/form-data">
		<table border="0" cellspacing="0" cellpadding="0" class="form-table">
			<?php foreach ($this->options as $option): ?>
				<?php echo $option->render() ?>
			<?php endforeach ?>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" value="Save"></td>
			</tr>
		</table>
	</form>
</div>