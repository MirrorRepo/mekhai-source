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

    namespace Webkul\Mobikul\Controller;
    define("DS", DIRECTORY_SEPARATOR);
    use Magento\Framework\Controller\ResultFactory;

    abstract class ApiController extends \Magento\Framework\App\Action\Action     {

        protected $_helper;

        public function __construct(
            \Webkul\Mobikul\Helper\Data $helper,
            \Magento\Framework\App\Action\Context $context
        ) {
            $this->_helper = $helper;
            parent::__construct($context);
        }

        protected function getJsonResponse($responseContent = []){
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($responseContent);
            return $resultJson;
        }

        public function canReorder(\Magento\Sales\Model\Order $order)   {
            if (!$this->_helper->getConfigData("sales/reorder/allow"))
                return 0;
            if (1)
                return $order->canReorder();
            else
                return 1;
        }

    }