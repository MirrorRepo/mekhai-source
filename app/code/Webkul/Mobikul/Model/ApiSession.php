<?php
    /**
    * Webkul Software api session.
    *
    * @category Webkul_Mobikul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Model;

    class ApiSession extends \Magento\Framework\Session\SessionManager  {

        protected $_apiHelper;

        public function __construct(
            \Magento\Framework\App\Request\Http $request,
            \Magento\Framework\Session\SidResolverInterface $sidResolver,
            \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
            \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
            \Magento\Framework\Session\ValidatorInterface $validator,
            \Magento\Framework\Session\StorageInterface $storage,
            \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
            \Magento\Framework\App\State $appState,
            \Webkul\Mobikul\Helper\Session $apiHelper
        ) {
            parent::__construct(
                $request,
                $sidResolver,
                $sessionConfig,
                $saveHandler,
                $validator,
                $storage,
                $cookieManager,
                $cookieMetadataFactory,
                $appState
            );
            $this->_apiHelper = $apiHelper;
        }

        public function generateSessionId() {
            $sessionId = $this->getSessionId();
            return $sessionId;
        }

        public function isAuthenticRequest($sessionId)  {
            $sessionKey = $this->getApiId();
            if ($sessionKey && $sessionId != null) {
                if ($sessionKey == $sessionId) {
                    return 1;
                } else {
                    return 0;
                }
            }
            return 0;
        }

        public function setApiId($id)   {
            $this->storage->setData("api_id", $id);
            return $this;
        }

        public function getApiId()  {
            if ($this->storage->getData("api_id")) {
                return $this->storage->getData("api_id");
            }
            return;
        }

        public function getCookieLifetime(){
            return $this->_apiHelper->getTimeout();
        }

    }