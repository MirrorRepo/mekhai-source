<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Mobikul
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Adminhtml\Featuredcategories;
    use Magento\Backend\App\Action;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;

    class Index extends Action  {

        protected $_resultPage;
        protected $_resultPageFactory;

        public function __construct(
            Context $context,
            PageFactory $resultPageFactory
        ) {
            parent::__construct($context);
            $this->_resultPageFactory = $resultPageFactory;
        }

        public function execute()   {
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->setActiveMenu("Webkul_Mobikul::featuredcategories");
            $resultPage->getConfig()->getTitle()->prepend(__("Manage Featured Categories"));
            return $resultPage;
        }

        protected function _isAllowed() {
            return $this->_authorization->isAllowed("Webkul_Mobikul::featuredcategories");
        }

    }