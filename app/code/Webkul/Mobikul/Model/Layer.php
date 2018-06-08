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

    class Layer extends \Magento\Catalog\Model\Layer    {

        public $_customCollection;

        public function getProductCollection(){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $wholeData     = $objectManager->create("\Magento\Framework\App\Request\Http")->getPostValue();
            if(isset($wholeData["custom"]) && $wholeData["customCollection"] == 1){
                $this->prepareProductCollection($this->_customCollection);
                $this->_productCollections[$this->getCurrentCategory()->getId()] = $this->_customCollection;
                return $this->_customCollection;
            }
            else{
                if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
                    $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
                } else {
                    $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
                    $this->prepareProductCollection($collection);
                    $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
                }
                return $collection;
            }
        }

    }