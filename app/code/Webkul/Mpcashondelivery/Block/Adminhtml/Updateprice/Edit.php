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
namespace Webkul\Mpcashondelivery\Block\Adminhtml\Updateprice;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Webkul_Mpcashondelivery';
        $this->_controller = 'adminhtml_updateprice';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_Mpcashondelivery::mpcodrates')) {
            $this->buttonList->update('save', 'label', __('Save Cash on Delivery Rates'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' =>
                                ['event' => 'saveAndContinueEdit',
                                    'target' => '#edit_form'
                                ],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
        if ($this->_isAllowedAction('Webkul_Mpcashondelivery::delete')) {
            $this->buttonList->update(
                'delete',
                'label',
                __('Delete Rate')
            );
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $codRegistry = $this->_coreRegistry->registry('cod_pricerates');
        $codPrice = $this->escapeHtml($codRegistry);
        if ($codPrice->getEntityId()) {
            return __("Edit rates '%1'", $codPrice->getEntityId());
        } else {
            return __('New rates');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    /**
     * Getter of url for "Save and Continue" button
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'updateprice/*/save',
            ['_current' => true,
                'back' => 'edit',
                'active_tab' => ''
            ]
        );
    }
}
