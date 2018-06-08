<?php

/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer;

class Notifyseller extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Array to store all options data.
     *
     * @var array
     */
    protected $_actions = [];

    /**
     * Return Actions.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->_actions = [];
        $adminStatus = $row->getAdminPayStatus();
        if ($adminStatus == 0 && $row->getCollectCodStatus() == 1) {
            $actions[0] = [
                '@' => [
                    'type' => 'button',
                    'class' => 'button wk_notifyseller',
                    'order-id' => $row->getEntityId(),
                    'title' => __('Notify Seller'),
                ],
                '#' => __('Notify'),
            ];
        } elseif ($adminStatus == 1 && $row->getCollectCodStatus() == 1) {
            $actions[0] = [
                '@' => [],
                '#' => __('Already Paid'),
            ];
        } else {
            $actions[0] = [
                '@' => [],
                '#' => __('Pending'),
            ];
        }
        $this->addToActions($actions);

        return $this->_actionsToHtml();
    }

    /**
     * Get escaped value.
     *
     * @param string $value
     *
     * @return string
     */
    protected function _getEscapedValue($value)
    {
        return addcslashes(htmlspecialchars($value), '\\\'');
    }

    /**
     * Render options array as a HTML string.
     *
     * @param array $actions
     *
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();
        if (empty($actions)) {
            $actions = $this->_actions;
        }
        foreach ($actions[0] as $action) {
            if (count($action['@'])) {
                $attributesObject->setData($action['@']);
                $html[] = '<button '.$attributesObject->serialize().'>'.$action['#'].'</button>';
            } else {
                $html[] = '<span>'.$action['#'].'</span>';
            }
        }

        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage.
     *
     * @param array $actionArray
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
