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

    namespace Webkul\Mobikul\Controller\Catalog;

    class AddToWishlist extends AbstractCatalog    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["itemId"]       = 0;
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
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
                        $params      = $this->_helper->validate($wholeData, "params")     ? $wholeData["params"]     : "[]";
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $productId   = $this->_helper->validate($wholeData, "productId")  ? $wholeData["productId"]  : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $params      = $this->_jsonHelper->jsonDecode($params);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
// Loading wishlist of customer ///////////////////////////////////////////////////////////////////////////////////////
                        $wishlist    = $this->_wishlist->create()->loadByCustomerId($customerId, true);
                        $product     = $this->_productFactory->create()->load($productId);
                        $paramOptionsArray = [];$paramOption = [];
                        if(isset($params["options"]))   {
                            $productOptions = $params["options"];
                            foreach($productOptions as $optionId=>$values) {
                                $option = $this->_productOption->load($optionId);
                                $optionType = $option->getType();
                                if(in_array($optionType, ["multiple", "checkbox"])) {
                                    foreach($values as $optionValue)
                                        $paramOption[$optionId][] = $optionValue;
                                } elseif (in_array($optionType, ["radio", "drop_down", "area", "field"])) {
                                    $paramOption[$optionId] = $values;
                                } elseif ($optionType == "file") {
// downloading file /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                    $base64String = $productOptions["optionId"]["encodeImage"];
                                    $fileName     = time().$productOptions["optionId"]["name"];
                                    $fileType     = $productOptions["optionId"]["type"];
                                    $fileWithPath = $this->_dir->getPath("media").DS.$fileName;
                                    $ifp          = fopen($fileWithPath, "wb");
                                    fwrite($ifp, base64_decode($base64String));
// assigning file to option /////////////////////////////////////////////////////////////////////////////////////////////////////
                                    $fileOption = array(
                                        "type"       => $fileType,
                                        "title"      => $fileName,
                                        "quote_path" => DS."media".DS.$fileName,
                                        "fullpath"   => $fileWithPath,
                                        "secret_key" => substr(md5(file_get_contents($fileWithPath)), 0, 20)
                                    );
                                    $filesToDelete[]        = $fileWithPath;
                                    $paramOption[$optionId] = $fileOption;
                                } elseif ($optionType == "date") {
                                    $paramOption[$optionId]["month"]    = $values["month"];
                                    $paramOption[$optionId]["day"]      = $values["day"];
                                    $paramOption[$optionId]["year"]     = $values["year"];
                                } elseif ($optionType == "date_time") {
                                    $paramOption[$optionId]["month"]    = $values["month"];
                                    $paramOption[$optionId]["day"]      = $values["day"];
                                    $paramOption[$optionId]["year"]     = $values["year"];
                                    $paramOption[$optionId]["hour"]     = $values["hour"];
                                    $paramOption[$optionId]["minute"]   = $values["minute"];
                                    $paramOption[$optionId]["day_part"] = $values["day_part"];
                                } elseif ($optionType == "time") {
                                    $paramOption[$optionId]["hour"]     = $values["hour"];
                                    $paramOption[$optionId]["minute"]   = $values["minute"];
                                    $paramOption[$optionId]["day_part"] = $values["day_part"];
                                }
                            }
                            if(count($paramOption) > 0)
                                $paramOptionsArray["options"] = $paramOption;
                        }
                        if($product->getTypeId() == "downloadable") {
                            if(isset($params["links"]))
                                $paramOptionsArray["links"] = $params["links"];
                        } elseif ($product->getTypeId() == "grouped") {
                            if(isset($params["super_group"]))
                                $paramOptionsArray["super_group"] = $params["super_group"];
                        } elseif ($product->getTypeId() == "configurable") {
                            if(isset($params["super_attribute"]))
                                $paramOptionsArray["super_attribute"] = $params["super_attribute"];
                        } elseif ($product->getTypeId() == "bundle") {
                            if(isset($params["bundle_option"]) && isset($params["bundle_option_qty"]))  {
                                $paramOptionsArray["bundle_option"]     = $params["bundle_option"];
                                $paramOptionsArray["bundle_option_qty"] = $params["bundle_option_qty"];
                            }
                        }
                        if (count($paramOptionsArray) > 0)
                            $buyRequest = new \Magento\Framework\DataObject($paramOptionsArray);
                        else
                            $buyRequest = new \Magento\Framework\DataObject();
                        if (!$product->getId() || !$product->isVisibleInCatalog()) {
                            $returnArray["message"] = __("Cannot specify product.");
                            $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
                            return $this->getJsonResponse($returnArray);
                        }
                        $result = $wishlist->addNewItem($product, $buyRequest);
                        if (is_string($result))
                            throw new \Magento\Framework\Exception\LocalizedException(__($result));
                        else
                           $returnArray["itemId"] = (int)$result->getId();
                        $wishlist->save();
                        $this->_eventManager->dispatch("wishlist_add_product", ["wishlist"=>$wishlist, "product"=>$product, "item"=>$result]);
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $returnArray["success"] = true;
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray["message"]      = $authData["message"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    }
                } else {
                    $returnArray["message"]      = __("Invalid Request");
                    $returnArray["responseCode"] = 0;
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch (Mage_Core_Exception $e) {
                $returnArray["message"] = __("An error occurred while adding item to wishlist: ").$e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            } catch (\Exception $e) {
                $returnArray["message"] = __($e->getMessage());
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }