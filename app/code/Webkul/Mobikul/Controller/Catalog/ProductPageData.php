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

    namespace Webkul\Mobikul\Controller\Catalog;

    class ProductPageData extends AbstractCatalog   {

        public function execute()   {
            $returnArray                               = [];
            $returnArray["id"]                         = 0;
            $returnArray["msrp"]                       = 0.0;
            $returnArray["name"]                       = "";
            $returnArray["price"]                      = 0.0;
            $returnArray["links"]                      = new \stdClass();
            $returnArray["typeId"]                     = "";
            $returnArray["authKey"]                    = "";
            $returnArray["success"]                    = false;
            $returnArray["message"]                    = "";
            $returnArray["samples"]                    = new \stdClass();
            $returnArray["maxPrice"]                   = 0.0;
            $returnArray["minPrice"]                   = 0.0;
            $returnArray["isInRange"]                  = false;
            $returnArray["priceView"]                  = "";
            $returnArray["cartCount"]                  = 0;
            $returnArray["finalPrice"]                 = 0.0;
            $returnArray["productUrl"]                 = "";
            $returnArray["ratingData"]                 = [];
            $returnArray["reviewList"]                 = [];
            $returnArray["tierPrices"]                 = [];
            $returnArray["msrpEnabled"]                = 0;
            $returnArray["description"]                = "";
            $returnArray["isAvailable"]                = false;
            $returnArray["priceFormat"]                = new \stdClass();
            $returnArray["groupedData"]                = [];
            $returnArray["responseCode"]               = 0;
            $returnArray["specialPrice"]               = 0.0;
            $returnArray["formatedMsrp"]               = "";
            $returnArray["availability"]               = __("Out of stock");
            $returnArray["imageGallery"]               = [];
            $returnArray["isInWishlist"]               = false;
            $eachProduct["groupedPrice"]               = "";
            $returnArray["formatedPrice"]              = "";
            $returnArray["customOptions"]              = [];
            $returnArray["bundleOptions"]              = [];
            $returnArray["ratingFormData"]             = [];
            $returnArray["guestCanReview"]             = true;
            $returnArray["wishlistItemId"]             = 0;
            $returnArray["formatedMinPrice"]           = "";
            $returnArray["formatedMaxPrice"]           = "";
            $returnArray["shortDescription"]           = "";
            $returnArray["configurableData"]           = new \stdClass();
            $returnArray["upsellProductList"]          = [];
            $returnArray["formatedFinalPrice"]         = "";
            $returnArray["relatedProductList"]         = [];
            $returnArray["showPriceDropAlert"]         = false;
            $returnArray["showBackInStockAlert"]       = false;
            $returnArray["formatedSpecialPrice"]       = "";
            $returnArray["additionalInformation"]      = [];
            $returnArray["isAllowedGuestCheckout"]     = true;
            $returnArray["msrpDisplayActualPriceType"] = 0;
            try {
                $wholeData       = $this->getRequest()->getPostValue();
                $this->_headers  = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $width        = $this->_helper->validate($wholeData, "width")      ? $wholeData["width"]      : 1000;
                        $storeId      = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $quoteId      = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $productId    = $this->_helper->validate($wholeData, "productId")  ? $wholeData["productId"]  : 0;
                        $customerId   = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment  = $this->_emulate->startEnvironmentEmulation($storeId);
// setting currency /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $store        = $this->_objectManager->create("\Magento\Store\Model\Store");
                        $baseCurrency = $store->getBaseCurrencyCode();
                        $currency     = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $baseCurrency;
                        $store->setCurrentCurrencyCode($currency);
                        $product      = $this->_objectManager->create("\Magento\Catalog\Model\Product")->load($productId);
                        if(!$product->getId()){
                            $returnArray["message"] = __("Invalid product.");
                            return $this->getJsonResponse($returnArray);
                        }
                        $this->_coreRegistry->register("current_product", $product);
                        $this->_coreRegistry->register("product", $product);
                        $taxHelper  = $this->_objectManager->create("\Magento\Catalog\Helper\Data");
                        $isIncludeTaxInPrice = false;
                        if ($this->_helper->getConfigData("tax/display/type") == 2)
                            $isIncludeTaxInPrice = true;
                        $returnArray["id"]                     = $productId;
                        $returnArray["productUrl"]             = $product->getProductUrl();
                        $returnArray["guestCanReview"]         = (bool)$this->_helper->getConfigData("catalog/review/allow_guest");
                        $returnArray["isAllowedGuestCheckout"] = (bool)$this->_helper->getConfigData("checkout/options/guest_checkout");
                        $returnArray["name"]                   = html_entity_decode($product->getName());
                        $returnArray["typeId"]                 = $product->getTypeId();
                        $returnArray["showPriceDropAlert"]     = (bool)$this->_helper->getConfigData("catalog/productalert/allow_price");
                        $returnArray["showBackInStockAlert"]   = (bool)$this->_helper->getConfigData("catalog/productalert/allow_stock");
                        if ($product->getTypeId() == "bundle") {
                            $bundlePriceModel                = $this->_objectManager->create("\Magento\Bundle\Model\Product\Price");
                            $returnArray["formatedMinPrice"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($bundlePriceModel->getTotalPrices($product, "min", 1)));
                            $returnArray["minPrice"]         = $bundlePriceModel->getTotalPrices($product, "min", 1);
                            $returnArray["formatedMaxPrice"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($bundlePriceModel->getTotalPrices($product, "max", 1)));
                            $returnArray["maxPrice"]         = $bundlePriceModel->getTotalPrices($product, "max", 1);
                        } else {
                            $returnArray["formatedMinPrice"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($product->getMinPrice()));
                            $returnArray["minPrice"]         = $product->getMinPrice();
                            $returnArray["formatedMaxPrice"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($product->getMaxPrice()));
                            $returnArray["maxPrice"]         = $product->getMaxPrice();
                        }
                        if ($isIncludeTaxInPrice) {
                            $returnArray["formatedPrice"]        = $this->_helperCatalog->stripTags($this->_priceFormat->currency($taxHelper->getTaxPrice($product, $product->getPrice())));
                            $returnArray["price"]                = $taxHelper->getTaxPrice($product, $product->getPrice());
                            $returnArray["formatedFinalPrice"]   = $this->_helperCatalog->stripTags($this->_priceFormat->currency($taxHelper->getTaxPrice($product, $product->getFinalPrice())));
                            $returnArray["finalPrice"]           = $taxHelper->getTaxPrice($product, $product->getFinalPrice());
                            $returnArray["formatedSpecialPrice"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($taxHelper->getTaxPrice($product, $product->getSpecialPrice())));
                            $returnArray["specialPrice"]         = $taxHelper->getTaxPrice($product, $product->getSpecialPrice());
                        }
                        else{
                            $returnArray["formatedPrice"]        = $this->_helperCatalog->stripTags($this->_priceFormat->currency($product->getPrice()));
                            $returnArray["price"]                = $product->getPrice();
                            $returnArray["formatedFinalPrice"]   = $this->_helperCatalog->stripTags($this->_priceFormat->currency($product->getFinalPrice()));
                            $returnArray["finalPrice"]           = $product->getFinalPrice();
                            $returnArray["formatedSpecialPrice"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($product->getSpecialPrice()));
                            $returnArray["specialPrice"]         = $product->getSpecialPrice();
                        }
                        $returnArray["msrpEnabled"]                = $product->getMsrpEnabled();
                        $returnArray["msrpDisplayActualPriceType"] = $product->getMsrpDisplayActualPriceType();
                        $returnArray["msrp"]                       = $product->getMsrp();
                        $returnArray["formatedMsrp"]               = $this->_helperCatalog->stripTags($this->_priceFormat->currency($product->getMsrp()));
                        $returnArray["shortDescription"]           = html_entity_decode($product->getShortDescription());
                        $returnArray["description"]                = html_entity_decode($product->getDescription());
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
                        $returnArray["isInRange"] = $isInRange;
                        if ($product->isAvailable()) {
                            $returnArray["availability"] = __("In stock");
                            $returnArray["isAvailable"] = true;
                        }
// getting price format /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["priceFormat"] = $this->_objectManager->get("Magento\Framework\Locale\Format")->getPriceFormat();
// getting image galleries //////////////////////////////////////////////////////////////////////////////////////////////////////
                        $imageGallery      = [];
                        $galleryCollection = $product->getMediaGalleryImages();
                        $imageGallery[0]["smallImage"] = $this->_helperCatalog->getImageUrl($product, $width/3, "product_page_image_small", false);
                        $imageGallery[0]["largeImage"] = $this->_helperCatalog->getImageUrl($product, $width, "product_page_image_large", false);
                        $imageCount = 0;
                        foreach ($galleryCollection as $image) {
                            $imageCount++;
                            if ($imageCount == 1)
                                continue;
                            $eachImage               = [];
                            $eachImage["smallImage"] = $this->_objectManager->create("\Magento\Catalog\Helper\Image")->init($product, "product_page_image_small")->keepFrame(false)->resize($width/3)->setImageFile($image->getFile())->getUrl();
                            $eachImage["largeImage"] = $this->_objectManager->create("\Magento\Catalog\Helper\Image")->init($product, "product_page_image_large")->keepFrame(false)->resize($width)->setImageFile($image->getFile())->getUrl();
                            $imageGallery[]          = $eachImage;
                        }
                        $returnArray["imageGallery"] = $imageGallery;
//getting additional information ////////////////////////////////////////////////////////////////////////////////////////////////
                        $additionalInformation = [];
                        foreach ($product->getAttributes() as $attribute) {
                            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), [])) {
                                $value = $attribute->getFrontend()->getValue($product);
                                if (!$product->hasData($attribute->getAttributeCode()))
                                    $value = __("N/A");
                                elseif ((string)$value == "")
                                    $value = __("No");
                                elseif ($attribute->getFrontendInput() == "price" && is_string($value))
                                    $value = $this->_helperCatalog->stripTags($this->_priceFormat->currency($value));
                                if (is_string($value) && strlen($value)) {
                                    $eachAttribute           = [];
                                    $eachAttribute["label"]  = html_entity_decode($attribute->getStoreLabel());
                                    $eachAttribute["value"]  = html_entity_decode($value);
                                    $additionalInformation[] = $eachAttribute;
                                }
                            }
                        }
                        $returnArray["additionalInformation"] = $additionalInformation;
