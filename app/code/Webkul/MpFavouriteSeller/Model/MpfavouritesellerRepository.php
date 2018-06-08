<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
namespace Webkul\MpFavouriteSeller\Model;

use Webkul\MpFavouriteSeller\Api\Data\MpfavouritesellerInterface;
use Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class MpfavouritesellerRepository implements \Webkul\MpFavouriteSeller\Api\MpfavouritesellerRepositoryInterface
{
    /**
     * @var MpfavouritesellerRepository
     */
    protected $_mpFavouritesellerFactory;

    /**
     * @var \Webkul\MpFavouriteSeller\Model\ResourceModel\MpFavouriteSeller\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @param MpfavouritesellerFactory                                         $mpFavouritesellerFactory
     * @param \Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller\CollectionFactory $collectionFactory
     * @param \Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller  $resourceModel
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter             $extensibleDataObjectConverter
     */
    public function __construct(
        MpfavouritesellerFactory $mpFavouritesellerFactory,
        \Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller\CollectionFactory $collectionFactory,
        \Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller $resourceModel,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
    
        $this->_resourceModel = $resourceModel;
        $this->_mpFavouritesellerFactory = $mpFavouritesellerFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * get seller collection by customer id
     * @param  int $sellerId   contain seller id
     * @param  id $customerId contain customer id
     * @return object
     */
    public function getSellerCollectionByCustomerId($sellerId, $customerId)
    {
        $sellerCollection = $this->_mpFavouritesellerFactory->create()->getCollection()
        ->addFieldToFilter(
            'seller_id',
            [
                'eq'=>$sellerId
            ]
        )
        ->addFieldToFilter(
            'customer_id',
            [
                'eq'=>$customerId
            ]
        );

        return $sellerCollection;
    }

    /**
     * get all seller collection by seller id
     * @param  int $customerId contain customer id
     * @return object
     */
    public function getsellersCollectionByCustomerId($customerId)
    {
        $sellersCollection = $this->_mpFavouritesellerFactory->create()->getCollection()
        ->addFieldToFilter(
            'customer_id',
            [
                'eq'=>$customerId
            ]
        );
        return $sellersCollection;
    }

    /**
     * get all customers of a seller
     * @param  int $sellerId contain seller id
     * @return object
     */
    public function getCustomersCollectionBySellerId($sellerId)
    {
        $sellersCollection = $this->_mpFavouritesellerFactory->create()->getCollection()
        ->addFieldToFilter(
            'seller_id',
            [
                'eq'=>$sellerId
            ]
        );
        return $sellersCollection;
    }
}
