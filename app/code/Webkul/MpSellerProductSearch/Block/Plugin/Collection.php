<?php
/**
* Webkul Software
* @category Webkul
* @package Webkul_MpSellerProductSearch
* @author Webkul
* @copyright Copyright (c) 2010-2016 Webkul Software Private Limited(https://webkul.com)
* @license https://store.webkul.com/license.html
*/

namespace Webkul\MpSellerProductSearch\Block\Plugin;

class Collection
{
    public function after_getProductCollection(\Webkul\Marketplace\Block\Collection $collection, $result)
    {
        $productname = $collection->getRequest()->getParam('name');
        if ($productname !="") {
             $result->addAttributeToFilter(
                 'name',
                 ['like' => '%'.$productname.'%']
             );
        }
        return $result;
    }
}
