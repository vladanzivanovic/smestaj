<?php

namespace SiteBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Category;
use SiteBundle\Entity\City;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Repository\CityRepository;
use Symfony\Component\Routing\Annotation\Route;

class RedirectionController extends SiteController
{
    private $adsRepository;
    private $categoryRepository;
    private $cityRepository;

    /**
     * RedirectionController constructor.
     *
     * @param AdsRepository      $adsRepository
     * @param CategoryRepository $categoryRepository
     * @param CityRepository     $cityRepository
     */
    public function __construct(
        AdsRepository $adsRepository,
        CategoryRepository $categoryRepository,
        CityRepository $cityRepository
    ) {
        $this->adsRepository = $adsRepository;
        $this->categoryRepository = $categoryRepository;
        $this->cityRepository = $cityRepository;
    }

    /**
     * @ParamConverter("city", options={"mapping": {"city": "alias"}})
     *
     * @param City   $city
     * @param int    $id
     * @param string $alias
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function oldSiteRedirectAction(?City $city, $id, string $alias)
    {
        try{
            /** @var Ads $ads */
            $ads = $this->adsRepository->findOneBy(['alias' => $alias]);
            $category = $ads->getCategoryId()->getAlias();

            return $this->redirect($this->generateUrl('site_single_ads', ['category' => $category, 'alias' => $ads->getAlias()]));
        } catch (\Throwable $exception) {
            return $this->redirect($this->generateUrl('site_index'));
        }
    }

    /**
     * @param string      $category
     * @param string|null $city
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function oldCategoryRedirectionAction(string $category, ?string $city)
    {
        switch ($category) {
            case 'apartmani':
            case 'sobe':
                $category = 'sobe-apartmani';
                break;
        }
        $category = $this->categoryRepository->findOneBy(['alias' => $category]);

        if (null !== $city) {
            $city = $this->cityRepository->findOneBy(['alias' => $city]);
        }

        $params = ['category' => 'smestaj'];

        if ($category instanceof Category) {
            $params['category'] = $category->getAlias();
        }

        if ($city instanceof City) {
            $params['city'] = $city->getAlias();
        }
        
        return $this->redirect($this->generateUrl('site_ads_view', $params));
    }

    /**
     * @Route("/kategorije/{page}/{category}/{city}",
     *     name="site_ads_view_old",
     *     defaults={"page": 1, "category": null, "city": null},
     *     requirements={"page": "\d+"},
     *     methods={"GET"}
     * )
     * @Route("/kategorije/{page}/kategorije/{category}/mesto/{city}",
     *     name="site_ads_view_old_2",
     *     defaults={"page": 1, "category": null, "city": null},
     *     requirements={"page": "\d+"},
     *     methods={"GET"}
     * )
     */
    public function oldAdsPageRedirection(int $page, ?string $category, ?string $city)
    {
        $params = [
            'category' => $category,
            'extraParams' => $city,
        ];

        if ($page > 1) {
            $params['stranica'] = $page;
        }

        return $this->redirect($this->generateUrl('site_ads_view', $params));
    }
}