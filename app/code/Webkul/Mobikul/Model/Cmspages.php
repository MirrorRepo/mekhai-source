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
    use Magento\Framework\Data\OptionSourceInterface;
    use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsCollection;

    class Cmspages implements OptionSourceInterface   {

        public function __construct(
            CmsCollection $collection
        ) {
            $this->_cmsPageCollection = $collection;
        }

        public function toOptionArray()     {
            $collection = $this->_cmsPageCollection
                ->create()
                ->addFieldToFilter("is_active", 1)
                ->addFieldToFilter("identifier", ["nin"=>["no-route", "enable-cookies"]]);
            $returnData = array();
            foreach ($collection as $cms) {
                $returnData[] =  array(
                    "value" => $cms->getId(),
                    "label" => $cms->getTitle()
                );
            }
            return $returnData;
        }

    }