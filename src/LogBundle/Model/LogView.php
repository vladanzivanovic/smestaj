<?php
/**
 * Created by PhpStorm.
 * User: todd
 * Date: 02/11/17
 * Time: 8:59 PM
 */

namespace LogBundle\Model;

use LogBundle\Collections\Collection;

class LogView
{
    public static function logToArray($log): array
    {
        $lines = Collection::createFromString($log, 'LogBundle\Model\LineView');
        $return = [];
        foreach ($lines as $line) {

            $return[] = $line->toArray();
        }
        return $return;
    }
}
