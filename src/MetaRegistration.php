<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails;

final class MetaRegistration
{
    public const META_KEY = 'term_thumbnail_id';

    public function register(): void
    {
        foreach ($this->getTaxonomies() as $taxonomy) {
            register_term_meta($taxonomy, self::META_KEY, [
                'show_in_rest'      => true,
                'single'            => true,
                'type'              => 'integer',
                'default'           => 0,
                'auth_callback'     => [$this, 'canEdit'],
                'sanitize_callback' => 'absint',
            ]);
        }
    }

    /**
     * @return string[]
     */
    public function getTaxonomies(): array
    {
        $taxonomies = [];

        foreach (get_taxonomies([], 'objects') as $taxonomy => $object) {
            if (empty($object->show_ui) || empty($object->show_in_rest)) {
                continue;
            }

            if (false === apply_filters($taxonomy . '-has-thumbnails', true)) {
                continue;
            }

            $taxonomies[] = $taxonomy;
        }

        return (array) apply_filters('term-thumbnail-taxonomies', $taxonomies);
    }

    public function canEdit(bool $allowed, string $metaKey, int $termId, int $userId): bool
    {
        return $allowed && user_can($userId, 'edit_term', $termId);
    }
}
