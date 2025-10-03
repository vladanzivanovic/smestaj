<?php
namespace SiteBundle\Services;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PaginationService
{
    protected $offset;
    protected $currentPage;
    protected $totalPage;
    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var int
     */
    private $limit;

    /**
     * @param QueryBuilder $queryBuilder
     * @param $currentPage
     * @return array
     */
    public function pagination(QueryBuilder $queryBuilder, int $currentPage, int $limit): array
    {
        $dataArray = [];
        $this->query = $queryBuilder;
        $this->currentPage = $currentPage;
        $this->limit = $limit;
        $this->calculateOffset();

        $dataArray['data'] = $this->getPaginationData();
        $totalRows = $this->totalRows();

        $dataArray['pagination']['totalPages'] = $this->totalPage;
        $dataArray['pagination']['totalRows'] = $totalRows;
        $dataArray['pagination']['prevPage'] = $this->getPrevPage();
        $dataArray['pagination']['nextPage'] = $this->getNextPage();

        $dataArray['pagination']['disableFirst'] = ($this->currentPage == 1);
        $dataArray['pagination']['disableLast'] = ($this->currentPage >= $this->totalPage);
        $dataArray['pagination']['currentPage'] = (int) $this->currentPage;

        return $dataArray;
    }

    /**
     * @return int|mixed
     */
    public function calculateOffset()
    {
        $this->offset = ($this->currentPage <= 1) ? 0 : $this->limit*($this->currentPage - 1);
        return $this;
    }

    /**
     * Set number of record for page calculation
     * @param $no
     */
    public function setNumberOfData($no)
    {
        $this->limit = $no;
    }

    /**
     * @param $rows
     * @return int
     */
    public function getTotalPageNumber($rows)
    {
        $this->totalPage = (int) ceil((int)$rows/$this->limit);
    }

    /**
     * Get pagination query result
     * @return array
     */
    private function getPaginationData(): array
    {
        return $this->query
            ->setFirstResult($this->offset)
            ->setMaxResults($this->limit)
            ->getQuery()
            ->getResult();
    }

    private function getPrevPage()
    {
        $prevPage = $this->currentPage;
        --$prevPage;

        if($this->currentPage <= 1 )
            $prevPage = 1;

        if($this->currentPage > $this->totalPage)
            $prevPage = $this->totalPage;

        return $prevPage;
    }

    private function getNextPage()
    {
        $nextPage = $this->currentPage;
        ++$nextPage;
        if($this->currentPage >= $this->totalPage )
            $nextPage = $this->totalPage;

        return $nextPage;
    }

    /**
     * Get total rows for pagination
     *
     * @return float|int
     */
    private function totalRows()
    {
        $alias = current($this->query->getDQLPart('from'))->getAlias();

        $totalRowsQuery = $this->query
            ->select("COUNT(DISTINCT $alias.id ) as totalRows")
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery();

        $totalRowsQuery = $this->filterParams($totalRowsQuery);

        $counter = array_sum(array_column($totalRowsQuery->getScalarResult(), 'totalRows'));

        $this->getTotalPageNumber($counter);

        return $counter;
    }

    private function filterParams(Query $query)
    {
        $params = $query->getParameters();
        $queryDql = $query->getDQL();

        $query->setParameters([]);

        foreach ($params->toArray() as $param) {
            /** @var Parameter $param */
            if (false !== strpos($queryDql, ':'.$param->getName())) {
                $query->setParameter($param->getName(), $param->getValue());
            }
        }

        return $query;
    }
}
