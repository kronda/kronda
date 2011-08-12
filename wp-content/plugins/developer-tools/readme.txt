=== Developer Tools ===
Contributors: kjmeath
Donate link: http://developertools.kjmeath.com/donate/
Tags: theme, developer, development, tool, tools, enqueue, script, scripts, cufon, sifr, swf, object, dd, belated, png, fix, ie, ie6, html5, shiv, google, analytics, excerpt, length, menu, menus, sidebar, sidebars, custom, taxonomy, taxonomies, post, type, types, feature, featured, image, thumbnail, thumbnails, disable, meta, box, boxes, field, fields, size, quality, core, update, updates, plugin, tinymce, auto, formating, auto-formating, hide, admin, login, dashboard, widgets, widget, krumo, database, backup, db, sql, buddy, sqlbuddy, management, login, log-in, log, in, multisite, remove, admin, tool, bar, modernizr, selectivizr, custom, background, header, theme, options
Requires at least: 3.0.0
Tested up to: 3.1.3
Stable tag: 1.1.3

Streamline your WordPress development!  Creates admin UI for many of the code-enabled features in WP and includes commonly used JavaScript libraries.

== Description ==

Streamline your WordPress development!

The Developer Tools plugin creates an admin user interface for many of the code-enabled features in WordPress and commonly used JavaScript libraries.  It also generate template code for theme development.

<strong>[Plugin home page](http://developertools.kjmeath.com/ "Plugin home page")</strong>

<strong>[Forum / Support](http://wordpress.org/tags/developer-tools/ "Forum / Suppport")</strong>

<strong>Features:</strong>

* Load WordPress core JavaScript libraries such as jQuery, Scriptaculous, Prototype, Thickbox and many many more.
* Cufon JavaScript utility for font replacement
* sIFR Flash + JavaScript utility for font replacement
* SWF Object JavaScript utility for embedding flash media on a webpage
* DD Belated PNG fix JavaScript utility for IE6
* Modernizr JavaScript utility
* :select[ivizr] JavaScript utility
* HTML5 shiv JavaScript utility
* Google Analytics
* Automatically open external links in a new window / tab.
* JavaScript required message
* Update excerpt length
* Create WordPress menus with the Menu Manager
* Create sidebars
* Create custom taxonomies
* Create custom post types
* Enable feature image thumbnails
* Disable meta boxes on the page and post post-types
* Add image thumbnail size
* Add Post Formats
* Change uploaded images re-sizing quality
* Enable background image theme option
* Enable custom header theme option
* Add addtional custom theme options
* Disable WordPress core updates
* Disable WordPress plugin updates
* Disable TinyMCE auto formatting
* Disable TinyMCE visual editor
* Hide admin menu items
* Hide the admin tool bar
* Disable dashboard widgets
* Custom login image

<strong>[Feature requests](http://wordpress.org/tags/developer-tools/ "Feature requests")</strong>

If you're willing to help translate my plugin please contact me.

== Installation ==

1. Download and unzip the Developer Tools plugin
2. Upload 'developer-tools' folder to the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Have a feature request? =

[Forum / Support](http://wordpress.org/tags/developer-tools/ "Forum / Suppport")


== Screenshots ==

1. Developer Tools - Settings

== Changelog ==

= 1.1.3 =
* Fixed "Enable custom header theme option" feature when using twentyten theme

= 1.1.2 =
* Added "Enable background image theme option" feature
* Added "Enable custom header theme option" feature
* Added "Custom Theme options" feature
* Added Modernizr JavaScript utility feature
* Added :select[ivizr] JavaScript utility feature
* Updated admin user interface
* Re-factored field class
* Completed select list field class 
* Fixed advanced fields error when setting an advanced field
* Removed server configuration panel
* Removed Enabled features panel
* Updated database managment tools for all admin user accounts
* Fixed deprecated has_cap capabilities error when define('WP_DEBUG', true); is enabled in wp-config.php

= 1.1.1 =
* Fixed Error log RSS feed url on welcome screen
* Added "Disable TinyMCE visual editor" feature
* Updated template code for add post type to include "post_status=publish"
* Updated "Hide Developer Tools plugin" feature
* Updated "Hide Default admin" feature

= 1.1.0 =
* Hid required field indicator if feature value is set

= 1.0.9 =
* Fixed remove button not appearing after save
* Hid required field indicator after duplicating a feature
* Minor style update to distinguish between features better
* Added private and hierarchical fields to custom post type feature

= 1.0.8 = 
* Included missing models dir

= 1.0.7 =
* Fixed Warning: array_merge() [function.array-merge]: Argument #2 bug when saving
* Updated saved value checking method
* Fixed required field indicators

= 1.0.6 =
* Fixed Fatal error from making 1.0.5 update

= 1.0.5 =
* Fixed incorrect version in readme

= 1.0.4 =
* Fixed JavaScript "click me" alert

= 1.0.3 =
* Moved Application.php to com/app/MainApplication.php
* Removed deprecated call_user_method() function in MainApplication.php to fix bug when loading fieldData models
* Updated "advanced fields" button bug
* Fixed remove button bug

= 1.0.2 =
* Updated: SQL Buddy database library to version 1.3.3
* Added Post Format Feature for WP 3.1 release

= 1.0.1 =
* Bugfix: Remove admin menu bar feature for WP 3.1 release
* Updated: Localization 

= 1.0.0 =
Public release

== Upgrade Notice ==

= 1.0.0 =
N/A
