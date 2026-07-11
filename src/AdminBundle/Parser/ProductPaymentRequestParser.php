<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use AdminBundle\Dto\Product\ProductSaveRequest;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsPayedDate;
use SiteBundle\Repository\Adspayeddaterepository;

final class ProductPaymentRequestParser
{
    public function __construct(
        private readonly Adspayeddaterepository $adspayeddaterepository,
    ) {
    }

    public function parse(ProductSaveRequest $dto, Ads $ads): void
    {
        $paymentDateEntity = $this->adspayeddaterepository->findOneBy(['ads' => $ads]);

        if (null !== $paymentDateEntity) {
            if (null === $dto->paymentDate) {
                $this->adspayeddaterepository->delete($paymentDateEntity);

                return;
            }
            $paymentDateEntity->setDate(new \DateTimeImmutable($dto->paymentDate));

            return;
        }

        if (null === $dto->paymentDate) {
            return;
        }

        $paymentDateEntity = $this->create();
        $paymentDateEntity->setAds($ads);
        $paymentDateEntity->setDate(new \DateTimeImmutable($dto->paymentDate));
    }

    public function create(): AdsPayedDate
    {
        return new AdsPayedDate();
    }
}
