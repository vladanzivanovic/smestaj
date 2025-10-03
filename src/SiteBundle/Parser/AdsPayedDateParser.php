<?php
declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsPayedDate;
use SiteBundle\Entity\EntityStatusInterface;

class AdsPayedDateParser
{
    public function parse(Ads $ads, int $type, ?\DateTimeInterface $activeTo = null): AdsPayedDate
    {
        $activePayment = $ads->getActivePayment();
        $now = new \DateTimeImmutable();

        $payedDate = $this->create();

//        if (null === $activePayment) {

            $payedDate->setDate($activeTo);
            $payedDate->setStatus(EntityStatusInterface::STATUS_ACTIVE);
            $payedDate->setType($type);
            $payedDate->setAds($ads);

            if (null !== $activePayment) {
                $activePayment->setStatus(EntityStatusInterface::STATUS_ARCHIVED);

                if (null === $activeTo) {
                    $payedDate->setDate($activePayment->getDate());
                }
            }

            $activePayment = $payedDate;
//        }

        return $activePayment;
    }

    public function create(): AdsPayedDate
    {
        return new AdsPayedDate();
    }
}
