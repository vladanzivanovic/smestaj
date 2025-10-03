<?php

declare(strict_types=1);

namespace AdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

class DataTableModel
{
    private int $draw;

    private int $offset;

    private int $limit;

    private ArrayCollection $columns;

    private string $orderColumn;

    private string $orderDirection;

    private ?string $search;

    public function __construct(
        int $draw,
        int $offset,
        int $limit,
        array $columns,
        int $orderColumn,
        string $orderDirection,
        string $search = null
    ) {
        $this->draw = $draw;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->setColumns($columns);
        $this->setOrderColumn($orderColumn);
        $this->orderDirection = $orderDirection;
        $this->search = $search;
    }

    /**
     * @param array $columns
     */
    private function setColumns(array $columns): void
    {
        $this->columns = new ArrayCollection();

        foreach ($columns as $column) {
            $this->columns->add(new DataTableColumnModel(
                $column['data'],
                $column['name'],
                $column['searchable'] == true,
                $column['orderable'] == true,
                $column['search']['value'],
                $column['search']['regex']
            ));
        }
    }

    private function setOrderColumn(int $orderColumn)
    {
        /** @var DataTableColumnModel $dataTableColumn */
        $dataTableColumn = $this->columns->get($orderColumn);

        $this->orderColumn = $dataTableColumn->getName();
    }

    /**
     * @return int
     */
    public function getDraw(): int
    {
        return $this->draw;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return ArrayCollection
     */
    public function getColumns(): ArrayCollection
    {
        return $this->columns;
    }

    /**
     * @return string
     */
    public function getOrderColumn(): string
    {
        return $this->orderColumn;
    }

    /**
     * @return string
     */
    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    /**
     * @return string|null
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }
}