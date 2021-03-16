<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpreportsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Report extends \Magento\Backend\App\Action
{
    /**
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpreportsystem::mpreports'
        );
    }
}
