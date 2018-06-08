<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Index;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Context;
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Webkul\Mobikul\Helper\Catalog as HelperCatalog;

    class UploadBannerPic extends \Webkul\Mobikul\Controller\ApiController  {

        public function __construct(
            Context $context,
            HelperData $helper,
            Emulation $emulate,
            HelperCatalog $helperCatalog
        ) {
            parent::__construct($helper, $context);
        }

        public function execute()   {
            $returnArray            = [];
            $returnArray["url"]     = "";
            $returnArray["message"] = "";
            $returnArray["success"] = false;
            $files = (array) $this->getRequest()->getFiles();
            if(isset($files)){
                $wholeData    = $this->getRequest()->getParams();
                $width        = $this->_helper->validate($wholeData, "width")      ? $wholeData["width"]      : 1000;
                $mFactor      = $this->_helper->validate($wholeData, "mFactor")    ? $wholeData["mFactor"]    : 1;
                $customerId   = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                $uploadHelper = $this->_objectManager->get("\Webkul\Mobikul\Helper\Upload");
                $uploadHelper->uploadPicture($files, $customerId, $customerId."-banner", "banner");
                $returnArray = $uploadHelper->resizeAndCache($width, $customerId, $mFactor, "banner");
                return $this->getJsonResponse($returnArray);
            }
            else{
                $returnArray["message"] = __("Invalid Image.");
                return $this->getJsonResponse($returnArray);
            }
        }

    }