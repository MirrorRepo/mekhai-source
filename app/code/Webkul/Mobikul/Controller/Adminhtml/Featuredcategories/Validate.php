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

    class Validate extends \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories  {

        protected function _validateFeaturedcategories($response)   {
            $featuredcategories = null;
            $errors             = [];
            try {
                $featuredcategories = $this->featuredcategoriesDataFactory->create();
                $data               = $this->getRequest()->getParams();
                $dataResult         = $data["mobikul_featuredcategories"];
                $errors             = [];
                if (!isset($dataResult["filename"][0]["name"]))
                    $errors[] =  __("Please upload featuredcategories image.");
                if (isset($dataResult["sort_order"])) {
                    if (!is_numeric($dataResult["sort_order"]))
                        $errors[] =  __("Sort order should be a number.");
                } else {
                    $errors[] =  __("Sort order field can not be blank.");
                }
                if (isset($dataResult["category_id"])) {
                    if ($dataResult["category_id"]) {
                        try {
                            $this->categoryRepositoryInterface->get($dataResult["category_id"]);
                        } catch (\Exception $exception) {
                            $errors[] =  __("Requested category doesn't exist");
                        }
                    }
                } else {
                    $errors[] =  __("Category id should be set.");
                }
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $exceptionMsg = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
                foreach ($exceptionMsg as $error)
                    $errors[] = $error->getText();
            }
            if ($errors) {
                $messages = $response->hasMessages() ? $response->getMessages() : [];
                foreach ($errors as $error)
                    $messages[] = $error;
                $response->setMessages($messages);
                $response->setError(1);
            }
            return $featuredcategories;
        }

        public function execute()   {
            $response = new \Magento\Framework\DataObject();
            $response->setError(0);
            $featuredcategories = $this->_validateFeaturedcategories($response);
            $resultJson = $this->resultJsonFactory->create();
            if ($response->getError()) {
                $response->setError(true);
                $response->setMessages($response->getMessages());
            }
            $resultJson->setData($response);
            return $resultJson;
        }

    }