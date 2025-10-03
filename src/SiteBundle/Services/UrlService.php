<?php
/**
 * Created by PhpStorm.
 * User: Vladan
 * Date: 9/27/2016
 * Time: 7:55 AM
 */

namespace SiteBundle\Services;

use SiteBundle\Constants\UrlLetterConstants;

class UrlService
{
    private $patternSpace = "/\\s+/";
    private $patternSpecialSpace = "/[^A-z0-9\\s]+/";
    private $patternSpecial = "/[^A-z0-9]+/";

    public function generateSeoUrl($str)
    {
        $str = trim($str);
        $str = str_replace(UrlLetterConstants::LETTER_LATIN, UrlLetterConstants::LETTER_SEO, $str);
        $str = preg_replace($this->patternSpecialSpace, "", $str);
        $str = preg_replace($this->patternSpace, "-", $str);

        return strtolower($str);
    }

    public function cleanStringFromSpecialChar($str, $replace)
    {
        return preg_replace($this->patternSpecial, $replace, $str);
    }
}
