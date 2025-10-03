<?php

/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 5/6/2017
 * Time: 9:51 AM
 */

namespace SiteBundle\Exceptions;

use Exception;

class ApplicationException extends \Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}