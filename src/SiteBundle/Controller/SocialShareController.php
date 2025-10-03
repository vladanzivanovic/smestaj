<?php


namespace SiteBundle\Controller;


use MartinGeorgiev\SocialPost\Provider\AllInOne;
use MartinGeorgiev\SocialPost\Provider\Message;
use SiteBundle\Services\SocialShareService;

class SocialShareController extends SiteController
{
    /**
     * @param int $adsType
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postToFacebookAction($adsType)
    {
        /** @var SocialShareService $socialService */
        $socialService = $this->setService('site.share_service');
        $socialService->share($adsType);

        return $this->jsonResponse;
    }
}