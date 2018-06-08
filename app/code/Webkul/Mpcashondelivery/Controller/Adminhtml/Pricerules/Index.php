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
namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Pricerules;

use Webkul\Mpcashondelivery\Controller\Adminhtml\Pricerules as Pricerules;
use Magento\Framework\Controller\ResultFactory;

class Index extends Pricerules
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpcashondelivery::mpcodrates');
        $resultPage->getConfig()->getTitle()->prepend(
            __('Marketplace Cash on Delivery Rates Manager')
        );
        $resultPage->addBreadcrumb(
            __('Marketplace Cash on Delivery Rates Manager'),
            __('Marketplace Cash on Delivery Rates Manager')
        );
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Edit'
            )
        );
        $resultPage->addLeft(
            $resultPage->getLayout()->createBlock(
                'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Edit\Tabs'
            )
        );
        
        return $resultPage;
    }
}
