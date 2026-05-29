<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails;

use RalfHortt\TermThumbnails\Admin\Admin;
use RalfHortt\TermThumbnails\Blocks\TermThumbnail;
use RalfHortt\TermThumbnails\Meta\MetaRegistration;

final class Plugin
{
    private Admin $admin;
    private TermThumbnail $block;
    private MetaRegistration $metaRegistration;

    public function __construct()
    {
        $this->admin = new Admin();
        $this->block = new TermThumbnail();
        $this->metaRegistration = new MetaRegistration();
    }

    public static function boot(): self
    {
        $instance = new self();
        $instance->registerHooks();

        return $instance;
    }

    private function registerHooks(): void
    {
        add_action('init', [$this->block, 'register']);
        add_action('init', [$this->metaRegistration, 'register'], 20);

        if (is_admin()) {
            add_action('wp_loaded', [$this->admin, 'registerTaxonomyHooks']);
            add_action('admin_enqueue_scripts', [$this->admin, 'enqueueScriptsForScreen']);
            add_action('delete_term', [$this->admin, 'onDeleteTerm'], 10, 1);
        }
    }
}
