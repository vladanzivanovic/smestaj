<?php

namespace SiteBundle\Services\Ads;

use SiteBundle\Entity\Category;
use SiteBundle\Entity\City;
use SiteBundle\Entity\User;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Services\PaginationService;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserInterface;

class AdsService
{
    private AdsRepository $adsRepository;

    private PaginationService $paginationService;

    public function __construct(
        AdsRepository $adsRepository,
        PaginationService $paginationService
    ) {
        $this->paginationService = $paginationService;
        $this->adsRepository = $adsRepository;
    }

    /**
     * @param int  $page
     * @param User $user
     *
     * @return array
     */
    public function getDashboardAdsList($page = 1, UserInterface $user)
    {
        $adsQuery = $this->adsRepository->getQueryForDashboard($user);

        return $this->paginationService->pagination($adsQuery, $page, 12);
    }

    /**
     * @param array $selectedTags
     * @param array $tags
     *
     * @return array
     */
    public function getSelectedTagTypes(array $selectedTags, array $tags): array
    {
        $types = [];

        foreach ($tags as $tag) {
            if (in_array($tag['id'], $selectedTags)) {
                $types[$tag['type_label']] = $tag['type_label'];
            }
        }

        return $types;
    }
}
