=== Term Thumbnails ===
Contributors: Horttcore
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40horttcore%2ede
Tags: post thumbnail, thumbnail, term, taxonomy, feature image, image
Requires at least: 3.7
Tested up to: 4.0
Stable tag: 1.0.2
License: GPLv2
License URI: http://www.opensource.org/licenses/GPL-2.0

Post Thumbnails for WordPress Terms

== Description ==

Post Thumbnails for WordPress Terms

== Installation ==

1. Put the plugin file in your plugin directory and activate it in your WP backend.
1. Go to any term page and add images

== Screenshots ==

1. Term listing
2. Term listing with image
3. Term edit page
4. Term edit page with image

== Language Support ==

* english
* german

== Template tags ==

* `get_term_thumbnail_id( $term_id )`
* `has_term_thumbnail( $term_id )`
* `the_term_thumbnail( $size, $attr )`
* `get_term_thumbnail( $term_id, $size, $attr )`

== Hooks ==

= Filters =

* `term-thumbnail-taxonomies` - Taxonomies that should support term thumbnails; expects an array with taxonomies
* `$taxonomy-has-thumbnails` - Remove term thumbnail support for taxonomy; expects bool value

== Frequently Asked Questions ==

= How do I remove thumbnail support from a specific taxonomy =

For removing support for categories put this in your functions.php
`add_filter( 'category-has-thumbnails', '__return_false' );`

== Changelog ==

= v1.0.2 =

* Fix: No term images for custom taxonomies

= v1.0.1 =

* Added: Screenshots
* Added: readme.txt
* Enhancement: Security improvements
* Fix: Typo on term edit page

= v1.0 =

* Initial release
