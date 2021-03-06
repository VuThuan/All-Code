<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\NewsletterCustom\Block\Adminhtml\Template;

/**
 * Newsletter template preview block
 *
 * @api
 * @since 100.0.2
 */
class Preview extends \Magento\Backend\Block\Widget
{
    /**
     * Name for profiler
     *
     * @var string
     */
    protected $profilerName = "newsletter_template_proccessing";

    /**
     * @var \Magento\Newsletter\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var $_templateId
     */
    protected $_templateId;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Newsletter\Model\TemplateFactory $templateFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        array $data = []
    ) {
        $this->_templateFactory = $templateFactory;
        $this->_subscriberFactory = $subscriberFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get html code
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var $template \Magento\Newsletter\Model\Template */
        $template = $this->_templateFactory->create();
        $id = $this->_templateId;

        $this->loadTemplate($template, $id);

        \Magento\Framework\Profiler::start($this->profilerName);
        $vars = [];

        $vars['subscriber'] = $this->_subscriberFactory->create();

        $template->emulateDesign($this->getStoreId());
        $templateProcessed = $this->_appState->emulateAreaCode(
            \Magento\Newsletter\Model\Template::DEFAULT_DESIGN_AREA,
            [$template, 'getProcessedTemplate'],
            [$vars]
        );
        $template->revertDesign();

        // if ($template->isPlain()) {
            // $templateProcessed = "<pre>" . htmlspecialchars($templateProcessed) . "</pre>";
        // }

        \Magento\Framework\Profiler::stop($this->profilerName);

        return $templateProcessed;
    }

    /**
     * Return template HTML data
     *
     * @return array
     */
    public function getTemplateHtmlData($id)
    {
        $this->_templateId = $id;
        return $this->_toHtml();
    }

    /**
     * Get Store Id from request or default
     *
     * @return int|null
     */
    protected function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        if ($storeId) {
            return $storeId;
        }

        $defaultStore = $this->_storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $this->_storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }

        return $defaultStore ? $defaultStore->getId() : null;
    }

    /**
     * @param \Magento\Newsletter\Model\Template $template
     * @param string $id
     * @return $this
     */
    protected function loadTemplate(\Magento\Newsletter\Model\Template $template, $id)
    {
        $template->load($id);
        return $this;
    }
}
