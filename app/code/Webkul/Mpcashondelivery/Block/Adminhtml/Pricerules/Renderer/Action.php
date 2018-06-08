<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->_actions = [];
        $actions[0] = [
            '@' => [
                'href' => $this->getUrl(
                    'mpcashondelivery/updateprice/index',
                    ['entity_id' => $row->getEntityId()]
                ),
            ],
            '#' => __('Edit'),
        ];
        $actions[1] = [
            '@' => [
                'href' => '#'
            ],
            '#' => __('/'),
        ];
        $actions[2] = [
            '@' => [
                'href' => $this->getUrl(
                    'mpcashondelivery/pricerules/delete',
                    ['entity_id' => $row->getEntityId()]
                ),
            ],
            '#' => __('Delete'),
        ];
        $this->addToActions($actions);
        return $this->_actionsToHtml();
    }

    /**
     * Get escaped value
     *
     * @param string $value
     * @return string
     */
    protected function _getEscapedValue($value)
    {
        return addcslashes(htmlspecialchars($value), '\\\'');
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
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
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
