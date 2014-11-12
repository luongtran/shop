=== Category Thumbnail ===
Contributors: dirtysuds, pathawks
Donate link: https://github.com/pathawks/Category-Thumbnail
Tags: WordPress,Post,plugin,posts,images,image,shortcode,thumbnail,category
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.5
Tested up to: 4.0
Stable tag: 1.03

Adds shortcode `[catthumb]` to embed a thumbnail image for a category.

== Description ==

`[catthumb id="CategoryID" link="true"]` will find the most recent post in the specified Category with a featured image and return the thumbnail for that image. If *link="true"* is specified, the image will link to the category specified.

The plugin will only search *featured images*, not *attached images*. It will not look for images embedded in the post itself, or other attached images. If a category does not have any posts with featured images, this plugin will not return a thumbnail image, even if the category has posts with attached images.

== Installation ==

1. Upload `dirtysuds-category-thumbnail` to the `/wp-content/plugins/` directory
2. Activate **DirtySuds - Category Thumbnail** through the 'Plugins' menu in WordPress
3. In the page editor, add the shortcode `[catthumb id="CategoryID"]` where _CategoryID_ is category id number. This _must be a number, not the category slug or name_


== Frequently Asked Questions ==

= Can I use `catthumb` in a template? =

Of course! The syntax is very similar to the shortcode syntax. Just add the following line to your template.

	<?php dirtysuds_category_thumb(array('id'=>'CategoryID')); ?>

You just need to replace *CategoryID* with the id number of the category.

= I have an idea for a great way to improve this plugin =

Please open a pull request on [Github](https://github.com/pathawks/Category-Thumbnail)


== Changelog ==

= 1.03 20121011 =
* Bugfix

= 1.02 20121011 =
* Cleaned up code a bit
* Make use of transients

= 1.01.20121202 =
* Cleaned up code a bit
* Now supports Jetpack Photon images

= 1.00.20110226 =
* First version
* Works
