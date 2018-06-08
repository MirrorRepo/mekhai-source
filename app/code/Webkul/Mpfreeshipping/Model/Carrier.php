<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpfreeshipping
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Mpfreeshipping\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Session\SessionManager;

/**
 * Marketplace Percountry Perproduct shipping.
 */
class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code of the carrier.
     *
     * @var string
     */
    const CODE = 'mpfreeshipping';
    /**
     * Code of the carrier.
     *
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * Rate request data.
     *
     * @var \Magento\Quote\Model\Quote\Address\RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data.
     *
     * @var Result|null
     */
    protected $_result = null;
    /**
     * @var SessionManager
     */
    protected $_coreSession;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * Raw rate request data
     *
     * @var \Magento\Framework\DataObject|null
     */
    protected $_rawRequest = null;
     /**
      * @var \Magento\Shipping\Model\Rate\ResultFactory
      */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\ObjectManagerInterface                   $objectManager
     * @param SessionManager                                              $coreSession
     * @param \Magento\Checkout\Model\Session                             $checkoutSession
     * @param \Magento\Customer\Model\Session                             $customerSession
     * @param \Webkul\Mpfreeshipping\Helper\Data                     $currentHelper
     * @param array                                                       $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $coreSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Mpfreeshipping\Helper\Data $currentHelper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreSession = $coreSession;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_currentHelper = $currentHelper;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect and get rates.
     *
     * @param RateRequest $request
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Error|bool|Result
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->getConfigFlag('active') || $this->_scopeConfig->getValue('carriers/mp_multishipping/active')) {
            return false;
        }

        $this->setRequest($request);
        $shippingpricedetail = $this->getShippingPricedetail($this->_rawRequest);

        $result = $this->_rateResultFactory->create();
        if (isset($shippingpricedetail['error']) && $shippingpricedetail['error'] == true) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('mpfreeshipping');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
            return $result;
        }
        $rate = $this->_rateMethodFactory->create();

        $rate->setCarrier('mpfreeshipping');
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('mpfreeshipping');
        $rate->setMethodTitle($this->getConfigData('method_title'));

        $rate->setCost($shippingpricedetail['handlingfee']);
        $rate->setPrice($shippingpricedetail['handlingfee']);
        $result->append($rate);

        return $result;
    }
    /**
     * @param \Magento\Framework\DataObject|null $request
     * @return $this
     * @api
     */
    public function setRawRequest($request)
    {
        $this->_rawRequest = $request;

        return $this;
    }

    /**
     * Prepare and set request to this instance.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $this->_request = $request;

        $r = new \Magento\Framework\DataObject();
        $mpassignproductId = 0;
        $shippingdetail = [];
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $sellerId = 0;
            $mpassignproductId = $this->_getAssignProduct($item);
            $sellerId = $this->_getSellerId($mpassignproductId, $item->getProductId());
            if (count($shippingdetail) == 0) {
                array_push(
                    $shippingdetail,
                    [
                        'seller_id' => $sellerId,
                        'product_name' => $item->getName(),
                        'item_id' => $item->getId(),
                        'qty' => $item->getQty(),
                        'price' => $item->getPrice()*$item->getQty(),
                    ]
                );
            } else {
                $shipinfoflag = true;
                $index = 0;
                foreach ($shippingdetail as $itemship) {
                    if ($itemship['seller_id'] == $sellerId) {
                        $itemship['product_name'] = $itemship['product_name'].','.$item->getName();
                        $itemship['item_id'] = $itemship['item_id'].','.$item->getId();
                        $itemship['qty'] = $itemship['qty'] + $item->getQty();
                        $itemship['price'] = $itemship['price'] + $item->getPrice()*$item->getQty();
                        $shippingdetail[$index] = $itemship;
                        $shipinfoflag = false;
                    }
                    ++$index;
                }
                if ($shipinfoflag == true) {
                    array_push(
                        $shippingdetail,
                        [
                            'seller_id' => $sellerId,
                            'product_name' => $item->getName(),
                            'item_id' => $item->getId(),
                            'qty' => $item->getQty(),
                            'price' => $item->getPrice()*$item->getQty(),
                        ]
                    );
                }
            }
        }
        if ($request->getShippingDetails()) {
            $shippingdetail = $request->getShippingDetails();
        }
        $request->setShippingDetails($shippingdetail);
        $this->setRawRequest($request);

        return $this;
    }

    /**
     * Calculate the rate according to free shipping.
     * @param \Magento\Framework\DataObject    $request
     * @return Result
     */
    public function getShippingPricedetail(\Magento\Framework\DataObject $request)
    {
        $r = $request;
        $submethod = [];
        $shippinginfo = [];
        $handling = 0;
        $minimumOrderAmount = 0;
        $isFreeShipping = false;
        $freeShippingCheck = false;
        foreach ($r->getShippingDetails() as $shipdetail) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                if (isset($shipdetail['seller_id']) && $shipdetail['seller_id']) {
                    $isFreeShipping = false;
                    $customer = $this->_objectManager->create(
                        'Magento\Customer\Model\Customer'
                    )->load($shipdetail['seller_id']);
                    $minimumOrderAmount = $customer->getMpFreeshippingAmount();
                    if (!$minimumOrderAmount) {
                        $minimumOrderAmount = $this->getConfigData('free_shipping_subtotal');
                    }

                    if ($shipdetail['price'] >= $minimumOrderAmount) {
                        $price = 0;
                        $isFreeShipping = true;
                    }
                } else {
                    $isFreeShipping = false;
                    $minimumOrderAmount = $this->getConfigData('free_shipping_subtotal');
                    if ($shipdetail['price'] >= $minimumOrderAmount) {
                        $price = 0;
                        $isFreeShipping = true;
                    }
                }
            }
            if (!$isFreeShipping && !$this->_scopeConfig->getValue('carriers/mp_multishipping/active')) {
                return ['error'=> true];
            }
            $handling = $handling + $price;
            $submethod =
            [
                [
                    'method' => $this->getConfigData('title'),
                    'cost' => $price,
                    'base_amount' => $price,
                    'error' => 0
                ]
            ];
            array_push(
                $shippinginfo,
                [
                    'seller_id' => $shipdetail['seller_id'],
                    'methodcode' => $this->_code,
                    'shipping_ammount' => $price,
                    'product_name' => $shipdetail['product_name'],
                    'submethod' => $submethod,
                    'item_ids' => $shipdetail['item_id']
                ]
            );
        }
        $debugData['result'] = $shippinginfo;
        $result = ['handlingfee' => $handling, 'shippinginfo' => $shippinginfo, 'error' => false];
        $shippingAll = $this->_coreSession->getShippingInfo();
        $shippingAll[$this->_code] = $result['shippinginfo'];
        $this->_coreSession->setShippingInfo($shippingAll);

        return $result;
    }

    /**
     * get assign product id.
     *
     * @param object $item
     *
     * @return int
     */
    protected function _getAssignProduct($item)
    {
        $mpassignproductId = 0;
        $itemOption = $this->_objectManager
            ->create('Magento\Quote\Model\Quote\Item\Option')
            ->getCollection();

        $itemOption = $itemOption->addFieldToFilter('item_id', ['eq' => $item->getId()])
            ->addFieldToFilter('code', ['eq' => 'info_buyRequest']);
        $optionValue = '';
        
        if ($itemOption->getSize()) {
            foreach ($itemOption as $value) {
                $optionValue = $value->getValue();
            }
        }
        if ($optionValue != '') {
            $temp = json_decode($optionValue, true);
            $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
        }

        return $mpassignproductId;
    }

    /**
     * get seller id.
     *
     * @param int $mpassignproductId
     * @param int $proid
     *
     * @return int
     */
    protected function _getSellerId($mpassignproductId, $proid)
    {
        $sellerId = 0;
        if ($mpassignproductId) {
            $mpassignModel = $this->_loadModel($mpassignproductId, 'Webkul\MpAssignProduct\Model\Items');
            $sellerId = $mpassignModel->getSellerId();
        } else {
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                ->getCollection()
                                ->addFieldToFilter('mageproduct_id', ['eq' => $proid]);
            foreach ($collection as $temp) {
                $sellerId = $temp->getSellerId();
            }
        }

        return $sellerId;
    }

    /**
     * load model.
     *
     * @param int    $id
     * @param string $model
     *
     * @return object
     */
    protected function _loadModel($id, $model)
    {
        return $this->_objectManager->create($model)->load($id);
    }

     /**
      * @return array
      */
    public function getAllowedMethods()
    {
        return ['mpfreeshipping' => $this->getConfigData('method_title')];
    }
}
