<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
namespace Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller;

use \Webkul\MpFavouriteSeller\Model\ResourceModel\AbstractCollection;

/**
 * Webkul MpFavouriteSeller ResourceModel Mpfavouriteseller collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Webkul\MpFavouriteSeller\Model\Mpfavouriteseller',
            'Webkul\MpFavouriteSeller\Model\ResourceModel\Mpfavouriteseller'
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
    
    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
