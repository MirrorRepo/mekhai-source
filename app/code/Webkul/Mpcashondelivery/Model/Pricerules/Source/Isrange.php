<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpcashondelivery\Model\Pricerules\Source;

class Isrange implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_pricerules;

    /**
     * @param \Webkul\Mpcashondelivery\Model\Pricerules $pricerules
     */
    public function __construct(\Webkul\Mpcashondelivery\Model\Pricerules $pricerules)
    {
        $this->_pricerules = $pricerules;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    public static function getOptionArray()
    {
        return [1 => __('Specific'), 0 => __('Range')];
    }
}
