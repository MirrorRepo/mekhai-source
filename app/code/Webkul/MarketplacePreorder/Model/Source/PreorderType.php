<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplacePreorder\Model\Source;

/**
 * Used in seller configuration.
 */
class PreorderType
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
            ['value'=>'0', 'label' => _("Complete Payment")],
            ['value'=>'1', 'label' => __("Percent Payment")],
        ];

        return $data;
    }
}
