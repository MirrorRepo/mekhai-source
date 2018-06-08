<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpFavouriteSeller\Api;

/**
 * @api
 */
interface MpfavouritesellerRepositoryInterface
{
    /**
     * get seller collection by customer id
     * @param  int $sellerId   contain seller id
     * @param  id $customerId contain customer id
     * @return object
     */
    public function getSellerCollectionByCustomerId($sellerId, $customerId);

    /**
     * get all seller collection by seller id
     * @param  int $customerId contain customer id
     * @return object
     */
    public function getsellersCollectionByCustomerId($customerId);

    /**
     * get all customers of a seller
     * @param  int $sellerId contain seller id
     * @return object
     */
    public function getCustomersCollectionBySellerId($sellerId);
}
