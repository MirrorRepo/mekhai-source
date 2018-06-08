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

    namespace Webkul\Mobikul\Model\Bannerimage;
    use Webkul\Mobikul\Api\Data\BannerimageInterface;
    use Magento\Framework\Api\ExtensibleDataObjectConverter;
    use Magento\Framework\Convert\ConvertArray;

    class Mapper    {

        private $extensibleDataObjectConverter;

        public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)   {
            $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        }

        public function toFlatArray(BannerimageInterface $bannerimage)  {
            $flatArray = $this->extensibleDataObjectConverter->toNestedArray($bannerimage, [], "\Webkul\Mobikul\Api\Data\BannerimageInterface");
            return ConvertArray::toFlatArray($flatArray);
        }

    }