//getting rating form data //////////////////////////////////////////////////////////////////////////////////////////////////////
                        $ratingFormData   = [];
                        $ratingCollection = $this->_objectManager
                            ->create("\Magento\Review\Model\Rating")
                            ->getResourceCollection()
                            ->addEntityFilter("product")
                            ->setPositionOrder()
                            ->setStoreFilter($storeId)
                            ->addRatingPerStoreName($storeId)
                            ->load()
                            ->addOptionToItems();
                        foreach ($ratingCollection as $rating) {
                            $eachTypeRating     = [];
                            $eachRatingFormData = [];
                            foreach ($rating->getOptions() as $option)
                                $eachTypeRating[] = $option->getId();
                            $eachRatingFormData["id"]     = $rating->getId();
                            $eachRatingFormData["name"]   = $this->_helperCatalog->stripTags($rating->getRatingCode());
                            $eachRatingFormData["values"] = $eachTypeRating;
                            $ratingFormData[]             = $eachRatingFormData;
                        }
                        $returnArray["ratingFormData"] = $ratingFormData;
// getting rating data //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $ratingCollection->addEntitySummaryToItem($productId, $storeId);
                        $ratingData = [];
                        foreach ($ratingCollection as $rating) {
                            if ($rating->getSummary()) {
                                $eachRating                = [];
                                $eachRating["ratingCode"]  = $this->_helperCatalog->stripTags($rating->getRatingCode());
                                $eachRating["ratingValue"] = number_format((5 * $rating->getSummary()) / 100, 2, ".", "");
                                $ratingData[]              = $eachRating;
                            }
                        }
                        $returnArray["ratingData"] = $ratingData;
