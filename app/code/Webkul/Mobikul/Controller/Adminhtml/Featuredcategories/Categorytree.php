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

    namespace Webkul\Mobikul\Controller\Adminhtml\Featuredcategories;
    use Magento\Backend\App\Action\Context;

    class Categorytree extends \Magento\Backend\App\Action      {

        protected $_categoryRepository;
        protected $_categoryResourceModel;

        public function __construct(
            Context $context,
            \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
            \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
        ) {
            $this->_categoryRepository = $categoryRepository;
            $this->_categoryResourceModel = $categoryResourceModel;
            parent::__construct($context);
        }

        public function execute()   {
            $data = $this->getRequest()->getParams();
            try {
                $parentCategory = $this->_categoryRepository->get($data["parentCategoryId"]);
                $parentChildren = $parentCategory->getChildren();
                $parentChildIds = explode(",", $parentChildren);
                $index = 0;
                foreach ($parentChildIds as $parentChildId) {
                    $categoryData = $this->_categoryRepository->get($parentChildId);
                    if ($this->_categoryResourceModel->getChildrenCount($parentChildId) > 0)
                        $result[$index]["counting"] = 1;
                    else
                        $result[$index]["counting"] = 0;
                    $result[$index]["id"]   = $categoryData["entity_id"];
                    $result[$index]["name"] = $categoryData->getName();
                    $categories             = [];
                    $categoryIds            = "";
                    if (isset($data["categoryIds"])) {
                        $categories  = explode(",", $data["categoryIds"]);
                        $categoryIds = $data["categoryIds"];
                    }
                    if ($categoryIds && in_array($categoryData["entity_id"], $categories))
                        $result[$index]["check"] = 1;
                    else
                        $result[$index]["check"] = 0;
                    ++$index;
                }
                $this->getResponse()->representJson($this->_objectManager->get("Magento\Framework\Json\Helper\Data")->jsonEncode($result));
            } catch (\Exception $e) {
                $this->getResponse()->representJson($this->_objectManager->get("Magento\Framework\Json\Helper\Data")->jsonEncode(""));
            }
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::featuredcategories");
        }

    }