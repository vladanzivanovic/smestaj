<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use AdminBundle\Model\DataTableModel;
use Symfony\Component\HttpFoundation\Request;

final class DataTableRequestParser
{
    public function formatRequest(Request $request)
    {
        $bag = $request->request;
        $order = $bag->all('order');

        return new DataTableModel(
            $bag->getInt('draw'),
            $bag->getInt('start'),
            $bag->getInt('length'),
            $bag->all('columns'),
            (int)$order[0]['column'],
            $order[0]['dir'],
            $bag->all('search')['value']
        );
    }
}
