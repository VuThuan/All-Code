<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customoption
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customoption\Block\Options\Type;

class Date extends \Webkul\Customoption\Block\Options\Type\AbstractType
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/options/type/date.phtml';
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Webkul_Customoption::options/type/date.phtml');
    }

    /**
     * Return text input for price type
     *
     * @param string $extraParams
     * @return string
     */
    public function getPriceTypeSelectHtml($extraParams = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $type = '';
        if ($this->getRequest()->getParam('type') &&
            ($this->getRequest()->getParam('type') == 'configurable' || $this->getRequest()->getParam('type') == 'bundle')) {
            $type = $this->getRequest()->getParam('type');
        } elseif ($this->getRequest()->getParam('id')) {
            if ($this->getParentBlock()->getProduct()->getTypeId() == 'configurable' || $this->getParentBlock()->getProduct()->getTypeId() == 'bundle') {
                $type = $this->getParentBlock()->getProduct()->getTypeId();
            }
        }
        
        $this->_optionPrice = $objectManager->get('Webkul\Customoption\Model\Config\Product\Price');
        $this->getChildBlock(
            'option_price_type'
        )->setOptions(
            $this->_optionPrice->toOptionArray($type)
        );

        return parent::getPriceTypeSelectHtml($extraParams);
    }
}
