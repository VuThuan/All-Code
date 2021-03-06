<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.3.20
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Reports\Cart;

use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Report\Api\Data\Query\ColumnInterface;
use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Context;
use Mirasvit\Reports\Model\Config;

class Overview extends AbstractReport
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config,
        Context $context
    ) {
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Abandoned Carts Overview');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('quote');


        $this->addFastFilters([
            'quote|created_at',
            'quote|store_id',
        ]);

        $this->setRequiredColumns([
            'quote|entity_id__cnt',
        ]);

        $this->setDefaultColumns([
            'quote|entity_id__cnt',
            'quote|subtotal__sum',
            'quote|grand_total__sum',
        ]);

        $this->setDefaultDimension(
            'quote|created_at__day'
        )->addDimensions([
            'quote|created_at__day',
            'quote|created_at__week',
            'quote|created_at__month',
            'quote|created_at__quarter',
            'quote|created_at__year',
        ]);

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'quote|entity_id__cnt',
            ]);

        $this->setDefaultFilters([
            ['quote|is_active',  1, 'eq'],
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function getActions($item)
    {
        return [
            [
                'label' => __('View Abandoned Carts'),
                'href'  => $this->getRangeUrl(
                    $this->getUiContext()->getActiveDimension(),
                    $item[$this->getUiContext()->getActiveDimension() . '_orig'],
                    'Cart_Abandoned'
                ),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRangeUrl($dimension, $fromDate, $report = 'Order_Plain')
    {
        $fromDate = (new DateTime())->strToTime($fromDate);
        $toDate = $fromDate;

        switch ($dimension) {
            default:
            case 'quote|day':
                //                $toDate += 24 * 60 * 60;
                break;

            case 'quote|week':
                $toDate += 7 * 24 * 60 * 60;
                break;

            case 'quote|month':
                $toDate += 30 * 24 * 60 * 60;
                break;

            case 'quote|quarter':
                $toDate += 80 * 24 * 60 * 60;
                break;

            case 'quote|year':
                $toDate += 365 * 24 * 60 * 60;
        }

        return $this->context->urlManager->getUrl(
            'reports/report/view',
            [
                'report' => $report,
                '_query' => [
                    'filters' => [
                        'quote|created_at' => [
                            'from' => (new \Zend_Date($fromDate))->get(DateTime::DATETIME_INTERNAL_FORMAT),
                            'to'   => (new \Zend_Date($toDate))->get(DateTime::DATETIME_INTERNAL_FORMAT),
                        ],
                    ],
                ],
            ]
        );
    }
}
