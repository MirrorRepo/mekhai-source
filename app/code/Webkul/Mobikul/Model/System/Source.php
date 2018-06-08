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

    namespace Webkul\Mobikul\Model\System;
    use Magento\Framework\Data\OptionSourceInterface;

    class Source implements OptionSourceInterface   {

        public function toOptionArray()     {
            $options = [];
            array_push($options, ["value"=>1, "label"=>"Red-Green"]);
            array_push($options, ["value"=>2, "label"=>"Light Green"]);
            array_push($options, ["value"=>3, "label"=>"Deep Purple-Pink"]);
            array_push($options, ["value"=>4, "label"=>"Blue-Orange"]);
            array_push($options, ["value"=>5, "label"=>"Light Blue-Red"]);
            return $options;
        }

    }