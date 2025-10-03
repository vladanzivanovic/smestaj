<?php

namespace AdminBundle\Formatter\Datatable;

use AdminBundle\Model\DataTableModel;

trait DataTableResponseTrait
{
    /**
     * @param DataTableModel $tableModel
     * @param array          $data
     * @param int            $total
     *
     * @return array
     */
    public function response(DataTableModel $tableModel, array $data, int $total): array
    {
        return [
            'draw' => $tableModel->getDraw(),
            'data' => $data,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
        ];
    }
}
