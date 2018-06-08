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

    namespace Webkul\Mobikul\Model\Bannerimage\Source;
    use Magento\Framework\Data\OptionSourceInterface;

    class Type implements OptionSourceInterface     {

        protected $_mobikulBannerimage;

        public function __construct(\Webkul\Mobikul\Model\Bannerimage $mobikulBannerimage) {
            $this->_mobikulBannerimage = $mobikulBannerimage;
        }

        public function toOptionArray()     {
            $availableOptions = $this->_mobikulBannerimage->getAvailableTypes();
            $options = [];
            foreach ($availableOptions as $key => $value) {
                $options[] = [
                    "label" => $value,
                    "value" => $key
                ];
            }
            return $options;
        }

    }