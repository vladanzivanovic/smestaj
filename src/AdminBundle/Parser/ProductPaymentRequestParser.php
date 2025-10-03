<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsPayedDate;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Repository\Adspayeddaterepository;
use Symfony\Component\HttpFoundation\ParameterBag;

final class ProductPaymentRequestParser
{
    private Adspayeddaterepository $adspayeddaterepository;

    public function __construct(
        Adspayeddaterepository $adspayeddaterepository
    ) {

        $this->adspayeddaterepository = $adspayeddaterepository;
    }

    public function parse(ParameterBag $bag, Ads $ads): void
    {
        $paymentDateEntity = $this->adspayeddaterepository->findOneBy(['ads' => $ads]);

        if (null !== $paymentDateEntity) {
            if (false === $bag->has('payment_date')) {
                $this->adspayeddaterepository->delete($paymentDateEntity);

                return;
            }
            $paymentDateEntity->setDate(new \DateTimeImmutable($bag->get('payment_date')));

            return;
        }

        if (false === $bag->has('payment_date')) {
            return;
        }

        $paymentDateEntity = $this->create();
        $paymentDateEntity->setAds($ads);
        $paymentDateEntity->setDate(new \DateTimeImmutable($bag->get('payment_date')));
    }

    public function create(): AdsPayedDate
    {
        return new AdsPayedDate();
    }
}