<?php

declare(strict_types=1);

namespace SiteBundle\Services\InfoPage;

/*
 * Main-image invariant for AdsInfoPage.
 *
 * Enforces that whenever an AdsInfoPage has at least one image attached,
 * exactly one of those images is flagged isMain. Called from the admin
 * save path immediately after image reconciliation and before flush.
 */

use SiteBundle\Entity\AdsInfoPage;

final class AdsInfoPageMainImageValidator
{
    /**
     * @return array<string, string> field path => first violation message
     */
    public function validate(AdsInfoPage $entity): array
    {
        if (0 === $entity->getImages()->count()) {
            return [];
        }

        $mainCount = 0;

        foreach ($entity->getImages() as $image) {
            if (true === $image->isMain()) {
                $mainCount++;
            }
        }

        if (1 === $mainCount) {
            return [];
        }

        return ['images' => 'Tačno jedna slika mora biti označena kao glavna.'];
    }
}
