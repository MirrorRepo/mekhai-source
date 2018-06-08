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

    namespace Webkul\Mobikul\Model;

    class Theme implements \Magento\Framework\Option\ArrayInterface     {
        
        public function toOptionArray()    {
            $data = array();
            $data[] = array("value"=>1, "label"=>"red-green");
            $data[] = array("value"=>2, "label"=>"light green");
            $data[] = array("value"=>3, "label"=>"deep purple-pink");
            $data[] = array("value"=>4, "label"=>"blue-orange");
            $data[] = array("value"=>5, "label"=>"light blue-red");
            return  $data;
        }

    }