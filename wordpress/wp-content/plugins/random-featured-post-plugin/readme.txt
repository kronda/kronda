=== Random Featured Post ===
Contributors: scott@mydollarplan.com
Donate link: http://www.mydollarplan.com/random-featured-post-plugin/
Tags: random, featured, home, favorite, post
Requires at least: 2.3
Tested up to: 2.5.1
Stable tag: 1.1.3

The Random Featured Post plugin allows you to display a random post from a designated category as a "featured" post.

== Description ==

The Random Featured Post plugin allows you to display a random post from a designated category. Ideally this should 
draw readers landing on your homepage to some of your best posts. The featured post will display the title 
"Featured Post" (customizable) along with the post's title, an excerpt and a link to continue to the full post.

== Installation ==

1. Download and unzip featuredpost.zip to your plugin folder.
2. Activate the plugin from the Plugins section of your dashboard.
3. (Optional) Create a "Featured" category.
4. Go to the "Featured Post" options in your dashboard and select the categories from which you would like posts to be randomly selected.
5. Check the "Show Featured Post" box and click "Update" to save your changes.
6. Place a call to the show\_featured\_post() function in your template, most likely in your main index template just before the loop.

== Additional Notes ==

* The show\_featured\_post($PreFeature = '', $PostFeature = '', $AlwaysShow = false, $categoryID = 0, $NumberOfPosts = 0) function has five optional arguments. 
The first two are for any HTML you would like to display before and after the featured post. The third argument is a 
boolean (true/false) argument which will override the \'Show Featured Post\' option and always display the featured post when true. The fourth option allows you to 
specify a single category to display from, overriding the selections in settings page. The fifth option allows you to show more than one post at a time, overriding 
the settings page option.
* All output is contained in a DIV class named "featuredpost" and can be adjusted via your stylesheet.

== Frequently Asked Questions ==

Please see the [plugin home page](http://www.mydollarplan.com/random-featured-post-plugin/ "Random Featured Post Plugin") for more information.
