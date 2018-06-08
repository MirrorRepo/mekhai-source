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
namespace Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\ObjectManagerInterface;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var InlineInterface
     */
    protected $_translateInline;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Framework\Json\EncoderInterface    $jsonEncoder
     * @param Magento\Backend\Model\Auth\Sessio           $authSession
     * @param array                                       $data
     */
    public function __construct(
        Context $context,
        InlineInterface $translateInline,
        ObjectManagerInterface $objectManager,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        array $data = []
    ) {
        $this->_translateInline = $translateInline;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('codrates_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Cash On Delivery Rates Information'));
    }

    /**
     * Prepare Layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'shippinginfo',
            [
                'label' => __('Cash On Delivery Rates Detail'),
                'url' => $this->getUrl('mpcashondelivery/*/grid', ['_current' => true]),
                'class' => 'ajax',
            ]
        );
        $this->addTab(
            'addshipping',
            [
                'label' => __('Add Cash On Delivery Rates'),
                'content' => $this->getLayout()->createBlock(
                    'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Edit\Tab\Form'
                )->toHtml(),
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Translate html content.
     *
     * @param string $html
     *
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);

        return $html;
    }
}
