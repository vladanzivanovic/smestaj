<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use AdminBundle\Dto\Embedded\DataTableQueryDto;
use AdminBundle\Model\DataTableModel;

final class DataTableRequestParser
{
    public function parse(DataTableQueryDto $dto): DataTableModel
    {
        $order = $dto->order;

        return new DataTableModel(
            $dto->draw,
            $dto->start,
            $dto->length,
            $dto->columns,
            (int) ($order[0]['column'] ?? 0),
            (string) ($order[0]['dir'] ?? 'asc'),
            (string) ($dto->search['value'] ?? '')
        );
    }
}
