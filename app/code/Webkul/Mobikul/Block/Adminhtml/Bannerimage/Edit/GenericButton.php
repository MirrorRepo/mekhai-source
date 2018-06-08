<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Mobikul
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Block\Adminhtml\Bannerimage\Edit;

    use Webkul\Mobikul\Controller\RegistryConstants;

    /**
    * Class GenericButton.
    */
    class GenericButton     {
        /**
        * Url Builder.
        *
        * @var \Magento\Framework\UrlInterface
        */
        protected $urlBuilder;

        /**
        * Registry.
        *
        * @var \Magento\Framework\Registry
        */
        protected $registry;

        /**
        * Constructor.
        *
        * @param \Magento\Backend\Block\Widget\Context $context
        * @param \Magento\Framework\Registry           $registry
        */
        public function __construct(\Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry) {
            $this->urlBuilder = $context->getUrlBuilder();
            $this->registry = $registry;
        }

        /**
        * Return the customer Id.
        *
        * @return int|null
        */
        public function getBannnerimageId()     {
            return $this->registry->registry(RegistryConstants::CURRENT_BANNER_ID);
        }

        /**
        * Generate url by route and parameters.
        *
        * @param string $route
        * @param array  $params
        *
        * @return string
        */
        public function getUrl($route="", $params=[])   {
            return $this->urlBuilder->getUrl($route, $params);
        }

    }