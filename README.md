Term Thumbnails
===============

Post Thumbnails for WordPress Terms

## Installation

* Put the plugin file in your plugin directory and activate it in your WP backend.
* Go to any term page and add images

## Screenshots

### Term listing
[![Term listing](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-1.png)](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-1.png)

### Term listing with image
[![Term listing with image](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-2.png)](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-2.png)

### Term edit page
[![Term edit page](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-3.png)](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-3.png)

### Term edit page with image
[![Term edit page with image](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-4.png)](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-4.png)

## Language Support

* english
* german

## Template tags

* `get_term_thumbnail_id( $term_id )`
* `has_term_thumbnail( $term_id )`
* `the_term_thumbnail( $size, $attr )`
* `get_term_thumbnail( $term_id, $size, $attr )`

## Frequently Asked Questions

### How do I remove thumbnail support from a specific taxonomy

For removing support for categories put this in your functions.php
`add_filter( 'category-has-thumbnails', '__return_false' );`

## Hooks

### Filters

* `term-thumbnail-taxonomies` - Taxonomies that should support term thumbnails; expects an array with taxonomies
* `$taxonomy-has-thumbnails` - Remove term thumbnail support for taxonomy; expects bool value

## Changelog

### v1.0.2

* Fix: No term images for custom taxonomies

### v1.0.1

* Added: Screenshots
* Added: readme.txt
* Enhancement: Security improvements
* Fix: Typo on term edit page

### v1.0

* Initial release
