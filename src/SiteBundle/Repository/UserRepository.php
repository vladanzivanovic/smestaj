<?php

namespace SiteBundle\Repository;

use AdminBundle\Model\DataTableModel;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\Role;
use SiteBundle\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends ExtendedEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $username
     * @param null   $id
     *
     * @return mixed|UserInterface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername($username, $id = null)
    {

        $query = $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameters(['username' => $username, 'email' => $username]);

        if((int)$id > 0) {
            $query->andWhere('u.id <> :id')
                ->setParameter('id', $id);
        }

        return $query->getQuery()
            ->getOneOrNullResult();
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }

    /**
     * Get All Users
     * @return array
     */
    public function getAll()
    {
        $query = $this->getBasePart()
            ->addSelect('
                u.firstname AS FirstName,
                u.lastname AS LastName,
                u.address AS Address,
                u.telephone AS Telephone,
                u.mobilephone AS MobilePhone
            ')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return array<int, mixed>
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id): array
    {
        $query = $this->getBasePart()
            ->addSelect('
                u.firstname AS FirstName,
                u.lastname AS LastName,
                u.address AS Address,
                u.telephone AS Telephone,
                u.mobilephone AS MobilePhone,
                u.viber AS Viber,
                u.contactemail AS ContactEmail,
                city.name AS CityName
            ')
            ->leftJoin('u.city', 'city')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @return array<mixed>
     */
    public function getActiveOwnersForOptions(): array
    {
        $query = $this->createQueryBuilder('u')
            ->select(
                'u.id as value',
                'CONCAT(u.firstname, \' \', u.lastname, \' - \', u.email) as title'
            )
            ->innerJoin('u.roles', 'utr')
            ->innerJoin('utr.role', 'r')
            ->where('u.status = :activeStatus')
            ->andWhere('r.code = :advancedUser')
            ->setParameter('activeStatus', EntityStatusInterface::STATUS_ACTIVE)
            ->setParameter('advancedUser', Role::ROLE_ADVANCED_USER);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countData(DataTableModel $tableModel)
    {
        $query = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as total')
            ->innerJoin('u.roles', 'ur')
            ->where('u INSTANCE OF :userClass')
            ->setParameter('userClass', 'user')
        ;

        if (!empty($tableModel->getSearch())) {
            $whereQuery = '
                CONCAT(u.firstname, \' \', u.lastname) LIKE :search or
                u.email LIKE :search
            ';

            $query
                ->andWhere($whereQuery)
                ->setParameter('search', '%'.$tableModel->getSearch().'%');
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    public function getAdminList(DataTableModel $tableModel): array
    {
        $query = $this->createQueryBuilder('u')
            ->select(
                'u.id as id',
                'CONCAT(u.firstname, \' \', u.lastname) as full_name',
                'u.email as email',
                'u.status as status',
                'GROUP_CONCAT(role.code) as roles'
            )
            ->innerJoin('u.roles', 'ur')
            ->innerJoin('ur.role', 'role')
            ->where('u INSTANCE OF :userClass')
            ->setParameter('userClass', 'user')
            ->setFirstResult($tableModel->getOffset())
            ->setMaxResults($tableModel->getLimit())
            ->orderBy($tableModel->getOrderColumn(), $tableModel->getOrderDirection())
            ->groupBy('u.id')
        ;

        if (!empty($tableModel->getSearch())) {
            $whereQuery = '
                CONCAT(u.firstname, \' \', u.lastname) LIKE :search or
                u.email LIKE :search
            ';

            $query
                ->andWhere($whereQuery)
                ->setParameter('search', '%'.$tableModel->getSearch().'%');
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Default query part
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBasePart()
    {
        $query = $this->createQueryBuilder('u')
            ->select('
                u.id AS Id,
                u.username AS Username,
                u.status AS IsActive,
                u.email AS Email
            ');

        return $query;
    }
}
