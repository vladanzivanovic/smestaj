<?php


namespace SiteBundle\EventListeners;


use Doctrine\Common\Persistence\ObjectManager;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use SiteBundle\Entity\Category;
use SiteBundle\Repository\AdsRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SiteMapAdsSubscriber implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;

    private ObjectManager $manager;

    private AdsRepository $adsRepository;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ObjectManager $manager,
        AdsRepository $adsRepository
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->manager = $manager;
        $this->adsRepository = $adsRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => [
                ['registerAdsPerCategoryPages', 0],
            ],
        ];
    }

    public function registerAdsPerCategoryPages(SitemapPopulateEvent $event)
    {
        $categories = $this->manager->getRepository('SiteBundle:Category')->findAll();

        /** @var Category[] $categories */
        foreach ($categories as $category) {
            $ads = $this->adsRepository->getAdsForSiteMapByCategory($category);

            foreach ($ads as $ad) {
                $event->getUrlContainer()->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            'site_ads_view',
                            [
                                'category' => $category->getAlias(),
                                'extraParams' => $ad->getCityId()->getAlias() .'/'.$ad->getAlias()
                            ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_DAILY
                    ),
                    'ads_by_category'
                );
            }

            $event->getUrlContainer()->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate(
                        'site_ads_view',
                        [
                            'page' => 1,
                            'category' => $category->getAlias()
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    new \DateTime(),
                    UrlConcrete::CHANGEFREQ_MONTHLY
                ),
                'ads_category'
            );
        }
    }
}
