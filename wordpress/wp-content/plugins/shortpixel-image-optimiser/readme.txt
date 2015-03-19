=== ShortPixel Image Optimiser ===

Contributors: AlexSP
Tags: picture,  optimization, image editor, pngout, upload speed, shortpixel, compression, jpegmini, webp, lossless, cwebp, media, tinypng, jpegtran,image, image optimisation, shrink, picture, photo, optimize photos, compress, performance, tinypng, crunch, pngquant, attachment, optimize, pictures,fast, images, image files, image quality, lossy, upload, kraken, resize, seo, smushit, optipng, kraken image optimizer, ewww, photo optimization, gifsicle, image optimizer, images, krakenio, png, gmagick, image optimize, pdf, pdf optimisation, optimise pdf, shrink pdf, jpg, jpeg, jpg optimisation, optimise jpg, shrink jpg, gif, animated gif, optimise gif
Requires at least: 3.0.0 or higher
Tested up to: 4.1.1
Stable tag: 2.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The ShortPixel plugin reduces the images' size making your website load faster. Image quality is preserved using advanced compression technology.

== Description ==

ShortPixel is an image compression tool that helps improve your website performance. The plugin optimises images automatically using both lossy and lossless compression. Resulting, smaller, images are no different in quality from the original.

ShortPixel uses powerful algorithms that enable your website to load faster, use less bandwidth and rank better in search. The unique API key you receive for activating the plugin can be used for multiple websites.

**The ShortPixel package includes:**

* **Both lossy and lossless optimisation:** you can choose between the two types of compression. Lossy for photographs. Lossless for technical drawings, clip art and comics.
* **One API Key for multiple sites:** after registration, you receive an API key that you can further use on several websites or applications.
* **Up to 90% compression rate:** with lossy compression images that were 3MB can crunch to 307Kb, with no before/after differences..
* **Supported formats:** JPG, PNG, PDF, both static and animated GIFS. NEW UPDATE: we recently introduced optimisation for PDFs.
* **Backup and restore originals:** if you ever want to return to the original version, images are automatically stored in a backup folder on your hosting servers.
* **Bulk image optimisation:** Crunch your image gallery, and downsize your website. This feature may take up to several hours, depending on the number and size of existing images. 

On the https://ShortPixel.com website, we offer free access to the ShrtPixel API which you can use for further image optimisation purposes.

== Installation ==

Let's get ShortPixel plugin running on your WordPress website:


1. Sign up using your email at https://shortpixel.com/wp-apikey
2. You will receive your personal API key in a confirmation email, to the address you provided.
3. Upload the ShortPixel plugin to the /wp-content/plugins/ directory
4. Use your unique API key to activate ShortPixel plugin in the 'Plugins' menu in WordPress.
5. Uploaded images can be automatically optimised in the Media Library.
6. Done!


== Frequently Asked Questions ==

= What happens to the existing images, when installing the ShortPixel plugin? = 

Just installing the plugin won’t start the optimisation process on existing images. To begin optimising the images previously loaded on your website, you should:

* Go to **Media Library**, and select which of the existing images you want to optimise.

OR

* Use the **Bulk ShortPixel** option, to automatically optimise all your previous library.

= Should I pick lossy or lossless optimisation? =

This depends on your compression needs. **Lossy** has a better compression rate than lossless compression. The resulting image is not 100% identical with the original. Works well for photos taken with your camera.

With **lossless** compression, the shrunk image will be indistinguishable from the original, and smaller in size. Use this when you do not want to loose any of the original image's details. Works best for technical drawings, clip art and comics.

For more information about the difference read the <a href="http://en.wikipedia.org/wiki/Lossy_compression#Lossy_and_lossless_compression" target="_blank">Wiki article</a> on the lossy/lossless difference.

= Why do I need an API key? =

ShortPixel Image Optimiser uses automated processes to crunch images. The ShortPixel API integrates in the dashboard of your WordPress website and processes both old and new images automatically. You can also use the same API, multiple times, in your own applications, the <a href="https://shortpixel.com/api-docs">Documentation API</a> shows you how.

= Where do I get my API key? =

To get your API key, you must <a href="https://shortpixel.com/wp-apikey">Sign up to ShortPixel</a>. You will receive your personal API key in a confirmation email to the address you provided. Use your API key to activate ShortPixel plugin in the 'Plugins' menu in WordPress.

= Where do I use my API key? =

You use the API key in the ShortPixel plugin Settings (don’t forget to click Save Settings). The same API key can be used on multiple websites/blogs. 

= How does Bulk Optimisation work? = 

The Bulk option makes ShortPixel optimise all your images at once (not one by one). You can do this in the Media > Bulk ShortPixel section by clicking on the **Compress all your images** button.

The batch optimisation may work slower, depending on your existing image gallery. Please be patient and do not close the Wordpress admin while you are rolling the Bulk Processing on your media gallery.

= Are my images safe? =

Yes, privacy is guaranteed. The ShortPixel encryption process doesn't allow anyone to view your photos.

