=== Term Thumbnails ===
Contributors: Horttcore
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40horttcore%2ede
Tags: post thumbnail, thumbnail, term, taxonomy, featured image, image, block
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.4
Stable tag: 3.0.0
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Post Thumbnails for WordPress Terms.

== Description ==

Adds featured image (thumbnail) support to WordPress taxonomy terms — categories, tags, and any custom taxonomy.

Includes a Gutenberg block (`term-thumbnails/term-thumbnail`) for use in block-theme templates and the Query Loop block, with aspect ratio, scale, image size, and focal point controls.

== Installation ==

1. Put the plugin folder in your plugin directory and activate it in the WordPress backend.
1. Go to any term edit page and add an image.

== Gutenberg Block ==

The block can be placed in block-theme templates or inside a Query Loop block. Inspector controls:

* **Image size** — choose any registered image size
* **Aspect ratio** — Original, 1:1, 4:3, 3:4, 3:2, 2:3, 16:9, 9:16
* **Scale** — Cover, Contain, Fill (shown when an aspect ratio is set)
* **Focal point** — drag-to-set crop focus (shown when scale is Cover or Fill)

== Template Tags ==

* `get_term_thumbnail_id( $term_id )` — returns the attachment ID or false
* `has_term_thumbnail( $term_id )` — returns bool
* `the_term_thumbnail( $size, $attr )` — echoes the thumbnail for the current term archive
* `get_term_thumbnail( $term_id, $size, $attr )` — returns the `<img>` HTML

== Hooks ==

= Filters =

* `term-thumbnail-taxonomies` — Array of taxonomy slugs that support thumbnails. Defaults to all registered taxonomies.
* `{$taxonomy}-has-thumbnails` — Return `false` to disable thumbnail support for a specific taxonomy.
* `term_thumbnail_size` — Override the image size used by `get_term_thumbnail()`.
* `term_thumbnail_html` — Filter the final `<img>` HTML returned by `get_term_thumbnail()`.

== Frequently Asked Questions ==

= How do I remove thumbnail support from a specific taxonomy? =

Add this to your `functions.php`:

`add_filter( 'category-has-thumbnails', '__return_false' );`

= How do I limit thumbnails to one specific taxonomy? =

`add_filter( 'term-thumbnail-taxonomies', fn() => [ 'genre' ] );`

== Screenshots ==

1. Term listing
2. Term listing with image
3. Term edit page
4. Term edit page with image

== Changelog ==

= v3.0.0 =

* Complete rewrite: PSR-4 namespaced PHP 8.4 architecture
* Replaced jQuery admin JS with vanilla JS + @wordpress/api-fetch
* Replaced wp_ajax_* handlers with the WordPress REST API
* Added Gutenberg block with aspect ratio, scale, image size, and focal point controls
* Build tooling migrated to @wordpress/scripts
* Added Pest test suite

= v2.1.0 =

* Added: Composer support

= v2.0.1 =

* Fix: Remove warning

= v2.0.0 =

* Switch from options to term meta structure

= v1.0.2 =

* Fix: Missing term images for custom taxonomies

= v1.0.1 =

* Added: Screenshots
* Added: readme.txt
* Enhancement: Security improvements
* Fix: Typo on term edit page

= v1.0 =

* Initial release
