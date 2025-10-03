<?php

namespace SiteBundle\Helper;

use Exception;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ValidatorService
 */
class ValidatorHelper extends TraceableValidator
{
    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        parent::__construct($validator);
    }

    /**
     * @param ConstraintViolationListInterface|ConstraintViolationList $errors
     *
     * @return array
     * @throws Exception
     */
    public function parseErrors(ConstraintViolationListInterface $errors): array
    {
        $errorsArray = [];
        foreach ($errors->getIterator() as $val) {
            $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $val->getPropertyPath()));
            if (!isset($errorsArray[$key])) {
                $errorsArray[$key] = $val->getMessage();
            }
        }

        return $errorsArray;
    }

    /**
     * @param ConstraintViolationListInterface|ConstraintViolationList $errors
     *
     * @return string
     * @throws Exception
     */
    public function getFirstError(ConstraintViolationListInterface $errors): string
    {
        foreach ($errors->getIterator() as $val) {
            return $val->getMessage();
        }

        return '';
    }
}
