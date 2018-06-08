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

    namespace Webkul\Mobikul\Controller\Adminhtml\Categoryimages;
    use Magento\Backend\App\Action;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;

    class Index extends \Webkul\Mobikul\Controller\Adminhtml\Categoryimages      {

        public function execute()   {
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->setActiveMenu("Webkul_Mobikul::categoryimages");
            $resultPage->getConfig()->getTitle()->prepend(__("Manage Category's Banners and Icons"));
            return $resultPage;
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::categoryimages");
        }

    }