// getting review list //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $reviewList = [];
                        $reviewCollection = $this->_objectManager
                            ->create("\Magento\Review\Model\Review")
                            ->getResourceCollection()
                            ->addStoreFilter($storeId)
                            ->addEntityFilter("product", $productId)
                            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                            ->setDateOrder()
                            ->addRateVotes();
                        foreach ($reviewCollection as $review) {
                            $oneReview            = [];
                            $ratings              = [];
                            $oneReview["title"]   = $this->_helperCatalog->stripTags($review->getTitle());
                            $oneReview["details"] = $this->_helperCatalog->stripTags($review->getDetail());
                            $votes                = $review->getRatingVotes();
                            if (count($votes)) {
                                foreach ($votes as $_vote) {
                                    $oneVote          = [];
                                    $oneVote["label"] = $this->_helperCatalog->stripTags($_vote->getRatingCode());
                                    $oneVote["value"] = number_format($_vote->getValue(), 2, ".", "");
                                    $ratings[]        = $oneVote;
                                }
                            }
                            $oneReview["ratings"]  = $ratings;
                            $oneReview["reviewBy"] = __("Review by %1", $this->_helperCatalog->stripTags($review->getNickname()));
                            $oneReview["reviewOn"] = __("(Posted on %1)", $this->_helperCatalog->formatDate($review->getCreatedAt()), "long");
                            $reviewList[]          = $oneReview;
                        }
                        $returnArray["reviewList"] = $reviewList;
