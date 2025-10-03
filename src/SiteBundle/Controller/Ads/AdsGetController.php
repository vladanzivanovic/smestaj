<?php

namespace SiteBundle\Controller\Ads;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Repository\MediaRepository;
use SiteBundle\Repository\YouTubeInfoRepository;
use SiteBundle\Services\Ads\AdsFormatter;
use SiteBundle\Services\Ads\AdsTagService;
use SiteBundle\Services\TagService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdsGetController extends SiteController
{
    private TagService $tagService;

    public function __construct(
        TagService $tagService
    ){
        $this->tagService = $tagService;
    }

    /**
     * @Route("/pregled/{category}/{alias}", name="site_single_ads", methods={"GET"})
     * @param Ads $ads
     *
     * @return Response
     */
    public function singleAdsAction(Ads $ads): Response
    {
        try{
            return $this->redirect($this->generateUrl('site_ads_view', ['category' => $ads->getCategoryId()->getAlias(), 'extraParams' => $ads->getCityId()->getAlias().'/'.$ads->getAlias()]));
        } catch (\Throwable $exception) {
            return $this->redirect($this->generateUrl('site_index'));
        }
    }

    /**
     * @param Ads $ads
     *
     * @return JsonResponse
     */
    public function getAdsByIdAction(Ads $ads)
    {
        $imageUrl = $this->get('app.app_helper')->getHttpHostBaseUrl() . $this->getParameter('upload_image_dir');
        $youtubes = $this->setEntity('SiteBundle:Youtubeinfo')->getByadsId($ads->getId(), false);

        /** @var AdsFormatter $formatter */
        $formatter = $this->setService('site.ads_formatter');
        /** @var AdsTagService $tagsService */
        $tagsService = $this->setService('site.ads_tag_service');
        /** @var MediaRepository $mediaRepo */
        $mediaRepo = $this->setEntity('SiteBundle:Media');

        foreach ($youtubes as &$youtube){
            $youtube['Thumbnails'] = json_decode($youtube['Thumbnails'], true);
        }
        unset($youtube);

        $user = $this->setEntity('SiteBundle:User')->getById($ads->getContact()->getId());
        $adsInfo = $this->setService('site.additional_info_service')->getByadsId($ads->getId());

        return $this->outputJson([
            'ads' => $formatter->formatDataForEdit($ads),
            'media' => $formatter->imageFormatter($mediaRepo->getByAds($ads->getId(), $imageUrl)),
            'youtube' => $youtubes,
            'user' => $user,
            'additionalInfo' => $adsInfo,
            'tags' => $tagsService->getTagsByadsId($ads),
        ]);
    }

//    /**
//     * @Template("@Site/Moduls/popupAdsImage.html.twig")
//     */
//    public function popupAdsImageAction($slug, $infoId)
//    {
//        /** @var MediaRepository $imageRepo */
//        $imageRepo = $this->setEntity('SiteBundle:Media');
//
//        $imageUrl = $this->get('app.app_helper')->getHttpHostBaseUrl() . $this->getParameter('upload_image_dir');
//
//        if (null !== $infoId) {
//            $images = $imageRepo->getByAdsInfoId($infoId, $slug, $imageUrl);
//        } else {
//            $images = $imageRepo->getByAds($slug, $imageUrl);
//        }
//
//        return array(
//            "id" => $slug,
//            "images" => $images
//        );
//    }
//
//    /**
//     * @Template("@Site/Moduls/popupAdsVideo.html.twig")
//     */
//    public function popupAdsVideoAction($alias)
//    {
//        /** @var YouTubeInfoRepository $youtubeRepo */
//        $youtubeRepo = $this->setEntity('SiteBundle:Youtubeinfo');
//        $youTubeLists = $youtubeRepo->getByadsId($id);
//        return array(
//            "id" => $id,
//            "youtube" => $youTubeLists
//        );
//    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function checkAdsExistByTitleAction(Request $request)
    {
        $id = $request->query->get('id');
        if( !($title = $request->query->get('Title')) ) {
            return $this->jsonResponse->setData(true);
        }


        /** @var Ads $ads */
        $ads = $this->setEntity('SiteBundle:Ads')->findOneBy(['title' => $title]);

        if ( (int) $id > 0 && $ads && $ads->getId() == $id) {
            $ads = null;
        }

        $response = null !== $ads ? false : true;

        return $this->jsonResponse->setData($response);
    }

    /**
     * @Route("/api/ads-options", name="site_ads_options", methods={"GET"})
     *
     * @return mixed
     */
    public function getAdsEditOptions()
    {
        $tags = $this->tagService->getTags();
        $tags = $this->tagService->formatTags($tags);

        return $this->json(['tags' => $tags]);
    }
}
