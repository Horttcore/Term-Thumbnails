<?php

declare(strict_types=1);

namespace RalfHortt\TermThumbnails;

final class MetaMigration
{
    private const OPTION = 'term_thumbnails_meta_migrated';
    private const LEGACY_META_KEY = '_thumbnail_id';

    public function migrate(): void
    {
        if (get_option(self::OPTION, false)) {
            return;
        }

        foreach ((new MetaRegistration())->getTaxonomies() as $taxonomy) {
            $termIds = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'fields'     => 'ids',
            ]);

            if (is_wp_error($termIds)) {
                continue;
            }

            foreach ($termIds as $termId) {
                $legacyId = absint(get_term_meta((int) $termId, self::LEGACY_META_KEY, true));

                if ($legacyId && ! get_term_meta((int) $termId, MetaRegistration::META_KEY, true)) {
                    update_term_meta((int) $termId, MetaRegistration::META_KEY, $legacyId);
                }
            }
        }

        update_option(self::OPTION, time(), false);
    }
}
