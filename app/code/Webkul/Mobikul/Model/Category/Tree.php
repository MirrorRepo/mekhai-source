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

    namespace Webkul\Mobikul\Model\Category;

    class Tree extends \Magento\Catalog\Model\Category\Tree     {

        protected function getChildren($node, $depth, $currentLevel)    {
            if ($node->hasChildren()) {
                $children = [];
                foreach ($node->getChildren() as $child) {
                    if ($depth !== null && $depth <= $currentLevel)
                        break;
                    if($child->getIsActive())
                        $children[] = $this->getTree($child, $depth, $currentLevel + 1);
                }
                return $children;
            }
            return [];
        }

        public function getTree($node, $depth = null, $currentLevel = 0)    {
            $children = $this->getChildren($node, $depth, $currentLevel);
            $tree     = $this->treeFactory->create()
                ->setCategoryId($node->getId())
                ->setParentId($node->getParentId())
                ->setName($node->getName())
                ->setPosition($node->getPosition())
                ->setLevel($node->getLevel())
                ->setIsActive($node->getIsActive())
                ->setProductCount($node->getProductCount())
                ->setChildren($children);
            return $tree;
        }

        protected function prepareCollection()  {
            $storeId = $this->storeManager->getStore()->getId();
            $this->categoryCollection
                ->addAttributeToSelect("name")
                ->addAttributeToSelect("is_active")
                ->addAttributeToSelect("include_in_menu")
                ->addAttributeToFilter("include_in_menu", 1)
                ->addAttributeToFilter("is_active", 1)
                ->setProductStoreId($storeId)
                ->setLoadProductCount(true)
                ->setStoreId($storeId);
        }

    }