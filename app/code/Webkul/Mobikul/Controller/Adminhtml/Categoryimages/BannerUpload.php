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

    namespace Webkul\Mobikul\Controller\Adminhtml\Categoryimages;
    use Magento\Framework\Controller\ResultFactory;

    class BannerUpload extends \Webkul\Mobikul\Controller\Adminhtml\Categoryimages  {

        public function execute()   {
            $result = [];
            if ($this->getRequest()->isPost()) {
                try {
                    $fields                     = $this->getRequest()->getParams();
                    $mobikulDirPath             = $this->_mediaDirectory->getAbsolutePath("mobikul");
                    $categoryimagesDirPath      = $this->_mediaDirectory->getAbsolutePath("mobikul/categoryimages");
                    $categoryBannerImageDirPath = $this->_mediaDirectory->getAbsolutePath("mobikul/categoryimages/banner");
                    if (!file_exists($mobikulDirPath))
                        mkdir($mobikulDirPath, 0777, true);
                    if (!file_exists($categoryimagesDirPath))
                        mkdir($categoryimagesDirPath, 0777, true);
                    if (!file_exists($categoryBannerImageDirPath))
                        mkdir($categoryBannerImageDirPath, 0777, true);
                    $baseTmpPath  = "mobikul/categoryimages/banner/";
                    $target       = $this->_mediaDirectory->getAbsolutePath($baseTmpPath);
                    try {
                        $uploader = $this->_fileUploaderFactory->create(["fileId"=>"mobikul_categoryimages[banner]"]);
                        $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                        $uploader->setAllowRenameFiles(true);
                        $result = $uploader->save($target);
                        if (!$result) {
                            $result = [
                                "error"     => __("File can not be saved to the destination folder."),
                                "errorcode" => ""
                            ];
                        }
                        if (isset($result["file"])) {
                            try {
                                $result["tmp_name"] = str_replace("\\", "/", $result["tmp_name"]);
                                $result["path"] = str_replace("\\", "/", $result["path"]);
                                $result["url"]  = $this->_storeManager
                                    ->getStore()
                                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$this->getFilePath($baseTmpPath, $result["file"]);
                                $result["name"] = $result["file"];
                            } catch (\Exception $e) {
                                $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
                            }
                        }
                        $result["cookie"] = [
                            "name"     => $this->_getSession()->getName(),
                            "value"    => $this->_getSession()->getSessionId(),
                            "path"     => $this->_getSession()->getCookiePath(),
                            "domain"   => $this->_getSession()->getCookieDomain(),
                            "lifetime" => $this->_getSession()->getCookieLifetime()
                        ];
                    } catch (\Exception $e) {
                        $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
                    }
                } catch (\Exception $e) {
                    $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
                }
            }
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
        }

        public function getFilePath($path, $imageName)  {
            return rtrim($path, "/") . "/" . ltrim($imageName, "/");
        }

    }