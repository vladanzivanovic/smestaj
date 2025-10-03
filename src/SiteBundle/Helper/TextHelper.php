<?php


namespace SiteBundle\Helper;


class TextHelper
{
    const PHONE_NUMBER = "/(\+)?(\d{1,3})([ ]|[\/._-])(\d{1,3})[-._\-\s]?(\d{2,4})[-._\-\s](\d{2,4})/";
    const EMAIL = "/(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/i";

    public function clearPhone($text, $replace = '')
    {
        return preg_replace(self::PHONE_NUMBER, $replace, $text);
    }

    public function clearEmail($text, $replace = '')
    {
        return preg_replace(self::EMAIL, $replace, $text);
    }

    public function clearText(string $text): string
    {
        $text = $this->clearPhone($text);
        $text = $this->clearEmail($text);

        return $text;
    }
}