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
use Magento\Framework\DataObject\IdentityInterface;

/**
 * MpFavouriteSeller Model.
 *
 * @method \Webkul\MpSellerGroup\Model\ResourceModel\SellerGroup _getResource()
 * @method \Webkul\MpSellerGroup\Model\ResourceModel\SellerGroup getResource()
 */
class Mpfavouriteseller extends \Magento\Framework\Model\AbstractModel implements
    MpfavouritesellerInterface,
    IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * MpFavouriteSeller's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * MpFavouriteSeller cache tag.
     */
    const CACHE_TAG = 'marketplace_mpfavouriteseller';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_mpfavouriteseller';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_mpfavouriteseller';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller');
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSellerGroup();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route MpFavouriteSeller.
     *
     * @return \Webkul\MpFavouriteSeller\Model\Mpfavouriteseller
     */
    public function noRouteSellerGroup()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Seller Id.
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->_getData(self::SELLER_ID);
    }

    /**
     * Get CustomerId Code.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * get liked at
     * @return timestamp
     */
    public function getLikedAt()
    {
        return $this->_getData(self::LIKED_AT);
    }

    /**
     * set seller id
     * @param int $sellerId contain seller id
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * set customer id
     * @param int $customerId contain customer id
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * set liked at
     * @param date $likedAt
     */
    public function setLikedAt($likedAt)
    {
        return $this->setData(self::LIKED_AT, $likedAt);
    }
}
