<?php

namespace SiteBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFunction;

class ParamsExtension extends \Twig_Extension
{
    private $container;

    /**
     * ParamsExtension constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('app_params', [$this, 'getParams']),
            new TwigFunction('param_exist', [$this, 'isSetAndExist']),
            new TwigFunction('locale_codes', [$this, 'getCodeFromLocales']),
        ];
    }

    public function getParams($parameter)
    {
        return $this->container->getParameter($parameter);
    }

    public function isSetAndExist($value)
    {
        return !empty($value);
    }

    /**
     * @return array
     */
    public function getCodeFromLocales(): array
    {
        return array_map(function ($locale) {
            return $locale['code'];
        }, $this->getParams('languages'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'params_extension';
    }
}