<?php

namespace SiteBundle\Twig;

use SiteBundle\Entity\Category;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppTestExtension extends \Twig_Extension
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
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('category', [$this, 'isCategory']),
        ];
    }

    public function isCategory($item)
    {
        return $item instanceof Category;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'params_extension';
    }
}