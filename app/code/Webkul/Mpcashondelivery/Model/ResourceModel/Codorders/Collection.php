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

namespace Webkul\Mpcashondelivery\Model\ResourceModel\Codorders;

use \Webkul\Mpcashondelivery\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(
            'Webkul\Mpcashondelivery\Model\Codorders',
            'Webkul\Mpcashondelivery\Model\ResourceModel\Codorders'
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
