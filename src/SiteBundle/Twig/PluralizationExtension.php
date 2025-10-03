<?php

namespace SiteBundle\Twig;


class PluralizationExtension extends \Twig_Extension
{
    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pluralization', [$this, 'pluralizeString'])
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