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

namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;

class Massnotify extends \Magento\Backend\App\Action
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @param Context                                            $context
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
    }
    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpcashondelivery::save'
        );
    }
    // generate template to send mail
    protected function generateTemplate(
        $emailTemplateVariables,
        $senderInfo,
        $receiverInfo
    ) {
        $template = $this->_transportBuilder
                ->setTemplateIdentifier($this->_tempId)
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

    public function execute()
    {
        try {
            $wholedata=$this->getRequest()->getParams();
            $actparterprocost = 0;
            $totalamount = 0;
            $adminCommision = 0;
            $totaltax = 0;
            $helper = $this->_objectManager
                    ->get('Webkul\Marketplace\Helper\Data');
            $orderinfo = '';
            $sellerId = $wholedata['sellerid'];
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()
                    ->setPath('customer/index/edit', ['id'=>$sellerId]);
            }
            $wksellerorderids = explode(',', $wholedata['wksellerorderids']);
            $orderinfo = '';
            foreach ($wksellerorderids as $key) {
                $collection = $this->_objectManager
                    ->create('Webkul\Marketplace\Model\Saleslist')
                    ->getCollection()
                    ->addFieldToFilter('entity_id', ['eq'=>$key])
                    ->addFieldToFilter('collect_cod_status', ['eq'=>1])
                    ->addFieldToFilter('admin_pay_status', ['eq'=>0])
                    ->addFieldToFilter('order_id', ['neq'=>0]);
                foreach ($collection as $row) {
                    $sellerAmount = $row->getActualSellerAmount();
                    $totalCommission = $row->getTotalCommission();
                    $order = $this->_objectManager
                            ->create('Magento\Sales\Model\Order')
                            ->load($row['order_id']);
                    $totaltax = $totaltax + $row->getTotalTax();
                    $actparterprocost = $actparterprocost + $sellerAmount;
                    $adminCommision = $adminCommision + $totalCommission;
                    $totalamount = $totalamount + $row->getTotalAmount();
                    $orderinfo = $orderinfo."<tbody><tr>".
                        "<td class='item-info'>".$row['magerealorder_id'].
                        "</td><td class='item-info'>".$row['magepro_name'].
                        "</td><td class='item-info'>".$row['magequantity'].
                        "</td><td class='item-info'>".
                        $order->formatPrice($row['magepro_price']).
                        "</td><td class='item-info'>".
                        $order->formatPrice($row['total_tax']).
                        "</td><td class='item-info'>".
                        $order->formatPrice($row['actual_seller_amount']).
                        "</td><td class='item-info'>".
                        $order->formatPrice($row['total_commission']).
                        "</td></tr></tbody>";
                }
            }
            if ($helper->getConfigTaxManage()) {
                $adminCommision = $adminCommision + $totaltax;
            }
            if ($adminCommision) {
                $formatPrice = $order->formatPrice($adminCommision);
                $defaultEmailId = $helper->getDefaultTransEmailId();
                $seller = $this->_objectManager
                        ->create('Magento\Customer\Model\Customer')
                        ->load($sellerId);
                $emailTempVariables = [];
                $emailTempVariables['myvar1'] = $seller->getName();
                $emailTempVariables['myvar2'] = $actparterprocost;
                $emailTempVariables['myvar3'] = $orderinfo;
                $emailTempVariables['myvar4'] = $wholedata['customnote'];
                $emailTempVariables['myvar5'] = $formatPrice;
                $adminStoremail = $helper->getAdminEmailId();
                $adminEmail=$adminStoremail?$adminStoremail:$defaultEmailId;
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
                $this->sendadminMail(
                    $emailTempVariables,
                    $receiverInfo,
                    $senderInfo
                );
                $this->messageManager->addSuccess(
                    __('Notification has been successfully sent to seller.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            'customer/index/edit',
            ['id'=>$sellerId]
        );
    }
    // sendMail to admin to notify
    protected function sendadminMail(
        $emailTempVariables,
        $senderInfo,
        $receiverInfo
    ) {
        $this->_tempId = 'marketplace_cod_notifyseller_email_template';
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo
        );
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }
}
