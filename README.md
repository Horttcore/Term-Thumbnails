Term-Thumbnails
===============

Post Thumbnails for WordPress Terms

## Installation

* Put the plugin file in your plugin directory and activate it in your WP backend.
* Go to any term page and add images

## Language Support

* english
* german

## Template tags

* `get_term_thumbnail_id( $term_id )`
* `has_term_thumbnail( $term_id )`
* `the_term_thumbnail( $size, $attr )`
* `get_term_thumbnail( $term_id, $size, $attr )`

## Hooks

### Filters

* `term-thumbnail-taxonomies` - Taxonomies that should support term thumbnails; expects an array with taxonomies
* `$taxonomy-has-thumbnails` - Remove term thumbnail support for taxonomy; expects bool value

## Changelog

### v1.0

* Initial release
