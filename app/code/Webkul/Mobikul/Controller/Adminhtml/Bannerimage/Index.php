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

    namespace Webkul\Mobikul\Controller\Adminhtml\Bannerimage;

    class Index extends \Webkul\Mobikul\Controller\Adminhtml\Bannerimage  {

        public function execute()   {
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->setActiveMenu("Webkul_Mobikul::bannerimage");
            $resultPage->getConfig()->getTitle()->prepend(__("Manage Banner Images"));
            return $resultPage;
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::bannerimage");
        }

    }