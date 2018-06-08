<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Helper;
    use Magento\Framework\Stdlib\DateTime\DateTime;

    class Catalog extends \Magento\Framework\App\Helper\AbstractHelper  {

        protected $_date;
        protected $directory;
        protected $_dateTime;
        protected $_customer;
        protected $coreString;
        protected $_priceFormat;
        protected $_storeManager;
        protected $_imageFactory;
        protected $_coreRegistry;
        protected $_objectManager;
        protected $_catalogHelper;
        protected $_sessionManager;
        protected $_checkoutHelper;
        protected $_wishlistRepository;

        public function __construct(
            DateTime $date,
            \Magento\Framework\Registry $coreRegistry,
            \Magento\Catalog\Helper\Data $catalogHelper,
            \Magento\Checkout\Helper\Data $checkoutHelper,
            \Magento\Framework\App\Helper\Context $context,
            \Magento\Framework\Image\Factory $imageFactory,
            \Magento\Framework\Filesystem\DirectoryList $dir,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Customer\Api\CustomerRepositoryInterface $customer,
            \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
            \Magento\Framework\Session\SessionManagerInterface $sessionManager
        ) {
            $this->directory           = $dir;
            $this->_date               = $date;
            $this->_customer           = $customer;
            $this->_coreRegistry       = $coreRegistry;
            $this->_storeManager       = $storeManager;
            $this->_imageFactory       = $imageFactory;
            $this->_objectManager      = $objectManager;
            $this->_catalogHelper      = $catalogHelper;
            $this->_sessionManager     = $sessionManager;
            $this->_checkoutHelper     = $checkoutHelper;
            $this->_wishlistRepository = $wishlistRepository;
            parent::__construct($context);
            $this->_storeSwitcher      = $this->_objectManager->create("\Magento\Store\Block\Switcher");
            $this->_dateTime           = $this->_objectManager->create("\Magento\Framework\Stdlib\DateTime");
            $this->coreString          = $this->_objectManager->create("\Magento\Framework\Stdlib\StringUtils");
            $this->_priceFormat        = $this->_objectManager->create("\Magento\Framework\Pricing\Helper\Data");
        }

        public function getCurrentStoreId(){
            return $this->_storeManager->getStore()->getStoreId();
        }

        public function getAttributeInputType($attribute)   {
            $dataType  = $attribute->getBackend()->getType();
            $inputType = $attribute->getFrontend()->getInputType();
            if ($inputType == "select" || $inputType == "multiselect")
                return "select";
            elseif ($inputType == "boolean")
                return "yesno";
            elseif ($inputType == "price")
                return "price";
            elseif ($dataType == "int" || $dataType == "decimal")
                return "number";
            elseif ($dataType == "datetime")
                return "date";
            else
                return "string";
        }

        public function _renderRangeLabel($fromPrice, $toPrice, $storeId)   {
            $formattedFromPrice = $this->stripTags($this->_priceFormat->currency($fromPrice));
            if ($toPrice === "" || $toPrice < 1) {
                return __("%1 and above", $formattedFromPrice);
            } elseif ($fromPrice == $toPrice && $this->scopeConfig->getValue("catalog/layered_navigation/one_price_interval", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                return $formattedFromPrice;
            } else {
                if ($fromPrice != $toPrice)
                    $toPrice -= .01;
                return __("%1 - %2", $formattedFromPrice, $this->stripTags($this->_priceFormat->currency($toPrice)));
            }
        }

        public function getPriceFilter($priceFilterModel, $storeId)     {
            if ($this->getPriceRangeCalculation() == "improved") {
                $algorithmModel = $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\Price\Algorithm");
                $collection = $priceFilterModel->getLayer()->getProductCollection();
                $appliedInterval = $priceFilterModel->getInterval();
                if ($appliedInterval && $collection->getPricesCount() <= $priceFilterModel->getIntervalDivisionLimit())
                    return [];
                $algorithmModel->setPricesModel($priceFilterModel)->setStatistics(
                    $collection->getMinPrice(),
                    $collection->getMaxPrice(),
                    $collection->getPriceStandardDeviation(),
                    $collection->getPricesCount()
                );
                if ($appliedInterval) {
                    if ($appliedInterval[0] == $appliedInterval[1] || $appliedInterval[1] === "0")
                        return [];
                    $algorithmModel->setLimits($appliedInterval[0], $appliedInterval[1]);
                }
                $items = [];
                foreach ($algorithmModel->calculateSeparators() as $separator) {
                    $items[] = [
                        "label" => $this->stripTags($this->_renderRangeLabel($separator["from"], $separator["to"], $storeId)),
                        "id"    => (($separator["from"] == 0) ? "" : $separator["from"])."-".$separator["to"].$priceFilterModel->_getAdditionalRequestData(),
                        "count" => $separator["count"]
                    ];
                }
            } elseif ($priceFilterModel->getInterval()) {
                return [];
            }
            $range    = $priceFilterModel->getPriceRange();
            $dbRanges = $priceFilterModel->getRangeItemCounts($range."");
            $data     = [];
            if (!empty($dbRanges)) {
                $lastIndex = array_keys($dbRanges);
                $lastIndex = $lastIndex[count($lastIndex) - 1];
                foreach ($dbRanges as $index => $count) {
                    $fromPrice = ($index == 1) ? "" : (($index - 1) * $range);
                    $toPrice   = ($index == $lastIndex) ? "" : ($index * $range);
                    $data[]    = [
                        "label" => $this->stripTags($this->_renderRangeLabel($fromPrice, $toPrice, $storeId)),
                        "id"    => $fromPrice."-".$toPrice,
                        "count" => $count
                    ];
                }
            }
            return $data;
        }

        public function getAttributeFilter($attributeFilterModel, $_filter)     {
            $options      = $_filter->getFrontend()->getSelectOptions();
            $optionsCount = $this->_objectManager
                ->create("\Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute")
                ->getCount($attributeFilterModel);
            $data = [];
            foreach ($options as $option) {
                if (is_array($option["value"])) {
                    continue;
                }
                if ($this->coreString->strlen($option["value"])) {
                    if ($_filter->getIsFilterable() == 1) {
                        if (!empty($optionsCount[$option["value"]])) {
                            $data[] = [
                                "label" => html_entity_decode($option["label"]),
                                "id"    => $option["value"],
                                "count" => $optionsCount[$option["value"]]
                            ];
                        }
                    } else {
                        $data[] = [
                            "label" => html_entity_decode($option["label"]),
                            "id"    => $option["value"],
                            "count" => isset($optionsCount[$option["value"]]) ? $optionsCount[$option["value"]] : 0
                        ];
                    }
                }
            }
            return $data;
        }

        public function getQueryArray($queryStringArray)    {
            $queryArray = [];
            foreach($queryStringArray as $each) {
                if(in_array($each["inputType"], ["string", "yesno"])) {
                    if($each["value"] != "")
                        $queryArray[$each["code"]] = $each["value"];
                }
                else
                if(in_array($each["inputType"], ["price", "date"])) {
                    $valueArray = $each["value"];
                    if($valueArray["from"] != "" && $valueArray["to"] != "")
                        $queryArray[$each["code"]] = ["from"=>$valueArray["from"], "to"=>$valueArray["to"]];
                }
                else
                if($each["inputType"] == "select") {
                    $valueArray = $each["value"];
                    $selectedArray = [];
                    foreach($valueArray as $key=>$value) {
                        if($value == "true")
                            $selectedArray[] = $key;
                    }
                    if(count($selectedArray) > 0)
                        $queryArray[$each["code"]] = $selectedArray;
                }
            }
            return $queryArray;
        }

        public function getIfTaxIncludeInPrice()    {
            return $this->scopeConfig->getValue("tax/display/type", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getOneProductRelevantData($product, $storeId, $width, $customerId=0)  {
            $this->_coreRegistry->unregister("current_product");
            $this->_coreRegistry->unregister("product");
            $this->_coreRegistry->register("current_product", $product);
            $this->_coreRegistry->register("product", $product);
            $reviews = $this->_objectManager->create("\Magento\Review\Model\Review")
                ->getResourceCollection()
                ->addStoreFilter($storeId)
                ->addEntityFilter("product", $product->getId())
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->setDateOrder()
                ->addRateVotes();
            $ratings = [];
            if (count($reviews) > 0) {
                foreach ($reviews->getItems() as $review) {
                    foreach ($review->getRatingVotes() as $vote)
                        $ratings[] = $vote->getPercent();
                }
            }
            $isIncludeTaxInPrice = false;
            if ($this->getIfTaxIncludeInPrice() == 2)
                $isIncludeTaxInPrice = true;
            $eachProduct                   = [];
            if ($product->getTypeId() == "configurable") {
                $configurableBlock               = $this->_objectManager->create("\Webkul\Mobikul\Block\Configurable");
                $eachProduct["configurableData"] = $configurableBlock->getJsonConfig();
            }
            else
                $eachProduct["configurableData"] = new \stdClass();
            $eachProduct["isInWishlist"]   = false;
            $eachProduct["wishlistItemId"] = 0;
            if($customerId != 0)    {
                $wishlist = $this->_wishlistRepository->create()->loadByCustomerId($customerId, true);
                $wishlistCollection = $this->_objectManager->create("Magento\Wishlist\Model\ResourceModel\Item\Collection")
                        ->addFieldToFilter("wishlist_id", $wishlist->getId())
                        ->addFieldToFilter("product_id", $product->getId());
                $item = $wishlistCollection->getFirstItem();
                $eachProduct["isInWishlist"] = !!$item->getId();
                if($eachProduct["isInWishlist"])
                    $eachProduct["wishlistItemId"] = (int)$item->getId();
            }
            $eachProduct["entityId"] = $product->getId();
            $eachProduct["sku"]      = $product->getSku();
            $eachProduct["typeId"]   = $product->getTypeId();
            if ($product->getTypeId() == "downloadable") {
                $eachProduct["linksPurchasedSeparately"] = $product->getLinksPurchasedSeparately();
            }
            if ($product->getTypeId() == "bundle") {
                $eachProduct["priceView"] = $product->getPriceView();
                $priceModel  = $product->getPriceModel();
                if ($isIncludeTaxInPrice) {
                    list($minimalPriceInclTax, $maximalPriceInclTax) = $priceModel->getTotalPrices($product, null, true, false);
                    $eachProduct["formatedMinPrice"] = $this->stripTags($this->_priceFormat->currency($this->_catalogHelper->getTaxPrice($product, $minimalPriceInclTax)));
                    $eachProduct["minPrice"]         = $this->_catalogHelper->getTaxPrice($product, $minimalPriceInclTax);
                    $eachProduct["formatedMaxPrice"] = $this->stripTags($this->_priceFormat->currency($this->_catalogHelper->getTaxPrice($product, $maximalPriceInclTax)));
                    $eachProduct["maxPrice"]         = $this->_catalogHelper->getTaxPrice($product, $maximalPriceInclTax);
                }
                else {
                    list($minimalPriceTax, $maximalPriceTax) = $priceModel->getTotalPrices($product, null, null, false);
                    $eachProduct["formatedMinPrice"] = $this->stripTags($this->_priceFormat->currency($minimalPriceTax));
                    $eachProduct["minPrice"]         = $minimalPriceTax;
                    $eachProduct["formatedMaxPrice"] = $this->stripTags($this->_priceFormat->currency($maximalPriceTax));
                    $eachProduct["maxPrice"]         = $maximalPriceTax;
                }
            }
            $tierPrice = $product->getTierPrice();
            if (count($tierPrice) > 0) {
                $tierPrices = [];
                foreach ($tierPrice as $value) {
                    $tierPrices[] = $value["price"];
                }
                sort($tierPrices);
                $eachProduct["tierPrice"] = $this->stripTags($this->_priceFormat->currency($tierPrices[0]));
                $eachProduct["hasTierPrice"] = true;
            } else
                $eachProduct["hasTierPrice"] = false;
            $eachProduct["shortDescription"] = html_entity_decode($product->getShortDescription());
            if($eachProduct["shortDescription"] == "")
                $eachProduct["shortDescription"] = html_entity_decode($product->getDescription());
            $rating = 0;
            if (count($ratings) > 0)
                $rating = number_format((5 * (array_sum($ratings) / count($ratings))) / 100, 2, ".", "");
            $eachProduct["rating"] = $rating;
            if ($product->isAvailable())
                $eachProduct["isAvailable"] = true;
            else
                $eachProduct["isAvailable"] = false;
            if ($isIncludeTaxInPrice) {
                $eachProduct["formatedPrice"]        = $this->stripTags($this->_priceFormat->currency($this->_catalogHelper->getTaxPrice($product->getPrice())));
                $eachProduct["price"]                = $this->_catalogHelper->getTaxPrice($product->getPrice());
                $eachProduct["formatedFinalPrice"]   = $this->stripTags($this->_priceFormat->currency($this->_catalogHelper->getTaxPrice($product->getFinalPrice())));
                $eachProduct["finalPrice"]           = $this->_catalogHelper->getTaxPrice($product->getFinalPrice());
                $eachProduct["formatedSpecialPrice"] = $this->stripTags($this->_priceFormat->currency($this->_catalogHelper->getTaxPrice($product->getSpecialPrice())));
                $eachProduct["specialPrice"]         = $this->_catalogHelper->getTaxPrice($product->getSpecialPrice());
            }
            else    {
                $eachProduct["formatedPrice"]        = $this->stripTags($this->_priceFormat->currency($product->getPrice()));
                $eachProduct["price"]                = $product->getPrice();
                $eachProduct["formatedFinalPrice"]   = $this->stripTags($this->_priceFormat->currency($product->getFinalPrice()));
                $eachProduct["finalPrice"]           = $product->getFinalPrice();
                $eachProduct["formatedSpecialPrice"] = $this->stripTags($this->_priceFormat->currency($product->getSpecialPrice()));
                $eachProduct["specialPrice"]         = $product->getSpecialPrice();
            }
            $eachProduct["hasOptions"]                 = $product->getHasOptions();
            $eachProduct["requiredOptions"]            = $product->getRequiredOptions();
            $returnArray["msrpEnabled"]                = $product->getMsrpEnabled();
            $returnArray["msrpDisplayActualPriceType"] = $product->getMsrpDisplayActualPriceType();
            $eachProduct["name"]                       = html_entity_decode($product->getName());
            if ($product->getTypeId() == "grouped") {
                $minPrice = 0;
                if($product->getMinimalPrice() == "") {
                    // $groupedParentId = $this->_objectManager
                    //     ->create("\Magento\GroupedProduct\Model\Product\Type\Grouped")
                    //     ->getParentIdsByChild($product->getId());
                    $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                    $minPrice = [];
                    foreach ($associatedProducts as $associatedProduct) {
                        if ($ogPrice = $associatedProduct->getPrice())
                            $minPrice[] = $ogPrice;
                    }
                    $minPrice = min($minPrice);
                }
                else
                    $minPrice = $product->getMinimalPrice();
                if ($isIncludeTaxInPrice)
                    $eachProduct["groupedPrice"] = $this->stripTags($this->_priceFormat->currency($this->_catalogHelper->getTaxPrice($minPrice)));
                else
                    $eachProduct["groupedPrice"] = $this->stripTags($this->_priceFormat->currency($minPrice));
            }
            $fromdate  = $product->getSpecialFromDate();
            $todate    = $product->getSpecialToDate();
            $isInRange = false;
            if (isset($fromdate) && isset($todate)) {
                $today     = $this->_date->date("Y-m-d H:i:s");
                $todayTime = strtotime($today);
                $fromTime  = strtotime($fromdate);
                $toTime    = strtotime($todate);
                if ($todayTime >= $fromTime && $todayTime <= $toTime)
                    $isInRange = true;
            }
            if (isset($fromdate) && !isset($todate)) {
                $today     = $this->_date->date("Y-m-d H:i:s");
                $todayTime = strtotime($today);
                $fromTime  = strtotime($fromdate);
                if ($todayTime >= $fromTime)
                    $isInRange = true;
            }
            if(!isset($fromdate) && isset($todate)){
                $today      = $this->_date->date("Y-m-d H:i:s");
                $today_time = strtotime($today);
                $from_time  = strtotime($fromdate);
                if($today_time <= $from_time)
                    $isInRange = true;
            }
            $eachProduct["isInRange"] = $isInRange;
            $eachProduct["thumbNail"] = $this->getImageUrl($product, $width/2.5);
            return $eachProduct;
        }

        public function getStoreData()  {
            $storeData                    = [];
            $storeBlock                   = $this->_storeSwitcher;
            foreach ($storeBlock->getGroups() as $group) {
                $groupArr                 = [];
                $groupArr["id"]           = $group->getGroupId();
                $groupArr["name"]         = $group->getName();
                $stores                   = $group->getStores();
                foreach ($stores as $store) {
                    if (!$store->isActive())
                        continue;
                    $storeArr             = [];
                    $storeArr["id"]       = $store->getStoreId();
                    $code                 = explode("_", $this->getLocaleCodes($store->getId()));
                    $storeArr["code"]     = $code[0];
                    $storeArr["name"]     = $store->getName();
                    $groupArr["stores"][] = $storeArr;
                }
                $storeData[]              = $groupArr;
            }
            return $storeData;
        }

        public function stripTags($data) {
            return strip_tags($data);
        }

        public function getLocaleCodes($store)  {
            return $this->scopeConfig->getValue("general/locale/code", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        }

        public function showOutOfStock()    {
            return $this->scopeConfig->getValue("cataloginventory/options/show_out_of_stock", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getPageSize()    {
            return $this->scopeConfig->getValue("mobikul/configuration/pagesize", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getPriceRangeCalculation()  {
            return $this->scopeConfig->getValue("catalog/layered_navigation/price_range_calculation", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getMaxQueryLength()     {
            return $this->scopeConfig->getValue(\Magento\Search\Model\Query::XML_PATH_MAX_QUERY_LENGTH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function formatDate($date, $format = null)   {
            if ($format != null)
                return $this->_dateTime->formatDate($date, $format);
            else
                return $this->_dateTime->formatDate($date);
        }

        public function escapeHtml($text)   {
            return $this->_objectManager->create("\Magento\Framework\Escaper")->escapeHtml($text);
        }

        public function getBasePath($folder = "media")  {
            return $this->directory->getPath($folder);
        }

        public function getImageUrl($product, $resize, $imageType="product_page_image_large", $keepFrame=true) {
            return $this->_objectManager
                ->create("\Magento\Catalog\Helper\Image")
                ->init($product, $imageType)
                ->keepFrame($keepFrame)
                ->resize($resize)
                ->getUrl();
        }

        public function resizeNCache($basePath, $newPath, $width, $height, $forCustomer=false)   {
            if (!is_file($newPath) || $forCustomer) {
                $imageObj = $this->_imageFactory->create($basePath);
                $imageObj->keepAspectRatio(false);
                $imageObj->backgroundColor([255, 255, 255]);
                $imageObj->keepFrame(false);
                $imageObj->resize($width, $height);
                $imageObj->save($newPath);
            }
        }

    }