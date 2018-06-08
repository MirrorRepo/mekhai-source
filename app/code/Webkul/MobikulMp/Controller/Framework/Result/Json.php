<?php

    namespace Webkul\MobikulMp\Controller\Framework\Result;

    class Json extends \Magento\Framework\Controller\Result\Json    {

        public function getRawData()      {
            return $this->json;
        }

    }