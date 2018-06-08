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
    use Webkul\Mobikul\Api\Data\BannerimageInterface;

    interface BannerimageRepositoryInterface    {

        public function save(BannerimageInterface $bannerimage);

        public function getById($bannerimageId);

        public function getList(SearchCriteriaInterface $searchCriteria);

        public function delete(BannerimageInterface $bannerimage);

        public function deleteById($bannerimageId);

    }