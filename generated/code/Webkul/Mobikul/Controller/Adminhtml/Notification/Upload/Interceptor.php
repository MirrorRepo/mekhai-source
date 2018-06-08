<?php
namespace Webkul\Mobikul\Controller\Adminhtml\Notification\Upload;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Adminhtml\Notification\Upload
 */
class Interceptor extends \Webkul\Mobikul\Controller\Adminhtml\Notification\Upload implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Webkul\Mobikul\Api\Data\NotificationInterfaceFactory $notificationDataFactory, \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface, \Webkul\Mobikul\Api\NotificationRepositoryInterface $notificationRepository, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory)
    {
        $this->___init();
        parent::__construct($context, $filesystem, $resultPageFactory, $resultForwardFactory, $coreRegistry, $date, $notificationDataFactory, $productRepositoryInterface, $notificationRepository, $categoryRepositoryInterface, $storeManager, $resultJsonFactory, $fileUploaderFactory);
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
