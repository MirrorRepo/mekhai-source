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

    namespace Webkul\Mobikul\Model\Bannerimage;
    use Magento\Eav\Model\Config;
    use Webkul\Mobikul\Model\Bannerimage;
    use Magento\Framework\App\ObjectManager;
    use Magento\Framework\Session\SessionManagerInterface;
    use Webkul\Mobikul\Model\ResourceModel\Bannerimage\Collection;
    use Webkul\Mobikul\Model\ResourceModel\Bannerimage\CollectionFactory as BannerimageCollectionFactory;

    class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider    {

        protected $collection;
        protected $loadedData;
        protected $session;

        public function __construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            BannerimageCollectionFactory $bannerimageCollectionFactory,
            array $meta = [],
            array $data = []
        ) {
            parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
            $this->collection = $bannerimageCollectionFactory->create();
            $this->collection->addFieldToSelect("*");
        }

        protected function getSession()     {
            if ($this->session === null) {
                $this->session = ObjectManager::getInstance()->get("Magento\Framework\Session\SessionManagerInterface");
            }
            return $this->session;
        }

        public function getData()   {
            if (isset($this->loadedData))
                return $this->loadedData;
            $items = $this->collection->getItems();
            foreach ($items as $bannerimage) {
                $result["bannerimage"] = $bannerimage->getData();
                $this->loadedData[$bannerimage->getId()] = $result;
            }
            $data = $this->getSession()->getBannerimageFormData();
            if (!empty($data)) {
                $bannerimageId = isset($data["mobikul_bannerimage"]["id"]) ? $data["mobikul_bannerimage"]["id"] : null;
                $this->loadedData[$bannerimageId] = $data;
                $this->getSession()->unsBannerimageFormData();
            }
            return $this->loadedData;
        }

    }