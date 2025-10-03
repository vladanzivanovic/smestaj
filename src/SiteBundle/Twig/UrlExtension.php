<?php

declare(strict_types=1);

namespace SiteBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UrlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('full_url', [$this, 'getFulUrl']),
        ];
    }

    public function getFulUrl(?string $url): string
    {
        if (null === $url) {
            return '';
        }

        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['scheme'])) {
            return 'http://'.$url;
        }

        return $url;
    }

    public function hasKey($key, $array): bool
    {
        return in_array($key, array_keys($array));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'array_extension';
    }
}