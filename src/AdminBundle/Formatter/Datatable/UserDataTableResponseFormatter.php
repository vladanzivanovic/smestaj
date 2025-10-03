<?php

declare(strict_types=1);

namespace AdminBundle\Formatter\Datatable;

use AdminBundle\Model\DataTableModel;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Helper\ConstantsHelper;

final class UserDataTableResponseFormatter
{
    use DataTableResponseTrait;

    public function formatResponse(DataTableModel $tableModel, array $data, int $total): array
    {
        $data = array_map(function ($item) {
            $item['status_text'] = ConstantsHelper::getConstantName((string) $item['status'], 'STATUS', EntityStatusInterface::class);
            $item['role'] = !empty($user['roles']) ? $user['roles'][0] : '';

            return $item;
        }, $data);

        return $this->response($tableModel, $data, $total);

    }
}
