=== Pressgram ===
Contributors: pressgram, UaMV
Donate link: http://pressgr.am/
Tags: pressgram, photos, filters, instagram, twitter, facebook, pictures, photo sharing
Requires at least: 3.5.2
Tested up to: 3.6.0
Stable tag: 2.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The official WordPress plugin for <a href="http://pressgr.am/">Pressgram</a>. Automated management of your awesome Pressgram images!

== Description ==

The official WordPress plugin for <a href="http://pressgr.am/">Pressgram</a>. De-clutters the home page / home screen from all those awesome images you're posting! Auto-categorizes images uploaded to your site from Pressgram and applies presets  to these posts (post type, post status, post format, featured image control, image alignment, image link, comment status, ping status, hashtag to post tag translation, and removal of specific content). Power tags allow you to control posting options directly from the app. The widget allows you to display your recent images in the sidebar. Oh, and it also encourage digital publishers to be <strong>rebels with a cause</strong>. Viva la revolución!

This plugin allows you to select a category that will be used for your Pressgram photos (bottom of Settings Panel >> Media). It will ...

1. auto-categorize all Pressgram posts to this selected category,
1. remove those photos from showing up on the main feed (homepage) and rss feed of your blog,
1. apply your custom fine control settings to each Pressgram post, and
1. allow you to display recent photos via the widget (provided they are set as a featured image).

Note, that photos will continue to display in your search and in your archives. Options are available to allow photos on your home page and/or in your RSS feed (for yummy SEO inclusion!).

Power users can make use of power tags. Use the following tags in posts from app and you can control how your content is published and displayed:

* **_t:post-type**  (post-type must be an existing post type slug: ex. post, attachment)
* **_s:post-status**  (post-status options include: publish, pending, draft, private)
* **_f:post-format**  (post-format must be a supported post format: ex. image, aside, standard)
* **_i:featured-image**  (featured-image must be either t or f and will control featured image assignment)
* **_a:image-alignment**  (image-alignment options: left, center, right, none)
* **_l:image-link**  (image-link options: link, post, none)
* **_c:comment-status**  (comment status must be t or f)
* **_p:ping-status**  (ping status must be t or f)
* **_h:hashtag-to-post-tag-translation**  (hashtag-to-post-tag must be t or f)
* **_r:removal-of-content**  (remove content options: hashtags, text, image)

Download the <a href="http://pressgr.am/">mobile app here</a> and support our community with <a href="http://store.pressgr.am/">some official swag</a>!

[youtube http://www.youtube.com/watch?v=7H41GL6EFfI]

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' Plugin Dashboard
2. Select `pressgram.zip` from your computer
3. Upload
4. Activate the plugin on the WordPress Plugin Dashboard
5. Visit Settings > Media and set your custom options

= Using FTP =

1. Extract `pressgram.zip` to your computer
2. Upload the `pressgram` directory to your `wp-content/pressgram` directory
3. Activate the plugin on the WordPress Plugins dashboard
4. Visit Settings > Media and set your custom options

== Screenshots ==

1. The Pressgram Administration options

== Changelog ==

= 2.0.4 =
* Removing anonymous function in widgets_init() for PHP support < 5.3.0 fixing Parse error: unexpected T_FUNCTION

= 2.0.3 =
* Adding Pressgram widget

= 2.0.2 =
* Adding options for showing in home feed and in rss feeds

= 2.0.1 =
* Removing line of code that was for dev purposes
* Allowing images to show in feeds

= 2.0.0 =
* Adding auto-categorization of Pressgram posts
* Adding fine control options
* Adding power tag functionality

= 1.0.5 =
* One more update to the YouTube embed in the description

= 1.0.4 =
* Updating the YouTube video in the plugin description

= 1.0.3 =
* Adding additional tags to the plugin description
* Adding a video to the README

= 1.0.2 =
* Fixing a typo in the plugin description in the README and header

= 1.0.1 =
* Fixing plugin description and link in the README

= 1.0.0 =
* Initial release

= 0.6.0 =
* Updating the plugin's description
* Updating localization files
* Updating code comments

= 0.5.0 =
* Making sure the plugin description also includes anchors to Pressgram and a bolded description

= 0.4.0 =
* Fixing a bug with not listing Pressgram posts in the dashboard
* Updating the short and long description

= 0.3.0 =
* Removing unused attribute
* Updating references to the company name
* Updating the plugin description
* Updating localization
* Updating the screenshot

= 0.2.0 =
* Updating typos in the plugin description
* Changing activation notice link to the proper page
* Localized the plugin
* Added Select2 for the dropdown option for the categories
* Added administrative JavaScript files (and minified files) for the dashboard
* Making sure images are only excluded from the main blog loop (they are searchable and in the archives now)

= 0.1.0 =
* Initial alpha release for internal testing
