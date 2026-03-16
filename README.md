# Term Thumbnails

[![CI](https://github.com/Horttcore/Term-Thumbnails/actions/workflows/ci.yml/badge.svg)](https://github.com/Horttcore/Term-Thumbnails/actions/workflows/ci.yml)

Post Thumbnails for WordPress Terms.

## Requirements

- PHP 8.4+
- WordPress 6.0+

## Installation

- Drop the plugin into your plugin directory and activate it in the WordPress backend.
- Go to any term page and add an image.

## Gutenberg Block

The plugin registers a `term-thumbnails/term-thumbnail` block for use in block-theme templates and the Query Loop block. It supports:

- **Image size** — choose any registered image size
- **Aspect ratio** — Original, 1:1, 4:3, 3:4, 3:2, 2:3, 16:9, 9:16
- **Scale** — Cover, Contain, Fill (shown when an aspect ratio is set)
- **Focal point** — drag-to-set crop focus (shown when scale is Cover or Fill)

## Template Tags

```php
get_term_thumbnail_id( int $term_id ): int|false
has_term_thumbnail( int $term_id ): bool
the_term_thumbnail( string|array $size = 'post-thumbnail', string|array $attr = '' ): void
get_term_thumbnail( int $term_id, string|array $size = 'post-thumbnail', string|array $attr = '' ): string
```

## Hooks

### Filters

| Filter | Description |
|---|---|
| `term-thumbnail-taxonomies` | Array of taxonomy slugs that support thumbnails. Defaults to all registered taxonomies. |
| `{$taxonomy}-has-thumbnails` | Return `false` to disable thumbnail support for a specific taxonomy. |
| `term_thumbnail_size` | Override the image size used by `get_term_thumbnail()`. |
| `term_thumbnail_html` | Filter the final `<img>` HTML returned by `get_term_thumbnail()`. |

### Examples

```php
// Disable thumbnails for categories.
add_filter( 'category-has-thumbnails', '__return_false' );

// Limit thumbnails to a custom taxonomy only.
add_filter( 'term-thumbnail-taxonomies', fn() => [ 'genre' ] );
```

## Screenshots

### Term listing
[![Term listing](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-1.png)](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-1.png)

### Term listing with image
[![Term listing with image](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-2.png)](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-2.png)

### Term edit page
[![Term edit page](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-3.png)](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-3.png)

### Term edit page with image
[![Term edit page with image](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-4.png)](https://raw.github.com/Horttcore/Term-Thumbnails/master/screenshot-4.png)

## Changelog

### v3.0.0

- Complete rewrite: PSR-4 namespaced PHP 8.4 architecture
- Replaced jQuery admin JS with vanilla JS + `@wordpress/api-fetch`
- Replaced `wp_ajax_*` handlers with the WordPress REST API (`register_term_meta` with `show_in_rest: true`)
- Added Gutenberg block (`term-thumbnails/term-thumbnail`) with aspect ratio, scale, image size, and focal point controls
- Build tooling migrated to `@wordpress/scripts`
- Added Pest test suite

### v2.1.0

- Added: Composer support

### v2.0.1

- Fix: Remove warning

### v2.0.0

- Switch from options to term meta structure

### v1.0.2

- Fix: Missing term images for custom taxonomies

### v1.0.1

- Added: Screenshots
- Added: readme.txt
- Enhancement: Security improvements
- Fix: Typo on term edit page

### v1.0

- Initial release
