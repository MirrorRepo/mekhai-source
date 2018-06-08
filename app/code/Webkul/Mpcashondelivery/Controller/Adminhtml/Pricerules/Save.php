<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Pricerules;

use Magento\Directory\Model\ResourceModel\Country;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpcashondelivery\Model\PricerulesFactory;
use Magento\Backend\App\Action;
use Magento\Store\Model\StoreManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends \Magento\Backend\App\Action
{

    /**
    * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
    */
    protected $_countryCollectionFactory;
    /**
     * @var storeManager
     */
    protected $_storeManager;
    /**
    * @var Price rule collection
    */
    protected $_priceruleFactory;
    /**
     * Pricerules model
     *
     * @var string
     */
    protected $_model = 'Webkul\Mpcashondelivery\Model\Pricerules';
    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
   
    /**
     * @param Action\Context            $context
     * @param Country\CollectionFactory $countryCollectionFactory
     * @param StoreManagerInterface     $storeManager
     * @param PricerulesFactory         $priceruleFactory
     * @param UploaderFactory           $fileUploaderFactory
     */
    
    public function __construct(
        Action\Context $context,
        Country\CollectionFactory $countryCollectionFactory,
        StoreManagerInterface $storeManager,
        PricerulesFactory $priceruleFactory,
        UploaderFactory $fileUploaderFactory
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_priceruleFactory = $priceruleFactory;
        $this->_storeManager = $storeManager;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct(
            $context
        );
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpcashondelivery::mpcodrates'
        );
    }
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $fieldsArray = [
            0 => 'country_code',
            1 => 'region_id',
            2 => 'is_range',
            3 => 'zipcode',
            4 => 'dest_zip_from',
            5 => 'dest_zip_to',
            6 => 'price_type',
            7 => 'fixed_price',
            8 => 'percentage_price',
            9 => 'weight_from',
            10 => 'weight_to',
        ];
        $helper = $this->_objectManager->create('Webkul\Mpcashondelivery\Helper\Data');
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $resultRedirect->setPath('mpcashondelivery/pricerules/index');
                }
                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'codratesfile']);
                $uploader->setAllowedExtensions(['csv']);
                $uploader->setAllowRenameFiles(true);
                $file = $uploader->validateFile();
                if ($file['error'] != 0) {
                    $this->messageManager->addError(
                        __(
                            'There is some error in file, please try again'
                        )
                    );

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $fileTmpname = $file['tmp_name'];
                $fileNameArray = explode('.', $file['name']);
                $ext = end($fileNameArray);
                $rows = [];
                if ($fileTmpname != '' && $ext == 'csv') {
                    $count = 0;
                    $fileHandle = fopen($fileTmpname, 'r');
                    while (!feof($fileHandle)) {
                        $data[] = fgetcsv($fileHandle, 1024);
                    }
                    fclose($fileHandle);
                    $fileData = $data;
                    foreach ($fileData as $key => $filerow) {
                        $wholedata = [];
                        if ($key == 0) {
                            $arrayDiff = array_diff($filerow, $fieldsArray);
                            if (count($arrayDiff)) {
                                $this->messageManager->addError(
                                    __(
                                        'Not a valid File, please match with sample file.'
                                    )
                                );
                                return $this->resultRedirectFactory->create()->setPath(
                                    '*/*/index',
                                    ['_secure' => $this->getRequest()->isSecure()]
                                );
                            }
                            continue;
                        } else {
                            if (is_array($filerow)) {
                                foreach ($filerow as $filekey => $filevalue) {
                                    $wholedata[$data[0][$filekey]] = $filevalue;
                                }
                                list($updatedWholedata, $errors) = $this->validation($wholedata);
                                if (empty($errors)) {
                                    $websiteId = $this->_storeManager->getStore(true)
                                                    ->getWebsite()->getId();
                                    $updatedWholedata['seller_id'] = 0;
                                    $updatedWholedata['website_id'] = $websiteId;
                                    $isNewRate = $this->checkedNewRate($updatedWholedata);
                                    if ($isNewRate == 0) {
                                        $priceruleModel = $this->_priceruleFactory->create()
                                                        ->setData($updatedWholedata);
                                        $saved = $priceruleModel->save();
                                    } else {
                                        $priceruleModel = $this->_priceruleFactory->create()
                                                        ->load($isNewRate);
                                        if (array_key_exists('fixed_price', $updatedWholedata)) {
                                            $priceruleModel->setFixedPrice($updatedWholedata['fixed_price']);
                                        }
                                        if (array_key_exists('percentage_price', $updatedWholedata)) {
                                            $priceruleModel->setPercentagePrice(
                                                $updatedWholedata['percentage_price']
                                            );
                                        }
                                        $priceruleModel->save();
                                    }
                                } else {
                                    $rows[] = $key.":".$errors[0];
                                }
                            }
                        }
                    }
                    if (count($rows)) {
                        $this->messageManager->addError(
                            __(
                                'Following rows are not valid rows : %1',
                                implode(",", $rows)
                            )
                        );
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/index',
                            ['_secure'=>$this->getRequest()->isSecure()]
                        );
                    }
                    $this->messageManager->addSuccess(__('Cash On Delivery rates are saved.'));

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
                } else {
                    $this->messageManager->addError(__('Please upload Csv file'));

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
                }
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/index',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/index',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/index',
                ['_secure'=>$this->getRequest()->isSecure()]
            );
        }
    }

    // validate data of csv for pricerules

    public function validation($wholedata)
    {
        $data = [];
        $errors = [];
        foreach ($wholedata as $key => $value) {
            switch ($key) {
                case 'country_code':
                    if (!is_string(trim($value))) {
                        $errors[] = __('Country code should be a string %1', $value);
                    } elseif (!in_array($value, $this->getCountryCollection())) {
                        $errors[] = __('Invalid country code', $value);
                    } else {
                        $data['dest_country_id'] = $value;
                    }
                    break;
                case 'region_id':
                    if ($value == '') {
                        $data['dest_region_id'] = '*';
                    } else {
                        $data['dest_region_id'] = $value;
                    }
                    break;
                case 'is_range':
                    if ($value == '') {
                        $errors[] = __('Is range can not be empty %1', $value);
                    } elseif (strtolower(trim($value)) == 'yes') {
                        $data[$key] = 0;
                    } elseif (strtolower(trim($value)) == 'no') {
                        $data[$key] = 1;
                    } else {
                        $errors[] = __('Invalid value for field is_range %1', $value);
                    }
                    break;
                case 'zipcode':
                    if (array_key_exists('is_range', $data)) {
                        if ($data['is_range'] == 1) {
                            if ($value == '') {
                                $errors[] = __('Zipcode field can not be empty %1', $value);
                            } else {
                                $data[$key] = $value;
                            }
                        }
                    }
                    break;
                case 'dest_zip_from':
                    if (array_key_exists('is_range', $data)) {
                        if ($data['is_range'] == 0) {
                            if ($value == '') {
                                $errors[] = __('Destination Zip from can not be empty %1', $value);
                            } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
                                $errors[] = __('Destination Zip from should be a numeric value %1', $value);
                            } else {
                                $data[$key] = $value;
                            }
                        }
                    }
                    break;
                case 'dest_zip_to':
                    if (array_key_exists('is_range', $data)) {
                        if ($data['is_range'] == 0) {
                            if ($value == '') {
                                $errors[] = __('Destination Zip to can not be empty %1', $value);
                            } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
                                $errors[] = __('Destination Zip to should be a numeric value %1', $value);
                            } else {
                                $data[$key] = $value;
                            }
                        }
                    }
                    break;
                case 'price_type':
                    if ($value == '') {
                        $errors[] = __('Price type can not be empty %1', $value);
                    } elseif (strtolower(trim($value)) == 'fixed') {
                        $data[$key] = 0;
                    } elseif (strtolower(trim($value)) == 'percentage') {
                        $data[$key] = 1;
                    } else {
                        $errors[] = __('Invalid value for Price Type %1', $value);
                    }
                    break;
                case 'fixed_price':
                    if (array_key_exists('price_type', $data)) {
                        if ($data['price_type'] == 0) {
                            if ($value == '') {
                                $errors[] = __('Fixed price can not be empty %1', $value);
                            } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
                                $errors[] = __('Not a valid value for fixed price %1', $value);
                            } else {
                                $data[$key] = $value;
                            }
                        }
                    }
                    break;
                case 'percentage_price':
                    if (array_key_exists('price_type', $data)) {
                        if ($data['price_type'] == 1) {
                            if ($value == '') {
                                $errors[] = __('Percentage price can not be empty %1', $value);
                            } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
                                $errors[] = __('Not a valid value for percentage price %1', $value);
                            } else {
                                $data[$key] = $value;
                            }
                        }
                    }
                    break;
                case 'weight_from':
                    if ($value == '') {
                        $errors[] = __('Weight From can not be empty %1', $value);
                    } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
                        $errors[] = __('Not a valid value for weight from field %1', $value);
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'weight_to':
                    if ($value == '') {
                        $errors[] = __('Weight To can not be empty %1', $value);
                    } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
                        $errors[] = __('Not a valid value for weight to field %1', $value);
                    } else {
                        $data[$key] = $value;
                    }
                    break;
            }
        }
        return [$data, $errors];
    }

    // get country codes in array to check valid country code
    public function getCountryCollection()
    {
        $countryCodes = [];
        $collection = $this->_countryCollectionFactory
                        ->create()->loadByStore()
                        ->toOptionArray();
        if (count($collection)) {
            foreach ($collection as $key => $value) {
                $countryCodes[] = $value['value'];
            }
        }
        return $countryCodes;
    }

    // check whether a pricerule already exists or not
    public function checkedNewRate($wholedata)
    {
        unset($wholedata['percentage_price']);
        unset($wholedata['fixed_price']);
        $priceRuleCollection = $this->_priceruleFactory->create()
                            ->getCollection();
        foreach ($wholedata as $key => $value) {
            $priceRuleCollection->addFieldToFilter($key, ['eq'=>$value]);
        }
        if (count($priceRuleCollection)) {
            foreach ($priceRuleCollection as $value) {
                return $value->getEntityId();
            }
        }
        return 0;
    }
}
