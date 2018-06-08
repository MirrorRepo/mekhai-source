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

    namespace Webkul\Mobikul\Ui\Component\Listing\Columns;
    use Magento\Framework\View\Element\UiComponentFactory;
    use Magento\Framework\View\Element\UiComponent\ContextInterface;
    use Magento\Store\Model\StoreManagerInterface;

    class CategoryIconImage extends \Magento\Ui\Component\Listing\Columns\Column    {
        /**
        * Store manager.
        *
        * @var \Magento\Store\Model\StoreManagerInterface
        */
        protected $storeManager;

        /**
        * @param ContextInterface      $context
        * @param UiComponentFactory    $uiComponentFactory
        * @param StoreManagerInterface $storeManager
        * @param array                 $components
        * @param array                 $data
        */
        public function __construct(
            ContextInterface $context,
            UiComponentFactory $uiComponentFactory,
            StoreManagerInterface $storeManager,
            array $components = [],
            array $data = []) {
            parent::__construct($context, $uiComponentFactory, $components, $data);
            $this->storeManager = $storeManager;
        }

        /**
        * Prepare Data Source.
        *
        * @param array $dataSource
        *
        * @return array
        */
        public function prepareDataSource(array $dataSource)    {
            if (isset($dataSource["data"]["items"])) {
                $baseTmpPath = "mobikul/categoryimages/icon/";
                $target = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$baseTmpPath;
                $fieldName = $this->getData("name");
                foreach ($dataSource["data"]["items"] as &$item) {
                    $item[$fieldName."_html"] = "<img src='".$target.$item["icon"]."'/>";
                    $item[$fieldName."_src"] = $target.$item["icon"];
                }
            }
            return $dataSource;
        }

    }