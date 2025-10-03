<?php

declare(strict_types=1);

namespace AdminBundle\Formatter;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsPayedDate;
use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Repository\MediaRepository;
use SiteBundle\Repository\TagRepository;
use SiteBundle\Repository\UserRepository;
use SiteBundle\Services\Ads\AdsTagService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductEditResponseFormatter
{
    use ImageTrait;

    private CategoryRepository $categoryRepository;

    private RouterInterface $router;

    private array $shoesMaterials;

    private TranslatorInterface $translator;

    private CityRepository $cityRepository;

    private TagRepository $tagRepository;

    private AdsTagService $adsTagService;

    private MediaRepository $mediaRepository;

    private UserRepository $userRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        RouterInterface $router,
        TranslatorInterface $translator,
        CityRepository $cityRepository,
        TagRepository $tagRepository,
        AdsTagService $adsTagService,
        MediaRepository $mediaRepository,
        UserRepository $userRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->router = $router;
        $this->translator = $translator;
        $this->cityRepository = $cityRepository;
        $this->tagRepository = $tagRepository;
        $this->adsTagService = $adsTagService;
        $this->mediaRepository = $mediaRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Ads|null $product
     *
     * @return array
     */
    public function formatResponse(Ads $product = null): array
    {
        $responseData = [
            'tags' => $this->formatTags($this->tagRepository->getTags()),
            'categories' => $this->categoryRepository->getActiveForOptions(),
            'cities' => $this->cityRepository->getForOptions(),
            'owners' => $this->userRepository->getActiveOwnersForOptions(),
            'paymentPlan' => [
                ['value' => AdsPayedDate::PAYMENT_PLAN_BASIC, 'title' => 'Osnovni'],
                ['value' => AdsPayedDate::PAYMENT_PLAN_BUSINESS, 'title' => 'Biznis'],
                ['value' => AdsPayedDate::PAYMENT_PLAN_PREMIUM, 'title' => 'Premium'],
            ],
        ];

        if (null !== $product) {
            $responseData['product'] = $product;
            $responseData['selectedTags'] = $this->adsTagService->getTagsByadsId($product);
            $responseData['selectedImages'] = $this->imagesFormatter($this->router, $this->mediaRepository->getByAd($product), 'oglasi');
        }

        return $responseData;
    }

    private function formatTags(array $tags): array
    {
        $formattedTags = [];

        foreach ($tags as $tag) {
            $formattedTags[$tag['type_label']]['name'] = $tag['type_name'];
            $formattedTags[$tag['type_label']]['tags'][] = $tag;
        }

        return $formattedTags;
    }
}
