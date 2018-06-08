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

    class UpdateItemOptions extends AbstractCheckout    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["success"]      = false;
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
                        $itemId          = $this->_helper->validate($wholeData, "itemId")          ? $wholeData["itemId"]          : 0;
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
                        if ($customerId != 0) {
                            $quoteCollection = $this->_quoteFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if ($quoteId != 0)
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        $quoteItem = $quote->getItemById($itemId);
                        if (!$quoteItem)
                            throw new \Magento\Framework\Exception\LocalizedException(__("We can't find the quote item."));
                        $product = $this->_objectManager->create("\Magento\Catalog\Model\Product")->load($productId);
                        $paramOption = [];
                        if (isset($params["options"])) {
                            $productOptions = $params["options"];
                            foreach ($productOptions as $optionId=>$values) {
                                $option     = $this->_objectManager->create("\Magento\Catalog\Model\Product\Option")->load($optionId);
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
                                } elseif ($_optionType == "date") {
                                    $paramOption[$optionId]["month"]   = $values["month"];
                                    $paramOption[$optionId]["day"]     = $values["day"];
                                    $paramOption[$optionId]["year"]    = $values["year"];
                                } elseif ($_optionType == "date_time") {
                                    $paramOption[$optionId]["month"]   = $values["month"];
                                    $paramOption[$optionId]["day"]     = $values["day"];
                                    $paramOption[$optionId]["year"]    = $values["year"];
                                    $paramOption[$optionId]["hour"]    = $values["hour"];
                                    $paramOption[$optionId]["minute"]  = $values["minute"];
                                    $paramOption[$optionId]["dayPart"] = $values["dayPart"];
                                } elseif ($_optionType == "time") {
                                    $paramOption[$optionId]["hour"]    = $values["hour"];
                                    $paramOption[$optionId]["minute"]  = $values["minute"];
                                    $paramOption[$optionId]["dayPart"] = $values["dayPart"];
                                }
                            }
                        }
                        if(count($relatedProducts) == 0)
                            $relatedProducts = null;
                        if ($product->getTypeId() == "downloadable") {
                            if (isset($params))
                                $params = ["related_product"=>$relatedProducts, "links"=>$params["links"], "options"=>$paramOption, "qty"=>$qty, "product"=>$productId];
                            else
                                $params = ["related_product"=>$relatedProducts, "options"=>$paramOption, "qty"=>$qty, "product"=>$productId];
                        } elseif ($product->getTypeId() == "grouped") {
                            if(isset($params["super_group"]))
                                $params = ["related_product"=>$relatedProducts, "super_group"=>$params["super_group"], "product"=>$productId];
                        } elseif ($product->getTypeId() == "configurable") {
                            if(isset($params["super_attribute"]))
                                $params = ["related_product"=>$relatedProducts, "super_attribute"=>$params["super_attribute"], "options"=>$paramOption, "qty"=>$qty, "product_id"=>$productId];
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
                                $params = ["related_product"=>$relatedProducts, "bundle_option"=>$params["bundle_option"], "bundle_option_qty"=>$params["bundle_option_qty"], "options"=>$paramOption, "qty"=>$qty, "product_id"=>$productId];
                            }
                        } else {
                            $params = ["related_product"=>$relatedProducts, "options"=>$paramOption, "qty"=>$qty, "product"=>$productId];
                        }
                        $item = $quote->updateItem($itemId, new \Magento\Framework\DataObject($params));
                        if (is_string($item))
                            throw new \Magento\Framework\Exception\LocalizedException(__($item));
                        if ($item->getHasError())
                            throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
                        $item->setItemId($itemId)->save();
                        if (!$quote->getHasError()) {
                            $returnArray["success"] = true;
                            $returnArray["message"] = __(
                                "%1 was updated in your shopping cart.",
                                $this->_objectManager->get("Magento\Framework\Escaper")->escapeHtml($item->getProduct()->getName())
                            );
                        }
                        $quote = $this->_objectManager->get("\Magento\Quote\Model\QuoteFactory")->create()->setStoreId($storeId)->load($quoteId);
                        $returnArray["cartCount"] = $quote->getItemsQty() * 1;
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
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $returnArray["message"] = implode(", ", $messages);
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            } catch (\Exception $e) {
                $returnArray["message"] = __("We can't update the item right now.");
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }