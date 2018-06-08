<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpfreeshipping
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpfreeshipping\Plugin\Helper;

class Data
{
    /**
     * function to run to change the return data of afterIsSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['freeshipping/shipping/view'] = 'freeshipping/shipping/index';
        return $result;
    }
}