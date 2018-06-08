<?php
    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @package   Webkul_MobikulMp
     * @author    Webkul
     * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     */

     namespace Webkul\MobikulMp\Controller\Product;

    class SaveAttribute extends AbstractProduct    {

        public function execute()   {
            $returnArray            = [];
            $returnArray["authKey"] = "";
            $returnArray["message"] = "";
            $returnArray["success"] = false;
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
                        $storeId       = $this->_helper->validate($wholeData, "storeId")       ? $wholeData["storeId"]       : 0;
                        $isRequired    = $this->_helper->validate($wholeData, "isRequired")    ? $wholeData["isRequired"]    : 0;
                        $attributeCode = $this->_helper->validate($wholeData, "attributeCode") ? $wholeData["attributeCode"] : "";
                        $attributeLabel = $this->_helper->validate($wholeData, "attributeLabel") ? $wholeData["attributeLabel"] : "";
                        $attributeType = $this->_helper->validate($wholeData, "attributeType") ? $wholeData["attributeType"] : "";
                        $attributeOption = $this->_helper->validate($wholeData, "attributeOption") ? $wholeData["attributeOption"] : "[]";
                        $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                        




                        // $wholedata = $this->getRequest()->getParams();

                        $attributes     = $this->_product->getAttributes();
                        $attributeCodes = [];
                        foreach ($attributes as $attribute)
                            $attributeCodes = $attribute->getEntityType()->getAttributeCodes();
                        if (count($attributeCodes) && in_array($attributeCode, $attributeCodes)) {
                            $returnArray["message"] = __("Attribute Code already exists");
                        } else {
                            $attributeOptionArray = [];
                            if (count($attributeOption) > 0) {
                                foreach ($attributeOption as $c) {
                                    $attributeOptionArray[".".$c["admin"]."."] = [$c["admin"],$c["store"]];
                                }
                            }
                            if ($attributeCode == "") {
                                $attributeCode = $this->generateAttrCode($attributeLabel);
                            }
                            if ($attributeCode) {
                                $validatorRegx = new \Zend_Validate_Regex(["pattern"=>"/^[a-z][a-z_0-9]{0,30}$/"]);
                                if (!$validatorRegx->isValid($attributeCode)) {
                                    $returnArray["message"] = __("Attribute code '%1' is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.", $attributeCode);
                                    $this->_emulate->stopEnvironmentEmulation($environment);
                                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
                            $attributeData = [
                                "apply_to"                      => 0,
                                "is_global"                     => 1,
                                "is_unique"                     => 0,
                                "is_required"                   => $isRequired,
                                "is_searchable"                 => 0,
                                "is_comparable"                 => 0,
                                "default_value"                 => "",
                                "is_filterable"                 => 0,
                                "attribute_code"                => $attributeCode,
                                "frontend_input"                => $attributeType,
                                "frontend_label"                => [$attributeLabel],
                                "is_configurable"               => 1,
                                "used_for_sort_by"              => 0,
                                "default_value_text"            => "",
                                "default_value_date"            => "",
                                "is_wysiwyg_enabled"            => 0,
                                "default_value_yesno"           => 0,
                                "is_visible_on_front"           => 0,
                                "default_value_textarea"        => "",
                                "is_used_for_price_rules"       => 0,
                                "used_in_product_listing"       => 0,
                                "is_filterable_in_search"       => 0,
                                "is_html_allowed_on_front"      => 1,
                                "is_visible_in_advanced_search" => 1
                            ];
                            $model = $this->_attributeModel;
                            if (($model->getIsUserDefined() === null) || $model->getIsUserDefined() != 0) {
                                $attributeData["backend_type"] = $model->getBackendTypeByInput($attributeData["frontend_input"]);
                            }
                            $model->addData($attributeData);
                            $data["option"]["value"] = $attributeOptionArray;
                            if ($attributeType == "select") {
                                $model->addData($data);
                            }
                            $entityTypeID = $this->_entityModel->setType("catalog_product")->getTypeId();
                            $model->setEntityTypeId($entityTypeID);
                            $model->setIsUserDefined(1);
                            $model->save();
                            $returnArray["message"] = __("Attribute Created Successfully");
                            $returnArray["success"] = true;
                        }
                        $this->_emulate->stopEnvironmentEmulation($environment);
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
            } catch (\Exception $e) {
                $returnArray["message"] = __($e->getMessage());
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

        protected function generateAttrCode($attributeLabel)    {
            $attributeLabelFormatUrlKey = $this->_productUrl->formatUrlKey($attributeLabel);
            $attributeCode = substr(preg_replace("/[^a-z_0-9]/", "_", $attributeLabelFormatUrlKey), 0, 30);
            $validatorAttrCode = new \Zend_Validate_Regex(["pattern"=>"/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/"]);
            if (!$validatorAttrCode->isValid($attributeCode))
                $attributeCode = "attr_".($attributeCode ?: substr(md5(time()), 0, 8));
            return $attributeCode;
        }

    }