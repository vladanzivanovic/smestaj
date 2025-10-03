<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 1/27/2017
 * Time: 2:23 PM
 */

namespace SiteBundle\Helper;


class StringHelper
{
    public function formatPhone($phone)
    {
        $phone = preg_replace('/^\d/', '', $phone);

        if(strlen($phone) <= 7) {
            $phone = preg_replace('/(\d{3})(\d{4})/', '$1-$2', $phone);
        }
        elseif(strlen($phone) == 10) {
            $phone = preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $phone);
        }
        return $phone;
    }
}