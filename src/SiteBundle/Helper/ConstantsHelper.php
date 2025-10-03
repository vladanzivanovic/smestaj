<?php

namespace SiteBundle\Helper;

use SiteBundle\Entity\Ads;

class ConstantsHelper
{
    /**
     * @param string|int $searchVal
     * @param string     $group
     * @param string     $className
     *
     * @return null|string
     */
    public static function getConstantName($searchVal, string $group, string $className): ?string
    {
        $reflection = new \ReflectionClass($className);
        $constants = $reflection->getConstants();

        foreach ($constants as $name => $value) {
            if ($value == $searchVal && false !== strpos($name, $group)) {
                return strtolower(str_replace($group.'_', '', $name));
            }
        }

        return null;
    }

    /**
     * @param string $str
     * @param string $group
     * @param string $className
     *
     * @return mixed|null
     */
    public static function getConstantValueByStr($str, $group, $className)
    {
        $reflection = new \ReflectionClass($className);
        $constants = $reflection->getConstants();

        $constName = $group.'_'.strtoupper($str);

        if (true === array_key_exists($constName, $constants)) {
            return $reflection->getConstant($constName);
        }

        return null;
    }

    public static function getAllConstants()
    {
        $ads = (new \ReflectionClass(Ads::class))->getConstants();

        return array_merge($ads);
    }
}
