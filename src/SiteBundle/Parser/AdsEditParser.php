<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\AdsSaveRequest;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Helper\TextHelper;
use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Services\Ads\AdsImageService;
use Symfony\Component\Security\Core\User\UserInterface;

class AdsEditParser
{
    private TextHelper $textHelper;

    private YouTubeParser $youTubeParser;

    private AdsImageService $adsImageService;

    private AdsContactUserParser $adsUserParser;

    private AdsTagParser $adsTagParser;

    private CategoryRepository $categoryRepository;

    private CityRepository $cityRepository;

    private AdsPayedDateParser $adsPayedDateParser;

    public function __construct(
        TextHelper $textHelper,
        YouTubeParser $youTubeParser,
        AdsImageService $adsImageService,
        AdsContactUserParser $adsUserParser,
        AdsTagParser $adsTagParser,
        CategoryRepository $categoryRepository,
        CityRepository $cityRepository,
        AdsPayedDateParser $adsPayedDateParser
    ) {
        $this->textHelper = $textHelper;
        $this->youTubeParser = $youTubeParser;
        $this->adsImageService = $adsImageService;
        $this->adsUserParser = $adsUserParser;
        $this->adsTagParser = $adsTagParser;
        $this->categoryRepository = $categoryRepository;
        $this->cityRepository = $cityRepository;
        $this->adsPayedDateParser = $adsPayedDateParser;
    }

    public function parse(
        AdsSaveRequest $dto,
        UserInterface $user,
        ?UserInterface $owner,
        ?Ads $ads = null
    ): Ads {
        if (null === $ads) {
            $ads = $this->create();
            $ads->setStatus(EntityStatusInterface::STATUS_PENDING);
        }

        $ads->setTitle($dto->title);
        $ads->setDescription($this->textHelper->clearText($dto->description));
        $ads->setShortDescription($this->generateShortDescription($ads->getDescription()));
        $ads->setLat($dto->lat);
        $ads->setLng($dto->lng);
        $ads->setPostpricefrom($dto->postPriceFrom);
        $ads->setPostpriceto($dto->postPriceTo);
        $ads->setPrepricefrom($dto->prePriceFrom);
        $ads->setPrepriceto($dto->prePriceTo);
        $ads->setPriceFrom($dto->priceFrom);
        $ads->setPriceTo($dto->priceTo);
        $ads->setAddress($dto->address);
        $ads->setSysModifyTime(new \DateTime());
        $ads->setSysModifyUserId($user);
        $ads->setOwner(null);
        $ads->setFacebook($dto->facebook);
        $ads->setWebsite($dto->website);
        $ads->setInstagram($dto->instagram);

        if (null === $ads->getId()) {
            $ads->setSysCreatedUserId($user);
            $ads->setSyscreatedTime(new \DateTime());
        }

        if (null !== $owner) {
            $ads->setOwner($owner);
        }

        $youtube = json_decode($dto->youtube, true);

        if (0 < count($youtube)) {
            $this->youTubeParser->parse($ads, $youtube);
        }

        $this->adsImageService->setImage($ads, json_decode($dto->documents, true));
        $this->adsUserParser->parse($ads, $dto->contact);

        $this->adsTagParser->parse($ads, $dto->tags);
        $this->setCategory($ads, $dto->category);
        $this->setCity($ads, $dto->city);

        $now = new \DateTimeImmutable();
        $paymentDate = $now->modify('+1 year');

        if (null !== $dto->paymentDate) {
            $paymentDate = new \DateTimeImmutable($dto->paymentDate);
        }

        $paymentType = $this->adsPayedDateParser->parse($ads, $dto->pricePlan, $paymentDate);

        $ads->addPayedType($paymentType);

        return $ads;
    }

    public function create(): Ads
    {
        return new Ads();
    }

    private function generateShortDescription(string $description)
    {
        $trimmed = strip_tags(stripslashes($description));
        $length = strlen($trimmed);

        if ($length > 250) {
            return preg_replace('/\s?(\S?)+$/', '', substr($trimmed, 0, 250));
        }

        return $trimmed;
    }

    private function setCategory(Ads $ads, int $categoryId): void
    {
        $category = $this->categoryRepository->find($categoryId);

        $ads->setCategoryId($category);
    }

    private function setCity(Ads $ads, string $citySlug): void
    {
        $city = $this->cityRepository->findOneBy(['alias' => $citySlug]);

        $ads->setCityId($city);
    }
}
