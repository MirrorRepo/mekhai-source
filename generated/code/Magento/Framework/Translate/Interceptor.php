<?php
namespace Magento\Framework\Translate;

/**
 * Interceptor class for @see \Magento\Framework\Translate
 */
class Interceptor extends \Magento\Framework\Translate implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\DesignInterface $viewDesign, \Magento\Framework\Cache\FrontendInterface $cache, \Magento\Framework\View\FileSystem $viewFileSystem, \Magento\Framework\Module\ModuleList $moduleList, \Magento\Framework\Module\Dir\Reader $modulesReader, \Magento\Framework\App\ScopeResolverInterface $scopeResolver, \Magento\Framework\Translate\ResourceInterface $translate, \Magento\Framework\Locale\ResolverInterface $locale, \Magento\Framework\App\State $appState, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\App\RequestInterface $request, \Magento\Framework\File\Csv $csvParser, \Magento\Framework\App\Language\Dictionary $packDictionary)
    {
        $this->___init();
        parent::__construct($viewDesign, $cache, $viewFileSystem, $moduleList, $modulesReader, $scopeResolver, $translate, $locale, $appState, $filesystem, $request, $csvParser, $packDictionary);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getLocale');
        if (!$pluginInfo) {
            return parent::getLocale();
        } else {
            return $this->___callPlugins('getLocale', func_get_args(), $pluginInfo);
        }
    }
}
