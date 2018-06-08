<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpcashondelivery\Model\Total;

class Invoicetotal extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Collect invoice tax amount.
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     *
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $mpcod = 0;
        $method = $order->getPayment()->getMethod();
        if ($method == 'mpcashondelivery') {
            $balance = $this->getCodPaymentAmount($order->getId());
            if ($balance==0) {
                $balance = $order->getMpcashondelivery();
            }
        } else {
            $balance = 0;
        }
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ((double) $previousInvoice->getMpcashondelivery() && !$previousInvoice->isCanceled()) {
                $mpcod = $mpcod + $previousInvoice->getMpcashondelivery();
            }
            if ($mpcod==$order->getMpcashondelivery()) {
                return $this;
            }
        }

        $invoice->setMpcashondelivery($balance);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $balance);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $balance);

        return $this;
    }

    // get Cod amount by saleslist tabel
    public function getCodPaymentAmount($orderId)
    {
        $codCharges = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Webkul\Mpcashondelivery\Helper\Data');
        $customerId = $helper->getCustomerId();
        if ($customerId=='' || $customerId==0) {
            $salesList = $objectManager->create('Webkul\Marketplace\Model\Saleslist')
                        ->getCollection()
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter('order_id', $orderId);
            if (count($salesList)) {
                foreach ($salesList as $value) {
                    $codCharges = $codCharges + $value->getCodCharges();
                }
            }
        }

        return $codCharges;
    }
}
