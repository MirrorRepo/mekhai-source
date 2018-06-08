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

    namespace Webkul\Mobikul\Model\Featuredcategories\Source;
    use Magento\Framework\Data\OptionSourceInterface;

    class Status implements OptionSourceInterface   {

        protected $_mobikulFeaturedcategories;

        public function __construct(\Webkul\Mobikul\Model\Featuredcategories $mobikulFeaturedcategories) {
            $this->_mobikulFeaturedcategories = $mobikulFeaturedcategories;
        }

        public function toOptionArray()     {
            $availableOptions = $this->_mobikulFeaturedcategories->getAvailableStatuses();
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