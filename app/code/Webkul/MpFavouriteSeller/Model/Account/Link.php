<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
namespace Webkul\MpFavouriteSeller\Model\Account;

use Magento\Framework\View\Element\Html\Link\Current;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Webkul\MpFavouriteSeller\Helper\Data');
        if (!$helper->checkIsSeller()) {
            return "";
        }
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }
        $highlight = '';
        if ($this->getIsHighlighted()) {
            $highlight = ' current';
        }
        if ($this->isCurrent()) {
            $html = '<li class="nav item current">';
            $html .= '<strong>'
                . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel()))
                . '</strong>';
            $html .= '</li>';
        } else {
            $html = '<li class="nav item' . $highlight . '"><a href="' . $this->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle()
                ? ' title="' . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getTitle())) . '"'
                : '';
            $html .= $this->getAttributesHtml() . '>';
            if ($this->getIsHighlighted()) {
                $html .= '<strong>';
            }
            $html .= $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel()));
            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }
            $html .= '</a></li>';
        }
        return $html;
    }
}
