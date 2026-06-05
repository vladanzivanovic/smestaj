<?php

namespace SiteBundle\Twig;


class PluralizationExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * @return array|\Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('pluralization', [$this, 'pluralizeString'])
        ];
    }

    /**
     * @param int    $number
     * @param string $multiString
     * @param string $singleString
     *
     * @return string
     */
    public function pluralizeString(int $number, string $multiString, string $singleString)
    {
        if ($number > 1 || $number === 0) {
            return $multiString;
        }

        return $singleString;
    }

    public function getName()
    {
        return 'pluralization_extension';
    }
}