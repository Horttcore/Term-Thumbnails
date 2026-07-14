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
        $term_id = isset($block->context['termId'])
            ? (int) $block->context['termId']
            : (int) get_queried_object_id();
        $taxonomy = sanitize_key((string) ($block->context['taxonomy'] ?? ''));

        if (! $term_id || ! has_term_thumbnail($term_id)) {
            return '';
        }

        $term = $taxonomy ? get_term($term_id, $taxonomy) : get_term($term_id);
        $size_slug    = sanitize_key($attributes['sizeSlug'] ?? 'post-thumbnail');
        $aspect_ratio = $attributes['aspectRatio'] ?? '';
        $scale        = $attributes['scale'] ?? 'cover';
        $width        = $attributes['width'] ?? '';
        $height       = $attributes['height'] ?? '';
        $focal_point  = $attributes['focalPoint'] ?? null;

        // Focal point is only applied when the image is cropped.
        $has_focal_point = $focal_point
            && is_array($focal_point)
            && $aspect_ratio
            && $scale !== 'contain';

        // Build inline styles for the <img> element.
        $styles = [];

        if ($aspect_ratio) {
            $styles[] = 'width:100%';
            $styles[] = 'height:100%';
            $styles[] = 'object-fit:' . esc_attr((string) $scale);
        } elseif ($height) {
            $styles[] = 'height:' . esc_attr((string) $height);
        }

        if ($has_focal_point) {
            $x = round((float) $focal_point['x'] * 100);
            $y = round((float) $focal_point['y'] * 100);
            $styles[] = 'object-position:' . $x . '% ' . $y . '%';
        }

        $img_attrs = [];

        if ($styles) {
            $img_attrs['style'] = implode(';', $styles);
        }

        if ($aspect_ratio) {
            $img_attrs['class'] = 'has-aspect-ratio';
        }

        if (! empty($attributes['isLink']) && $term instanceof \WP_Term) {
            $term_link = get_term_link($term);

            if (! is_wp_error($term_link)) {
                $img_attrs['alt'] = $term->name;
            }
        }

        $image = get_term_thumbnail($term_id, $size_slug, $img_attrs);

        if (
            ! empty($attributes['isLink'])
            && $term instanceof \WP_Term
            && ! is_wp_error($term_link ?? null)
        ) {
            $link_target = '_blank' === ($attributes['linkTarget'] ?? '')
                ? '_blank'
                : '_self';

            $image = sprintf(
                '<a href="%1$s" target="%2$s"%3$s>%4$s</a>',
                esc_url($term_link),
                esc_attr($link_target),
                empty($attributes['rel'])
                    ? ''
                    : ' rel="' . esc_attr($attributes['rel']) . '"',
                $image,
            );
        }

        $wrapper_style = [];

        if ($aspect_ratio) {
            $wrapper_style[] = 'aspect-ratio:' . esc_attr((string) $aspect_ratio);
        }

        if ($width) {
            $wrapper_style[] = 'width:' . esc_attr((string) $width);
        }

        if ($height) {
            $wrapper_style[] = 'height:' . esc_attr((string) $height);
        }

        $wrapper_attributes = get_block_wrapper_attributes(
            $wrapper_style
                ? ['style' => implode(';', $wrapper_style)]
                : [],
        );

        return sprintf(
            '<figure %s>%s</figure>',
            $wrapper_attributes,
            $image,
        );
    }
}
