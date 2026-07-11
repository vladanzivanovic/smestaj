<?php

declare(strict_types=1);

namespace SiteBundle\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ParamsExtension extends AbstractExtension
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_params', [$this, 'getParams']),
            new TwigFunction('param_exist', [$this, 'isSetAndExist']),
            new TwigFunction('locale_codes', [$this, 'getCodeFromLocales']),
        ];
    }

    public function getParams($parameter)
    {
        return $this->params->get($parameter);
    }

    public function isSetAndExist($value): bool
    {
        return !empty($value);
    }

    public function getCodeFromLocales(): array
    {
        return array_map(function ($locale) {
            return $locale['code'];
        }, $this->getParams('languages'));
    }
}
