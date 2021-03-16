<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Name;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Mapping
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Name
 */
class Mapping extends AbstractFieldArray
{
    /**
     * @var
     */
    protected $_mageGroupRender;

    /**
     * @var
     */
    protected $_plentyGroupRender;

    /**
     * Mapping constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
       Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'mage_name',
            ['label' => __('Magento Name'), 'renderer' => $this->_getMageGroupRender()]
        );

        $this->addColumn(
            'plenty_name',
            ['label' => __('PlentyMarkets Name'), 'renderer' => $this->_getPlentyGroupRender()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Name Mapping');
    }


    /**
     * @return BlockInterface|Renderer\Magento
     * @throws LocalizedException
     */
    protected function _getMageGroupRender()
    {
        if (!$this->_mageGroupRender) {
            $this->_mageGroupRender = $this->getLayout()->createBlock(
                Renderer\Magento::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mageGroupRender->setClass('mage_name_select');
        }
        return $this->_mageGroupRender;
    }

    /**
     * @return BlockInterface|Renderer\Plenty
     * @throws LocalizedException
     */
    protected function _getPlentyGroupRender()
    {
        if (!$this->_plentyGroupRender) {
            $this->_plentyGroupRender = $this->getLayout()->createBlock(
                Renderer\Plenty::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_plentyGroupRender->setClass('plenty_name_select');
        }
        return $this->_plentyGroupRender;
    }

    /**
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMageGroupRender()->calcOptionHash($row->getData('mage_name'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPlentyGroupRender()->calcOptionHash($row->getData('plenty_name'))] = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }
}
