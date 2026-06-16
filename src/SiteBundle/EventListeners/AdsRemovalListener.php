<?php

declare(strict_types=1);

namespace SiteBundle\EventListeners;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use SiteBundle\Entity\Ads;
use SiteBundle\Repository\AdsInfoPageRepository;

/**
 * Ensures the linked AdsInfoPage (and therefore each AdsInfoPageImage::postRemove
 * lifecycle event) fires before the DB-level ON DELETE CASCADE on linked_ads_id
 * deletes the row outside Doctrine's UoW.
 */
final class AdsRemovalListener
{
    public function __construct(
        private readonly AdsInfoPageRepository $adsInfoPageRepository
    ) {
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (false === $entity instanceof Ads) {
            return;
        }

        $infoPage = $this->adsInfoPageRepository->findOneByLinkedAds($entity);

        if (null === $infoPage) {
            return;
        }

        $args->getObjectManager()->remove($infoPage);
    }
}