// getting custom options ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        $optionBlock = $this->_objectManager->create("\Magento\Catalog\Block\Product\View\Options");
                        $options     = $optionBlock->decorateArray($optionBlock->getOptions());
                        $customOptions = [];
                        if (count($options)) {
                            $eachOption       = [];
                            foreach ($options as $option) {
                                $eachOption   = $option->getData();
                                $eachOption["unformated_default_price"] = $this->_priceFormat->currency($option->getDefaultPrice(), false, false);
                                $eachOption["formated_default_price"]   = $this->_helperCatalog->stripTags($this->_priceFormat->currency($option->getDefaultPrice()));
                                $eachOption["unformated_price"] = $this->_priceFormat->currency($option->getPrice(), false, false);
                                $eachOption["formated_price"]   = $this->_helperCatalog->stripTags($this->_priceFormat->currency($option->getPrice()));
                                $optionValueCollection          = $option->getValues();
                                if(is_array($optionValueCollection) || is_object($optionValueCollection)){
                                    foreach ($optionValueCollection as $optionValue) {
                                        $eachOptionValue = [];
                                        $eachOptionValue = $optionValue->getData();
                                        $eachOptionValue["formated_price"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($optionValue->getPrice()));
                                        $eachOptionValue["formated_default_price"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($optionValue->getDefaultPrice()));
                                        $eachOption["optionValues"][] = $eachOptionValue;
                                    }
                                }
                                $customOptions[] = $eachOption;
                            }
                            $returnArray["customOptions"]   = $customOptions;
                        }
