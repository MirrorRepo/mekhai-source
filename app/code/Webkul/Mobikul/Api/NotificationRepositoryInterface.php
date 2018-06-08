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
    use Webkul\Mobikul\Api\Data\NotificationInterface;

    interface NotificationRepositoryInterface   {

        public function save(NotificationInterface $notification);

        public function getById($notificationId);

        public function getList(SearchCriteriaInterface $searchCriteria);

        public function delete(NotificationInterface $notification);

        public function deleteById($notificationId);

    }