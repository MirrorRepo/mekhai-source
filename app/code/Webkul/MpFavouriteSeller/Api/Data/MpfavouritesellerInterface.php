<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpFavouriteSeller\Api\Data;

/**
 * MpFavouriteSeller MpfavouritesellerInterface interface.
 *
 * @api
 */
interface MpfavouritesellerInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID   = 'entity_id';

    const CUSTOMER_ID = 'customer_id';

    const SELLER_ID   = 'seller_id';

    const LIKED_AT    = 'liked_at';

    /**#@-*/

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpFavouriteSeller\Api\Data\MpFavouriteSellerInterface
     */
    public function setId($id);

    /**
     * get Seller Id
     *
     * @return int|null
     */
    public function getSellerId();
    /**
     * set seller Id
     * @param int $sellerId
     */
    
    public function setSellerId($sellerId);
    /**
     * get customer id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * set customer Id
     * @param int $customerId contain customer id
     */
    
    public function setCustomerId($customerId);
    /**
     * get liked_at
     * @return int|null
     */
    public function getLikedAt();

    /**
     * set liked at
     * @param date $timeStamp
     */
    public function setLikedAt($timeStamp);
}
