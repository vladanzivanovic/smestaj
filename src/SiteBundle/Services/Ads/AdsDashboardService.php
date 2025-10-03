<?php

namespace SiteBundle\Services\Ads;

use SiteBundle\Entity\Ads;
use SiteBundle\Repository\ContactRepository;
use SiteBundle\Repository\MediaRepository;
use SiteBundle\Repository\UserRepository;
use SiteBundle\Repository\YouTubeInfoRepository;

class AdsDashboardService
{
    private YouTubeInfoRepository $youTubeInfoRepository;

    private MediaRepository $mediaRepository;

    private AdsFormatter $formatter;

    private AdsTagService $tagService;

    private UserRepository $userRepository;

    private AdsAdditionalInfoService $additionalInfoService;

    private ContactRepository $contactRepository;

    public function __construct(
        YouTubeInfoRepository $youTubeInfoRepository,
        MediaRepository $mediaRepository,
        AdsFormatter $formatter,
        AdsTagService $tagService,
        UserRepository $userRepository,
        AdsAdditionalInfoService $additionalInfoService,
        ContactRepository $contactRepository
    ) {
        $this->youTubeInfoRepository = $youTubeInfoRepository;
        $this->mediaRepository = $mediaRepository;
        $this->formatter = $formatter;
        $this->tagService = $tagService;
        $this->userRepository = $userRepository;
        $this->additionalInfoService = $additionalInfoService;
        $this->contactRepository = $contactRepository;
    }

    public function getAdDashboard(Ads $ads): array
    {
        $data = [];
        $youtubes = $this->youTubeInfoRepository->getByAdsId($ads->getId(), false);

        foreach ($youtubes as &$youtube){
            $youtube['Thumbnails'] = json_decode($youtube['Thumbnails'], true);
        }
        unset($youtube);

        $images = $this->mediaRepository->getByAds($ads);

        $data['ads'] = $this->formatter->formatDataForEdit($ads);
        $data['media'] = $this->formatter->imageFormatter($images);
        $data['youtube'] = $youtubes;
        $data['tags'] = $this->tagService->getTagsByadsId($ads);
        $data['user'] = $this->contactRepository->getContactByAd($ads->getContact());
        $data['additional_info'] = $this->additionalInfoService->getByAdsId($ads->getId());

        return $data;
    }
}