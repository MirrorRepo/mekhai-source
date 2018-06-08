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

    namespace Webkul\Mobikul\Controller\Adminhtml\Categoryimages;

    class Validate extends \Webkul\Mobikul\Controller\Adminhtml\Categoryimages  {

        protected function _validateCategoryimages($response)   {
            $categoryimages = null;
            $errors         = [];
            try {
                $categoryimages = $this->_categoryimagesDataFactory->create();
                $data           = $this->getRequest()->getParams();
                $dataResult     = $data["mobikul_categoryimages"];
                $errors         = [];
                if (!isset($dataResult["icon"][0]["name"]))
                    $errors[] = __("Please upload category icon image.");
                if (!isset($dataResult["banner"][0]["name"]))
                    $errors[] = __("Please upload category banner image.");
                if (isset($dataResult["category_id"])) {
                    if ($dataResult["category_id"]) {
                        try {
                            $this->_categoryRepository->get($dataResult["category_id"]);
                        } catch (\Exception $exception) {
                            $errors[] = __("Requested category doesn't exist");
                        }
                    }
                } else {
                    $errors[] = __("Category id should be set.");
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
            return $categoryimages;
        }

        public function execute()   {
            $response = new \Magento\Framework\DataObject();
            $response->setError(0);
            $categoryimages = $this->_validateCategoryimages($response);
            $resultJson = $this->_resultJsonFactory->create();
            if ($response->getError()) {
                $response->setError(true);
                $response->setMessages($response->getMessages());
            }
            $resultJson->setData($response);
            return $resultJson;
        }

    }