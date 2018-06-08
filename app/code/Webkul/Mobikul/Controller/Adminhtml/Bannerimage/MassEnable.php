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

    class MassEnable extends \Webkul\Mobikul\Controller\Adminhtml\Bannerimage    {

        public function execute()   {
            $resultRedirect = $this->resultRedirectFactory->create();
            $collection     = $this->_filter->getCollection($this->_collectionFactory->create());
            $bannersUpdated = 0;
            $coditionArr    = [];
            foreach ($collection->getAllIds() as $key => $bannerimageId) {
                $currentBanner = $this->_bannerimageRepository->getById($bannerimageId);
                $bannerimageData = $currentBanner->getData();
                if (count($bannerimageData)) {
                    $condition = "`id`=".$bannerimageId;
                    array_push($coditionArr, $condition);
                    $bannersUpdated++;
                }
            }
            $coditionData = implode(" OR ", $coditionArr);
            $collection->setBannerimageData($coditionData, ["status"=>1]);
            if ($bannersUpdated) {
                $this->messageManager->addSuccess(__("A total of %1 record(s) were enabled.", $bannersUpdated));
            }
            return $resultRedirect->setPath("mobikul/bannerimage/index");
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::bannerimage");
        }

    }