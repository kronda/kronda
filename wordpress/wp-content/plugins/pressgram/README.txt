=== Pressgram ===
Contributors: pressgram, UaMV
Donate link: 	
Tags: pressgram, photos, filters, instagram, twitter, facebook, pictures, photo sharing, publishing
Requires at least: 3.5.2
Tested up to: 3.9
Stable tag: 2.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The official WordPress plugin for Pressgram. Automated management of Pressgram posts helps you publish pictures worth 1,000 words!

== Description ==

Our mission is to publish pictures worth 1,000 words. The official WordPress plugin for <a href="http://pressgr.am/">Pressgram</a> allows you automated management of the many awesome images you post to your own blog from the Pressgram app.

= What does the plugin do? =
Once activated and configured, the Pressgram plugin will handle all incoming posts from Pressgram. It will ...

* Apply fine control settings according to your custom setup and any powertags you've defined.
* Feature Pressgram images via a simple and clean Pressgram widget.
* Write metadata to your database identifying the post and the images as from Pressgram.

= How do I set up the plugin? =
Upon activation, the plugin will prompt you to configure settings for Pressgram management. *(found at Settings > Media)*

1. Select categories to add as Pressgram categories. *(note: as of v2.2.0 category must be assigned to post in-app)*
1. Define custom fine control settings to handle posts to the category. *(note: override settings in-app via powertags)*
1. Enable post types for which you would like to allow Pressgram relation.
1. Set query filters for home page and feeds.
1. If desired, add the Pressgram widget to sidebars.

= What fine control settings are included? =
The following fine control presets are customizable to your blog:

* Show/hide Pressgram defined category posts from home page.
* Show Pressgram defined category posts on home page only if they also contain a non-Pressgram related category.
* Show/hide Pressgram defined category posts from rss feeds.
* Show Pressgram defined category posts in feeds only if they also contain a non-Pressgram related category.
* Post Type: select from available post types or select 'Unattached Media'
* Post Status: select from Published, Pending, Draft, or Private
* Post Format: select from available post formats
* Featured Image Control: set first image as featured image *(or not)*
* Comment Status: allow or disallow
* Trackback Status: allow or disallow
* Post Content Removal: remove first image
* Gallery: when multiple images are posted, create a gallery

= How do post relations and the widget work? =
Pressgram post relation is created for all posts made from Pressgram. Post relation can be broken or created for posts by use of the checkbox found in the Publish metabox of posts. The widget allows you to display from 1 to 16 images in a grid of one to four columns for recent posts that are related to Pressgram. Images link to the associated Pressgram post on your blog.

= What are powertags? =
Powertags allow you in-app control of how the plugin will process a single Pressgram post. Use the following tags in-app and you control how your content is published and displayed:

* **{post.type:post-type}**  (post-type must be an existing post type slug: ex. post, attachment)
* **{post.status:post-status}**  (post-status options include: publish, pending, draft, private)
* **{post.format:post-format}**  (post-format must be a supported post format: ex. image, aside, standard)
* **{featured.img:set/unset}**  (selecting set or unset will control featured image assignment)
* **{comments:open/closed}**  (comment status must open or closed)
* **{pings:open/closed}**  (ping status must be open or closed)
* **{remove.img:set/unset}**  (selecting set or unset will control removal of first image)
* **{gallery:set/unset}** (selecting set or unset will control whether a gallery is created)

Download the <a href="http://pressgr.am/">mobile app here</a> and support our community with <a href="http://store.pressgr.am/">some official swag</a>!

== Other Notes ==

= Tutorial =

The following tutorial is for an earlier version of the Pressgram plugin. Note that the interface and options have changed slightly.

[youtube http://www.youtube.com/watch?v=_DJqGpx9zdQ]

= Understanding the Metadata =

Prior to Version 2.0.5, Pressgram posts and image attachments were not tagged with metadata. However, as of Version 2.0.5, each post and image attachment is tagged with metadata identifying it's source as the Pressgram app. This metadata is now used to query images for display in the Pressgram widget. In future versions, it may be used for other features. If you would like to tag your existing Pressgram posts and image attachments with the necessary metadata, do one of the following:

**Adding _pressgram_post and _pressgram_image metadata:**

1. Insert the following code in your theme's functions.php file.
1. Define the array containing post IDs.
1. Define the array containing attachment IDs.
1. Deactivate the Pressgram plugin.
1. Reactivate the Pressgram plugin.
1. Remove the code from your theme's functions.php file.

`/**
 * Add Pressgram metadata to specific posts
 * Note: Posts via Pressgram prior to use of v2.0.5 do not contain metadata
 */
function add_pressgram_meta() {
	// Array of post ids which you would like to mark as a Pressgram post
	// e.g. $post_ids = array( 2, 7, 14, 16, 19 );
	$post_ids = array(  );

	// Array of attachment ids which you would like to mark as a Pressgram image
	// e.g. $image_ids = array( 1, 6, 13, 15, 18 );
	$image_ids = array(  );

	// Loop through posts and add metadata
	foreach ( $post_ids as $post_id ) {
		add_post_meta( $post_id, '_pressgram_post', TRUE, TRUE );
	}

	// Loop through images and add metadata
	foreach ( $image_ids as $image_id ) {
		add_post_meta( $image_id, '_pressgram_image', TRUE, TRUE );
	}
}
// Do this on Pressgram plugin activation
add_action( 'activate_pressgram/pressgram.php', 'add_pressgram_meta' );`

**Adding/Removing _pressgram_post metadata:**

As of Version 2.1.0, you can add/remove _pressgram_post metadata by enabling Post Relation on the settings screen and checking/unchecking the Pressgram Post option in the Publish metabox.


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

1. Pressgram Administration Options
2. Pressgram Widget Options
3. Pressgram Post Relation

== Changelog ==

= 2.2.3 =
* Fix: Include gallery only if more than one image exists

= 2.2.2 =
* Allows processing to post types that do not support categories
* Fixes for powertags
* Adds gallery option & powertag

= 2.2.1 =
* Fixes PHP Warning in some instances
* Adds XMLRPC restriction variable

= 2.2.0 =
* Allowing control of multiple categories.
* Removed hashtag, alignment, and image link options.
* Organized code.

= 2.1.4 =
* Adding tutorial video

= 2.1.3 =
* Adding finer control for home feed and rss feed inclusion/exclusion of posts
* Allowing custom post type inclusion in home feed adn rss feed

= 2.1.2 =
* Fixing horizontal scrolling issue

= 2.1.1 =
* Fixing accessibility of settings fields

= 2.1.0 =
* Improving regex for hashtag to post tag translation
* Fixing auto-categorization following in-app switch from default category of 'Uncategorized' to 'Pressgram'
* Adding Pressgram-to-post relations for control of posts featured in widget
* Adding dismissible plugin notices on the settings screen
* Adding Pressgram branding on the settings screen

= 2.0.5 =
* Adding metadata to identify Pressgram posts and images
* Fix to allow publicizing via Jetpack
* Switching widget query from use of category to post meta

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