= What happens with my original images after they have been processed with ShortPixel? =

Your images are automatically stored in a backup folder, on your hosting server. After optimisation, if you want to switch back to a certain original image, hit **Restore backup** in the Media Library. If you are happy with the ShortPixel optimised images, you can deactivate saving the backups in the plugin Settings.

= What types of formats can be optimised? =

For now, ShortPixel supports JPEG, PNG, PDF and GIF formats. Animated GIFs and thumbnails are also optimised. Additional formats are scheduled for optimisation in the future. 

= I’m stuck. What do I do? =

The ShortPixel team is here to help. <a href="https://shortpixel.com/contact">Contact us</a>!


== Screenshots ==

1. Activate your API key in the plugin Settings. (Settings>ShortPixel)

2. Compress all your past images with one click. (Media>Bulk)

3. Your stats: number of processed files, saved space, average compression, saved bandwidth, remaining images. (Settings>ShortPixel)

4. Restore to original image. (Media>Library)

== Changelog ==

= 2.1.0 =

* speedier file download from API resource
* SQL changed to use less CPU intensive queries
* improved BULK processing logic, faster results
* different small fixes & improvements
* skip processed images when running bulk processing


= 2.0.8 =

* improved logic for processed files to be downloaded every time
* sometimes the processing script stopped before finishing all the files to be optimized, fixed
* improved processing speed for bulk processing

= 2.0.7 =

* fixed issue with "missing" images
* save plugin version for easier debugging
* list mode is set for media library for first time run 
* fixed bug that prevented backuped files to remove when the original was removed

= 2.0.6 =

* different small fixes

= 2.0.5 =

* small improvement to make the optimization of newly added images faster.
* fixed condition when the PDF files weren't processed
* different improvements

= 2.0.4 =

* fixed recursive backup directory size counter
* added backup with subdirectory structure to handle many files
* empty backup can handle subdirectories & sets the right flag for backup restore
* latest images are optimized first
* check for missing images on disk but still linked in DB

= 2.0.3 = 

* added extra check for bad server responses
* 10 files/post request for file processing
* updated error codes according to API v2
* updated description 

= 2.0.2 =

* added more tags so we better describe newest features

= 2.0.1 =

* some improvements to bulk processing 
* PDF files are also optimized now
* fixed a thumb processing bug that caused extra API requests

= 2.0.0 =

* SP plugin uses API v2 and the processing speed is significantly improved

= 1.6.10 =

* Corrected a bug affecting option saving for some of the users.

= 1.6.9 =

* Optimize now option only appears when the image wasn't optimized

= 1.6.8 =

* Bulk Processing optimized to skip images that were already optimized with the same options when Bulk Processing is run multiple times
* changed the place where original (backup) files are stored
* extra check for missing(expired) processed images

= 1.6.7 =

* extra check for exif_imagetype function 

= 1.6.6 =

* changed method from GET to POST for API Key validation
* bulk optimization text update

= 1.6.5 =

* plugin tested for WP 4.1

= 1.6.4 =

* API validation URL changed to v1

= 1.6.3 =

* fallback to http if plugin activation fails for https
* added error mesage API Key validation fail

= 1.6.2 =

* extra check for images that return 3xx/4xx codes to be ignored
* API Key validation (error) message is returned to user
* error messages for images are displayed in the "ShortPixel Compression" column

= 1.6.1 = 

* fixed small upload glitch
* added succes message upon bulk processing completion
* improved image backup 
* lossy option by default upon plugin installation

= 1.6.0 =

* images' requests for optimization are sent for all sizes upon image upload in media gallery
* non-image (e.g. PDF files) are ignored now @ bulk processing
* bulk optimization improved & some bugs fixed.
* FAQ/Description small changes

= 1.5.1 = 

* readme changes

= 1.5.0 =

* pictures are removed from backup as well when deleted
* restore backup warning/error fixed
* fixed useless/bad AJAX requests that occured sometimes
* added user agent to API Key validation for debugging purposes
* Bulk Processing was freezing for some users, fixed this + added Cancel button

= 1.4.1 =

* optimize again overwrote the original image, fixed
* fixed restore errors
* changes to FAQ/Description texts

= 1.4.0 =

* Bulk image processing improved so it can optimize all the images in background while admin page is open
* small changes in readme.txt descrption

= 1.3.5 =

* fixed broken link in settings page
* updated FAQ
* description updated

= 1.3.2 =

* fixed missing action link @ Bulk Processing
* added more screenshots

= 1.3.1 =

* possible fix for API key validation failing
* added backup and restore for images that are processed with shortpixel
* optimize now feature on Media Library

= 1.0.6 =

* bulk processing runs in background now.

= 1.0.5 =

* extra check for the converted images to be safely copied from ShortPixel

= 1.0.4 =

* corrections and additions to readme.txt and wp-shortpixel.php

= 1.0.3 =

* minor bug fixes

= 1.0.2 =

* Updated Bulk editing to run in background
* Updated default options
* Added notifications on activation

= 1.0 =

* First working version

