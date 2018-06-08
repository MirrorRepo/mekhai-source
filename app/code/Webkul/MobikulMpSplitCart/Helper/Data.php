<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpSplitCart
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MobikulMpSplitCart\Helper;

/**
 * Webkul MobikulMpSplitCart Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\MobikulMpSplitCart\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }
}
