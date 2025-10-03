<?php

namespace SiteBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class ServiceContainer
{
    protected $objectManager;
    protected $token;
    protected $mainRepo;

    public function __construct(ObjectManager $objectManager, TokenStorageInterface $tokenStorage)
    {
        $this->objectManager = $objectManager;
        $this->token = $tokenStorage;
    }

    /**
     * @param array  $data
     * @param string $entity
     *
     * @return object|null
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function arrayToEntity( array $data, $entity)
    {
        $entityName = $entity;
        if(is_object($entity)) {
            $entityName = get_class($entity);
        }

        /** @var ClassMetadata $metaData */
        $metaData = $this->objectManager->getClassMetadata($entityName);
        $ident = $metaData->getSingleIdentifierFieldName();

        if(isset($data[ucfirst($ident)])) {
            $data[$ident] = $data[ucfirst($ident)];
        }

        $entity = $this->setEntity($entity, $ident, $data, $metaData->name);

        $data = $this->prepareAttributes($data, $metaData, !empty($data[$ident]));

        foreach ($data as $prop => $item) {
            if( $method = $this->getSetter($prop, $entity) ) {
                $entity->{$method}($item);
            }
        }

        return $entity;
    }

    /**
     * Insert Data into Db
     * @param $dataObject
     * @return mixed
     */
    protected function insertData($dataObject)
    {
        $this->objectManager->persist($dataObject);
        $this->updateData($dataObject);

        return $dataObject;
    }

    /**
     * Update data Object in Db
     * @param object|array|null $dataObject
     */
    protected function updateData($dataObject = null)
    {
        $this->objectManager->flush($dataObject);
    }

    protected function removeData($dataObject)
    {
        $this->objectManager->remove($dataObject);
        $this->updateData($dataObject);
    }

    private function setEntity($entity, $ident, $data, $entityName)
    {
        if(is_object($entity)) {
            return $entity;
        }

        if(!empty($data[$ident])) {
            return $this->objectManager->getRepository($entity)->findOneBy([ $ident => $data[$ident] ]);
        }

        return new $entityName;
    }

    private function prepareAttributes( array $data, ClassMetadata $metaData, $hasIdent = false)
    {
        $dateType = ['date', 'datetime', 'time'];

        foreach ($data as $fieldName => &$fieldValue) {
            $fieldType = $metaData->getTypeOfField($fieldName);

            if( !$metaData->hasAssociation($fieldName) ){

                if(true === in_array($fieldType, $dateType)) {
                    $fieldValue = new \DateTime($fieldValue);
                }

                continue;
            }

            $association = $metaData->getAssociationMapping($fieldName);
            if(!isset($association['joinColumns']) || (true === $association['joinColumns'][0]['nullable'] && null === $fieldValue))
                continue;
            $fieldValue = ($fieldValue instanceof  $association['targetEntity']) ? $fieldValue : $this->objectManager->getReference($association['targetEntity'], $fieldValue);

            unset($fieldValue);
        }

        $data = $this->setSystemsFields($data, $metaData, $hasIdent);
        return $data;
    }

    private function setSystemsFields($data, ClassMetadata $metaData, $hasIdent = false)
    {
        $dateType = ['date', 'datetime', 'time'];
        $tableFields = array_diff(array_keys($metaData->fieldNames), array_keys($data));

        foreach ($tableFields as $tableField) {
            if(false === stripos($tableField, 'sys'))
                continue;

            if(true === $hasIdent && false !== stripos($tableField, 'creat'))
                continue;

            $fieldType = $metaData->getTypeOfField(strtolower($tableField));

            if(true === in_array($fieldType, $dateType)){
                $data[$tableField] = new \DateTime();
            }
            if($fieldType === 'integer')
                $data[$tableField] = $this->token->getToken()->getUser();
        }

        return $data;
    }

    /**
     * @param string $prop
     * @param object $entityObj
     *
     * @return bool|string
     */
    private function getSetter(string $prop, $entityObj)
    {
        $methodName = 'set'. ucfirst($prop);

        return method_exists($entityObj, $methodName) ? $methodName : false;
    }
}