<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubDomain\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\SellerSubDomain\Model\DomainFactory;

/**
 * Webkul Marketplace AdminhtmlCustomerSaveAfterObserver Observer.
 */
class AdminhtmlCustomerSaveAfterObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager,
     * @param \Magento\Framework\Message\ManagerInterface      $messageManager,
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager,
     * @param CollectionFactory                                $collectionFactory,
     * @param ProductCollection                                $sellerProduct
     * @param \Magento\Framework\Json\DecoderInterface         $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        DomainFactory $domainFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_domainFactory = $domainFactory;
    }

    /**
     * admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // $customer = $observer->getCustomer();
        $customerid = $observer->getCustomer()->getId();
        $postData = $observer->getRequest()->getPostValue();
        $helper = $this->_objectManager->get('Webkul\SellerSubDomain\Helper\Data');
        if ($this->isSeller($customerid) && $helper->isModuleEnable() && $helper->isLocalDomainSettingEnable()) {
            try {
                $error = 0;
                if ($postData['vendor_domain'] && !filter_var($postData['vendor_domain'], FILTER_VALIDATE_URL)) {
                    $this->_messageManager->addError('%1 is not an valid email', $postData['vendor_domain']);
                    return $this;
                } elseif ($postData['vendor_domain']) {
                    $url = explode('/', trim($postData['vendor_domain']));
                    $postData['vendor_domain'] = $url[0].'//'.$url[2].'/';
                }
                if ($postData['vendor_domain']) {
                    $urlExists = $this->_domainFactory->create()->getCollection()
                    ->addFieldToFilter('vendor_url', $postData['vendor_domain'])
                    ->addFieldToFilter('seller_id', ['neq' => $customerid])
                    ->getSize();
                    if ($urlExists) {
                        $error = 1;
                    }
                }
                if (!$error) {
                    $domainData['seller_id'] = $customerid;
                    $domainData['vendor_store_id'] = $postData['store_switcher'];
                    $domainData['vendor_website_id'] = $postData['website_switcher'];
                    $domainData['vendor_url'] = $postData['vendor_domain'];
                    $domainData['created_at'] = date('Y-m-d H:i:s');
                    $domainData['updated_at'] = date('Y-m-d H:i:s');
                    $collection = $this->_domainFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('seller_id', $customerid)
                        ->addFieldToFilter('vendor_store_id', $postData['store_switcher'])
                        ->addFieldToFilter('vendor_website_id', $postData['website_switcher']);
                    if ($collection->getSize()) {
                        foreach ($collection as $vendorDomain) {
                            $vendorDomain->setVendorUrl($domainData['vendor_url'])
                                ->setUpdatedAt($domainData['updated_at'])
                                ->setEntityId($vendorDomain->getEntityId())
                                ->save();
                        }
                    } else {
                        $this->_domainFactory->create()
                        ->setData($domainData)
                        ->save();
                    }
                } else {
                    $this->_messageManager->addError(__('%1 Vendor domain already assigned.', $postData['vendor_domain']));
                }
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
        return $this;
    }
    public function isSeller($customerid)
    {
        $sellerStatus = 0;
        $model = $this->_collectionFactory->create()
        ->addFieldToFilter('seller_id', $customerid);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }
    public function getAllSellerIds()
    {
        return $this->_collectionFactory->create()
        ->addFieldToFilter('is_seller', 1)
        ->getColumnValues('entity_id');
    }
}
