<?php

declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\Ads;
use SiteBundle\Repository\AdshastagsRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdView
{
    private \HTMLPurifier $purifier;

    private AdPriceView $priceView;

    private AdshastagsRepository $adshastagsRepository;

    private AdTagView $adTagView;

    private ImageView $imageView;

    private TranslatorInterface $translator;

    private RouterInterface $router;

    private CategoryView $categoryView;

    public function __construct(
        \HTMLPurifier $purifier,
        AdPriceView $priceView,
        AdshastagsRepository $adshastagsRepository,
        AdTagView $adTagView,
        ImageView $imageView,
        TranslatorInterface $translator,
        RouterInterface $router,
        CategoryView $categoryView
    ){
        $this->purifier = $purifier;
        $this->priceView = $priceView;
        $this->adshastagsRepository = $adshastagsRepository;
        $this->adTagView = $adTagView;
        $this->imageView = $imageView;
        $this->translator = $translator;
        $this->router = $router;
        $this->categoryView = $categoryView;
    }
    public function view(Ads $ads): array
    {
        $city = $ads->getCityId();

        $view = [
            'id' => $ads->getId(),
            'title' => $ads->getTitle(),
            'slug' => $ads->getAlias(),
            'prices' => [
                Ads::PRICE_TYPE_PRE_SEASON => $this->priceView->view($ads->getPrepricefrom(), $ads->getPrepriceto()),
                Ads::PRICE_TYPE_SEASON => $this->priceView->view($ads->getPriceFrom(), $ads->getPriceTo()),
                Ads::PRICE_TYPE_POST_SEASON => $this->priceView->view($ads->getPostpricefrom(), $ads->getPostpriceto()),
            ],
            'short_description' => $this->purifier->purify($ads->getShortDescription()),
            'description' => $this->purifier->purify($ads->getDescription()),
            'address' => [
                'city' => [
                    'title' => $city->getName(),
                    'slug' => $city->getAlias(),
                ],
                'street' => [
                    'title' => $ads->getAddress(),
                ],
                'coordinates' => [
                    'lat' => $ads->getLat(),
                    'lng' => $ads->getLng(),
                ],
            ],
            'tags' => $this->getAdTags($ads),
            'social' => [
                'website' => $ads->getWebsite(),
                'facebook' => $ads->getFacebook(),
                'instagram' => $ads->getInstagram(),
            ],
            'ad_categories' => $this->getCategories($ads),
            'media' => [
                'images' => $this->getImages($ads),
            ],
            '_link' => $this->router->generate(
                'site_ads_view',
                ['category' => $ads->getCategoryId()->getAlias(), 'extraParams' => $ads->getCityId()->getAlias() .'/'.$ads->getAlias()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];

        return $view;
    }

    public function socialMetaDataContent(Ads $ads): array
    {
        $mainImage = $ads->getMainImage();
        $mainImageLink = '';

        if (null !== $mainImage) {
            $mainImageLink = $this->imageView->view($ads->getMainImage(), $this->translator->trans('ads'), ['single']);
        }

        $view = [
            'title' => $ads->getTitle(),
            'short_description' => $ads->getShortDescription(),
            'image' => $mainImageLink,
        ];

        return $view;
    }

    public function getLdView(Ads $ads): array
    {
        $mainImage = $ads->getMainImage();

        $mainImageLink = '';

        if (null !== $mainImage) {
            $mainImageLink = $this->router->generate(
                'app.image_show',
                ['entity' => $this->translator->trans('ads'), 'filter' => 'single', 'name' => $mainImage->getName()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $view = [
            "@context" => "http://schema.org",
            "@type" => "LodgingBusiness",
            "name" => $ads->getTitle(),
            "url" => $this->router->generate(
                'site_ads_view',
                ['category' => $ads->getCategoryId()->getAlias(), 'extraParams' => $ads->getCityId()->getAlias() .'/'.$ads->getAlias()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            "description" => $this->purifier->purify($ads->getShortDescription()),
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => $ads->getAddress(),
                "addressLocality" => $ads->getCityId()->getName(),
                "addressRegion" => "Montenegro",
                "addressCountry" => "Montenegro",
            ],
            "image" => $mainImageLink,
            "priceRange" => "$",
        ];

        $amenities = [];

        foreach ($this->getAdTags($ads) as $tagType) {
            foreach ($tagType as $tag) {
                $amenities[] = [
                    "@type" => "LocationFeatureSpecification",
                    'name' => $tag['title'],
                ];
            }
        }
        $view['amenityFeature'] = $amenities;

        return $view;
    }

    public function getImages(Ads $ads):array
    {
        $mainImage = $ads->getMainImage();

        $images = [
            'main' => null,
            'additional' => [],
        ];

        if (null !== $mainImage) {
            $images['main'] = $this->imageView->view($mainImage, $this->translator->trans('ads'), ['single', 'single_thumb', 'single_full', 'list_thumb']);
        }

        foreach ($ads->getMedia() as $media) {
            if (null !== $mainImage && $media->getId() === $mainImage->getId()) {
                continue;
            }

            $images['additional'][] = $this->imageView->view(
                $media,
                $this->translator->trans('ads'),
                ['single', 'single_thumb', 'single_full'],
            );
        }

        return $images;
    }

    public function getMainImage(Ads $ads, array $filters):array
    {
        $mainImage = $ads->getMainImage();

        if (null === $mainImage) {
            return [];
        }

        return $this->imageView->view(
            $mainImage,
            $this->translator->trans('ads'),
            $filters,
        );
    }

    private function getAdTags(Ads $ads): array
    {
        $tags = $this->adshastagsRepository->getByAd($ads);

        return $this->adTagView->viewGroupedByType($tags);
    }

    private function getCategories(Ads $ads): array
    {
        $category = $ads->getCategoryId();

        $categories = [
            'main' => $this->categoryView->view($category),
            'parents' => [],
        ];

        while(null !== $category->getParent()) {
            $categories['parents'][] = $this->categoryView->view($category);
        }

        $categories['parents'] = array_reverse($categories['parents']);

        return $categories;
    }
}
