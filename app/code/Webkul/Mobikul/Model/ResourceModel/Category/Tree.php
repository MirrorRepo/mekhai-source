<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

    namespace Webkul\Mobikul\Model\ResourceModel\Category;

    class Tree extends \Magento\Catalog\Model\ResourceModel\Category\Tree    {

        public function addCollectionData($collection = null, $sorted = false, $exclude = [], $toLoad = true, $onlyActive = false) {
            if ($collection === null) {
                $collection = $this->getCollection($sorted);
            } else {
                $this->setCollection($collection);
            }
            if (!is_array($exclude)) {
                $exclude = [$exclude];
            }
            $nodeIds = [];
            foreach ($this->getNodes() as $node) {
                if (!in_array($node->getId(), $exclude)) {
                    $nodeIds[] = $node->getId();
                }
            }
            $collection->addIdFilter($nodeIds);
            if ($onlyActive) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                if($objectManager->get("\Magento\Framework\App\Request\Http")->getModuleName() != "mobikul"){
                    $disabledIds = $this->_getDisabledIds($collection, $nodeIds);
                    if ($disabledIds) {
                        $collection->addFieldToFilter('entity_id', ['nin' => $disabledIds]);
                    }
                    $collection->addAttributeToFilter('is_active', 1);
                    $collection->addAttributeToFilter('include_in_menu', 1);
                }
            }
            if ($this->_joinUrlRewriteIntoCollection) {
                $collection->joinUrlRewrite();
                $this->_joinUrlRewriteIntoCollection = false;
            }
            if ($toLoad) {
                $collection->load();
                foreach ($collection as $category) {
                    if ($this->getNodeById($category->getId())) {
                        $this->getNodeById($category->getId())->addData($category->getData());
                    }
                }
                foreach ($this->getNodes() as $node) {
                    if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                        $this->removeNode($node);
                    }
                }
            }
            return $this;
        }

    }