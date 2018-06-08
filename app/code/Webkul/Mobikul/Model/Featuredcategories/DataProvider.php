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

    namespace Webkul\Mobikul\Model\Featuredcategories;
    use Magento\Eav\Model\Config;
    use Webkul\Mobikul\Model\Featuredcategories;
    use Magento\Framework\App\ObjectManager;
    use Magento\Framework\Session\SessionManagerInterface;
    use Webkul\Mobikul\Model\ResourceModel\Featuredcategories\Collection;
    use Webkul\Mobikul\Model\ResourceModel\Featuredcategories\CollectionFactory as FeaturedcatCollectionFactory;

    class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider    {

        protected $collection;
        protected $loadedData;
        protected $session;

        public function __construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            FeaturedcatCollectionFactory $featuredcategoriesCollectionFactory,
            array $meta = [],
            array $data = []
        ) {
            parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
            $this->collection = $featuredcategoriesCollectionFactory->create();
            $this->collection->addFieldToSelect("*");
        }

        protected function getSession()     {
            if ($this->session === null)
                $this->session = ObjectManager::getInstance()->get("Magento\Framework\Session\SessionManagerInterface");
            return $this->session;
        }

        public function getData()   {
            if (isset($this->loadedData))
                return $this->loadedData;
            $items = $this->collection->getItems();
            foreach ($items as $featuredcategories) {
                $result["featuredcategories"] = $featuredcategories->getData();
                $this->loadedData[$featuredcategories->getId()] = $result;
            }
            $data = $this->getSession()->getFeaturedcategoriesFormData();
            if (!empty($data)) {
                $featuredcategoriesId = isset($data["mobikul_featuredcategories"]["id"]) ? $data["mobikul_featuredcategories"]["id"] : null;
                $this->loadedData[$featuredcategoriesId] = $data;
                $this->getSession()->unsFeaturedcategoriesFormData();
            }
            return $this->loadedData;
        }

    }