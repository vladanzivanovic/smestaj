<?php

namespace SiteBundle\Twig;

use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Component\Routing\RouterInterface;

class HttpExtension extends \Twig_Extension
{
    /**
     * @var HttpFoundationExtension
     */
    private $httpFoundationExtension;

    public function __construct(HttpFoundationExtension $httpFoundationExtension)
    {
        $this->httpFoundationExtension = $httpFoundationExtension;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('absolute_url_custom', [$this, 'getAbsoluteUrl'])
        ];
    }

    public function getAbsoluteUrl($path)
    {
        return str_replace('web/', '',$this->httpFoundationExtension->generateAbsoluteUrl($path));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'http_extension';
    }
}
