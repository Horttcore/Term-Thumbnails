<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails;

final class Block
{
    public function register(): void
    {
        register_block_type(
            plugin_dir_path(__FILE__) . '../block.json',
            [
                'render_callback' => [$this, 'render'],
            ],
        );
    }

    public function render(array $attributes, string $content, \WP_Block $block): string
    {
        $term_id = (int) ($block->context['termId'] ?? get_queried_object_id());

        if (! $term_id || ! has_term_thumbnail($term_id)) {
            return '';
        }

        $wrapper_attributes = get_block_wrapper_attributes();

        return sprintf(
            '<figure %s>%s</figure>',
            $wrapper_attributes,
            get_term_thumbnail($term_id, 'post-thumbnail'),
        );
    }
}
