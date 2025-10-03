<?php
declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsPayedDate;
use SiteBundle\Entity\Contact;
use SiteBundle\Entity\Media;
use SiteBundle\Entity\Youtubeinfo;
use SiteBundle\Helper\ConstantsHelper;
use SiteBundle\Services\Ads\AdsTagService;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SingleAdViewFormatter
{
    private \HTMLPurifier $purifier;

    private AdsTagService $adsTagService;

    private AdView $adView;

    private ImageView $imageView;

    private CategoryView $categoryView;

    private YoutubeView $youtubeView;

    private ContactView $contactView;

    private AdsPaymentView $paymentView;

    private TranslatorInterface $translator;

    public function __construct(
        \HTMLPurifier $purifier,
        AdsTagService $adsTagService,
        AdView $adView,
        ImageView $imageView,
        CategoryView $categoryView,
        YoutubeView $youtubeView,
        ContactView $contactView,
        AdsPaymentView $paymentView,
        TranslatorInterface $translator
    ) {

        $this->purifier = $purifier;
        $this->adsTagService = $adsTagService;
        $this->adView = $adView;
        $this->imageView = $imageView;
        $this->translator = $translator;
        $this->categoryView = $categoryView;
        $this->youtubeView = $youtubeView;
        $this->contactView = $contactView;
        $this->paymentView = $paymentView;
    }

    public function fullData(Ads $ads): array
    {
        $view = $this->adView->view($ads);
        $view['media']['images'] = $this->adView->getImages($ads);
        $view['media']['videos'] = $this->getYoutubeData($ads);
        $view['_social_meta_data'] = $this->adView->socialMetaDataContent($ads);
//        $view['categories'] = $this->getCategories($ads);
        $view['contact'] = $this->contactView->view($ads->getContact());
        $view['json_ld_data'] = $this->adView->getLdView($ads);
        $view['payment'] = [];

        if (null !== $ads->getActivePayment()) {
            $view['payment'] = $this->paymentView->view($ads->getActivePayment());
        }

//        dd($view);

        return $view;
    }

    private function getPaymentData(?AdsPayedDate $payedDate): array
    {
        return [
            'type' => null !== $payedDate ? $payedDate->getType() : null,
            'status' => null !== $payedDate ? $payedDate->getStatus(): null,
            'type_text' => null !== $payedDate ? ConstantsHelper::getConstantName($payedDate->getType(), 'PAYMENT_PLAN', AdsPayedDate::class) : null,
        ];
    }

    private function socialMetaDataContent(Ads $ads): array
    {
        $view = [
            'title' => $ads->getTitle(),
            'short_description' => $ads->getShortDescription(),
            'image' => $this->imageView->view($ads->getMainImage(), $this->translator->trans('ads'), ['single']),
        ];

        return $view;
    }

//    private function getMedia(Ads $ads): array
//    {
//        $images = [];
//
//        foreach ($ads->getMedia() as $media) {
//            $images[] = $this->imageView->view(
//                $media,
//                $this->translator->trans('ads'),
//                ['single', 'single_thumb', 'single_full'],
//            );
//        }
//
//        return $images;
////        $formattedMedia = [];
////
////        /** @var Media $media */
////        foreach ($ads->getMedia() as $media) {
////            if (true === $media->getIsmain()) {
////                $formattedMedia['main'] = [
////                    'id' => $media->getId(),
////                    'title' => $media->getName(),
////                    'original_title' => $media->getOriginalName(),
////                    'alias' => $media->getSlug(),
////                ];
////            }
////
////            $formattedMedia['all'][] = [
////                'id' => $media->getId(),
////                'title' => $media->getName(),
////                'original_title' => $media->getOriginalName(),
////                'alias' => $media->getSlug(),
////            ];
////        }
////
////        return $formattedMedia;
//    }

    private function getYoutubeData(Ads $ads): array
    {
        $data = [];

        /** @var Youtubeinfo $youtube */
        foreach ($ads->getYoutube() as $youtube) {
            $data[$youtube->getId()] = $this->youtubeView->view($youtube);
        }

        return $data;
    }

    private function contactData(Contact $contact): array
    {
        return [
            'first_name' => $contact->getFirstname(),
            'last_name' => $contact->getLastname(),
            'telephone' => [
                'classic' => $contact->getTelephone(),
                'mobile' => $contact->getMobilePhone(),
                'viber' => $contact->getViber(),
            ]
        ];
    }

//    private function getCategories(Ads $ads): array
//    {
//        $category = $ads->getCategoryId();
//
//        $categories = [$this->categoryView->view($category)];
//
//        while(null !== $category->getParent()) {
//            $categories[] = $this->categoryView->view($category);
//        }
//
//        return array_reverse($categories);
//    }
}
