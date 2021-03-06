<?php
namespace Cor\Artist\Block\Adminhtml\Artist\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('artist_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Artist Information'));
    }
}