<?php
/**
 * Webkul SellerSubDomain Data Setup
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubDomain\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $_urlRewrite;

    /**
     * Init
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
    ) {
        $this->_storeManager = $storeManager;
        $this->_urlRewrite = $urlRewrite;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $allStores = $this->_storeManager->getStores(true, false);
            foreach ($allStores as $store) {
                $sourceUrlList = [
                    'sellersubdomain/collection/index' => 'collection',
                    'sellersubdomain/location/index' => 'location',
                    'sellersubdomain/feedback/index' => 'feedback'
                ];
                foreach ($sourceUrlList as $key => $path) {
                    $urlId = '';
                    $profileRequestUrl = '';
                    $urlCollectionData = $this->_urlRewrite
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter('target_path', $key)
                        ->addFieldToFilter('store_id', $store->getId());
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $profileRequestUrl = $value->getRequestPath();
                    }
                    if ($profileRequestUrl != $path) {
                        $idPath = rand(1, 100000);
                        $this->_urlRewrite->create()
                        ->load($urlId)
                        ->setStoreId($store->getId())
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($key)
                        ->setRequestPath($path)
                        ->save();
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
}
