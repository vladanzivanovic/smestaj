<?php

namespace SiteBundle\Services\Ads;

use SiteBundle\Entity\Ads;
use Symfony\Component\Routing\RouterInterface;

class AdsFormatter
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
    }

    /**
     * @param Ads $ads
     *
     * @return array
     */
    public function formatDataForEdit(Ads $ads): array
    {
        $payment = $ads->getActivePayment();

        if (null === $payment) {
            $payment = $ads->getLastPayment();
        }

        return [
            'id' => $ads->getId(),
            'alias' => $ads->getAlias(),
            'category_alias' => $ads->getCategoryId()->getAlias(),
            'category_name' => $ads->getCategoryId()->getName(),
            'category_id' => $ads->getCategoryId()->getId(),
            'city' => $ads->getCityId()->getName(),
            'city_id' => $ads->getCityId()->getId(),
            'description' => $ads->getDescription(),
            'post_price_from' => $ads->getPostpricefrom(),
            'post_price_to' => $ads->getPostpriceto(),
            'pre_price_from' => $ads->getPrepricefrom(),
            'pre_price_to' => $ads->getPrepriceto(),
            'price_from' => $ads->getPriceFrom(),
            'price_to' => $ads->getPriceTo(),
            'public_price' => $ads->getPublicprice(),
            'short_description' => $ads->getShortDescription(),
            'title' => $ads->getTitle(),
            'user_id' => $ads->getContact()->getId(),
            'street' => $ads->getAddress(),
            'lat' => $ads->getLat(),
            'lng' => $ads->getLng(),
            'website' => $ads->getWebsite(),
            'facebook' => $ads->getFacebook(),
            'instagram' => $ads->getInstagram(),
            'payment_type' => null !== $payment ? $payment->getType() : null,
        ];
    }

    public function imageFormatter(array $media): array
    {
        return array_map(function($img) {
            return [
                'id' => $img['id'],
                'fileName' => $img['name'],
                'file' => $this->router->generate('app.image_show', ['entity' => 'oglasi', 'filter' => 'dashboard_set_images', 'name' => $img['slug']]),
                'isImage' => true,
                'isMain' => $img['isMain'],
            ];
        }, $media);
    }
}
