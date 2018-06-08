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

    namespace Webkul\Mobikul\Model\Notification\Source;
    use Magento\Framework\Data\OptionSourceInterface;

    class Type implements OptionSourceInterface     {

        protected $_mobikulNotification;

        public function __construct(\Webkul\Mobikul\Model\Notification $mobikulNotification) {
            $this->_mobikulNotification = $mobikulNotification;
        }

        public function toOptionArray()     {
            $availableOptions = $this->_mobikulNotification->getAvailableTypes();
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