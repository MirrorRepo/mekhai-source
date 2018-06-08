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

namespace Webkul\Mpcashondelivery\Model;

use Webkul\Mpcashondelivery\Api\Data\PricerulesInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Codorders extends \Magento\Framework\Model\AbstractModel implements PricerulesInterface, IdentityInterface
{
    const CACHE_TAG = 'mpcashondelivery_codorders';

    /**
     * @var string
     */
    protected $_cacheTag = 'mpcashondelivery_codorders';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpcashondelivery_codorders';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Mpcashondelivery\Model\ResourceModel\Codorders');
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
