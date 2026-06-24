<?php

declare(strict_types=1);

namespace SiteBundle\Repository;

use AdminBundle\Model\DataTableModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsInfoPage;

/**
 * @extends ServiceEntityRepository<AdsInfoPage>
 */
final class AdsInfoPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdsInfoPage::class);
    }

    public function findOneBySlug(string $slug): ?AdsInfoPage
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findOneByLinkedAds(Ads $ads): ?AdsInfoPage
    {
        return $this->findOneBy(['linkedAds' => $ads]);
    }

    /**
     * @return array<int, AdsInfoPage>
     */
    public function getAdminList(DataTableModel $tableModel): array
    {
        $query = $this->buildAdminListQuery($tableModel)
            ->setFirstResult($tableModel->getOffset())
            ->setMaxResults($tableModel->getLimit())
            ->orderBy($this->resolveOrderColumn($tableModel->getOrderColumn()), $tableModel->getOrderDirection());

        return $query->getQuery()->getResult();
    }

    public function countData(DataTableModel $tableModel): int
    {
        $query = $this->buildAdminListQuery($tableModel)
            ->select('COUNT(DISTINCT info.id)');

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    private function buildAdminListQuery(DataTableModel $tableModel): QueryBuilder
    {
        $query = $this->createQueryBuilder('info')
            ->leftJoin('info.linkedAds', 'linkedAds');

        $search = $tableModel->getSearch();

        if (null !== $search && '' !== $search) {
            $query->andWhere('
                    info.propertyName LIKE :search
                 OR info.hostFirstName LIKE :search
                 OR info.hostLastName LIKE :search
                 OR info.addressCity LIKE :search
                 OR info.facebookUrl LIKE :search
                 OR info.instagramUrl LIKE :search
                 OR linkedAds.title LIKE :search
                 OR linkedAds.id LIKE :search
            ')
                ->setParameter('search', '%' . $search . '%');
        }

        return $query;
    }

    private function resolveOrderColumn(string $orderColumn): string
    {
        return match ($orderColumn) {
            'id', 'propertyName', 'addressCity', 'published', 'createdAt', 'updatedAt' => 'info.' . $orderColumn,
            'linkedAdsTitle' => 'linkedAds.title',
            default => 'info.id',
        };
    }
}
