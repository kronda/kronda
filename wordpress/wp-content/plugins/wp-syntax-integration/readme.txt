=== Plugin Name ===
Contributors: diekleinehexe
Donate link: http://www.effinger.org
Tags: wp-syntax, quicktags, tinymce, highlighter, syntax, code
Requires at least: 3.3
Tested up to: 3.7.1
Stable tag: trunk

Adds new buttons to the visual and html editor window which allow to use WP-Syntax.

== Description ==

This plugin will add a button to both the visual and HTML WordPress Editor panel allowing you to easily insert code for WP-Syntax. WP-Syntax is a syntax highlighting plugin for WordPress which can be downloaded from http://wordpress.org/extend/plugins/wp-syntax/

**Usage**

1. Select the text you want to have highlighted by WP-Syntax
1. Click the button pre (WP-Syntax) in HTML editing mode or the icon with colored lines in visual editing mode
1. Enter the language and starting line separated by a comma. The later is optional.

== Installation ==

1. Upload the wp-syntax-integration folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= If I enter HTML tags in visual mode and click the button, most of the HTML tags disappear! =

This is a known limitation which is caused by TinyMCE stripping out HTML tags which are not allowed according to its settings. A workaround is to do the editing in HTML mode (see usage).

= I can't get the TinyMCE (Rich Text) buttons to display! =

Make sure that you have uploaded the entire contents of the wp-syntax-integration folder.  If it still doesn't work, drop me a line.

== Changelog ==

= 0.2 =
Adjusted plugin to work with Wordpress 3.3 and above in plain text editor. Note that versions prior to Wordpress 3.3 are not supported any longer.

= 0.1 =
* Initial release

== Screenshots ==

1.  The first image screenshot-1.png shows the rich text / TinyMCE editor version of the editing screen.
1.  The second image screenshot-2.png shows the plain text / HTML editor version of the editing screen.
