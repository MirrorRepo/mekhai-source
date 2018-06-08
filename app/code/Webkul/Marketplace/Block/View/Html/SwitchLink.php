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

class SwitchLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data                  $helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Render block HTML
     *<li data-label="'.__('or').'" class="help_customer"><a href="#" >' . __('Help') . '</a></li><li data-label="'.__('or').'" class="download_app"><a href="#" >' . __('App') . '</a></li>
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helper->getIsSeparatePanel()) {
            if (false != $this->getTemplate()) {
                return parent::_toHtml();
            }
            $label = $this->escapeHtml($this->getLabel());
            return '<li data-label="'.__('or').'" class="vendor-dashboard"><a ' . $this->getLinkAttributes() . ' >' . $label . '</a></li>';
        }
    }
}
