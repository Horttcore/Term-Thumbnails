<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails;

final class Plugin
{
    private Admin $admin;
    private Block $block;
    private MetaRegistration $metaRegistration;

    public function __construct()
    {
        $this->admin = new Admin();
        $this->block = new Block();
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
        add_action('plugins_loaded', [$this, 'loadTextdomain']);
        add_action('init', [$this->metaRegistration, 'register']);
        add_action('init', [$this->block, 'register']);

        if (is_admin()) {
            add_action('wp_loaded', [$this->admin, 'registerTaxonomyHooks']);
            add_action('admin_print_scripts-edit-tags.php', [$this->admin, 'enqueueScripts']);
            add_action('delete_term', [$this->admin, 'onDeleteTerm'], 10, 4);
        }
    }

    public function loadTextdomain(): void
    {
        load_plugin_textdomain(
            'term-thumbnails',
            false,
            dirname(plugin_basename(__FILE__)) . '/../languages/',
        );
    }
}
