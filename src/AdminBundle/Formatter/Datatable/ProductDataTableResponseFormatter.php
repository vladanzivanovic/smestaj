<?php

declare(strict_types=1);

namespace AdminBundle\Formatter\Datatable;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Helper\ConstantsHelper;
use AdminBundle\Model\DataTableModel;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductDataTableResponseFormatter
{
    use DataTableResponseTrait;

    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    /**
     * @param DataTableModel $tableModel
     * @param array          $data
     * @param int            $total
     *
     * @return array
     */
    public function formatResponse(DataTableModel $tableModel, array $data, int $total): array
    {
        $now = new \DateTime();
        $data = array_map(function ($ad) use ($now) {
            $statusText = ConstantsHelper::getConstantName((string) $ad['status'], 'STATUS', EntityStatusInterface::class);
            $ad['status_text'] = $this->translator->trans($statusText);
            $ad['position_text'] = null;

            if (null !== $ad['payedDate']) {
                $prefix = 'ističe za';
                $dateDiff = $now->diff($ad['payedDate']);

                if ($dateDiff->format('%R') === '-' ) {
                    $prefix = 'istekao pre';
                }

                $ad['payed_date_text'] = $prefix.' '. $dateDiff->format('%a') .' dana';
            }

            return $ad;
        }, $data);

        return $this->response($tableModel, $data, $total);

    }
}
