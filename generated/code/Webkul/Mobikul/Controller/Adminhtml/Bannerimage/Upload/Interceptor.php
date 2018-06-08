<?php
namespace Webkul\Mobikul\Controller\Adminhtml\Bannerimage\Upload;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Adminhtml\Bannerimage\Upload
 */
class Interceptor extends \Webkul\Mobikul\Controller\Adminhtml\Bannerimage\Upload implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Registry $coreRegistry, \Magento\Framework\Filesystem $filesystem, \Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory, \Webkul\Mobikul\Api\BannerimageRepositoryInterface $bannerimageRepository, \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface, \Webkul\Mobikul\Api\Data\BannerimageInterfaceFactory $bannerimageDataFactory, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface, \Webkul\Mobikul\Model\ResourceModel\Bannerimage\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($coreRegistry, $filesystem, $context, $filter, $storeManager, $resultPageFactory, $resultJsonFactory, $fileUploaderFactory, $resultForwardFactory, $bannerimageRepository, $productRepositoryInterface, $bannerimageDataFactory, $categoryRepositoryInterface, $collectionFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}
