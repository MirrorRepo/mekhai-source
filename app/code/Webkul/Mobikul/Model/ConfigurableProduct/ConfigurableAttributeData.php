<?php
    namespace Webkul\Mobikul\Model\ConfigurableProduct;

    use Magento\Catalog\Model\Product;
    use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

    class ConfigurableAttributeData  extends \Magento\ConfigurableProduct\Model\ConfigurableAttributeData  {

        public function getAttributesData(Product $product, array $options = [])    {
            $attributes    = [];
            $defaultValues = [];
            foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
                $attributeOptionsData = $this->getAttributeOptionsData($attribute, $options);
                if ($attributeOptionsData) {
                    $swatchType       = "";
                    $objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
                    $productAttribute = $attribute->getProductAttribute();
                    if($this->isJson($productAttribute->getAdditionalData())) {
                        $swatchInputType = $objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($productAttribute->getAdditionalData());
                        if($swatchInputType["swatch_input_type"] != "")
                            $swatchType  = $swatchInputType["swatch_input_type"];
                    }
                    $updateProductPreviewImage     = false;
                    if((bool)$productAttribute->getUpdateProductPreviewImage())
                        $updateProductPreviewImage = (bool)$productAttribute->getUpdateProductPreviewImage();
                    $attributeId                   = $productAttribute->getId();
                    $attributes[$attributeId]      = [
                        "id"                        => $attributeId,
                        "code"                      => $productAttribute->getAttributeCode(),
                        "label"                     => $productAttribute->getStoreLabel($product->getStoreId()),
                        "options"                   => $attributeOptionsData,
                        "position"                  => $attribute->getPosition(),
                        "swatchType"                => $swatchType,
                        "updateProductPreviewImage" => $updateProductPreviewImage
                    ];
                    $defaultValues[$attributeId] = $this->getAttributeConfigValue($attributeId, $product);
                }
            }
            return [
                "attributes"    => $attributes,
                "defaultValues" => $defaultValues,
            ];
        }

        public function isJson($string) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

    }