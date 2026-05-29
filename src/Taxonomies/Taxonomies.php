<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails\Taxonomies;

final class Taxonomies
{
    /**
     * Taxonomy slugs that support term thumbnails.
     *
     * @return string[]
     */
    public static function getSlugs(): array
    {
        $taxonomies = get_taxonomies(['show_ui' => true], 'names');

        /** @var string[] $taxonomies */
        $taxonomies = apply_filters('term-thumbnail-taxonomies', $taxonomies);

        return array_values($taxonomies);
    }

    public static function supportsThumbnails(string $taxonomy): bool
    {
        return false !== apply_filters($taxonomy . '-has-thumbnails', true);
    }
}
