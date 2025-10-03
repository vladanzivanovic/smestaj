<?php


namespace SiteBundle\Services;


use Doctrine\ORM\EntityManager;
use MartinGeorgiev\SocialPost\Provider\AllInOne;
use MartinGeorgiev\SocialPost\Provider\Message;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Repository\AdsRepository;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SocialShareService extends ServiceContainer
{
    private $allInOne;
    private $router;
    private $httpFoundationExtension;

    /**
     * @param EntityManager           $entity
     * @param TokenStorage            $tokenStorage
     * @param AllInOne                $allInOne
     * @param Router                  $router
     * @param HttpFoundationExtension $httpFoundationExtension
     */
    public function __construct(
        EntityManager $entity,
        TokenStorage $tokenStorage,
        AllInOne $allInOne,
        Router $router,
        HttpFoundationExtension $httpFoundationExtension
    ) {
        parent::__construct($entity, $tokenStorage);
        $this->allInOne = $allInOne;
        $this->router = $router;
        $this->httpFoundationExtension = $httpFoundationExtension;
    }

    public function share($type)
    {
        /** @var AdsRepository $adsRepo */
        $adsRepo = $this->em->getRepository('SiteBundle:Ads');

        $adsPayed = [];
        $adsRegular = [];

        if((int)$type === EntityStatusInterface::STATUS_ACTIVE) {
            $adsPayed = $adsRepo->getRandom(4, true);
            $adsRegular = $adsRepo->getRandom(2, false);
        }

        if ((int)$type === EntityStatusInterface::STATUS_PAYED) {
            $adsPayed = $adsRepo->getRandom(6, true);
        }

        foreach (array_merge($adsPayed, $adsRegular) as $ad) {
            $message = new Message($ad['Title'],
                $this->httpFoundationExtension->generateAbsoluteUrl(
                    $this->router->generate('site_single_ads', ['category' => $ad['CatAlias'], 'alias' => $ad['Alias']])
                ),
                $this->httpFoundationExtension->generateAbsoluteUrl(
                    $this->router->generate('site_ads_image', ['slug' => $ad['Alias'], 'name' => $ad['MediaName']])
                ),
                $ad['ShortDescription']);

            $this->allInOne->publish($message);

//            $message[] = [
//                $ad['ShortDescription'],
//                $this->httpFoundationExtension->generateAbsoluteUrl($this->router->generate('site_single_ads', ['category' => $ad['CatAlias'], 'alias' => $ad['Alias']])),
//                $this->httpFoundationExtension->generateAbsoluteUrl($this->router->generate('site_ads_image', ['slug' => $ad['Alias'], 'name' => $ad['MediaName']])),
//                $ad['Title']
//            ];
        }

        return true;
    }
}
