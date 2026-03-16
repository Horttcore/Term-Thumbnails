<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails;

final class MetaRegistration
{
    public function register(): void
    {
        $taxonomies = apply_filters('term-thumbnail-taxonomies', get_taxonomies());

        foreach ($taxonomies as $taxonomy) {
            if (false === apply_filters($taxonomy . '-has-thumbnails', true)) {
                continue;
            }

            register_term_meta($taxonomy, '_thumbnail_id', [
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => 'integer',
                'default'       => 0,
                'auth_callback' => static function (): bool {
                    return current_user_can('edit_posts');
                },
                'sanitize_callback' => 'absint',
            ]);
        }
    }
}
