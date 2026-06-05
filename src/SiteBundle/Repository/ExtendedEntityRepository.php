<?php

namespace SiteBundle\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class ExtendedEntityRepository
 */
abstract class ExtendedEntityRepository extends ServiceEntityRepository
{
    /**
     * @param object $object
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function persist($object): void
    {
        $this->getEntityManager()->persist($object);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function save($object)
    {
        $this->persist($object);
        $this->flush();
    }

    public function delete($object)
    {
        $this->getEntityManager()->remove($object);
    }

    public function removeWithFlush($object)
    {
        $this->delete($object);
        $this->flush();
    }
}
