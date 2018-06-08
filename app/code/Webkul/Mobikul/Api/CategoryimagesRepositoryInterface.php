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

    namespace Webkul\Mobikul\Api;
    use Magento\Framework\Api\SearchCriteriaInterface;
    use Webkul\Mobikul\Api\Data\CategoryimagesInterface;

    interface CategoryimagesRepositoryInterface     {

        public function save(CategoryimagesInterface $categoryimages);

        public function getById($categoryimagesId);

        public function getList(SearchCriteriaInterface $searchCriteria);

        public function delete(CategoryimagesInterface $categoryimages);

        public function deleteById($categoryimagesId);

    }