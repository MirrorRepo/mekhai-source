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

    namespace Webkul\Mobikul\Controller\Checkout;

    class ChangeOrderStatus extends AbstractCheckout    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["success"]      = false;
            $returnArray["responseCode"] = 0;
            try {
                $wholeData       = $this->getRequest()->getPostValue();
                $this->_headers  = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $status      = $this->_helper->validate($wholeData, "status")      ? $wholeData["status"]      : 0;
                        $confirm     = $this->_helper->validate($wholeData, "confirm")     ? $wholeData["confirm"]     : "{}";
                        $storeId     = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId")  ? $wholeData["customerId"]  : 0;
                        $incrementId = $this->_helper->validate($wholeData, "incrementId") ? $wholeData["incrementId"] : "";
                        $confirm     = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($confirm);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $order       = $this->_orderFactory->create()->loadByIncrementId($incrementId);
                        $payment     = $order->getPayment();
                        $payment->setTransactionId($confirm["response"]["id"])
                            ->setPreparedMessage("status : ".$confirm["response"]["state"])
                            ->setShouldCloseParentTransaction(true)
                            ->setIsTransactionClosed(0)
                            ->registerCaptureNotification($order->getGrandTotal());
                        $order->save();
                        $state = "";
                        if ($status == 0) {
                            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                                ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                                ->save();
                            $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
                        } else {
                            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)
                                ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED)
                                ->save();
                            $state = \Magento\Sales\Model\Order::STATE_CANCELED;
                        }
                        if ($order->canInvoice()) {
                            $invoice = $this->_objectManager->create("Magento\Sales\Model\Service\InvoiceService")->prepareInvoice($order);
                            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                            $invoice->register();
                            $transactionSave = $this->_objectManager->create("\Magento\Framework\DB\Transaction")->addObject($invoice)->addObject($invoice->getOrder());
                            $transactionSave->save();
                            $this->_invoiceSender->send($invoice);
                        }
                        $comment = "status :".$confirm["response"]["state"]."<br>";
                        $comment .= "transaction id :".$confirm["response"]["id"]."<br>";
                        $comment .= "date :".$confirm["response"]["create_time"]."<br>";
                        $comment .= "from :".$confirm["client"]["product_name"]."<br>";
                        $order->addStatusHistoryComment($comment)->setIsCustomerNotified(true);
                        $order->save();
                        $returnArray["success"] = true;
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = __("Invalid Request");
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch(Exception $e)   {
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }