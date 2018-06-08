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

    namespace Webkul\Mobikul\Controller\Productalert;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Context;
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Webkul\Mobikul\Helper\Catalog as HelperCatalog;

    class Stock extends \Webkul\Mobikul\Controller\ApiController    {

        protected $_productLoader;

        public function __construct(
            Context $context,
            Emulation $emulate,
            HelperData $helper,
            HelperCatalog $helperCatalog,
            \Magento\Catalog\Model\ProductFactory $productLoader
        ) {
            $this->_productLoader = $productLoader;
            parent::__construct($helper, $context);
        }

        public function execute()   {
            $returnArray = [];
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["message"]      = "";
            $returnArray["success"]      = false;
            try {
                $wholeData            = $this->getRequest()->getPostValue();
                $this->_headers       = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey          = $this->getRequest()->getHeader("authKey");
                    $apiKey           = $this->getRequest()->getHeader("apiKey");
                    $apiPassword      = $this->getRequest()->getHeader("apiPassword");
                    $authData         = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $productId   = $this->_helper->validate($wholeData, "productId")  ? $wholeData["productId"]  : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $product     = $this->_productLoader->create()->load($productId);
                        $model       = $this->_objectManager->create("Magento\ProductAlert\Model\Stock")
                            ->setCustomerId($customerId)
                            ->setProductId($product->getId())
                            ->setPrice($product->getFinalPrice())
                            ->setWebsiteId($this->_objectManager->get("Magento\Store\Model\StoreManagerInterface")->getStore()->getWebsiteId());
                        $model->save();
                        $returnArray["message"] = __("Alert subscription has been saved..");
                        $returnArray["success"] = true;
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = __("Invalid Request");
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch (NoSuchEntityException $noEntityException) {
                $returnArray["message"] = __("There are not enough parameters.");
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            } catch (\Exception $e) {
                $returnArray["message"] = __("We can't update the alert subscription right now.");
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }