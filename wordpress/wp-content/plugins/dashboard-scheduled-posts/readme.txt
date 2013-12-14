=== Dashboard: Scheduled Posts ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: dashboard, widgets, dashboard widget
Requires at least: 2.7
Tested up to: 3.6
Stable tag: trunk

Widget for the WordPress 2.7+ dashboard to display scheduled posts.

== Description ==

Adds a widget to your dashboard displaying all scheduled posts.

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Installing The Plugin###

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload it to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - dashboard-scheduled-posts
            | dashboard-scheduled-posts.php
            | readme.txt`

Then just visit your admin area and activate the plugin.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Using The Plugin###

The new widget will show up automatically on your dashboard. If you use my [Dashboard Widget Manager plugin](http://wordpress.org/extend/plugins/dashboard-widget-manager/) and have specified a custom widget order, you will need to visit it's management page and add this new widget to your dashboard.

== Frequently Asked Questions ==

= Does this plugin support other languages? =

Yes, it does. See the [WordPress Codex](http://codex.wordpress.org/Translating_WordPress) for details on how to make a translation file. Then just place the translation file, named dashboard-scheduled-posts[value in wp-config].mo`, into the plugin's folder.

= I love your plugin! Can I donate to you? =

Sure! I do this in my free time and I appreciate all donations that I get. It makes me want to continue to update this plugin. You can find more details on [my donate page](http://www.viper007bond.com/donate/).

== ChangeLog ==

**Version 2.0.0**

* Complete recode for WordPress 2.7.

**Version 1.0.1**

* My fetch posts method was wrong. Switched to just a manual query. Not as future proof, but it works and seems to be the only way.
* Don't make the post title a link if the user can't edit it.

**Version 1.0.0**

* Initial release.