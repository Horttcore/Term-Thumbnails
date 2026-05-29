<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails\Meta;

use RalfHortt\TermThumbnails\Taxonomies\Taxonomies;

final class MetaRegistration
{
    public function register(): void
    {
        foreach (Taxonomies::getSlugs() as $taxonomy) {
            if (! Taxonomies::supportsThumbnails($taxonomy)) {
                continue;
            }

            register_term_meta($taxonomy, '_thumbnail_id', [
                'show_in_rest'      => [
                    'schema' => [
                        'type'    => 'integer',
                        'default' => 0,
                    ],
                ],
                'single'            => true,
                'type'              => 'integer',
                'default'           => 0,
                'auth_callback'     => static function (
                    bool $allowed,
                    string $metaKey,
                    int $termId,
                ): bool {
                    unset($allowed, $metaKey);

                    if ($termId <= 0) {
                        return false;
                    }

                    return current_user_can('edit_term', $termId);
                },
                'sanitize_callback' => static function ($value) {
                    if ($value === null || $value === '') {
                        return 0;
                    }

                    return absint($value);
                },
            ]);
        }
    }
}
