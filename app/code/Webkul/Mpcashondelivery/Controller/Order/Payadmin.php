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
namespace Webkul\Mpcashondelivery\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

/**
 * Class Payadmin.
 */
class Payadmin extends Action
{
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;
    /**
     * @var templateId
     */
    protected $_tempId;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @param Context                                            $context
     * @param Session                                            $customerSession
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_mpHelper = $mpHelper;
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    protected function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->_tempId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name']);

        return $this;
    }

    protected function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $wholedata = $this->getRequest()->getParams();
            $actparterprocost = 0;
            $totalamount = 0;
            $adminCommision = 0;
            $totaltax = 0;
            $orderId = $wholedata['id'];
            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
            $orderinfo = '';
            $sellerId = $this->getCustomerId();
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                        ->getCollection()
                                        ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                                        ->addFieldToFilter('order_id', ['eq' => $orderId])
                                        ->addFieldToFilter('collect_cod_status', ['eq' => 1])
                                        ->addFieldToFilter('admin_pay_status', ['eq' => 0]);
            foreach ($collection as $row) {
                $totalCommission = 0;
                $order = $this->_objectManager
                        ->create('Magento\Sales\Model\Order')
                        ->load($row['order_id']);
                $row->setAdminPayStatus(1)->save();
                $totaltax = $totaltax + $row->getTotalTax();
                $actparterprocost = $actparterprocost + $row->getActualSellerAmount();
                $adminCommision = $adminCommision + $row->getTotalCommission();
                $totalamount = $totalamount + $row->getTotalAmount();
                $totalCommission = $totalCommission + $row->getTotalCommission();
                if (!$helper->getConfigTaxManage()) {
                    $totalCommission = $totalCommission + $row->getTotalTax();
                }
                $sellerId = $row->getSellerId();
                $orderinfo = $orderinfo."<tbody><tr>
                <td class='item-info'>".$row['magerealorder_id']."</td>
                <td class='item-info'>".$row['magepro_name']."</td>
                <td class='item-info'>".$row['magequantity']."</td>
                <td class='item-info'>".$order->formatPrice($row['magepro_price'])."</td>
                <td class='item-info'>".$order->formatPrice($row['cod_charges'])."</td>
                <td class='item-info'>".$order->formatPrice($row['total_tax'])."</td>
                <td class='item-info'>".$order->formatPrice($row['actual_seller_amount'])."</td>
                <td class='item-info'>".$order->formatPrice($totalCommission).'</td>
                </tr></tbody>';
            }
            if (!$helper->getConfigTaxManage()) {
                $adminCommision = $adminCommision + $totaltax;
            }
            if ($adminCommision) {
                $seller = $this->_objectManager
                        ->create('Magento\Customer\Model\Customer')
                        ->load($sellerId);
                $emailTempVariables = [];
                $emailTempVariables['myvar1'] = $seller->getName();
                $emailTempVariables['myvar2'] = $order->getRealOrderId();
                $emailTempVariables['myvar3'] = $orderinfo;
                $emailTempVariables['myvar4'] = $order->formatPrice($adminCommision);
                $adminStoremail = $helper->getAdminEmailId();
                $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
                $adminUsername = 'Admin';
                $senderInfo = [];
                $receiverInfo = [];

                $senderInfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail(),
                ];
                $receiverInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];
                $this->sendadminMail($emailTempVariables, $senderInfo, $receiverInfo);
                $this->messageManager->addSuccess(
                    __('Payment has been successfully done for the admin.')
                );

                return $this->resultRedirectFactory
                        ->create()->setPath(
                            'marketplace/order/view',
                            ['id' => $orderId]
                        );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t pay the admin right now.'));
        }

        return $this->resultRedirectFactory->create()->setPath('marketplace/order/history');
    }

    protected function sendadminMail($emailTempVariables, $senderInfo, $receiverInfo)
    {
        $this->_tempId = 'marketplace_cod_payadmin_email_template';
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTempVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }
}
