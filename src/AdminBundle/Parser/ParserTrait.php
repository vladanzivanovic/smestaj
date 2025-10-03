<?php

namespace AdminBundle\Parser;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

trait ParserTrait
{
    private function setLanguageArray(ParameterBagInterface $parameterBag, ParameterBag $bag): array
    {
        $langArray = [];

        $locales = explode('|', $parameterBag->get('locales'));

        foreach ($bag->all() as $key => $item) {
            $langCode = substr($key, 0, 2);

            if (false === in_array($langCode, $locales)) {
                continue;
            }

            if (false === array_key_exists($langCode, $langArray)) {
                $langArray[$langCode] = new ParameterBag();
            }

            $langBag = $langArray[$langCode];
            $langBag->set(substr($key, 3), $item);
        }

        return $langArray;
    }
}