// getting downloadable product data ////////////////////////////////////////////////////////////////////////////////////////////
                        if ($product->getTypeId() == "downloadable") {
                            $linkArray          = [];
                            $downloadableBlock  = $this->_objectManager->create("\Magento\Downloadable\Block\Catalog\Product\Links");
                            $linkArray["title"] = $downloadableBlock->getLinksTitle();
                            $linkArray["linksPurchasedSeparately"] = $downloadableBlock->getLinksPurchasedSeparately();
                            $links              = $downloadableBlock->getLinks();
                            $linkData           = [];
                            foreach ($links as $link) {
                                $eachLink                  = [];
                                $eachLink["id"]            = $linkId = $link->getId();
                                $eachLink["linkTitle"]     = $link->getTitle()?$link->getTitle():"";
                                $eachLink["price"]         = $this->_priceFormat->currency($link->getPrice(), false, false);
                                $eachLink["formatedPrice"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($link->getPrice()));
                                if ($link->getSampleFile() || $link->getSampleUrl()) {
                                    $link = $this->_objectManager->get("\Magento\Downloadable\Model\LinkFactory")->create()->load($linkId);
                                    if ($link->getId()) {
                                        if ($link->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                                            $eachLink["url"]      = $link->getSampleUrl();
                                            $buffer               = file_get_contents($link->getSampleUrl());
                                            $fileInfo             = new finfo(FILEINFO_MIME_TYPE);
                                            $eachLink["mimeType"] = $fileInfo->buffer($buffer);
                                            $fileArray            = explode(DS, $link->getSampleUrl());
                                            $eachLink["fileName"] = end($fileArray);
                                        } elseif ($link->getSampleType() ==\Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                                            $sampleLinkFilePath   = $this
                                                ->_objectManager
                                                ->create("\Magento\Downloadable\Helper\File")
                                                ->getFilePath($this->_objectManager->create("\Magento\Downloadable\Model\Link")->getBaseSamplePath(), $link->getSampleFile());
                                            $eachLink["url"]      = $this->_objectManager->get("\Magento\Store\Model\StoreManagerInterface")->getStore()->getUrl("mobikulhttp/download/downloadlinksample", ["linkId"=>$linkId]);
                                            $fileArray            = explode(DS, $sampleLinkFilePath);
                                            $eachLink["mimeType"] = mime_content_type($sampleLinkFilePath);
                                            $eachLink["fileName"] = end($fileArray);
                                        }
                                    }
                                    $eachLink["haveLinkSample"]   = 1;
                                    $eachLink["linkSampleTitle"]  = __("sample");
                                }
                                $linkData[]        = $eachLink;
                            }
                            $linkArray["linkData"] = $linkData;
                            $returnArray["links"]  = $linkArray;
                            $linkSampleArray       = [];
                            $downloadableSampleBlock      = $this->_objectManager->create("\Magento\Downloadable\Block\Catalog\Product\Samples");
                            $linkSampleArray["hasSample"] = $downloadableSampleBlock->hasSamples();
                            $linkSampleArray["title"]     = $downloadableSampleBlock->getSamplesTitle();
                            $linkSamples    = $downloadableSampleBlock->getSamples();
                            $linkSampleData = [];
                            foreach ($linkSamples as $linkSample) {
                                $eachSample = [];
                                $sampleId   = $linkSample->getId();
                                $eachSample["sampleTitle"] = $this->_helperCatalog->stripTags($linkSample->getTitle());
                                $sample     = $this->_objectManager->create("\Magento\Downloadable\Model\Sample")->load($sampleId);
                                if ($sample->getId()) {
                                    if ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                                        $eachSample["url"]      = $sample->getSampleUrl();
                                        $buffer                 = file_get_contents($eachSample["url"]);
                                        $fileInfo               = new finfo(FILEINFO_MIME_TYPE);
                                        $eachSample["mimeType"] = $fileInfo->buffer($buffer);
                                        $fileArray              = explode(DS, $sample->getSampleUrl());
                                        $eachSample["fileName"] = end($fileArray);
                                    } elseif ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                                        $sampleFilePath = $this->_objectManager->create("\Magento\Downloadable\Helper\File")->getFilePath($sample->getBasePath(), $sample->getSampleFile());
                                        $eachSample["url"]      = $this->_url->getUrl("downloadable/download/sample", ["sample_id"=>$sampleId]);
                                        $fileArray              = explode(DS, $sampleFilePath);
                                        $eachSample["mimeType"] = mime_content_type($this->_objectManager->get("\Magento\Framework\Filesystem\DirectoryList")->getPath("media").DS.$sampleFilePath);
                                        $eachSample["fileName"] = end($fileArray);
                                    }
                                }
                                $linkSampleData[]               = $eachSample;
                            }
                            $linkSampleArray["linkSampleData"]  = $linkSampleData;
                            $returnArray["samples"]             = $linkSampleArray;
                        }
// getting grouped product data /////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($product->getTypeId() == "grouped") {
                            $groupedParentId    = $this->_objectManager->create("\Magento\GroupedProduct\Model\Product\Type\Grouped")
                                ->getParentIdsByChild($product->getId());
                            $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                            $minPrice    = [];
                            $groupedData = [];
                            foreach ($associatedProducts as $associatedProduct) {
                                $associatedProduct = $this->_objectManager->create("\Magento\Catalog\Model\Product")->load($associatedProduct->getId());
                                $eachAssociatedProduct         = [];
                                $eachAssociatedProduct["name"] = $this->_helperCatalog->stripTags($associatedProduct->getName());
                                $eachAssociatedProduct["id"]   = $associatedProduct->getId();
                                if ($associatedProduct->isAvailable())
                                    $eachAssociatedProduct["isAvailable"] = $associatedProduct->isAvailable();
                                else
                                    $eachAssociatedProduct["isAvailable"] = 0;
                                $fromdate  = $associatedProduct->getSpecialFromDate();
                                $todate    = $associatedProduct->getSpecialToDate();
                                $isInRange = false;
                                if (isset($fromdate) && isset($todate)) {
                                    $today     = $this->_date->date("Y-m-d H:i:s");
                                    $todayTime = $this->_date->timestamp($today);
                                    $fromTime  = $this->_date->timestamp($fromdate);
                                    $toTime    = $this->_date->timestamp($todate);
                                    if ($todayTime >= $fromTime && $todayTime <= $toTime)
                                        $isInRange = true;
                                }
                                if (isset($fromdate) && !isset($todate)) {
                                    $today     = $this->_date->date("Y-m-d H:i:s");
                                    $todayTime = $this->_date->timestamp($today);
                                    $fromTime  = $this->_date->timestamp($fromdate);
                                    if ($todayTime >= $fromTime)
                                        $isInRange = true;
                                }
                                if (!isset($fromdate) && isset($todate)) {
                                    $today     = $this->_date->date("Y-m-d H:i:s");
                                    $todayTime = $this->_date->timestamp($today);
                                    $fromTime  = $this->_date->timestamp($fromdate);
                                    if ($todayTime <= $fromTime)
                                        $isInRange = true;
                                }
                                $eachAssociatedProduct["isInRange"]     = $isInRange;
                                $eachAssociatedProduct["specialPrice"]  = $this->_helperCatalog->stripTags($this->_priceFormat->currency($associatedProduct->getSpecialPrice()));
                                $eachAssociatedProduct["foramtedPrice"] = $this->_helperCatalog->stripTags($this->_priceFormat->currency($associatedProduct->getPrice()));
                                $eachAssociatedProduct["thumbNail"]     = $this->_helperCatalog->getImageUrl($associatedProduct, $width/5);
                                $groupedData[]                          = $eachAssociatedProduct;
                            }
                            $returnArray["groupedData"] = $groupedData;
                            $minPrice = 0;
                            if($product->getMinimalPrice() == "") {
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
                                $returnArray["groupedPrice"] = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($taxHelper->getTaxPrice($minPrice)));
                            else
                                $returnArray["groupedPrice"] = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($minPrice));
                        }
