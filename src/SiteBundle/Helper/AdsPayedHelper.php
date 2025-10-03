<?php

namespace SiteBundle\Helper;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsPayedDate;
use \DateTime;

class AdsPayedHelper
{
    /**
     * @param Ads $ads
     *
     * @return bool
     */
    public function getActivePayedAd(Ads $ads): bool
    {
        $payedAd = $ads->getPayedTypes();

        if (null !== $payedAd) {
            $now = new DateTime();

            return $now <= $payedAd->getDate();
        }

        return false;
    }
}
