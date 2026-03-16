<?php

declare(strict_types=1);

use Brain\Monkey\Functions;

// -------------------------------------------------------------------------
// get_term_thumbnail_id()
// -------------------------------------------------------------------------

it('returns the attachment id as int when term meta is set', function (): void {
    Functions\expect('get_term_meta')
        ->once()
        ->with(42, '_thumbnail_id', true)
        ->andReturn('99');

    require_once __DIR__.'/../../inc/template-tags.php';

    expect(get_term_thumbnail_id(42))->toBe(99);
});

it('returns false when term meta is empty', function (): void {
    Functions\expect('get_term_meta')
        ->once()
        ->with(0, '_thumbnail_id', true)
        ->andReturn('');

    expect(get_term_thumbnail_id(0))->toBeFalse();
});

// -------------------------------------------------------------------------
// has_term_thumbnail()
// -------------------------------------------------------------------------

it('returns true when the term has a thumbnail', function (): void {
    Functions\expect('get_term_meta')
        ->once()
        ->with(5, '_thumbnail_id', true)
        ->andReturn('12');

    expect(has_term_thumbnail(5))->toBeTrue();
});

it('returns false when the term has no thumbnail', function (): void {
    Functions\expect('get_term_meta')
        ->once()
        ->with(5, '_thumbnail_id', true)
        ->andReturn('');

    expect(has_term_thumbnail(5))->toBeFalse();
});

// -------------------------------------------------------------------------
// get_term_thumbnail()
// -------------------------------------------------------------------------

it('returns an img tag when the term has a thumbnail', function (): void {
    Functions\expect('get_term_meta')
        ->once()
        ->with(7, '_thumbnail_id', true)
        ->andReturn('33');

    // apply_filters is called twice: once for size, once for html.
    // Use stubs() for both so each call passes through its first argument.
    Functions\stubs([
        'apply_filters' => static fn (string $tag, mixed $value) => $value,
    ]);

    Functions\expect('wp_get_attachment_image')
        ->once()
        ->with(33, 'post-thumbnail', false, '')
        ->andReturn('<img src="photo.jpg" />');

    expect(get_term_thumbnail(7))->toBe('<img src="photo.jpg" />');
});

it('returns an empty string when the term has no thumbnail', function (): void {
    Functions\expect('get_term_meta')
        ->once()
        ->with(7, '_thumbnail_id', true)
        ->andReturn('');

    Functions\stubs([
        'apply_filters' => static fn (string $tag, mixed $value) => $value,
    ]);

    Functions\expect('wp_get_attachment_image')->never();

    expect(get_term_thumbnail(7, 'thumbnail'))->toBe('');
});

// -------------------------------------------------------------------------
// get_term_thumbnail() — term_thumbnail_size filter
// -------------------------------------------------------------------------

it('applies term_thumbnail_size filter and uses the returned size', function (): void {
    Functions\expect('get_term_meta')
        ->once()
        ->andReturn('10');

    // Stub apply_filters: for the size hook return 'medium', for html passthrough.
    Functions\stubs([
        'apply_filters' => static function (string $tag, mixed $value) {
            if ($tag === 'term_thumbnail_size') {
                return 'medium';
            }

            return $value;
        },
    ]);

    Functions\expect('wp_get_attachment_image')
        ->once()
        ->with(10, 'medium', false, '')
        ->andReturn('<img />');

    $result = get_term_thumbnail(1, 'large');

    expect($result)->toBe('<img />');
});
