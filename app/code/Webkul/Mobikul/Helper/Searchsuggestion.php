<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Helper;

    class Searchsuggestion extends \Magento\Framework\App\Helper\AbstractHelper     {

        protected $_helper;
        protected $_localeDate;

        public function __construct(
            \Webkul\Mobikul\Helper\Data $helper,
            \Magento\Framework\App\Helper\Context $context,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
        ) {
            $this->_helper     = $helper;
            $this->_localeDate = $localeDate;
            parent::__construct($context);
        }

        public function isOnSale($product)  {
            $specialPrice = number_format($product->getFinalPrice(), 2);
            $regularPrice = number_format($product->getPrice(), 2);
            if ($specialPrice != $regularPrice)
                return $this->_nowIsBetween($product->getData("special_from_date"), $product->getData("special_to_date"));
            else
                return false;
        }

        protected function _nowIsBetween($fromDate, $toDate)    {
            if ($fromDate)  {
                $fromDate = strtotime($fromDate);
                $toDate   = strtotime($toDate);
                $now      = strtotime($this->_localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s"));
                if ($toDate)    {
                    if ($fromDate <= $now && $now <= $toDate)
                        return true;
                }
                else    {
                    if ($fromDate <= $now)
                        return true;
                }
            }
            return false;
        }

        public function matchString($term, $tagName)    {
            $str      = "";
            $len      = strlen($term);
            $term1    = strtolower($term);
            $tagName1 = strtolower($tagName);
            $pos      = strpos($tagName1, $term1);
            for($i=0; $i<$len; $i++) {
                $j = $pos+$i;
                $subTerm  = substr($term, $i, 1);
                $subTerm1 = strtolower($subTerm);
                $subTerm2 = strtoupper($subTerm);
                $subName  = substr($tagName, $j, 1);
                if ($subTerm1 == $subName)
                    $str .= $subTerm1;
                elseif ($subTerm2 == $subName)
                    $str .= $subTerm2;
            }
            return($str);
        }

        public function getBoldName($tagName, $str, $term)  {
            $len = strlen($term);
            if(strlen($str) >= $len)
                $tagName = str_replace($str, "<b>".$str."</b>", $tagName);
            return($tagName);
        }

        public function displayTags()   {
            return (bool)$this->_helper->getConfigData("mobikul/searchsuggestion/displaytag");
        }

        public function displayProducts() {
            return (bool)$this->_helper->getConfigData("mobikul/searchsuggestion/displayproduct");
        }

        public function getNumberOfTags()   {
            return (int) $this->_helper->getConfigData("mobikul/searchsuggestion/tagcount");
        }

        public function getNumberOfProducts()   {
            return (int) $this->_helper->getConfigData("mobikul/searchsuggestion/productcount");
        }

    }