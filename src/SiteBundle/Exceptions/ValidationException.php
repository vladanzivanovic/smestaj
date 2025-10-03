<?php

/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 5/6/2017
 * Time: 9:51 AM
 */

namespace SiteBundle\Exceptions;

use Exception;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends ApplicationException
{
    /**
     * ValidationException constructor.
     * @param ConstraintViolationListInterface $validator
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct( ConstraintViolationListInterface $validator, $code = 0, Exception $previous = null)
    {
        parent::__construct(json_encode($this->toArray($validator)), $code, $previous);
    }

    /**
     * Transform validation errors object list to error messages array
     * @param $validator
     * @return array
     */
    private function toArray($validator)
    {
        $errorArray = array();

        /** @var ConstraintViolationInterface $error */
        foreach($validator as $error)
        {
            $errorArray[$error->getPropertyPath()] = $error->getMessage();
        }

        return $errorArray;
    }
}