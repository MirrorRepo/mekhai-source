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

    namespace Webkul\Mobikul\Controller\Checkout;

    class AddToCart extends AbstractCheckout    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["success"]      = false;
            $returnArray["quoteId"]      = 0;
            $returnArray["cartCount"]    = 0;
            $returnArray["responseCode"] = 0;
            try {
                $wholeData       = $this->getRequest()->getPostValue();
                $this->_headers  = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $qty             = $this->_helper->validate($wholeData, "qty")             ? $wholeData["qty"]             : 1;
                        $params          = $this->_helper->validate($wholeData, "params")          ? $wholeData["params"]          : "{}";
                        $quoteId         = $this->_helper->validate($wholeData, "quoteId")         ? $wholeData["quoteId"]         : 0;
                        $storeId         = $this->_helper->validate($wholeData, "storeId")         ? $wholeData["storeId"]         : 1;
                        $productId       = $this->_helper->validate($wholeData, "productId")       ? $wholeData["productId"]       : 0;
                        $customerId      = $this->_helper->validate($wholeData, "customerId")      ? $wholeData["customerId"]      : 0;
                        $relatedProducts = $this->_helper->validate($wholeData, "relatedProducts") ? $wholeData["relatedProducts"] : "[]";
                        $params          = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($params);
                        $relatedProducts = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($relatedProducts);
                        $environment     = $this->_emulate->startEnvironmentEmulation($storeId);
                        $quote           = new \Magento\Framework\DataObject();
                        if ($customerId == 0 && $quoteId == 0) {
                            $quote = $this->_quoteFactory->create()
                                ->setStoreId($storeId)
                                ->setIsActive(true)
                                ->setIsMultiShipping(false)
                                ->save();
                            $quote->getBillingAddress();
                            $quote->getShippingAddress()->setCollectShippingRates(true);
                            $quote->collectTotals()->save();
                            $quoteId = (int) $quote->getId();
                            $returnArray["quoteId"] = $quoteId;
                        }
                        if ($qty == 0)
                            $qty = 1;
                        if ($customerId != 0) {
                            $quoteCollection = $this->_quoteFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote   = $quoteCollection->getFirstItem();
                            $quoteId = $quote->getId();
                            if ($quote->getId() < 0 || !$quoteId) {
                                $quote = $this->_quoteFactory->create()
                                    ->setStoreId($storeId)
                                    ->setIsActive(true)
                                    ->setIsMultiShipping(false)
                                    ->save();
                                $quoteId = (int) $quote->getId();
                                $customer = $this->_customerRepository->getById($customerId);
                                $quote->assignCustomer($customer);
                                $quote->setCustomer($customer);
                                $quote->getBillingAddress();
                                $quote->getShippingAddress()->setCollectShippingRates(true);
                                $quote->collectTotals()->save();
                            }
                        } else
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        $product = $this->_productFactory->create()->setStoreId($storeId)->load($productId);
                        if ($qty && !($product->getTypeId() == "downloadable")) {
                            $availableQty = $this->_stockRegistry->getStockItem($product->getId())->getQty();
                            if ($qty <= $availableQty) {
                                $filter = new \Magento\Framework\Filter\LocalizedToNormalized(["locale"=>$this->_objectManager->get("\Magento\Framework\Locale\Resolver")->getLocale()]);
                                $qty = $filter->filter($qty);
                            } else {
                                if (!in_array($product->getTypeId(), ["grouped", "configurable", "bundle"])) {
                                    $returnArray["message"] = __("The requested quantity is not available");
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
                        }
                        $request       = [];
                        $paramOption   = [];
                        $filesToDelete = [];
                        if (isset($params["options"])) {
                            $productOptions = $params["options"];
                            foreach ($productOptions as $optionId=>$values) {
                                $option = $this->_objectManager->create("\Magento\Catalog\Model\Product\Option")->load($optionId);
                                $optionType = $option->getType();
                                if (in_array($optionType, ["multiple", "checkbox"])) {
                                    foreach ($values as $optionValue)
                                        $paramOption[$optionId][] = $optionValue;
                                } elseif (in_array($optionType, ["radio", "drop_down", "area", "field"])) {
                                    $paramOption[$optionId] = $values;
                                } elseif ($optionType == "file") {
// downloading file /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                    $base64String = $productOptions[$optionId]["encodeImage"];
                                    $fileName     = time().$productOptions[$optionId]["name"];
                                    $fileType     = $productOptions[$optionId]["type"];
                                    $fileWithPath = $this->_helperCatalog->getBasePath().DS.$fileName;
                                    $ifp          = fopen($fileWithPath, "wb");
                                    fwrite($ifp, base64_decode($base64String));
// assigning file to option /////////////////////////////////////////////////////////////////////////////////////////////////////
                                    $fileOption = [
                                        "type"       => $fileType,
                                        "title"      => $fileName,
                                        "quote_path" => DS."media".DS.$fileName,
                                        "fullpath"   => $fileWithPath,
                                        "secret_key" => substr(md5(file_get_contents($fileWithPath)), 0, 20)
                                    ];
                                    $filesToDelete[]        = $fileWithPath;
                                    $paramOption[$optionId] = $fileOption;
                                } elseif ($optionType == "date") {
                                    $paramOption[$optionId]["month"]   = $values["month"];
                                    $paramOption[$optionId]["day"]     = $values["day"];
                                    $paramOption[$optionId]["year"]    = $values["year"];
                                } elseif ($optionType == "date_time") {
                                    $paramOption[$optionId]["month"]   = $values["month"];
                                    $paramOption[$optionId]["day"]     = $values["day"];
                                    $paramOption[$optionId]["year"]    = $values["year"];
                                    $paramOption[$optionId]["hour"]    = $values["hour"];
                                    $paramOption[$optionId]["minute"]  = $values["minute"];
                                    $paramOption[$optionId]["dayPart"] = $values["dayPart"];
                                } elseif ($optionType == "time") {
                                    $paramOption[$optionId]["hour"]    = $values["hour"];
                                    $paramOption[$optionId]["minute"]  = $values["minute"];
                                    $paramOption[$optionId]["dayPart"] = $values["dayPart"];
                                }
                            }
                        }
                        if ($product->getTypeId() == "downloadable") {
                            if (isset($params["links"]))
                                $request = ["links"=>$params["links"], "options"=>$paramOption, "product"=>$productId];
                            else
                                $request = ["options"=>$paramOption, "product"=>$productId];
                        } elseif ($product->getTypeId() == "grouped") {
                            if(isset($params["super_group"]))
                                $request = ["super_group"=>$params["super_group"], "product"=>$productId];
                        } elseif ($product->getTypeId() == "configurable") {
                            if(isset($params["super_attribute"]))
                                $request = ["super_attribute"=>$params["super_attribute"], "options"=>$paramOption, "product"=>$productId];
                        } elseif ($product->getTypeId() == "bundle") {
                            if(isset($params["bundle_option"]) && isset($params["bundle_option_qty"])){
                                $this->_coreRegistry->register("product", $product);
                                $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                                    $product->getTypeInstance(true)->getOptionsIds($product),
                                    $product
                                );
                                foreach ($selectionCollection as $option) {
                                    $selectionQty = $option->getSelectionQty() * 1;
                                    $key = $option->getOptionId();
                                    if (isset($params["bundle_option_qty"][$key]))
                                        $probablyRequestedQty = $params["bundle_option_qty"][$key];
                                    if ($selectionQty > 1)
                                        $requestedQty = $selectionQty * $qty;
                                    elseif (isset($probablyRequestedQty))
                                        $requestedQty = $probablyRequestedQty * $qty;
                                    else
                                        $requestedQty = 1;
                                    $associateBundleProduct = $this->_productFactory->create()->load($option->getProductId());
                                    $availableQty = $this->_stockRegistry->getStockItem($associateBundleProduct->getId())->getQty();
                                    if ($associateBundleProduct->getIsSalable()) {
                                        if ($requestedQty > $availableQty) {
                                            $returnArray["message"] = __("The requested quantity of ").$option->getName().__(" is not available");
                                            return $this->getJsonResponse($returnArray);
                                        }
                                    }
                                }
                                $request = ["bundle_option"=>$params["bundle_option"], "bundle_option_qty"=>$params["bundle_option_qty"], "options"=>$paramOption, "product"=>$productId];
                            }
                        } else {
                            $request = ["options"=>$paramOption, "product"=>$productId];
                        }
                        $request["qty"] = $qty;
                        $request = $this->_getProductRequest($request);
                        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL;
                        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);
                        if (!is_array($cartCandidates)) {
                            $cartCandidates = [$cartCandidates];
                        }
                        $parentItem = null;
                        $existingId = 0;
                        foreach ($cartCandidates as $candidate) {
                            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
                            $candidate->setStickWithinParent($stickWithinParent);
                            $item = $this->getItemByProduct($quote, $candidate);
                            if ($item)
                                $existingId = $item->getId();
                        }
                        $productAdded = null;
                        if ($existingId == 0) {
                            $productAdded = $quote->addProduct($product, $request)->save();
                        } else {
                            $quoteItem = $quote->getItemById($existingId);
                            $qty = $quoteItem->getQty()+(int)$qty;
                            $quoteItem->setQty($qty)->save();
                            $productAdded = "updated";
                        }
                        $allAdded     = true;
                        $allAvailable = true;
                        if (!empty($relatedProducts)) {
                            foreach ($relatedProducts as $productId) {
                                $productId = (int)$productId;
                                if (!$productId)
                                    continue;
                                $relatedProduct = $this->_productFactory->create()->setStoreId($storeId)->load($productId);
                                if ($relatedProduct->getId() && $relatedProduct->isVisibleInCatalog()) {
                                    try {
                                        $quote->addProduct($relatedProduct);
                                    } catch (\Exception $e) {
                                        $allAdded = false;
                                    }
                                } else {
                                    $allAvailable = false;
                                }
                            }
                        }
                        $quote->collectTotals()->save();
                        if (!$productAdded) {
                            $returnArray["message"] = __("Unable to add product to cart.");
                            return $this->getJsonResponse($returnArray);
                        } else {
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
                        $returnArray["message"] = html_entity_decode(__("%1 was added to your shopping cart", $this->_helperCatalog->stripTags($product->getName())));
                        if (!$allAvailable)
                            $returnArray["message"] .= __(" but, We don't have some of the products you want.");
                        if (!$allAdded)
                            $returnArray["message"] .= __(" but, We don't have as many of some products as you want.");
// delete files uploaded for custom option //////////////////////////////////////////////////////////////////////////////////////
                        foreach ($filesToDelete as $eachFile)
                            unlink($eachFile);
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
            } catch(\Exception $e)   {
                if($e->getMessage() != "")
                    $returnArray["message"] = $e->getMessage();
                else
                    $returnArray["message"] = __("Can't add the item to shopping cart.");
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

        protected function _getProductRequest($requestInfo)     {
            if ($requestInfo instanceof \Magento\Framework\DataObject) {
                $request = $requestInfo;
            } elseif (is_numeric($requestInfo)) {
                $request = new \Magento\Framework\DataObject(["qty"=>$requestInfo]);
            } elseif (is_array($requestInfo)) {
                $request = new \Magento\Framework\DataObject($requestInfo);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("We found an invalid request for adding product to quote."));
            }
            $this->getRequestInfoFilter()->filter($request);
            return $request;
        }

        protected function getRequestInfoFilter(){
            if ($this->_requestInfoFilter === null) {
                $this->_requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class);
            }
            return $this->_requestInfoFilter;
        }

        public function getItemByProduct($quote, $product) {
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->representProduct($product)) {
                    return $item;
                }
            }
            return false;
        }

    }