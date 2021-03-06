<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerVacation\Ui\Component\Listing\Columns;

/**
 * Webkul MpSellerVacation columns renderer
 *
 * @category Webkul
 * @package  Webkul_MpSellerVacation
 * @author   Webkul Software Private Limited
 */
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Customerlink.
 */
class Customerlink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {

        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id'])) {
                    $url = $this->_urlBuilder
                        ->getUrl(
                            'customer/index/edit',
                            ['id' => $item['seller_id']]
                        );
                    $item[$fieldName] = "<a href='"
                    .$this->_urlBuilder->getUrl(
                        'customer/index/edit',
                        ['id' => $item['seller_id']]
                    )."'
                    target='blank' title='".__('View Customer')."'>"
                    .$item[$fieldName]
                    .'</a>';
                }
            }
        }

        return $dataSource;
    }
}
