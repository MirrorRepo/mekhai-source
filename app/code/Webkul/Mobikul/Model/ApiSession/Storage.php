<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Model\ApiSession;

    class Storage extends \Magento\Framework\Session\Storage    {

        public function __construct(
            \Magento\Customer\Model\Config\Share $configShare,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            $namespace="mobikul",
            array $data=[]
        ) {
            parent::__construct($namespace, $data);
        }

    }