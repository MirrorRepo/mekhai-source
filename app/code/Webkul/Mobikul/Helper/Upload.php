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
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Webkul\Mobikul\Helper\Catalog as HelperCatalog;

    class Upload extends \Magento\Framework\App\Helper\AbstractHelper     {

        protected $_helper;
        protected $_directory;
        protected $_helperCatalog;
        protected $_objectManager;

        public function __construct(
            HelperData $helper,
            HelperCatalog $helperCatalog,
            \Magento\Framework\App\Helper\Context $context,
            \Magento\Framework\Filesystem\DirectoryList $dir,
            \Magento\Framework\ObjectManagerInterface $objectManager
        ) {
            $this->_directory     = $dir;
            $this->_helper        = $helper;
            $this->_objectManager = $objectManager;
            $this->_helperCatalog = $helperCatalog;
            parent::__construct($context);
        }

        public function getBasePath($directory="media")  {
            return $this->_directory->getPath($directory);
        }

        public function uploadPicture($files, $customerId, $name, $signal){
            $target = $this->getBasePath("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS;
            if(isset($files) && count($files) > 0) {
                $file = $this->_objectManager->create("\Magento\Framework\Filesystem\Io\File");
                if(is_dir($target)){
                    $directories = glob($target."*" , GLOB_ONLYDIR);
                    foreach ($directories as $dir)
                        $file->rmdir($dir, true);
                }
                $file->mkdir($target);
                foreach($files as $image) {
                    if($image["tmp_name"] != "") {
                        $splitname = explode(".", $image["name"]);
                        $finalTarget = $target.$name.".".end($splitname);
                        move_uploaded_file($image["tmp_name"], $finalTarget);
                        $userImageModel = $this->_objectManager->create("\Webkul\Mobikul\Model\UserImageFactory")->create();
                        $collection = $userImageModel->getCollection()->addFieldToFilter("customer_id", $customerId);
                        if($collection->getSize() > 0){
                            foreach($collection as $value){
                                $loadedUserImageModel = $userImageModel->load($value->getId());
                                if($signal == "banner")
                                    $loadedUserImageModel->setBanner($name.".".end($splitname));
                                if($signal == "profile")
                                    $loadedUserImageModel->setProfile($name.".".end($splitname));
                                $loadedUserImageModel->save();
                            }
                        }
                        else{
                            if($signal == "banner")
                                $userImageModel->setBanner($name.".".end($splitname));
                            if($signal == "profile")
                                $userImageModel->setProfile($name.".".end($splitname));
                            $userImageModel->setCustomerId($customerId)->save();
                        }
                    }
                }
            }
        }

        public function resizeAndCache($width=1000, $customerId, $mFactor=1, $signal){
            $returnArray            = [];
            $returnArray["url"]     = "";
            $returnArray["success"] = false;
            $returnArray["message"] = "";
            $collection  = $this->_objectManager->create("\Webkul\Mobikul\Model\UserImage")->getCollection()->addFieldToFilter("customer_id", $customerId);
            $time = time();
            if($collection->getSize() > 0){
                foreach($collection as $value) {
                    if($signal == "banner" && $value->getBanner() != ""){
                        $basePath = $this->getBasePath("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getBanner();
                        $newUrl = "";
                        if(is_file($basePath)){
                            list($w, $h, $type, $attr) = getimagesize($basePath);
                            $ratio  = $w/$h;
                            $height = ($width/$ratio)*$mFactor;
                            $width *= $mFactor;
                            $newUrl = $this->_helper->getUrl("media")."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$width."x".$height.DS.$value->getBanner()."?".$time;
                            $newPath = $this->getBasePath("media").DS."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$width."x".$height.DS.$value->getBanner();
                            $this->_helperCatalog->resizeNCache($basePath, $newPath, $width, $height, true);
                        }
                        $returnArray["url"]     = $newUrl."?".$time;
                        $returnArray["success"] = true;
                        return $returnArray;
                    }
                    if($signal == "profile" && $value->getProfile() != ""){
                        $basePath = $this->getBasePath("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getProfile();
                        $ppHeight = $ppWidth = 144 * $mFactor;
                        $newUrl = $this->_helper->getUrl("media")."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$ppWidth."x".$ppHeight.DS.$value->getProfile();
                        if(is_file($basePath)){
                            $newPath = $this->getBasePath("media").DS."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$ppWidth."x".$ppHeight.DS.$value->getProfile();
                            $this->_helperCatalog->resizeNCache($basePath, $newPath, $ppWidth, $ppHeight, true);
                        }
                        $returnArray["url"]     = $newUrl."?".$time;
                        $returnArray["success"] = true;
                        return $returnArray;
                    }
                }
            }
        }

    }