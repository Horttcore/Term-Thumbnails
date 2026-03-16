<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails;

final class Block
{
    public function register(): void
    {
        register_block_type(
            plugin_dir_path(__FILE__).'../block.json',
            [
                'render_callback' => [$this, 'render'],
            ],
        );
    }

    public function render(array $attributes, string $content, \WP_Block $block): string
    {
        $term_id = (int) ($block->context['termId'] ?? get_queried_object_id());

        if (!$term_id || !has_term_thumbnail($term_id)) {
            return '';
        }

        $size_slug = $attributes['sizeSlug'] ?? 'post-thumbnail';
        $aspect_ratio = $attributes['aspectRatio'] ?? '';
        $scale = $attributes['scale'] ?? 'cover';
        $width = $attributes['width'] ?? '';
        $height = $attributes['height'] ?? '';
        $focal_point = $attributes['focalPoint'] ?? null;

        // Focal point is only applied when the image is cropped.
        $has_focal_point = $focal_point
            && is_array($focal_point)
            && $aspect_ratio
            && $scale !== 'contain';

        // Build inline styles for the <img> element.
        $styles = [];

        if ($aspect_ratio) {
            $styles[] = 'aspect-ratio:'.esc_attr($aspect_ratio);
            $styles[] = 'object-fit:'.esc_attr($scale);
            $styles[] = 'height:100%';
        }

        if ($width) {
            $styles[] = 'width:'.esc_attr($width);
        }

        if ($height) {
            $styles[] = 'height:'.esc_attr($height);
        }

        if ($has_focal_point) {
            $x = round((float) $focal_point['x'] * 100);
            $y = round((float) $focal_point['y'] * 100);
            $styles[] = 'object-position:'.$x.'% '.$y.'%';
        }

        $img_attrs = [];

        if ($styles) {
            $img_attrs['style'] = implode(';', $styles);
        }

        if ($aspect_ratio) {
            $img_attrs['class'] = 'has-aspect-ratio';
        }

        $wrapper_attributes = get_block_wrapper_attributes();

        return sprintf(
            '<figure %s>%s</figure>',
            $wrapper_attributes,
            get_term_thumbnail($term_id, $size_slug, $img_attrs),
        );
    }
}
