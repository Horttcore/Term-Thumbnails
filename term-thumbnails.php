<?php

/**
 * Plugin Name:       Term Thumbnails
 * Plugin URI:        https://github.com/Horttcore/Term-Thumbnails
 * Description:       Featured images (thumbnails) for WordPress taxonomy terms.
 * Version:           3.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Ralf Hortt
 * Author URI:        https://horttcore.de
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       term-thumbnails
 * Domain Path:       /languages
 */

declare(strict_types=1);

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

// Composer autoloader.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Public template tags (global functions — no autoloading, always available).
require_once __DIR__ . '/inc/template-tags.php';

// Boot the plugin.
if (class_exists(\RalfHortt\TermThumbnails\Plugin::class)) {
    add_action('plugins_loaded', static function (): void {
        \RalfHortt\TermThumbnails\Plugin::boot();
    }, 5);
}
