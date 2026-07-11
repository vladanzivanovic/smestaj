<?php

declare(strict_types=1);

namespace SiteBundle\Twig;

use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Component\Routing\RouterInterface;

final class HttpExtension extends \Twig\Extension\AbstractExtension
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
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('absolute_url_custom', [$this, 'getAbsoluteUrl'])
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
