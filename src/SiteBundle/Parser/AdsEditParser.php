<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Contact;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\User;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Helper\TextHelper;
use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Services\Ads\AdsImageService;
use Symfony\Component\HttpFoundation\ParameterBag;
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

    public function parse(ParameterBag $bag, UserInterface $user, ?UserInterface $owner, Ads $ads= null): Ads
    {
        if (null === $ads) {
            $ads = $this->create();
            $ads->setStatus(EntityStatusInterface::STATUS_PENDING);
        }

        $contactArray = $bag->get('contact');

        $ads->setTitle($bag->get('title_rs'));
        $ads->setDescription($this->textHelper->clearText($bag->get('description_rs')));
        $ads->setShortDescription($this->generateShortDescription($ads->getDescription()));
        $ads->setLat((float)$bag->get('lat'));
        $ads->setLng((float)$bag->get('lng'));
        $ads->setPostpricefrom($bag->getInt('post_price_from'));
        $ads->setPostpriceto($bag->getInt('post_price_to'));
        $ads->setPrepricefrom($bag->getInt('pre_price_from'));
        $ads->setPrepriceto($bag->getInt('pre_price_to'));
        $ads->setPriceFrom($bag->getInt('price_from'));
        $ads->setPriceTo($bag->getInt('price_to'));
        $ads->setAddress($bag->get('_address'));
        $ads->setSysModifyTime(new \DateTime());
        $ads->setSysModifyUserId($user);
        $ads->setOwner(null);
        $ads->setFacebook($bag->get('facebook'));
        $ads->setWebsite($bag->get('website'));
        $ads->setInstagram($bag->get('instagram'));

        if (null === $ads->getId()) {
            $ads->setSysCreatedUserId($user);
            $ads->setSyscreatedTime(new \DateTime());
        }

        if (null !== $owner) {
            $ads->setOwner($owner);
        }

        $youtube = json_decode($bag->get('youtube'), true);

        if (0 < count($youtube)) {
            $this->youTubeParser->parse($ads, $youtube);
        }

        $this->adsImageService->setImage($ads, json_decode($bag->get('documents'), true));
        $this->adsUserParser->parse($ads, $contactArray);

        $this->adsTagParser->parse($ads, $bag->get('tags'));
        $this->setCategory($ads, $bag->getInt('category'));
        $this->setCity($ads, $bag->get('city'));

        $now = new \DateTimeImmutable();
        $paymentDate = $now->modify('+1 year');

        if ($bag->has('payment_date')) {
            $paymentDate = new \DateTimeImmutable($bag->get('payment_date'));
        }

        $paymentType = $this->adsPayedDateParser->parse($ads, $bag->getInt('price_plan'), $paymentDate);

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
