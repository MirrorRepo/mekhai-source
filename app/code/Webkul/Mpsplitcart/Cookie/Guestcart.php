<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitcart
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpsplitcart\Cookie;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 *  Webkul Mpsplitcart Cookie Guestcart
 */
class Guestcart
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddressInstance;

    /**
     * [__construct ]
     *
     * @param CookieManagerInterface                    $cookieManager
     * @param CookieMetadataFactory                     $cookieMetadataFactory
     * @param SessionManagerInterface                   $sessionManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->_objectManager = $objectManager;
        $this->_remoteAddressInstance = $this->_objectManager->get(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress'
        );
    }

    /**
     * Get data from cookie
     *
     * @return string
     */
    public function get()
    {
        return $this->_cookieManager->getCookie($this->getRemoteAddress());
    }

    /**
     * [set used to set virtual cart in cookie for guest user]
     *
     * @param [string] $value    [contains value of cookie]
     * @param integer  $duration [contains duration for cookie]
     *
     * @return void
     */
    public function set($value, $duration = 86400)
    {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());

        $this->_cookieManager->setPublicCookie(
            $this->getRemoteAddress(),
            $value,
            $metadata
        );
    }

    /**
     * [delete used to delete cookie]
     *
     * @return void
     */
    public function delete()
    {
        $this->_cookieManager->deleteCookie(
            $this->getRemoteAddress(),
            $this->_cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->_sessionManager->getCookiePath())
                ->setDomain($this->_sessionManager->getCookieDomain())
        );
    }

    /**
     * [getRemoteAddress used to get remote address]
     *
     * @return [string] [returns modified string of remote addr]
     */
    public function getRemoteAddress()
    {
        $str = str_replace(
            ".",
            "_",
            $this->_remoteAddressInstance->getRemoteAddress()
        );
        return $str;
    }
}
