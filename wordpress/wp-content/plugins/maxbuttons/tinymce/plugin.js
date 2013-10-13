(function() {
	tinymce.create("tinymce.plugins.MaxButtons", {
		init: function(editor, url) {
			editor.addCommand("mceMaxButton", function() {
				editor.windowManager.open(
					{
						title: "Insert Button",
						file: url + "/dialog.php",
						width: 500,
						height: 500,
						inline: 1
					},
					{ plugin_url: url }
				)}
			);
			
			editor.addButton("MaxButtons", {
				title: "Insert Button",
				cmd: "mceMaxButton",
				image: url + "/button.png"
			})
		}
	});
	
	tinymce.PluginManager.add("MaxButtons", tinymce.plugins.MaxButtons);
})();
