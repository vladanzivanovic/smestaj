<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 3/22/2017
 * Time: 4:57 PM
 */

namespace SiteBundle\Validators;


use Doctrine\ORM\EntityManager;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Exception\ValidatorException;

abstract class ValidatorContainer extends ServiceContainer
{
    private $fields;
    private $requredErrors = array();
    private $regexErrors = array();
    private $equalErrors = array();

    public function __construct(EntityManager $entity, TokenStorage $tokenStorage)
    {
        parent::__construct($entity, $tokenStorage);
    }

    /**
     * Validate input data
     * @param array $data
     * @param $fields
     * @return bool
     * @throws \Symfony\Component\Validator\Exception\ValidatorException
     */
    protected function validateArray(array $data, $fields)
    {
        $this->fields = $fields;

        foreach($data as $field => $val){
            $this->requiredField($field, $val);
            $this->regexValid($field, $val);
            $this->equalWith($field, $val, $data);
        }

        if(count($this->requredErrors) > 0){
            throw new ValidatorException('Niste popunili obavezna polja: '. implode(', ', $this->requredErrors));
        }

        if(count($this->regexErrors) > 0){
            throw new ValidatorException('Sledeća polja nisu validna: '. implode(', ', $this->regexErrors));
        }

        if(count($this->equalErrors) > 0){
            throw new ValidatorException('Sledeća polja se ne poklapaju: '. implode(', ', $this->equalErrors));
        }

        return true;
    }

    /**
     * Check if field is required and is not empty
     * @param $name
     * @param $value
     */
    private function requiredField($name, $value)
    {
        if(isset($this->fields[$name]['required']) && empty($value)) {
            $this->requredErrors[$name] = $this->fields[$name]['name'];
        }
    }

    /**
     * Check if field is valid against regexp
     * @param $name
     * @param $value
     */
    private function regexValid($name, $value)
    {
        if(isset($this->fields[$name]['regexp']) &&
            (0 === preg_match($this->fields[$name]['regexp'], $value) || false === preg_match($this->fields[$name]['regexp'], $value))
        ){
            $this->regexErrors[$name] = $this->fields[$name]['name'];
        }
    }

    /**
     * Check if fields are equal
     * @param $name
     * @param $value
     * @param array $data
     */
    private function equalWith($name, $value, array $data)
    {
        if(isset($this->fields[$name]['equal'])) {
            $againstField = $this->fields[$name]['equal'];
            if($data[$againstField] != $value){
                $this->equalErrors[$name] = $this->fields[$name]['name'] .' i '. $this->fields[$againstField]['name'];
            }
        }
    }
}