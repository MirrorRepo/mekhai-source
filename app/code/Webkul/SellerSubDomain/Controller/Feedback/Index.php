<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubDomain\Controller\Feedback;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul SellerSubDomain Seller Feedback controller.
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context                             $context
     * @param \Webkul\SellerSubDomain\Helper\Data $helper
     * @param PageFactory                         $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\SellerSubDomain\Helper\Data $helper,
        PageFactory $resultPageFactory
    ) {
        $this->_helper = $helper;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $shop = $this->_helper->getShopNameByCurrentUrl();
        if ($shop) {
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->addHandle('marketplace_seller_feedback');
            return $resultPage;
        }
        return $this->resultRedirectFactory->create()->setPath(
            'marketplace',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
