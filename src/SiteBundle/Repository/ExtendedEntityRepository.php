<?php

namespace SiteBundle\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class ExtendedEntityRepository
 */
class ExtendedEntityRepository extends ServiceEntityRepository
{
    /**
     * @param object $object
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function persist($object): void
    {
        $this->_em->persist($object);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function save($object)
    {
        $this->persist($object);
        $this->flush();
    }

    public function delete($object)
    {
        $this->_em->remove($object);
    }

    public function removeWithFlush($object)
    {
        $this->delete($object);
        $this->flush();
    }
}
