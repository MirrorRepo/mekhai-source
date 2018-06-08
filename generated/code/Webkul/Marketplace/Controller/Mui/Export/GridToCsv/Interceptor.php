<?php
namespace Webkul\Marketplace\Controller\Mui\Export\GridToCsv;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Mui\Export\GridToCsv
 */
class Interceptor extends \Webkul\Marketplace\Controller\Mui\Export\GridToCsv implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Ui\Model\Export\ConvertToCsv $convertToCsv, \Magento\Framework\App\Response\Http\FileFactory $httpFile)
    {
        $this->___init();
        parent::__construct($context, $convertToCsv, $httpFile);
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
