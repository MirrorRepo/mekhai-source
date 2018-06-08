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

    namespace Webkul\Mobikul\Block;

    class Configurable extends \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable     {

        public function getJsonConfig()     {
            $objectManager         = \Magento\Framework\App\ObjectManager::getInstance();
            $store                 = $this->getCurrentStore();
            $product               = $this->getProduct();
            $swatchBlock           = $objectManager->create("\Magento\Swatches\Block\Product\Renderer\Configurable");
            $swatchBlock->setProduct($product);
            $regularProductPrice   = $product->getPriceInfo()->getPrice("regular_price");
            $finalProductPrice     = $product->getPriceInfo()->getPrice("final_price");
            $productOptions        = $this->helper->getOptions($product, $this->getAllowProducts());
            $attributesData        = $objectManager->create("\Webkul\Mobikul\Model\ConfigurableProduct\ConfigurableAttributeData")->getAttributesData($product, $productOptions);
            $costomizedAttr        = [];
            $customizedIndex       = [];
            $customizeOptionPrizes = [];
            $optionPrizes          = $this->getOptionPrices();
            if (count($attributesData["attributes"]) > 0) {
                foreach ($attributesData["attributes"] as $value)
                    $costomizedAttr[] = $value;
            }
            if (isset($productOptions["index"])) {
                foreach ($productOptions["index"] as $index => $indexValue) {
                    $indexValue["product"] = $index;
                    $customizedIndex[]     = $indexValue;
                }
            }
            if (isset($optionPrizes)) {
                foreach ($optionPrizes as $index => $optionPrice) {
                    $optionPrice["product"]  = $index;
                    $customizeOptionPrizes[] = $optionPrice;
                }
            }
            $optionPrizes                 = $customizeOptionPrizes;
            $productOptions["index"]      = $customizedIndex;
            $attributesData["attributes"] = $costomizedAttr;
            $jsonHelper                   = $objectManager->create("Magento\Framework\Json\Helper\Data");
            $images                       = isset($productOptions["images"]) ? $productOptions["images"] : [];
            $index                        = isset($productOptions["index"])  ? $productOptions["index"]  : [];
            $images                       = $jsonHelper->jsonEncode($images);
            $index                        = $jsonHelper->jsonEncode($index);
            $config                       = [
                "attributes"     => $attributesData["attributes"],
                "template"       => str_replace("%s", "<%- data.price %>", $store->getCurrentCurrency()->getOutputFormat()),
                "optionPrices"   => $optionPrizes,
                "prices"         => [
                    "oldPrice"   => ["amount"=>$this->_registerJsPrice($regularProductPrice->getAmount()->getValue())],
                    "basePrice"  => ["amount"=>$this->_registerJsPrice($finalProductPrice->getAmount()->getBaseAmount())],
                    "finalPrice" => ["amount"=>$this->_registerJsPrice($finalProductPrice->getAmount()->getValue())]
                ],
                "productId"      => $product->getId(),
                "chooseText"     => __("Choose an Option..."),
                "images"         => $images,
                "index"          => $index,
                "swatchData"     => $swatchBlock->getJsonSwatchConfig()
            ];
            if ($product->hasPreconfiguredValues() && !empty($attributesData["defaultValues"]))
                $config["defaultValues"] = $attributesData["defaultValues"];
            $config = array_merge($config, $this->_getAdditionalConfig());
            return $config;
        }

    }