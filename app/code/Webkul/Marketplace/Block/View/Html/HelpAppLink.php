<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Block\View\Html;

class HelpAppLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data                  $helper
     * @param array                                            $data
     */

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return '<li data-label="'.__('or').'" class="help_customer"><a href="help" >' . __('Help') . '</a></li><li data-label="'.__('or').'" class="download_app"><a href="app-download" >' . __('App') . '</a></li>';
    }
}
