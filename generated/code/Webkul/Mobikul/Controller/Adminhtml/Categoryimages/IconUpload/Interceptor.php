<?php
namespace Webkul\Mobikul\Controller\Adminhtml\Categoryimages\IconUpload;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Adminhtml\Categoryimages\IconUpload
 */
class Interceptor extends \Webkul\Mobikul\Controller\Adminhtml\Categoryimages\IconUpload implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\Registry $coreRegistry, \Magento\Ui\Component\MassAction\Filter $filter, \Magento\Framework\Json\Helper\Data $jsonHelper, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory, \Webkul\Mobikul\Api\CategoryimagesRepositoryInterface $categoryimagesRepository, \Webkul\Mobikul\Api\Data\CategoryimagesInterfaceFactory $categoryimagesDataFactory, \Webkul\Mobikul\Model\ResourceModel\Categoryimages\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($context, $filesystem, $coreRegistry, $filter, $jsonHelper, $date, $storeManager, $resultPageFactory, $resultJsonFactory, $categoryResourceModel, $categoryRepository, $fileUploaderFactory, $resultForwardFactory, $categoryimagesRepository, $categoryimagesDataFactory, $collectionFactory);
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
