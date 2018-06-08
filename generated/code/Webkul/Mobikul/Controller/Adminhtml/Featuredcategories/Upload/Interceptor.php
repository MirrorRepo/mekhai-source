<?php
namespace Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\Upload;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\Upload
 */
class Interceptor extends \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\Upload implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface, \Magento\Store\Model\StoreManagerInterface $storeManager, \Webkul\Mobikul\Api\Data\FeaturedcategoriesInterfaceFactory $featuredcategoriesDataFactory, \Webkul\Mobikul\Api\FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory)
    {
        $this->___init();
        parent::__construct($context, $filesystem, $resultPageFactory, $resultForwardFactory, $coreRegistry, $date, $categoryRepositoryInterface, $storeManager, $featuredcategoriesDataFactory, $featuredcategoriesRepository, $resultJsonFactory, $fileUploaderFactory);
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
