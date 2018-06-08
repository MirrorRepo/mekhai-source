<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Adminorder;

use Webkul\Mpcashondelivery\Controller\Adminhtml\Adminorder;
use Magento\Framework\Controller\ResultFactory;

class Index extends Adminorder
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpcashondelivery::mpcodorders');
        $resultPage->getConfig()->getTitle()->prepend(
            __('Marketplace Cash On Delivery Orders')
        );
        $resultPage->addBreadcrumb(
            __('Marketplace Cash On Delivery Orders'),
            __('Marketplace Cash On Delivery Orders')
        );
        return $resultPage;
    }
}
