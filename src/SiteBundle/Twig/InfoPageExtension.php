<?php

declare(strict_types=1);

namespace SiteBundle\Twig;

use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\Tag;
use SiteBundle\Services\InfoPage\GoogleReviewLinkBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class InfoPageExtension extends AbstractExtension
{
    private const RANGE_ICON_OVERRIDES = [
        'centra' => 'fa fa-landmark',
        'bankomata' => 'fa fa-credit-card',
    ];

    private const AMENITY_ICONS = [
        'wifi' => 'soap-icon-wifi',
        'air_conditioning' => 'soap-icon-aircon',
        'heating' => 'soap-icon-fireplace',
        'parking_free' => 'soap-icon-parking',
        'parking_paid' => 'soap-icon-parking',
        'garage' => 'fa fa-warehouse',
        'pool' => 'soap-icon-swimming',
        'sea_view' => 'soap-icon-anchor',
        'mountain_view' => 'soap-icon-balloon',
        'garden' => 'soap-icon-tree',
        'terrace' => 'fa fa-sun-o',
        'kitchen' => 'soap-icon-fork',
        'dishwasher' => 'fa fa-cutlery',
        'washing_machine' => 'fa fa-tint',
        'iron' => 'fa fa-magic',
        'tv' => 'soap-icon-television',
        'smart_tv' => 'soap-icon-television',
        'coffee_machine' => 'soap-icon-coffee',
        'kettle' => 'soap-icon-coffee',
        'microwave' => 'fa fa-fire',
        'refrigerator' => 'soap-icon-fridge',
        'hair_dryer' => 'fa fa-magic',
        'towels_linen' => 'fa fa-bath',
        'workspace' => 'fa fa-laptop',
        'crib' => 'fa fa-child',
        'high_chair' => 'fa fa-child',
        'pet_friendly' => 'soap-icon-pets',
        'smoking_allowed' => 'soap-icon-smoking',
        'non_smoking' => 'fa fa-ban',
        'family_friendly' => 'soap-icon-family',
        'wheelchair_accessible' => 'soap-icon-handicapaccessiable',
        'elevator' => 'soap-icon-elevator',
        'soundproofing' => 'fa fa-volume-off',
        'safe' => 'soap-icon-securevault',
        'bbq' => 'fa fa-fire',
        'bicycle_rental' => 'fa fa-bicycle',
    ];

    public function __construct(
        private readonly GoogleReviewLinkBuilder $googleReviewLinkBuilder,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @return array<int, TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('info_page_locale_value', [$this, 'infoPageLocaleValue']),
            new TwigFunction('info_page_review_url', [$this, 'infoPageReviewUrl']),
            new TwigFunction('info_page_amenity_label', [$this, 'infoPageAmenityLabel']),
            new TwigFunction('info_page_amenity_icon', [$this, 'infoPageAmenityIcon']),
            new TwigFunction('info_page_range_icon', [$this, 'infoPageRangeIcon']),
        ];
    }

    /**
     * @return array<int, TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('info_page_phone_digits', [$this, 'infoPagePhoneDigits']),
        ];
    }

    public function infoPageLocaleValue(AdsInfoPage $entity, string $base, string $locale): ?string
    {
        return $entity->getCanonicalLocaleField($base, $locale);
    }

    public function infoPageReviewUrl(?string $input): ?string
    {
        return $this->googleReviewLinkBuilder->build($input);
    }

    public function infoPageAmenityLabel(string $key): string
    {
        return $this->translator->trans('amenity.' . $key);
    }

    public function infoPageAmenityIcon(string $key): string
    {
        return self::AMENITY_ICONS[$key] ?? 'soap-icon-check';
    }

    public function infoPageRangeIcon(Tag $tag): string
    {
        $slug = $tag->getSlug();
        if (null !== $slug && isset(self::RANGE_ICON_OVERRIDES[$slug])) {
            return self::RANGE_ICON_OVERRIDES[$slug];
        }

        $rawIcon = $tag->getIcon();
        if ('' === $rawIcon) {
            return 'fa fa-map-marker';
        }

        if (str_starts_with($rawIcon, 'fa-')) {
            return 'fa ' . $rawIcon;
        }

        return $rawIcon;
    }

    public function infoPagePhoneDigits(?string $value): string
    {
        if (null === $value || '' === $value) {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (true === str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        return $digits;
    }
}
