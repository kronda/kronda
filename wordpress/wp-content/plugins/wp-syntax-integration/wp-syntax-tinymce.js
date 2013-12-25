function edWPSyntaxGetPreStartTag() {
	var syntaxInfo = prompt('Please insert syntax highlighting information:'+"\n"+'language[,line number]!', 'php,1').split(',');
	return '<pre escaped="true" lang="'+syntaxInfo[0]+'"'+(syntaxInfo[1] != undefined ? ' line="'+syntaxInfo[1]+'"' : '')+'>'+"\n";
}

(function() {	
	tinymce.create('tinymce.plugins.wpsyntaxintegration', {
		init : function(ed, url) {
			// Register commands, mceMyown is name of command to be executed.
			ed.addCommand('wpsyntaxtag', function() {
				tinyMCE.execCommand('mceReplaceContent',false,edWPSyntaxGetPreStartTag()+'{$selection}</pre>');return false;
			});

			// Register buttons,this is the button will be displayed on wordpress rich editor
			ed.addButton('wpsyntaxintegration', {title : 'Enclose with pre block of WP-Syntax', cmd : 'wpsyntaxtag', image:url + '/wp-syntax-tinymce.png'});
		},

		getInfo : function() {
			return {
				longname : 'WP-Syntax Integration',
				author : 'Markus Effinger',
				authorurl : 'http://www.effinger.org',
				infourl : 'http://www.effinger.org',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wpsyntaxintegration', tinymce.plugins.wpsyntaxintegration);
})();