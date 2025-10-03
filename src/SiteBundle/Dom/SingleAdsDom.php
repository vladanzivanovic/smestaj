<?php

namespace SiteBundle\Dom;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Helper\AdsPayedHelper;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\MediaRepository;
use SiteBundle\Repository\YouTubeInfoRepository;
use SiteBundle\Services\Ads\AdsFormatter;
use SiteBundle\Services\Ads\AdsTagService;
use SiteBundle\Services\TagService;
use SiteBundle\View\SingleAdViewFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \HTMLPurifier;
use Twig\Environment;

final class SingleAdsDom
{
    private AdsTagService $adsTagService;

    private AdsRepository $adsRepository;

    private TagService $tagService;

    private HTMLPurifier $purifier;

    private AdsPayedHelper $payedHelper;

    private Environment $twig;

    private SingleAdViewFormatter $viewFormatter;

    public function __construct(
        AdsTagService $adsTagService,
        AdsRepository $adsRepository,
        TagService $tagService,
        HTMLPurifier $purifier,
        AdsPayedHelper $payedHelper,
        Environment $twig,
        SingleAdViewFormatter $viewFormatter
    ){
        $this->adsTagService = $adsTagService;
        $this->adsRepository = $adsRepository;
        $this->tagService = $tagService;
        $this->purifier = $purifier;
        $this->payedHelper = $payedHelper;
        $this->twig = $twig;
        $this->viewFormatter = $viewFormatter;
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    public function singleAdsAction(Ads $ads): Response
    {
        $cleanedText = $this->purifier->purify($ads->getDescription());
        $ads->setDescription($cleanedText);
//        $isPayed = $this->payedHelper->getActivePayedAd($ads);

        $data = $this->viewFormatter->fullData($ads);
        $data['ads_suggestions'] = $this->adsRepository->getSuggestions($ads->getCategoryId()->getId());

//        $data = [
//            'ads' => $ads,
//            'is_payed' => $isPayed,
//            'ads_custom_data' => [
//                'escaped_description' => strip_tags($cleanedText),
//            ],
//            'ads_suggestions' => $this->adsRepository->getSuggestions($ads->getCategoryId()->getId()),
//            'tags' => $this->adsTagService->getTagsByAdsId($ads),
//        ];

        return new Response($this->twig->render('@Site/Site/singleAds.html.twig', $data));
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
