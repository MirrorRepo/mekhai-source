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

    class NewAction extends \Webkul\Mobikul\Controller\Adminhtml\Bannerimage      {

        public function execute()   {
            $resultForward = $this->_resultForwardFactory->create();
            $resultForward->forward("edit");
            return $resultForward;
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::bannerimage");
        }

    }