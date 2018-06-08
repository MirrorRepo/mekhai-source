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

namespace Webkul\Mpcashondelivery\Controller\Availability;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Check extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $proid = 0;
        $pincode = '0';
        $params = $this->getRequest()->getParams();
        if (array_key_exists('pid', $params)) {
            $proid = $params['pid'];
        }
        if (array_key_exists('pincode', $params)) {
            $pincode = $params['pincode'];
        }
        if ($proid == 0 || $pincode == '0') {
            $msg = 0;
        } else {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($proid);
            $codAvailable = $product->getCodAvailable();
            if ($codAvailable) {
                $partner = '';
                $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                            ->getCollection()->addFieldToFilter('mageproduct_id', ['eq' => $proid]);
                foreach ($collection as $temp) {
                    $partner = $temp->getSellerId();
                }
                $productWeight = $product->getWeight();
                $weight = $productWeight;
                if (!is_numeric($pincode)) {
                    $shipping = $this->_objectManager->create('Webkul\Mpcashondelivery\Model\Pricerules')
                                ->getCollection()
                                ->addFieldToFilter('seller_id', ['eq' => $partner])
                                ->addFieldToFilter('zipcode', ['eq' => $pincode])
                                ->addFieldToFilter('weight_from', ['lteq' => $weight])
                                ->addFieldToFilter('weight_to', ['gteq' => $weight]);
                } else {
                    $shipping = $this->_objectManager->create('Webkul\Mpcashondelivery\Model\Pricerules')
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id', ['eq' => $partner])
                                    ->addFieldToFilter('zipcode', ['eq' => $pincode])
                                    ->addFieldToFilter('weight_from', ['lteq' => $weight])
                                    ->addFieldToFilter('weight_to', ['gteq' => $weight]);
                    if (count($shipping) == 0) {
                        $shipping = $this->_objectManager->create('Webkul\Mpcashondelivery\Model\Pricerules')
                                        ->getCollection()
                                        ->addFieldToFilter('seller_id', ['eq' => $partner])
                                        ->addFieldToFilter('dest_zip_from', ['lteq' => intval($pincode)])
                                        ->addFieldToFilter('dest_zip_to', ['gteq' => intval($pincode)])
                                        ->addFieldToFilter('weight_from', ['lteq' => $weight])
                                        ->addFieldToFilter('weight_to', ['gteq' => $weight]);
                        if (count($shipping) == 0) {
                            $shipping = $this->_objectManager->create('Webkul\Mpcashondelivery\Model\Pricerules')
                                            ->getCollection()
                                            ->addFieldToFilter('seller_id', ['eq' => $partner])
                                            ->addFieldToFilter('dest_zip_from', ['eq' => '*'])
                                            ->addFieldToFilter('dest_zip_to', ['eq' => '*'])
                                            ->addFieldToFilter('weight_from', ['lteq' => $weight])
                                            ->addFieldToFilter('weight_to', ['gteq' => $weight]);
                        }
                    }
                }
                if (count($shipping)) {
                    $msg = 1;
                } else {
                    $msg = 0;
                }
            } else {
                $msg = 0;
            }
        }
        $this->getResponse()->setHeader('Content-type', 'text/html');
        $this->getResponse()->setBody($msg);
    }
}