// getting bundle product options ///////////////////////////////////////////////////////////////////////////////////////////////
                        if ($product->getTypeId() == "bundle") {
                            $typeInstance           = $product->getTypeInstance(true);
                            $typeInstance->setStoreFilter($product->getStoreId(), $product);
                            $optionCollection       = $typeInstance->getOptionsCollection($product);
                            $selectionCollection    = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
                            $bundleOptionCollection = $optionCollection
                                ->appendSelections($selectionCollection, false, $this->_objectManager->create("\Magento\Catalog\Model\Product")->getSkipSaleableCheck());
                            $bundleOptions = [];
                            foreach ($bundleOptionCollection as $bundleOption) {
                                $oneOption = [];
                                if (!$bundleOption->getSelections())
                                    continue;
                                $oneOption          = $bundleOption->getData();
                                $selections         = $bundleOption->getSelections();
                                unset($oneOption["selections"]);
                                $bundleOptionValues = [];
                                foreach ($selections as $selection) {
                                    $eachBundleOptionValues = [];
                                    if ($selection->isSaleable()) {
                                        $coreHelper = $this->_priceFormat;
                                        $price      = $product->getPriceModel()->getSelectionPreFinalPrice($product, $selection, 1);
                                        $priceTax   = $taxHelper->getTaxPrice($product, $price);
                                        if ($oneOption["type"] == "checkbox" || $oneOption["type"] == "multi") {
                                            $eachBundleOptionValues["title"] = str_replace("&nbsp;", " ", $this->_helperCatalog->stripTags($this->getSelectionQtyTitlePrice($priceTax, $selection, true)));
                                        }
                                        if ($oneOption["type"] == "radio" || $oneOption["type"] == "select") {
                                            $eachBundleOptionValues["title"] = str_replace("&nbsp;", " ", $this->_helperCatalog->stripTags($this->getSelectionTitlePrice($priceTax, $selection, false)));
                                        }
                                        $eachBundleOptionValues["isQtyUserDefined"] = $selection->getSelectionCanChangeQty();
                                        $eachBundleOptionValues["isDefault"]     = $selection->getIsDefault();
                                        $eachBundleOptionValues["optionValueId"] = $selection->getSelectionId();
                                        $eachBundleOptionValues["foramtedPrice"] = $coreHelper->currencyByStore($priceTax, $product->getStore(), true, true);
                                        $eachBundleOptionValues["price"]         = $coreHelper->currencyByStore($priceTax, $product->getStore(), false, false);
                                        $eachBundleOptionValues["isSingle"]      = (count($selections) == 1 && $bundleOption->getRequired());
                                        $eachBundleOptionValues["defaultQty"]    = $selection->getSelectionQty();
                                        $bundleOptionValues[] = $eachBundleOptionValues;
                                    }
                                }
                                $oneOption["optionValues"] = $bundleOptionValues;
                                $bundleOptions[]           = $oneOption;
                            }
                            $returnArray["bundleOptions"]  = $bundleOptions;
                            $returnArray["priceView"]      = $product->getPriceView();
                        }
