<?php

namespace SiteBundle\Services\Ads;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Media;
use SiteBundle\Entity\Reservation;

class AdsEmailDataPreparation
{
    public function prepareAdsEmailData(Ads $ads, Reservation $reservation, array $data)
    {
        $mediaCollection = $ads->getMedia()->filter(function ($file) {
            /** @var Media $file */
            return true === $file->getIsmain();
        });

        $media = $mediaCollection->first();

        $data['templateData'] = [
            'ads' => $ads,
            'reservation' => $reservation,
            'media' => $media
        ];

        return $data;
    }
}