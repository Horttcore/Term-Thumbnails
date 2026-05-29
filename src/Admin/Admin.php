<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails\Admin;

use RalfHortt\TermThumbnails\Taxonomies\Taxonomies;

final class Admin
{
    private const NONCE_ACTION  = 'term_thumbnail_save';
    private const NONCE_FIELD   = 'term_thumbnail_nonce';
    private const META_KEY      = '_thumbnail_id';
    private const SCRIPT_HANDLE = 'term-thumbnails-admin';

    public function enqueueScriptsForScreen(string $hookSuffix): void
    {
        if (! in_array($hookSuffix, ['edit-tags.php', 'term.php'], true)) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $taxonomySlug = isset($_GET['taxonomy'])
            ? sanitize_key((string) wp_unslash($_GET['taxonomy']))
            : '';

        if (
            $taxonomySlug === ''
            || ! Taxonomies::supportsThumbnails($taxonomySlug)
        ) {
            return;
        }

        $this->enqueueScripts();
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_media();

        $assetFile = plugin_dir_path(__FILE__) . '../../build/admin.asset.php';

        if (file_exists($assetFile)) {
            $asset = require $assetFile;
        } else {
            $asset = [
                'dependencies' => [
                    'media-editor',
                    'wp-api-fetch',
                    'wp-dom-ready',
                ],
                'version' => '3.0.0',
            ];
        }

        wp_register_script(
            self::SCRIPT_HANDLE,
            plugins_url('../../build/admin.js', __FILE__),
            $asset['dependencies'],
            $asset['version'],
            true,
        );

        wp_register_style(
            self::SCRIPT_HANDLE,
            plugins_url('../../build/admin.css', __FILE__),
            [],
            $asset['version'],
        );

        wp_enqueue_script(self::SCRIPT_HANDLE);
        wp_enqueue_style(self::SCRIPT_HANDLE);

        $restBases = [];

        foreach (Taxonomies::getSlugs() as $slug) {
            if (! Taxonomies::supportsThumbnails($slug)) {
                continue;
            }

            $taxonomyObject = get_taxonomy($slug);

            if (
                $taxonomyObject
                && ! empty($taxonomyObject->show_in_rest)
                && ! empty($taxonomyObject->rest_base)
            ) {
                $restBases[$slug] = $taxonomyObject->rest_base;
            }
        }

        wp_localize_script(self::SCRIPT_HANDLE, 'termThumbnails', [
            'restBases' => $restBases,
        ]);
    }

    public function registerTaxonomyHooks(): void
    {
        foreach (Taxonomies::getSlugs() as $taxonomy) {
            if (! Taxonomies::supportsThumbnails($taxonomy)) {
                continue;
            }

            add_action($taxonomy . '_add_form_fields', [$this, 'renderAddFormField']);
            add_action($taxonomy . '_edit_form_fields', [$this, 'renderEditFormField']);
            add_action('created_' . $taxonomy, [$this, 'saveOnCreate']);
            add_action('edited_' . $taxonomy, [$this, 'saveOnEdit']);
            add_filter('manage_edit-' . $taxonomy . '_columns', [$this, 'addThumbnailColumn']);
            add_filter('manage_' . $taxonomy . '_custom_column', [$this, 'renderThumbnailColumn'], 10, 3);
        }
    }