// configurable product options /////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($product->getTypeId() == "configurable") {
                            $configurableBlock               = $this->_objectManager->create("\Webkul\Mobikul\Block\Configurable");
                            $returnArray["configurableData"] = $configurableBlock->getJsonConfig();
                        }
// getting tier prices //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $allTierPrices = [];
                        $tierBlock     = $this->_objectManager->create("\Magento\Catalog\Block\Product\Price");
                        $tierPrices    = $tierBlock->getTierPrices();
                        if ($tierPrices && count($tierPrices) > 0) {
                            foreach ($tierPrices as $price) {
                                $allTierPrices[] = __("Buy %1 for %2 each", $price["price_qty"], $this->_helperCatalog->stripTags($price["formated_price_incl_tax"]))." ".__("and")." ".__("save")." ".$price["savePercent"]."%";
                            }
                            $returnArray["tierPrices"] = $allTierPrices;
                        }
// getting related product list /////////////////////////////////////////////////////////////////////////////////////////////////
                        $relatedProductCollection = $product->getRelatedProductIds();
                        $relatedProductList = [];
                        foreach ($relatedProductCollection as $id) {
                            $eachProduct = $this->_objectManager->create("\Magento\Catalog\Model\Product")->load($id);
                            if($eachProduct->isAvailable() || (bool)$this->_helper->getConfigData("cataloginventory/options/show_out_of_stock"))
                                $relatedProductList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["relatedProductList"] = $relatedProductList;
// getting upsell product list //////////////////////////////////////////////////////////////////////////////////////////////////
                        $upsellProductCollection = $product->getUpSellProductCollection()->setPositionOrder()->addStoreFilter();
                        $upsellProductCollection->setVisibility($this->_objectManager->get("\Magento\Catalog\Model\Product\Visibility")->getVisibleInCatalogIds());
                        $upsellProductList = [];
                        foreach ($upsellProductCollection as $eachProduct) {
                            $eachProduct = $this->_objectManager->create("\Magento\Catalog\Model\Product")->load($eachProduct->getId());
                            if($eachProduct->isAvailable() || (bool)$this->_helper->getConfigData("cataloginventory/options/show_out_of_stock"))
                                $upsellProductList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["upsellProductList"] = $upsellProductList;
                        $quote = new \Magento\Framework\DataObject();
                        if ($customerId != 0) {
                            $quoteCollection          = $this->_objectManager->create("\Magento\Quote\Model\Quote")->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote                    = $quoteCollection->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
// checking for product in wishlist /////////////////////////////////////////////////////////////////////////////////////////////
                            $wishlist           = $this->_objectManager->create("\Magento\Wishlist\Model\Wishlist")->loadByCustomerId($customerId, true);
                            $wishListCollection = $wishlist->getItemCollection()->addFieldToFilter("product_id", $productId);
                            $item               = $wishListCollection->getFirstItem();
                            $returnArray["isInWishlist"] = !!$item->getId();
                            if($returnArray["isInWishlist"])
                                $returnArray["wishlistItemId"] = $item->getId();
                        }
                        if ($quoteId != 0){
                            $quote                    = $this->_objectManager->create("\Magento\Quote\Model\Quote")->setStoreId($storeId)->load($quoteId);
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
                        $returnArray["success"]       = true;
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = __("Invalid Request");
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch (\Exception $e) {
                $returnArray["message"] = __($e->getMessage());
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

        public function getSelectionTitlePrice($amount, $selection, $includeContainer = true)   {
            $priceTitle = '<span class="product-name">' . $this->_helperCatalog->escapeHtml($selection->getName()) . '</span>';
            $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+'
                . $this->_priceFormat->currency($amount). ($includeContainer ? '</span>' : '');
            return $priceTitle;
        }

        public function getSelectionQtyTitlePrice($amount, $selection, $includeContainer = true)    {
            $priceTitle = '<span class="product-name">' . $selection->getSelectionQty() * 1 . ' x ' . $this->_helperCatalog->escapeHtml($selection->getName()) . '</span>';
            $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '').'+'.$this->_priceFormat->currency($amount).($includeContainer ? '</span>' : '');
            return $priceTitle;
        }

    }