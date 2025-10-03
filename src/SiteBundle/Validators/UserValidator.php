<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 3/22/2017
 * Time: 3:34 PM
 */

namespace SiteBundle\Validators;


use Doctrine\ORM\EntityManager;
use SiteBundle\Validators\ValidatorConstants\UserFieldsConstants;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Exception\ValidatorException;

class UserValidator extends ValidatorContainer
{
    public function __construct(EntityManager $entity, TokenStorage $tokenStorage)
    {
        parent::__construct($entity, $tokenStorage);
    }

    /**
     * Validate User fields
     * @param array $data
     * @return bool|string
     */
    public function validate(array $data)
    {
        try {
            $this->validateArray($data, UserFieldsConstants::ADD_NEW_USER);
            return true;
        }catch (ValidatorException $validatorException){
            return $validatorException->getMessage();
        }
    }
}