    public function renderAddFormField(): void
    {
        $taxonomyLabel = $this->getTaxonomyLabel();
        ?>
        <div class="form-field term-thumbnail-field">
            <label for="term-thumbnail"><?php esc_html_e('Thumbnail', 'term-thumbnails'); ?></label>
            <div>
                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD); ?>
                <a
                    class="button remove-term-thumbnail term-thumbnail-btn is-hidden"
                    id="remove-term-thumbnail-new"
                    href="#"
                    data-id-field="#term-thumbnail-id-new"
                ><?php printf(esc_html__('Remove %s image', 'term-thumbnails'), esc_html($taxonomyLabel)); ?></a>
                <a
                    class="button add-term-thumbnail term-thumbnail-btn"
                    href="#"
                    data-id-field="#term-thumbnail-id-new"
                ><?php printf(esc_html__('Set %s image', 'term-thumbnails'), esc_html($taxonomyLabel)); ?></a>
                <input name="term-thumbnail-id" value="" id="term-thumbnail-id-new" type="hidden">
            </div>
        </div>
        <?php
    }

    public function renderEditFormField(\WP_Term $tag): void
    {
        $termId        = $tag->term_id;
        $taxonomyLabel = $this->getTaxonomyLabel();
        $hasThumbnail  = has_term_thumbnail($termId);
        ?>
        <tr class="form-field term-thumbnail-field">
            <th scope="row">
                <label for="term-thumbnail"><?php esc_html_e('Thumbnail', 'term-thumbnails'); ?></label>
            </th>
            <td>
                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD); ?>
                <?php if ($hasThumbnail) : ?>
                    <p class="term-thumbnail">
                        <?php echo get_term_thumbnail($termId, 'thumbnail'); ?>
                    </p>
                <?php endif; ?>

                <a
                    class="button remove-term-thumbnail term-thumbnail-btn<?php echo $hasThumbnail ? '' : ' is-hidden'; ?>"
                    id="remove-term-thumbnail-<?php echo esc_attr((string) $termId); ?>"
                    href="#"
                    data-id-field="#term-thumbnail-id-<?php echo esc_attr((string) $termId); ?>"
                ><?php printf(esc_html__('Remove %s image', 'term-thumbnails'), esc_html($taxonomyLabel)); ?></a>

                <a
                    class="button add-term-thumbnail term-thumbnail-btn<?php echo $hasThumbnail ? ' is-hidden' : ''; ?>"
                    href="#"
                    data-id-field="#term-thumbnail-id-<?php echo esc_attr((string) $termId); ?>"
                ><?php printf(esc_html__('Set %s image', 'term-thumbnails'), esc_html($taxonomyLabel)); ?></a>

                <input
                    name="term-thumbnail-id"
                    value="<?php echo esc_attr((string) ($hasThumbnail ? get_term_thumbnail_id($termId) : '')); ?>"
                    id="term-thumbnail-id-<?php echo esc_attr((string) $termId); ?>"
                    type="hidden"
                >
            </td>
        </tr>
        <?php
    }

    public function addThumbnailColumn(array $columns): array
    {
        $columns['term-thumbnail'] = esc_html__('Thumbnail', 'term-thumbnails');

        return $columns;
    }

    public function renderThumbnailColumn(string $content, string $columnName, int $termId): string
    {
        if ('term-thumbnail' !== $columnName) {
            return $content;
        }

        $taxonomyLabel = $this->getTaxonomyLabel();
        $hasThumbnail  = has_term_thumbnail($termId);

        ob_start();
        ?>
        <div class="term-thumbnail-field">
            <?= get_term_thumbnail($termId, 'thumbnail', ['class' => 'term-thumbnail']) ?>
            <p>
                <a
                    class="button remove-term-thumbnail term-thumbnail-btn<?php echo $hasThumbnail ? '' : ' is-hidden'; ?>"
                    href="#"
                    data-term-id="<?php echo esc_attr((string) $termId); ?>"
                ><?php printf(esc_html__('Remove image'), esc_html($taxonomyLabel)); ?></a>
                <a
                    class="button add-term-thumbnail term-thumbnail-btn<?php echo $hasThumbnail ? ' is-hidden' : ''; ?>"
                    href="#"
                    data-term-id="<?php echo esc_attr((string) $termId); ?>"
                ><?php printf(esc_html__('Set image'), esc_html($taxonomyLabel)); ?></a>
            </p>
        </div>
        <?php

        return ob_get_clean() ?: '';
    }

    public function saveOnCreate(int $termId): void
    {
        if (! $this->verifyNonce() || ! $this->canEditTerm($termId)) {
            return;
        }

        $attachmentId = isset($_POST['term-thumbnail-id'])
            ? absint($_POST['term-thumbnail-id'])
            : 0;

        if ($attachmentId > 0) {
            $this->setThumbnail($termId, $attachmentId);
        }
    }

    public function saveOnEdit(int $termId): void
    {
        if (! $this->verifyNonce() || ! $this->canEditTerm($termId)) {
            return;
        }

        if (! isset($_POST['term-thumbnail-id'])) {
            return;
        }

        $attachmentId = absint($_POST['term-thumbnail-id']);

        if ($attachmentId === 0) {
            $this->deleteThumbnail($termId);
        } else {
            $this->setThumbnail($termId, $attachmentId);
        }
    }

    public function onDeleteTerm(int $termId): void
    {
        $this->deleteThumbnail($termId);
    }

    private function setThumbnail(int $termId, int $attachmentId): void
    {
        if ($termId === 0 || $attachmentId === 0) {
            return;
        }

        update_term_meta($termId, self::META_KEY, $attachmentId);
    }

    private function deleteThumbnail(int $termId): void
    {
        if ($termId === 0) {
            return;
        }

        delete_term_meta($termId, self::META_KEY);
    }

    private function canEditTerm(int $termId): bool
    {
        return $termId > 0 && current_user_can('edit_term', $termId);
    }

    private function verifyNonce(): bool
    {
        if (! isset($_POST[self::NONCE_FIELD])) {
            return false;
        }

        return (bool) wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD])),
            self::NONCE_ACTION,
        );
    }

    private function getTaxonomyLabel(): string
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $taxonomySlug = isset($_GET['taxonomy'])
            ? sanitize_key($_GET['taxonomy'])
            : '';

        if (! $taxonomySlug) {
            return '';
        }

        $taxonomy = get_taxonomy($taxonomySlug);

        return $taxonomy ? $taxonomy->labels->singular_name : '';
    }
}
