<?php

declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\AdsPayedDate;
use SiteBundle\Helper\ConstantsHelper;

final class AdsPaymentView
{
    public function view(AdsPayedDate $adsPayedDate): array
    {
        $view = [
            'type' => $adsPayedDate->getType(),
            'status' => $adsPayedDate->getStatus(),
            'type_text' => ConstantsHelper::getConstantName($adsPayedDate->getType(), 'PAYMENT_PLAN', AdsPayedDate::class),
        ];

        return $view;
    }
}
