<?php

declare(strict_types=1);

namespace AdminBundle\Handler;

use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\Ads;
use SiteBundle\Helper\ValidatorHelper;
use SiteBundle\Repository\AdsRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Webmozart\Assert\Assert;

final class ProductEditHandler
{
    private ValidatorHelper $validator;

    private AdsRepository $adsRepository;

    public function __construct(
        ValidatorHelper $validator,
        AdsRepository $adsRepository
    ) {
        $this->validator = $validator;
        $this->adsRepository = $adsRepository;
    }

    /**
     * @param EntityInterface $entity
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function save(EntityInterface $entity): void
    {
        Assert::isInstanceOf($entity, Ads::class);

        $errors = $this->validator->validate($entity, null, "SetAdAdmin");

        if ($errors->count() > 0) {
            throw new UnprocessableEntityHttpException(json_encode($this->validator->parseErrors($errors)));
        }

        if (is_null($entity->getId())) {
            $this->adsRepository->persist($entity);
        }

        $this->adsRepository->flush();
    }

    public function changeStatus(Ads $ads, int $status): void
    {
        $ads->setStatus($status);

        $this->adsRepository->flush();
    }

    public function remove(Ads $ads): void
    {
        $this->adsRepository->delete($ads);
        $this->adsRepository->flush();
    }
}