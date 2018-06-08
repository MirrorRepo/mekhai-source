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
namespace Webkul\Mpcashondelivery\Block\Adminhtml\Seller\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block.
 */
class Report extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'tab/report.phtml';
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Return Tab label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Seller Cash On Delivery Report');
    }
    /**
     * Return Tab title.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Seller Cash On Delivery Report');
    }
    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }
    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }
    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }
    /**
     * Can show tab in tabs.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->_coreRegistry->registry(
            RegistryConstants::CURRENT_CUSTOMER_ID
        );
    }
    /**
     * Tab is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
    /**
     * Initialize the form.
     *
     * @return $this
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_report');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Admin Comission Report')]
        );
        $fieldset->addField(
            'wk_total_sellerprice',
            'label',
            [
                'label' => __('Total commission from this seller'),
                'name' => 'wk_total_sellerprice',
                'value' => 10,
            ]
        );
        $fieldset->addField(
            'customnote',
            'textarea',
            [
                'label' => __('Add Comment'),
                'name' => 'customnote',
            ]
        );
        $fieldset->addField(
            'notifybutton',
            'button',
            [
                'value' => __('Notify Seller'),
                'name' => 'notifybutton',
            ]
        );
        $this->setForm($form);

        return $this;
    }
    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Webkul\Mpcashondelivery\Block\Adminhtml\Seller\Edit\Tab\Report\Grid',
                'report.grid'
            )
        );
        parent::_prepareLayout();

        return $this;
    }
    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();

            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
