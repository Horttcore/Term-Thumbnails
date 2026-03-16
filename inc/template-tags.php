<?php

declare(strict_types=1);

// Make sure we don't expose any info if called directly.
if (! function_exists('add_action')) {
    exit;
}

/**
 * Retrieve the attachment ID for a term's thumbnail.
 *
 * @param int $term_id Term ID.
 * @return int|false Attachment ID or false if not set.
 */
function get_term_thumbnail_id(int $term_id = 0): int|false
{
    $thumbnail_id = get_term_meta($term_id, '_thumbnail_id', true);

    return $thumbnail_id ? (int) $thumbnail_id : false;
}

/**
 * Check whether a term has a thumbnail set.
 *
 * @param int $term_id Term ID.
 * @return bool
 */
function has_term_thumbnail(int $term_id = 0): bool
{
    return false !== get_term_thumbnail_id($term_id);
}

/**
 * Output the thumbnail image for the current term archive.
 *
 * @param string|int[]  $size Image size slug or [width, height] array. Default 'post-thumbnail'.
 * @param string|array  $attr Optional. HTML attributes for the <img> tag.
 */
function the_term_thumbnail(string|array $size = 'post-thumbnail', string|array $attr = ''): void
{
    if (is_category()) {
        $term_id = (int) get_query_var('cat');
    } elseif (is_tag()) {
        $term_id = (int) get_queried_object_id();
    } elseif (is_tax()) {
        $term_id = (int) get_queried_object_id();
    } else {
        return;
    }

    echo get_term_thumbnail($term_id, $size, $attr); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Return the thumbnail <img> HTML for a term.
 *
 * @param int           $term_id Term ID.
 * @param string|int[]  $size    Image size slug or [width, height] array. Default 'post-thumbnail'.
 * @param string|array  $attr    Optional. HTML attributes for the <img> tag.
 * @return string HTML <img> tag or empty string.
 */
function get_term_thumbnail(int $term_id = 0, string|array $size = 'post-thumbnail', string|array $attr = ''): string
{
    $term_thumbnail_id = get_term_thumbnail_id($term_id);

    /** @var string|int[] $size */
    $size = apply_filters('term_thumbnail_size', $size);

    $html = $term_thumbnail_id
        ? wp_get_attachment_image($term_thumbnail_id, $size, false, $attr)
        : '';

    return (string) apply_filters('term_thumbnail_html', $html, $term_id, $term_thumbnail_id, $size, $attr);
}
